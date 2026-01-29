import json
import os
from datetime import datetime

from playwright.sync_api import sync_playwright


BASE_URL = "https://kavork-app-production.up.railway.app/"

MENU_ROUTES = {
    "My account": "/franchisee/payments",
    "Role user": "/permit/access/role",
    "Translations": "/i18n/default",
    "Region params": "/cafe/admin-params",
    "Franchises": "/franchisee/admin/index",
    "Departments": "/cafe/admin/index",
    "Tariffs": "/tariffs/admin/index",
    "Report": "/report/admin/index",
    "Transactions": "/report/admin/transactions",
    "Staff": "/users/admin/index",
    "Staff sessions": "/users/log/index",
    "Staff schedule": "/timetable/admin/index",
    "Tasks": "/tasks/admin/index",
    "Visitor Database": "/visitor/admin/index",
    "Visits": "/visits/admin/index",
    "Polls": "/polls/admin/index",
    "Products": "/shop/catalog/index",
    "Sold products": "/shop/sale/index",
    "Сommodity research": "/shop/inventory/index",
    "Templates letters, checks": "/templates/admin/index",
    "Bulk emails": "/mails/admin/index",
    "Announcement": "/selfservice/default/index",
}


def _write_json(path, data):
    with open(path, "w", encoding="utf-8") as handle:
        json.dump(data, handle, indent=2, ensure_ascii=False)


def _maybe_login(page, username, password):
    password_inputs = page.locator("input[type='password']")
    if password_inputs.count() == 0:
        return False

    username_selectors = [
        "input[name='LoginForm[username]']",
        "input[name='login']",
        "input[type='text']",
        "input[type='email']",
    ]
    password_selector = "input[type='password']"

    username_filled = False
    for selector in username_selectors:
        if page.locator(selector).count() > 0:
            page.locator(selector).first.fill(username)
            username_filled = True
            break

    if not username_filled:
        return False

    page.locator(password_selector).first.fill(password)

    for button_name in ["Login", "Sign in", "ENTER", "Submit"]:
        if page.get_by_role("button", name=button_name).count() > 0:
            page.get_by_role("button", name=button_name).first.click()
            page.wait_for_load_state("networkidle")
            return True

    if page.locator("button[type='submit']").count() > 0:
        page.locator("button[type='submit']").first.click()
        page.wait_for_load_state("networkidle")
        return True

    return False


def _ensure_menu_loaded(page):
    try:
        page.wait_for_selector("text=Visitor Database", timeout=15000)
        return True
    except Exception:
        return False


def _enter_department(page):
    enter_button = page.get_by_role("button", name="ENTER")
    if enter_button.count() > 0:
        enter_button.first.click()
        page.wait_for_load_state("networkidle")
        return True
    return False


def _find_menu_href(page, label):
    return page.evaluate(
        """
        (menuLabel) => {
            const links = Array.from(document.querySelectorAll('a'));
            const match = links.find((link) => {
                const text = (link.textContent || '').trim();
                return text === menuLabel;
            });
            return match ? match.getAttribute('href') : null;
        }
        """,
        label,
    )


def main():
    timestamp = datetime.now().strftime("%Y%m%d-%H%M%S")
    output_dir = os.path.join(".playwright-mcp", f"scan-{timestamp}")
    os.makedirs(output_dir, exist_ok=True)

    console_logs = []
    network_events = []
    menu_results = []

    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(headless=False)
        context = browser.new_context(
            record_video_dir=output_dir,
            record_video_size={"width": 1280, "height": 720},
        )
        page = context.new_page()

        page.on(
            "console",
            lambda message: console_logs.append(
                {
                    "type": message.type,
                    "text": message.text,
                    "location": str(message.location),
                }
            ),
        )
        page.on(
            "response",
            lambda response: network_events.append(
                {
                    "url": response.url,
                    "status": response.status,
                    "ok": response.ok,
                }
            ),
        )
        page.on(
            "requestfailed",
            lambda request: network_events.append(
                {
                    "url": request.url,
                    "status": "failed",
                    "error": request.failure,
                }
            ),
        )

        page.goto(f"{BASE_URL}login")
        page.wait_for_load_state("networkidle")

        _enter_department(page)
        page.wait_for_load_state("networkidle")

        credentials = [
            (
                os.environ.get("KAVORK_USERNAME", "testuser"),
                os.environ.get("KAVORK_PASSWORD", "Test1234!"),
            ),
            (
                os.environ.get("KAVORK_USERNAME_ALT", "filipp1"),
                os.environ.get("KAVORK_PASSWORD_ALT", "Test1234!"),
            ),
        ]

        login_attempted = []
        menu_ready = False
        for username, password in credentials:
            attempted = _maybe_login(page, username, password)
            login_attempted.append({"username": username, "attempted": attempted})
            _enter_department(page)
            page.wait_for_load_state("networkidle")
            menu_ready = _ensure_menu_loaded(page)
            if menu_ready:
                break

        if not menu_ready:
            page.goto(BASE_URL)
            page.wait_for_load_state("networkidle")
            menu_ready = _ensure_menu_loaded(page)

        if not menu_ready:
            page.goto(f"{BASE_URL}visitor/admin/index")
            page.wait_for_load_state("networkidle")
            menu_ready = _ensure_menu_loaded(page)

        for menu_name, route in MENU_ROUTES.items():
            result = {"menu": menu_name, "url": None, "error": None, "status": None}
            try:
                target = BASE_URL.rstrip("/") + "/" + route.lstrip("/")
                response = page.goto(target)
                page.wait_for_load_state("networkidle")
                result["url"] = page.url
                result["status"] = response.status if response else None
                screenshot_path = os.path.join(
                    output_dir, f"menu-{menu_name.replace(' ', '_')}.png"
                )
                page.screenshot(path=screenshot_path, full_page=True)
            except Exception as exc:
                result["error"] = str(exc)
            menu_results.append(result)

        # Try clicking a visitor card if visible on the dashboard.
        try:
            visitor_card = page.get_by_role("heading", name="Anonym")
            if visitor_card.count() > 0:
                visitor_card.first.click()
                page.wait_for_load_state("networkidle")
                page.wait_for_timeout(1000)
                page.screenshot(
                    path=os.path.join(output_dir, "visitor-detail.png"), full_page=True
                )
        except Exception:
            pass

        _write_json(
            os.path.join(output_dir, "page_state.json"),
            {"url": page.url, "title": page.title(), "content": page.content()},
        )

        _write_json(
            os.path.join(output_dir, "auth_state.json"),
            {
                "menu_ready": menu_ready,
                "login_attempted": login_attempted,
                "current_url": page.url,
            },
        )

        context.close()
        browser.close()
    _write_json(os.path.join(output_dir, "console.json"), console_logs)
    _write_json(os.path.join(output_dir, "network.json"), network_events)
    _write_json(
        os.path.join(output_dir, "report.json"),
        {
            "base_url": BASE_URL,
            "menus": menu_results,
            "console_errors": [log for log in console_logs if log["type"] == "error"],
            "http_500s": [event for event in network_events if event.get("status") == 500],
        },
    )

    print(output_dir)


if __name__ == "__main__":
    main()
