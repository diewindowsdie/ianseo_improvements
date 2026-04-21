<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

$currentSessionFinalsIndividual = [];
//если просят конкретное соревнование - сохраним $_SESSION, который перетирается на время выполнения скрипта
//в OrisFunctions.php делается define языка распечаток на основании текущей сессии, поэтому нужно переопределить сессию до того как ее ктото попытается прочитать
if (isset($_REQUEST['TourId'])) {
    global $requestedForPublicReport;
    $requestedForPublicReport = true;

    $currentSessionFinalsIndividual = $_SESSION;
    CreateTourSession($_REQUEST['TourId']);
}

require_once('Common/Fun_FormatText.inc.php');
require_once('Common/pdf/ResultPDF.inc.php');
checkFullACL(AclIndividuals, '', AclReadOnly);

$isCompleteResultBook = true;

$pdf = new ResultPDF(get_text('BrakRank'));

if(isset($_REQUEST["IncBrackets"]) && $_REQUEST["IncBrackets"]==1)
	include 'PrnBracket.php';

if(isset($_REQUEST["IncBrackets"]) && $_REQUEST["IncBrackets"]==1 && isset($_REQUEST["IncRankings"]) && $_REQUEST["IncRankings"]==1)
	$pdf->AddPage();

if(isset($_REQUEST["IncRankings"]) && $_REQUEST["IncRankings"]==1)
	include 'PrnRanking.php';

if(isset($_REQUEST['ToFitarco']))
{
	$Dest='D';
	if (isset($_REQUEST['Dest']))
		$Dest=$_REQUEST['Dest'];
	$pdf->Output($_REQUEST['ToFitarco'],$Dest);
}
else
	$pdf->Output();

//если просят конкретное соревнование - восстановим $_SESSION, который перетирается на время выполнения скрипта
if (isset($_REQUEST['TourId'])) {
    $_SESSION = $currentSessionFinalsIndividual;
}

?>