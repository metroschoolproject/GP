---
name: run-golden-promise
description: Build, run, screenshot, and test Golden Promise (GP) — a PHP wedding marketplace web app. Use when asked to start GP, build its CSS, take a screenshot, run the smoke test, or interact with the running app.
---

All paths are relative to `/Applications/XAMPP/xamppfiles/htdocs/GP/`.

Golden Promise is a custom PHP MVC wedding marketplace running under XAMPP (Apache + MySQL). It's already served at `http://localhost/GP/` on this machine. Drive it via `chromium-cli` (Playwright-based REPL) or the bundled `driver.mjs`.

## Prerequisites

The project requires XAMPP (Apache + MySQL/MariaDB + PHP 8+), Composer, and Node.js. Everything is already installed on this machine:

- Apache is running (port 80)
- MySQL (MariaDB 10.4.28) is running with database `goldenpromise` already imported
- PHP 8.4 via Homebrew + XAMPP PHP 8.2
- Node v24.16.0, npm 11.13.0
- Composer dependencies installed in `vendor/`
- Playwright + Chromium installed (for headless screenshots)

## Setup

```bash
# Install JS dependencies (for Tailwind CSS v4 build)
npm install

# Build CSS (Tailwind v4 + PostCSS)
npm run build:css

# CSS compiles from resources/css/app.css → public/css/app.css
```

The database is ready. No additional setup needed.

## Build

```bash
npm run build:css       # Build CSS once
npm run watch:css       # Watch during development
```

PHP needs no build step — the custom MVC framework autoloads classes on demand.

## Run (agent path — Playwright + driver.mjs)

The app is served by XAMPP Apache at `http://localhost/GP/`. Use the Playwright driver to navigate and take screenshots:

```bash
# Default sequence: nav home → screenshot → nav login → screenshot
node .claude/skills/run-golden-promise/driver.mjs
```

Custom commands:

```bash
# Take screenshots of specific pages
node .claude/skills/run-golden-promise/driver.mjs \
  --nav / --wait body --ss home.png \
  --nav /login --wait body --ss login.png \
  --nav /users/auth --wait body --ss auth.png \
  --nav /customerServices/packages --wait body --ss packages.png

# Check for console errors on a page
node .claude/skills/run-golden-promise/driver.mjs --nav /login --errors --ss login.png

# Extract HTML content
node .claude/skills/run-golden-promise/driver.mjs --nav / --html '#main-content'

# Use a different base URL
URL=http://192.168.1.5/GP node .claude/skills/run-golden-promise/driver.mjs --nav / --ss remote.png
```

Screenshots land in `/tmp/gp-shots/` by default. Override with `SS_DIR=/path/to/dir`.

| Command | Description |
|---|---|
| `--nav <path>` | Navigate to URL path (relative to base URL) |
| `--wait <css-sel>` | Wait for a CSS selector to appear |
| `--click <css-sel>` | Click on an element |
| `--fill <sel> <text>` | Fill an input field |
| `--ss [filename]` | Take a screenshot |
| `--html [selector]` | Dump HTML of selector (default: body) |
| `--errors` | Print browser console errors |
| `--eval <js>` | Evaluate JavaScript in page context |

### Key routes

| Route | Page |
|---|---|
| `/` or `/main/home` | Home page |
| `/login` | Login page |
| `/users/auth` | Unified auth (register) |
| `/users/auth?type=supplier` | Supplier registration |
| `/users/auth?type=internal` | Admin/staff login |
| `/main/services` | Browse services |
| `/customerServices/packages` | Browse packages |
| `/supplier/onboarding` | Supplier onboarding |
| `/admin/dashboard` | Admin dashboard |

## Run (human path)

The app runs under XAMPP Apache. Access it at `http://localhost/GP/` in a browser. No separate server process to start.

## Test

The project has no formal test suite. Verification steps:

```bash
# 1. Confirm Apache + MySQL are running
curl -sf http://localhost/GP/ > /dev/null && echo "App is up"

# 2. Verify CSS is compiled
curl -sf http://localhost/GP/public/css/app.css | head -c 100

# 3. Run the smoke test (takes screenshots of key pages)
node .claude/skills/run-golden-promise/driver.mjs

# Check screenshots in /tmp/gp-shots/
```

## Gotchas

- **CSS changes require rebuild:** Tailwind v4 source is `resources/css/app.css`. After editing, run `npm run build:css` to compile. No hot reload.
- **Database config is hardcoded:** `app/config/config.php` defines MySQL creds (root, no password, database `goldenpromise`). No `.env` file.
- **404 assets in console:** Fonts and some assets 404 in the console. These are external font URLs that are blocked; they don't affect functionality.
- **Detached from XAMPP control:** XAMPP services (Apache, MySQL) run via system launch daemons. Use `/Applications/XAMPP/xamppfiles/bin/mysql` for MySQL CLI — the system `mysql` is not on PATH.
- **Config constants are PHP `define()`:** All configuration (DB, OAuth keys, API keys, URLs) is in `app/config/config.php`. No `.env` support.

## Troubleshooting

- **"MySQL not found"**: Use the full path: `/Applications/XAMPP/xamppfiles/bin/mysql -u root`
- **CSS not updating**: Run `npm run build:css` — Tailwind v4 needs compilation.
- **Blank pages or PHP errors**: Check XAMPP Apache error log: `/Applications/XAMPP/xamppfiles/logs/error_log`
- **404 on all routes**: Ensure `.htaccess` files exist at root and in `public/`. The rewrite engine may be disabled in Apache config.
