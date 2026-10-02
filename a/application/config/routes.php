<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['init'] = 'init';
$route['init/login_post'] = 'init/login_post';
$route['init/logout'] = 'init/logout';
$route['init/seed'] = 'init/seed';
$route['api/courses'] = 'api/courses';
$route['api/courses/(:any)'] = 'api/courses/$1';
$route['api/tasks'] = 'api/tasks';
$route['api/tasks/(:num)'] = 'api/tasks/$1';
$route['api/worklog'] = 'api/worklog';
