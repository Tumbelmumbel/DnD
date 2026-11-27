"""Click tests for the account pages and the language menu (requirement items 1 to 7)."""
import secrets

import pytest
from conftest import set_language, sign_in

NO_VALIDATE = (
    "document.querySelector('form').noValidate = true;"
    "document.querySelectorAll('input[maxlength]').forEach(i => i.removeAttribute('maxlength'))"
)
USERNAME_ERROR = "Username must be 3 to 40 characters"


def submit(page, url, fields):
    """Fill the form and send it. The browser checks and the maxlength attribute are off, so that the server rules are tested."""
    page.goto(url)
    page.evaluate(NO_VALIDATE)
    for field, value in fields.items():
        page.fill("#" + field, value)
    page.click("form button[type=submit]")
    page.wait_for_load_state()


def message(page):
    """The text of the page card without the form labels and buttons."""
    text = page.inner_text("main")
    for word in ("Email", "Password", "Username", "Sign in", "Register", "Reset", "Forgot password?",
                 "Create account", "Forgot password", "Back to sign in", "No account yet?",
                 "Already have an account?"):
        text = text.replace(word, "")
    return " ".join(text.split())


def new_email():
    return "ci_" + secrets.token_hex(5) + "@example.test"


# 1. Sign-in page

def test_login_form_has_email_and_password(page, base_url):
    page.goto(base_url + "/login.php")
    assert page.locator("h1").inner_text() == "Sign in"
    assert page.locator("#email").is_visible()
    assert page.locator("#password").is_visible()
    assert page.locator("#password").get_attribute("type") == "password"
    assert page.js_errors == []


def test_wrong_password_and_unknown_email_show_the_same_message(page, base_url, test_user):
    submit(page, base_url + "/login.php", {"email": test_user["email"], "password": "Wrong-" + secrets.token_hex(4)})
    wrong_password = message(page)
    assert page.url.endswith("/login.php")
    submit(page, base_url + "/login.php", {"email": new_email(), "password": "Wrong-" + secrets.token_hex(4)})
    unknown_email = message(page)
    assert page.url.endswith("/login.php")
    assert wrong_password != ""
    assert wrong_password == unknown_email
    assert "registered" not in wrong_password.lower()
    assert "not found" not in wrong_password.lower()


# 2. Sign-in

def test_verified_user_signs_in(page, base_url, test_user):
    sign_in(page, base_url, test_user)
    assert page.url.endswith("/characteroverview.php")
    assert page.locator("h1").inner_text() == "Your characters"
    assert page.js_errors == []


# 3. Protected pages

@pytest.mark.parametrize("path", ["/characteroverview.php", "/CharacterSandbox.php"])
def test_protected_pages_send_visitor_to_login(page, base_url, path):
    page.goto(base_url + path)
    assert page.url.split("?")[0].endswith("/login.php")
    assert page.locator("h1").inner_text() == "Sign in"


# 4. Log out

def test_log_out_ends_the_session(page, base_url, test_user):
    sign_in(page, base_url, test_user)
    page.click("text=Log out")
    page.wait_for_url("**/login.php**")
    for path in ("/characteroverview.php", "/CharacterSandbox.php"):
        page.goto(base_url + path)
        assert page.url.split("?")[0].endswith("/login.php"), path


def test_back_button_after_log_out_does_not_open_a_protected_page_again(page, base_url, test_user):
    sign_in(page, base_url, test_user)
    page.click("text=Log out")
    page.wait_for_url("**/login.php**")
    page.go_back()
    page.reload()
    assert page.url.split("?")[0].endswith("/login.php")


# 5. Registration

def test_register_form_fields(page, base_url):
    page.goto(base_url + "/register.php")
    assert page.locator("h1").inner_text() == "Create account"
    assert page.locator("#email").is_visible()
    assert page.locator("#username").is_visible()
    assert page.js_errors == []


@pytest.mark.parametrize("username", ["ab", "a", "bad name", "bad!name", "ab@cd", "a" * 41, "a" * 60])
def test_register_rejects_bad_username(page, base_url, username):
    submit(page, base_url + "/register.php", {"email": new_email(), "username": username})
    assert USERNAME_ERROR in page.inner_text("main")


