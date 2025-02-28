<?php
$updateLogFilename = 'TV/Photos/improvements_update_log.html';

if (!$log = fopen($updateLogFilename, 'w')) {
    error_log("Unable to create improvements update log file: " . $updateLogFilename);
}

require_once 'improvements/update_judges.php';
require_once 'improvements/update_tournament_code.php';
require_once 'improvements/judges_order.php';
require_once 'improvements/update_event_name.php';

if ($log) fclose($log);
?>