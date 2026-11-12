# BusinessFlow — Quick Run & Demo Guide

> **Purpose:** This is the fastest repeatable setup guide for running the complete BusinessFlow project locally whenever someone asks to check/demo the project.
>
> **Project:** BusinessFlow — Business Management System  
> **Developer:** Rana Umar Draz  
> **Stack:** PHP 8.2 + MariaDB/MySQL (XAMPP) + HTML/CSS/JS

---

## 1. Requirements

Install/start:

- XAMPP
- Apache
- MySQL/MariaDB
- PHP 8.2.x (included with XAMPP)

Current known PHP executable:

```powershell
C:\xampp\php\php.exe
```

Current database:

```text
Host: localhost
Port: 3307
Database: bms_db
User: root
Password: empty
Charset: utf8mb4
```

> **Important:** This project uses **MySQL/MariaDB port 3307**, not the default 3306.

---

# 2. Project Location

Recommended local location:

```text
C:\xampp\htdocs\BusinessFlow-Final (1)\bms
```

Expected structure:

```text
bms\
├── api\
├── assets\
├── classes\
├── config\
├── dashboard\
├── database\
├── includes\
├── about.php
├── contact.php
├── index.php
├── login.php
├── logout.php
├── pricing.php
├── register.php
├── services.php
├── README.md
├── INSTALLATION.md
├── PROJECT_STRUCTURE.md
├── BUG_FIX_REPORT.md
├── FINAL_CHANGELOG.md
└── TEST_REPORT.md
```

---

# 3. Start XAMPP

Open **XAMPP Control Panel**.

Start:

```text
Apache
MySQL
```

Make sure both show **Running**.

---

# 4. Verify PHP

Open PowerShell:

```powershell
C:\xampp\php\php.exe -v
```

Expected:

```text
PHP 8.2.x
```

### If `php -v` says "php is not recognized"

That is only a PATH issue.

Use:

```powershell
C:\xampp\php\php.exe -v
```

Do NOT waste time reinstalling PHP.

---

# 5. Verify PDO MySQL

Run:

```powershell
C:\xampp\php\php.exe -m | Select-String "PDO|pdo_mysql"
```

Expected:

```text
PDO
pdo_mysql
```

---

# 6. Verify Database Server

Run:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -P 3307 -e "SELECT VERSION();"
```

Expected output contains a MariaDB version such as:

```text
10.4.x-MariaDB
```

If this command works, MariaDB is reachable on port **3307**.

---

# 7. Verify Database Exists

Run:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -P 3307 -e "SHOW DATABASES;"
```

Confirm:

```text
bms_db
```

exists.

---

# 8. Database Configuration

Open:

```text
bms\config\config.php
```

