# CLAUDE.md – DnD Character Manager

Rules and background for AI assistants working on this repository.
Read this file completely before you change anything.

## Project

A web application to create and manage Dungeons & Dragons characters.
Live site: https://rudolph.cologne (IONOS shared hosting).

- **Language:** PHP 8 (no framework), plain HTML/CSS/JavaScript
- **Database:** MySQL on IONOS (`dbs15432866`), accessed with PDO
- **Mail:** PHPMailer 7.0.2 over IONOS SMTP (in `libs/PHPMailer`, third-party code: do not change it)
- **Owner:** Tumbelmumbel (solo developer)

**No frameworks.** The project uses plain PHP and vanilla JavaScript on purpose.
Do not add a framework (for example Laravel, Symfony, React, Vue), Composer, npm or
a build step unless the owner asks for it. Small single-purpose libraries (like
PHPMailer) only after the owner agrees.

## Files

| File | Purpose |
|---|---|
| `config.php` | Loads `.env`, builds `$APP_CONFIG`. No secrets in this file. |
| `bootstrap.php` | Session, CSRF, `db()`, `guard_protect()`. Every page includes it. |
| `helper_functions.php` | Small helpers (`e()`, tokens, ...) |
| `tinymailer.php` | `send_mail()` |
| `login.php`, `register.php`, `verify.php`, `resend.php`, `ForgottenPW.php`, `reset.php`, `logout.php` | User accounts |
| `characteroverview.php` | List of the user's characters (start page after login) |
| `CharacterSandbox.php` | Character sheet |
| `calculation.js`, `calculation.php` | D&D calculations (modifiers) |
| `ajax/sandbox_endpoint.php` | JSON endpoint for the character sheet (`getSubclasses`, `getHitDice`) |
| `Assets/style.css` | Shared stylesheet for all pages |
| `libs/PHPMailer/` | PHPMailer library (third-party, LGPL) |
| `database_description.md` | Description of all tables and fields (keep up to date) |
| `migration_*.sql` | Database changes, run by hand in phpMyAdmin |
| `.github/workflows/deploy.yml` | Uploads `main` to IONOS over SFTP |
| `.github/workflows/claude.yml` | Claude review agent on GitHub: answers `@claude` comments (review only) |

## Git workflow

- **Never commit or push directly to `main`.** `main` is live: every push deploys to the website.
- **Each change gets its own new branch**, named after the change (for example `fix-resend-page`).
- Open a pull request into `main`. The GitHub review agent reviews it (see "Review and merge").
- Keep each pull request to one topic.
- Before starting new work, update from the latest `main`.

### Description of changes: ASD-STE100

Write the description of each change in **English ASD-STE100** (Simplified Technical English).
This applies to the **pull request description** and the **commit message**.

- Use short sentences: max. 20 words in procedures, max. 25 words in descriptions.
- One instruction or one topic per sentence.
- Use the active voice.
- Use approved STE words and technical names (file names, field names) only.
- Use this structure in the pull request:
  1. **Changes** – what changed, file by file.
  2. **Reason** – why the change is necessary.
  3. **Before you merge** – manual steps (for example: "Run the migration").
  4. **Test** – how the owner examines the change on the live site.

## Database changes

- Never change the live database without the owner's explicit "yes" for that exact SQL.
- Read-only queries (SELECT, SHOW, information_schema) are fine.
- Every schema change goes into a new file `migration_<short_name>.sql` in the pull request.
- The pull request description must say: **"Run the migration BEFORE you merge."**
- Code must not use a new column or table before its migration exists.
- The current schema has no foreign keys and `sql_mode` is empty (lenient mode).

### `database_description.md`

- This file lists **every table and every field**, with the data type and a description
  of the data it stores. It is the reference for the database structure.
- **Read it before you write code or SQL that uses the database.**
- **Every database change must update this file in the same branch**, also changes
  that the owner makes by hand in phpMyAdmin.
