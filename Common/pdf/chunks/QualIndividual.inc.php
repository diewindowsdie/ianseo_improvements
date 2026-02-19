<?php

require_once('Common/Lib/TournamentOfficials.php');
require_once('Common/Lib/StatusesLegendProvider.php');
//error_reporting(E_ALL);

//$pdf->HideCols=$PdfData->HideCols;
$pdf->NumberThousandsSeparator=$PdfData->NumberThousandsSeparator;
$pdf->NumberDecimalSeparator=$PdfData->NumberDecimalSeparator;
$pdf->Continue=$PdfData->Continue;
$pdf->TotalShort=$PdfData->TotalShort;
$pdf->ShotOffShort=$PdfData->ShotOffShort;
$pdf->CoinTossShort=$PdfData->CoinTossShort;

$officialsSize = TournamentOfficials::getOfficialsBlockHeight();
$spaceBetweenSections = 5;

$legendStatusProvider = new StatusLegendProvider($pdf, true);
$legendSize = $legendStatusProvider->getLegendBlockHeight();

global $hideTempHeader;
if (!isset($hideTempHeader)) {
    $hideTempHeader = false;
}

if(count($rankData['sections'])) {
	$DistSize = 11;
	$pdf->setDocUpdate($rankData['meta']['lastUpdate']);
    $currentSectionIndex = 0;

    $pdf->SetFont($pdf->FontStd,'B',$pdf->FontSizeTitle + 2);
    $pdf->Cell(190, 10, get_text("Q-Session", "Tournament"), 0, 1, 'C', 0, '', 1, false, 'T', 'T');

    foreach($rankData['sections'] as $section) {
        $AddSize=0;
        $currentSectionIndex++;
		//Calcolo Le Misure per i Campi
		if($section['meta']['numDist']>=4 && !$rankData['meta']['double'])
			$DistSize = 44/$section['meta']['numDist'];
		elseif($section['meta']['numDist']>=4 && $rankData['meta']['double'])
			$DistSize = 44/(($section['meta']['numDist']/2)+1);
		else
			$AddSize = (44-($section['meta']['numDist']*11))/2;

        $totalRowsInGroup = count($section['items']);
        $spaceNeeded = 4 * min(3, count($section['items'])) + $officialsSize + $legendSize;

        //Verifico se l'header e qualche riga ci stanno nella stessa pagina altrimenti salto alla prosisma
        if(!$pdf->SamePage(15+(strlen($section['meta']['printHeader']) ? 8:0)+($section['meta']['sesArrows'] ? 8:0)))
            $pdf->AddPage();

        //предотвращаем ситуацию когда рендерится заголовок группы, а строк мало и все они лезут на следующую страницу
        if ($totalRowsInGroup < 4 && $currentSectionIndex == count($rankData['sections'])) {
            $spaceNeeded += 6 + 4 + //заголовок группы и поля таблицы
                (strlen($section['printHeader']) ? 7.5 : 0); //текстовый заголовок справа

            if (!$pdf->SamePage($spaceNeeded) && $currentSectionIndex == count($rankData['sections'])) {
                $pdf->AddPage();
            }
        }

        $pdf->writeGroupHeaderPrnIndividualAbs($section['meta'], $DistSize, $AddSize, $section['meta']['running'], $section['meta']['numDist'], $rankData['meta']['double'], false, $hideTempHeader, $rankData["meta"]["InternationalProtocol"]);
		$EndQualified = ($section['meta']['qualifiedNo']==0);
        $StartQualified = ($section['meta']['firstQualified']==1);
        $dataIndex = 0;
		foreach($section['items'] as $item) {
            $dataIndex++;
            //хотим, чтобы как минимум три строки (если в группе меньше - то вся группа) были на той же странице, что и легенда и подписи ГСК
            if ($dataIndex === $totalRowsInGroup - min(3, $totalRowsInGroup) + 1) {
                //print_r($dataIndex . ' ');
                $spaceNeeded = 4 * min(3, $totalRowsInGroup) + $officialsSize + $legendSize;
                //если три последние (если в группе меньше - то вся группа) строки + легенда + подписи не лезут - разрываем страницу
                //проверяем только последнюю группу
                if (!$pdf->SamePage($spaceNeeded) && $currentSectionIndex == count($rankData['sections'])) {
                    $pdf->AddPage();
                    $pdf->writeGroupHeaderPrnIndividualAbs($section['meta'], $DistSize, $AddSize, $section['meta']['running'], $section['meta']['numDist'], $rankData['meta']['double'], true, $hideTempHeader, $rankData["meta"]["InternationalProtocol"]);
                }
            }
		    if(!$StartQualified AND ($section['meta']['finished'] ? $item['rank']: $item['rankBeforeSO']+$item['ct'])>=$section['meta']['firstQualified']) {
                $pdf->SetFont($pdf->FontStd,'',1);
		        $pdf->Cell(190, 1,  '', 1, 1, 'C', 1);
                if (!$pdf->SamePage(4* ($rankData['meta']['double'] ? 2 : 1))) {
                    $pdf->AddPage();
                    $pdf->writeGroupHeaderPrnIndividualAbs($section['meta'], $DistSize, $AddSize, $section['meta']['running'], $section['meta']['numDist'], $rankData['meta']['double'], true, $hideTempHeader, $rankData["meta"]["InternationalProtocol"]);
                }
                $StartQualified = true;
            }
			if(!$EndQualified AND $item['rank']>($section['meta']['qualifiedNo']+$section['meta']['firstQualified']-1))	{
				$pdf->SetFont($pdf->FontStd,'',1);
				$pdf->Cell(190, 1,  '', 1, 1, 'C', 1);
				if (!$pdf->SamePage(4* ($rankData['meta']['double'] ? 2 : 1))) {
					$pdf->AddPage();
					$pdf->writeGroupHeaderPrnIndividualAbs($section['meta'], $DistSize, $AddSize, $section['meta']['running'], $section['meta']['numDist'], $rankData['meta']['double'], true, $hideTempHeader, $rankData["meta"]["InternationalProtocol"]);
				}
				$EndQualified = true;
			}

			if (!$pdf->SamePage(4* ($rankData['meta']['double'] ? 2 : 1))) {
				$pdf->AddPage();
				$pdf->writeGroupHeaderPrnIndividualAbs($section['meta'], $DistSize, $AddSize, $section['meta']['running'], $section['meta']['numDist'], $rankData['meta']['double'], true, $hideTempHeader, $rankData["meta"]["InternationalProtocol"]);
			}
			$pdf->writeDataRowPrnIndividualAbs($item, $DistSize, $AddSize, $section['meta']['running'],$section['meta']['numDist'], $rankData['meta']['double'], ($PdfData->family=='Snapshot' ? $section['meta']['snapDistance']: 0), "TB", $rankData["meta"]["InternationalProtocol"]);

		}
		$pdf->SetY($pdf->GetY()+$spaceBetweenSections);
	}

    //один раз отодвинем назад, потому что отступ логика подписей добавляет сама
    $pdf->SetY($pdf->GetY()-$spaceBetweenSections);

    TournamentOfficials::printOfficials($pdf);
    $legendStatusProvider->printLegend();
}
