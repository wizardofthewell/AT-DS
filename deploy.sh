#!/usr/bin/env bash

set -euo pipefail

DEPLOY_ENV_USER=${DEPLOY_ENV_USER:-${SUDO_USER:-$(whoami)}}

DEPLOY_ENV_HOME=$(getent passwd "$DEPLOY_ENV_USER" | cut -d: -f6 || true)
if [ -z "$DEPLOY_ENV_HOME" ]; then
    DEPLOY_ENV_HOME="/home/${DEPLOY_ENV_USER}"
fi
# Default to a project-specific deployment env file so multiple apps on the same
# host do not trample each other when no explicit DEPLOY_ENV_FILE is provided.
DEFAULT_DEPLOY_ENV_FILENAME=${DEFAULT_DEPLOY_ENV_FILENAME:-".env_harbourstone"}
DEPLOY_ENV_FILE=${DEPLOY_ENV_FILE:-"${DEPLOY_ENV_HOME}/${DEFAULT_DEPLOY_ENV_FILENAME}"}

if [ -f "$DEPLOY_ENV_FILE" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Loading deployment env from $DEPLOY_ENV_FILE"
    set -a
    . "$DEPLOY_ENV_FILE"
    set +a
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] No deploy env file found at $DEPLOY_ENV_FILE; proceeding with defaults"
fi

GIT_PROVIDER=${GIT_PROVIDER:-"github"}

REPO_URL=${REPO_URL:-"git@${GIT_PROVIDER}.com:user/repo.git"}

REPO_DIR=${REPO_DIR:-"/srv/my-laravel-app/repo"}

DEPLOY_DIR=${DEPLOY_DIR:-"/var/www/my-laravel-app"}

APP_USER=${APP_USER:-"www-data"}

BRANCH=${BRANCH:-"main"}

APP_NAME=${APP_NAME:-"my-laravel-app"}

NPM_SKIP=${NPM_SKIP:-1}

RELEASES_DIR=${RELEASES_DIR:-"${DEPLOY_DIR}/releases"}
SHARED_DIR=${SHARED_DIR:-"${DEPLOY_DIR}/shared"}
CURRENT_LINK=${CURRENT_LINK:-"${DEPLOY_DIR}/current"}
KEEP_RELEASES=${KEEP_RELEASES:-5}
OPCACHE_RESET=${OPCACHE_RESET:-1}
OPCACHE_RELOAD_WAIT_SECONDS=${OPCACHE_RELOAD_WAIT_SECONDS:-2}
RESTART_PHPFPM_ON_RELOAD_FAIL=${RESTART_PHPFPM_ON_RELOAD_FAIL:-0}
HEALTHCHECK_URL=${HEALTHCHECK_URL:-}
HEALTHCHECK_ATTEMPTS=${HEALTHCHECK_ATTEMPTS:-10}
HEALTHCHECK_DELAY=${HEALTHCHECK_DELAY:-1}

MAINTENANCE_MODE=${MAINTENANCE_MODE:-0}
MIGRATE_BEFORE_SWITCH=${MIGRATE_BEFORE_SWITCH:-1}
# Seed the admin user + starter content once, on the first deploy only (marker file in shared/)
SEED_ON_FIRST_DEPLOY=${SEED_ON_FIRST_DEPLOY:-1}
DOCROOT_SYMLINK=${DOCROOT_SYMLINK:-1}
DOCROOT_CONVERT_IF_DIR=${DOCROOT_CONVERT_IF_DIR:-1}

COMPOSER_USER=${COMPOSER_USER:-${SUDO_USER:-$(whoami)}}

LOCK_FILE=${LOCK_FILE:-"/tmp/${APP_NAME}.deploy.lock"}

# Normalize paths to avoid accidental double slashes and help comparisons
DEPLOY_DIR="${DEPLOY_DIR%/}"
REPO_DIR="${REPO_DIR%/}"
RELEASES_DIR="${RELEASES_DIR%/}"
SHARED_DIR="${SHARED_DIR%/}"
CURRENT_LINK="${CURRENT_LINK%/}"

umask 002

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"; }
fail() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $1" >&2; exit 1; }
require_cmd() { command -v "$1" >/dev/null 2>&1 || fail "Missing required command: $1"; }


trap 'fail "Unexpected error on line $LINENO"' ERR

