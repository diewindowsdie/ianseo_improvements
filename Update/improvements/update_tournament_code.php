<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
if ($log) fwrite($log, "Executing <i>update_tournament_code.php</i> update script...<br/><br/>\n");

//установим длину колонки Tournament.ToCode в 15 символов
if ($log) fwrite($log, "Changing column <b>ToCode</b> length in table <b><i>Tournament</i></b>...<br />\n");
safe_w_SQL('alter table Tournament modify column ToCode varchar(15) not null;');

if ($log) fwrite($log, "<i>update_tournament_code.php</i> script finished successfully.<br/><br/>\n");
?>