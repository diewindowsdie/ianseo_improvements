<?php
define('ProgramRelease', 'STABLE');
define('ProgramBuild', 'rev 47 [improvements v4.15.1]');
define("CurrentTag", "ianseo_improvements_2025-09-03_rev47_v4.15.1");
define('MinTimeOut', '120');
//Add the Timezone Check if not setup by system
if(strlen(ini_get('date.timezone')))
	date_default_timezone_set(ini_get('date.timezone'));
else
	date_default_timezone_set('UTC');
?>