run_artisan_tasks() {
    local dir="$1"
    cd "$dir" || fail "Cannot cd to $dir"

    if [ "$MAINTENANCE_MODE" -eq 1 ]; then
        log "Enabling maintenance mode..."
        sudo -u "$APP_USER" php artisan down || true
    fi

    log "Running database migrations..."
    sudo -u "$APP_USER" php artisan migrate --force --no-interaction

    log "Clearing and rebuilding caches..."
    sudo -u "$APP_USER" php artisan optimize:clear
    sudo -u "$APP_USER" php artisan config:cache
    sudo -u "$APP_USER" php artisan route:cache
    sudo -u "$APP_USER" php artisan view:cache
    sudo -u "$APP_USER" php artisan event:cache || true

    # Clear Spatie permission cache to ensure role/permission changes take effect
    if (sudo -u "$APP_USER" php artisan list | grep -q "^  permission:cache-reset" ); then
        log "Clearing permission cache..."
        sudo -u "$APP_USER" php artisan permission:cache-reset || true
    else
        log "permission:cache-reset command not available; skipping"
    fi

    if [ "$MAINTENANCE_MODE" -eq 1 ]; then
        log "Disabling maintenance mode..."
        sudo -u "$APP_USER" php artisan up || true
    fi
}

prepare_shared() {
    sudo mkdir -p \
        "$RELEASES_DIR" \
        "$SHARED_DIR" \
        "$SHARED_DIR/storage" \
        "$SHARED_DIR/storage/logs" \
        "$SHARED_DIR/storage/app/public" \
        "$SHARED_DIR/storage/framework/cache/data" \
        "$SHARED_DIR/storage/framework/sessions" \
        "$SHARED_DIR/storage/framework/views" \
        "$SHARED_DIR/public_build"

    # Check if shared .env already exists (preserves manual changes)
    if [ -f "$SHARED_DIR/.env" ]; then
        log "Shared .env already exists at $SHARED_DIR/.env - preserving existing file and manual changes"
        log "To reset .env from source, manually delete $SHARED_DIR/.env before deploying"
    else
        if [ -e "$CURRENT_LINK" ] && [ -f "$CURRENT_LINK/.env" ]; then
            log "Moving existing .env from current release ($CURRENT_LINK/.env) to shared/.env"
            sudo mv "$CURRENT_LINK/.env" "$SHARED_DIR/.env"
            sudo ln -sfn "$SHARED_DIR/.env" "$CURRENT_LINK/.env"
        elif [ -f "$DEPLOY_DIR/.env" ]; then
            log "Moving existing .env from $DEPLOY_DIR/.env to shared/.env"
            sudo mv "$DEPLOY_DIR/.env" "$SHARED_DIR/.env"
        else
            log "WARNING: No .env file source found. You will need to manually create $SHARED_DIR/.env"
        fi

        if [ -f "$SHARED_DIR/.env" ]; then
            sudo chown "$APP_USER":"$APP_USER" "$SHARED_DIR/.env" || true
            sudo chmod 640 "$SHARED_DIR/.env" || true
        fi
    fi

    if [ -d "$DEPLOY_DIR/storage" ] && [ ! -d "$SHARED_DIR/storage_migrated" ]; then
        log "Moving existing storage to shared/storage"
        sudo rsync -aH "$DEPLOY_DIR/storage/" "$SHARED_DIR/storage/"
        sudo touch "$SHARED_DIR/storage_migrated"
    fi
    sudo chown -R "$APP_USER":"$APP_USER" "$SHARED_DIR"
    sudo chmod -R 775 "$SHARED_DIR"
}

