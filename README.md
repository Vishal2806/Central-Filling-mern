# Court E-Filing Registry — Core PHP Conversion

This project has been converted from a React + Node/Express + PostgreSQL application into a plain PHP application using PostgreSQL.

## Files
- `config.php` — shared database connection and session initialization
- `auth.php` — authentication helpers and login/session management
- `login.php` — login form and credential validation
- `logout.php` — session logout
- `index.php` — authenticated dashboard
- `records.php` — record list, search, and status filter
- `add_record.php` — create new filing records
- `record_details.php` — view details, timeline, and update status
- `includes/header.php` / `includes/footer.php` — shared page layout
- `styles.css` — UI styles

## Setup
1. Configure PostgreSQL connection in `config.php`.
2. Start PostgreSQL and make sure the configured user can create/use databases.
3. Place the project in a PHP-enabled web root.
4. Open `setup.php` once. It creates the `efile_db` database when missing, creates the required tables, and seeds the demo admin user.
5. Open `login.php` and sign in.

## Notes
- Authentication uses PHP sessions.
- Password validation uses `password_verify()`.
- `records.php` supports searching by case number or advocate name and filtering by status.
- `record_details.php` preserves history and increments `total_returns` when status becomes `RETURNED`.
