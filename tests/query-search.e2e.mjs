/** Exercise the public AJAX SKU search against seeded, disposable demo data. */
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
assert.ok(process.env.POT_WP_PATH);
const wp = code => execFileSync('php', ['-d', 'error_reporting=22527', '/usr/local/bin/wp', 'eval', code, `--path=${process.env.POT_WP_PATH}`], { encoding: 'utf8' }).trim();
const site = wp('echo home_url();');
assert.match(new URL(site).hostname, /^(localhost|127\.0\.0\.1)$/);
const fixture = JSON.parse(wp('$p=wc_get_product(wc_get_product_id_by_sku("POT-001"));$other=wc_get_product(wc_get_product_id_by_sku("POT-004"));echo wp_json_encode(array("id"=>$p->get_id(),"category"=>$p->get_category_ids()[0],"other"=>$other->get_category_ids()[0]));'));
const browser = await chromium.launch({ headless: true });
try {
	const context = await browser.newContext(); const page = await context.newPage();
	await page.goto(site, { waitUntil: 'networkidle' });
	const config = await page.evaluate(() => window.purpleOptimize);
	assert.ok(config?.nonce && config.ajaxUrl);
	const search = async (term, category) => {
		const url = new URL(config.ajaxUrl); for (const [key, value] of Object.entries({ action: 'pot_search_products', nonce: config.nonce, term, category })) url.searchParams.set(key, String(value));
		const response = await context.request.get(url.href); assert.equal(response.status(), 200);
		const result = await response.json(); assert.equal(result.success, true); return result.data;
	};
	const result = await search('OT-001', fixture.category);
	assert.ok(result.some(item => item.id === fixture.id), 'Partial SKU returns its visible product.');
	assert.ok(result.every(item => item.url && item.name && typeof item.price === 'string'));
	assert.equal((await search('OT-001', fixture.other)).length, 0, 'Category scope survives the lookup-table search.');
	assert.equal((await search("' OR 1=1 --", fixture.category)).length, 0, 'SQL-looking input is literal.');
	assert.equal((await search('x'.repeat(129), fixture.category)).length, 0, 'Oversized public search is rejected.');
	console.log('PASS: public nonce-protected partial SKU search, category isolation, response shape, SQL-looking and oversized input.');
} finally { await browser.close(); }
