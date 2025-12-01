<?php

require_once('Common/Lib/TournamentOfficials.php');
require_once('Common/Lib/StatusesLegendProvider.php');

$PdfData->LastUpdate=$PdfData->rankData['meta']['lastUpdate'];

$pdf->setDocUpdate($PdfData->LastUpdate);
$legendStatusProvider = new StatusLegendProvider($pdf);

$FirstPage=true;
$pdf->SetFont($pdf->FontStd,'B',12);
$pdf->Cell(190, 10, get_text('FinalRankings', 'Tournament'), 0, 1, 'C', 0, '', 1, false, 'T', 'T');

$currentSectionIndex = 0;
$spaceBetweenSections = 5;

$maxNumPhases = 0;
$maxElimRounds = 0;
$averageAthleteNameLength = 0;
$averageRegionNameLength = 0;

$athleteNameColumnLength = 0;

foreach ($PdfData->rankData['sections'] as $section) {
    //сначала, найдем самые "большие" финалы и на их основе посчитаем, сколько у нас есть место под имя спортсмена плюс информацию о регионе
    $ElimCols=0;
    if($section['meta']['elimType']!=5) {
        if($section['meta']['elim1']) $ElimCols++;
        if($section['meta']['elim2']) $ElimCols++;
    }

    $NumPhases=$section['meta']['firstPhase'] ? ceil(log($section['meta']['firstPhase'], 2))+1 : 1;
    $maxNumPhases = max($maxNumPhases, $NumPhases);
    $maxElimRounds = max($maxElimRounds, $ElimCols);
    //пробежимся по всем спортсменам и регионам и найдем самые длинные строки
    foreach($section['items'] as $item) {
        $averageAthleteNameLength += strlen($item['athlete']);
        $averageRegionNameLength += strlen(getFullCountryName($item['countryName'], $item['countryName2'], $item['countryName3']));
    }
    $averageAthleteNameLength = $averageAthleteNameLength / count($section['items']);
    $averageRegionNameLength = $averageRegionNameLength / count($section['items']);
}

$spaceAvailable = 190 - 8 - 12 - 12 * $maxElimRounds - 15 * $maxNumPhases;
$athleteNameLength = floor($spaceAvailable * $averageAthleteNameLength / ($averageAthleteNameLength + $averageRegionNameLength));

$officialsSize = TournamentOfficials::getOfficialsBlockHeight();
$IRMLegendSize = $legendStatusProvider->getLegendBlockHeight();
$additionalSpaceForOfficialsAndIRMStatusLegend = 5 + 5;
if ($officialsSize == 0) {
    $additionalSpaceForOfficialsAndIRMStatusLegend = 5;
}
$headerSize = 7.5 + //division and class
    5; //table header
$dataRowSize = 4;

