<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
checkFullACL(AclParticipants, 'pEntries', AclReadOnly);
require_once('Common/pdf/ResultPDF.inc.php');

if (!isset($isCompleteResultBook)) {
    $pdf = new ResultPDF(get_text("StatSumPositions", "Tournament"), true);
}

$pdf->SetFont($pdf->FontStd, 'B', 11);
$pdf->Cell(190, 10, "Список участников, отсортированный по сумме мест в квалификации и финалах", 0, 1, 'C');

$query = "select e.EnFirstName, e.EnName, e.EnMiddleName, date_format(e.EnDob, '" . get_text('DateFmtDB') . "') as BirthDate, concat(d.DivDescription, ' ', c.ClDescription) as GroupDesc, i.IndRankFinal + q.QuClRank as RankSum, q.QuClRank, q.QuScore, i.IndRankFinal,
        if(co1.CoNameComplete is null || co1.CoNameComplete = '', co1.CoName, co1.CoNameComplete) as CoName1,
        if(co2.CoNameComplete is null || co2.CoNameComplete = '', co2.CoName, co2.CoNameComplete) as CoName2,
        if(co3.CoNameComplete is null || co3.CoNameComplete = '', co3.CoName, co3.CoNameComplete) as CoName3
from Entries e
        left join Qualifications q on e.EnId = q.QuId
        left join Individuals i on e.EnId = i.IndId
        left join Divisions d on e.EnDivision = d.DivId and e.EnTournament = d.DivTournament
        left join Classes c on e.EnClass = c.ClId and e.EnTournament = c.ClTournament
        left join Countries co1 on e.EnCountry = co1.CoId and e.EnTournament = co1.CoTournament
        left join Countries co2 on e.EnCountry2 = co2.CoId and e.EnTournament = co2.CoTournament
        left join Countries co3 on e.EnCountry3 = co3.CoId and e.EnTournament = co3.CoTournament
where e.EnAthlete = 1
  and e.EnTournament = " . $_SESSION['TourId'] . "
order by d.DivViewOrder, c.ClViewOrder, (i.IndRankFinal + q.QuClRank), q.QuClRank asc";
$resultSet = safe_r_SQL($query);

$lastGroupName = null;
while ($row = safe_fetch($resultSet)) {
    if ($row->GroupDesc !== $lastGroupName) {
        $pdf->SetFont($pdf->FontStd, 'B', 10);
        if ($lastGroupName !== null) {
           $pdf->Cell(190, 10, '', 0, 1, 'C', 0);
        }
        //название группы
        $pdf->Cell(190, 8, $row->GroupDesc, 1, 1, 'C', 1);
        $lastGroupName = $row->GroupDesc;
        $finalRank = 0;

        //заголовок таблицы группы
        $pdf->SetFont($pdf->FontStd, 'B', 8);
        $pdf->Cell(10, 8, "Место", 1, 0, 'C', 1);
        $pdf->Cell(55, 8, "Спортсмен", 1, 0, 'L', 1);
        $pdf->Cell(59, 8, "Страна / Регион", 1, 0, 'L', 1);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Cell(14, 4, "Дата", 'LTR', 0, 'C', 1, '', 1, false, 'T', 'B');
        $pdf->setXY($x, $y + 4);
        $pdf->Cell(14, 4, "рождения", 'LBR', 0, 'C', 1, '', 1, false, 'T', 'T');
        $pdf->setXY($x + 14, $y);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Cell(12, 4, "Сумма", 'LTR', 0, 'C', 1, '', 1, false, 'T', 'B');
        $pdf->setXY($x, $y + 4);
        $pdf->Cell(12, 4, "мест", 'LBR', 0, 'C', 1, '', 1, false, 'T', 'T');
        $pdf->setXY($x + 12, $y);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Cell(14, 4, "Место в", 'LTR', 0, 'C', 1, '', 1, false, 'T', 'B');
        $pdf->setXY($x, $y + 4);
        $pdf->Cell(14, 4, "квалификации", 'LBR', 0, 'C', 1, '', 1, false, 'T', 'T');
        $pdf->setXY($x + 14, $y);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Cell(14, 4, "Результат", 'LTR', 0, 'C', 1, '', 1, false, 'T', 'B');
        $pdf->setXY($x, $y + 4);
        $pdf->Cell(14, 4, "квалификации", 'LBR', 0, 'C', 1, '', 1, false, 'T', 'T');
        $pdf->setXY($x + 14, $y);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Cell(12, 4, "Место в", 'LTR', 0, 'C', 1, '', 1, false, 'T', 'B');
        $pdf->setXY($x, $y + 4);
        $pdf->Cell(12, 4, "финалах", 'LBR', 1, 'C', 1, '', 1, false, 'T', 'T');
    }

    $finalRank++;
    //спортсмен
    $pdf->SetFont($pdf->FontStd, 'B', 8);
    $pdf->Cell(10, 5, $finalRank, 1, 0, 'C', 0);
    $pdf->SetFont($pdf->FontStd, '', 8);
    $pdf->Cell(55, 5, getFullAthleteName(mb_strtoupper($row->EnFirstName), $row->EnName, $row->EnMiddleName), 1, 0, 'L', 0);
    $pdf->Cell(59, 5, getFullCountryName($row->CoName1, $row->CoName2, $row->CoName3), 1, 0, 'L', 0);
    $pdf->Cell(14, 5, $row->BirthDate, 1, 0, 'C', 0);
    $pdf->Cell(12, 5, $row->RankSum, 1, 0, 'C', 0);
    $pdf->Cell(14, 5, $row->QuClRank, 1, 0, 'C', 0);
    $pdf->Cell(14, 5, $row->QuScore, 1, 0, 'C', 0);
    $pdf->Cell(12, 5, $row->IndRankFinal, 1, 1, 'C', 0);
}
safe_free_result($resultSet);

$pdf->Output();
?>