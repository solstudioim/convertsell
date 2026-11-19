/** Capture public reference pages in a dedicated headless browser; never logs in. */
import { mkdirSync, realpathSync, writeFileSync } from 'node:fs';
import path from 'node:path';

const modulePath = process.env.PLAYWRIGHT_MODULE || 'playwright';
const { chromium } = await import(modulePath);
const base = 'https://purplecrodemo.wpcomstaging.com';
const output = path.resolve(process.env.DEMO_ARTIFACTS || '/Users/sol/Codex/artifacts/convertsell-demo-refresh');
mkdirSync(output, { recursive: true });
if (!realpathSync(output).startsWith('/Users/sol/Codex/artifacts/')) throw new Error('Use the local artifact directory.');
const browser = await chromium.launch({ headless: true, args: ['--no-proxy-server'], ...(process.env.DEMO_BROWSER ? { executablePath: process.env.DEMO_BROWSER } : {}) });
const context = await browser.newContext({ viewport: { width: 1440, height: 1000 }, deviceScaleFactor: 1 });
const page = await context.newPage();
let documentUrl = '';
// Use Playwright's HTTP client for public requests when a system PAC is not
// supported by headless Chromium. Cookies still belong to this capture context.
await page.route('**/*', async (route) => {
  try {
    const response = await route.fetch({ timeout: 20000 });
    if (route.request().isNavigationRequest() && route.request().frame() === page.mainFrame()) documentUrl = response.url();
    await route.fulfill({ response });
  } catch {
    await route.abort();
  }
});
const evidence = { source: base, capturedAt: new Date().toISOString(), pages: [], errors: [] };
page.on('pageerror', (error) => evidence.errors.push(error.message));
page.setDefaultTimeout(20000);

async function syncDocumentUrl() {
  // The HTTP client follows redirects outside Chromium's unsupported PAC stack.
  // Restore the real response URL so relative offer forms retain their step.
  if (documentUrl && new URL(documentUrl).origin === base && page.url() !== documentUrl) {
    await page.evaluate((url) => history.replaceState(null, '', url), documentUrl);
  }
}

async function settle() {
  await page.evaluate(async () => {
    await Promise.race([document.fonts.ready, new Promise((resolve) => setTimeout(resolve, 5000))]);
    await Promise.race([Promise.all([...document.images].filter((img) => img.getBoundingClientRect().top < innerHeight).map((img) => img.decode().catch(() => {}))), new Promise((resolve) => setTimeout(resolve, 5000))]);
  });
}

async function capture(name, route, { mobile = false } = {}) {
  await page.setViewportSize(mobile ? { width: 390, height: 844 } : { width: 1440, height: 1000 });
  const response = await page.goto(new URL(route, base).href, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await syncDocumentUrl();
  await page.waitForLoadState('networkidle', { timeout: 10000 }).catch(() => {});
  await settle();
  writeFileSync(path.join(output, `${name}.html`), await page.content());
  await page.screenshot({ path: path.join(output, `${name}.png`), animations: 'disabled' });
  await page.screenshot({ path: path.join(output, `${name}-full.png`), fullPage: true, animations: 'disabled' });
  const observed = await page.evaluate(() => {
    const style = (selector) => {
      const element = document.querySelector(selector);
      if (!element) return null;
      const css = getComputedStyle(element);
      return { color: css.color, background: css.backgroundColor, font: css.fontFamily, fontSize: css.fontSize, fontWeight: css.fontWeight };
    };
    const root = getComputedStyle(document.body);
    return {
      title: document.title,
      text: document.body.innerText.slice(0, 18000),
      styles: Object.fromEntries(['body', 'h1', 'h2', 'header', '.wp-block-button__link', '.single_add_to_cart_button', '.pot-promo'].map((selector) => [selector, style(selector)])),
      tokens: Object.fromEntries([...root].filter((key) => key.startsWith('--wp--preset--color--') || key.startsWith('--wp--preset--font-family--')).map((key) => [key, root.getPropertyValue(key).trim()])),
      links: [...document.querySelectorAll('a[href]')].map((a) => ({ text: a.textContent.trim(), href: a.href })).filter((a) => a.text),
      overflow: document.documentElement.scrollWidth > innerWidth + 1,
    };
  });
  evidence.pages.push({ name, url: page.url(), status: response?.status(), ...observed });
  writeFileSync(path.join(output, 'staging-observations.json'), JSON.stringify(evidence, null, 2));
  console.log(JSON.stringify({ name, status: response?.status(), url: page.url(), title: observed.title, overflow: observed.overflow, text: observed.text.slice(0, 400) }));
}

async function state(name) {
  await settle();
  await page.screenshot({ path: path.join(output, `${name}.png`), animations: 'disabled', timeout: 20000 });
  writeFileSync(path.join(output, `${name}.html`), await page.content());
  evidence.pages.push({ name, url: page.url(), text: (await page.locator('body').innerText()).slice(0, 18000), overflow: await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1) });
  writeFileSync(path.join(output, 'staging-observations.json'), JSON.stringify(evidence, null, 2));
  console.log(JSON.stringify({ name, url: page.url() }));
}

