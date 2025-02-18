<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
if ($log) fwrite($log, "Executing <i>update_event_name.php</i> update script...<br/><br/>\n");

//установим длину колонки Events.EvEventName в 100 символов
if ($log) fwrite($log, "Changing column <b>EvEventName</b> length in table <b><i>Events</i></b>...<br />\n");
safe_w_SQL('alter table Events modify column EvEventName varchar(100) not null;');

if ($log) fwrite($log, "<i>update_event_name.php</i> script finished successfully.<br/><br/>\n");
?>