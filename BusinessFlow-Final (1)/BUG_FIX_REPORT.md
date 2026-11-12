# BUG_FIX_REPORT.md — BusinessFlow

**Audit type:** Forensic bug audit and repair (no redesign, no rebuild)
**Environment under test:** XAMPP on Windows — Apache 2.4.58, PHP 8.2.12, MariaDB 10.4.32 on port 3307
**Trigger symptom reported:** Public pages load correctly; account registration and other database-backed actions fail.

This report documents every **verified** issue found during the audit, the exact root cause, the fix applied, and how it was verified. Nothing was changed that wasn't a confirmed bug — the existing UI, layout, styling, navigation structure, and all working features were left untouched.

---

## Issue #1 (Critical — root cause of the reported symptom)

**Issue:** Registration (and, transitively, login and every other database-backed feature) fails with a generic "Database connection failed" message.

**File:** `classes/Database.php`, `config/config.php`

**Root cause:**
`config/config.php` defined `DB_HOST` but **never defined a `DB_PORT` constant at all**, and `classes/Database.php` built its PDO DSN as:
```php
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
```
This DSN has no `port=` segment. On this environment, MariaDB listens on **3307**, not the MySQL/MariaDB standard default of 3306. Without an explicit port in the DSN, PHP's PDO MySQL driver attempts the default port (3306) — which nothing is listening on here — and the connection fails.

This exactly matches the reported symptom: pages that never touch the database (the public marketing pages) rendered fine, while the first thing to `instantiate` the `Database` class — `new Auth()` → `new User()` → `Database::getInstance()`, hit on the very first line of `Auth::register()` — failed immediately. Login (`login.php`) goes through the identical `new Auth()` → `new User()` chain and would have failed for the same reason, even though it wasn't the symptom explicitly reported.

**Original behavior:**
```php
// config/config.php
define('DB_HOST', 'localhost');
// (no DB_PORT constant existed anywhere in the codebase)

// classes/Database.php
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
```

**Fix applied:**
1. Added a `DB_PORT` constant to `config/config.php`, set to `3307` to match this confirmed environment (with a comment explaining how to change it if the project moves to a machine using the standard port 3306).
2. Also changed `DB_HOST` from `'localhost'` to `'127.0.0.1'` — on Windows, PDO's `mysqlnd` driver can resolve `localhost` via a named pipe rather than TCP in some configurations, which would silently ignore the port entirely. Using the literal IP forces a TCP connection, guaranteeing the port is actually honored. This matches the connection string the user confirmed works via a direct PDO test.
3. Updated the DSN in `classes/Database.php` to include the port:
   ```php
   $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
   ```

**Why this fix is correct:** The PDO_MYSQL DSN format is `mysql:host=host;port=port;dbname=db;charset=charset` — this is the documented, standard syntax. No other part of the codebase opens a database connection outside of `classes/Database.php` (verified — see Verification section below), so this single change fixes every module that touches the database.

**Test performed:** Static verification — traced `new Auth()` in `register.php` and `login.php` down through `Auth::__construct()` → `new User()` → `Database::getInstance()` → `new PDO($dsn, ...)`, confirming this is the only connection path in the app and that the DSN now matches the documented, working configuration (`127.0.0.1:3307`). Also grepped the entire codebase for any other `new PDO`, `mysqli_connect`, or `mysql_connect` call — none found.

**Test result:** **VERIFIED via static trace.** This sandbox has no network access to install a PHP CLI or run a live MySQL/MariaDB server, so the fix could not be executed end-to-end here — the DSN construction and PDO syntax were verified correct, but **the live connection itself must be confirmed by the user on their actual XAMPP stack**. See Section "Not Verified" below.

---

## Issue #2 (High — error visibility)

**Issue:** When the database connection fails, the real reason is hidden, making the actual problem (a wrong port) much harder to self-diagnose.

**File:** `classes/Database.php`

