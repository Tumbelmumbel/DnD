"""Shared fixtures for the browser tests (Playwright, headless Chromium).

The tests run against the PHP built-in server and the test database of the
GitHub workflow. They never use rudolph.cologne or the live database.

Environment variables (set by .github/workflows/tests.yml):
  TEST_BASE_URL   for example http://127.0.0.1:8000
  DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS   the test database
"""
import os
import secrets
import subprocess

import pytest
from playwright.sync_api import sync_playwright

BASE_URL = os.environ.get("TEST_BASE_URL", "http://127.0.0.1:8000")


def run_sql(sql: str) -> str:
    """Run SQL on the test database with the mysql client and return the output."""
    cmd = [
        "mysql", "--protocol=tcp", "--batch", "--skip-column-names",
        "-h", os.environ.get("DB_HOST", "127.0.0.1"),
        "-P", os.environ.get("DB_PORT", "3306"),
        "-u", os.environ["DB_USER"],
        os.environ["DB_NAME"],
        "-e", sql,
    ]
    env = dict(os.environ, MYSQL_PWD=os.environ.get("DB_PASS", ""))
    return subprocess.run(cmd, env=env, check=True, capture_output=True, text=True).stdout


def sql_text(value: str) -> str:
    """A safe SQL string literal for the test values (letters, digits and a few signs only)."""
    assert all(c.isalnum() or c in "@._-$/" for c in value), value
    return "'" + value + "'"


@pytest.fixture(scope="session")
def base_url():
    return BASE_URL


@pytest.fixture(scope="session")
def test_user():
    """A throwaway user that exists only in the test database (email already verified)."""
    name = "ci_" + secrets.token_hex(4)
    password = "Ci-" + secrets.token_hex(8)
    pw_hash = subprocess.run(
        ["php", "-r", "echo password_hash($argv[1], PASSWORD_DEFAULT);", password],
        check=True, capture_output=True, text=True,
    ).stdout
    email = name + "@example.test"
    run_sql(
        "INSERT INTO users (email, username, password_hash, role, email_verified_at, language) VALUES ("
        f"{sql_text(email)}, {sql_text(name)}, {sql_text(pw_hash)}, 'user', NOW(), 'en')"
    )
    return {"email": email, "username": name, "password": password}


@pytest.fixture(scope="session")
def browser():
    with sync_playwright() as p:
        b = p.chromium.launch()
        yield b
        b.close()


@pytest.hookimpl(hookwrapper=True)
def pytest_runtest_makereport(item, call):
    """Remember the result of each test, so the page fixture can take a screenshot on failure."""
    outcome = yield
    report = outcome.get_result()
    setattr(item, "rep_" + report.when, report)


@pytest.fixture()
def page(browser, request):
    """A new browser page. It collects JavaScript errors in page.js_errors.
    On failure it saves a screenshot in tests/browser/screenshots/."""
    context = browser.new_context(viewport={"width": 1280, "height": 900})
    pg = context.new_page()
    pg.js_errors = []
    pg.on("pageerror", lambda err: pg.js_errors.append(str(err)))
    pg.on("console", lambda msg: pg.js_errors.append(msg.text) if msg.type == "error" else None)
    yield pg
    rep = getattr(request.node, "rep_call", None)
    if rep is not None and rep.failed:
        folder = os.path.join(os.path.dirname(__file__), "screenshots")
        os.makedirs(folder, exist_ok=True)
        pg.screenshot(path=os.path.join(folder, request.node.name + ".png"), full_page=True)
    context.close()


def sign_in(page, base_url, user):
    page.goto(base_url + "/login.php")
    page.fill("#email", user["email"])
    page.fill("#password", user["password"])
    page.click("button[type=submit].auth-button:not(.secondary)")
    page.wait_for_url("**/characteroverview.php")


def set_language(page, code):
    """Choose a language in the footer menu and wait for the new texts."""
    with page.expect_response(lambda r: "ajax/i18n.php" in r.url):
        page.select_option("#langSelect", code)
    page.wait_for_load_state("networkidle")
