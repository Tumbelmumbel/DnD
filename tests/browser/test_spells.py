"""Browser tests for the card "Spells" (part 1: no spells in the table).

They check the requirement in the pull request: values, spell slots, search box,
empty list, languages, phone width and JavaScript errors.
"""
import pytest
from playwright.sync_api import expect

from conftest import set_language, sign_in

ABILITY_FIELD = {
    "wisdom": "#ability_wisdom",
    "charisma": "#ability_charisma",
    "intelligence": "#ability_intelligence",
}


def open_sheet(page, base_url, user):
    sign_in(page, base_url, user)
    page.goto(base_url + "/CharacterSandbox.php")
    page.wait_for_load_state("networkidle")


def set_char(page, cls, level, scores=None, subclass=None):
    """Set class, (subclass), level and ability scores on the character sheet."""
    if not page.locator("#class_dropdown").is_visible():
        if not page.locator("#identityButton").is_visible():
            page.click("#barMore")
        page.click("#identityButton")
    page.select_option("#class_dropdown", value=cls.lower())
    if subclass:
        page.wait_for_function(
            "l => [...document.querySelectorAll('#subclass_dropdown option')].some(o => o.textContent.trim() === l)",
            arg=subclass,
        )
        page.select_option("#subclass_dropdown", label=subclass)
    else:
        page.wait_for_timeout(500)
    page.fill("#Level", str(level))
    page.press("#Level", "Tab")
    for ability, score in (scores or {}).items():
        page.fill(ABILITY_FIELD[ability], str(score))
        page.press(ABILITY_FIELD[ability], "Tab")
    page.wait_for_timeout(300)


def text(page, selector):
    return page.locator(selector).inner_text().strip()


def slot_boxes(page):
    return page.locator("#spellSlots .cs-spell-slot")


def slot_summary(page):
    out = []
    for i in range(slot_boxes(page).count()):
        box = slot_boxes(page).nth(i)
        out.append((box.locator(".cs-spell-slot-title").inner_text().strip(),
                    box.locator(".cs-spell-slot-total").inner_text().strip()))
    return out


# name, class, subclass, level, scores, ability, DC, attack, cantrips, kind, count
VALUE_CASES = [
    ("cleric1_wis16", "Cleric", None, 1, {"wisdom": 16}, "Wisdom", "13", "+5", "0 / 3", "Prepared", "0 / 4"),
    ("cleric5_wis16", "Cleric", None, 5, {"wisdom": 16}, "Wisdom", "14", "+6", "0 / 4", "Prepared", "0 / 8"),
    ("cleric1_wis10", "Cleric", None, 1, {"wisdom": 10}, "Wisdom", "10", "+2", "0 / 3", "Prepared", "0 / 1"),
    ("bard1_cha14", "Bard", None, 1, {"charisma": 14}, "Charisma", "12", "+4", "0 / 2", "Spells known", "0 / 4"),
    ("warlock1_cha10", "Warlock", None, 1, {"charisma": 10}, "Charisma", "10", "+2", "0 / 2", "Spells known", "0 / 2"),
    ("wizard5_int10", "Wizard", None, 5, {"intelligence": 10}, "Intelligence", "11", "+3", "0 / 4", "Prepared", "0 / 5"),
    ("paladin2_cha14", "Paladin", None, 2, {"charisma": 14}, "Charisma", "12", "+4", None, "Prepared", "0 / 3"),
    ("ek3_int10", "Fighter", "Eldritch Knight", 3, {"intelligence": 10}, "Intelligence", "10", "+2", "0 / 2", "Spells known", "0 / 3"),
]


@pytest.mark.parametrize("case", VALUE_CASES, ids=[c[0] for c in VALUE_CASES])
def test_values_of_the_card(page, base_url, test_user, case):
    _, cls, sub, level, scores, ability, dc, attack, cantrips, kind, count = case
    open_sheet(page, base_url, test_user)
    set_char(page, cls, level, scores, sub)
    assert text(page, "#spellAbility") == ability
    assert text(page, "#spellSaveDc") == dc
    assert text(page, "#spellAttack") == attack
    if cantrips is None:
        expect(page.locator("#spellCantripStat")).to_be_hidden()
    else:
        expect(page.locator("#spellCantripStat")).to_be_visible()
        assert text(page, "#spellCantripCount") == cantrips
    if kind == "Prepared":
        expect(page.locator("#spellPreparedStat")).to_be_visible()
        expect(page.locator("#spellKnownStat")).to_be_hidden()
        assert text(page, "#spellPreparedCount") == count
    else:
        expect(page.locator("#spellKnownStat")).to_be_visible()
        expect(page.locator("#spellPreparedStat")).to_be_hidden()
        assert text(page, "#spellKnownCount") == count
    expect(page.locator("#spellsNoCasting")).to_be_hidden()
    expect(page.locator("#spellAdd")).to_be_visible()
    assert page.js_errors == []


