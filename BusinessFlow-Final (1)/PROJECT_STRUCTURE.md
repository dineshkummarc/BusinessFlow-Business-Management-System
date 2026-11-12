# PROJECT_STRUCTURE.md — BusinessFlow

This document reflects the **actual, current file structure** of the project as it exists in this ZIP — nothing described here is planned or hypothetical.

```
BusinessFlow-Final/
│
├── .gitignore                  # Keeps OS/editor/log files out of version control
├── README.md                   # Main project documentation (start here)
├── INSTALLATION.md             # Step-by-step XAMPP/Windows setup
├── TEST_REPORT.md              # Full test coverage and verification status
├── BUG_FIX_REPORT.md           # Root-cause analysis and fixes from this audit
├── PROJECT_STRUCTURE.md        # This file
├── FINAL_CHANGELOG.md          # Concise summary of this audit's changes
│
├── index.php                   # Public homepage
├── about.php                   # Public "About" page
├── services.php                # Public "Services" page (module overview)
├── pricing.php                 # Public "Pricing" page
├── contact.php                 # Public contact form (writes to contact_messages)
├── login.php                   # Login page (session auth, CSRF, remember-me)
├── register.php                # Public registration page (always creates 'staff' role)
├── logout.php                  # Session/remember-me token destruction
│
├── config/
│   ├── config.php               # Constants, DB credentials, session bootstrap,
│   │                             # BASE_PATH resolution, autoloader, remember-me hook
│   └── .htaccess                 # Blocks direct browser access to this folder
│
├── classes/                     # OOP backend — one class per business concern
│   ├── Database.php              # PDO singleton — the ONLY DB connection point in the app
│   ├── Auth.php                   # Login/register/session/CSRF/RBAC/remember-me
│   ├── User.php                    # Login accounts + admin user management
│   ├── Client.php                   # CRM module (includes delete-safety guard)
│   ├── Employee.php                  # HR module (includes user-account linking)
│   ├── Project.php                    # Project management module
│   ├── Task.php                        # System-wide task management (Kanban)
│   ├── Invoice.php                      # Billing module (includes auto-overdue detection)
│   ├── Expense.php                       # Financial/expense tracking module
│   ├── ContactMessage.php                 # Public contact-form submissions
│   ├── Notification.php                    # In-app notification center
│   ├── Settings.php                         # Company-wide key/value configuration
│   ├── ActivityLog.php                       # Audit trail of key actions
│   ├── Validator.php                          # Input validation (required, email, length,
│   │                                           # numeric, range, date-order rules)
│   ├── Flash.php                               # Post/Redirect/Get flash messages
│   └── .htaccess                                # Blocks direct browser access to this folder
│
├── dashboard/                    # Authenticated application area
│   ├── index.php                  # Dashboard overview / live stats
│   ├── clients.php                 # CRM — list/create/edit/delete
│   ├── employees.php                # HR — list/create/edit/delete + login-account linking
│   ├── projects.php                  # Projects — list/create/edit/delete
│   ├── project_view.php               # Single project detail: team + task list
│   ├── tasks.php                       # System-wide Kanban task board
│   ├── calendar.php                     # Month view: task/project/invoice due dates
│   ├── invoices.php                      # Invoices — list/create (with line items)/delete
│   ├── invoice_view.php                   # Single invoice — printable detail view
│   ├── expenses.php                        # Expense tracking — list/create/edit/delete
│   ├── messages.php                         # Contact-form submissions inbox
│   ├── reports.php                           # Live analytics + Profit & Loss
│   ├── profile.php                            # Current user's own profile/password
│   ├── users.php                               # Admin-only: manage login accounts/roles
│   ├── settings.php                             # Admin-only: company-wide configuration
│   └── notifications_read.php                    # Handler: mark all notifications read
│
├── includes/                     # Shared layout partials, included by other pages
│   ├── header.php                 # Public site: opens <html>, navbar
│   ├── footer.php                  # Public site: footer + closes </html>
│   ├── dash_header.php              # Dashboard: opens <html>, sidebar, topbar,
│   │                                 # notification dropdown, quick-nav search
│   ├── dash_footer.php               # Dashboard: closes layout, loads scripts
│   ├── sidebar.php                    # Dashboard sidebar navigation (role-aware)
│   └── badge_status.php                # Reusable colored status badge (shared by
│                                         # every module's tables)
│
├── assets/
│   ├── css/
│   │   ├── style.css              # Public site design system (navy/gold, cards, hero)
│   │   └── dashboard.css           # Dashboard design system (sidebar, Kanban, calendar,
│   │                                # quick-nav dropdown, tables, badges)
│   ├── js/
│   │   ├── main.js                 # Public site: scroll-reveal, navbar effects, counters
│   │   └── dashboard.js             # Dashboard: sidebar toggle, delete confirmation,
│   │                                 # live table search, invoice line-item calculator,
│   │                                 # Kanban drag-and-drop, quick-nav search
│   └── images/
│       ├── logo-full.png            # Official logo (monogram + wordmark), transparent
│       ├── logo-mark.png             # Official logo (monogram only, compact spaces)
│       └── favicon.png, favicon-32.png, favicon-180.png   # Generated from the logo
│
├── database/
│   ├── schema.sql                 # Full schema (14 tables) + seed data — see below
│   └── .htaccess                   # Blocks direct browser access to this folder
│
└── api/
    └── README.md                  # Explains this folder is reserved for a future
                                     # AJAX/JSON API phase (currently empty by design)
```

## What each top-level folder is *for*

- **`config/`** — the only place database credentials and site-wide constants live. Every other file gets these via `require_once __DIR__ . '/config/config.php'` (or `../config/config.php` from `dashboard/`).
- **`classes/`** — the entire backend. If a page needs to touch the database, it does so exclusively through one of these classes — no page ever writes raw SQL inline against `$_POST` data directly.
- **`dashboard/`** — every page here starts with `Auth::requireLogin()` (or `Auth::requireRole()` for the two admin-only pages) and is otherwise a self-contained controller+view: validate input → call a class method → render HTML.
- **`includes/`** — pure presentation partials with no business logic of their own; they read variables the including page already prepared.
- **`assets/`** — no build step, no bundler — these are the literal files the browser requests.
- **`database/`** — a single SQL file is the entire database setup story; see README.md and INSTALLATION.md for how to import it.
- **`api/`** — intentionally empty except for a README explaining it's reserved; not a broken/missing feature, a documented placeholder for future work.

## Files present in this ZIP that are new since the previous delivery

This audit added exactly two new files and modified two existing ones (see `BUG_FIX_REPORT.md` and `FINAL_CHANGELOG.md` for details):
- **New:** `.gitignore`
- **New:** `BUG_FIX_REPORT.md`, `TEST_REPORT.md`, `PROJECT_STRUCTURE.md`, `INSTALLATION.md`, `FINAL_CHANGELOG.md` (documentation only)
- **Modified:** `config/config.php`, `classes/Database.php` (the database connectivity fix)

No file was removed, renamed, or redesigned.
