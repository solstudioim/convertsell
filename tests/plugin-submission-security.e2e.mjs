/** Disposable-site authorization/CSRF regression; never targets a public site. */
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
assert.ok(process.env.POT_WP_PATH);
const wp = (code) => execFileSync('php', ['-d', 'error_reporting=22527', '/usr/local/bin/wp', 'eval', code, `--path=${process.env.POT_WP_PATH}`], { encoding: 'utf8' }).trim();
const site = wp('echo home_url();');
assert.match(new URL(site).hostname, /^(localhost|127\.0\.0\.1)$/);
const before = wp('echo wp_json_encode(array(get_option("pot_settings"),get_option("pot_checkout_settings"),get_option("pot_checkout_content_id")));');
const browser = await chromium.launch({ headless: true });
try {
	const admin = await browser.newContext();
	const page = await admin.newPage();
	await page.goto(`${site}/wp-login.php`);
	await page.locator('#user_login').fill('compat');
	await page.locator('#user_pass').fill(process.env.POT_TEST_PASSWORD || 'local-test-only');
	await Promise.all([page.waitForURL(url => !url.pathname.endsWith('wp-login.php')), page.locator('#wp-submit').click()]);
	for (const action of ['pot_set_offer_enabled', 'pot_preview_reviews']) {
		const response = await admin.request.post(`${site}/wp-admin/admin-ajax.php`, { form: { action, nonce: 'invalid', enabled: '1' } });
		assert.equal(response.status(), 403, `Admin request without a valid nonce: ${action}`);
	}
	const document = await admin.request.post(`${site}/wp-admin/admin-post.php`, { form: { action: 'pot_edit_checkout_content', _wpnonce: 'invalid' } });
	assert.equal(document.status(), 403, 'Document creation requires its nonce.');
	const guest = await browser.newContext();
	for (const action of ['pot_set_offer_enabled', 'pot_preview_reviews']) {
		const response = await guest.request.post(`${site}/wp-admin/admin-ajax.php`, { form: { action, nonce: 'invalid', enabled: '1' } });
		assert.ok([400, 403].includes(response.status()), `Guest denied: ${action}`);
	}
	const subscriber = Number(wp('$id=wp_create_user("submission_subscriber","local-test-only","submission-subscriber@example.test");if(is_wp_error($id)){$id=username_exists("submission_subscriber");}echo $id;'));
	const low = await browser.newContext();
	const login = await low.newPage();
	await login.goto(`${site}/wp-login.php`);
	await login.locator('#user_login').fill('submission_subscriber');
	await login.locator('#user_pass').fill('local-test-only');
	await Promise.all([login.waitForURL(url => !url.pathname.endsWith('wp-login.php')), login.locator('#wp-submit').click()]);
	for (const action of ['pot_set_offer_enabled', 'pot_preview_reviews']) {
		const response = await low.request.post(`${site}/wp-admin/admin-ajax.php`, { form: { action, nonce: 'invalid', enabled: '1' } });
		assert.equal(response.status(), 403, `Subscriber denied: ${action}`);
	}
	const protectedContent = await low.request.get(`${site}/wp-json/wp/v2/pot_checkout_info?context=edit`);
	assert.ok([401, 403].includes(protectedContent.status()), 'Subscriber cannot access checkout documents in REST edit context.');
	assert.equal(wp('echo wp_json_encode(array(get_option("pot_settings"),get_option("pot_checkout_settings"),get_option("pot_checkout_content_id")));'), before, 'Rejected requests must not change settings or content references.');
	wp(`require_once ABSPATH.'wp-admin/includes/user.php';wp_delete_user(${subscriber});`);
	console.log('PASS: admin CSRF, guest/subscriber authorization, private content REST protection and unchanged saved data.');
} finally { await browser.close(); }