- Write the descriptions in ASD-STE100. Change the "Last update" date.
- Descriptions marked **(check)** are assumptions. Ask the owner before you rely on them.

## Security rules

- **No secrets in the repository.** Passwords, hosts and keys come only from `.env`.
  `.env` is on the server only; `.env.example` lists the keys without values.
- Use prepared statements for all SQL. Never put user input directly into SQL.
- Escape all output with `e()`.
- Every form that changes data needs a CSRF token (`csrf_token()` / `csrf_validate()`).
- Protected pages call `guard_protect()` at the top.
- Do not show PHP errors to users in production.
- Messages must not reveal whether an email address is registered.

### Security-relevant issues: direct contact only

The repository is public. Everything in GitHub (issues, pull requests, commits, comments) is visible to everyone.

- **Discuss security-relevant issues only via direct contact with the owner** (for example in a private chat).
- **Do not enter security-relevant issues as GitHub issues.** Do not post them as comments either.
- Do not add security-relevant issues to the list "Known open issues" in this file.
- For a security fix, write the pull request description and the commit message in neutral words.
  Describe what the change does. Do not describe how to attack the old code.
- Security-relevant means, for example: login, passwords, sessions, access to data of other users,
  secrets, SQL injection, XSS, CSRF, or a bug that lets a user do something that is not permitted.

## Code style

- Keep the existing style: `declare(strict_types=1);`, PDO with `db()`.
- **Keep CRLF line endings** in existing files. Do not convert whole files.
- Small, focused changes. Do not reformat code you do not change.
- Run `php -l` on every changed PHP file before committing.

## Deploy

- Merging into `main` starts the upload automatically (GitHub Actions → "Deploy to IONOS").
- The upload never deletes files on the server.
- Not uploaded: `.git/`, `.github/`, `.env*`, `*.log`, `*.sql`, `*.md`.
- Manual upload: Actions → Deploy to IONOS → Run workflow.

## Working method

1. **Plan:** describe the change and any SQL. Wait for the owner's approval.
2. **Build:** on a new branch.
3. **Pull request:** list what changed, what to test, and any steps before merging.
4. **Review:** the GitHub review agent reviews every pull request (see below).
5. **Merge:** if the review agent approves, Claude merges the pull request.
6. **Owner tests** the change on the live site.

### Review and merge

**Every pull request gets a review by the GitHub review agent** (`.github/workflows/claude.yml`).

1. After Claude opens a pull request, Claude writes this comment on the pull request:

   `@claude Please check this pull request. Approve if you find no errors. Disapprove if you find errors.`

2. Claude waits for the answer of the review agent (normally 1 to 5 minutes).
   The answer contains the line `VERDICT: APPROVE` or `VERDICT: DISAPPROVE`.
3. **`VERDICT: APPROVE`:** Claude merges the pull request (merge commit) and deletes the branch.
   The merge starts the deploy to the live website.
4. **`VERDICT: DISAPPROVE`:** Claude corrects the errors on the same branch, pushes the change
   and writes the review comment again.
   After 3 reviews with `DISAPPROVE`, Claude stops and asks the owner.
5. After the merge, Claude tells the owner what to test on the live site.

**Claude does not merge automatically when:**

- the section "Before you merge" of the pull request has a manual step that is not done
  (for example: a migration that the owner must run). Claude merges only after the step is done.
- the owner wrote "do not merge" on the pull request, or the pull request is a draft.
- the review agent did not answer, or the answer has no `VERDICT` line.
  Then Claude tells the owner.

**Security findings:** the review agent does not describe security weaknesses in public comments.
For a security finding, it writes only `VERDICT: DISAPPROVE – security finding, details privately`.
Then Claude does a private review in the chat with the owner to find the details,
and corrects the problem. The details stay out of GitHub.

## Communication

- Explain in simple English. The owner is learning Git and web development.
- Say clearly what the owner must do by hand (for example, run SQL, add a secret).

## Known open issues

- `CharacterSandbox.php` does not save data yet. The `Characters` table stores only
  name, owner, image and the six ability scores.