create_release() {
    local base_id
    base_id=$(date +%Y%m%d%H%M%S)
    while [ -d "$RELEASES_DIR/$base_id" ]; do
        sleep 1
        base_id=$(date +%Y%m%d%H%M%S)
    done
    NEW_RELEASE_DIR="$RELEASES_DIR/$base_id"
    log "Creating new release at $NEW_RELEASE_DIR"
    sudo mkdir -p "$NEW_RELEASE_DIR"
        local _REPO_REAL _DEPLOY_REAL
        _REPO_REAL=$(readlink -f "$REPO_DIR" 2>/dev/null || echo "$REPO_DIR")
        _DEPLOY_REAL=$(readlink -f "$DEPLOY_DIR" 2>/dev/null || echo "$DEPLOY_DIR")
        if [ "$_REPO_REAL" = "$_DEPLOY_REAL" ]; then
                log "In-place deploy detected; exporting tracked files from git instead of rsync to avoid recursion"
                sudo chown -R "$COMPOSER_USER":"$COMPOSER_USER" "$NEW_RELEASE_DIR"
                (
                    cd "$REPO_DIR" && \
                    git archive --format=tar HEAD -- \
                        . \
                        ':(exclude).env' \
                        ':(exclude)vendor/**' \
                        ':(exclude)node_modules/**' \
                        ':(exclude)storage/**' \
                        ':(exclude).github/**' \
                        ':(exclude)tests/**' \
                        ':(exclude)releases/**' \
                        ':(exclude)shared/**' \
                        ':(exclude)current' \
                    | sudo tar -x -C "$NEW_RELEASE_DIR"
                )
        else
                sudo rsync -aH \
                        --exclude='.env' \
                        --exclude='vendor' \
                        --exclude='node_modules' \
                        --exclude='.github' \
                        --exclude='tests' \
                        --exclude='/releases/**' \
                        --exclude='/shared/**' \
                        --exclude='/current' \
                        --exclude='.git' \
                        "$REPO_DIR"/ "$NEW_RELEASE_DIR"/
        fi

    if [ -f "$SHARED_DIR/.env" ]; then
        sudo ln -sfn "$SHARED_DIR/.env" "$NEW_RELEASE_DIR/.env"
    else
        log "WARNING: No shared .env present at $SHARED_DIR/.env"
    fi
    sudo rm -rf "$NEW_RELEASE_DIR/storage"
    sudo ln -sfn "$SHARED_DIR/storage" "$NEW_RELEASE_DIR/storage"
    sudo mkdir -p "$NEW_RELEASE_DIR/bootstrap/cache"

    sudo chown -R "$COMPOSER_USER":"$COMPOSER_USER" "$NEW_RELEASE_DIR"
    sudo chown -R "$APP_USER":"$APP_USER" "$NEW_RELEASE_DIR/bootstrap/cache" "$NEW_RELEASE_DIR/storage"
    sudo chmod -R 775 "$NEW_RELEASE_DIR/bootstrap/cache" "$NEW_RELEASE_DIR/storage"
}

