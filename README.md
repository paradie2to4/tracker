# ProductSphere — Product Traceability Management System

ProductSphere is a web application for registering products, recording their
manufacturing batches, and monitoring stock and expiry. It is the foundation
of a supply-chain traceability platform designed to be adapted to
businesses operating in Rwanda.

The current release is the **MVP**: authentication, product management,
batch management and an expiry-aware dashboard. Supply-chain movements are
planned for the next phase (see [Roadmap](#roadmap)).

## Features

**Authentication and access**
- Session-based login and logout (Laravel Fortify), with passwords hashed by bcrypt.
- Login throttling: 5 attempts per minute per email and IP address.
- No public registration. Accounts are created by an administrator from the command line.
- Two roles. **Staff** can view, register and edit products and batches. **Administrators** can also activate/deactivate products and recall batches.

**Dashboard**
- Total products, total batches, expired batches and batches approaching expiry, all calculated from live database records.
- A list of active batches expiring within the warning window (30 days by default).

**Products**
- Paginated list with search by name or code, and filters by category and status.
- Register, view and edit products. Product codes are unique and case-insensitive.
- A product's code becomes read-only once batches exist for it.
- Products are **deactivated, never deleted**. The database refuses to delete a product that has batches.

**Batches**
- Paginated list with search by batch number, and filters by product and status.
- Register batches for active products only. Batch numbers are unique.
- Validation: the manufacturing date cannot be in the future, the expiry date cannot precede manufacturing, and quantities must be positive with at most three decimals.
- The product and batch number are immutable after creation.
- Administrators can recall a batch with a mandatory reason. Recalled batches are frozen.

## Batch status rules

A batch's status is **derived, never stored**, so it can never become stale.

| Status | Condition | Precedence |
|---|---|---|
| Recalled | An administrator recorded a recall | 1 (highest) |
| Depleted | Current quantity is zero | 2 |
| Expired | Expiry date is before today | 3 |
| Active | None of the above | 4 |

A batch is still active **on** its expiry date and is expired from the next
day. "Today" uses the application time zone (`Africa/Kigali`). A recall
overrides everything because it is safety-critical. Depleted takes precedence
over expired because an empty batch has no stock left to sell after expiry.
The same rules exist in PHP (`Batch::status`) and in SQL (`Batch::scopeWithStatus`),
so lists can be filtered and paginated in the database.

## Technology stack

- PHP 8.4, Laravel 13
- PostgreSQL 17
- Laravel Fortify (authentication backend) with custom Blade views
- Blade templates, Tailwind CSS 4, Vite
- PHPUnit 12

## Data model

```
users ──< batches.recalled_by        (nullable, ON DELETE SET NULL)
products ──< batches.product_id      (required, ON DELETE RESTRICT)
```

| Table | Key columns and constraints |
|---|---|
| `users` | Laravel defaults + `role` (`admin` / `staff`, CHECK constraint) |
| `products` | `product_code` UNIQUE, `name`, `description`, `category`, `manufacturer_name`, `unit_of_measure`, `is_active` |
| `batches` | `batch_number` UNIQUE, `manufacturing_date`, `expiry_date` (nullable), `initial_quantity` / `current_quantity` as `NUMERIC(14,3)`, `recalled_at`, `recall_reason`, `recalled_by` |

PostgreSQL CHECK constraints enforce, independently of the application:
initial quantity > 0, 0 ≤ current quantity ≤ initial quantity,
expiry date ≥ manufacturing date, and a recall always has a reason.
Quantities use exact `NUMERIC`, never floating point.

## Prerequisites

- PHP 8.3+ with the `pdo_pgsql`, `pgsql`, `mbstring`, `openssl` and `fileinfo` extensions
- Composer 2
- Node.js 20+ and npm
- PostgreSQL 15+
- Git

On Windows, the simplest setup is [php.new](https://php.new), which installs
PHP, Composer and the Laravel installer.

## Installation

```bash
git clone https://github.com/paradie2to4/tracker.git
cd tracker
composer install
npm install
cp .env.example .env        # PowerShell: Copy-Item .env.example .env
php artisan key:generate
```

### Database setup

Create a dedicated database user and two databases: one for development and
one for the automated tests. Run this as the `postgres` superuser.
`\password` prompts for the new password without echoing it.

```bash
psql -U postgres -h 127.0.0.1 -c "CREATE ROLE producttrace WITH LOGIN;" -c "\password producttrace" -c "CREATE DATABASE producttrace OWNER producttrace ENCODING 'UTF8' TEMPLATE template0;" -c "CREATE DATABASE producttrace_test OWNER producttrace ENCODING 'UTF8' TEMPLATE template0;"
```

### Environment configuration

Edit `.env` (never commit it):

| Variable | Purpose |
|---|---|
| `DB_CONNECTION=pgsql` | Use PostgreSQL |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Development database credentials |
| `APP_TIMEZONE=Africa/Kigali` | Time zone used to decide what "today" means for expiry |
| `EXPIRY_WARNING_DAYS=30` | Optional. Size of the approaching-expiry window |

### Run the migrations

```bash
php artisan migrate
```

Optionally load demo data (local environment only):

```bash
php artisan db:seed
```

The seeder creates `admin@productsphere.test` and `staff@productsphere.test`,
both with the password `password`, plus sample products and batches in every
status. These accounts exist only for local development. The seeder refuses
to run unless `APP_ENV=local`.

### Create a real user account

```bash
php artisan app:create-user --admin
```

Omit `--admin` to choose the role interactively. The password is entered at
a hidden prompt.

## Running the application

```bash
npm run build
php artisan serve
```

Open http://localhost:8000. During front-end development, run `npm run dev`
in a second terminal for hot reloading instead of `npm run build`.

## Running the tests

```bash
php artisan test
```

The tests run against the separate `producttrace_test` database (forced in
`phpunit.xml`), so `RefreshDatabase` never touches development data. They use
PostgreSQL rather than SQLite so that the CHECK constraints and
case-insensitive `ILIKE` search behave exactly as in production.

Coverage includes authentication and route protection, rate limiting,
dashboard statistics, product and batch validation (duplicates, dates,
negative and over-precise quantities), search and filters, role-based
permissions, recall rules, status derivation and direct database
constraint checks.

> Avoid `php artisan config:cache` during development. A cached
> configuration would bypass the test database override in `phpunit.xml`.

## Deployment (Render + Neon)

Production runs as a Docker web service on [Render](https://render.com),
backed by a [Neon](https://neon.tech) serverless PostgreSQL database.

| File | Purpose |
|---|---|
| `Dockerfile` | Multi-stage build: Composer (no dev packages), Node/Vite assets, then PHP 8.4 + Apache with `pdo_pgsql` and OPcache |
| `docker/start.sh` | Runs on every container start: `php artisan optimize`, then `php artisan migrate --force`, then Apache |
| `docker/apache.conf`, `docker/php.ini` | Web server (listens on Render's `$PORT`, security headers) and production PHP settings |
| `render.yaml` | Render Blueprint describing the service and its environment variables |

1. **Neon:** create a project in region *AWS Europe Central 1 (Frankfurt)*.
   Copy the **direct** (non-pooled) connection string, which ends in `?sslmode=require`.
2. **App key:** run `php artisan key:generate --show` locally and copy the output.
3. **Render:** choose *New → Blueprint*, select this repository, then fill in the
   secret values when prompted:
   - `DB_URL`: the Neon connection string
   - `APP_KEY`: the key from step 2
   - `APP_URL`: the service URL, for example `https://productsphere.onrender.com`
     (update it after the first deploy if Render assigns a different name)
4. Render builds the image, the container runs the migrations against Neon, and
   `/up` is used as the health check. Every push to `main` deploys automatically.
5. **First administrator:** the free plan has no shell access, so create the account
   from your own machine, pointed at Neon. `DB_URL` overrides the local
   `DB_*` settings for that single PowerShell session:

   ```bash
   $env:DB_URL = "<neon connection string>"; php artisan app:create-user --admin; Remove-Item Env:DB_URL
   ```

Notes:
- The free Render plan sleeps after 15 minutes of inactivity. The first request after that takes up to about a minute.
- Do not run `php artisan db:seed` against production. The seeder refuses to run outside `APP_ENV=local`.
- Vercel is not used: the app is a server-rendered Laravel monolith with no separate front end to host.

## Current limitations

- Single organisation: all users see all records. Organisation-level access arrives with Phase 6/7.
- `current_quantity` is edited manually. It will become read-only once stock movements exist.
- No password reset or profile page yet (needs a configured mailer).
- No user-management screen; users are created with `app:create-user`.
- A recall is irreversible and has no workflow (notifications, affected shipments).
- No audit trail of edits yet.

## Roadmap

- **Phase 6 — Traceability:** supply-chain organisations and locations, shipment and receipt events, product movement history, transaction-safe stock movements, an append-only audit trail, and rules against invalid transitions.
- **Phase 7 — Advanced:** QR codes and public product verification, recall workflows and affected-batch reports, role-based organisational access, a documented REST API with authentication and rate limiting, Docker and CI/CD, and optional GS1/EPCIS-based interoperability.
