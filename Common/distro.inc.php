<?php
define('ProgramRelease', 'STABLE');
define('ProgramBuild', 'rev 254 [improvements v5.6]');
define("CurrentTag", "v5.6");
//pre-релизы собираются из ветки live - это lts ветка, предназначенная для проведения крупных соревнований, в нее попадают только критически важные обновления
define("UsePreReleases", false);
define("DefaultCompetitionResultsPagePrefix", "Common/Results/public/competition.php?code=");
define("PDFPrefix", "Common/Results/public/");
define('MinTimeOut', '120');
//Add the Timezone Check if not setup by system
if(strlen(ini_get('date.timezone')))
	date_default_timezone_set(ini_get('date.timezone'));
else
	date_default_timezone_set('UTC');
?>