The database section must contain:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');
define('DB_NAME', 'bms_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

### IMPORTANT

Keep:

```text
localhost
```

for this working local setup.

During troubleshooting we found:

```text
127.0.0.1:3307
```

could be refused by PDO on this machine, while:

```text
localhost:3307
```

successfully connected.

So **do not casually change `localhost` back to `127.0.0.1`.**

---

# 9. Test PHP → Database Connection

From the project folder:

```powershell
cd "C:\xampp\htdocs\BusinessFlow-Final (1)\bms"
```

Then:

```powershell
C:\xampp\php\php.exe -r "require './config/config.php'; `$db = Database::getInstance(); echo 'DATABASE CONNECTED';"
```

Expected:

```text
DATABASE CONNECTED
```

If you get this, the PHP application can connect to the database.

---

# 10. If the Database Is Missing

First inspect SQL files:

```powershell
Get-ChildItem ".\database" -Filter *.sql -Recurse
```

Then use the appropriate SQL file from the project's `database` folder to initialize `bms_db`.

Example pattern:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -P 3307 bms_db < ".\database\YOUR_SQL_FILE.sql"
```

Replace `YOUR_SQL_FILE.sql` with the actual SQL filename.

> Do not randomly execute multiple SQL files if you are unsure of their purpose. Check the project README/INSTALLATION instructions first.

---

# 11. Run the Website

The project is served by **Apache**, not by `php -S`.

Open:

```text
http://localhost/BusinessFlow-Final%20(1)/bms/
```

Dashboard:

```text
http://localhost/BusinessFlow-Final%20(1)/bms/dashboard/index.php
```

Login:

```text
http://localhost/BusinessFlow-Final%20(1)/bms/login.php
```

> The `%20` represents the space in `BusinessFlow-Final (1)`.

### Better long-term option

For a cleaner URL, rename the folder to:

```text
BusinessFlow-Final
```

Then use:

```text
http://localhost/BusinessFlow-Final/bms/
```

If you rename the folder, re-check `SITE_URL`/`BASE_PATH` behavior before the interview.

---

# 12. Fast Demo Checklist

When someone says:

> "Show me your BusinessFlow project."

Do this:

### A. Start XAMPP

```text
Apache → Start
MySQL → Start
```

### B. Open project

```text
http://localhost/BusinessFlow-Final%20(1)/bms/
```

### C. Login

Use the valid local test/user account already configured in the database.

### D. Quick smoke test

Open:

1. Dashboard
2. Clients
3. Projects
4. Tasks
5. Calendar
6. Invoices
7. Expenses
8. Reports
9. HR & Staff
10. Profile
11. Settings

---

# 13. Features Already Tested

The following workflows were manually tested successfully:

## Authentication
- Login
- Dashboard access
- Invalid credentials handling
- Session-based dashboard access

## Clients
- Client list
- Add Client
- Edit Client
- Status update
- Search/filter behavior

## Projects
- Project creation
- Client relationship
- Budget
- Start/deadline dates
- Status
- Priority
- Progress
- Project details
- Project team

## Tasks
- Task creation
- Project relationship
- Team-member assignment
- Task status workflow
- Search
- Project filter
- Assignee filter
- Board/status display

## Calendar
- Task due dates
- Project deadlines
- Invoice due dates
- Month navigation
- Cross-module date integration

## Invoices
- Invoice creation
- Client relationship
- Related project
- Line items
- Quantity
- Unit price
- Tax
- Total calculation
- Invoice view
- Draft → Sent
- Paid/outstanding reporting
- Calendar due-date integration

## Expenses
- Expense creation
- Category
- Amount
- Project relationship
- Payment method
- Pending/Approved status
- Approval workflow
- Category/status filters
- Expense summary
- P&L integration

## Reports
- Paid invoice revenue
- Approved expenses
- Net profit/loss
- Projects by status
- Clients by industry
- Expenses by category
- Invoice status breakdown
- Top clients by revenue
- Revenue trend

## HR & Staff
- Employee directory
- Department filter
- Employee status
- Employee/project-team relationship

## Profile
- Profile page verified

## Settings
- Settings page verified

---

# 14. Important Issues Already Investigated / Resolved

### Issue 1 — `DB_PORT` appeared undefined

The config initially appeared not to contain the port constant.

Verification command:

```powershell
Get-Content ".\config\config.php" | Select-String "DB_"
```

Confirmed:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

Then:

```powershell
C:\xampp\php\php.exe -r "require './config/config.php'; echo 'PORT=' . DB_PORT . PHP_EOL;"
```

returned:

```text
PORT=3307
```

So the constant is working.

---

### Issue 2 — PDO refused `127.0.0.1:3307`

This failed:

```text
mysql:host=127.0.0.1;port=3307
```

with:

```text
SQLSTATE[HY000] [2002]
No connection could be made because the target machine actively refused it
```

But this worked:

```text
mysql:host=localhost;port=3307
```

and returned:

```text
PDO LOCALHOST CONNECTED
```

Therefore the working configuration is:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');
```

---

### Issue 3 — Project was in Desktop instead of htdocs

The final working Apache location is:

```text
C:\xampp\htdocs\BusinessFlow-Final (1)\bms
```

The project must be inside XAMPP's `htdocs` directory for the normal Apache URL.

---

### Issue 4 — `php` command not recognized

This:

```powershell
php -v
```

may fail because PHP is not in Windows PATH.

Use:

```powershell
C:\xampp\php\php.exe -v
```

instead.

---

# 15. Important Testing Notes

During testing, some records were intentionally created as test data.

Examples include:

```text
Test Business Ltd
Test Analytics Platform
Prepare Sales Analysis
Final System Testing
BusinessFlow System Testing
```

Before a polished public demo, decide whether these test records should remain as demo data or be removed/reset.

Do not delete original/sample business records blindly.

---

# 16. GitHub Checklist

Before pushing the project:

### Do NOT commit

```text
.env
real passwords
API keys
database passwords
private credentials
personal secrets
large generated/cache folders
```

Check:

```powershell
Get-Content ".\.gitignore"
```

Then:

```powershell
git status
```

Review everything before committing.

---

# 17. GitHub Commands

From:

```text
C:\xampp\htdocs\BusinessFlow-Final (1)\bms
```

run:

```powershell
git init
git add .
git status
git commit -m "Final BusinessFlow Business Management System"
```

Then connect your GitHub repository:

```powershell
git remote add origin YOUR_GITHUB_REPOSITORY_URL
git branch -M main
git push -u origin main
```

Replace:

```text
YOUR_GITHUB_REPOSITORY_URL
```

with the actual repository URL.

---

# 18. Interview Demo — 2 Minute Flow

If an interviewer asks you to demonstrate the system:

1. Open Login
2. Login
3. Show Dashboard
4. Open Clients → show client management
5. Open Projects → show project/team/tasks
6. Open Calendar → show integrated deadlines
7. Open Invoices → show invoice lifecycle
8. Open Expenses → show approval workflow
9. Open Reports → show live P&L
10. Open HR & Staff → show employee directory
11. Mention Profile/Settings
12. Explain that the system is database-driven and modules are integrated

### Strong one-line explanation

> "BusinessFlow is a PHP-based business management system that centralizes clients, projects, tasks, invoices, expenses, employees, calendar events and live financial reporting in one database-driven application."

---

# 19. Emergency Troubleshooting

### Website gives database connection error

Check:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -P 3307 -e "SELECT VERSION();"
```

Then:

```powershell
C:\xampp\php\php.exe -r "require './config/config.php'; `$db = Database::getInstance(); echo 'DATABASE CONNECTED';"
```

### Website gives PHP syntax error

Run:

```powershell
C:\xampp\php\php.exe -l ".\config\config.php"
```

For another PHP file:

```powershell
C:\xampp\php\php.exe -l ".\path\to\file.php"
```

### Apache page not opening

Check:

- Apache is running
- Project is inside `C:\xampp\htdocs`
- URL matches the actual folder name
- `bms\index.php` exists

### Database connection works in CLI but website fails

Check:

```text
bms\config\config.php
```

and make sure the web application is using:

```php
DB_HOST = localhost
DB_PORT = 3307
DB_NAME = bms_db
DB_USER = root
DB_PASS = ''
```

---

# 20. Final Golden Rule

**Do not redesign the project just because you encounter a small bug.**

For future maintenance:

1. Reproduce the issue.
2. Identify the exact file/function.
3. Make the smallest safe fix.
4. Do not change UI/UX unnecessarily.
5. Re-test the affected workflow.
6. Re-test related integrations.
7. Update `BUG_FIX_REPORT.md` and `FINAL_CHANGELOG.md` when appropriate.

This project is already structured as a working Business Management System. Preserve the existing UI/UX unless a change is specifically required.

---

## Quickest possible startup

If everything is already installed and the database is ready:

```text
1. Start XAMPP Apache
2. Start XAMPP MySQL
3. Open:
   http://localhost/BusinessFlow-Final%20(1)/bms/
4. Login
5. Demo
```

**That's it.**
