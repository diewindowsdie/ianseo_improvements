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

$spaceBetweenSections = 5;

$legendStatusProvider = new StatusLegendProvider($pdf, true);

if(count($rankData['sections'])) {
	$DistSize = 11;
	$AddSize=0;
	$pdf->setDocUpdate($rankData['meta']['lastUpdate']);
    $currentSectionIndex = 0;
	foreach($rankData['sections'] as $section) {
        $currentSectionIndex++;
		//Calcolo Le Misure per i Campi
		if($section['meta']['numDist']>=4 && !$rankData['meta']['double'])
			$DistSize = 44/$section['meta']['numDist'];
		elseif($section['meta']['numDist']>=4 && $rankData['meta']['double'])
			$DistSize = 44/(($section['meta']['numDist']/2)+1);
		else
			$AddSize = (44-($section['meta']['numDist']*11))/2;

        if ($currentSectionIndex == count($rankData['sections'])) {
            //last group:
            //check if header message, group header, group, officials information and legend fits on the same page
            $headerSize = 7.5 + //message
                6 + //table header
                0.5; //separator before data starts
            $dataSize = 4 * count($section['items']) + $spaceBetweenSections;
            if (count($section['items']) > $section['meta']['qualifiedNo']) {
                $dataSize += 1;
            }
            $officialsSize = TournamentOfficials::getOfficialsBlockHeight();
            $legendSize = $legendStatusProvider->getLegendBlockHeight();

            if (!$pdf->SamePage($headerSize + $dataSize + $officialsSize + $legendSize))
                $pdf->AddPage();
        } else {
            //Verifico se l'header e qualche riga ci stanno nella stessa pagina altrimenti salto alla prosisma
            if(!$pdf->SamePage(15+(strlen($section['meta']['printHeader']) ? 8:0)+($section['meta']['sesArrows'] ? 8:0)))
                $pdf->AddPage();
        }
		$pdf->writeGroupHeaderPrnIndividualAbs($section['meta'], $DistSize, $AddSize, $section['meta']['running'], $section['meta']['numDist'], $rankData['meta']['double'], false);
		$EndQualified = ($section['meta']['qualifiedNo']==0);
        $StartQualified = ($section['meta']['firstQualified']==1);
		foreach($section['items'] as $item) {
		    if(!$StartQualified AND ($section['meta']['finished'] ? $item['rank']: $item['rankBeforeSO']+$item['ct'])>=$section['meta']['firstQualified']) {
                $pdf->SetFont($pdf->FontStd,'',1);
		        $pdf->Cell(190, 1,  '', 1, 1, 'C', 1);
                if (!$pdf->SamePage(4* ($rankData['meta']['double'] ? 2 : 1))) {
                    $pdf->AddPage();
                    $pdf->writeGroupHeaderPrnIndividualAbs($section['meta'], $DistSize, $AddSize, $section['meta']['running'], $section['meta']['numDist'], $rankData['meta']['double'], true);
                }
                $StartQualified = true;
            }
			if(!$EndQualified AND $item['rank']>($section['meta']['qualifiedNo']+$section['meta']['firstQualified']-1))	{
				$pdf->SetFont($pdf->FontStd,'',1);
				$pdf->Cell(190, 1,  '', 1, 1, 'C', 1);
				if (!$pdf->SamePage(4* ($rankData['meta']['double'] ? 2 : 1))) {
					$pdf->AddPage();
					$pdf->writeGroupHeaderPrnIndividualAbs($section['meta'], $DistSize, $AddSize, $section['meta']['running'], $section['meta']['numDist'], $rankData['meta']['double'], true);
				}
				$EndQualified = true;
			}

			if (!$pdf->SamePage(4* ($rankData['meta']['double'] ? 2 : 1))) {
				$pdf->AddPage();
				$pdf->writeGroupHeaderPrnIndividualAbs($section['meta'], $DistSize, $AddSize, $section['meta']['running'], $section['meta']['numDist'], $rankData['meta']['double'], true);
			}
			$pdf->writeDataRowPrnIndividualAbs($item, $DistSize, $AddSize, $section['meta']['running'],$section['meta']['numDist'], $rankData['meta']['double'], ($PdfData->family=='Snapshot' ? $section['meta']['snapDistance']: 0));

		}
		$pdf->SetY($pdf->GetY()+$spaceBetweenSections);
	}

    TournamentOfficials::printOfficials($pdf);

    $legendStatusProvider->printLegend();
}