foreach($PdfData->rankData['sections'] as $section) {
    $needContinue = false;
    $currentSectionIndex++;

    $rowsNeedToFit = min(3, count($section['items']));
    if (!$pdf->SamePage($headerSize + $dataRowSize * $rowsNeedToFit)) {
        //не помещается хотя бы три строки - начинаем группу с нового листа
        $pdf->AddPage();
        $NeedTitle=false;
    }

	$ElimCols=0;
	if($section['meta']['elimType']!=5) {
		if($section['meta']['elim1']) $ElimCols++;
		if($section['meta']['elim2']) $ElimCols++;
	}

	$NumPhases=$section['meta']['firstPhase'] ? ceil(log($section['meta']['firstPhase'], 2))+1 : 0;

	//Se Esistono righe caricate....
	if(count($section['items'])) {
		$FirstPage=false;

		$NeedTitle=true;
        $dataIndex = 0;
		foreach($section['items'] as $item) {
            $dataIndex++;
            //что-то делаем только для последней секции
            if ($currentSectionIndex == count($PdfData->rankData['sections'])) {
                //если осталось три строки, или же в группе меньше трех строк
                if ($dataIndex + 2 >= count($section['items'])) {
                    if (!$pdf->SamePage($dataRowSize * (count($section['items']) - $dataIndex + 1) + $officialsSize + $IRMLegendSize + $additionalSpaceForOfficialsAndIRMStatusLegend)) {
                        $pdf->AddPage(); //надо переносить не всю группу, а гарантировать что последние три строки будут на новом листе, если не лезет целиком
                        $NeedTitle=true;
                        $needContinue = true;
                    }
                }
            }

			if(!$pdf->SamePage(5)) {
                $NeedTitle=true;
                $needContinue = true;
            }

			//Valuto Se è necessario il titolo
			if($NeedTitle) {
				// testastampa
				// Titolo della tabella
			   	$pdf->SetFont($pdf->FontStd,'B',10);
				$pdf->Cell(190, 7.5, $section['meta']['descr'], 1, 1, 'C', 1);

                if($needContinue)
                {
                    $pdf->SetXY(170,$pdf->GetY()-7.5);
                    $pdf->SetFont($pdf->FontStd,'',6);
                    $pdf->Cell(0, 7.5, $pdf->Continue, 0, 1, 'R', 0);
                }

                // Header vero e proprio
			   	$pdf->SetFont($pdf->FontStd,'B',7);
				$pdf->Cell(8, 5, $section['meta']['fields']['rank'], 1, 0, 'C', 1);
				$pdf->Cell($athleteNameLength, 5, $section['meta']['fields']['athlete'], 1, 0, 'C', 1);
				$pdf->Cell(190-8-12-$athleteNameLength-12*$ElimCols-15*$NumPhases, 5, $section['meta']['fields']['countryName'], 1, 0, 'C', 1);
				$pdf->Cell(12, 5, $section['meta']['fields']['qualRank'], 1, 0, 'C', 1);
				for($i=1; $i<=$ElimCols; $i++)
					$pdf->Cell(12, 5, $section['meta']['fields']['elims']['e' . $i], 1, 0, 'C', 1);
				foreach($section['meta']['fields']['finals'] as $k=>$v)
				{
                    //в случае отсутствия финалов у группы в заголовках появляется лишнее поле. Пройдем по самим данным и проверим, есть ли для этого поля данные
                    $reallyExists = false;
                    foreach($section['items'] as $athlete) {
                        if (array_key_exists($k, $athlete['finals'])) {
                            $reallyExists = true;
                            break;
                        }
                    }
                    if ($reallyExists) {
                        if (is_numeric($k) && $k != 1)
                            $pdf->Cell(15, 5, $v, 1, 0, 'C', 1);
                    }
				}
				$pdf->Cell(0, 5,'',0,1,'C',0);
				$NeedTitle=false;
                $needContinue = false;
			}

            $isIRMStatus = !is_numeric($item['rank']);
		   	$pdf->SetFont($pdf->FontStd,'B',8);
			$pdf->Cell(8, 4, ($item['rank'] ? $item['rank'] : ''), 1, 0, 'C', 0);
		   	$pdf->SetFont($pdf->FontStd,'',8);
            $previousPadding = $pdf->getCellPaddings();
            $pdf->setCellPaddings($previousPadding["L"] + 0.3, $previousPadding["T"], $previousPadding["R"] + 0.3, $previousPadding["B"]);
			$pdf->Cell($athleteNameLength, 4, $item['athlete'], 'RBT', 0, 'L', 0);
			$pdf->Cell(190-8-12-$athleteNameLength-12*$ElimCols-15*$NumPhases, 4, getFullCountryName($item['countryName'], $item['countryName2'], $item['countryName3']), 'RTB', 0, 'L', 0);
            $pdf->setCellPaddings($previousPadding["L"], $previousPadding["T"], $previousPadding["R"], $previousPadding["B"]);
            $spaceUsed = 190-12*$ElimCols-15*$NumPhases;
			$pdf->SetFont($pdf->FontFix,'',7);
			$pdf->Cell(12, 4,  is_numeric($item['qualScore']) ? (number_format($item['qualScore'],0,$PdfData->NumberDecimalSeparator,$PdfData->NumberThousandsSeparator) . '-' . substr('00' . $item['qualRank'],-2,2)) : '', 1, 0, 'C', 0);
            if ($isIRMStatus && $item['qualNotes'] != '') {
                $pdf->Cell(190 - $spaceUsed, 4, $item['qualNotes'], 1, 1, 'L', 0);
                continue;
            }
			if($section['meta']['elimType']!=5) {
				//Risultati delle eliminatorie
				if(array_key_exists('e1',$item['elims'])) {
                    $pdf->Cell(12, 4, number_format($item['elims']['e1']['score'], 0, $PdfData->NumberDecimalSeparator, $PdfData->NumberThousandsSeparator) . '-' . substr('00' . $item['elims']['e1']['rank'], -2, 2), 1, 0, 'R', 0);
                    $spaceUsed += 12;
                }
				if(array_key_exists('e2',$item['elims'])) {
                    $pdf->Cell(12, 4, number_format($item['elims']['e2']['score'], 0, $PdfData->NumberDecimalSeparator, $PdfData->NumberThousandsSeparator) . '-' . substr('00' . $item['elims']['e2']['rank'], -2, 2), 1, 0, 'R', 0);
                    $spaceUsed += 12;
                }
			}
//Risultati  delle varie fasi
			foreach($item['finals'] as $k=>$v)
			{
				if($v['tie']==2) {
                    $pdf->Cell(15, 4, $PdfData->Bye, 1, 0, 'L', 0);
                    $spaceUsed += 15;
                }
				else
				{
					if($k==4 && $section['meta']['matchMode']!=0 && $item['rank']>=5)
					{
                        $pdf->Cell(strlen($v['tiebreak'])>0 ? 8 : 15, 4, $v['setScore'] . '(' . $v['score'] . ')', (strlen($v['tiebreak'])>0 ? 'TB' : 'RTB'), 0, 'L', 0);
                        $spaceUsed += strlen($v['tiebreak'])>0 ? 8 : 15;
                        if(strlen($v['tiebreak'])>0) {
                            $pdf->Cell(7, 4, get_text('ShotOffShort', 'Tournament') . str_replace('|', ',', $v['tiebreak']) . ($v['tie'] == 1 && $v['tiebreak'] == $v['oppTiebreak'] ? '+' : ''), 'RTB', 0, 'L', 0);
                            $spaceUsed += 7;
                        }
					}
					else
					{
						$pdf->Cell(15 - (strlen($v['tiebreak'])>0 ? 7 : 0), 4, ($section['meta']['matchMode']==0 ? $v['score'] : $v['setScore']) . ($v['tie']==1 && strlen($v['tiebreak'])==0 ? '*' : ''), (strlen($v['tiebreak'])>0 ? 'LTB' : 1), 0, 'L', 0);
                        $spaceUsed += 15 - (strlen($v['tiebreak'])>0 ? 7 : 0);
						if(strlen($v['tiebreak'])>0) {
                            $pdf->Cell(7, 4, get_text('ShotOffShort', 'Tournament') . str_replace('|', ',', $v['tiebreak']) . ($v['tie'] == 1 && $v['tiebreak'] == $v['oppTiebreak'] ? '+' : ''), 'RTB', 0, 'L', 0);
                            $spaceUsed += 7;
                        }
					}
				}
			}
            if ($isIRMStatus) {
                $pdf->Cell(190 - $spaceUsed, 4, array_values($item['finals'])[count($item['finals']) - 1]['notes'], 1, 0, 'L', 0);
            }
			$pdf->Cell(0, 4,'',0,1,'C',0);
		}
        $pdf->SetY($pdf->GetY() + $spaceBetweenSections);
	}
}

//один раз отодвинем назад, потому что отступ логика подписей добавляет сама
$pdf->SetY($pdf->GetY() - $spaceBetweenSections);
TournamentOfficials::printOfficials($pdf);

$legendStatusProvider->printLegend();
