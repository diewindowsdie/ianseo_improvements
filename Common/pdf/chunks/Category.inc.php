<?php
$SinglePage=isset($_REQUEST['SinglePage']);
$TargetFace=(isset($_REQUEST['tf']) && ($_REQUEST['tf']==1 || $_REQUEST["tf"]=='on'));

$pdf->HideCols = $PdfData->HideCols;
$pdf->setDocUpdate($PdfData->Timestamp ?? $PdfData->LastUpdate ?? '');

error_reporting(E_ALL);
$StartLetter = ".";
$ShowStatusLegend = false;
$FirstTime=true;
if (isset($PdfData->Data['Items']) && count($PdfData->Data['Items'])>0)
{
	foreach($PdfData->Data['Items'] as $Group => $Rows) {
		if(!$FirstTime && ($SinglePage || !$pdf->SamePage(20))) {
			$pdf->AddPage();
		}
		$FirstTime=false;

	   	$pdf->SetFont($pdf->FontStd,'B',10);
		$pdf->Cell(190, 6,  $Rows[0]->EventName, 1, 1, 'C', 1);

		$pdf->SetFont($pdf->FontStd,'B',7);
		$pdf->Cell(8, 4, '№', 1, 0, 'C', 1);
		$pdf->Cell($TargetFace ? 42 : 49, 4, $PdfData->Data['Fields']['Athlete'], 1, 0, 'L', 1);
		$pdf->Cell($TargetFace ? 56 : 68, 4, $PdfData->Data['Fields']['Nation'], 1, 0, 'L', 1);
        $pdf->Cell(15, 4, $PdfData->Data['Fields']['DOB'], 1, 0, 'C', 1);
        $pdf->Cell(8, 4, $PdfData->Data['Fields']['SubClass'], 1, 0, 'C', 1);
		$pdf->Cell(7, 4,  $PdfData->Data['Fields']['Session'], 1, 0, 'C', 1);
		$pdf->Cell(11, 4, $PdfData->Data['Fields']['TargetNo'], 1, 0, 'C', 1);

		if ($TargetFace)
		{
			$pdf->Cell(19, 4, $PdfData->Data['Fields']['TargetFace'], 1, 0, 'C', 1);
		}

		//Disegna i Pallini
		if(!$PdfData->HideCols)
		{
			$pdf->DrawParticipantHeader();
		   	$pdf->SetFont($pdf->FontStd,'B',7);
			$pdf->Cell(10, 4, $PdfData->Data['Fields']['Status'], 1, 0, 'C', 1);
		}
		$pdf->Cell(1,  4,  '', 0, 1, 'C', 0);
		$pdf->SetFont($pdf->FontStd,'',1);
		$pdf->Cell(190, 0.5,  '', 1, 1, 'C', 0);

        $athleteIndex = 0;
		foreach($Rows as $MyRow) {
            $athleteIndex = $MyRow->IsAthlete ? ($athleteIndex + 1) : 0;

            //для минимизации объема изменений тут просто захардкодим 1
			$secondaryTeam = 1;

			if (!$pdf->SamePage(4*$secondaryTeam)) {
				$pdf->AddPage();

				$pdf->SetFont($pdf->FontStd,'B',10);
				$pdf->Cell(0, 6,  $Rows[0]->EventName, 1, 1, 'C', 1);
				$pdf->SetXY(170,$pdf->GetY()-6);
			   	$pdf->SetFont($pdf->FontStd,'I',6);
				$pdf->Cell(30, 6, $PdfData->Continue, 0, 1, 'R', 0);

				$pdf->SetFont($pdf->FontStd,'B',7);
				$pdf->Cell(8, 4, '№', 1, 0, 'C', 1);
                $pdf->Cell($TargetFace ? 42 : 49, 4, $PdfData->Data['Fields']['Athlete'], 1, 0, 'L', 1);
                $pdf->Cell($TargetFace ? 56 : 68, 4, $PdfData->Data['Fields']['Nation'], 1, 0, 'L', 1);
                $pdf->Cell(15, 4, $PdfData->Data['Fields']['DOB'], 1, 0, 'C', 1);
                $pdf->Cell(8, 4, $PdfData->Data['Fields']['SubClass'], 1, 0, 'C', 1);
				$pdf->Cell(7, 4,  $PdfData->Data['Fields']['Session'], 1, 0, 'C', 1);
				$pdf->Cell(11, 4, $PdfData->Data['Fields']['TargetNo'], 1, 0, 'C', 1);

				if ($TargetFace)
				{
					$pdf->Cell(19, 4, $PdfData->Data['Fields']['TargetFace'], 1, 0, 'C', 1);
				}

				//Disegna i Pallini
				if(!$PdfData->HideCols)
				{
					$pdf->DrawParticipantHeader();
				   	$pdf->SetFont($pdf->FontStd,'B',7);
					$pdf->Cell(10, 4, $PdfData->Data['Fields']['Status'], 1, 0, 'C', 1);
				}
				$pdf->Cell(1,  4,  '', 0, 1, 'C', 0);
				$pdf->SetFont($pdf->FontStd,'',1);
				$pdf->Cell(190, 0.5,  '', 1, 1, 'C', 0);
			}

		   	$pdf->SetFont($pdf->FontStd,'',7);
			$pdf->Cell(8, 4 * $secondaryTeam, $athleteIndex, 1, 0, 'R', 0);
		   	$pdf->SetFont($pdf->FontStd,'B',7);
			$pdf->Cell($TargetFace ? 42 : 49, 4 * $secondaryTeam,  $MyRow->Athlete, 1, 0, 'L', 0);
		   	$pdf->SetFont($pdf->FontStd,'',7);
			$pdf->Cell($TargetFace ? 56 : 68, 4,  getFullCountryName($MyRow->Nation, $MyRow->Nation2, $MyRow->Nation3), 'RTB', 0, 'L', 0);
            $pdf->SetFont($pdf->FontStd,'',7);
            $pdf->Cell(15, 4 * $secondaryTeam,  $MyRow->DOB, 1, 0, 'C', 0);
            $pdf->Cell(8, 4 * $secondaryTeam,  ($MyRow->SubClassDescription), 1, 0, 'C', 0);
			$pdf->Cell(7, 4 * $secondaryTeam,  $MyRow->IsAthlete ? $MyRow->Session : '', 1, 0, 'R', 0);
			$TgtNo=ltrim(($PdfData->BisTarget && (intval(substr($MyRow->TargetNo,1)) > $PdfData->NumEnd) ? str_pad((substr($MyRow->TargetNo,0,-1)-$PdfData->NumEnd),3,"0",STR_PAD_LEFT) . substr($MyRow->TargetNo,-1,1) . ' bis'  : $MyRow->TargetNo), 'O');
			$pdf->Cell(11, 4 * $secondaryTeam,  $MyRow->IsAthlete ? $TgtNo : '', 1, 0, 'R', 0);

			if ($TargetFace)
			{
				$pdf->Cell(19,4* $secondaryTeam,$MyRow->TfName,1,0,'C',0);
			}
			//Disegna i Pallini per la partecipazione
			if(!$PdfData->HideCols)
			{
				if(!$MyRow->IsAthlete) {
					$pdf->DrawParticipantDetails(-1);
				} else {
                    $pdf->DrawParticipantDetails($MyRow->IC, $MyRow->IF, $MyRow->TC, $MyRow->TF, $MyRow->TM);
                }

//                elseif($secondaryTeam==1) {
//				} elseif($secondaryTeam>=2) {
//					$pdf->DrawParticipantDetails($MyRow->IC, $MyRow->IF);
//					$secTmpX=$pdf->GetX();
//					$secTmpY=$pdf->GetY();
//					$pdf->SetXY($secTmpX-14,$secTmpY+4);
//					$pdf->DrawParticipantDetails(0, 0, $MyRow->TC, $MyRow->TF, $MyRow->TM);
//					$pdf->SetXY($secTmpX,$secTmpY);
//				} else {

//				}

				$pdf->SetDefaultColor();
				$pdf->SetFont($pdf->FontStd, '', 7);
				$ShowStatusLegend = ($ShowStatusLegend || ($MyRow->Status!=0));
				$pdf->Cell(10, 4 * $secondaryTeam,  ($MyRow->Status==0 ? '' : ($MyRow->Status)) , 1, 0, 'C', 0);
			}
			$pdf->Cell(1,  4 * $secondaryTeam,  '', 0, 1, 'C', 0);

			if(!isset($PdfData->HTML)) continue;

			$PdfData->HTML['Events'][$MyRow->EventCode]['Description']=$MyRow->EvCode ? $MyRow->EventName : $MyRow->DivDescription . ' ' . $MyRow->ClDescription;
			// may go for several events...
			if(empty($PdfData->HTML['Events'][$MyRow->EventCode]['Countries'][$MyRow->NationCode])) {
				$PdfData->HTML['Events'][$MyRow->EventCode]['Countries'][$MyRow->NationCode]=array();
			}
			$PdfData->HTML['Events'][$MyRow->EventCode]['Countries'][$MyRow->NationCode][]=array(
					$MyRow->NationCode,
					$MyRow->Nation,
					$TgtNo,
					$MyRow->Ranking,
					$MyRow->DOB,
					$MyRow->Athlete,
					$MyRow->SesName ? $MyRow->SesName : $PdfData->Data['Fields']['Session'].' '. $MyRow->Session,
			);
		}

		$pdf->SetY($pdf->GetY()+5);
	}
}

// Legenda per la partecipazione alle varie fasi
if(!$PdfData->HideCols) {
	$pdf->DrawPartecipantLegend();
	// Legenda per lo stato di ammisisone alle gare
	if($ShowStatusLegend) $pdf->DrawStatusLegend();
}

?>