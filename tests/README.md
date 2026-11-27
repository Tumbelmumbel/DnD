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
2. **test-scope** checks who changed the tests. A commit of the Test Agent (author `github-actions[bot]`, signed by GitHub) may change only `tests/browser/`.
   Any other commit may not change `tests/` (only `tests/README.md`). The label `owner-test-change` (owner only) allows it once.
3. **php-lint** runs `php -l` on every PHP file (without `libs/`).
4. **engine** runs `node --test tests/engine/*.test.js`.
5. **database** loads the baseline, runs `php migrate.php`, checks the row counts and runs the self-test.
6. **browser** starts the PHP built-in server and runs `pytest tests/browser`. On failure, the screenshots and the server log are a download of the run.
7. **gate** is green only if all tests of the category passed. Branch protection requires this job.

## Test Agent

The workflow `.github/workflows/claude-test.yml` starts the Test Agent. Start: a comment on the pull request that begins with `/test-agent` (owner's account only).

- It reads only the PR section "Requirement and behavior", `CLAUDE.md`, `database_description.md`, `tests/` and the running page.
- It does not see the code diff. The code runs in `/tmp/app` for the PHP server only.
- It writes only in `tests/browser/`.
- The workflow has two jobs. The job `agent` runs the code (as the separate system user `appsrv`, in `/tmp/app`) and the agent, with read permissions only. The job `commit` runs no code of the pull request: it takes only `tests/browser/` as data, creates the commit through the GitHub API and starts the workflow "Tests" again.
- It writes its report as a comment on the pull request.
- It never weakens, skips or deletes a test to make it pass. The workflow never deletes an existing test file, and it lists changed existing test files in the report.
- If the Dev Agent thinks that a test is wrong: one round of discussion in PR comments, then the owner decides.

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
