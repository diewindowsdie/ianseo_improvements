<?php
define('ProgramRelease', 'STABLE');
define('ProgramBuild', 'rev 88 [improvements v3.6]');
define('MinTimeOut', '120');
//Add the Timezone Check if not setup by system
if(strlen(ini_get('date.timezone')))
	date_default_timezone_set(ini_get('date.timezone'));
else
	date_default_timezone_set('UTC');
?>