build_release() {
    log "Installing PHP dependencies in new release..."
    sudo -u "$COMPOSER_USER" composer install \
        --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts \
        -d "$NEW_RELEASE_DIR"

    sudo mkdir -p "$NEW_RELEASE_DIR/bootstrap/cache"
    sudo chown -R "$APP_USER":"$APP_USER" "$NEW_RELEASE_DIR/bootstrap/cache" "$NEW_RELEASE_DIR/storage"
    sudo chmod -R 775 "$NEW_RELEASE_DIR/bootstrap/cache" "$NEW_RELEASE_DIR/storage"

    (cd "$NEW_RELEASE_DIR" && sudo -u "$APP_USER" php artisan package:discover --ansi || true)

    log "Building frontend assets in new release (if npm available and package.json exists)..."
    if [ -f "$NEW_RELEASE_DIR/package.json" ] && [ "${NPM_SKIP}" -eq 0 ]; then
        if [ -f "$NEW_RELEASE_DIR/package-lock.json" ]; then
            sudo -u "$COMPOSER_USER" npm ci --prefix "$NEW_RELEASE_DIR"
        else
            sudo -u "$COMPOSER_USER" npm install --prefix "$NEW_RELEASE_DIR"
        fi
        sudo -u "$COMPOSER_USER" npm run build --prefix "$NEW_RELEASE_DIR"
        if [ -f "$NEW_RELEASE_DIR/public/build/.vite/manifest.json" ] && [ ! -f "$NEW_RELEASE_DIR/public/build/manifest.json" ]; then
            sudo cp "$NEW_RELEASE_DIR/public/build/.vite/manifest.json" "$NEW_RELEASE_DIR/public/build/manifest.json"
        fi
        if [ -d "$NEW_RELEASE_DIR/public/build" ]; then
            log "Syncing built assets to shared public_build and symlinking..."
            sudo rsync -aH --delete "$NEW_RELEASE_DIR/public/build/" "$SHARED_DIR/public_build/"
            if [ -f "$SHARED_DIR/public_build/.vite/manifest.json" ] && [ ! -f "$SHARED_DIR/public_build/manifest.json" ]; then
                sudo cp "$SHARED_DIR/public_build/.vite/manifest.json" "$SHARED_DIR/public_build/manifest.json"
            fi
            sudo chown -R "$APP_USER":"$APP_USER" "$SHARED_DIR/public_build"
            sudo chmod -R 775 "$SHARED_DIR/public_build"
            sudo rm -rf "$NEW_RELEASE_DIR/public/build"
            sudo ln -sfn "$SHARED_DIR/public_build" "$NEW_RELEASE_DIR/public/build"
        fi
    fi

    if [ ! -e "$NEW_RELEASE_DIR/public/build" ]; then
        if [ -f "$SHARED_DIR/public_build/manifest.json" ]; then
            log "Linking shared public_build into release..."
            sudo ln -sfn "$SHARED_DIR/public_build" "$NEW_RELEASE_DIR/public/build"
        elif [ -d "$CURRENT_LINK/public/build" ] && [ -f "$CURRENT_LINK/public/build/manifest.json" ]; then
            log "No shared build found; copying from current release to shared and linking..."
            sudo rsync -aH --delete "$CURRENT_LINK/public/build/" "$SHARED_DIR/public_build/"
            sudo chown -R "$APP_USER":"$APP_USER" "$SHARED_DIR/public_build"
            sudo chmod -R 775 "$SHARED_DIR/public_build"
            sudo ln -sfn "$SHARED_DIR/public_build" "$NEW_RELEASE_DIR/public/build"
        else
            log "WARNING: No Vite build assets available (npm skipped and no prior build). The site may error on @vite until assets are built."
        fi
    fi

    log "Publishing Filament assets..."
    sudo mkdir -p "$NEW_RELEASE_DIR/public"
    sudo chown -R "$APP_USER":"$APP_USER" "$NEW_RELEASE_DIR/public"
    if (cd "$NEW_RELEASE_DIR" && sudo -u "$APP_USER" php artisan list | grep -q "^  filament:assets" ); then
        if (cd "$NEW_RELEASE_DIR" && sudo -u "$APP_USER" php artisan help filament:assets | grep -q -- "--force" ); then
            (cd "$NEW_RELEASE_DIR" && sudo -u "$APP_USER" php artisan filament:assets --no-interaction --force)
        else
            (cd "$NEW_RELEASE_DIR" && sudo -u "$APP_USER" php artisan filament:assets --no-interaction)
        fi
    else
        (cd "$NEW_RELEASE_DIR" && sudo -u "$APP_USER" php artisan vendor:publish --tag=filament-assets --force || true)
    fi
    (cd "$NEW_RELEASE_DIR" && sudo -u "$APP_USER" php artisan filament:cache-components || true)
    sudo chown -R "$APP_USER":"$APP_USER" "$NEW_RELEASE_DIR/public" || true
    sudo find "$NEW_RELEASE_DIR/public" -type d -exec chmod 775 {} + || true
    sudo find "$NEW_RELEASE_DIR/public" -type f -exec chmod 664 {} + || true
    sudo mkdir -p "$SHARED_DIR/public_css" "$SHARED_DIR/public_js"
    if [ -d "$NEW_RELEASE_DIR/public/css/filament" ]; then
        sudo rsync -aH --delete "$NEW_RELEASE_DIR/public/css/filament/" "$SHARED_DIR/public_css/filament/"
    fi
    if [ -d "$NEW_RELEASE_DIR/public/js/filament" ]; then
        sudo rsync -aH --delete "$NEW_RELEASE_DIR/public/js/filament/" "$SHARED_DIR/public_js/filament/"
    fi
    sudo chown -R "$APP_USER":"$APP_USER" "$SHARED_DIR/public_css" "$SHARED_DIR/public_js" || true
    sudo chmod -R 775 "$SHARED_DIR/public_css" "$SHARED_DIR/public_js" || true
    if [ -d "$SHARED_DIR/public_css/filament" ]; then
        sudo mkdir -p "$NEW_RELEASE_DIR/public/css"
        sudo rm -rf "$NEW_RELEASE_DIR/public/css/filament"
        sudo ln -sfn "$SHARED_DIR/public_css/filament" "$NEW_RELEASE_DIR/public/css/filament"
    fi
    if [ -d "$SHARED_DIR/public_js/filament" ]; then
        sudo mkdir -p "$NEW_RELEASE_DIR/public/js"
        sudo rm -rf "$NEW_RELEASE_DIR/public/js/filament"
        sudo ln -sfn "$SHARED_DIR/public_js/filament" "$NEW_RELEASE_DIR/public/js/filament"
    fi
    if [ ! -f "$NEW_RELEASE_DIR/public/css/filament/filament/app.css" ] || [ ! -f "$NEW_RELEASE_DIR/public/js/filament/filament/app.js" ]; then
        log "WARNING: Filament assets still missing after publish and link."
        log "Expected: $NEW_RELEASE_DIR/public/css/filament/filament/app.css and /js/filament/filament/app.js"
    fi

    log "Priming caches in new release..."
    (cd "$NEW_RELEASE_DIR" && sudo -u "$APP_USER" php artisan optimize:clear && \
        sudo -u "$APP_USER" php artisan config:cache && \
        sudo -u "$APP_USER" php artisan route:cache && \
        sudo -u "$APP_USER" php artisan view:cache && \
        sudo -u "$APP_USER" php artisan event:cache || true)

    # Clear permission cache to ensure role/permission changes take effect
    if (cd "$NEW_RELEASE_DIR" && sudo -u "$APP_USER" php artisan list | grep -q "^  permission:cache-reset" ); then
        log "Clearing permission cache in new release..."
        (cd "$NEW_RELEASE_DIR" && sudo -u "$APP_USER" php artisan permission:cache-reset || true)
    else
        log "permission:cache-reset command not available in new release; skipping"
    fi

    log "Ensuring storage public symlink (public/storage -> storage/app/public)..."
    sudo mkdir -p "$SHARED_DIR/storage/app/public"
    sudo ln -sfn "$NEW_RELEASE_DIR/storage/app/public" "$NEW_RELEASE_DIR/public/storage"
    sudo chown -R "$APP_USER":"$APP_USER" "$SHARED_DIR/storage/app/public"
    sudo chmod -R 775 "$SHARED_DIR/storage/app/public"
}

