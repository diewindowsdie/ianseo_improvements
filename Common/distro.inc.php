<?php
define('ProgramRelease', 'STABLE');
define('ProgramBuild', 'rev 254 [improvements LIVE v1.0]');
define("CurrentTag", "live_v1.0");
//pre-релизы собираются из ветки live - это lts ветка, предназначенная для проведения крупных соревнований, в нее попадают только критически важные обновления
define("UsePreReleases", true);
define('MinTimeOut', '120');
//Add the Timezone Check if not setup by system
if(strlen(ini_get('date.timezone')))
	date_default_timezone_set(ini_get('date.timezone'));
else
	date_default_timezone_set('UTC');
?>