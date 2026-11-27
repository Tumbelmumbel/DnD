"""Online test after the deploy (workflow deploy.yml, job "online-test").

It checks the LIVE website https://rudolph.cologne with the test account of the owner.
The sign-in data come only from the secrets TEST_USER (email address) and TEST_PASSWORD.
The test reads pages only: it does not change data of the test account.

Run: pytest .github/online-test -v   (environment variables TEST_USER, TEST_PASSWORD)
"""
import os

import pytest
from playwright.sync_api import expect, sync_playwright

BASE_URL = os.environ.get("ONLINE_BASE_URL", "https://rudolph.cologne")


@pytest.fixture(scope="module")
def browser():
    with sync_playwright() as p:
        b = p.chromium.launch()
        yield b
        b.close()


def new_page(browser, width=1280, height=900, mobile=False):
    context = browser.new_context(viewport={"width": width, "height": height}, is_mobile=mobile, has_touch=mobile)
    page = context.new_page()
    page.js_errors = []
    page.on("pageerror", lambda err: page.js_errors.append(str(err)))
    page.on("console", lambda msg: page.js_errors.append(msg.text) if msg.type == "error" else None)
    return context, page


def sign_in(page):
    user, password = os.environ.get("TEST_USER", ""), os.environ.get("TEST_PASSWORD", "")
    assert user and password, "The secrets TEST_USER and TEST_PASSWORD are missing"
    page.goto(BASE_URL + "/login.php")
    page.fill("#email", user)
    page.fill("#password", password)
    page.click("button[type=submit].auth-button:not(.secondary)")
    page.wait_for_url("**/characteroverview.php", timeout=20000)


def test_login_page_opens(browser):
    context, page = new_page(browser)
    response = page.goto(BASE_URL + "/login.php")
    assert response.status == 200
    expect(page.locator("#email")).to_be_visible()
    expect(page.locator("#password")).to_be_visible()
    assert page.js_errors == []
    context.close()


def test_sign_in_overview_and_character_sheet(browser):
    context, page = new_page(browser)
    sign_in(page)
    expect(page.locator("h1")).to_be_visible()
    page.goto(BASE_URL + "/CharacterSandbox.php")
    assert page.url.endswith("/CharacterSandbox.php"), page.url
    expect(page.locator("#Weapons")).to_be_visible()
    expect(page.locator("#inventoryAdd")).to_be_visible()
    page.wait_for_load_state("networkidle")
    assert page.js_errors == []
    # Sign out at the end (this browser session only)
    page.goto(BASE_URL + "/characteroverview.php")
    page.click("form[action='/logout.php'] button")
    page.wait_for_url("**/login.php*")
    context.close()


def test_phone_width_has_no_sideways_scrolling(browser):
    context, page = new_page(browser, width=390, height=844, mobile=True)
    sign_in(page)
    for path in ("/characteroverview.php", "/CharacterSandbox.php"):
        page.goto(BASE_URL + path)
        page.wait_for_load_state("networkidle")
        width = page.evaluate("document.documentElement.scrollWidth")
        assert width <= 390, f"{path} is {width} px wide"
    assert page.js_errors == []
    context.close()