migrate_release() {
    if [ "$MIGRATE_BEFORE_SWITCH" -eq 1 ]; then
        log "Running database migrations (before switch) in $NEW_RELEASE_DIR..."
        (cd "$NEW_RELEASE_DIR" && sudo -u "$APP_USER" php artisan migrate --force --no-interaction)
    else
        log "Configured to migrate after switch; skipping pre-switch migrations."
    fi
}

seed_first_deploy() {
    local marker="$SHARED_DIR/.seeded"
    if [ "$SEED_ON_FIRST_DEPLOY" -ne 1 ] || [ -f "$marker" ]; then
        log "Starter content already seeded (or seeding disabled); skipping."
        return 0
    fi
    # The seeder reads ADMIN_* with env(), which is empty while config is cached.
    log "First deploy: seeding admin user and starter content..."
    (cd "$NEW_RELEASE_DIR" && sudo -u "$APP_USER" php artisan config:clear && \
        sudo -u "$APP_USER" php artisan db:seed --force --no-interaction && \
        sudo -u "$APP_USER" php artisan config:cache)
    sudo touch "$marker"
    sudo chown "$APP_USER":"$APP_USER" "$marker"
}

switch_current() {
    log "Switching 'current' symlink to new release..."
    sudo ln -sfn "$NEW_RELEASE_DIR" "$CURRENT_LINK"
    sudo chown -h "$APP_USER":"$APP_USER" "$CURRENT_LINK"

    if [ "$DOCROOT_SYMLINK" -eq 1 ]; then
        if [ -L "$DEPLOY_DIR/public" ] || [ ! -e "$DEPLOY_DIR/public" ]; then
            sudo ln -sfn "$CURRENT_LINK/public" "$DEPLOY_DIR/public"
            sudo chown -h "$APP_USER":"$APP_USER" "$DEPLOY_DIR/public"
        else
            if [ "${DOCROOT_CONVERT_IF_DIR}" -eq 1 ] && [ -d "$DEPLOY_DIR/public" ]; then
                ts=$(date +%s)
                log "Converting existing ${DEPLOY_DIR}/public directory to symlink (backup: public.bak.$ts)"
                sudo mv "$DEPLOY_DIR/public" "$DEPLOY_DIR/public.bak.$ts"
                sudo ln -sfn "$CURRENT_LINK/public" "$DEPLOY_DIR/public"
                sudo chown -h "$APP_USER":"$APP_USER" "$DEPLOY_DIR/public"
            else
                log "NOTE: ${DEPLOY_DIR}/public exists and is not a symlink; set DOCROOT_CONVERT_IF_DIR=1 to convert."
            fi
        fi
    fi

    if [ "$MIGRATE_BEFORE_SWITCH" -ne 1 ]; then
        log "Running database migrations (after switch) in $CURRENT_LINK..."
        (cd "$CURRENT_LINK" && sudo -u "$APP_USER" php artisan migrate --force --no-interaction)
    fi
}

