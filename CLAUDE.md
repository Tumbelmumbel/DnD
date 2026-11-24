# CLAUDE.md – DnD Character Manager

Rules and background for AI assistants working on this repository.
Read this file completely before you change anything.

## Project

A web application to create and manage Dungeons & Dragons characters.
Test site: https://rudolph.cologne (IONOS shared hosting).
`rudolph.cologne` is the **test environment of the owner**. It has no real users.

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
| `rate_limit.php` | Limits for sign-in, registration, new verification links and password resets (table `rate_limit`) |
| `login.php`, `register.php`, `verify.php`, `resend.php`, `ForgottenPW.php`, `reset.php`, `logout.php` | User accounts |
| `characteroverview.php` | List of the user's characters (start page after login) |
| `CharacterSandbox.php` | Character sheet |
| `calculation.js` | D&D calculations (modifiers) |
| `i18n.php`, `lang/*.php`, `ajax/i18n.php` | Languages of the interface (see "Languages") |
| `partials/language_footer.php` | Language menu at the end of every page (bottom right) |
| `partials/page_head.php` | Shared `<head>` of the account pages (title, viewport, `style.css` with a version number). Set `$pageTitle` before you include it. |
| `content_translation.php` | Names and texts of the game content in the current language (`lookup_list()`, `talent_list()`, ...) |
| `ajax/sandbox_endpoint.php` | JSON endpoint for the character sheet (`getSubclasses`, `getHitDice`, `getComponent`, `getContentNames`) |
| `Assets/style.css` | Shared stylesheet for all pages |
| `libs/PHPMailer/` | PHPMailer library (third-party, LGPL) |
| `database_description.md` | Description of all tables and fields (keep up to date) |
| `migration_*.sql` | Database changes, run by hand in phpMyAdmin |
| `index.php` | Start page: sends the visitor to the sign-in page |
| `.htaccess` | Sets `index.php` as the start page. Blocks downloads of `.env`, logs and other non-public files. Do not remove it. |
| `.github/workflows/deploy.yml` | Uploads `main` to IONOS over SFTP |
| `.github/workflows/claude.yml` | Claude review agent on GitHub: answers `@claude` comments (review only) |

## Git workflow

- **Never commit or push directly to `main`.** Every push to `main` deploys to the website.
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
  4. **Test** – the list of test steps for the browser test (see "Test steps in the pull request").

## Database changes

- Never change the database without the owner's explicit "yes" for that exact SQL.
- Read-only queries (SELECT, SHOW, information_schema) are fine.
- Every schema change goes into a new file `migration_<short_name>.sql` in the pull request.
- The pull request description must say: **"Run the migration BEFORE you merge."**
- Code must not use a new column or table before its migration exists.
- The current schema has no foreign keys and `sql_mode` is empty (lenient mode).
- Code refers to a lookup value (class, species, skill, condition, ...) only by its `code`, never by its name.
  The name is only for display.

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
- `.htaccess` blocks access to dot files (`.env`) and to `*.log`, `*.ini`, `*.sql`, `*.md`, `*.example`.
  Do not remove or weaken these rules.
- Use prepared statements for all SQL. Never put user input directly into SQL.
- Escape all output with `e()`.
- Every form that changes data needs a CSRF token (`csrf_token()` / `csrf_validate()`).
- Protected pages call `guard_protect()` at the top.
- Do not show PHP errors to users in production.
- Messages must not reveal whether an email address is registered.
- Pages that send emails or check passwords use the rate limit (`rate_limit.php`). The message is always `err.too_many`.

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
6. **Browser test:** after the deploy, Claude tests the change on the website (see "Browser test").

### Review and merge

**Every pull request gets a review by the GitHub review agent** (`.github/workflows/claude.yml`).

1. After Claude opens a pull request, Claude writes this comment on the pull request:

   `@claude Please check this pull request. Approve if you find no errors. Disapprove if you find errors.`

2. Claude waits for the answer of the review agent (normally 1 to 5 minutes).
   The answer contains the line `VERDICT: APPROVE` or `VERDICT: DISAPPROVE`.
3. **`VERDICT: APPROVE`:** Claude merges the pull request (merge commit) and deletes the branch.
   The merge starts the deploy to the website.
4. **`VERDICT: DISAPPROVE`:** Claude corrects the errors on the same branch, pushes the change
   and writes the review comment again.
   After 3 reviews with `DISAPPROVE`, Claude stops and asks the owner.
5. After the merge, Claude does the browser test (see "Browser test").

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

### Test steps in the pull request

**Each pull request has a section "Test" with a numbered list of test steps.**
Each test step has:

- **Action:** what to do (for example: "Open `/login.php`. Click 'Register'.").
- **Expected result:** what must happen (for example: "The page `/register.php` opens.").
- **(owner)** after the step number, if the owner must do the step (sign-in, password, email).

