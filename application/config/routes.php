<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['init'] = 'init';
$route['init/login_post'] = 'init/login_post';
$route['init/logout'] = 'init/logout';
$route['init/seed'] = 'init/seed';
$route['auth/login'] = 'auth/login';
$route['auth/magic/(:any)'] = 'auth/magic/$1';
$route['auth/logout'] = 'auth/logout';
$route['api/session'] = 'api/session';
$route['api/announcements'] = 'api/announcements';
$route['api/announcements/(:num)/read'] = 'api/announcement_read/$1';
$route['api/class-tasks'] = 'api/class_tasks';
$route['api/class-tasks/(:num)/check'] = 'api/class_task_check/$1';
$route['api/profile'] = 'api/profile';
$route['api/profile-link'] = 'api/profile_link';
$route['api/courses'] = 'api/courses';
$route['api/courses/(:any)'] = 'api/courses/$1';
$route['api/tasks'] = 'api/tasks';
$route['api/tasks/(:num)'] = 'api/tasks/$1';
$route['api/worklog'] = 'api/worklog';