@pytest.mark.parametrize("username", ["abc", "a" * 40, "A_b.c-1"])
def test_register_accepts_good_username(page, base_url, username):
    submit(page, base_url + "/register.php", {"email": new_email(), "username": username})
    assert USERNAME_ERROR not in page.inner_text("main")


def test_register_browser_limits_username_length(page, base_url):
    page.goto(base_url + "/register.php")
    page.fill("#username", "a" * 60)
    assert len(page.input_value("#username")) <= 40


def test_register_existing_email_shows_same_message_as_new_email(page, base_url, test_user):
    submit(page, base_url + "/register.php", {"email": new_email(), "username": "n" + secrets.token_hex(4)})
    new_message = message(page)
    submit(page, base_url + "/register.php", {"email": test_user["email"], "username": "n" + secrets.token_hex(4)})
    known_message = message(page)
    assert new_message != ""
    assert USERNAME_ERROR not in new_message
    assert known_message == new_message


# 6. Forgotten password

def test_forgotten_password_message_is_the_same_for_known_and_unknown_email(page, base_url, test_user):
    submit(page, base_url + "/ForgottenPW.php", {"email": test_user["email"]})
    known = message(page)
    submit(page, base_url + "/ForgottenPW.php", {"email": new_email()})
    unknown = message(page)
    assert known != ""
    assert known == unknown
    assert page.js_errors == []


# 7. Language menu

PUBLIC_PAGES = [
    ("/login.php", "Sign in", "Anmelden"),
    ("/register.php", "Create account", None),
    ("/ForgottenPW.php", "Forgot password", None),
]


@pytest.mark.parametrize("path,english,german", PUBLIC_PAGES)
def test_language_menu_on_public_pages(page, base_url, path, english, german):
    page.goto(base_url + path)
    assert page.locator("h1").inner_text() == english
    box = page.locator("#langSelect").bounding_box()
    size = page.viewport_size
    assert box["x"] + box["width"] > size["width"] * 0.6, "menu is not at the right"
    assert box["y"] + box["height"] > size["height"] * 0.6, "menu is not at the bottom"
    set_language(page, "de")
    assert page.locator("h1").inner_text() != english
    if german:
        assert page.locator("h1").inner_text() == german
    set_language(page, "en")
    assert page.locator("h1").inner_text() == english
    assert page.js_errors == []


def test_language_menu_on_character_overview(page, base_url, test_user):
    sign_in(page, base_url, test_user)
    assert page.locator("#langSelect").is_visible()
    set_language(page, "de")
    assert page.locator("h1").inner_text() != "Your characters"
    assert "Log out" not in page.inner_text("body")
    set_language(page, "en")
    assert page.locator("h1").inner_text() == "Your characters"
    assert page.js_errors == []


def test_language_menu_on_character_sheet(page, base_url, test_user):
    sign_in(page, base_url, test_user)
    page.goto(base_url + "/CharacterSandbox.php")
    assert page.locator("#langSelect").is_visible()
    set_language(page, "de")
    assert page.locator("html").get_attribute("lang") == "de"
    set_language(page, "en")
    assert page.js_errors == []


def test_german_error_message_on_login(page, base_url):
    page.goto(base_url + "/login.php")
    set_language(page, "de")
    submit(page, base_url + "/login.php", {"email": new_email(), "password": "Wrong-" + secrets.token_hex(4)})
    assert "Invalid email or password" not in page.inner_text("main")


def test_german_is_kept_on_the_next_page(page, base_url):
    page.goto(base_url + "/login.php")
    set_language(page, "de")
    page.goto(base_url + "/register.php")
    assert page.locator("h1").inner_text() != "Create account"
    set_language(page, "en")


# Phone width

@pytest.mark.parametrize("path", ["/login.php", "/register.php", "/ForgottenPW.php"])
def test_public_pages_fit_phone_width(browser, base_url, path):
    context = browser.new_context(viewport={"width": 390, "height": 844}, is_mobile=True, has_touch=True)
    page = context.new_page()
    errors = []
    page.on("pageerror", lambda err: errors.append(str(err)))
    page.goto(base_url + path)
    page.wait_for_load_state("networkidle")
    assert page.evaluate("document.documentElement.scrollWidth") <= 390
    assert errors == []
    context.close()