Example:

```
1. Action: Open https://rudolph.cologne/login.php.
   Expected result: The page shows the form "Sign in".
2. (owner) Action: Sign in with your account.
   Expected result: The page /characteroverview.php opens.
```

A pull request that changes only documentation (for example `*.md`) has the text
"No test steps: documentation only." in the section "Test".

### Browser test

**After each merge, Claude runs the test steps of the pull request automatically.**
Claude does not wait for a request from the owner.

1. Claude waits until the deploy is finished (about 2 minutes after the merge).
2. Claude opens https://rudolph.cologne in the **browser of the Claude desktop app**.
   The desktop app must be open on the computer of the owner.
3. Claude does the test steps of the pull request, one after the other.
   For each step, Claude compares the actual result with the expected result.
   For a step with **(owner)**, Claude asks the owner and waits.
4. Claude reports the result of each step in the chat ("passed" or "failed").

**All test steps passed:** Claude writes this comment on the pull request:

`Tests carried out without errors`

**At least one test step failed:** Claude opens a **GitHub issue** with the title
`Test failure: #<number> <title of the pull request>` and these parts:

1. **Pull request:** the number and the link of the tested pull request.
2. **Expected result:** the expected result of each failed step.
3. **Actual result:** what happened in the browser.
4. **Steps:** the steps that Claude did to get the result (from the start of the test).
5. **Possible causes:** the possible causes of the failure (file and code, if known).

Claude also writes a comment with the link to the issue on the pull request.
Then Claude makes a correction with the normal procedure (plan, branch, pull request, review, merge, test).

**Exceptions:**

- **Security-relevant failure:** Claude does not describe it on GitHub (see "Security-relevant issues").
  The issue has only the title, the pull request and the text "Security-relevant failure. Details privately."
  Claude gives the details to the owner in the chat.
- **A step was not done** (for example, the owner was not available for an **(owner)** step):
  Claude does not write "Tests carried out without errors". Claude writes a comment with
  the list of the steps that were not done.
- **No test steps (documentation only):** no browser test, no comment.

**Passwords and accounts:**

- Claude does not type passwords on `rudolph.cologne` and does not make accounts there.
- If a test step needs a sign-in, a registration or a new password, Claude asks the owner.
  The owner does this step in the browser pane. Then Claude continues with the next step.
- The sign-in stays in the browser of the desktop app. Claude can then test pages that need a sign-in.
- Claude does not sign out of the account of the owner and does not change its data,
  unless a test step needs it and the owner agrees.
- Emails (verification, password reset) go to the mailbox of the owner.
  For these steps, the owner opens the link or gives it to Claude.

## Languages

- **English (`en`) is the base language.** Every text key must exist in `lang/en.php`.
  Other languages (now `de`) can leave out keys: then the English text is shown.
- **No fixed interface text in the HTML, in messages or in emails.** Use `t('key')` in PHP and `tr('key')` in JavaScript.
  Emails use the language of the person who sends the request.
- On a page that changes its texts without a reload (now `CharacterSandbox.php`), also mark the element with
  `data-i18n="key"` (text), `data-i18n-aria` (aria-label), `data-i18n-placeholder` or `data-i18n-title`.
  Other pages reload after a change of the language and need only `t()`.
- Add every new key to `lang/en.php` and `lang/de.php` in the same branch.
- To add a language: add `lang/<code>.php` and one entry in `i18n_languages()` in `i18n.php`.
- The selected language is in the session, in the cookie `lang` and in `users.language`.
- **Every page includes `partials/language_footer.php` directly before `</body>`.** A page that can change its texts
  in place defines `window.onLanguageChanged(data)`; other pages reload after a change of the language.
- **Content from the database** (classes, species, conditions, talents, ...) comes from the tables `Lookup_translation`
  and `Game_component_library_translation` (functions in `content_translation.php`). A missing text falls back to English.
  Each new value or talent needs an English row, and a German row is recommended (same migration).
- **Items:** the English name is in `items.name` (unique), other languages are in `items_translation` (unique per language).
  Tags come only from the table `tags` (names in `Lookup_translation`, type `item_tag`). Only a migration adds tags.
  Logic for a tag uses the tag code, never its name.
- On `CharacterSandbox.php`, a list with game content has `data-lookup="<type>"`. After a change of the language,
  the page loads `getContentNames` and renames the entries in place.

## Communication

- Explain in simple English. The owner is learning Git and web development.
- Say clearly what the owner must do by hand (for example, run SQL, add a secret).

## Known open issues

- `CharacterSandbox.php` does not save data yet. The `Characters` table stores only
  name, owner, image and the six ability scores.
