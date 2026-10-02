<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$google_key = null;
$google_key_paths = [
    FCPATH . 'googleAuth.key.txt',
    FCPATH . 'googleAuth.key',
    dirname(rtrim(FCPATH, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR . 'googleAuth.key.txt',
    dirname(rtrim(FCPATH, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR . 'googleAuth.key'
];
foreach ($google_key_paths as $google_key_path) {
    if (!is_readable($google_key_path)) continue;
    $decoded = json_decode(file_get_contents($google_key_path), true);
    if (is_array($decoded) && !empty($decoded['web'])) {
        $google_key = $decoded['web'];
        break;
    }
}

$google_client_id = is_array($google_key) ? ($google_key['client_id'] ?? '') : '';
$google_client_secret = is_array($google_key) ? ($google_key['client_secret'] ?? '') : '';
$google_client_id = $google_client_id ?: (getenv('GOOGLE_CLIENT_ID') ?: ($_SERVER['GOOGLE_CLIENT_ID'] ?? ''));
$google_client_secret = $google_client_secret ?: (getenv('GOOGLE_CLIENT_SECRET') ?: ($_SERVER['GOOGLE_CLIENT_SECRET'] ?? ''));

$recaptcha_site_key = '';
$recaptcha_secret_key = '';
$recaptcha_key_paths = [
    FCPATH . 'googleRev3Auth.key',
    dirname(rtrim(FCPATH, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR . 'googleRev3Auth.key'
];
foreach ($recaptcha_key_paths as $recaptcha_key_path) {
    if (!is_readable($recaptcha_key_path)) continue;
    foreach (file($recaptcha_key_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue;
        $label = strtolower(trim($parts[0]));
        $value = trim($parts[1]);
        if (strpos($label, 'sitekey') !== false) $recaptcha_site_key = $value;
        if (strpos($label, 'secret') !== false) $recaptcha_secret_key = $value;
    }
    if ($recaptcha_site_key !== '' && $recaptcha_secret_key !== '') break;
}
$recaptcha_site_key = $recaptcha_site_key ?: (getenv('RECAPTCHA_SITE_KEY') ?: ($_SERVER['RECAPTCHA_SITE_KEY'] ?? ''));
$recaptcha_secret_key = $recaptcha_secret_key ?: (getenv('RECAPTCHA_SECRET_KEY') ?: ($_SERVER['RECAPTCHA_SECRET_KEY'] ?? ''));

$config['google_oauth'] = [
    'client_id' => $google_client_id,
    'client_secret' => $google_client_secret,
    'auth_uri' => is_array($google_key) ? ($google_key['auth_uri'] ?? 'https://accounts.google.com/o/oauth2/v2/auth') : 'https://accounts.google.com/o/oauth2/v2/auth',
    'token_uri' => is_array($google_key) ? ($google_key['token_uri'] ?? 'https://oauth2.googleapis.com/token') : 'https://oauth2.googleapis.com/token',
    'redirect_uri' => 'https://jurnal.nazrey.my.id/auth/google/callback',
    'recaptcha_site_key' => $recaptcha_site_key,
    'recaptcha_secret_key' => $recaptcha_secret_key
];