@pytest.mark.parametrize("cls,sub,level", [
    ("Barbarian", None, 1), ("Barbarian", None, 10), ("Barbarian", None, 20),
    ("Fighter", None, 1), ("Fighter", None, 10), ("Fighter", None, 20),
    ("Rogue", None, 1), ("Rogue", None, 10), ("Rogue", None, 20),
    ("Paladin", None, 1),
    ("Fighter", "Eldritch Knight", 1), ("Fighter", "Eldritch Knight", 2),
])
def test_class_without_spells(page, base_url, test_user, cls, sub, level):
    open_sheet(page, base_url, test_user)
    set_char(page, cls, level, None, sub)
    expect(page.locator("#spellsNoCasting")).to_be_visible()
    assert text(page, "#spellsNoCasting") == "This class has no spells at this level."
    expect(page.locator("#spellStats")).to_be_hidden()
    expect(page.locator("#spellAdd")).to_be_hidden()
    assert slot_boxes(page).count() == 0
    assert page.js_errors == []


def test_card_position_and_title(page, base_url, test_user):
    open_sheet(page, base_url, test_user)
    order = page.evaluate(
        "[...document.querySelectorAll('section')].map(s => s.id).filter(i => ['Inventory','Spells','Talents'].includes(i))"
    )
    assert order == ["Inventory", "Spells", "Talents"]
    assert text(page, "#cs-spells-title") == "Spells"


def test_spell_slot_boxes(page, base_url, test_user):
    open_sheet(page, base_url, test_user)
    set_char(page, "Cleric", 1, {"wisdom": 16})
    assert slot_summary(page) == [("Level 1", "Total: 2")]
    assert slot_boxes(page).first.locator("span", has_text="Used").count() >= 1
    set_char(page, "Cleric", 5)
    assert slot_summary(page) == [("Level 1", "Total: 4"), ("Level 2", "Total: 3"), ("Level 3", "Total: 2")]
    set_char(page, "Warlock", 5)
    assert slot_summary(page) == [("Level 3", "Total: 2")]
    assert page.js_errors == []


def test_used_slots_are_limited(page, base_url, test_user):
    open_sheet(page, base_url, test_user)
    set_char(page, "Cleric", 1, {"wisdom": 16})
    field = slot_boxes(page).first.locator("input")
    field.fill("1")
    field.press("Tab")
    assert field.input_value() == "1"
    field.fill("9")
    field.press("Tab")
    assert field.input_value() == "2"
    field.fill("-3")
    field.press("Tab")
    assert field.input_value() == "0"
    field.fill("2")
    field.press("Tab")
    assert field.input_value() == "2"


def test_used_slots_stay_when_level_changes(page, base_url, test_user):
    open_sheet(page, base_url, test_user)
    set_char(page, "Cleric", 5, {"wisdom": 16})
    boxes = slot_boxes(page)
    for i, value in enumerate(["4", "3", "1"]):
        boxes.nth(i).locator("input").fill(value)
        boxes.nth(i).locator("input").press("Tab")
    # Lower level: values stay, but not more than the new total
    set_char(page, "Cleric", 1)
    boxes = slot_boxes(page)
    assert boxes.count() == 1
    assert boxes.first.locator("input").input_value() == "2"
    # Higher level: the value of level 1 stays at the lower number
    set_char(page, "Cleric", 5)
    boxes = slot_boxes(page)
    assert boxes.first.locator("input").input_value() in ("2", "4")
    assert boxes.nth(1).locator("input").input_value() in ("", "0", "3")


def test_used_slots_keep_value_below_total_on_level_up(page, base_url, test_user):
    open_sheet(page, base_url, test_user)
    set_char(page, "Cleric", 1, {"wisdom": 16})
    field = slot_boxes(page).first.locator("input")
    field.fill("1")
    field.press("Tab")
    set_char(page, "Cleric", 5)
    assert slot_boxes(page).first.locator("input").input_value() == "1"


