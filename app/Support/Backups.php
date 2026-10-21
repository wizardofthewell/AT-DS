<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * Creates and manages zip backups of the database and uploaded files.
 *
 * Each zip holds:
 *  - database/<table>.json  every table as JSON (works with any database driver)
 *  - database.sqlite        a ready-to-use copy of the database, when using SQLite
 *  - uploads/...            everything on the public disk (images, etc.)
 *  - manifest.json          when and what was backed up
 *
 * Backups live on the private "local" disk, never in the public web folder.
 */
class Backups
{
    public const DIRECTORY = 'backups';

    /**
     * Backups older than this many days are pruned...
     */
    public const KEEP_DAYS = 30;

    /**
     * ...but the newest few are always kept, however old.
     */
    public const KEEP_AT_LEAST = 7;

    /**
     * Short-lived or sensitive tables left out of the JSON export.
     *
     * @var list<string>
     */
    public const SKIPPED_TABLES = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'password_reset_tokens'];

    public function disk(): Filesystem
    {
        return Storage::disk('local');
    }

    /**
     * @return string The new backup's file name.
     */
    public function create(): string
    {
        $name = 'backup-'.now()->format('Y-m-d-His').'.zip';
        $path = $this->disk()->path(static::DIRECTORY.'/'.$name);
        $this->disk()->makeDirectory(static::DIRECTORY);

        $temporaryFiles = [];

        try {
            $zip = new ZipArchive;

            if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException("Could not create {$path}");
            }

            $rowCounts = $this->addDatabaseTables($zip);

            if ($snapshot = $this->sqliteSnapshot()) {
                $temporaryFiles[] = $snapshot;
                $zip->addFile($snapshot, 'database.sqlite');
            }

            $uploadCount = $this->addUploads($zip);

            $zip->addFromString('manifest.json', json_encode([
                'created_at' => now()->toIso8601String(),
                'app' => config('app.name'),
                'url' => config('app.url'),
                'laravel' => app()->version(),
                'database_driver' => DB::connection()->getDriverName(),
                'tables' => $rowCounts,
                'uploads' => $uploadCount,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            if (! $zip->close()) {
                throw new RuntimeException('Could not finish writing the backup zip.');
            }
        } catch (Throwable $e) {
            @unlink($path);

            Audit::record('backup_failed', description: Str::limit($e->getMessage(), 240));

            throw $e;
        } finally {
            foreach ($temporaryFiles as $file) {
                @unlink($file);
            }
        }

        Audit::record('backup_created', description: $name.' ('.static::formatBytes(filesize($path)).')');

        return $name;
    }

    /**
     * @return Collection<string, array{name: string, size: int, created_at: Carbon}>
     */
    public function all(): Collection
    {
        return collect($this->disk()->files(static::DIRECTORY))
            ->filter(fn (string $file): bool => str_ends_with($file, '.zip'))
            ->map(fn (string $file): array => [
                'name' => basename($file),
                'size' => $this->disk()->size($file),
                'created_at' => Carbon::createFromTimestamp($this->disk()->lastModified($file)),
            ])
            ->sortByDesc(fn (array $backup): string => $backup['name'])
            ->keyBy('name');
    }

    public function latest(): ?array
    {
        return $this->all()->first();
    }

    public function path(string $name): string
    {
        return $this->disk()->path($this->relativePath($name));
    }

    public function exists(string $name): bool
    {
        return $this->disk()->exists($this->relativePath($name));
    }

    public function delete(string $name): void
    {
        $this->disk()->delete($this->relativePath($name));

        Audit::record('backup_deleted', description: $name);
    }

    /**
     * Removes old backups, always keeping the newest few.
     *
     * @return int How many were removed.
     */
    public function prune(int $keepDays = self::KEEP_DAYS, int $keepAtLeast = self::KEEP_AT_LEAST): int
    {
        $cutoff = now()->subDays($keepDays);

        $old = $this->all()
            ->skip($keepAtLeast)
            ->filter(fn (array $backup): bool => $backup['created_at']->lt($cutoff));

        foreach ($old as $backup) {
            $this->disk()->delete($this->relativePath($backup['name']));
        }

        return $old->count();
    }

    public static function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? min((int) floor(log($bytes, 1024)), count($units) - 1) : 0;

        return round($bytes / (1024 ** $power), $power > 1 ? 1 : 0).' '.$units[$power];
    }

    /**
     * Only plain backup names are accepted, so a crafted name cannot reach other files.
     */
    protected function relativePath(string $name): string
    {
        if (! preg_match('/^backup-[\d-]+\.zip$/', $name)) {
            throw new InvalidArgumentException("Invalid backup name: {$name}");
        }

        return static::DIRECTORY.'/'.$name;
    }

    /**
     * @return array<string, int> Row count per table.
     */
    protected function addDatabaseTables(ZipArchive $zip): array
    {
        $counts = [];

        $tables = collect(Schema::getTableListing(schemaQualified: false))
            ->reject(fn (string $table): bool => in_array($table, static::SKIPPED_TABLES, true) || str_starts_with($table, 'sqlite_'))
            ->sort()
            ->values();

        foreach ($tables as $table) {
            $rows = DB::table($table)->get()->map(fn (object $row): array => (array) $row)->all();
            $counts[$table] = count($rows);

            $zip->addFromString("database/{$table}.json", json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
        }

        return $counts;
    }

    /**
     * A consistent copy of a file-based SQLite database, or null for other setups.
     */
    protected function sqliteSnapshot(): ?string
    {
        $connection = DB::connection();
        $database = $connection->getConfig('database');

        if ($connection->getDriverName() !== 'sqlite' || ! $database || $database === ':memory:' || ! is_file($database)) {
            return null;
        }

        $snapshot = storage_path('app/backup-'.Str::random(12).'.sqlite');

        if ($connection->transactionLevel() === 0) {
            // VACUUM INTO writes a clean, consistent copy even while the site is in use.
            $connection->statement('VACUUM INTO '.$connection->getPdo()->quote($snapshot));
        } else {
            copy($database, $snapshot);
        }

        return $snapshot;
    }

    protected function addUploads(ZipArchive $zip): int
    {
        $public = Storage::disk('public');
        $files = $public->allFiles();

        foreach ($files as $file) {
            if (basename($file) === '.gitignore') {
                continue;
            }

            $zip->addFile($public->path($file), 'uploads/'.$file);
        }

        return count($files);
    }
}
