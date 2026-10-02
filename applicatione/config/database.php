<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;
$server = $_SERVER['SERVER_NAME'] ?? '';
$is_local = ($server === 'localhost' || $server === '127.0.0.1' || $server === '::1' || strpos($server, '192.') === 0 || strpos($server, '.local') !== false);
if($is_local){
	$h = "localhost:3308";
	$u = "dbJurnal";
	$p = "123";
	$d = "jurnal";
}else{	
	$h = "";
	$u = "";
	$p = "";
	$d = "";
}
$db['default'] = array(
	'dsn'	=> '',
	'hostname' => $h,
	'username' => $u,
	'password' => $p,
	'database' => $d,	
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => TRUE,
	'failover' => array(),
	'save_queries' => TRUE
);
