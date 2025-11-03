<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
if ($log) fwrite($log, "Executing <i>2025-10-14_update_module_parameter_name_length.php</i> update script...<br/><br/>\n");

//установим длину колонки TVOut.TVOTourCode в 15 символов
if ($log) fwrite($log, "Changing column <b>MpParameter</b> length in table <b><i>ModulesParameters</i></b>...<br />\n");
safe_w_SQL('alter table ModulesParameters modify column MpParameter varchar(100) not null;');

if ($log) fwrite($log, "<i>2025-10-14_update_module_parameter_name_length.php</i> script finished successfully.<br/><br/>\n");
