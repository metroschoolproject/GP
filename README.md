# Golden Promise

Golden Promise is a PHP/MySQL wedding marketplace for couples, suppliers, and administrators. Customers can browse wedding services and curated packages, build a cart, submit booking requests, upload manual payment proof, track bookings, request cancellations, and leave reviews. Suppliers can onboard, manage services and availability, respond to bookings, track earnings, and request payouts. Admin users review suppliers, services, bookings, payments, refunds, replacements, packages, and platform settings.

The project is built as a custom MVC application intended to run under XAMPP at `/GP`.

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Requirements](#requirements)
- [Local Setup](#local-setup)
- [Configuration](#configuration)
- [Database](#database)
- [Running the App](#running-the-app)
- [User Roles and Main Areas](#user-roles-and-main-areas)
- [Core Workflows](#core-workflows)
- [Styling and Frontend Assets](#styling-and-frontend-assets)
- [Uploads and File Storage](#uploads-and-file-storage)
- [Testing and Verification](#testing-and-verification)
- [Useful Maintenance Commands](#useful-maintenance-commands)
- [Security Notes](#security-notes)
- [Known Caveats](#known-caveats)
- [Related Documentation](#related-documentation)

## Features

### Customer

- Public home, service, package, wishlist, and profile pages.
- Email/password registration with email verification.
- Google and Facebook OAuth login hooks.
- Cart support for individual services and packages.
- Booking creation with event details, schedule checks, package schedules, and booking item snapshots.
- Manual payment proof submission for deposits, remaining payments, replacement deltas, and full payment flows.
- Booking status tracking, vouchers, cancellation requests, and notifications.
- Wishlist collections, notes, and moving saved services between collections.
- Review submission after completed bookings.

### Supplier

- Supplier onboarding flow with business identity, categories, contact details, story, cover photo, license upload, and agreement acceptance.
- AI-assisted category suggestions through Gemini when `GEMINI_API_KEY` is configured, with local keyword fallback.
- Pending/locked dashboard states until admin approval and membership fee verification.
- Supplier membership fee proof upload.
- Dashboard KPIs, notifications, service management, media management, publishing requests, package management, and availability calendar.
- Venue room and service availability overrides.
- Booking assignment response, supplier cancellation requests, reschedule proposals, payment history, earnings, payout requests, and review views.

### Admin

- Dashboard, overview data, global search, logs, and notifications.
- Supplier application review, approval/rejection, warnings, notes, bans/unbans, and deletion.
- Customer moderation, status logs, profile/contact edits, suspension, bans/unbans, and deletion.
- Service publishing review and category management.
- Booking detail, cancellation, completion, supplier replacement, refund, and payment verification queues.
- Manual supplier payout queue and payout marking.
- Curated package creation, editing, draft publishing, item management, deletion, and restore.
- Cron-style endpoints for final payment collection, payment reminders, booking expiration, and auto-completion.

## Tech Stack

- PHP 8 custom MVC
- MySQL / MariaDB through PDO
- Apache with `mod_rewrite`
- Composer
- PHPMailer
- Stripe PHP SDK dependency currently installed, though active payment flow is manual
- Google API Client for OAuth integrations
- Node.js tooling for PostCSS/Tailwind CSS
- Tailwind CSS 4/PostCSS generated stylesheet
- Vanilla JavaScript for browser interactions and AJAX

## Project Structure

```text
GP/
├── app/
│   ├── config/             # App constants and environment-backed settings
│   ├── controllers/        # MVC controllers
│   ├── helpers/            # Security, redirects, env loading, flash messages, etc.
│   ├── libraries/          # Core router, Controller base, Database wrapper
│   ├── models/             # Database-facing domain models
│   ├── services/           # Email, upload, authorization, KPI services
│   ├── traits/             # Shared controller traits
│   └── views/              # PHP views for admin/customer/supplier/payment pages
├── database/
│   ├── goldenpromise15.sql # Current normalized schema dump, structure only
│   ├── goldenpromise14.sql # Older dump with sample data
│   ├── migrations/         # Later migration scripts
│   └── seed_*.php/.sql     # Optional seed scripts
├── docs/                   # Feature and business-logic documentation
├── public/
│   ├── index.php           # Front controller
│   ├── css/                # Generated and hand-maintained CSS
│   ├── js/                 # Browser-side JavaScript
│   ├── images/             # Static images
│   └── uploads/            # Runtime/user-uploaded files
├── resources/css/app.css   # CSS source compiled into public/css/app.css
├── tests/run-static-checks.js
├── composer.json
├── package.json
└── README.md
```

## Requirements

- PHP 8.1+ recommended
- Composer
- MySQL or MariaDB
- Apache with rewrite support
- XAMPP or equivalent local PHP stack
- Node.js 18+ and npm, for CSS builds and static checks

The current local configuration assumes:

- Project path: `/Applications/XAMPP/xamppfiles/htdocs/GP`
- App URL: `http://localhost/GP`
- Database host: `localhost;port=3307`
- Database name: `goldenpromise`
- Database user: `root`
- Database password: empty

Adjust these in `app/config/config.php` if your environment is different.

## Local Setup

1. Clone or place the project under your web root:

   ```bash
   /Applications/XAMPP/xamppfiles/htdocs/GP
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Install Node dependencies:

   ```bash
   npm install
   ```

4. Create an environment file:

   ```bash
   cp .env.example .env
   ```

5. Fill any required secrets in `.env`.

6. Create the MySQL database:

   ```sql
   CREATE DATABASE goldenpromise CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

7. Import the schema or a data dump. See [Database](#database).

8. Start Apache and MySQL/MariaDB from XAMPP.

9. Visit:

   ```text
   http://localhost/GP
   ```

## Configuration

### Environment Variables

Secrets are loaded from `.env` through `app/helpers/env.php` during bootstrap.

Current `.env.example` keys:

```dotenv
GOOGLE_CLIENT_SECRET=
FACEBOOK_APP_SECRET=
GEMINI_API_KEY=
CRON_TOKEN=
PAYMENT_GATEWAY_SANDBOX=true
MERCHANT_ID=
PAYMENT_GATEWAY_SECRET=
PAYMENT_GATEWAY_CURRENCY=MMK
PAYMENT_GATEWAY_MMQR_CHANNEL=
PAYMENT_GATEWAY_CARD_CHANNEL=CC
```

### App Constants

Most local constants live in `app/config/config.php`:

- `APPNAME`
- `URLROOT`
- `IMG_ROOT`
- database connection constants
- OAuth client IDs and redirect URIs
- mail host and port
- platform bank account placeholders
- booking/payment percentages
- category default service times
- slot-booking category list

Important: `URLROOT` is dynamically built from the request host and the hardcoded base path `/GP`. If you rename the folder or deploy under a different path, update `$basePath`.

### Manual Payment Accounts

`PLATFORM_BANK_ACCOUNTS` in `app/config/config.php` contains placeholder payment account numbers. Replace these before using payment flows outside local development.

## Database

### Main Schema

`database/goldenpromise15.sql` is the current normalized schema dump. It is structure-only and intentionally does not include sample data.

Import with:

```bash
/Applications/XAMPP/xamppfiles/bin/mysql -u root -P 3307 goldenpromise < database/goldenpromise15.sql
```

If your MySQL binary is on your shell path:

```bash
mysql -u root -P 3307 goldenpromise < database/goldenpromise15.sql
```

### Sample Data

`database/goldenpromise14.sql` is an older dump that includes data. It may be useful for local demos, but it is not the normalized v15 schema source.

There are also targeted seed scripts:

- `database/seed_service_categories.sql`
- `database/seed_all_categories.php`
- `database/seed_dress_shops.php`
- `database/seed_eiphyu_venues.sql`
- `database/seed_make_visible.php`
- `database/seed_subitems.php`
- `database/seed_reviews.php`
- `database/seed_supplier_users.php`

Example:

```bash
/Applications/XAMPP/xamppfiles/bin/php database/seed_supplier_users.php
```

`seed_supplier_users.php` creates login users for supplier rows that do not have `user_id`. The script-defined test password is `Aa12@3456`.

### Migrations

The `database/` folder contains migration scripts for features such as:

- supplier onboarding
- platform packages
- booking snapshots
- wishlist collections
- food, attire, car, venue, and rental-specific data
- supplier replacements
- cancellation/refund tracking
- manual payment fields
- payout lifecycle
- platform settings

Apply migrations carefully and review each script first. There is no migration runner in this repository.

## Running the App

The request entry point is:

```text
public/index.php
```

Apache rewrite rules in `public/.htaccess` route paths into the custom router:

```text
/GP/public/controller/method/param
```

The router also strips the `/GP` prefix from incoming `url` parameters so user-facing URLs can be served as:

```text
http://localhost/GP/main/service
http://localhost/GP/supplier/onboarding
http://localhost/GP/admin/dashboard
```

Default controller and method:

```text
Main::home()
```

## User Roles and Main Areas

Roles are stored in `roles` and assigned through `user_roles`.

Common roles:

- `customer`
- `supplier`
- `admin`

Main route areas:

```text
/main/...              Public/customer pages
/users/...             Registration, login, email verification, OAuth callbacks
/cart/...              Cart actions
/booking/...           Customer booking and payment workflows
/supplier/...          Supplier onboarding, dashboard, services, bookings, earnings
/payments/supplierFee Supplier membership fee payment proof
/admin/...             Admin dashboard and operations
/review/...            Customer reviews
/webhook/...           Legacy gateway endpoints; currently return 410
```

## Core Workflows

### Customer Booking Flow

1. Customer browses services or packages.
2. Customer adds items to cart.
3. Customer creates a booking and supplies event details.
4. Suppliers respond where required.
5. Customer submits manual deposit/payment proof.
6. Admin verifies payment.
7. Booking moves through confirmed/final payment/finalized/completed states.
8. Admin can handle cancellations, refunds, replacements, and completion.
9. Completed bookings can receive reviews.

### Manual Payment Flow

Active payment and payout flows are manual:

- Customer or supplier transfers money outside the system.
- User uploads proof, amount, date, bank/method, and transaction reference.
- Admin verifies or rejects the proof.
- The system records status, notes, proof paths, and notifications.

Legacy gateway webhook endpoints in `app/controllers/Webhook.php` return HTTP `410 Gone`.

### Supplier Onboarding Flow

1. Supplier registers and verifies email.
2. Supplier completes onboarding:
   - business name
   - business category selection
   - contact details
   - business description
   - cover photo
   - business license
   - supplier agreement acceptance
3. Admin reviews supplier application.
4. Approved supplier submits membership fee proof.
5. Admin verifies membership payment.
6. Supplier dashboard unlocks.
7. Supplier can create/manage services and request publication.

### Supplier Replacement Flow

The app supports replacement handling when a supplier declines or cannot fulfill a confirmed package booking:

- Admin reviews replacement queue.
- Admin picks replacement candidates.
- Customer approval may be required when replacement prices exceed configured thresholds.
- Replacement delta payments/refunds can be tracked manually.

### Refund and Cancellation Flow

Refunds are manual. The application records:

- refund amount
- reason and policy note
- status
- method/reference
- proof/slip path
- admin processing and completion timestamps

See `docs/manual-refund-logic.md` for more detail.

## Styling and Frontend Assets

The main generated stylesheet is:

```text
public/css/app.css
```

The source stylesheet is:

```text
resources/css/app.css
```

Tailwind/PostCSS config:

```text
tailwind.config.js
postcss.config.js
```

Build CSS once:

```bash
npm run build:css
```

Watch CSS during development:

```bash
npm run watch:css
```

The design system uses warm champagne, mauve, wine, cream, and gold tones defined in `tailwind.config.js` and in view-local CSS for highly custom screens such as supplier onboarding.

## Uploads and File Storage

Runtime uploads are stored under `public/uploads/`.

Common upload categories include:

- supplier cover photos and documents
- service media
- payment slips
- admin package images
- profile photos
- refund proof

Upload validation is implemented in controllers/services depending on workflow. Typical accepted formats include JPG, PNG, WEBP, and PDF for document/payment proof flows. Many uploads are limited to 5 MB.

Make sure Apache/PHP can write to required upload directories in local and production environments.

## Testing and Verification

Run the static check script:

```bash
npm test
```

The script:

- runs `php -l` over PHP files in `app`, `database`, and `public`
- runs `node --check` over public JavaScript files
- verifies selected expected strings in important files

You can also check a single PHP file:

```bash
/Applications/XAMPP/xamppfiles/bin/php -l app/views/supplier/onboarding.php
```

Note: the current `tests/run-static-checks.js` contains assertions for `app/services/PayoutService.php`, but the current tree handles payouts inside controller/model code and does not include that service file. If `npm test` fails on that assertion, either restore/update the expected service or update the static check to match the current architecture.

Some database verification scripts are included:

```bash
/Applications/XAMPP/xamppfiles/bin/php database/verify_reserve_failure_detail.php
/Applications/XAMPP/xamppfiles/bin/php database/verify_unavailable_package_services.php
/Applications/XAMPP/xamppfiles/bin/php database/verify_alternative_dates.php
```

## Useful Maintenance Commands

Install dependencies:

```bash
composer install
npm install
```

Build CSS:

```bash
npm run build:css
```

Watch CSS:

```bash
npm run watch:css
```

Run syntax/static checks:

```bash
npm test
```

Lint a PHP file with XAMPP PHP:

```bash
/Applications/XAMPP/xamppfiles/bin/php -l path/to/file.php
```

Import schema:

```bash
/Applications/XAMPP/xamppfiles/bin/mysql -u root -P 3307 goldenpromise < database/goldenpromise15.sql
```

Seed supplier user accounts:

```bash
/Applications/XAMPP/xamppfiles/bin/php database/seed_supplier_users.php
```

## Security Notes

- Do not commit real `.env` secrets.
- `.env.example` notes that older secrets may have existed in config history; rotate any real keys before production use.
- Replace placeholder platform bank account numbers before production.
- Set a strong `CRON_TOKEN` before exposing cron endpoints.
- Review admin cron endpoints before making them reachable from the public internet.
- Keep uploaded files out of executable paths where possible and validate MIME/type/size.
- Email, OAuth, Gemini, and payment-related credentials should be managed through environment variables.
- The active payment model is manual, so admin verification and proof records are part of the financial control process.
- Legacy gateway webhook endpoints intentionally return `410 Gone`.

## Known Caveats

- The project uses a custom MVC framework rather than Laravel/Symfony.
- There is no automated migration runner; SQL scripts must be applied manually.
- `database/goldenpromise15.sql` is schema-only. Use seed scripts or another dump for demo data.
- `app/config/config.php` still contains environment-specific defaults such as `/GP`, `localhost;port=3307`, and placeholder/manual payment account data.
- `MAIL_USERNAME` is currently hardcoded in config. Move production mail credentials into `.env` before deployment.
- Some older implementation docs discuss a 2C2P gateway abstraction, but current runtime webhook endpoints state that payment and payout flows are manual.
- The static test script may need updating if the payout architecture remains controller/model-based instead of service-based.

## Related Documentation

- `docs/manual-refund-logic.md` — manual refund policy and status model
- `docs/review-feature-design.md` — review feature design notes
- `docs/booking-logic-audit-and-remediation.md` — booking logic audit notes
- `PAYMENT_FLOW_IMPLEMENTATION.md` — historical/payment-flow implementation notes
- `IMPLEMENTATION_2C2P.md` — historical 2C2P implementation notes; verify against current code before using
- `supplier_aggrement.md` — supplier agreement content
- `PHASE_2_SUMMARY.md` — phase summary notes

## License

No explicit project license is defined in this repository. Add a license before distributing or deploying publicly.
