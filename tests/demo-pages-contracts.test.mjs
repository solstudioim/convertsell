import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import test from 'node:test';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const demo = path.join(root, 'docs/demo');
const page = fs.readFileSync(path.join(demo, 'index.html'), 'utf8');
const css = fs.readFileSync(path.join(demo, 'styles.css'), 'utf8');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');

test('Pages deploys the checked-in demo with the necessary permissions', () => {
  const workflow = read('.github/workflows/pages.yml');
  for (const value of ['actions/configure-pages', 'actions/upload-pages-artifact', 'actions/deploy-pages', 'pages: write', 'id-token: write', 'path: docs/demo']) assert.ok(workflow.includes(value));
});

test('every demo local asset and in-page destination resolves', () => {
  const ids = new Set([...page.matchAll(/\bid="([^"]+)"/g)].map((match) => match[1]));
  for (const [, url] of page.matchAll(/(?:href|src|poster)="([^"]+)"/g)) {
    if (url.startsWith('#')) assert.ok(ids.has(url.slice(1)), `Missing destination: ${url}`);
    else if (!/^(?:https?:|mailto:|\.\/$)/.test(url)) assert.ok(fs.existsSync(path.join(demo, url)), `Missing asset: ${url}`);
  }
  for (const [, url] of css.matchAll(/url\("([^"]+)"\)/g)) assert.ok(fs.existsSync(path.join(demo, url)), `Missing CSS asset: ${url}`);
});

test('demo displays the actual current source package versions and requirements', () => {
  const child = read('wp-content/themes/convertsell/style.css');
  const toolkit = read('wp-content/plugins/convertsell-toolkit/convertsell-toolkit.php');
  for (const source of [child, toolkit]) {
    const version = source.match(/^(?: \* )?Version:\s*(\S+)/m)?.[1];
    assert.ok(version && page.includes(`>${version}<`), `Missing current version ${version}`);
  }
  for (const [label, version] of [['WordPress', '7.1+'], ['WooCommerce', '11.1+'], ['PHP', '7.4+']]) assert.ok(page.includes(`<dt>${label}</dt><dd>${version}</dd>`));
  assert.doesNotMatch(page, /0\.5\.12|0\.7\.4|historical video|Mobile sticky cart keeps/);
});

test('demo explains parent ownership, optional toolkit and material feature limits', () => {
  for (const text of ['The child theme', 'The optional toolkit', 'SKU lookup', 'Browser-based guest wishlist', 'Up to four optional product add-ons', 'Nothing is preselected', 'static cards', 'three placements', 'separate checkout and order', 'defaults to off and anonymous', 'On mobile, the product sticky bar is hidden', 'Checkout block', 'Payment-provider buttons depend']) assert.ok(page.includes(text), `Missing feature or limit: ${text}`);
  for (const url of ['https://purplecrodemo.wpcomstaging.com/', 'https://github.com/solstudioim/convertsell', 'https://github.com/woocommerce/woocommerce/tree/trunk/themes/purple']) assert.ok(page.includes(url));
  assert.doesNotMatch(page, /one-click post-purchase|guaranteed conversion|\d+% conversion lift/i);
});

test('media is opt-in, with a matching transcript and complete reproducible storyboard', () => {
  assert.match(page, /<video[^>]*controls[^>]*preload="none"/);
  assert.doesNotMatch(page.match(/<video\b[^>]*>/)?.[0] || '', /\sautoplay(?:\s|=|>)/);
  const storyboard = read('tools/demo/captions.txt').trim().split('\n').map((line) => line.split('|'));
  const transcript = page.match(/<ol id="storyboard">([\s\S]*?)<\/ol>/)?.[1];
  assert.equal([...transcript.matchAll(/<li>/g)].length, storyboard.length);
  assert.equal(new Set(storyboard.map(([frame]) => frame)).size, storyboard.length);
  for (const [frame, duration, caption] of storyboard) { assert.match(frame, /^[a-z0-9-]+\.png$/); assert.ok(Number(duration) > 0); assert.ok(caption.length > 10); }
  const script = read('tools/demo/build-video.sh');
  for (const value of ['-threads 1', '-filter_threads 1', '-filter_complex_threads 1', '-fflags +bitexact', '-map_metadata -1', 'convertsell-demo.mp4']) assert.ok(script.includes(value));
  const verify = read('tools/demo/verify-video-reproducibility.sh');
  for (const value of ['build-video.sh', 'shasum -a 256', 'hashes differ']) assert.ok(verify.includes(value));
});

test('demo uses Purple tokens and bundled licensed typography with responsive safeguards', () => {
  const parent = JSON.parse(read('wp-content/themes/purple/theme.json'));
  for (const { color } of parent.settings.color.palette.slice(0, 5)) assert.ok(css.toLowerCase().includes(color.toLowerCase()));
  assert.match(css, /@font-face/);
  assert.match(css, /max-width:\s*720px/);
  assert.match(css, /prefers-reduced-motion/);
  assert.match(css, /focus-visible/);
  assert.match(page, /class="skip-link"/);
  assert.ok(fs.readFileSync(path.join(demo, 'assets/InterVariable.woff2')).equals(fs.readFileSync(path.join(root, 'wp-content/themes/purple/assets/fonts/inter/InterVariable.woff2'))));
});
