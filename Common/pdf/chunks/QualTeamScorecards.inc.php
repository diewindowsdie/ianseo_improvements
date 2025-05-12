<?php

$pdf->NumberThousandsSeparator=$PdfData->NumberThousandsSeparator;
$pdf->NumberDecimalSeparator=$PdfData->NumberDecimalSeparator;
$pdf->Continue=$PdfData->Continue;
$pdf->TotalShort=$PdfData->TotalShort;

if(count($rankData['sections'])) {
	$pdf->setDocUpdate($rankData['meta']['lastUpdate']);

	foreach($rankData['sections'] as $section) {
		$meta=$section['meta'];

		if(!$pdf->SamePage(4*count($section['items'][0]['athletes'])+(!empty($meta['printHeader']) ? 30 : 16)+($section['meta']['sesArrows'] ? 8:0)))
			$pdf->AddPage();

		$pdf->writeGroupHeaderPrnTeamScorecards($meta, false);


		foreach($section['items'] as $item) {
			if(!$pdf->SamePage(4*(count($item['athletes'])+1))) {
				$pdf->AddPage();
				$pdf->writeGroupHeaderPrnTeamScorecards($meta,true);
			}

			$pdf->writeDataRowPrnTeamAbsScorecards($item);
		}
		$pdf->SetY($pdf->GetY()+5);
	}
}

