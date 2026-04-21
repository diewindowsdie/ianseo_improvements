<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');

$currentSessionQualTeam = [];
//если просят конкретное соревнование - сохраним $_SESSION, который перетирается на время выполнения скрипта
//в OrisFunctions.php делается define языка распечаток на основании текущей сессии, поэтому нужно переопределить сессию до того как ее ктото попытается прочитать
if (isset($_REQUEST['TourId'])) {
    global $requestedForPublicReport;
    $requestedForPublicReport = true;

    $currentSessionQualTeam = $_SESSION;
    CreateTourSession($_REQUEST['TourId']);
}

require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Lib/Obj_RankFactory.php');
require_once('Common/pdf/ResultPDF.inc.php');
require_once('Common/Lib/Fun_PrintOuts.php');
require_once('Common/OrisFunctions.php');
require_once('Common/pdf/PdfChunkLoader.php');

checkFullACL(AclQualification, '', AclReadOnly);

$PdfData=getQualificationTeam();
$rankData=$PdfData->rankData;

global $hideTempHeader;
if (isset($_REQUEST["hideTempHeader"])) {
    $hideTempHeader = true;
}

if(!isset($isCompleteResultBook))
	$pdf = new ResultPDF($PdfData->Description);

require_once(PdfChunkLoader('QualTeam.inc.php'));

//если просят конкретное соревнование - восстановим $_SESSION, который перетирается на время выполнения скрипта
if (isset($_REQUEST['TourId'])) {
    $_SESSION = $currentSessionQualTeam;
}

if(isset($__ExportPDF))
	$__ExportPDF = $pdf->Output('','S');
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