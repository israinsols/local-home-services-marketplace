# Local Home Services — UK & US Home Services Marketplace

Local Home Services connects homeowners with local home-service businesses. Homeowners submit a job request at no charge. Approved, email-verified providers are matched by service and city, receive a contact-safe email preview, and spend credits to unlock the homeowner's contact details.

## Stack and prerequisites

- PHP 8.2 or newer and Composer 2
- Laravel 11, Blade, Tailwind CSS, Vite, and Filament 3
- Node.js 20+ and npm
- SQLite for local development; MySQL 8+ recommended for production
- PHP extensions: `ctype`, `curl`, `fileinfo`, `intl`, `mbstring`, `openssl`, `pdo`, `pdo_sqlite` or `pdo_mysql`, `tokenizer`, and `xml`

Filament uses PHP's `intl` extension for number formatting. Enable it in the PHP configuration used by both the web server and CLI. On Windows/XAMPP, uncomment `extension=intl` in the active `php.ini`, restart the web server, and confirm `php -m` lists `intl`.

## Local setup

From the project directory:

```sh
cp .env.example .env
composer install
npm ci
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`. The seeded local catalog contains eight services, ten UK cities, five US cities, two clearly labelled demo listings for every active service/city pair, demo leads, and the legal pages. Local demo provider accounts use `password`; change or remove these fixtures before exposing a non-test environment.

Create a local administrator without committing credentials:

```sh
php artisan tinker
```

Then in Tinker:

```php
\App\Models\User::updateOrCreate(
    ['email' => 'admin@example.test'],
    ['name' => 'Marketplace Admin', 'password' => 'replace-with-a-strong-password', 'role' => 'admin', 'email_verified_at' => now()],
);
```

Visit `/admin`. Only users with the `admin` role can access the Filament panel. In production, the seeder creates an administrator only when both `ADMIN_EMAIL` and `ADMIN_PASSWORD` are explicitly set; use a strong unique password and do not put production credentials in source control.

## Environment configuration

The starter `.env.example` uses SQLite, database sessions/cache/queues, and the log mailer. Important settings:

| Variable | Purpose |
| --- | --- |
| `APP_ENV`, `APP_DEBUG`, `APP_URL`, `APP_KEY` | Environment, error exposure, canonical site origin, and encryption key. Production must use HTTPS and `APP_DEBUG=false`. |
| `DB_CONNECTION` and `DB_*` | Use `sqlite` locally or `mysql` with the production database credentials. |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | Defaults to `database`; the required tables are included in migrations. |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS` | Local mail is written to Laravel logs. Configure a real SMTP account for verification and lead notifications in production. |
| `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` | Stripe secret API key and signing secret for the Checkout webhook. Keep these out of logs and source control. |
| `MARKETPLACE_NAME`, `SITE_CONTACT_EMAIL`, `LEAD_CONSENT_VERSION` | Branding, privacy contact, and the version recorded with homeowner consent. |
| `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Optional first production administrator, used by the production seeder. |

For MySQL, set `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`; create the database and grant the app user only the required privileges. Never run `migrate:fresh` against production.

## Mail and queues

Locally, verification and lead mail are logged to `storage/logs/laravel.log`. In production, configure SMTP and run a persistent queue worker:

```sh
php artisan queue:work database --queue=default --tries=3 --timeout=90
```

Use Supervisor or the hosting platform's process manager to restart workers after deployment:

```ini
[program:omnilead-queue]
command=php /var/www/omnilead/artisan queue:work database --queue=default --tries=3 --timeout=90
directory=/var/www/omnilead
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/omnilead-queue.log
```

The scheduler prunes failed queue records older than seven days. Add Laravel's scheduler once per minute:

```cron
* * * * * cd /var/www/omnilead && php artisan schedule:run >> /dev/null 2>&1
```

On shared hosting without a process manager, configure a once-per-minute cron job to run `php artisan queue:work --stop-when-empty --tries=3 --timeout=90`. A persistent worker is preferable; queued email will not be delivered until a worker is running.

## Stripe credit checkout

1. Create a Stripe account and use **test-mode** API credentials during development.
2. Set `STRIPE_SECRET=sk_test_...`.
3. Forward Stripe's `checkout.session.completed` event to the local webhook, for example with Stripe CLI:

   ```sh
   stripe listen --forward-to http://127.0.0.1:8000/stripe/webhook
   ```

4. Copy the CLI's `whsec_...` signing secret into `STRIPE_WEBHOOK_SECRET`.
5. In production, register `https://your-domain.example/stripe/webhook` in Stripe and configure its signing secret separately.

The browser return URL never grants credits. A signature-verified webhook checks payment status, amount, currency, provider, and credit count before applying an idempotent ledger transaction. The starter packs are defined in `config/marketplace.php` (`credits` and `price_cents`); adjust those prices and have local counsel review the commercial terms before launch. Stripe processing fees are not free.

## Admin and catalog operations

The Filament panel at `/admin` manages service categories, cities, provider applications, leads, credit transactions, review moderation, blog posts, legal/content pages, and site settings. Admin actions can approve/reject/suspend providers and add audited provider credits. Approval requires a verified email.

The city CSV importer expects exactly these headers:

```csv
country_code,name,region,postal_prefix,population
GB,Reading,Berkshire,RG1,174000
US,Austin,Texas,78701,974000
```

Import runs transactionally and rejects invalid rows. Where a city name would collide across countries, its public slug receives a country suffix (for example `london-us`) so `/{city}` remains unambiguous. Keep public slugs unique when adding cities, categories, or pages.

