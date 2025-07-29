<?php

$pdf->HideCols = $PdfData->HideCols;
$pdf->setDocUpdate($PdfData->Timestamp ?? $PdfData->LastUpdate ?? '');

$SinglePage=isset($_REQUEST['SinglePage']);
$TargetFace=(isset($_REQUEST['tf']) && $_REQUEST['tf']==1);
$ShowStatusLegend = false;
$FirstTime=true;
$OldTeam='';
$OldSession='qwe';

$LongCell=$pdf->getPageWidth()/4;
$SesCell=7; // session
$TgtCell=10; // target  subclass, status
$PalCell=14; // 1-5 squares
$divAndClassCell=20;
$birthdayCell = 15;

$W=$SesCell+$PalCell+($TgtCell*3) + $divAndClassCell*2;
$t=($pdf->getPageWidth()-$W-20)/2;
$nationCell = $t - 25;
$athleteCell = $t + 10;

if (isset($PdfData->Data['Items']) && count($PdfData->Data['Items'])>0) {
	if(isset($_REQUEST['Email'])) {
		require_once(dirname(__FILE__).'/Country-emails.inc.php');
	} else {
		require_once(dirname(__FILE__).'/Country-standard.inc.php');
	}
}

//Legenda per la partecipazione alle varie fasi
if(!$PdfData->HideCols)
{
	$pdf->SetDefaultColor();

	$pdf->DrawPartecipantLegend();
//Legenda per lo stato di ammisisone alle gare
	if($ShowStatusLegend) {
		$pdf->SetDefaultColor();
		$pdf->DrawStatusLegend();

	}
}

?>