# One-shot migration: run legacy payments backfill + import exactly once on next production deploy
cleanup_releases() {
    log "Cleaning up old releases (keeping last $KEEP_RELEASES)..."
    local releases
    releases=$(ls -1 "${RELEASES_DIR}" | sort || true)
    local count total
    total=$(echo "$releases" | wc -l || echo 0)
    if [ "$total" -le "$KEEP_RELEASES" ]; then
        log "No old releases to clean up. Total: $total"
        return 0
    fi
    count=$((total - KEEP_RELEASES))

    local current_target
    current_target=$(readlink -f "$CURRENT_LINK" 2>/dev/null || echo "")

    echo "$releases" | head -n "$count" | while read -r old; do
        [ -n "$old" ] || continue
        local path="${RELEASES_DIR}/$old"
        if [ -n "$current_target" ] && [ "$(readlink -f "$path" 2>/dev/null || echo "")" = "$current_target" ]; then
            log "Skipping current release $path (is active)"
            continue
        fi
        log "Removing old release $path"
        sudo rm -rf "$path"
    done
}

setup_storage() {
    log "setup_storage is handled via 'php artisan storage:link'."
}

reload_php_fpm() {
    if [ "${OPCACHE_RESET}" -ne 1 ]; then
        return 0
    fi
    log "Resetting OPcache by reloading PHP-FPM (best effort)..."

    local reloaded=0
    if command -v systemctl >/dev/null 2>&1; then
        for svc in php-fpm php8.5-fpm php8.4-fpm php8.3-fpm php8.2-fpm; do
            if systemctl is-active --quiet "$svc"; then
                if sudo systemctl reload "$svc" 2>/dev/null; then
                    log "Reloaded $svc"
                    reloaded=1
                    break
                fi
            fi
        done
    fi

    if [ "$reloaded" -ne 1 ]; then
        for pidfile in /run/php/php*-fpm.pid /var/run/php*-fpm.pid; do
            if [ -f "$pidfile" ]; then
                if sudo kill -USR2 "$(cat "$pidfile")" 2>/dev/null; then
                    log "Sent USR2 to PHP-FPM master (pidfile: $pidfile)"
                    reloaded=1
                    break
                fi
            fi
        done
    fi

    if command -v systemctl >/dev/null 2>&1; then
        for websvc in apache2 httpd; do
            if systemctl is-active --quiet "$websvc"; then
                sudo systemctl reload "$websvc" 2>/dev/null && log "Reloaded $websvc" || true
            fi
        done
    fi

    if [ "$reloaded" -eq 1 ]; then
        sleep "$OPCACHE_RELOAD_WAIT_SECONDS" || true
    else
        if [ "$RESTART_PHPFPM_ON_RELOAD_FAIL" -eq 1 ] && command -v systemctl >/dev/null 2>&1; then
            for svc in php-fpm php8.5-fpm php8.4-fpm php8.3-fpm php8.2-fpm; do
                if systemctl is-active --quiet "$svc"; then
                    if sudo systemctl restart "$svc" 2>/dev/null; then
                        log "Restarted $svc (reload failed)"; reloaded=1; break
                    fi
                fi
            done
        fi
        if [ "$reloaded" -ne 1 ]; then
            log "Could not automatically reload PHP-FPM. If you use OPcache with validate_timestamps=0, consider reloading your PHP-FPM service manually."
        fi
    fi
}

