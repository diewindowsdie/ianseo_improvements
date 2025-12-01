<?php

$isContinue = false;
$realPreviousTeam = null;
$pdf->setDocUpdate($PdfData->Timestamp ?? $PdfData->LastUpdate ?? '');
	foreach($PdfData->Data['Items'] as $Country => $Rows) {
		if($SinglePage and !$FirstTime) {
			$pdf->AddPage();
			$FirstTime=true;
		}

		foreach($Rows as $MyRow) {
	//		if($MyRow->Session!=$OldSession) {
	//			$pdf->sety($pdf->gety()+1);
	//		}
	//		$OldSession=$MyRow->Session;

			if(isset($_REQUEST["NewPage"]) and $OldTeam != $MyRow->NationCode and $OldTeam) {
				$pdf->AddPage();
				$FirstTime=true;
			}
			if ($FirstTime OR !$pdf->SamePage(4)) {
                $isContinue = $realPreviousTeam == $MyRow->NationCode;
				$pdf->SetDefaultColor();
			   	$pdf->SetFont($pdf->FontStd,'B',7);
				$pdf->Cell($nationCell, 4, '', 'LTB', 0, 'L', 1);
				$pdf->Cell($athleteCell + ($PdfData->InternationalProtocol ? $TgtCell : 0), 4, $PdfData->Data['Fields']['Athlete'], 'RTB', 0, 'L', 1);
                $pdf->Cell($regionCell, 4, $PdfData->Data['Fields']['Nation'], 1, 0, 'L', 1);
                $pdf->Cell($birthdayCell, 4, $PdfData->Data['Fields']['DOB'], 1, 0, 'L', 1);
				if(!$PdfData->HideCols and !$TargetFace and !$PdfData->InternationalProtocol) {
					$pdf->Cell($TgtCell, 4, $PdfData->Data['Fields']['SubClass'], 1, 0, 'C', 1);
				}
				$pdf->Cell($divAndClassCell + ($PdfData->HideCols==true ? $divAndClassCell:0), 4, $PdfData->Data['Fields']['DivDescription'], 1, 0, 'C', 1);
				$pdf->Cell($divAndClassCell + ($PdfData->HideCols==true ? $divAndClassCell:0), 4, $PdfData->Data['Fields']['ClDescription'], 1, 0, 'C', 1);
                $pdf->Cell($SesCell, 4, $PdfData->Data['Fields']['Session'], 1, 0, 'C', 1);
                $pdf->Cell($TgtCell, 4, $PdfData->Data['Fields']['TargetNo'], 1, 0, 'C', 1);

				if ($TargetFace) {
					$pdf->Cell($TgtCell*2, 4, $PdfData->Data['Fields']['TargetFace'], 1, 0, 'C', 1);
				}

				//Disegna i Pallini
				if(!$PdfData->HideCols) {
					$pdf->DrawParticipantHeader();
				   	$pdf->SetFont($pdf->FontStd,'B',7);
					$pdf->Cell($TgtCell, 4, $PdfData->Data['Fields']['Status'], 1, 0, 'C', 1);
				}

				$pdf->ln();
				$OldTeam='';
				$FirstTime=false;
			}
			if($OldTeam != $MyRow->NationCode) {
                if ($realPreviousTeam != $MyRow->NationCode) {
                    $isContinue = false;
                }
                //переносим на новую страницу регион, если вместе с заголовком не лезет две строки (и в регионе больше двух строк)
                //высота заголовка 6, высота одной строки со спортсменом 4
                if (!$pdf->SamePage(6 + 4 * min(2, count($Rows)))) {
                    $pdf->AddPage();
                    $OldTeam='';
                }
			   	$pdf->SetFont($pdf->FontStd,'B',1);
				$pdf->Cell(0, 1,  '', 0, 1, 'C', 0);
				$pdf->SetFont($pdf->FontStd,'B',8);
				//$pdf->Cell($TgtCell*1.5, 6, "", 'LTB', 0, 'L', 0);
				$pdf->Cell(0, 6,  $MyRow->NationComplete ? $MyRow->NationComplete : $MyRow->Nation, 1, 1, 'L', 0);
                if ($isContinue) {
                    $pdf->SetXY(170,$pdf->GetY()-6);
                    $pdf->SetFont($pdf->FontStd,'',6);
                    $pdf->Cell(0, 6, $pdf->Continue, 0, 1, 'R', 0);
                }
                //$pdf->Cell($NatAtlCell, 4,  $MyRow->Nation, '1', 0, 'L', 0);
				$OldTeam = $MyRow->NationCode;
                $realPreviousTeam = $MyRow->NationCode;
			}
            $pdf->Cell($nationCell, 4, '', 0, 0, 'C', 0);
		   	$pdf->SetFont($pdf->FontStd,'',7);
//			$pdf->Cell($athleteCell + ($PdfData->InternationalProtocol ? $TgtCell : 0), 4,  $MyRow->Athlete . ($MyRow->EnSubTeam==0 ? "" : " (" . $MyRow->EnSubTeam . ")"), 1, 0, 'L', 0);
            $pdf->Cell($athleteCell + ($PdfData->InternationalProtocol ? $TgtCell : 0), 4, getFullAthleteName($MyRow->FirstName, $MyRow->Name, $MyRow->MiddleName) . ($MyRow->EnSubTeam==0 ? "" : " (" . $MyRow->EnSubTeam . ")"), 1, 0, 'L', 0);
            $pdf->Cell($regionCell, 4, getFullCountryName(null, $MyRow->NationComplete2, $MyRow->NationComplete3), 1, 0, 'L', 0);
            $pdf->Cell($birthdayCell, 4,  $MyRow->EnDob, 1, 0, 'L', 0);
			if(!$PdfData->HideCols AND !$TargetFace and !$PdfData->InternationalProtocol) {
				$pdf->Cell($TgtCell, 4,  ($MyRow->SubClassDescription), 1, 0, 'C', 0);
			}
			$pdf->Cell($divAndClassCell + ($PdfData->HideCols==true ? $divAndClassCell:0), 4,  ($PdfData->HideCols==true ? $MyRow->DivDescription : $MyRow->DivDescription), 1, 0, 'C', 0);
			$pdf->Cell($divAndClassCell + ($PdfData->HideCols==true ? $divAndClassCell:0), 4,  ($PdfData->HideCols==true ? $MyRow->ClDescription : $MyRow->ClDescription), 1, 0, 'C', 0);
            $pdf->Cell($SesCell, 4,  ($MyRow->Session && $MyRow->IsAthlete ? $MyRow->Session : ''), 1, 0, 'R', 0);
            $pdf->Cell($TgtCell, 4,  ($MyRow->IsAthlete && $MyRow->TargetNo ? (!empty($PdfData->BisTarget) && (intval(substr($MyRow->TargetNo,1)) > $PdfData->NumEnd) ? str_pad((substr($MyRow->TargetNo,0,-1)-$PdfData->NumEnd),3,"0",STR_PAD_LEFT) . substr($MyRow->TargetNo,-1,1) . ' bis'  : $MyRow->TargetNo) : ''), 1, 0, 'R', 0);

			if ($TargetFace) {
				$pdf->Cell($TgtCell*2, 4, $MyRow->TfName, 1, 0, 'C', 0);
			}

		//Disegna i Pallini per la partecipazione
			if(!$PdfData->HideCols)
			{
				if(!$MyRow->IsAthlete) {
                    $pdf->DrawParticipantDetails(-1);
                } else {
                    $pdf->DrawParticipantDetails($MyRow->IC, $MyRow->IF, $MyRow->TC, $MyRow->TF, $MyRow->TM);
                }

				$pdf->SetDefaultColor();
				$pdf->SetFont($pdf->FontStd,'',7);
				$ShowStatusLegend = ($ShowStatusLegend || ($MyRow->Status!=0));
				$pdf->Cell($TgtCell, 4,  ($MyRow->Status==0 ? '' : ($MyRow->Status)) , 1, 0, 'C', 0);

			}

			$pdf->ln();

			if(!isset($PdfData->HTML)) continue;

			$PdfData->HTML['Countries'][$MyRow->NationCode]['Description']=$MyRow->Nation;
			$PdfData->HTML['Countries'][$MyRow->NationCode]['Archers'][]=array(
				$MyRow->Athlete,
				(!empty($PdfData->BisTarget) && (intval(substr($MyRow->TargetNo,1)) > $PdfData->NumEnd) ? 'bis ' . (substr($MyRow->TargetNo,0,-1)-$PdfData->NumEnd) . substr($MyRow->TargetNo,-1,1)  : $MyRow->TargetNo),
				$MyRow->DivDescription . ' ' . $MyRow->ClDescription,
				$MyRow->SesName ? $MyRow->SesName : $PdfData->Data['Fields']['Session'].' '. $MyRow->Session,
				);
		}
	}
