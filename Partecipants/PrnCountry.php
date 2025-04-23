<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
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