try {
  let product = { href: process.env.DEMO_BASE_PRODUCT || `${base}/product/colorblock-knit-cardigan/` };
  if (new URL(product.href).origin !== base) throw new Error('Use a public product from the reference store.');
  if (!['cart', 'checkout'].includes(process.env.DEMO_JOURNEY)) {
  await capture('staging-home', '/');
  await capture('staging-shop', '/shop/');
  product = evidence.pages.at(-1).links.find((link) => new URL(link.href).pathname.startsWith('/product/'));
  if (!product) throw new Error('No public product found.');
  await capture('staging-product', product.href);
  await capture('staging-product-mobile', product.href, { mobile: true });
  await capture('staging-home-mobile', '/', { mobile: true });
  }
  if (['1', 'cart', 'checkout'].includes(process.env.DEMO_JOURNEY)) {
    if (process.env.DEMO_JOURNEY === '1') {
    await capture('journey-product', product.href);
    await page.locator('.wp-block-search__button').first().click();
    await page.locator('input[type="search"]').first().fill('knit');
    await page.locator('.pot-search-results a').first().waitFor();
    await state('staging-search');
    await page.locator('input[type="search"]').first().fill('');
    await page.locator('.pot-wishlist-button').first().click();
    await page.locator('.pot-wishlist-button[aria-pressed="true"]').waitFor();
    await capture('staging-wishlist', '/wishlist/');
    }
    await capture('journey-product-cart', product.href);
    const id = await page.locator('.single_add_to_cart_button').first().getAttribute('data-product_id');
    if (!/^\d+$/.test(id || '')) throw new Error('Expected a public simple-product identifier.');
    await capture('staging-cart-added', `/cart/?add-to-cart=${id}`);
    await page.locator('.wc-block-cart-items__row').first().waitFor();
    if (process.env.DEMO_JOURNEY !== 'checkout') {
    await capture('journey-product-with-cart', product.href);
    await page.locator('.wc-block-mini-cart__button:visible').first().click();
    await page.waitForFunction(() => document.querySelector('.wc-block-mini-cart__drawer')?.getAttribute('aria-hidden') === 'false');
    await state('staging-mini-cart');
    await capture('staging-cart', '/cart/');
    await page.locator('.wc-block-cart-items__row').first().waitFor();
    await state('staging-cart');
    }
    await capture('staging-checkout-entry', '/checkout/');
    for (let step = 0; step < 2 && await page.locator('.pot-offer-reject').count(); step++) {
      await state(step === 0 ? 'staging-upsell' : 'staging-downsell');
      await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }), page.locator('.pot-offer-reject').first().click()]);
      await syncDocumentUrl();
      await page.waitForLoadState('networkidle', { timeout: 10000 }).catch(() => {});
    }
    await page.locator('.wc-block-checkout').first().waitFor();
    await page.locator('#email').waitFor();
    await state('staging-checkout');
    if (await page.locator('.pot-checkout-upsells').count()) {
      await page.locator('.pot-checkout-upsells').scrollIntoViewIfNeeded();
      await state('staging-checkout-addons');
    }
    if (await page.locator('.pot-checkout-content').count()) {
      await page.locator('.pot-checkout-content').scrollIntoViewIfNeeded();
      await state('staging-checkout-content');
    }
    await page.setViewportSize({ width: 390, height: 844 });
    await page.evaluate(() => scrollTo(0, 0));
    await state('staging-checkout-mobile');
  }
  console.log(JSON.stringify({ output, product: product.href, errors: evidence.errors }));
} finally {
  await browser.close();
}
