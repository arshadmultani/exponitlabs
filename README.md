# Exponit Labs

The web platform for **Exponit Labs**, a focused pharmaceutical company (Mumbai, India)
working across pain management, gastro care, antibiotics and allergy treatment.

It is a single Laravel application that serves four things from one codebase:

1. **Public marketing website** — a modern, SEO/AEO‑optimised, server‑rendered site at `/`.
2. **Admin console (CMS)** — a Filament panel at `/console` to manage all content.
3. **Augmented‑reality viewer** — image‑tracked AR creatives (MindAR) reached via printed QR codes.
4. **Doctor microsites** — shareable per‑doctor pages at `/dr/{slug}`.

---

## Features

### Public marketing site (`/`)
- Multi‑page: Home, About, Products (with per‑area filtering and product detail pages),
  News (with article detail pages), Contact, plus Privacy/Terms.
- Content is CMS‑driven (therapeutic areas, products, news) — see the console below.
- Contact form persists submissions, fires a Discord alert, and is spam‑protected with
  a honeypot (`spatie/laravel-honeypot`).
- **SEO/AEO built in:**
  - Per‑page `<title>`/meta/canonical/OpenGraph/Twitter, `robots` meta.
  - JSON‑LD structured data via `spatie/schema-org` (`App\Support\Seo`): Organization +
    WebSite sitewide, Product + Breadcrumb on product pages, NewsArticle on articles,
    FAQPage on the home page.
  - `GET /sitemap.xml` (generated from active products + published news + static pages),
    `public/robots.txt`, and `public/llms.txt` for AI answer engines.
  - Self‑hosted fonts (no CDNs) and Google Analytics 4 (gated on `GA_MEASUREMENT_ID`).

### Admin console (`/console`)
A Filament v5 panel grouped under **Website** plus the AR/doctor tooling:
- **Therapeutic Areas**, **Products**, **News Posts** — full CMS for the public site.
- **Contact Submissions** — read‑only inbox with an unhandled‑count badge.
- **AR Creatives** — manage image‑tracked AR experiences.
- **Doctors** & **Microsites** — manage doctor profiles and their public microsites.

### Augmented reality (`/ar/{creative}`)
MindAR image tracking. The `.mind` tracking file is compiled **in the browser** (no
external service) and posted back to the app; a printed QR code points patients/HCPs to
the public viewer. Only published creatives are visible. QR codes are generated with
`endroid/qr-code`.

### Doctor microsites (`/dr/{slug}`)
Standalone, shareable pages per doctor (profile, showcases, reviews, contact), driven by
the Microsite/Doctor/Showcase/Review models and editable in the console.

---

## Tech stack

| Area | Choice |
| --- | --- |
| Framework | Laravel 13 (PHP 8.3+) |
| Admin / CMS | Filament 5, Livewire 4 |
| Frontend | Blade + Tailwind CSS v4, Alpine.js (bundled), Vite 8 |
| Database | **SQLite** (local and production) |
| AR | MindAR + Three.js |
| Icons | Blade Heroicons + Bootstrap Icons (`davidhsianturi/blade-bootstrap-icons`) |
| Notable packages | `spatie/schema-org`, `spatie/laravel-honeypot`, `spatie/laravel-discord-alerts`, `endroid/qr-code` |
| Testing | Pest 4 |
| Deployment | Deployer 8 (`deploy.php`) |

> **No external CDNs in production.** Alpine is bundled via Vite; fonts (Sora, DM Sans,
> Funnel Sans) are self‑hosted through the `laravel-vite-plugin` Bunny fonts integration.

---

## Local development

Requirements: PHP 8.3+, Composer, Node 20+, SQLite.

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database (SQLite) + demo content
touch database/database.sqlite
php artisan migrate --seed       # seeds therapeutic areas, products, news

# 4. Storage symlink (uploaded images, QR, AR assets)
php artisan storage:link

# 5. Run
composer run dev                 # or: php artisan serve + npm run dev
```

The site is then at `/`, the admin console at `/console`.
Create an admin user with `php artisan tinker` (e.g. `User::factory()->create([...])`).

### Useful commands
```bash
npm run dev          # Vite dev server (HMR)
npm run build        # production assets
php artisan test     # Pest test suite
./vendor/bin/pint    # code style (Laravel Pint)
```

---

## Configuration

Set in `.env`:

| Key | Purpose |
| --- | --- |
| `DB_DATABASE` | Absolute path to the SQLite file. In production, point this at a **persistent/shared** location so deploys don't reset it. |
| `GA_MEASUREMENT_ID` | Google Analytics 4 ID. Leave blank to disable the gtag snippet. |
| `DISCORD_ALERT_WEBHOOK` | Discord webhook for contact‑form notifications. |
| `DISCORD_ALERT_QUEUE_CONNECTION` | Set to `sync` (no queue worker in production). |
| `HONEYPOT_ENABLED` | Toggles contact‑form spam protection (default on). |

---

## Testing

Pest feature tests cover the public routes, contact submission + validation + honeypot,
sitemap, structured data, and the AR / microsite flows.

```bash
php artisan test
```

Tests run on an in‑memory SQLite database (see `phpunit.xml`).

---

## Deployment

Deploys run through **Deployer** (`deploy.php`) over SSH to the cPanel host. Assets are
built locally (`npm ci && npm run build`) and shipped; `.env`, `version.txt` and
`storage/` are shared across releases, and the app version auto‑bumps on each deploy.

```bash
dep deploy
```

> **SQLite note:** the database is a single file. Ensure production `DB_DATABASE` points
> to a path **outside the per‑release directory** (e.g. under shared `storage/`), or each
> deploy will start from an empty database. To copy production content down for local
> work, snapshot the production SQLite file (e.g. `sqlite3 … ".backup …"`) and the shared
> `storage/app/public` media — production is the source of truth; never push local → prod.

The production runtime is **PHP‑only** (no Node, Redis, or queue worker): build steps and
asset compilation happen locally before deploy, and queue‑backed work runs synchronously.

---

## Project structure

```
app/
  Filament/Resources/        # console: Products, TherapeuticAreas, NewsPosts,
                             #          ContactSubmissions, ArCreatives, Doctors, Microsites
  Http/Controllers/          # PageController (marketing), ArController, MicrositeController
  Models/                    # Product, TherapeuticArea, NewsPost, ContactSubmission,
                             # ArCreative, Doctor, Microsite, Showcase, Review
  Support/Seo.php            # JSON-LD / structured-data builder
resources/
  views/pages/               # public page templates
  views/components/site/     # marketing section components (hero, header, footer, faq, …)
  views/components/layouts/  # public layout
  views/microsite/           # doctor microsite
  css/ js/                   # Tailwind + Alpine entrypoints
routes/web.php               # all public + AR + microsite routes
deploy.php                   # Deployer recipe
```

---

## License

Proprietary — © Exponit Labs. All rights reserved.
