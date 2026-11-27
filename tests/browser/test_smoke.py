"""Smoke tests: the main pages load, in both languages, at phone width, without JavaScript errors.

The full click tests follow in a later pull request (see the workflow plan).
"""
from conftest import set_language, sign_in


def test_login_page_in_english_and_german(page, base_url):
    page.goto(base_url + "/login.php")
    assert page.locator("h1").inner_text() == "Sign in"
    set_language(page, "de")
    assert page.locator("h1").inner_text() == "Anmelden"
    set_language(page, "en")
    assert page.locator("h1").inner_text() == "Sign in"
    assert page.js_errors == []


def test_protected_page_needs_sign_in(page, base_url):
    page.goto(base_url + "/CharacterSandbox.php")
    assert page.url.endswith("/login.php")


def test_sign_in_and_character_sheet(page, base_url, test_user):
    sign_in(page, base_url, test_user)
    assert page.locator("h1").inner_text() == "Your characters"
    page.goto(base_url + "/CharacterSandbox.php")
    assert page.locator("#Weapons").is_visible()
    assert "No weapon equipped." in page.locator("#weaponsEmpty").inner_text()
    # The character sheet changes its texts in place (no reload)
    set_language(page, "de")
    page.wait_for_function("document.querySelector('#weaponsEmpty').innerText !== 'No weapon equipped.'")
    assert "Waffen" in page.locator("#Weapons").inner_text()
    set_language(page, "en")
    assert page.js_errors == []


def test_phone_width_has_no_horizontal_scrolling(browser, base_url, test_user):
    context = browser.new_context(viewport={"width": 390, "height": 844}, is_mobile=True, has_touch=True)
    page = context.new_page()
    errors = []
    page.on("pageerror", lambda err: errors.append(str(err)))
    sign_in(page, base_url, test_user)
    for path in ("/characteroverview.php", "/CharacterSandbox.php"):
        page.goto(base_url + path)
        page.wait_for_load_state("networkidle")
        width = page.evaluate("document.documentElement.scrollWidth")
        assert width <= 390, f"{path} is {width} px wide"
    assert errors == []
    context.close()
