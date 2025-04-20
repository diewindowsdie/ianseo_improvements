<?php
require_once('Common/Lib/TournamentOfficials.php');
require_once('Common/Lib/StatusesLegendProvider.php');

$pdf->setDocUpdate($PdfData->rankData['meta']['lastUpdate']);

$legendStatusProvider = new StatusLegendProvider($pdf);

// se ho degli eventi
$FirstPage=true;

$pdf->SetFont($pdf->FontStd,'B',12);
$pdf->Cell(190, 10, get_text('FinalStanding', 'Tournament'), 0, 1, 'C', 0);

$currentSectionIndex = 0;
$spaceBetweenSections = 5;

foreach($PdfData->rankData['sections'] as $section) {
    $currentSectionIndex++;
    if ($currentSectionIndex == count($PdfData->rankData['sections'])) {
        //last group:
        //check if header message, group header, group, officials information and legend fits on the same page
        $headerSize = 7.5 + //division and class
            5; //table header

        $rowHeight = 4 * (count($section["items"]) > 0 ? max(1, count(array_values($section["items"])[0]['athletes'])) : 1);
        $dataSize = $rowHeight * count($section['items']) + $spaceBetweenSections;
        $officialsSize = TournamentOfficials::getOfficialsBlockHeight();
        $IRMLegendSize = $legendStatusProvider->getLegendBlockHeight();

        if (!$pdf->SamePage($headerSize + $dataSize + $officialsSize + $IRMLegendSize)) {
            $pdf->AddPage();
            $NeedTitle=true;
        }
    }

    $NumPhases=$section['meta']['firstPhase'] ? ceil(log($section['meta']['firstPhase'], 2))+1 : 1;
	$NeedTitle=true;

	// Se Esistono righe caricate....
	if(count($section['items'])) {
		$FirstPage=false;

		foreach($section['items'] as $item) {
			$NumComponenti = max(1, count($item['athletes']));
			if(!$pdf->SamePage(4*$NumComponenti )) $NeedTitle=true;

			//Valuto Se è necessario il titolo
			if($NeedTitle) {
				// Titolo della tabella
			   	$pdf->SetFont($pdf->FontStd,'B',10);
				$pdf->Cell(190, 7.5,  $section['meta']['descr'], 1, 1, 'C', 1);
				// Header vero e proprio
			   	$pdf->SetFont($pdf->FontStd,'B',7);
				$pdf->Cell(10, 5, $section['meta']['fields']['rank'], 1, 0, 'C', 1);
				$pdf->Cell(55+(15*(7-$NumPhases)), 5, $section['meta']['fields']['countryName'], 1, 0, 'C', 1);
				$pdf->Cell(20, 5, $section['meta']['fields']['qualRank'], 1, 0, 'C', 1);
				foreach($section['meta']['fields']['finals'] as $k=>$v)
				{
					if(is_numeric($k) && $k!=1)
						$pdf->Cell(15, 5, $v, 1, 0, 'C', 1);
				}
				$pdf->Cell(0, 5,'',0,1,'C',0);
				$NeedTitle=false;
			}

			$pdf->SetFont($pdf->FontStd,'B',1);
			$pdf->Cell(190, 0.2,'',0,1,'C',0);
		   	$pdf->SetFont($pdf->FontStd,'B',8);
			$pdf->Cell(10, 4*$NumComponenti, ($item['rank'] ? $item['rank'] : ''), 1, 0, 'C', 0);
		   	$pdf->SetFont($pdf->FontStd,'',8);
            $paddings = $pdf->getCellPaddings();
            $pdf->setCellPaddings($paddings["L"]+1.5, $paddings["T"], $paddings["R"]+1.5, $paddings["B"]);
			$pdf->Cell(30+(15*(5-$NumPhases)), 4*$NumComponenti, $item['countryName'] . ($item['subteam']<=1 ? '' : ' (' . $item['subteam'] .')'), 'TB', 0, 'L', 0);
            $pdf->setCellPaddings($paddings["L"], $paddings["T"], $paddings["R"], $paddings["B"]);

			//Metto i nomi dei Componenti se li ho
			if(count($item['athletes'])) {
				$tmpX=$pdf->GetX();
				$tmpY=$pdf->GetY();
				$NameCount=0;
				foreach($item['athletes'] as $k =>$v)
				{
					$pdf->SetXY($tmpX, $tmpY+(4*$NameCount++));
					$pdf->Cell(55, 4, $v['athlete'], 1, 0, 'L', 0);
				}
				$pdf->SetXY($tmpX+55, $tmpY);
			} else {
				$pdf->Cell(55, 4*$NumComponenti, '', 'RTB', 0, 'L', 0);
			}

			$pdf->SetFont($pdf->FontFix,'',8);
			$pdf->Cell(20, 4*$NumComponenti,  number_format($item['qualScore'],0,$PdfData->NumberDecimalSeparator,$PdfData->NumberThousandsSeparator) . '-' . substr('00' . $item['qualRank'],-2,2), 1, 0, 'R', 0);
			//Risultati  delle varie fasi
			foreach($item['finals'] as $k=>$v)
			{
				if($v['tie']==2)
					$pdf->Cell(15, 4*$NumComponenti,  $PdfData->Bye, 1, 0, 'L', 0);
				else
				{
					$pdf->SetFont($pdf->FontFix,'',8);
					if($k==4 && $section['meta']['matchMode']!=0 && $item['rank']>=5)
					{
                        $tiebreakScore = false;
                        if(strlen($v['tiebreak'])>0) {
                            $tiebreakScore = true;
                            $previousX = $pdf->GetX();
                            $previousY = $pdf->GetY();
                            $pdf->SetXY($previousX, $previousY + 2 * $NumComponenti);

                            $pdf->Cell(15, 2 * $NumComponenti, "T." . str_replace('|', ',', $v['tiebreak']) . ($v['tie'] == 1 && $v['tiebreak'] == $v['oppTiebreak'] ? '+' : ''), 'RBL', 0, 'R', 0, '', 1, false, 'T', 'T');
                            $pdf->SetXY($previousX, $previousY);
                        }
                        $pdf->Cell(15, $tiebreakScore ? 2*$NumComponenti : 4*$NumComponenti, $v['setScore'] . '(' . $v['score'] . ')', ($tiebreakScore ? 'RTL' : 1), 0, 'L', 0, '', 1, false, 'T', $tiebreakScore ? 'B' : 'C');
                    }
					else
					{
						$pdf->SetFont($pdf->FontFix,'',7);
                        $tiebreakScore = false;
						if(strlen($v['tiebreak'])>0)
						{
                            $tiebreakScore = true;
                            $previousX = $pdf->GetX();
                            $previousY = $pdf->GetY();
                            $pdf->SetXY($previousX, $previousY + 2 * $NumComponenti);
							$pdf->Cell(15, 2*$NumComponenti,  "T." . str_replace('|', ',', $v['tiebreak']) . ($v['tie'] == 1 && $v['tiebreak'] == $v['oppTiebreak'] ? '+' : ''), 'RBL', 0, 'R', 0, '', 1, false, 'T', 'T');
                            $pdf->SetXY($previousX, $previousY);
						}
                        $pdf->Cell(15, $tiebreakScore ? 2*$NumComponenti : 4*$NumComponenti, ($section['meta']['matchMode']==0 ? $v['score'] : $v['setScore']) . ($k<=1 && $v['tie']==1 && strlen($v['tiebreak'])==0 ? '*' : ''), ($tiebreakScore ? 'RTL' : 1), 0, 'L', 0, '', 1, false, 'T', $tiebreakScore ? 'B' : 'C');
					}
				}
			}
			$pdf->Cell(0.1, 4*$NumComponenti,'',0,1,'C',0);
		}
        $pdf->SetY($pdf->GetY() + $spaceBetweenSections);
    }
}

TournamentOfficials::printOfficials($pdf);
$legendStatusProvider->printLegend();
?>