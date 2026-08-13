# API Directory (Reserved for Later Phases)

This folder is intentionally reserved. Phase 1 implements all functionality
through direct PHP page requests using the PRG (Post/Redirect/Get) pattern,
so no AJAX/JSON API endpoints were required yet.

Future phases may add lightweight JSON endpoints here (e.g. `clients.php`,
`invoice_status.php`) for AJAX-driven UI updates, calendar widgets, or a
public API — without disturbing the existing dashboard pages, which will
keep working exactly as they do now.
