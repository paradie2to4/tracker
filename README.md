# ProductSphere — Product Traceability Management System

ProductSphere is a web application for registering products, recording their
manufacturing batches, and monitoring stock and expiry. It is the foundation
of a supply-chain traceability platform designed to be adapted to
businesses operating in Rwanda.

The current release covers the MVP (products, batches, expiry dashboard)
and **Phase 6, traceability**: supply-chain organisations and locations,
shipments between them, a transaction-safe stock ledger and an append-only
audit trail.

## Features

**Public site**
- A landing page explaining the product, with live platform figures, then sign-up and login screens in the same design.
- A calm, minimal design: warm cream backgrounds, white rounded cards, soft sage panels, deep forest green for primary actions and selected states, and faint gold line accents. Status colours are muted (sage, honey, clay). The font is Outfit.

**Authentication and access**
- Session-based login, logout and public sign-up (Laravel Fortify), with passwords hashed by bcrypt.
- Login throttling: 5 attempts per minute per email and IP address.
- Two roles. Self-registered users are always **Staff**. **Administrators** are created from the command line with `php artisan app:create-user --admin`.
- Staff can view everything, register and edit products and batches, dispatch and receive shipments, and record stock removals.
- Administrators can also activate/deactivate products, recall batches, manage organisations and locations, cancel any shipment, and view the audit log.

**Dashboard**
- Total products, total batches, expired batches, batches approaching expiry and shipments in transit, all calculated from live database records.
- A list of active batches expiring within the warning window (30 days by default).

**Supply chain**
- Organisations (manufacturers, distributors, wholesalers, retailers, logistics providers) with an optional 9-digit RRA TIN.
- Locations per organisation (factories, warehouses, distribution centres, shops), with Rwanda's 30 districts.
- Inactive locations cannot send or receive shipments. Nothing in the ledger can be deleted.

**Shipments and stock**
- Every batch starts at a production location. Its stock is then tracked per location.
- A shipment moves one or more batches between two locations: **in transit → received** or **in transit → cancelled** (stock returns to the origin). Received and cancelled are final.
- Rules: you cannot ship more than is at the origin, ship recalled or expired batches, ship to the same location, use inactive locations, or receive or cancel a shipment twice. If one item is invalid, nothing is dispatched.
- Stock removals record stock leaving the chain: sold, consumed, damaged, disposed of, lost, or a count correction (losses and corrections need a note).
- The batch page shows where the stock is now, what is in transit, and the full movement history: the chain of custody.

**Audit trail**
- Product, batch, organisation and location changes, plus shipment, recall and stock events, are recorded with the user, IP address, and old and new values.
- Administrators can browse it at `/audit-log`.

**Products**
- Paginated list with search by name or code, and filters by category and status.
- Register, view and edit products. Product codes are unique and case-insensitive.
- A product's code becomes read-only once batches exist for it.
- Products are **deactivated, never deleted**. The database refuses to delete a product that has batches.

**Batches**
- Paginated list with search by batch number, and filters by product and status.
- Register batches for active products only, at an active production location. Batch numbers are unique.
- Validation: the manufacturing date cannot be in the future, the expiry date cannot precede manufacturing, and quantities must be positive with at most three decimals.
- The product, batch number and quantities cannot be edited after creation. Only the dates can be corrected; stock changes only through movements.
- Administrators can recall a batch with a mandatory reason. Recalled batches cannot be edited or shipped, but their stock can be removed for disposal.

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
products ──< batches >── locations (origin) >── organizations
                │
                ├──< stock_balances >── locations       quantity per batch per location
                ├──< stock_movements                    append-only ledger
                └──< shipment_items >── shipments >── locations (from, to)
