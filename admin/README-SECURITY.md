# Panda BC Secure Admin — STAGING ONLY

Status: **Not deployed to VPS1.** Public production pages remain unchanged. The current `/admin.html` is intentionally absent (404).

## What this branch implements

- `admin/login.php`: password hash validation, rotating session ID, CSRF token, HttpOnly + Secure + SameSite cookie, 30-minute inactivity timeout
- `admin/index.php`: original RTL editor, authenticated server session, no `eval`, no hard-coded browser password, saves through `api.php`
- `admin/api.php`: authenticated JSON endpoint with CSRF, schema checks, 512-KB request limit, optimistic concurrency hash, file lock, backup and atomic replace of `config.js`
- `admin/logout.php`: POST-only logout with CSRF
- `admin/common.php`: shared authenticated session and validation logic

## Critical pre-production checks

1. Confirm PHP is **executed**, not served as source, for `bc.panda-il.com`. If PHP is not enabled in Hestia, do **not** publish these files.
2. Reconfirm the running PHP-FPM pool user and ensure it can write `config.js` and create a lock/backups under `/home/matanadmin/.panda-bc-admin`.
3. Provision `password.hash` outside public_html with a strong unique password using `password_hash(..., PASSWORD_DEFAULT)`; never put the password/hash into git or chat.
4. Require TLS and verify Hestia's automatic renewal and HTTP → HTTPS redirect.
5. Run unit, integration and real-browser tests for authentication, CSRF, expiry, rate-limiting, concurrent edits, backups, RTL and mobile layouts. Login attempts now use per-IP counters outside the web root; distributed-attack and account-level protections still require assessment.
6. Verify web server denies access to hidden/backup files and PHP error output contains no secrets.
7. Test a complete backup restoration, including ownership and permissions.
8. Keep the existing production site untouched until a security review passes.

## API save contract

`GET /admin/api.php` → authenticated `{revision,csrf}`

`POST /admin/api.php` with header `X-CSRF-Token` and JSON `{revision,data}` → `{saved:true,revision}`.

A `409` means another edit changed `config.js`; the editor must reload rather than overwrite.

## Known limitations

- Does not yet offer in-app backup restoration.
- Admin login rate-limit is per-IP, not distributed across multiple application servers; account-level and proxy-aware controls need assessment.
- Needs dynamic PHP/Hestia integration verification on VPS1.
- Site config is JavaScript and public content; do not put secrets in `config.js`.
- Public repo history still contains the former client-side demo password; that password must not be reused.

**Do not run deployment until the checks above pass.**
