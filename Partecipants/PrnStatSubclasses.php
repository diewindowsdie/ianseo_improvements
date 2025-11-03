<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
checkFullACL(AclParticipants, 'pEntries', AclReadOnly);
require_once('Common/pdf/ResultPDF.inc.php');
require_once('Common/Lib/Normative/NormativeCalculator.php');
require_once('Common/Lib/Normative/NormativeStatistics.php');

if (!isset($isCompleteResultBook)) {
    $pdf = new ResultPDF((get_text('StatSubClasses', 'Tournament')), false);
}

$data = array();
$subclassTotals = array();

//описания классов запросим отдельно
$subclassDescriptions = array();
$query = "select ScId, ScDescription from SubClass where ScTournament=" . $_SESSION['TourId'] . " order by ScViewOrder";
$resultSet = safe_r_SQL($query);
while ($row = safe_fetch($resultSet)) {
    $subclassDescriptions[$row->ScId] = $row->ScDescription;
}
safe_free_result($resultSet);

$Sql = "SELECT EnClass, EnSubClass, ScDescription, count(*) as numArchers 
    FROM Entries 
    INNER JOIN Qualifications on EnId=QuId
    LEFT JOIN Classes ON EnClass=ClId AND ClTournament=EnTournament
    LEFT JOIN SubClass ON EnSubClass=ScId AND ScTournament=EnTournament
    WHERE EnTournament=" . StrSafe_DB($_SESSION['TourId']) . " 
    GROUP BY ClViewOrder, ScViewOrder, EnClass, EnSubClass 
    ORDER BY ClViewOrder, ScViewOrder, EnClass, EnSubClass";
$q = safe_r_SQL($Sql);
while ($r = safe_fetch($q)) {
    if (!array_key_exists($r->EnClass, $data)) {
        $data[$r->EnClass] = array();
    }
    $data[$r->EnClass][$r->EnSubClass] = $r->numArchers;

    if (!array_key_exists($r->EnSubClass, $subclassTotals)) {
        $subclassTotals[$r->EnSubClass] = 0;
    }
    $subclassTotals[$r->EnSubClass] += $r->numArchers;
}
safe_free_result($q);

$WCode = 15;
$subclassesCount = count($subclassDescriptions);
$WCell = min(20, (($pdf->getPageWidth() - 20 - $WCode) / ($subclassesCount + 2)));
$pdf->SetFont($pdf->FontStd, 'B', 10);
$pdf->SetXY($pdf->GetX() + $WCode, $pdf->GetY() + 5);
//заголовок таблицы
$pdf->Cell($WCell * ($subclassesCount + 2), 6, (get_text('StatSubClasses', 'Tournament')), 1, 1, 'C', 1);
$pdf->SetX($pdf->GetX() + $WCode);
foreach ($subclassDescriptions as $subclassKey => $subclassDescription) {
    $YORG = $pdf->GetY();
    $XORG = $pdf->GetX();
    $pdf->SetFont($pdf->FontStd, 'B', 8);
    //столбцы для каждого разряда
    $pdf->Cell($WCell, 5, $subclassDescription, 1, 0, 'C', 1);
    //}
    $pdf->setXY($pdf->GetX(), $YORG);
}

//столбец итогов по классу
$pdf->SetFont($pdf->FontStd, 'B', 10);
$pdf->Cell(2 * $WCell, 5, get_text('TotalShort', 'Tournament'), 1, 1, 'R', 1);

foreach ($data as $classKey => $classData) {
    $classTotal = 0;
    $pdf->SetFont($pdf->FontStd, 'B', 10);
    //класс
    $pdf->Cell($WCode, 5, $classKey, 1, 0, 'C', 1);
    $pdf->SetFont($pdf->FontStd, '', 8);
    foreach ($subclassDescriptions as $subclassKey => $subclassDescription) {
        //нужно проверять наличие данных для класса по этому разряду, иначе пропустим пустые поля
        if (array_key_exists($subclassKey, $classData)) {
            $pdf->Cell($WCell, 5, $classData[$subclassKey], 1, 0, 'R', 0);
            $classTotal += $classData[$subclassKey];
        } else {
            $pdf->Cell($WCell, 5, '0', 1, 0, 'R', 0);
        }
    }
    $pdf->SetFont($pdf->FontStd, 'B', 8);
    $pdf->Cell(2 * $WCell, 5, $classTotal, 1, 1, 'R', 1);
}
$pdf->SetFont($pdf->FontStd, 'B', 1);
$pdf->Cell($WCode + $WCell * ($subclassesCount + 2), 0.5, '', 1, 1, 'C', 0);
$pdf->SetFont($pdf->FontStd, 'B', 10);
$pdf->Cell($WCode, 5, get_text('Total'), 1, 0, 'C', 1);
$pdf->SetFont($pdf->FontStd, 'B', 8);
$total = 0;
foreach ($subclassDescriptions as $subclassKey => $subclassDescription) {
    $pdf->Cell($WCell, 5, $subclassTotals[$subclassKey] ?? 0, 1, 0, 'R', 1);
    $total += $subclassTotals[$subclassKey] ?? 0;
}
$pdf->SetFont($pdf->FontStd, 'B', 10);
$pdf->Cell(2 * $WCell, 5, $total, 1, 1, 'R', 1);

