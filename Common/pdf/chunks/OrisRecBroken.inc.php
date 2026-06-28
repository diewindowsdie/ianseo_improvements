<?php
$pdf->setPhase('As of '.$PdfData->RecordAs);
$pdf->setEvent($PdfData->Description);
$pdf->setDocUpdate($PdfData->LastUpdate ?? $PdfData->Timestamp ?? '');

$Version='';
if($PdfData->DocVersion) {
	$Version=trim('Vers. '.$PdfData->DocVersion . " ($PdfData->DocVersionDate) $PdfData->DocVersionNotes");
}
$pdf->setComment($Version);
$pdf->setOrisCode($PdfData->Code, $PdfData->Description);

$pdf->SetDataHeader($PdfData->Header, $PdfData->HeaderWidth);
$pdf->PrintHeader($pdf->GetX(), $pdf->GetY()+1);

$pdf->AddPage();
$pdf->Bookmark($PdfData->IndexName, 0);

$ONLINE=isset($PdfData->HTML);

if(empty($PdfData->Data['Items'])) {
	$pdf->printSectionTitle('No data§', $pdf->GetY()+10);
} else {
    $firstRun=true;
    foreach($PdfData->Data['Items'] as $Team => $Rows) {
        $oldRecName = '';
        foreach($Rows as $RecType => $MyRows) {
            $PrintSection = true;
            foreach ($MyRows as $MyRow) {
                $MinRows = count($MyRow->RtRecExtra) * count($MyRows);
                if ($MyRow->RtRecExtra and count($MyRow->RtRecExtra[0]->Archers) > 1) {
                    $MinRows = (count($MyRow->RtRecExtra[0]->Archers) + 1) * count($MyRows);
                }
                if ($PrintSection and !$pdf->SamePage($MinRows, 3.5, $pdf->lastY, false)) {
                    $pdf->SamePage($MinRows, 3.5, $pdf->lastY);
                    $oldRecName = '';
                    $firstRun = true;
                }
                if ($PrintSection and !$firstRun) {
                    $PrintSection = false;
                    $pdf->lastY += 3.5;
                }
                $firstRun = false;
                $PrintSection = false;

                $first = true;
                foreach ($MyRow->RtRecExtra as $Record) {
                    if ($MyRow->RtRecTotal) {
                        $OldRecord = $MyRow->RtRecTotal . (($MyRow->CheckXNine and $MyRow->RtRecXNine) ? "/$MyRow->RtRecXNine" : '');
                    }
                    if ($MyRow->TeamEvent) {
                        $tmp = array(
                            ($oldRecName == $MyRow->RtRecCategoryName . " - " . $MyRow->RtRecDistance ? '' : $MyRow->RtRecCategoryName . " - " . $MyRow->RtRecDistance),
                            $MyRow->TrHeaderCode . '#',
                            '§' . $OldRecord . ' / ' . $MyRow->NewRecord . (($MyRow->CheckXNine) ? "/$MyRow->NewXNine" : ''),
                            $MyRow->Athlete,
                            '§' .$MyRow->CoCode,
                            substr($MyRow->RecordDate,0,10) . '#'
                        );
                        $pdf->printDataRow($tmp);
                        foreach ($MyRow->Archers as $Archer) {
                            $tmp = array(
                                '', '', '', '   '.$Archer
                            );
                            $pdf->printDataRow($tmp);
                        }
                        $pdf->lastY += 1;
                    } else {
                        $tmp = array(
                            ($oldRecName == $MyRow->RtRecCategoryName . " - " . $MyRow->RtRecDistance ? '' : $MyRow->RtRecCategoryName . " - " . $MyRow->RtRecDistance),
                            $MyRow->TrHeaderCode . '#',
                            '§' . $OldRecord . ' / ' . $MyRow->NewRecord . (($MyRow->CheckXNine) ? "/$MyRow->NewXNine" : ''),
                            $MyRow->Athlete,
                            '§' . $MyRow->CoCode,
                            substr($MyRow->RecordDate,0,10) . '#'
                        );
                        if (!$first) {
                            $tmp[1] = '';
                        }
                        $first = false;
                        $pdf->printDataRow($tmp);
                    }
                    $oldRecName = $MyRow->RtRecCategoryName . " - " . $MyRow->RtRecDistance;
                }
            }
        }
    }
}

