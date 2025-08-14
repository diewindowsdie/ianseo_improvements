<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
if ($log) fwrite($log, "Executing <i>update_country_name_length_2025_08_14.php</i> update script...<br/><br/>\n");

//установим длину колонки Countries.CoName в 80 символов
if ($log) fwrite($log, "Changing column <b>CoName</b> length in table <b><i>Countries</i></b>...<br />\n");
safe_w_SQL('alter table Countries modify column CoName varchar(80) not null;');

if ($log) fwrite($log, "<i>update_country_name_length_2025_08_14.php</i> script finished successfully.<br/><br/>\n");
?><?php
