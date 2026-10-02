<?php

date_default_timezone_set('Asia/Jakarta');
$config['time_reference'] = 'local';
$sv = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : ($_SERVER['SERVER_NAME'] ?? 'localhost');
$http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';
$newurl = str_replace("index.php", "", $_SERVER['SCRIPT_NAME']);
$port  = "";
$sv = $_SERVER['SERVER_NAME'] ?? $sv;
$host = $_SERVER['HTTP_HOST'] ?? 'jurnal.nazrey.my.id';
$is_offline_host = in_array($sv, ['localhost', '127.0.0.1', '::1'], true)
    || strpos($sv, '192.') === 0
    || strpos($sv, '.local') !== false
    || strpos($host, '.local') !== false;
if ($is_offline_host) {
    $live = "0";
    $server_port = $_SERVER['SERVER_PORT'] ?? '80';
    $port = ($server_port != "80" && $server_port != "443") ? ":".$server_port : "";
    $config['base_url'] = $http . $sv . $port . $newurl;   
} else {        
    $live = "1";
    $config['base_url'] = "https://" . $host . $newurl;
}
$config['app_online'] = ($live === "1");
//die($config['base_url'].$live);
if ($live=="0") {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
}

$version = true;
$vs = "v41";
if($version){
    $vs = $vs.date("dmy");
}

$config['versionJsCss'] = $vs;
$config['appVersion'] = '2.0';
$config['index_page'] = '';
$config['uri_protocol']	= 'REQUEST_URI';
$config['url_suffix'] = '';
$config['language']	= 'english';
$config['charset'] = 'UTF-8';
$config['enable_hooks'] = FALSE;
$config['subclass_prefix'] = 'MY_';
$config['composer_autoload'] = FALSE;
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';
$config['enable_query_strings'] = FALSE;
$config['controller_trigger'] = 'c';
$config['function_trigger'] = 'm';
$config['directory_trigger'] = 'd';
$config['allow_get_array'] = TRUE;
$config['log_threshold'] = 0;
$config['log_path'] = '';
$config['log_file_extension'] = '';
$config['log_file_permissions'] = 0644;
$config['log_date_format'] = 'Y-m-d H:i:s';
$config['error_views_path'] = '';
$config['cache_path'] = '';
$config['cache_query_string'] = FALSE;
$config['encryption_key'] = getenv('CI_ENCRYPTION_KEY') ?: 'c13ad327a2a396d070079ced1a8201aa035ae220fc277e877ba87103a7558aa4';
$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'ci_session';
$config['sess_expiration'] = 31536000;
//die($live.$sv);
if ($live=="0") {
    $config['sess_save_path'] = NULL;
}else{
    $config['sess_save_path'] = APPPATH . 'cache/sessions';
}
$config['sess_match_ip'] = FALSE;
// Jangan regenerasi ID session otomatis. Request AJAX yang bersamaan dapat
// masih membawa ID lama dan kehilangan session jika ID lama langsung dihapus.
$config['sess_time_to_update'] = 0;
$config['sess_regenerate_destroy'] = FALSE;
$config['cookie_prefix']	= '';
$config['cookie_domain']	= '';
$config['cookie_path']		= '/';
$config['cookie_secure']	= ($live == '1');
$config['cookie_httponly'] 	= TRUE;
$config['standardize_newlines'] = FALSE;
$config['global_xss_filtering'] = FALSE;
// $config['csrf_protection'] = FALSE;
// $config['csrf_token_name'] = 'csrf_test_name';
// $config['csrf_cookie_name'] = 'csrf_cookie_name';
// $config['csrf_expire'] = 7200;
// $config['csrf_regenerate'] = TRUE;
// $config['csrf_exclude_uris'] = array();
$config['compress_output'] = FALSE;
$config['time_reference'] = 'local';
$config['rewrite_short_tags'] = FALSE;
$config['proxy_ips'] = '';
// Ubah dari FALSE menjadi TRUE
$config['csrf_protection'] = TRUE;
$config['csrf_token_name'] = 'csrf_token';
$config['csrf_cookie_name'] = 'csrf_cookie';
$config['csrf_expire'] = 7200;
$config['csrf_regenerate'] = FALSE;
$config['csrf_exclude_uris'] = array('api/.*'); // Exclude API endpoints jika perlu
