<?php
require_once('Common/Lib/TournamentOfficials.php');
require_once('Common/Lib/StatusesLegendProvider.php');

$pdf->NumberThousandsSeparator=$PdfData->NumberThousandsSeparator;
$pdf->NumberDecimalSeparator=$PdfData->NumberDecimalSeparator;
$pdf->Continue=$PdfData->Continue;
$pdf->TotalShort=$PdfData->TotalShort;
$pdf->ShotOffShort=$PdfData->ShotOffShort;
$pdf->CoinTossShort=$PdfData->CoinTossShort;

$legendStatusProvider = new StatusLegendProvider($pdf, true);
if (!isset($hideTempHeader)) {
    $hideTempHeader = false;
}

$pdf->SetFont($pdf->FontStd,'B',$pdf->FontSizeTitle + 2);
$pdf->Cell(190, 10, get_text("Q-Session", "Tournament"), 0, 1, 'C', 0, '', 1, false, 'T', 'T');

if(count($rankData['sections'])) {
	$pdf->setDocUpdate($rankData['meta']['lastUpdate']);

	foreach($rankData['sections'] as $section) {
		$meta=$section['meta'];

		if(!$pdf->SamePage(4*count($section['items'][0]['athletes'])+(!empty($meta['printHeader']) ? 30 : 16)+($section['meta']['sesArrows'] ? 8:0)))
			$pdf->AddPage();

		$pdf->writeGroupHeaderPrnTeamAbs($meta, false, $hideTempHeader);

		$endQualified = false;
		foreach($section['items'] as $item) {
			if(!$pdf->SamePage(4*count($item['athletes']))) {
				$pdf->AddPage();
				$pdf->writeGroupHeaderPrnTeamAbs($meta,true, $hideTempHeader);
			}

			$pdf->writeDataRowPrnTeamAbs($item, ($endQualified===false && $item['rank']>$meta['qualifiedNo']), $meta['running']);

			if($item['rank']>$meta['qualifiedNo'])
				$endQualified = true;
		}
		$pdf->SetY($pdf->GetY()+5);
	}

    TournamentOfficials::printOfficials($pdf);

    $legendStatusProvider->printLegend();
}

