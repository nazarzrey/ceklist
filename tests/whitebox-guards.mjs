import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../${path}`, import.meta.url), 'utf8');
const mainInit = await read('application/controllers/Init.php');
const archivedInit = await read('a/application/controllers/Init.php');
const api = await read('application/controllers/Api.php');
const model = await read('application/models/Classroom_model.php');
const js = await read('assets/js/ci-webkelas.js');
const config = await read('application/config/config.php');
const mainDashboard = await read('application/views/init/dashboard.php');
const archivedDashboard = await read('a/application/views/init/dashboard.php');

for (const [name, source] of [['main setup controller', mainInit], ['archived setup controller', archivedInit]]) {
  assert.match(source, /WEBKELAS_INIT_USER/);
  assert.match(source, /WEBKELAS_INIT_PASSWORD_HASH/);
  assert.match(source, /password_verify\(/);
  assert.match(source, /sess_regenerate\(TRUE\)/);
  assert.match(source, /verify_init_csrf\(/);
  assert.doesNotMatch(source, /VALID_PASSWORD|VALID_USER/);
}

assert.match(mainDashboard, /method="post" action="<\?= site_url\('init\/logout'\)/);
assert.match(mainDashboard, /method="post" action="<\?= site_url\('init\/seed'\)/);
assert.match(archivedDashboard, /method="post" action="<\?= site_url\('init\/logout'\)/);
assert.match(api, /Link sumber harus berupa URL http atau https/);
assert.match(api, /Link sumber maksimal 500 karakter/);
assert.match(api, /format\('Y-m-d\\TH:i'\) !== \$deadline/);
assert.match(model, /source_url VARCHAR\(500\) DEFAULT NULL/);
assert.match(model, /field_exists\('source_url', 'class_announcements'\)/);
assert.match(model, /'source_url' => \$payload\['source_url'\]/);
assert.match(js, /safeHttpUrl/);
assert.match(js, /Sumber instruksi/);
assert.match(config, /\$config\['cookie_secure'\] = !empty\(\$_SERVER\['HTTPS'\]\)/);

console.log('White-box guard checks passed (15 assertions).');
