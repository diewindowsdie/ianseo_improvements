<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
if ($log) fwrite($log, "Executing <i>2025-09-30_update_tournament_code_in_tvout.php</i> update script...<br/><br/>\n");

//установим длину колонки TVOut.TVOTourCode в 15 символов
if ($log) fwrite($log, "Changing column <b>TVOTourCode</b> length in table <b><i>TVOut</i></b>...<br />\n");
safe_w_SQL('alter table TVOut modify column TVOTourCode varchar(15) not null;');

if ($log) fwrite($log, "<i>2025-09-30_update_tournament_code_in_tvout.php</i> script finished successfully.<br/><br/>\n");