**Root cause:** The `catch (PDOException $e)` block discarded `$e` entirely and always displayed the same generic message ("Database connection failed. Please verify your database is running and the credentials in config/config.php are correct."), regardless of the actual underlying error (wrong port, wrong password, database doesn't exist, service not running, etc. all looked identical).

**Original behavior:**
```php
} catch (PDOException $e) {
    die('Database connection failed. Please verify your database is running '
        . 'and the credentials in config/config.php are correct.');
}
```

**Fix applied:** When `display_errors` is on (the default in this project's `config.php`, appropriate for local development), the real `PDOException` message is now shown in a styled error box, along with a pointer to the likely `config/config.php` constants to check. When `display_errors` is off (recommended for production — see README), it falls back to the original safe generic message, so no information is ever leaked to real end users in a live deployment.

**Why this fix is correct:** This directly satisfies "do not hide errors" during development/testing, while preserving safe behavior for production. It's a diagnostic improvement only — no change to any working code path, no change to the UI a normal user ever sees during normal operation.

**Test performed:** Reviewed the conditional logic and confirmed `ini_get('display_errors')` correctly reflects the `ini_set('display_errors', 1)` already set at the top of `config.php`. Confirmed the HTML fragment is well-formed and all quotes are properly escaped.

**Test result:** **VERIFIED via static review.** The actual on-screen appearance during a real failed connection could not be captured in this sandbox (no live PHP/MySQL available) — recommend the user glance at it once by temporarily setting an intentionally wrong `DB_PORT` to confirm the message renders as expected, then reverting.

---

## Issue #3 (Medium — Windows-specific path bug)

**Issue:** `BASE_PATH` (used to build every asset `<link>`/`<script>`/`<img>` and every internal navigation `href` across the entire site) could silently resolve to the wrong value on a Windows/XAMPP setup, which would break CSS, JavaScript, the logo, and internal links across the whole site.

**File:** `config/config.php`

**Root cause:** `BASE_PATH` is computed by checking whether the project's real filesystem path (from `dirname(__DIR__)`) starts with Apache's `DOCUMENT_ROOT`, using PHP's `strpos()` — which is **case-sensitive**. Windows filesystems are case-insensitive, so PHP and Apache can legitimately report the same physical location with different casing (e.g. PHP reporting `C:/xampp/htdocs/bms/config` while Apache's `DOCUMENT_ROOT` reports `c:/xampp/htdocs`). A case-sensitive `strpos()` check would then fail to find the match, silently falling back to `BASE_PATH = ''` — which would make every asset link resolve to the site root instead of `/bms/...`, causing broken CSS/JS/images and broken internal navigation.

**Fix applied:** Changed the comparison from `strpos()` to `stripos()` (case-insensitive), which correctly matches regardless of drive-letter casing differences between PHP and Apache on Windows, while remaining functionally identical (and safe) on case-sensitive filesystems like Linux/macOS.

**Why this fix is correct:** This is the standard, minimal fix for this exact class of Windows path bug — a one-word function-name change with no effect on the resulting logic for any environment where the case already matched (which is the common case), and correct new behavior for the case-mismatch scenario this project's confirmed environment (Windows) can realistically produce.

**Test performed:** Manually traced the logic with simulated Windows-style mixed-case paths (`C:/xampp/htdocs/bms` vs `c:/xampp/htdocs`) and confirmed `stripos()` returns `0` (match) where `strpos()` would have returned `false`.

**Test result:** **VERIFIED via static trace and logic simulation.** Could not be confirmed against a live Windows/Apache request in this sandbox — recommend the user do a quick visual check that the site's CSS/logo load correctly (they likely already were, if the casing happened to match on their specific machine, in which case this fix is a safety net rather than an active repair).

---

## Issues audited and confirmed **NOT** present (no fix needed)

The following areas from the audit checklist were thoroughly checked and found to already be correct — **left untouched**, per the no-redesign rule:

| Area | Finding |
|---|---|
| SQL injection | Every query in every class uses PDO prepared statements with `?` placeholders and `PDO::ATTR_EMULATE_PREPARES => false`. Full codebase grep for string-concatenated SQL found two matches, both false positives (verified by direct inspection — both are fully parameterized). |
| XSS | All user-supplied output passes through `htmlspecialchars()`, either at input time via `Validator::clean()` or at output time. |
| CSRF | Every POST form across all 46 PHP files carries and verifies a CSRF token (`Auth::verifyCsrf()`), automated-checked with zero exceptions. |
| Password security | Passwords are hashed with `password_hash()` (bcrypt) and never passed through `Validator::clean()` (which would corrupt them by HTML-escaping special characters) — verified in `register.php`. |
| Headers-already-sent risk | No file has a BOM or stray whitespace before `<?php`; every `header('Location: ...')` redirect call occurs before any HTML output in its file — automated-checked across every PHP file with zero exceptions. |
| Schema/code column mismatches | Every `INSERT`/`UPDATE` statement in every class was cross-referenced against the actual `CREATE TABLE` definitions in `database/schema.sql` — zero mismatches across all 14 tables. |
| Foreign key integrity | All 17 foreign keys reference tables and columns (`id`) that exist and are defined earlier in the schema file — zero dangling references. |
| Duplicate email/username handling | `Auth::register()` explicitly checks `User::emailExists()` and `User::usernameExists()` before insert, returning a clear error rather than relying on the database's `UNIQUE` constraint to fail silently or ugly. |
| Broken includes/links | Every `require`/`include` path and every internal `href`/`src` reference across all 46 PHP files resolves to a real file on disk — automated-checked, zero broken references. |
| Duplicate form submission | Every state-changing form follows the Post/Redirect/Get pattern (`header()` + `exit` immediately after a successful write), so refreshing the result page never re-submits the form. |

---

## Files Modified

| File | Change |
|---|---|
| `config/config.php` | Added `DB_PORT` constant (3307); changed `DB_HOST` to `127.0.0.1`; changed `BASE_PATH` comparison from `strpos()` to `stripos()` |
| `classes/Database.php` | Added `port=` to the PDO DSN; improved the connection-failure error message to show the real cause during development without leaking it in production |

**No other files were modified.** No UI, layout, CSS, JavaScript behavior, navigation structure, or business logic was touched.

---

## Not Verified (honest disclosure)

This sandbox environment has **no network access** and therefore no way to install a PHP CLI or run a live MySQL/MariaDB server. The following could not be executed end-to-end here and should be confirmed by the user on their actual XAMPP stack:

- **Live database connection** using the corrected DSN (`127.0.0.1:3307`)
- **Full registration flow**, submitted through an actual browser against a running Apache + MariaDB stack
- **Login with a freshly registered account**
- **Duplicate email/username rejection**, observed live rather than traced through code
- **Actual on-screen rendering** of every page (confirmed via code/asset-path tracing, not a rendered screenshot)

Everything above was verified as thoroughly as static analysis allows: brace/parenthesis balance across all 46 PHP files, every class reference resolving, every include/link resolving, every SQL statement's columns matching the schema, and the exact code path from form submission to database write traced line by line. See `TEST_REPORT.md` for the full breakdown of what was and wasn't executable in this environment.