healthcheck_wait() {
    [ -n "$HEALTHCHECK_URL" ] || return 0
    if ! command -v curl >/dev/null 2>&1; then
        log "curl not found; skipping healthcheck."
        return 0
    fi
    log "Healthcheck: waiting for 200 from $HEALTHCHECK_URL (attempts=$HEALTHCHECK_ATTEMPTS delay=${HEALTHCHECK_DELAY}s)"
    local i rc
    for i in $(seq 1 "$HEALTHCHECK_ATTEMPTS"); do
        if curl -fsS -o /dev/null -m 5 "$HEALTHCHECK_URL"; then
            log "Healthcheck OK"
            return 0
        fi
        sleep "$HEALTHCHECK_DELAY" || true
    done
    log "WARNING: Healthcheck did not pass after $HEALTHCHECK_ATTEMPTS attempts. Proceeding."
}

ROLLBACK_COUNT=0
ROLLBACK_MIGRATIONS=0
while [ "$#" -gt 0 ]; do
    case "$1" in
        -rollback|--rollback)
            if [ -n "${2-}" ] && ! echo "${2-}" | grep -qE '^-' ; then
                ROLLBACK_COUNT="${2-}"
                shift 2
            else
                ROLLBACK_COUNT=1
                shift
            fi
            ;;
        -m|--migrate)
            ROLLBACK_MIGRATIONS=1
            shift
            ;;
        -h|--help)
            echo "Usage: deploy.sh [options]"
            echo "  -rollback [N]    Roll back N releases (default 1) and switch 'current' to that release"
            echo "  -m, --migrate    When used with -rollback, also run 'php artisan migrate:rollback' on the target release"
            echo "  -h, --help       Show this help"
            exit 0
            ;;
        *)
            shift
            ;;
    esac
done

perform_rollback() {
    local n="$1"
    if [ -z "$RELEASES_DIR" ] || [ ! -d "$RELEASES_DIR" ]; then
        fail "Releases directory not found: $RELEASES_DIR"
    fi

    log "Preparing to roll back $n release(s) in $RELEASES_DIR"
    releases=$(ls -1 "$RELEASES_DIR" | sort || true)
    total=$(echo "$releases" | grep -c '^[^ ]' || echo 0)
    if [ "$total" -le "$n" ]; then
        fail "Not enough releases to roll back $n (total: $total)"
    fi

    target_index=$((total - n))
    target_release=$(echo "$releases" | sed -n "${target_index}p")
    target_path="$RELEASES_DIR/$target_release"

    if [ ! -d "$target_path" ]; then
        fail "Target release not found: $target_path"
    fi

    log "Switching current to $target_path"
    sudo ln -sfn "$target_path" "$CURRENT_LINK"
    sudo chown -h "$APP_USER":"$APP_USER" "$CURRENT_LINK"

    log "Clearing and rebuilding caches on rolled-back release"
    (cd "$CURRENT_LINK" && sudo -u "$APP_USER" php artisan optimize:clear || true)
    (cd "$CURRENT_LINK" && sudo -u "$APP_USER" php artisan config:cache || true)
    (cd "$CURRENT_LINK" && sudo -u "$APP_USER" php artisan route:cache || true)
    (cd "$CURRENT_LINK" && sudo -u "$APP_USER" php artisan view:cache || true)

    reload_php_fpm

    if [ "$ROLLBACK_MIGRATIONS" -eq 1 ]; then
        log "Rolling back database migrations on the target release (explicitly requested)"
        (cd "$CURRENT_LINK" && sudo -u "$APP_USER" php artisan migrate:rollback --force --no-interaction || true)
    else
        log "Skipping database migration rollback (use -m to enable)."
    fi

    log "Rollback complete. Current release: $(readlink -f "$CURRENT_LINK" 2>/dev/null || echo "$CURRENT_LINK")"
}

require_cmd git
require_cmd rsync
require_cmd php
require_cmd composer
if ! command -v npm >/dev/null 2>&1; then
    log "npm not found; frontend build will be skipped."
    NPM_SKIP=1
else
    NPM_SKIP=0
fi

exec 9>"$LOCK_FILE" || fail "Cannot open lock file $LOCK_FILE"
if ! flock -n 9; then
    fail "Another deployment is in progress (lock: $LOCK_FILE)"
fi

