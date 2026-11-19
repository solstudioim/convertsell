/** Browser acceptance checks for the real static demo, served from source bytes. */
import assert from 'node:assert/strict';
import { mkdirSync, readFileSync, realpathSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const site = path.join(root, 'docs/demo');
const output = path.resolve(process.env.DEMO_ARTIFACTS || '/Users/sol/Codex/artifacts/convertsell-demo-refresh/preview');
mkdirSync(output, { recursive: true });
assert.ok(realpathSync(output).startsWith('/Users/sol/Codex/artifacts/'));
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
const browser = await chromium.launch({ headless: true, ...(process.env.DEMO_BROWSER ? { executablePath: process.env.DEMO_BROWSER } : {}) });
const results = [];
const errors = [];
const mime = { '.html': 'text/html', '.css': 'text/css', '.jpg': 'image/jpeg', '.webp': 'image/webp', '.svg': 'image/svg+xml', '.woff2': 'font/woff2', '.mp4': 'video/mp4', '.txt': 'text/plain' };
async function context(options = {}) {
  const context = await browser.newContext(options);
  await context.route('https://convertsell-preview.test/**', async (route) => {
    const pathname = decodeURIComponent(new URL(route.request().url()).pathname);
    const file = path.resolve(site, `.${pathname === '/' ? '/index.html' : pathname}`);
    if (!file.startsWith(site + path.sep)) return route.abort();
    try {
      const data = readFileSync(file);
      const range = route.request().headers().range?.match(/^bytes=(\d+)-(\d*)$/);
      const headers = { 'content-type': mime[path.extname(file)] || 'application/octet-stream', 'accept-ranges': 'bytes' };
      if (range) {
        const start = Number(range[1]);
        const end = range[2] ? Math.min(Number(range[2]), data.length - 1) : data.length - 1;
        await route.fulfill({ status: 206, headers: { ...headers, 'content-range': `bytes ${start}-${end}/${data.length}` }, body: data.subarray(start, end + 1) });
      } else await route.fulfill({ status: 200, headers, body: data });
    } catch (error) {
      errors.push(`${pathname}: ${error.message}`);
      await route.fulfill({ status: 404, body: 'Not found' });
    }
  });
  return context;
}
try {
  const client = await context();
  const page = await client.newPage();
  page.on('pageerror', (error) => errors.push(error.message));
  let mediaRequests = 0;
  page.on('request', (request) => { if (request.url().endsWith('.mp4')) mediaRequests++; });
  for (const width of [1440, 768, 390, 320]) {
    await page.setViewportSize({ width, height: width > 1000 ? 1050 : 900 });
    await page.goto('https://convertsell-preview.test/', { waitUntil: 'networkidle' });
    await page.evaluate(() => document.fonts.ready);
    assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1), false, `${width}px page overflow`);
    assert.equal(await page.locator('h1').count(), 1);
    assert.equal(await page.locator('video').getAttribute('preload'), 'none');
    assert.equal(await page.locator('video').evaluate((video) => video.autoplay), false);
    for (const image of await page.locator('img').all()) {
      await image.scrollIntoViewIfNeeded();
      await image.evaluate((image) => image.decode());
      assert.ok(await image.evaluate((image) => image.naturalWidth > 0));
    }
    await page.evaluate(() => scrollTo(0, 0));
    await page.screenshot({ path: path.join(output, `demo-${width}.png`), fullPage: true });
    if (width === 1440) await page.screenshot({ path: path.join(output, 'demo-desktop-first-screen.png') });
    await page.locator('#confidence > summary').focus();
    await page.keyboard.press('Enter');
    assert.equal(await page.locator('#confidence').getAttribute('open'), '');
    assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1), false, `${width}px expanded feature overflow`);
    results.push({ width, overflow: false, imagesDecoded: 3, keyboardDetails: true });
  }
  assert.equal(mediaRequests, 0, 'Preload none should avoid downloading the video before interaction.');
  await page.setViewportSize({ width: 1440, height: 1000 });
  const video = page.locator('video');
  await video.scrollIntoViewIfNeeded();
  await video.evaluate((video) => video.load());
  await page.waitForFunction(() => document.querySelector('video').readyState >= 1);
  const metadata = await video.evaluate((video) => ({ duration: video.duration, width: video.videoWidth, height: video.videoHeight }));
  assert.deepEqual([metadata.width, metadata.height], [1920, 1080]);
  assert.equal(metadata.duration, 63);
  await video.evaluate((video) => video.play());
  await page.waitForFunction(() => document.querySelector('video').currentTime > .5);
  await video.evaluate((video) => video.pause());
  assert.equal(await video.evaluate((video) => video.error), null);
  await video.screenshot({ path: path.join(output, 'video-playback.png') });
  await page.goto('https://convertsell-preview.test/');
  await page.keyboard.press('Tab');
  assert.equal(await page.locator('.skip-link').evaluate((link) => link === document.activeElement), true);
  await page.keyboard.press('Enter');
  assert.equal(await page.locator('#main').evaluate((main) => main === document.activeElement), true);
  const noScript = await context({ javaScriptEnabled: false, viewport: { width: 390, height: 844 } });
  const fallback = await noScript.newPage();
  await fallback.goto('https://convertsell-preview.test/');
  await fallback.locator('#offers > summary').click();
  assert.equal(await fallback.locator('#offers').getAttribute('open'), '');
  assert.ok(await fallback.getByRole('link', { name: 'Try the live demo' }).isVisible());
  assert.deepEqual(errors, []);
  writeFileSync(path.join(output, 'verification.json'), JSON.stringify({ results, metadata, noScript: true, skipLink: true, errors }, null, 2) + '\n');
  console.log(JSON.stringify({ results, metadata, noScript: true, skipLink: true, output }));
} finally {
  await browser.close();
}