$pdf->setY($pdf->GetY() + 10);

//сбрасываем статистику

//названия выполненных нормативов в базе не хранятся, поэтому берем все что янсео знает
$normativeDescriptions = NormativeStatistics::normativeDescriptions();
$data = NormativeStatistics::getNormativeStatistics();

$WCode = 15;
$normativesPresentCount = count($normativeDescriptions);
$WCell = min(20, (($pdf->getPageWidth() - 20 - $WCode) / ($normativesPresentCount + 2)));
$pdf->SetFont($pdf->FontStd, 'B', 10);
$pdf->SetXY($pdf->GetX() + $WCode, $pdf->GetY() + 5);
//заголовок таблицы
$pdf->Cell($WCell * ($normativesPresentCount + 2), 6, (get_text('StatNormatives', 'Tournament')), 1, 1, 'C', 1);
$pdf->SetX($pdf->GetX() + $WCode);
foreach ($normativeDescriptions as $normativeKey => $normativeName) {
    $YORG = $pdf->GetY();
    $XORG = $pdf->GetX();
    $pdf->SetFont($pdf->FontStd, 'B', 8);
    //столбцы для каждого разряда
    $pdf->Cell($WCell, 5, $normativeName, 1, 0, 'C', 1);
    $pdf->setXY($pdf->GetX(), $YORG);
}

//столбец итогов по классу
$pdf->SetFont($pdf->FontStd, 'B', 10);
$pdf->Cell(2 * $WCell, 5, get_text('TotalShort', 'Tournament'), 1, 1, 'R', 1);

foreach ($data as $classKey => $classData) {
    if ($classKey === "normativeTotals") {
        continue;
    }
    $classTotal = 0;
    $pdf->SetFont($pdf->FontStd, 'B', 10);
    //класс
    $pdf->Cell($WCode, 5, $classKey, 1, 0, 'C', 1);
    $pdf->SetFont($pdf->FontStd, '', 8);
    foreach ($normativeDescriptions as $normativeKey => $normativeName) {
        //нужно проверять наличие данных для класса по этому разряду, иначе пропустим пустые поля
        $pdf->Cell($WCell, 5, $classData[$normativeKey] ?? 0, 1, 0, 'R', 0);
        $classTotal += $classData[$normativeKey] ?? 0;
    }
    $pdf->SetFont($pdf->FontStd, 'B', 8);
    $pdf->Cell(2 * $WCell, 5, $classTotal, 1, 1, 'R', 1);
}
$pdf->SetFont($pdf->FontStd, 'B', 1);
$pdf->Cell($WCode + $WCell * ($normativesPresentCount + 2), 0.5, '', 1, 1, 'C', 0);
$pdf->SetFont($pdf->FontStd, 'B', 10);
$pdf->Cell($WCode, 5, get_text('Total'), 1, 0, 'C', 1);
$pdf->SetFont($pdf->FontStd, 'B', 8);
$total = 0;
foreach ($normativeDescriptions as $normativeKey => $normativeName) {
    $pdf->Cell($WCell, 5, $data["normativeTotals"][$normativeName] ?? 0, 1, 0, 'R', 1);
    $total += $data["normativeTotals"][$normativeName] ?? 0;
}
$pdf->SetFont($pdf->FontStd, 'B', 10);
$pdf->Cell(2 * $WCell, 5, $total, 1, 1, 'R', 1);

if (!isset($isCompleteResultBook)) {
    $pdf->Output();
}