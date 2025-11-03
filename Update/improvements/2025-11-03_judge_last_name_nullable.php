<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
if ($log) fwrite($log, "Executing <i>2025-11-03_judge_last_name_nullable.php</i> update script...<br/><br/>\n");

safe_w_SQL('alter table TournamentInvolved modify column TiLastName varchar(255)');

if ($log) fwrite($log, "<i>2025-11-03_judge_last_name_nullable.php</i> script finished successfully.<br/><br/>\n");
?><?php
