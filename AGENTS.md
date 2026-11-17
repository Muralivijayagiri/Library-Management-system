# AGENTS.md

## Project shape
Classic XAMPP PHP/MySQL app (PHP 8.2, `mysqli`). No Composer/npm, build step, tests, lint, or CI. Each page is a standalone PHP script with inline HTML/CSS/JS; the only shared layout partials are `admin/sidebar.php` and `student/sidebar.php` (`includes/` is empty).

`admin/dashboard.php` and `student/dashboard.php` are redirect shims; the real dashboards are `admin/admin_dashboard.php` and `student/student_dashboard.php`.

## Run and verify
- Start Apache + MySQL in XAMPP, then open `http://localhost/Library_Managment/`. The folder name/spelling matters: `config/config.php` and the document viewers hardcode `/Library_Managment/`.
- Database is `library_management` with user `root` and a blank password, hardcoded in `config/db.php`. There is no `.env` or `config.local.php` loading. The README's `library_db` name is wrong.
- Create the schema from `config/database.sql`, then apply `config/migrations/*.sql` manually; there is no migration runner.
- Create `uploads/{book_covers,audio,audio_images,documents,student_profiles}`. `uploads/` is gitignored, so a fresh clone has none, and most upload handlers do not `mkdir`.
- PHP is not on PATH. Syntax-check changed files with: `& 'C:\xampp\php\php.exe' -l path\to\file.php`
- No automated test suite; verify by loading the affected pages in a browser and inspecting the DB.

## Schema drift (important)
`config/database.sql` is stale and not idempotent. Checked-in schema/migrations are missing columns the code uses:
- `students.branch`, `students.email`, `students.mobile` (`admin/add_student_ajax.php`, profiles)
- `borrow_records.expected_return` (SQL migration exists)
- `audio_books.audio_image` (patched by `admin/add_audio_image_column.php`)
- `chat_messages.seen_status` (used by chat queries; no SQL migration creates it)

The file also ends with `ALTER TABLE books ADD total_copies` even though the table already defines that column, so importing it wholesale can end with a duplicate-column error.

Treat `DESCRIBE` / the live database as the source of truth. These are one-off maintenance scripts, not features: `admin/add_audio_image_column.php`, `admin/fix_document_paths.php`, `chat/create_table.php`, `chat/verify_setup.php`.

## Auth conventions
- Pages call `session_start()` and gate on `$_SESSION['admin_logged_in']` or `$_SESSION['student_logged_in']`. HTML pages redirect to `../login.php`; JSON endpoints return `{"status":"error","message":"Unauthorized"}`.
- Admin passwords are SHA-256 hex only (`hash('sha256', ...)` and SQL `SHA2`). Student passwords are bcrypt via `password_hash()`, with a legacy SHA-256 fallback in login/reset. Do not switch admins to bcrypt without updating every admin password check.
- `admin/get_book.php` is intentionally shared by both roles; the student catalog calls it via `../admin/get_book.php`.

## Circulation rules
- Availability is computed, not stored: `total_copies - COUNT(borrow_records WHERE actual_return_date IS NULL)`.
- A due date is written to both `return_date` and `expected_return`. Dashboard APIs use `return_date` for overdue checks; `admin/manage_books.php` uses `expected_return`. Keep both columns in sync.

## Chat subsystem
- Active chat uses `chat_messages` via `chat/send_message.php` (both roles) and `chat/fetch_messages.php`. Admin UI: `admin/chat_inbox.php` / `admin/chat_view.php`; student UI: `student/student_chat.php`.
- `chat/fetch_messages.php`, `chat/check_unread*.php`, and `admin/chat_inbox.php` require `chat_messages.seen_status`; verify it exists in the live DB before debugging chat.
- Legacy `messages`-table endpoints `admin/send_message.php`, `admin/mark_seen.php`, and `student/send_message.php` are not wired to the UI. `CHAT_SYSTEM_*.md` files are historical diagnostics and partly stale; trust the current code.

## PDF viewer
PDF previews are embedded from the vendored PDF.js distribution at `pdfjs/web/viewer.html`. Do not hand-edit `pdfjs/` unless intentionally updating that library.
