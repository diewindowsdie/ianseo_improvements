<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
echo "Executing <i>update_tournament_code.php</i> update script...<br/><br/>";

//установим длину колонки Tournament.ToCode в 15 символов
echo "Changing column <b>ToCode</b> length in table <b><i>Tournament</i></b>...<br />";
safe_w_SQL('alter table Tournament modify column ToCode varchar(15) not null;');
?>