if [ "${ROLLBACK_COUNT}" -gt 0 ]; then
    log "Rollback requested (count=${ROLLBACK_COUNT}). Skipping deploy steps."
    perform_rollback "${ROLLBACK_COUNT}"
    exit 0
fi

if [ ! -d "$REPO_DIR/.git" ]; then
    log "Cloning repository into $REPO_DIR..."
    sudo mkdir -p "$REPO_DIR"
    sudo chown -R "$COMPOSER_USER":"$COMPOSER_USER" "$REPO_DIR"
    git clone --origin origin "$REPO_URL" "$REPO_DIR"
fi

git -C "$REPO_DIR" config --global --add safe.directory "$REPO_DIR" || true
cd "$REPO_DIR" || fail "Cannot cd to $REPO_DIR"
sudo chown -R "$COMPOSER_USER":"$COMPOSER_USER" "$REPO_DIR"

log "Fetching branch $BRANCH..."
git fetch --all --prune

sudo mkdir -p "$SHARED_DIR"
if [ -f "$REPO_DIR/.env" ] && [ ! -f "$SHARED_DIR/.env" ]; then
    log "Protecting .env by moving it to $SHARED_DIR/.env before cleaning"
    sudo mv "$REPO_DIR/.env" "$SHARED_DIR/.env"
    sudo chown "$APP_USER":"$APP_USER" "$SHARED_DIR/.env" || true
    sudo chmod 640 "$SHARED_DIR/.env" || true
fi
if [ -f "$DEPLOY_DIR/.env" ] && [ ! -f "$SHARED_DIR/.env" ]; then
    log "Protecting deploy .env by moving it to $SHARED_DIR/.env before cleaning"
    sudo mv "$DEPLOY_DIR/.env" "$SHARED_DIR/.env"
    sudo chown "$APP_USER":"$APP_USER" "$SHARED_DIR/.env" || true
    sudo chmod 640 "$SHARED_DIR/.env" || true
fi

log "Discarding any local changes in $REPO_DIR so branch switch will succeed"
git reset --hard || true
REPO_REAL=$(readlink -f "$REPO_DIR" 2>/dev/null || echo "$REPO_DIR")
DEPLOY_REAL=$(readlink -f "$DEPLOY_DIR" 2>/dev/null || echo "$DEPLOY_DIR")
if [ "$REPO_REAL" = "$DEPLOY_REAL" ]; then
    log "Safety: REPO_DIR equals DEPLOY_DIR ($REPO_REAL); skipping 'git clean' to avoid removing app files"
else
    git clean -fdx -e .env -e ".env.*" || true
fi
git checkout -B "$BRANCH" "origin/$BRANCH" || true
git reset --hard "origin/$BRANCH" || true

prepare_shared
create_release
build_release
migrate_release
seed_first_deploy
switch_current
reload_php_fpm

log "Post-switch: ensuring Filament assets exist in current release..."
if (cd "$CURRENT_LINK" && sudo -u "$APP_USER" php artisan list | grep -q "^  filament:assets" ); then
    if (cd "$CURRENT_LINK" && sudo -u "$APP_USER" php artisan help filament:assets | grep -q -- "--force" ); then
        (cd "$CURRENT_LINK" && sudo -u "$APP_USER" php artisan filament:assets --no-interaction --force || true)
    else
        (cd "$CURRENT_LINK" && sudo -u "$APP_USER" php artisan filament:assets --no-interaction || true)
    fi
else
    (cd "$CURRENT_LINK" && sudo -u "$APP_USER" php artisan vendor:publish --tag=filament-assets --force || true)
fi
sudo chown -R "$APP_USER":"$APP_USER" "$CURRENT_LINK/public" || true
sudo find "$CURRENT_LINK/public" -type d -exec chmod 775 {} + || true
sudo find "$CURRENT_LINK/public" -type f -exec chmod 664 {} + || true

healthcheck_wait

sudo chown -R "$APP_USER":"$APP_USER" "$DEPLOY_DIR"
sudo find "$DEPLOY_DIR" -type d -exec chmod 775 {} +
sudo find "$DEPLOY_DIR" -type f -exec chmod 664 {} +

cleanup_releases

log "Deploy complete. Current release: $(readlink -f "$CURRENT_LINK" 2>/dev/null || echo "$CURRENT_LINK")"
