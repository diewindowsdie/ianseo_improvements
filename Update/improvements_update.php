<?php
$updateLogFilename = 'TV/Photos/improvements_update_log.html';

if (!$log = fopen($updateLogFilename, 'w')) {
    error_log("Unable to create improvements update log file: " . $updateLogFilename);
}

require_once 'improvements/update_judges.php';
require_once 'improvements/update_tournament_code.php';
require_once 'improvements/judges_order.php';
require_once 'improvements/update_event_name.php';
require_once 'improvements/add_middle_name.php';
require_once 'improvements/add_judges_2025_07_21.php';
require_once 'improvements/increase_country_name_length_2025_08_14.php';
require_once 'improvements/2025-09-10_add_device_note.php';
require_once 'improvements/2025-09-30_update_tournament_code_in_tvout.php';
require_once 'improvements/2025-10-14_update_module_parameter_name_length.php';
require_once 'improvements/2025-11-03_judge_last_name_nullable.php';

if ($log) fclose($log);
?>