audit_logs                                              append-only, polymorphic subject
```

| Table | Key columns and constraints |
|---|---|
| `users` | Laravel defaults + `role` (`admin` / `staff`, CHECK constraint) |
| `products` | `product_code` UNIQUE, `name`, `description`, `category`, `manufacturer_name`, `unit_of_measure`, `is_active` |
| `batches` | `batch_number` UNIQUE, `origin_location_id`, dates, `initial_quantity` / `current_quantity` as `NUMERIC(14,3)`, recall columns |
| `organizations` | `name` UNIQUE, `type`, `tin` UNIQUE (nullable), contacts, `is_active` |
| `locations` | `organization_id`, `code` UNIQUE, `name`, `type`, `district`, `address`, `is_active` |
| `stock_balances` | UNIQUE (`batch_id`, `location_id`), `quantity` ≥ 0 |
| `shipments` | `from_location_id` ≠ `to_location_id`, `status`, dispatch/receipt/cancellation timestamps and users |
| `shipment_items` | UNIQUE (`shipment_id`, `batch_id`), `quantity` > 0 |
| `stock_movements` | `type`, `quantity` > 0, `from_location_id` / `to_location_id`, `shipment_id`, `removal_reason`, `user_id`, `occurred_at` |
| `audit_logs` | `event`, `subject_type` / `subject_id`, `user_id`, `old_values` / `new_values` (JSON), `ip_address` |

PostgreSQL enforces these rules independently of the application:
- **CHECK constraints:** quantities are never negative, current ≤ initial, expiry ≥ manufacturing, a recall has a reason, a shipment's two locations differ, status-consistent timestamps, and each movement type has exactly the right columns filled.
- **Triggers:** reject every `UPDATE` and `DELETE` on `stock_movements` and `audit_logs`.
- **Foreign keys:** use `RESTRICT`, so nothing that appears in the history can be deleted.

Quantities use exact `NUMERIC`, never floating point. PHP-side arithmetic uses `brick/math`.

## How stock stays consistent

Every stock operation (register batch, dispatch, receive, cancel, remove) is an
action class in `app/Actions` that runs inside **one database transaction**:
the balance change, the ledger entry, the shipment status change and the audit
entry are committed together or not at all.

Concurrent operations are made safe with row locks (`SELECT ... FOR UPDATE`),
always taken in the same order (shipment, then batches by ID, then balances)
to avoid deadlocks:
- Two people shipping the last 10 units at the same time: the second waits for the first, then sees 0 available and is rejected.
- Two people pressing "Mark as received" at once: the shipment row is locked, so the second sees it is already received.

The invariant, which is covered by the tests:
`sum(stock at locations) + sum(in transit) = batch.current_quantity = produced − removed`.

Batches created before Phase 6 have no location. An administrator assigns
their opening stock once, from the batch page.

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

### Demo data

The demo supply chain is fictional but realistic: 8 Rwandan organisations,
10 products, 13 batches and four months of shipments, sales, a recall and a
cancellation. Every batch status appears.

| Command | Where | What it does |
|---|---|---|
| `php artisan db:seed` | Local only | Creates `admin@productsphere.test` and `staff@productsphere.test` (password `password`), then loads the demo supply chain. Refuses to run unless `APP_ENV=local`. |
| `php artisan app:seed-demo` | Anywhere, including production | Loads the demo supply chain **once**, and does nothing on later runs. Creates no account with a known password, so visitors explore by signing up. |

`DemoDataSeeder` uses only the real action classes (no Faker, which is not
installed in production), so its ledger and audit trail are exactly what the
application itself would produce. It moves the clock back for each step, so
the history looks like four months of real activity.

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
- Set `SEED_DEMO_DATA=true` to load the demo supply chain on the first deploy (`start.sh` runs `app:seed-demo`, which is idempotent). `php artisan db:seed` refuses to run in production because it creates known-password accounts.
- Vercel is not used: the app is a server-rendered Laravel monolith with no separate front end to host.

## Current limitations

- **No organisation-level access yet.** Users are not linked to an organisation, so any signed-in user (including self-registered Staff) can see all records and dispatch from or receive at any location. Phase 7 adds this.
- Shipments are received in full. There are no partial receipts or discrepancy reports yet.
- Quantity typos are corrected with a "stock count correction" removal; there is no way to increase stock other than production.
- No password reset or profile page yet (needs a configured mailer).
- No user-management screen; administrators are created with `app:create-user`.
- A recall is irreversible and has no workflow (notifications, affected-shipment reports).

## Roadmap

- **Phase 6 — Traceability:** ✅ done (organisations, locations, shipments, stock ledger, audit trail).
- **Phase 7 — Advanced:** QR codes and public product verification, recall workflows and affected-batch reports, role-based organisational access, a documented REST API with authentication and rate limiting, Docker and CI/CD, and optional GS1/EPCIS-based interoperability.
