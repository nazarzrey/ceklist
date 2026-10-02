import assert from 'node:assert/strict';

const base = (process.env.WEBKELAS_TEST_BASE_URL || 'http://127.0.0.1:8765/index.php').replace(/\/$/, '');
const checks = [];
const pass = (name) => checks.push(name);

const home = await fetch(`${base}/`, { redirect: 'manual' });
assert.equal(home.status, 200, 'home page should load');
assert.match(home.headers.get('set-cookie') || '', /HttpOnly/i, 'session cookie should be HttpOnly');
pass('home route and HttpOnly session cookie');

const unauthenticatedSession = await fetch(`${base}/api/session`);
assert.equal(unauthenticatedSession.status, 401, 'session API must reject anonymous requests');
pass('anonymous session API is rejected');

const anonymousAnnouncement = await fetch(`${base}/api/announcements`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ title: 'test', body: 'test' }),
});
assert.equal(anonymousAnnouncement.status, 401, 'announcement API must reject anonymous writes');
pass('anonymous announcement write is rejected');

const getLogout = await fetch(`${base}/init/logout`, { redirect: 'manual' });
assert.equal(getLogout.status, 404, 'administrative logout must not work through GET');
pass('administrative logout rejects GET');

const initSession = await fetch(`${base}/init`, { redirect: 'manual' });
assert.equal(initSession.status, 200, 'setup login page should load');
const cookie = (initSession.headers.get('set-cookie') || '').split(';', 1)[0];
assert.ok(cookie, 'setup session cookie should be issued');
const page = await initSession.text();
const csrf = page.match(/name="init_csrf" value="([a-f0-9]{64})"/i)?.[1];
assert.ok(csrf, 'setup login form should have a CSRF token');
assert.doesNotMatch(page, /<code>[^<]+<\/code>\s*\/\s*<code>/i, 'setup page must not print a hard-coded login pair');
pass('setup page issues CSRF token without exposing static credentials');

const failedLogin = await fetch(`${base}/init/login_post`, {
  method: 'POST',
  redirect: 'manual',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded', Cookie: cookie },
  body: new URLSearchParams({ username: 'invalid-test-user', password: 'invalid-test-password', init_csrf: csrf }),
});
assert.equal(failedLogin.status, 303, 'invalid setup credentials should redirect back to login');
assert.match(failedLogin.headers.get('location') || '', /\/init(?:$|\?)/, 'failed login should return to setup login');
const failedPage = await fetch(`${base}/init`, { headers: { Cookie: cookie } });
const failedBody = await failedPage.text();
assert.match(failedBody, /Akses setup tidak tersedia atau kredensial tidak cocok/);
assert.doesNotMatch(failedBody, /Status Inisialisasi Database/);
pass('setup login fails closed for invalid credentials');

const refreshedCsrf = failedBody.match(/name="init_csrf" value="([a-f0-9]{64})"/i)?.[1];
assert.ok(refreshedCsrf && refreshedCsrf !== csrf, 'rendering the form should rotate its CSRF token');
const replay = await fetch(`${base}/init/login_post`, {
  method: 'POST',
  redirect: 'manual',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded', Cookie: cookie },
  body: new URLSearchParams({ username: 'invalid-test-user', password: 'invalid-test-password', init_csrf: csrf }),
});
assert.equal(replay.status, 303, 'replayed setup CSRF token should be rejected');
const replayPage = await fetch(`${base}/init`, { headers: { Cookie: cookie } });
assert.match(await replayPage.text(), /Sesi form berakhir/);
pass('setup CSRF token is single-use');

console.log(`Black-box smoke checks passed (${checks.length}):\n- ${checks.join('\n- ')}`);
