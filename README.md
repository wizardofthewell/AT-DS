# Harbourstone Postpartum Support

*Strong Foundations for New Beginnings*

Laravel 13 + Filament 5 site for Aubri Porter's postpartum support practice in Victoria, BC. The public site is built from
editable page sections, so nearly everything can be changed from the admin at `/admin`
without touching code.

## Local setup

```bash
composer install
npm install
cp .env.example .env        # then set ADMIN_EMAIL / ADMIN_PASSWORD
php artisan key:generate
php artisan migrate --seed  # creates the admin user + starter content
php artisan storage:link
npm run build               # or `npm run dev` while working on styles
php artisan serve
```

If `ADMIN_PASSWORD` is empty, the seeder generates one and prints it. The starter content
seeder only creates missing records, so re-running it never overwrites admin edits.

Run the tests with `php artisan test`.

## What Aubri can edit (admin)

| Menu | What it controls |
| --- | --- |
| **Inquiries** | Contact-form messages, with status (New → Contacted → Booked → Closed) and private notes. New ones are also emailed. |
| **Website → Pages** | Every page, built from drag-and-drop sections (see below). Also menu visibility and SEO text. The page with address `home` is the homepage. |
| **Website → Services** | Each service has its own page at `/services/{slug}`, plus a card and spotlight used in sections. |
| **Website → Testimonials** | Reviews, optionally linked to a service so they appear with it. |
| **Website → FAQs** | Questions with optional categories (sections can show one category). |
| **Website → Blog** | Posts with cover images; future publish dates are scheduled automatically. |
| **Settings → Site Settings** | Business name, motto, header name/logo/favicon, colour presets + custom colours, fonts, button shape, announcement bar, main menu (with dropdowns), contact details, social links, footer, SEO and Google Analytics. |
| **Settings → Admin Users** | Logins for the admin. |

### Page sections

Hero banner · Section heading · Text · Image + text · Service cards (grid or carousel) ·
Service spotlight (with a testimonial) · Icon feature list · Process steps · Packages/pricing ·
Single testimonial · Testimonials carousel · FAQ · Photo gallery · Call-to-action banner ·
Contact form · Latest blog posts · Divider · Custom HTML/embed.

Most sections have an **Appearance** panel for background tone (page / white / soft / accent /
dark) and an anchor ID for `/#links`.

## Code map

- `app/Cms/PageBlocks.php` — section definitions for the page builder (admin form fields).
- `resources/views/cms/blocks/{type}.blade.php` — how each section renders.
- `app/Support/Theme.php` — colour presets, font list, and the CSS variables written into `<head>`.
- `app/Support/Site.php` — `$site` helper shared with every view (settings, menu, rich text, images).
- `resources/css/app.css` — Tailwind 4 theme tokens mapped to those CSS variables.
- `app/Filament/` — admin resources, the Site Settings page and dashboard widgets.

To add a new section type: add a `static::block(...)` entry in `PageBlocks::all()` and a matching
view in `resources/views/cms/blocks/`.

## Before going live

- Replace placeholder copy, prices, the sample testimonials, contact details and social links.
- Set real mail settings in `.env` (`MAIL_MAILER`, etc.) so inquiry emails are delivered; locally they go to `storage/logs/laravel.log`.
- Set `APP_ENV=production`, `APP_DEBUG=false` and `APP_URL`.
