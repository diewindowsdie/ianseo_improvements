<?php

require_once('Common/Lib/TournamentOfficials.php');

$pdf->setDocUpdate($PdfData->LastUpdate);

$pdf->SetFont($pdf->FontStd,'B',10);
$pdf->Cell(190, 6, $PdfData->Description, 1, 1, 'C', 1);
$pdf->SetFont($pdf->FontStd,'B',7);
$pdf->Cell(40, 6, $PdfData->EvName, 1, 0, 'L', 1);
$pdf->Cell(15, 6, $PdfData->TourWhen, 1, 0, 'C', 1);
$pdf->Cell(15, 6, $PdfData->Medal, 1, 0, 'C', 1);
$pdf->Cell(40, 6, $PdfData->Athlete, 1, 0, 'L', 1);
$pdf->Cell(80, 6, $PdfData->Country, 1, 1, 'L', 1);
$pdf->SetFont($pdf->FontStd,'',8);

$arrMedals = array(1=>'gold','2'=>'silver','3'=>'bronze');

foreach($PdfData->rankData['events'] as $Event => $section) {
	$Rows=0;
	foreach($arrMedals as $vMed)
	{
		if(!empty($section[$vMed]))
		{
			$Rows += count($section[$vMed]);
			foreach($section[$vMed] as $ath)
			{
				if(!empty($ath))
					$Rows += (count($ath['athletes'])-1);
			}
		}
	}
	if(!$Rows)
		continue;

	$blockHeight=$Rows*5;

	if(!$pdf->SamePage($blockHeight)) {
  		$pdf->AddPage();
		$pdf->SetFont($pdf->FontStd,'B',10);
		$pdf->Cell(190, 6, $PdfData->Description, 1, 1, 'C', 1);
		$pdf->SetFont($pdf->FontStd,'B',7);
		$pdf->Cell(40, 6, $PdfData->EvName, 1, 0, 'L', 1);
		$pdf->Cell(15, 6, $PdfData->TourWhen, 1, 0, 'C', 1);
		$pdf->Cell(15, 6, $PdfData->Medal, 1, 0, 'C', 1);
		$pdf->Cell(40, 6, $PdfData->Athlete, 1, 0, 'L', 1);
		$pdf->Cell(80, 6, $PdfData->Country, 1, 1, 'L', 1);
		$pdf->SetFont($pdf->FontStd,'',8);
	}
	$pdf->sety($pdf->gety()+1);
	$pdf->Cell(40, $blockHeight, $section['evName'], '1', 0, 'L', 0);
	$pdf->Cell(15, $blockHeight, date('d.m.Y', strtotime($section['date'])), '1', 0, 'R', 0);


	//Ciclo per ogni singola medaglia
	$X=$pdf->GetX();
	foreach($arrMedals as $kMed=>$vMed)
	{
		if(!empty($section[$vMed]))
		{
			foreach($section[$vMed] as $item) {
				$pdf->SetX($X);
				//Nome della medaglia
				$pdf->Cell(15, 5 * count($item['athletes']), $PdfData->{'Medal_'.$kMed}, '1', 0, 'L', 0);
				//Elenco Atleti
				$tmpX=$pdf->getX();
				$tmpY=$pdf->getY();
				$n=0;
				foreach($item['athletes'] as $ath) {
					$pdf->setXY($tmpX, $tmpY + 5*$n);
					$pdf->Cell(40, 5, $ath['athlete'],'RL' . ($n ? ($n==count($item['athletes'])-1 ? 'B' : '') : 'T' . (count($item['athletes'])==1 ? 'B':'')), 0, 'L', 0);
					$n++;
				}
				$pdf->setXY($tmpX+40, $tmpY);
				//Elenco NOC
				$pdf->Cell(80, 5 * count($item['athletes']), getFullCountryName($item['countryName'], $item['countryName2'], $item['countryName3']), 'RTB', 1, 'L', 0);
// 				$pdf->Cell(60, 5 * count($item['athletes']), $item['countryCode'] . '-' . $item['countryName'], 1, 1, 'L', 0);
			}
		}
	}
}

$pdf->SetY($pdf->GetY()+5);
TournamentOfficials::printOfficials($pdf);
?>