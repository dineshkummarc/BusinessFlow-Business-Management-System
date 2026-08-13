# INSTALLATION.md — BusinessFlow (Windows / XAMPP)

These steps are written specifically for the confirmed environment this project was audited against:

- **OS:** Windows
- **Stack:** XAMPP 8.2.12 (Apache 2.4.58, PHP 8.2.12)
- **Database:** MariaDB 10.4.32, listening on **port 3307**
- **Database name:** `bms_db`
- **Database user:** `root`, no password

If your setup differs (e.g. MariaDB on the standard port 3306), see the note in Step 4.

---

## 1. Place the project in XAMPP's web root

Extract this ZIP so the project folder ends up at:

```
C:\xampp\htdocs\bms\
```

(If you extract it with an extra wrapper folder, e.g. `C:\xampp\htdocs\BusinessFlow-Final\bms\`, move the inner `bms` folder up so it sits directly under `htdocs`.)

Confirm the folder contains `index.php`, `config/`, `classes/`, `dashboard/`, etc. directly — not nested one level deeper.

## 2. Start Apache and MySQL/MariaDB in the XAMPP Control Panel

Open the XAMPP Control Panel and click **Start** next to both **Apache** and **MySQL**.

If MySQL fails to start on the default port, or you know it's configured for port 3307 (as this project's confirmed environment is), that's expected here — see Step 4.

## 3. Create the database and import the schema

**Option A — via phpMyAdmin (recommended, no command line needed):**
1. Open `http://localhost/phpmyadmin` (or `http://localhost:PORT/phpmyadmin` if your Apache runs on a non-default port).
2. Click **New** in the left sidebar, name the database `bms_db`, choose collation `utf8mb4_unicode_ci`, click **Create**.
3. Select the new `bms_db` database, click the **Import** tab.
4. Choose the file `database/schema.sql` from this project, then click **Go**.
5. You should see confirmation that the import succeeded and 14 tables were created.

**Option B — via command line** (if you have the MySQL client available and know your MariaDB is on a non-default port, add `-P 3307` or your actual port):
```bash
"C:\xampp\mysql\bin\mysql.exe" -u root -P 3307 < database\schema.sql
```

Either way, `database/schema.sql` uses `CREATE TABLE IF NOT EXISTS` throughout, so it's safe to re-run — it will never drop or overwrite existing data.

## 4. Confirm the database configuration matches your setup

Open `config/config.php` and check these lines near the top:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'bms_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

This project ships pre-configured for **port 3307**, matching the confirmed environment. If your MariaDB instance runs on the standard port instead, change `DB_PORT` to `'3306'`. If your `root` account has a password, or you use a different username, update `DB_USER`/`DB_PASS` accordingly.

> **Why the port matters:** if `DB_PORT` doesn't match where your MariaDB is actually listening, every database-backed feature (registration, login, and the entire dashboard) will fail with a connection error, even though the public marketing pages will still load fine (they don't touch the database). See `BUG_FIX_REPORT.md` for the full explanation — this was the primary issue found and fixed during this audit.

## 5. Open the site in your browser

Visit:

```
http://localhost/bms/
```

You should see the BusinessFlow homepage with the full navy/gold design, navigation, and footer.

## 6. Create your account

Click **Get Started** or **Register**, and fill out the form. This creates a real account in the `users` table with a bcrypt-hashed password.

> Public self-registration always creates a `staff`-role account (by design — self-service signup shouldn't grant admin access). To get admin access, see the next step.

## 7. Get admin access (optional, needed for Manage Users / Settings)

The seed data includes a placeholder `admin` row, but its password hash won't match any real password you type. Two ways to get real admin access:

**Easiest — promote your own account via phpMyAdmin:**
1. Register a normal account as in Step 6.
2. In phpMyAdmin, open `bms_db` → `users` table, find your row, click **Edit**.
3. Change the `role` column from `staff` to `admin`, save.
4. Log out and back in — you'll now see **Manage Users** and **Settings** in the sidebar.

**Alternative — set a real password on the seeded admin row:**
1. Generate a bcrypt hash for a password of your choice. If you have PHP CLI available anywhere:
   ```bash
   php -r "echo password_hash('YourPassword123', PASSWORD_DEFAULT);"
   ```
2. In phpMyAdmin, run:
   ```sql
   UPDATE users SET password_hash = '<paste the generated hash here>' WHERE username = 'admin';
   ```
3. Log in with username `admin` and your chosen password.

## 8. Explore the connected workflow

Once logged in, try the full chain end to end: add a **Client** → create a **Project** for them → add a **Task** and drag it across the Kanban board → log an **Expense** → raise an **Invoice** and mark it paid → check **Reports** for the updated numbers.

---

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| Homepage loads fine, but Register/Login shows a database error | `DB_PORT` (or `DB_HOST`/`DB_USER`/`DB_PASS`) in `config/config.php` doesn't match your MariaDB setup | Confirm the actual port MariaDB is listening on in the XAMPP Control Panel (click **Config** → **my.ini**, look for `port=`), and update `config/config.php` to match |
| CSS/logo/images don't load, links look broken | `BASE_PATH` resolved incorrectly for your folder placement | Confirm the project sits at `C:\xampp\htdocs\bms\` exactly (see Step 1); if you placed it elsewhere, adjust accordingly |
| "403 Forbidden" when visiting `config/`, `classes/`, or `database/` directly | This is intentional — `.htaccess` blocks direct access to these folders for security | Not a bug — just visit `http://localhost/bms/` instead |
| phpMyAdmin import fails partway through | An earlier partial import left some tables in place | Since every `CREATE TABLE` uses `IF NOT EXISTS`, just re-run the import — it will skip existing tables and pick up where it left off |
