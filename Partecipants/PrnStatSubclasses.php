<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
checkFullACL(AclParticipants, 'pEntries', AclReadOnly);
require_once('Common/pdf/ResultPDF.inc.php');
require_once('Common/Lib/Normative/NormativeCalculator.php');
require_once('Common/Lib/Normative/NormativeStatistics.php');

if (!isset($isCompleteResultBook)) {
    $pdf = new ResultPDF((get_text('StatSubClasses', 'Tournament')), false);
}

//описания классов запросим отдельно
$subclassDescriptions = array();
$query = "select ScId, ScDescription from SubClass where ScTournament=" . $_SESSION['TourId'] . " order by ScViewOrder desc";
$resultSet = safe_r_SQL($query);
while ($row = safe_fetch($resultSet)) {
    $subclassDescriptions[$row->ScId] = $row->ScDescription;
}
safe_free_result($resultSet);

$query = "select ClId, ClDescription from Classes where ClTournament = " . StrSafe_DB($_SESSION['TourId']);
$rs = safe_r_SQL($query);
$classes = array();
while ($row = safe_fetch($rs)) {
    $classes[$row->ClId] = $row->ClDescription;
}

//статистика по имеющимся разрядам
$query = "SELECT EnClass, EnSubClass, ScId, count(*) as numArchers 
    FROM Entries 
    LEFT JOIN Classes ON EnClass=ClId AND ClTournament=EnTournament
    LEFT JOIN SubClass ON EnSubClass=ScId AND ScTournament=EnTournament
    WHERE EnTournament=" . StrSafe_DB($_SESSION['TourId']) . " 
    GROUP BY ClViewOrder, ScViewOrder, EnClass, EnSubClass 
    ORDER BY ClViewOrder, ScViewOrder, EnClass, EnSubClass";
$rs = safe_r_SQL($query);
$subclassStatistics = array();
while ($row = safe_fetch($rs)) {
    $subclassStatistics[$row->ScId][$row->EnClass] = $row->numArchers;
}

$classesCount = count($classes);
$cellWidth = min(22, ($pdf->getPageWidth() - 20 - 15) / ($classesCount + 2));
$pdf->SetFont($pdf->FontStd, 'B', 10);
$pdf->SetXY($pdf->GetX() + $cellWidth, $pdf->GetY() + 5);
//заголовок таблицы
$pdf->Cell($cellWidth * ($classesCount + 2), 6, (get_text('StatSubClasses', 'Tournament')), 1, 1, 'C', 1);
$pdf->SetX($pdf->GetX() + $cellWidth);
foreach ($classes as $id => $description) {
    $pdf->Cell($cellWidth, 5, $description, 1, 0, "C");
}

//столбец итогов по подклассу(разряду)
$pdf->SetFont($pdf->FontStd, 'B', 10);
$pdf->Cell(2 * $cellWidth, 5, get_text('TotalShort', 'Tournament'), 1, 1, 'R', 1);

foreach ($subclassDescriptions as $subclassId => $subclassDescription) {
    $pdf->SetFont($pdf->FontStd,'B', 9);
    $pdf->Cell($cellWidth, 5, $subclassDescription, 1, 0, "C", 1);
    $subclassTotal = 0;
    $pdf->SetFont($pdf->FontStd,'', 9);
    foreach ($classes as $id => $description) {
        $subclassForGroup = $subclassStatistics[$subclassId][$id] ?? "0";
        $subclassTotal += $subclassForGroup;
        $pdf->Cell($cellWidth, 5, $subclassForGroup, 1, 0, "R");
    }
    $pdf->SetFont($pdf->FontStd,'B', 9);
    $pdf->Cell(2 * $cellWidth, 5, $subclassTotal, 1, 1, "R", 1);
}

//названия выполненных нормативов в базе не хранятся, поэтому берем все что янсео знает
$normativeDescriptions = NormativeStatistics::normativeDescriptions();
$data = NormativeStatistics::getNormativeStatistics();

$pdf->SetFont($pdf->FontStd, 'B', 10);
$pdf->SetXY($pdf->GetX() + $cellWidth, $pdf->GetY() + 5);
//заголовок таблицы
$pdf->Cell($cellWidth * ($classesCount + 2), 6, (get_text('StatNormatives', 'Tournament')), 1, 1, 'C', 1);
$pdf->SetX($pdf->GetX() + $cellWidth);
foreach ($classes as $id => $description) {
    $pdf->Cell($cellWidth, 5, $description, 1, 0, "C");
}

//столбец итогов по подклассу(разряду)
$pdf->SetFont($pdf->FontStd, 'B', 10);
$pdf->Cell(2 * $cellWidth, 5, get_text('TotalShort', 'Tournament'), 1, 1, 'R', 1);

foreach ($normativeDescriptions as $normativeKey => $normativeName) {
    $pdf->SetFont($pdf->FontStd,'B', 9);
    $pdf->Cell($cellWidth, 5, $normativeName, 1, 0, "C", 1);
    $subclassTotal = 0;
    $pdf->SetFont($pdf->FontStd,'', 9);
    foreach ($classes as $classKey => $classDescription) {
        $pdf->Cell($cellWidth, 5, $data[$classKey][$normativeKey] ?? 0, 1, 0, "R", 0);
        $subclassTotal += $data[$classKey][$normativeKey];
    }
    $pdf->SetFont($pdf->FontStd,'B', 9);
    $pdf->Cell(2 * $cellWidth, 5, $subclassTotal, 1, 1, "R", 1);
}

if (!isset($isCompleteResultBook)) {
    $pdf->Output();
}