<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');

$currentSessionAthletesByCountry = [];
//если просят конкретное соревнование - сохраним $_SESSION, который перетирается на время выполнения скрипта
//в OrisFunctions.php делается define языка распечаток на основании текущей сессии, поэтому нужно переопределить сессию до того как ее ктото попытается прочитать
if (isset($_REQUEST['TourId'])) {
    global $forceHidingFullNamesAndBirthdate;
    $forceHidingFullNamesAndBirthdate = true;

    $currentSessionAthletesByCountry = $_SESSION;
    CreateTourSession($_REQUEST['TourId']);
}

checkFullACL(AclParticipants, 'pEntries', AclReadOnly);
require_once('Common/pdf/ResultPDF.inc.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/OrisFunctions.php');
require_once('Common/pdf/PdfChunkLoader.php');

// ATTENTION!
// MUST BE called $PdfData
error_reporting(E_ALL ^ E_NOTICE);
$includedRegionIndexes=array(1 => true, 2 => false, 3 => false);
if (isset($_REQUEST["includeRegion2"])) {
    $includedRegionIndexes[2] = $_REQUEST["includeRegion2"] == "on";
}
if (isset($_REQUEST["includeRegion3"])) {
    $includedRegionIndexes[3] = $_REQUEST["includeRegion3"] == "on";
}
$PdfData=getStartListByCountries(false, isset($_REQUEST['Athletes']), (isset($_REQUEST["MainOrder"]) ? $_REQUEST["MainOrder"] : false), array(), array(), $includedRegionIndexes);

if(!isset($isCompleteResultBook))
	$pdf = new ResultPDF($PdfData->Description);

require_once(PdfChunkLoader('Country.inc.php'));

//если просят конкретное соревнование - восстановим $_SESSION, который перетирается на время выполнения скрипта
if (isset($_REQUEST['TourId'])) {
    $_SESSION = $currentSessionAthletesByCountry;
}

if(!isset($isCompleteResultBook))
{
	if(isset($_REQUEST['ToFitarco']))
	{
		$Dest='D';
		if (isset($_REQUEST['Dest']))
			$Dest=$_REQUEST['Dest'];
		$pdf->Output($_REQUEST['ToFitarco'],$Dest);
	}
	else
		$pdf->Output();
}
?>