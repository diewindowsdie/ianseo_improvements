<?php
require_once('Common/Lib/TournamentOfficials.php');
require_once('Common/Lib/RowSpaceCalculator.php');

error_reporting(E_ALL);

//высота одной строки
$rowHeight = 5;
//высота строки с классами
$classRowHeight = 4;
//размер шрифта по умолчанию
$fontSize = 10;
//минимальное количество строк на следующей странице, если отчет не помещается на текущее количество страниц
$numberOfCountriesOnNextPage = 3;
//отступ перед блоком с подписями судей
$marginBeforeSignatures = 8;
//высота ячейки под разделитель
$separatorRowHeight = 0.5;
//количество стран
$numberOfCountries = count($PdfData->Data['Items']);
//высота строк с заголовком таблицы и строкой с дивизионами
$divisionAndTableHeaderRowHeight = 6;
//общая высота заголовка таблицы
$totalHeaderHeight = 2 * $divisionAndTableHeaderRowHeight + $classRowHeight;

$additionalSpaceUsed = $marginBeforeSignatures +
    TournamentOfficials::getOfficialsBlockHeight() +
    $separatorRowHeight +
    $totalHeaderHeight;

//проверим, помещается ли все на одну страницу
//+1 к количеству стран из-за заголовка таблицы
if ($numberOfCountries + 1 > getNumberOfRowsStillFittingPage($pdf, $rowHeight, $additionalSpaceUsed)) {
    //не помещается, подгоняем высоту так, чтобы было не меньше $numberOfCountriesOnNextPage на следующей, и увеличиваем шрифт
    while ($numberOfCountries + 1 - getNumberOfRowsStillFittingPage($pdf, $rowHeight, $totalHeaderHeight) < $numberOfCountriesOnNextPage) {
        $rowHeight += 0.1;
        $fontSize += 0.1;
    }
}

