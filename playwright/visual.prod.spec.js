const { test } = require('@playwright/test');

const baseURL =
  process.env.KAVORK_BASE_URL || 'https://kavork-app-production.up.railway.app';
const adminUser = process.env.KAVORK_ADMIN_USER;
const adminPass = process.env.KAVORK_ADMIN_PASS;

if (!adminUser || !adminPass) {
  throw new Error('Missing KAVORK_ADMIN_USER or KAVORK_ADMIN_PASS env vars.');
}

const viewports = [
  { name: 'desktop-1920x1080', width: 1920, height: 1080 },
  { name: 'laptop-1366x768', width: 1366, height: 768 },
  { name: 'mobile-390x844', width: 390, height: 844 },
];

async function snap(page, testInfo, name) {
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: testInfo.outputPath(`${name}.png`), fullPage: true });
}

async function login(page) {
  await page.goto(`${baseURL}/login`, { waitUntil: 'networkidle' });
  await page.fill('input[name="LoginForm[username]"]', adminUser);
  await page.fill('input[name="LoginForm[password]"]', adminPass);

  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle' }),
    page.click('button[name="login-button"]'),
  ]);

  const cafeSelect = page.locator('select[name="cafe"]');
  if (await cafeSelect.count()) {
    await cafeSelect.first().selectOption({ index: 0 });
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      page.click('button[name="login-button"]'),
    ]);
  }
}

for (const vp of viewports) {
  test.describe(vp.name, () => {
    test.use({ viewport: { width: vp.width, height: vp.height } });

    test('production visual smoke', async ({ page }, testInfo) => {
      await page.goto(`${baseURL}/`, { waitUntil: 'networkidle' });
      await snap(page, testInfo, 'landing');

      await login(page);
      await snap(page, testInfo, 'post-login');

      await page.goto(`${baseURL}/users/log`, { waitUntil: 'networkidle' });
      await page.waitForSelector('.kv-grid-table, .grid-view');
      await snap(page, testInfo, 'user-log');

      const editBtn = page.locator('.button_action .btn.btn-science-blue').first();
      if (await editBtn.count()) {
        await editBtn.click();
        await page.waitForSelector('.modal-dialog', { state: 'visible', timeout: 5000 });
        await snap(page, testInfo, 'user-log-edit-modal');
        await page.keyboard.press('Escape');
      }

      await page.goto(`${baseURL}/franchisee/payments`, { waitUntil: 'networkidle' });
      await page.waitForSelector('.kv-grid-table, .grid-view');
      await snap(page, testInfo, 'franchisee-payments');

      await page.goto(`${baseURL}/shop/report`, { waitUntil: 'networkidle' });
      await page.waitForSelector('.kv-grid-table, .grid-view');
      await snap(page, testInfo, 'shop-report');

      await page.goto(`${baseURL}/healthcheck.php`, { waitUntil: 'networkidle' });
      await snap(page, testInfo, 'healthcheck');
    });
  });
}
