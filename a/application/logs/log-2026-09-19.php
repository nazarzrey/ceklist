<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-09-19 00:30:35 --> Query error: Table 'webkelas.tasks' doesn't exist - Invalid query: SELECT `tasks`.`id`, `tasks`.`course_id`, `tasks`.`title`, `tasks`.`type`, `tasks`.`due_label` AS `due`, `tasks`.`priority`, `tasks`.`status`, `tasks`.`note`, `tasks`.`created_at`, `tasks`.`updated_at`, `courses`.`name` AS `course_name`, `courses`.`short_name` AS `course_short`
FROM `tasks`
JOIN `courses` ON `courses`.`id` = `tasks`.`course_id`
ORDER BY FIELD(tasks.priority, "high", "medium", "low"), `tasks`.`id` DESC
ERROR - 2026-09-19 00:30:35 --> Severity: Warning --> include(C:\project\Web\webkelas\application\views\errors\html\error_db.php): Failed to open stream: No such file or directory C:\project\Web\webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:30:35 --> Severity: Warning --> include(): Failed opening 'C:\project\Web\webkelas\application\views\errors\html\error_db.php' for inclusion (include_path='.;C:/runtime/laragon/etc/php/pear') C:\project\Web\webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:30:35 --> Query error: Table 'webkelas.courses' doesn't exist - Invalid query: SELECT *
FROM `courses`
ORDER BY `start_time` ASC
ERROR - 2026-09-19 00:30:35 --> Severity: Warning --> include(C:\project\Web\webkelas\application\views\errors\html\error_db.php): Failed to open stream: No such file or directory C:\project\Web\webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:30:35 --> Severity: Warning --> include(): Failed opening 'C:\project\Web\webkelas\application\views\errors\html\error_db.php' for inclusion (include_path='.;C:/runtime/laragon/etc/php/pear') C:\project\Web\webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:30:35 --> Query error: Table 'webkelas.worklogs' doesn't exist - Invalid query: SELECT *
FROM `worklogs`
ORDER BY `logged_at` ASC
ERROR - 2026-09-19 00:30:35 --> Severity: Warning --> include(C:\project\Web\webkelas\application\views\errors\html\error_db.php): Failed to open stream: No such file or directory C:\project\Web\webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:30:35 --> Severity: Warning --> include(): Failed opening 'C:\project\Web\webkelas\application\views\errors\html\error_db.php' for inclusion (include_path='.;C:/runtime/laragon/etc/php/pear') C:\project\Web\webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:32:03 --> Query error: Table 'webkelas.worklogs' doesn't exist - Invalid query: SELECT *
FROM `worklogs`
ORDER BY `logged_at` ASC
ERROR - 2026-09-19 00:32:03 --> Severity: Warning --> include(C:\project\Web\webkelas\application\views\errors\html\error_db.php): Failed to open stream: No such file or directory C:\project\Web\webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:32:03 --> Severity: Warning --> include(): Failed opening 'C:\project\Web\webkelas\application\views\errors\html\error_db.php' for inclusion (include_path='.;C:/runtime/laragon/etc/php/pear') C:\project\Web\webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:32:03 --> Query error: Table 'webkelas.tasks' doesn't exist - Invalid query: SELECT `tasks`.`id`, `tasks`.`course_id`, `tasks`.`title`, `tasks`.`type`, `tasks`.`due_label` AS `due`, `tasks`.`priority`, `tasks`.`status`, `tasks`.`note`, `tasks`.`created_at`, `tasks`.`updated_at`, `courses`.`name` AS `course_name`, `courses`.`short_name` AS `course_short`
FROM `tasks`
JOIN `courses` ON `courses`.`id` = `tasks`.`course_id`
ORDER BY FIELD(tasks.priority, "high", "medium", "low"), `tasks`.`id` DESC
ERROR - 2026-09-19 00:32:03 --> Severity: Warning --> include(C:\project\Web\webkelas\application\views\errors\html\error_db.php): Failed to open stream: No such file or directory C:\project\Web\webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:32:03 --> Severity: Warning --> include(): Failed opening 'C:\project\Web\webkelas\application\views\errors\html\error_db.php' for inclusion (include_path='.;C:/runtime/laragon/etc/php/pear') C:\project\Web\webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:32:03 --> Query error: Table 'webkelas.courses' doesn't exist - Invalid query: SELECT *
FROM `courses`
ORDER BY `start_time` ASC
ERROR - 2026-09-19 00:32:03 --> Severity: Warning --> include(C:\project\Web\webkelas\application\views\errors\html\error_db.php): Failed to open stream: No such file or directory C:\project\Web\webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:32:03 --> Severity: Warning --> include(): Failed opening 'C:\project\Web\webkelas\application\views\errors\html\error_db.php' for inclusion (include_path='.;C:/runtime/laragon/etc/php/pear') C:\project\Web\webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:42:08 --> Query error: Table 'webkelas.courses' doesn't exist - Invalid query: SELECT *
FROM `courses`
ORDER BY `start_time` ASC
ERROR - 2026-09-19 00:42:08 --> Severity: Warning --> include(C:\project\Web\ci3_webkelas\application\views\errors\html\error_db.php): Failed to open stream: No such file or directory C:\project\Web\ci3_webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:42:08 --> Severity: Warning --> include(): Failed opening 'C:\project\Web\ci3_webkelas\application\views\errors\html\error_db.php' for inclusion (include_path='.;C:/runtime/laragon/etc/php/pear') C:\project\Web\ci3_webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:42:08 --> Query error: Table 'webkelas.tasks' doesn't exist - Invalid query: SELECT `tasks`.`id`, `tasks`.`course_id`, `tasks`.`title`, `tasks`.`type`, `tasks`.`due_label` AS `due`, `tasks`.`priority`, `tasks`.`status`, `tasks`.`note`, `tasks`.`created_at`, `tasks`.`updated_at`, `courses`.`name` AS `course_name`, `courses`.`short_name` AS `course_short`
FROM `tasks`
JOIN `courses` ON `courses`.`id` = `tasks`.`course_id`
ORDER BY FIELD(tasks.priority, "high", "medium", "low"), `tasks`.`id` DESC
ERROR - 2026-09-19 00:42:08 --> Severity: Warning --> include(C:\project\Web\ci3_webkelas\application\views\errors\html\error_db.php): Failed to open stream: No such file or directory C:\project\Web\ci3_webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:42:08 --> Severity: Warning --> include(): Failed opening 'C:\project\Web\ci3_webkelas\application\views\errors\html\error_db.php' for inclusion (include_path='.;C:/runtime/laragon/etc/php/pear') C:\project\Web\ci3_webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:42:08 --> Query error: Table 'webkelas.worklogs' doesn't exist - Invalid query: SELECT *
FROM `worklogs`
ORDER BY `logged_at` ASC
ERROR - 2026-09-19 00:42:08 --> Severity: Warning --> include(C:\project\Web\ci3_webkelas\application\views\errors\html\error_db.php): Failed to open stream: No such file or directory C:\project\Web\ci3_webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:42:08 --> Severity: Warning --> include(): Failed opening 'C:\project\Web\ci3_webkelas\application\views\errors\html\error_db.php' for inclusion (include_path='.;C:/runtime/laragon/etc/php/pear') C:\project\Web\ci3_webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:42:22 --> 404 Page Not Found: 
ERROR - 2026-09-19 00:42:22 --> Severity: Warning --> include(C:\project\Web\ci3_webkelas\application\views\errors\html\error_404.php): Failed to open stream: No such file or directory C:\project\Web\ci3_webkelas\system\system\core\Exceptions.php 183
ERROR - 2026-09-19 00:42:22 --> Severity: Warning --> include(): Failed opening 'C:\project\Web\ci3_webkelas\application\views\errors\html\error_404.php' for inclusion (include_path='.;C:/runtime/laragon/etc/php/pear') C:\project\Web\ci3_webkelas\system\system\core\Exceptions.php 183