$FirstTime=true;
if (isset($PdfData->Data['Items']) && $numberOfCountries>0) {
    $pdf->setDocUpdate($PdfData->Timestamp ?? $PdfData->LastUpdate ?? '');

    $mainHeaderList = array();
    $cnt = 1;
    foreach($PdfData->Data['Fields'] as $field) {
        if(strstr($field,'|') !== false) {
            list($div,$cl) = explode('|',$field);
            if(!array_key_exists($div,$mainHeaderList)) {
                $mainHeaderList[$div]=array();
                $cnt++;
            }
            $mainHeaderList[$div][$cl]=0;
            $cnt++;
        }
    }
    $pages=array();
    $maxColNo = ($pdf->getPageWidth()< $pdf->getPageHeight() ? 25 : 35);
    if($cnt<=$maxColNo) {
        $pages[0] = array_keys($mainHeaderList);
    } else {
        $curPage = 0;
        $curCnt = 0;
        foreach ($mainHeaderList as $hKey=>$hValue) {
            if($curCnt+count($hValue)>$maxColNo) {
                $curPage++;
                $curCnt=0;
            }
            $pages[$curPage][] = $hKey;
            $curCnt += count($hValue);
        }
    }

    $CountryCount = 0;
    $CountryTotal = array();
    foreach($pages as $kPage=>$vPage) {
        if($kPage != 0) {
            $pdf->AddPage();
            $FirstTime= true;
        }
        $HeaderList = array();
        $cnt = 1;
        foreach ($vPage as $pageDiv) {
            $HeaderList[$pageDiv] = $mainHeaderList[$pageDiv];
            $cnt += count($mainHeaderList[$pageDiv]) + 1;
        }

        $ClSize = ($pdf->getPageWidth() - 65) / $cnt;
        foreach ($PdfData->Data['Items'] as $Country => $Rows) {
            if(!array_key_exists($Country,$CountryTotal)) {
                $CountryTotal[$Country] = 0;
                $CountryCount++;
            }
            if ($FirstTime || !$pdf->SamePage(5)) {
                $TmpSegue = (!$pdf->SamePage(5) OR $kPage!=0);
                $pdf->SetFont($pdf->FontStd, 'B', 10);
                $pdf->SetX(55);
                $pdf->Cell(($pdf->getPageWidth() - 65), $divisionAndTableHeaderRowHeight, $PdfData->StatCountries, 1, 1, 'C', 1);
                if ($TmpSegue) {
                    $pdf->SetXY(($pdf->getPageWidth() - 40), $pdf->GetY() - 6);
                    $pdf->SetFont($pdf->FontStd, 'I', 6);
                    $pdf->Cell(30, $divisionAndTableHeaderRowHeight, $PdfData->Continue, 0, 1, 'R', 0);
                }
                $pdf->SetX(55);
                $pdf->SetFont($pdf->FontStd, 'B', 10);
                //дивизионы
                foreach ($HeaderList as $Key => $Value)
                    $pdf->Cell($ClSize * (count($Value) + 1), $divisionAndTableHeaderRowHeight, ($Key == ' ' ? '--' : $Key), 1, 0, 'C', 1);
                $pdf->Cell(0.5, $divisionAndTableHeaderRowHeight + $classRowHeight, '', 1, 0, 'C', 1);
                $pdf->Cell($ClSize - 0.5, $divisionAndTableHeaderRowHeight + $classRowHeight, $PdfData->TotalShort, 1, 0, 'C', 1);
                $pdf->Cell(0.1, $divisionAndTableHeaderRowHeight, '', 0, 1, 'C', 0);


                $pdf->SetX(55);
                $pdf->SetFont($pdf->FontStd, 'B', 8);
                foreach ($HeaderList as $Key => $Value) {
                    //классы
                    foreach ($Value as $Cl => $Total)
                        $pdf->Cell($ClSize, $classRowHeight, $Cl, 1, 0, 'C', 1);
                    $pdf->Cell($ClSize, $classRowHeight, $PdfData->TotalShort, 1, 0, 'C', 1);
                }
                $pdf->Cell(0.1, $classRowHeight, '', 0, 1, 'C', 0);
                $FirstTime = false;
            }

            //начиная отсюда
            $pdf->SetFont($pdf->FontStd, '', 7);
            $pdf->Cell(45, $rowHeight, $Rows->NationName, 1, 0, 'L', 1);
            foreach ($HeaderList as $Key => $Value) {
                $DivTotal = 0;
                foreach ($Value as $Cl => $Total) {
                    $pdf->Cell($ClSize, $rowHeight, $Rows->{$Key . "|" . $Cl} ? $Rows->{$Key . "|" . $Cl} : '', 1, 0, 'R', 0);
                    $DivTotal += $Rows->{$Key . '|' . $Cl};
                    $HeaderList[$Key][$Cl] += $Rows->{$Key . '|' . $Cl};
                }
                $pdf->SetFont($pdf->FontStd, 'B', 7);
                $pdf->Cell($ClSize, $rowHeight, $DivTotal, 1, 0, 'R', 1);
                $CountryTotal[$Country] += $DivTotal;
            }
            $pdf->Cell(0.5, $rowHeight, '', 1, 0, 'C', 0);
            $pdf->SetFont($pdf->FontStd, 'B', 8);
            $pdf->Cell($ClSize - 0.5, $rowHeight, $CountryTotal[$Country], 1, 1, 'R', 1);
        }
        $pdf->SetFont($pdf->FontStd, 'B', 1);
        //ячейка-отступ перед итогом
        $pdf->Cell(($pdf->getPageWidth() - 20), 0.5, '', 1, 1, 'C', 0);

        $pdf->SetFont($pdf->FontStd, '', 8);
        $pdf->Cell(45, $rowHeight, $PdfData->Total . ": " . $CountryCount, 1, 0, 'L', 1);
        $GrandTotal=0;
        foreach ($HeaderList as $Key => $Value) {
            $DivTotal = 0;
            foreach ($Value as $Cl => $Total) {
                $pdf->Cell($ClSize, $rowHeight, $Total ? $Total : '', 1, 0, 'R', 0);
                $DivTotal += $Total;
            }
            $pdf->SetFont($pdf->FontStd, 'B', 8);
            $pdf->Cell($ClSize, $rowHeight, $DivTotal, 1, 0, 'R', 1);
            $GrandTotal += $DivTotal;
        }
        $pdf->Cell(0.5, $rowHeight, '', 1, 0, 'C', 0);
        $pdf->SetFont($pdf->FontStd, 'B', 8);
        $pdf->Cell($ClSize - 0.5, $rowHeight, $GrandTotal, 1, 1, 'R', 1);
    }

    $pdf->SetY($pdf->GetY()+8);
    TournamentOfficials::printOfficials($pdf);
}

