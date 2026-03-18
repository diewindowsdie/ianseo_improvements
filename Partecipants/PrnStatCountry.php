<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');

$currentSessionRegionStatistics = [];
//если просят конкретное соревнование - сохраним $_SESSION, который перетирается на время выполнения скрипта
//в OrisFunctions.php делается define языка распечаток на основании текущей сессии, поэтому нужно переопределить сессию до того как ее ктото попытается прочитать
if (isset($_REQUEST['TourId'])) {
    global $forceHidingFullNamesAndBirthdate;
    $forceHidingFullNamesAndBirthdate = true;

    $currentSessionRegionStatistics = $_SESSION;
    CreateTourSession($_REQUEST['TourId']);
}

checkFullACL(AclParticipants, 'pEntries', AclReadOnly);
require_once('Common/pdf/ResultPDF.inc.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/OrisFunctions.php');
require_once('Common/pdf/PdfChunkLoader.php');

// ATTENTION!
// MUST BE called $PdfData

$PdfData=($_REQUEST["countryIndex"]
    ? getStatEntriesByCountries(false, $_REQUEST['AthletesOnly'] === "1", $_REQUEST["countryIndex"])
    : getStatEntriesByCountries(false, $_REQUEST['AthletesOnly'] === "1")
);

if(!isset($isCompleteResultBook))
	$pdf = new ResultPDF($PdfData->Description,false);

require(PdfChunkLoader('StatByCountry.inc.php'));

//если просят конкретное соревнование - восстановим $_SESSION, который перетирается на время выполнения скрипта
if (isset($_REQUEST['TourId'])) {
    $_SESSION = $currentSessionRegionStatistics;
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