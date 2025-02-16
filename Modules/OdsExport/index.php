<?php

require_once(dirname(__FILE__, 3) .'/config.php');
CheckTourSession(true);
checkFullACL(AclCompetition, 'cExport', AclReadOnly);

require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Lib/Obj_RankFactory.php');

require_once('Common/ods/ods.php');

$excel = new ods();

//$TXT=array();

require_once('Participants.inc.php');
require_once('ResultInd.inc.php');
require_once('ResultTeam.inc.php');

$excel->save($_SESSION['TourCode'].'.ods', 'a');
die();
