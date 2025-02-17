<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

if ($log) fwrite($log, "Executing <i>judges_order.php</i> update script...<br/><br/>\n");

if ($log) fwrite($log, "Updating judge roles display order in table <b><i>InvolvedType</i></b>...<br/>\n");
safe_w_SQL("update InvolvedType set ItJudge = 3 where ItDescription = 'ChairmanJudgeDeputy';");
safe_w_SQL("update InvolvedType set ItJudge = 5 where ItDescription = 'ChiefSecretaryDeputy';");
safe_w_SQL("update InvolvedType set ItJudge = 6 where ItDescription = 'Judge';");
safe_w_SQL("update InvolvedType set ItJudge = 7 where ItDescription = 'RaceOfficer';");
safe_w_SQL("update InvolvedType set ItJudge = 8 where ItDescription = 'Spotter';");

if ($log) fwrite($log, "Updating judge subcategory for <b>FieldManager</b> judge role in table <b><i>InvolvedType</i></b>...<br/>\n");
safe_w_SQL("update InvolvedType set ItJudge=4, ItOC=0 where ItDescription='FieldResp';");
if ($log) fwrite($log, "<i>judges_order.php</i> script finished successfully.<br/><br/>\n");
?>