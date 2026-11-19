/** Assemble reviewed public captures into demo frames and publishable media. */
import assert from 'node:assert/strict';
import { copyFileSync, mkdirSync, readFileSync, realpathSync, writeFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const artifact = path.resolve(process.env.DEMO_ARTIFACTS || '/Users/sol/Codex/artifacts/convertsell-demo-refresh');
assert.ok(realpathSync(artifact).startsWith('/Users/sol/Codex/artifacts/'));
const frames = path.join(artifact, 'frames');
const publish = path.join(artifact, 'publish');
mkdirSync(frames, { recursive: true });
mkdirSync(path.join(publish, 'screenshots'), { recursive: true });
const mapping = {
  '01-home': 'staging-home.png',
  '02-shop': 'staging-shop.png',
  '03-search': 'journey/staging-search.png',
  '04-product': 'staging-product.png',
  '05-wishlist': 'journey/staging-wishlist.png',
  '06-mini-cart': 'verified-cart-final/staging-mini-cart.png',
  '07-cart': 'verified-cart-final/staging-cart.png',
  '08-upsell': 'verified-cart-final/staging-upsell.png',
  '10-checkout-addons': 'checkout-optin/staging-checkout-addons.png',
  '11-checkout-content': 'checkout-optin/staging-checkout-content.png',
  '12-mobile-checkout': 'checkout-optin/staging-checkout-mobile.png',
};
const sha = (file) => createHash('sha256').update(readFileSync(file)).digest('hex');
const manifest = {};
for (const [name, source] of Object.entries(mapping)) {
  const input = path.join(artifact, source);
  const output = path.join(frames, `${name}.png`);
  copyFileSync(input, output);
  assert.equal(sha(input), sha(output));
  manifest[name] = { source, sha256: sha(output) };
}
for (const [name, source] of Object.entries({ home: '01-home', product: '04-product', checkout: '10-checkout-addons' })) {
  execFileSync('magick', [path.join(frames, `${source}.png`), '-strip', '-quality', '86', path.join(publish, `screenshots/${name}.jpg`)]);
}
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
const browser = await chromium.launch({ headless: true, ...(process.env.DEMO_BROWSER ? { executablePath: process.env.DEMO_BROWSER } : {}) });
try {
  const page = await browser.newPage({ viewport: { width: 1920, height: 930 }, deviceScaleFactor: 1 });
  const font = readFileSync(path.join(root, 'wp-content/themes/purple/assets/fonts/inter/InterVariable.woff2')).toString('base64');
  await page.setContent(readFileSync(path.join(root, 'tools/demo/cards.html'), 'utf8').replace('FONT_DATA', font));
  await page.evaluate(() => document.fonts.ready);
  for (const [name, id] of [['09-offer-options', 'offer-options'], ['13-post-purchase', 'post-purchase'], ['14-packages', 'packages']]) {
    await page.evaluate((id) => document.querySelectorAll('section').forEach((section) => section.classList.toggle('active', section.id === id)), id);
    const output = path.join(frames, `${name}.png`);
    await page.screenshot({ path: output, animations: 'disabled' });
    manifest[name] = { source: 'tools/demo/cards.html', sha256: sha(output), explanationCard: true };
  }
} finally {
  await browser.close();
}
writeFileSync(path.join(artifact, 'media-manifest.json'), JSON.stringify(manifest, null, 2) + '\n');
console.log(JSON.stringify({ frames, publish, framesCount: Object.keys(manifest).length }));
