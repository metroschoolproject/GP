# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Golden Promise** — a wedding service marketplace connecting couples ("customers") with wedding service providers ("suppliers"). Built with a custom PHP MVC framework (no Laravel/Symfony), Tailwind CSS v4, MySQL, and Stripe.

## Tech Stack

- **Backend**: PHP 8+, custom MVC framework
- **Database**: MySQL (PDO wrapper)
- **Frontend**: Tailwind CSS v4 (via PostCSS), vanilla JS
- **Payments**: Stripe (stripe/stripe-php ^14.8)
- **Email**: PHPMailer (phpmailer/phpmailer ^7.0)
- **Auth**: Google OAuth (google/apiclient ^2.19), Facebook OAuth
- **AI**: Google Gemini API (via cURL)
- **Build**: PostCSS CLI, Tailwind CLI

## Common Commands

```bash
# Build CSS (Tailwind v4 + PostCSS)
npm run build:css

# Watch CSS during development
npm run watch:css
```

The PHP app runs via XAMPP on `http://localhost/GP`. No build step for PHP — the framework autoloads classes on demand (PSR-4 via Composer + a custom spl_autoload_register for libraries).

## Code Architecture

### Request Flow

```
.htaccess (RewriteRule) → public/index.php → app/boostrap.php → Core.php (router)
```

- `.htaccess` rewrites all requests to `public/index.php?url=<path>`
- `Core.php` parses the URL, loads the controller, calls the method, injects params
- Fallback: `index.php` at root also boots the app (for direct access)

### Directory Structure

| Directory | Purpose |
|---|---|
| `app/controllers/` | Route handlers (one class per URL prefix) |
| `app/models/` | Database query logic (PDO via `Database.php`) |
| `app/views/` | PHP templates, organized by controller name |
| `app/libraries/` | Core framework: `Core.php` (router), `Controller.php` (base), `Database.php` (PDO wrapper) |
| `app/helpers/` | Standalone functions: `Pagination.php`, `flashmessage.php`, `redirect.php`, `rememberauth.php` |
| `app/services/` | Business logic: `UploadService.php`, `SupplierAuthorizationService.php` |
| `app/config/config.php` | Constants: DB creds, OAuth keys, API keys, URL base |
| `public/` | Web root: `index.php` (entry point), `uploads/`, compiled CSS |
| `resources/css/` | Tailwind source CSS input |
| `database/` | SQL dumps and migration scripts |

### Key Files

- `app/config/config.php` — All environment constants (DB, OAuth, Gemini API key, URL roots)
- `app/boostrap.php` — App boot: loads config, helpers, Composer autoload, registers spl_autoload for `app/libraries/`
- `app/libraries/Core.php` — Front controller: URL routing, controller/method resolution
- `app/libraries/Controller.php` — Base class: `view()` and `model()` loader methods
- `app/libraries/Database.php` — PDO wrapper: `dbquery()`, `dbbind()`, `dbexecute()`, `getmultidata()`, `getsingledata()`

### Role System

Three user roles, stored in a `user_roles` pivot table:

| Role | Controllers | Entry Point |
|---|---|---|
| **admin** / **staff** | `Admin.php` | `admin/*` routes |
| **supplier** | `Supplier.php` (delegates to sub-controllers) | `supplier/*` routes |
| **customer** | `Main.php`, `CustomerServices.php` | `main/*`, `customerServices/*` routes |

### Supplier Feature Architecture

`Supplier.php` is a facade that delegates to sub-controllers via `forwardTo()`:

- `SupplierServices.php` — CRUD for services & packages, calendar, publish workflow with admin approval
- `SupplierServiceMedia.php` — Media uploads for services
- `SupplierAvailability.php` — Service availability slots, overrides, booking reservation
- `SupplierNotifications.php` — Notification polling (JSON endpoints)

This delegation pattern keeps the `supplier/*` URL namespace while organizing logic across files.

### Key Models

| Model | Purpose |
|---|---|
| `SupplierServiceManager.php` (~62KB, largest) | Service/package CRUD, calendar, availability, readiness checks |
| `CustomerServiceCatalog.php` (~24KB) | Customer-facing service browsing & filtering |
| `SupplierProfile.php` (~23KB) | Supplier applications, profiles, categories, dashboard data |
| `User.php` | Registration, login with challenge-response, role management, account locking |
| `Payment.php` | Stripe integration, supplier fee payment queue |
| `Notification.php` | Admin & supplier notifications for approval workflow |

### Key Services

- `UploadService.php` — File upload/validation, image optimization (WebP variant creation via GD)
- `SupplierAuthorizationService.php` — Checks supplier status, payment status, dashboard access gates

### Authentication Flow

1. Register with email/password + email verification (PHPMailer)
2. Login: challenge-response with SHA-256 password hashing → OTP verification → session
3. Password account locking after 3+ failed attempts within 15 min (triggers alert email)
4. "Remember me" via cookie token (`rememberauth.php`)
5. Social login: Google OAuth and Facebook OAuth

### Publish Workflow

Supplier creates a service → marks it as ready → requests publish → notification sent to admin → admin reviews & approves → service goes live for customers. Services require complete setup (images, packages, availability) before publish is allowed. Cooldown: 2 hours between publish requests.

### Tailwind CSS v4

- Uses the new Tailwind v4 PostCSS plugin (`@tailwindcss/postcss`)
- `tailwind.config.js` extends the default theme with custom wedding-industry colors (`app-*`), fonts (Poppins), and spacing
- Source: `resources/css/app.css` → Output: `public/css/app.css`
- Run `npm run build:css` or `npm run watch:css` during development
