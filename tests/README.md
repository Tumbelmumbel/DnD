# Automatic tests

The workflow `.github/workflows/tests.yml` runs these tests on every pull request.
The files in `tests/` are not uploaded to the website (see `deploy.yml`).

| Folder | What it tests | Tool |
|---|---|---|
| `baseline/` | `baseline.sql`: the database for the tests. Structure of all tables and the data of the game tables. **No user data.** | MySQL 8.0 |
| `engine/` | The calculation engine `Assets/calc_engine.js` with the real rules from the baseline. | Node.js, built-in test runner (no npm) |
| `browser/` | The pages in a headless browser: sign-in, character sheet, both languages, phone width, no JavaScript errors. | Python, Playwright, pytest |
| `migrations/` | A self-test for `migrate.php`. The workflow copies it next to `migrate.php` for one run. | MySQL 8.0 |

## Jobs of the workflow

1. **categorize** finds the category of the pull request from the changed files and sets the label `category: ...`.
   Only the categories `code`, `infra` and `tests` run the tests. The label `full-check` (owner only) forces all tests.
2. **php-lint** runs `php -l` on every PHP file (without `libs/`).
3. **engine** runs `node --test tests/engine/*.test.js`.
4. **database** loads the baseline, runs `php migrate.php`, checks the row counts and runs the self-test.
5. **browser** starts the PHP built-in server and runs `pytest tests/browser`. On failure, the screenshots and the server log are a download of the run.
6. **gate** is green only if all tests of the category passed. Branch protection requires this job.

## Run the tests on your own computer

You need PHP 8 with `pdo_mysql`, Node.js, Python 3 and a MySQL 8.0 test database (never the live database).

```
node --test tests/engine/*.test.js

mysql -u <user> -p <test_db> < tests/baseline/baseline.sql
php migrate.php
php -S 127.0.0.1:8000 -t .
pip install playwright pytest && python -m playwright install chromium
TEST_BASE_URL=http://127.0.0.1:8000 DB_USER=<user> DB_PASS=<password> DB_NAME=<test_db> pytest tests/browser -v
```

## Rules

- The baseline never contains user data (email addresses, password hashes, characters).
- A new migration needs no change in the baseline: the workflow runs it with `migrate.php`.
- Names of new migration files: `migration_YYYYMMDD_<short_name>.sql`.
