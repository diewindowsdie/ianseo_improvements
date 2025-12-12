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

global $hideTempHeader;
if (!isset($hideTempHeader)) {
    $hideTempHeader = false;
}

$pdf->SetFont($pdf->FontStd,'B',$pdf->FontSizeTitle + 2);
$pdf->Cell(190, 10, get_text("Q-Session", "Tournament"), 0, 1, 'C', 0, '', 1, false, 'T', 'T');

$officialsSize = TournamentOfficials::getOfficialsBlockHeight();
$legendSize = $legendStatusProvider->getLegendBlockHeight();
$currentSectionIndex = 0;
$spaceBetweenSections = 5;

if(count($rankData['sections'])) {
	$pdf->setDocUpdate($rankData['meta']['lastUpdate']);

	foreach($rankData['sections'] as $section) {
		$meta=$section['meta'];
        $currentSectionIndex++;

		if(!$pdf->SamePage(4*count($section['items'][0]['athletes'])+(!empty($meta['printHeader']) ? 30 : 16)+($section['meta']['sesArrows'] ? 8:0)))
			$pdf->AddPage();

		$pdf->writeGroupHeaderPrnTeamAbs($meta, false, $hideTempHeader, $rankData["meta"]["InternationalProtocol"], $rankData["meta"]["HidePatronymicAndBirthDate"]);

		$endQualified = false;
        $dataIndex = 0;
		foreach($section['items'] as $item) {
            $dataIndex++;

            //хотим, чтобы как минимум две строки были на той же странице, что и легенда и подписи ГСК
            if ($dataIndex + 1 === count($section['items'])) {
                $spaceNeeded = 2 * (4 * count($item['athletes'])) + $officialsSize + $legendSize +
                    $spaceBetweenSections + //отступ до подписей
                    + 5; //отступ до легенды
                //если две последние строки + легенда + подписи не лезут - разрываем страницу
                //проверяем только последнюю группу
                if (!$pdf->SamePage($spaceNeeded) && $currentSectionIndex === count($rankData['sections'])) {
                    $pdf->AddPage();
                    $pdf->writeGroupHeaderPrnTeamAbs($meta, true, $hideTempHeader, $rankData["meta"]["InternationalProtocol"], $rankData["meta"]["HidePatronymicAndBirthDate"]);
                }
            }

            if(!$pdf->SamePage(4*count($item['athletes']))) {
				$pdf->AddPage();
				$pdf->writeGroupHeaderPrnTeamAbs($meta,true, $hideTempHeader, $rankData["meta"]["InternationalProtocol"], $rankData["meta"]["HidePatronymicAndBirthDate"]);
			}

			$pdf->writeDataRowPrnTeamAbs($item, ($endQualified===false && $item['rank']>$meta['qualifiedNo']), $meta['running'], $rankData["meta"]["InternationalProtocol"]);

			if($item['rank']>$meta['qualifiedNo'])
				$endQualified = true;
		}
		$pdf->SetY($pdf->GetY()+5);
	}

    //один раз отодвинем назад, потому что отступ логика подписей добавляет сама
    $pdf->SetY($pdf->GetY()-5);
    TournamentOfficials::printOfficials($pdf);

    $legendStatusProvider->printLegend();
}

