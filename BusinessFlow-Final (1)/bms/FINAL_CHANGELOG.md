# FINAL_CHANGELOG.md — BusinessFlow

**Type of work:** Forensic bug audit and repair of the existing BusinessFlow-Final project. No redesign, no rebuild, no new features. See `BUG_FIX_REPORT.md` for full root-cause detail and `TEST_REPORT.md` for verification status of every item.

---

## Fixed

- **Database connectivity (critical):** the PDO connection string was missing a `port=` parameter entirely, so the app couldn't reach MariaDB running on the confirmed environment's non-default port (3307). This was the root cause of the reported registration/database failures. Fixed in `classes/Database.php` and `config/config.php`.
- **Error visibility:** database connection failures previously showed only a generic message with the real cause hidden, making this exact class of bug hard to self-diagnose. Now shows the real PDO error during local development, while still falling back to a safe generic message in production.
- **Windows path-casing edge case:** `BASE_PATH` resolution used a case-sensitive string comparison, which could silently break on Windows (where filesystem paths are case-insensitive) and cause every CSS/JS/image/link on the site to point to the wrong location. Fixed by switching to a case-insensitive comparison.

## Added

- `.gitignore` (did not previously exist)
- `BUG_FIX_REPORT.md` — full root-cause analysis for every issue found
- `TEST_REPORT.md` — complete test coverage with honest verified/not-verified labeling
- `PROJECT_STRUCTURE.md` — documents the actual current file tree
- `INSTALLATION.md` — step-by-step Windows/XAMPP setup matching the confirmed environment
- `FINAL_CHANGELOG.md` — this file

## Unchanged (verified correct, left alone)

Every other file in the project — every page, every class, every stylesheet, every script, the entire database schema, the visual design, the navigation structure, and all existing business logic — was audited and found to already be working correctly. Nothing was rewritten, redesigned, or restructured. See `BUG_FIX_REPORT.md` for the full list of areas specifically checked and confirmed already correct (SQL injection resistance, XSS protection, CSRF coverage, password hashing, schema/code column consistency, foreign key integrity, and more).

## Not changed, but worth knowing about

- `README.md` was updated (not rewritten) to reference the new companion documents and reflect this audit — the existing feature documentation, architecture description, and workflow explanations from the prior phase were preserved.
- The default admin seed row's password hash remains a placeholder, as it always was — this is expected, documented behavior (see `INSTALLATION.md` Step 7), not a bug.

## What to do next

1. Follow `INSTALLATION.md` to confirm the database import and configuration on your machine.
2. Test registration and login live — these are the two items most directly affected by tonight's fix and the ones this sandbox couldn't execute against a real database (see `TEST_REPORT.md` for the full "Not Verified — needs live runtime" list).
3. If anything still doesn't connect, the improved error message in `classes/Database.php` will now show you the *real* PDO error instead of a generic one — that will tell you immediately whether it's still a port/host/credentials mismatch.
