<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

$currentSessionBracketIndividual = [];
//если просят конкретное соревнование - сохраним $_SESSION, который перетирается на время выполнения скрипта
//в OrisFunctions.php делается define языка распечаток на основании текущей сессии, поэтому нужно переопределить сессию до того как ее ктото попытается прочитать
if (isset($_REQUEST['TourId'])) {
    global $requestedForPublicReport;
    $requestedForPublicReport = true;

    $currentSessionBracketIndividual = $_SESSION;
    CreateTourSession($_REQUEST['TourId']);
}

include_once('Common/pdf/ResultPDF.inc.php');
include_once('Common/Fun_FormatText.inc.php');
include_once('Common/Lib/ArrTargets.inc.php');
require_once('Common/Fun_Phases.inc.php');
require_once('Common/Lib/Fun_PrintOuts.php');
require_once('Common/OrisFunctions.php');
require_once('Common/pdf/PdfChunkLoader.php');
checkFullACL(AclIndividuals, '', AclReadOnly);

$Events='';
if(isset($_REQUEST["Event"]) && $_REQUEST["Event"][0]!=".") {
	$Events=$_REQUEST["Event"];
	// select all children and subchildren of these events
	if(!is_array($Events)) {
		$Events=array($Events);
	}

	if(empty($_REQUEST['ShowChildren'])) {
		$Events = getChildrenEvents($_REQUEST["Event"]);
	}
}

$PdfData=getBracketsIndividual($Events,
	 false,
	 isset($_REQUEST["ShowTargetNo"]),
	 isset($_REQUEST["ShowSchedule"]),
	 isset($_REQUEST["ShowSetArrows"])
	 );

if(!isset($isCompleteResultBook))
	$pdf = new ResultPDF($PdfData->Description);
//$pdf->SetAutoPageBreak(false);

require_once(PdfChunkLoader('BracketIndividual.inc.php'));

//если просят конкретное соревнование - восстановим $_SESSION, который перетирается на время выполнения скрипта
if (isset($_REQUEST['TourId'])) {
    $_SESSION = $currentSessionBracketIndividual;
}

if(isset($__ExportPDF))
{
	$__ExportPDF = $pdf->Output('','S');
}
elseif(!isset($isCompleteResultBook))
{
	if(isset($_REQUEST['ToFitarco']))
	{
		$Dest='D';
		if (isset($_REQUEST['Dest']))
			$Dest=$_REQUEST['Dest'];

		if ($Dest=='S')
			print $pdf->Output($_REQUEST['ToFitarco'],$Dest);
		else
			$pdf->Output($_REQUEST['ToFitarco'],$Dest);
	}
	else
		$pdf->Output();
}


?>