## SEO, performance, and privacy behavior

- Service/city pages use stable, varied introductory copy, FAQs, local links, structured `Service`/`FAQPage` data, and noindex empty provider combinations.
- Category and city listings are noindex when they have no approved providers. Provider profiles without a description, service, or coverage areas are noindex. Search results are noindex and canonicalize to `/find`.
- Public provider pages add `LocalBusiness` structured data and `AggregateRating` when approved reviews exist. Public pages provide canonical, OpenGraph/Twitter, hreflang, breadcrumb schema, and pagination `rel` links.
- `/sitemap.xml` is a dynamic sitemap index. Its category/provider/blog/page sections and UK/US city/service-city sections include only published or supported public content and split into files with no more than 50,000 URLs. `/robots.txt` links to the index and excludes admin, provider, and search routes.
- Sitemap output and site settings are cached. Static assets receive expiry headers and Apache gzip compression where the relevant modules are enabled. Public HTML is not shared-cacheable because it contains session-bound CSRF tokens; do not put these responses in a shared CDN cache without separating dynamic forms or applying safe per-session variation.
- The cookie banner offers accept/reject choices; only essential cookies are currently active. Do not activate analytics or advertising tags before implementing the appropriate consent flow.
- Provider accounts can export their account/profile/credit data as JSON and remove the public listing and login from the profile page. The erasure flow anonymizes the provider record so required accounting/credit history can be retained. Homeowners can contact the published privacy email to request access or deletion; identity verification may be required.
- Homeowner contact data is stored with consent time/version, is omitted from provider email previews, and is disclosed to a matched provider only after a successful unlock. This is not a substitute for documenting the operator's final lawful basis, retention schedule, processor agreements, international-transfer safeguards, and jurisdiction-specific privacy notices.

Legal page copy is starter content, not legal advice. Before launch, replace the example contact email, review the Privacy Policy/Terms/Cookie Policy for the real business, retention rules, and UK/US obligations, and verify your cookie and marketing practices.

## Tests and builds

With `intl` enabled in CLI PHP:

```sh
php artisan test
npm run build
```

The feature suite covers homeowner lead validation/consent, provider matching and contact-safe notices, idempotent credit unlocks, admin permissions/approval/CSV/credit workflows, verified provider registration, privacy export/erasure, legal/cookie pages, search filtering, and sitemap output including the 50,000-URL boundary.

## Deployment on shared hosting or a small VPS

1. Provision PHP 8.2+, `intl`, `pdo_mysql` (or SQLite for a single-node low-volume launch), Composer, and Node in CI/build tooling. Prefer MySQL for production.
2. Deploy source and built `public/build` assets. Set production `.env` values (especially `APP_KEY`, HTTPS `APP_URL`, SMTP, DB, Stripe, and `APP_DEBUG=false`) outside the web root and never commit `.env`.
3. Point the web server document root to `public/`; do not serve the repository root or expose `.env`, `vendor`, or `storage`.
4. Install dependencies and build assets:

   ```sh
   composer install --no-dev --optimize-autoloader --no-interaction
   npm ci
   npm run build
   ```

5. Run `php artisan migrate --force`, then seed catalog/legal content and create the initial production admin with explicit secure `ADMIN_EMAIL` and `ADMIN_PASSWORD` values. Remove the bootstrap password from the deployment environment after confirming the admin can sign in.
6. Run `php artisan storage:link`, ensure `storage` and `bootstrap/cache` are writable by the PHP-FPM user, and enable HTTPS.
7. Cache configuration/routes/views and restart queue workers:

   ```sh
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan queue:restart
   ```

8. Configure a persistent queue worker, the scheduler cron above, SMTP delivery, Stripe's webhook, database backups, log rotation, monitoring, and a tested restore procedure.

For Apache, retain Laravel's `public/.htaccess` and enable rewrite/deflate/expires modules. For Nginx, set the document root to `.../public`, route missing paths to `index.php`, enable gzip, and configure PHP-FPM; `.htaccess` is not read by Nginx. Use the hosting provider's process manager for workers, not an interactive shell.

## Launch small, then expand

1. Start with one city and three well-supported services. In `/admin`, disable unlaunched categories/cities, set a sensible lead-credit price for each category, and approve only providers whose email and service coverage have been checked.
2. Create complete, locally useful service/city pages and provider profiles before indexing the area. Test postcode/ZIP validation, lead consent, notification delivery, matching limits, provider unlocking, and both GBP/USD checkout/webhook flows in Stripe test mode.
3. Run a real end-to-end lead through the queue worker, confirm the preview contains no homeowner contact details, and verify that contact data appears only after the credit ledger and unlock record are committed.
4. Launch with a small provider cohort; monitor lead quality, credits, refunds/support needs, email deliverability, and search indexing. Add one city/service at a time when supply and useful page content are ready.
5. Import additional cities using CSV, resolve any slug conflicts, attach active providers to accurate service areas, review local copy and currency/pricing, and inspect the generated sitemap before expanding the index.

## Security and dependency maintenance

Run `composer audit` and `npm audit` during release preparation, update dependencies on a regular schedule, keep production debug output disabled, and rotate exposed keys immediately. The requested Laravel 11 branch currently produces five Composer advisory findings and the advisory database contains no patched Laravel 11 release. A patched Laravel release requires a framework major upgrade and aligned development dependencies; do not suppress these findings or consider this Laravel 11 lockfile cleared for public production until that upgrade has been approved, implemented, and fully retested.