def test_search_box_and_empty_list(page, base_url, test_user):
    open_sheet(page, base_url, test_user)
    set_char(page, "Wizard", 3, {"intelligence": 14})
    expect(page.locator("#spellPicker")).to_be_hidden()
    page.click("#spellAdd")
    expect(page.locator("#spellPicker")).to_be_visible()
    assert text(page, "label[for=spellSearch]") == "Search spell"
    expect(page.locator("#spellSearch")).to_be_visible()
    assert "No spell found." in text(page, "#spellResults")
    page.fill("#spellSearch", "fire")
    assert "No spell found." in text(page, "#spellResults")
    # Escape closes the box
    page.keyboard.press("Escape")
    expect(page.locator("#spellPicker")).to_be_hidden()
    # A click outside closes the box
    page.click("#spellAdd")
    expect(page.locator("#spellPicker")).to_be_visible()
    page.click("#cs-spells-title")
    expect(page.locator("#spellPicker")).to_be_hidden()
    assert page.js_errors == []


def test_empty_spell_list_and_credit(page, base_url, test_user):
    open_sheet(page, base_url, test_user)
    set_char(page, "Wizard", 3, {"intelligence": 14})
    expect(page.locator("#spellsEmpty")).to_be_visible()
    assert text(page, "#spellsEmpty") == "No spells."
    expect(page.locator("#Spells .cs-spell-cols")).to_be_hidden()
    assert page.locator("#spellList li").count() == 0
    link = page.locator("#Spells a", has_text="CC BY 4.0")
    expect(link).to_be_visible()
    assert "creativecommons.org/licenses/by/4.0" in link.get_attribute("href")
    assert "System Reference Document 5.1" in text(page, "#Spells .cs-spell-credit")


def test_values_change_with_ability_score(page, base_url, test_user):
    open_sheet(page, base_url, test_user)
    set_char(page, "Cleric", 1, {"wisdom": 10})
    assert text(page, "#spellSaveDc") == "10"
    page.fill("#ability_wisdom", "16")
    page.press("#ability_wisdom", "Tab")
    assert text(page, "#spellSaveDc") == "13"
    assert text(page, "#spellAttack") == "+5"
    assert text(page, "#spellPreparedCount") == "0 / 4"


def test_german_texts_and_used_slots_stay(page, base_url, test_user):
    open_sheet(page, base_url, test_user)
    set_char(page, "Cleric", 5, {"wisdom": 16})
    field = slot_boxes(page).nth(1).locator("input")
    field.fill("2")
    field.press("Tab")
    set_language(page, "de")
    page.wait_for_function("document.querySelector('#cs-spells-title').innerText.trim() === 'Zauber'")
    assert text(page, "#cs-spells-title") == "Zauber"
    assert text(page, "#spellAdd") == "+ Zauber"
    assert slot_summary(page) == [("Grad 1", "Gesamt: 4"), ("Grad 2", "Gesamt: 3"), ("Grad 3", "Gesamt: 2")]
    assert "Verbraucht" in slot_boxes(page).first.inner_text()
    assert slot_boxes(page).nth(1).locator("input").input_value() == "2"
    assert text(page, "#spellsEmpty") == "Keine Zauber."
    page.click("#spellAdd")
    assert text(page, "label[for=spellSearch]") == "Zauber suchen"
    assert "Kein Zauber gefunden." in text(page, "#spellResults")
    page.keyboard.press("Escape")
    set_char(page, "Barbarian", 1)
    assert text(page, "#spellsNoCasting") == "Diese Klasse hat auf dieser Stufe keine Zauber."
    set_language(page, "en")
    page.wait_for_function("document.querySelector('#cs-spells-title').innerText.trim() === 'Spells'")
    assert text(page, "#spellsNoCasting") == "This class has no spells at this level."
    assert page.js_errors == []


def test_phone_width_spells_card(browser, base_url, test_user):
    context = browser.new_context(viewport={"width": 390, "height": 844}, is_mobile=True, has_touch=True)
    page = context.new_page()
    errors = []
    page.on("pageerror", lambda err: errors.append(str(err)))
    open_sheet(page, base_url, test_user)
    set_char(page, "Cleric", 9, {"wisdom": 16})
    assert slot_boxes(page).count() == 5
    page.locator("#Spells").scroll_into_view_if_needed()
    assert page.evaluate("document.documentElement.scrollWidth") <= 390
    # The slot boxes and value boxes go to the next line: not all in one row
    tops = page.evaluate(
        "[...document.querySelectorAll('#spellSlots .cs-spell-slot')].map(e => Math.round(e.getBoundingClientRect().top))"
    )
    assert len(set(tops)) > 1
    stat_tops = page.evaluate(
        "[...document.querySelectorAll('#spellStats .cs-spell-stat')].filter(e => e.offsetParent !== null)"
        ".map(e => Math.round(e.getBoundingClientRect().top))"
    )
    assert len(set(stat_tops)) > 1
    for sel in ("#spellSlots", "#spellStats"):
        right = page.evaluate(f"document.querySelector('{sel}').getBoundingClientRect().right")
        assert right <= 390
    assert errors == []
    context.close()
