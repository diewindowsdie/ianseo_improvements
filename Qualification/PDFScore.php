<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');

$currentSessionFilledScorecards = [];
//если просят конкретное соревнование - сохраним $_SESSION, который перетирается на время выполнения скрипта
//в OrisFunctions.php делается define языка распечаток на основании текущей сессии, поэтому нужно переопределить сессию до того как ее ктото попытается прочитать
if (isset($_REQUEST['TourId'])) {
    global $requestedForPublicReport;
    $requestedForPublicReport = true;

    $currentSessionFilledScorecards = $_SESSION;
    CreateTourSession($_REQUEST['TourId']);
}

require_once('Common/pdf/ScorePDF.inc.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Fun_Sessions.inc.php');
require_once('Common/Lib/ScorecardsLib.php');
checkFullACL(AclQualification, '', AclReadOnly);

// switch to decide which scorecard type to print $_REQUEST['TourField3D']=$_SESSION['TourField3D'];

$Session=intval($_REQUEST['x_Session']);

if(!empty($_REQUEST['SessionType']) and $_REQUEST['SessionType']=='E') {
	$Session=$_REQUEST['x_ElimSession'];
	$_REQUEST['x_Phase']=intval($_REQUEST['x_Session']);
	$_REQUEST['ScoreDist']=array(1);
}


$pdf=CreateSessionScorecard($Session, $_REQUEST['x_From'], (empty($_REQUEST['x_To']) ? $_REQUEST['x_From'] : $_REQUEST['x_To']), $_REQUEST);

//если просят конкретное соревнование - восстановим $_SESSION, который перетирается на время выполнения скрипта
if (isset($_REQUEST['TourId'])) {
    $_SESSION = $currentSessionFilledScorecards;
}

$pdf->output();
