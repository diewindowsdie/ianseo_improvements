<?php

class TournamentOfficials
{
    private static $judgeRoleCellHeight = 5;
    private static $judgeSignatureCellHeight = 12;
    private static $judgeNameCellHeight = 5;

    private static $judgeCellWidth = 60;
    private static $spacerCellWidth = 5;

    static function printOfficials($pdf)
    {
        $tournament = $_SESSION['TourId'];

        $sql = 'select TiName, TiGivenName as Credential, CoNameComplete as Region, ItDescription as JudgeRoleCode from TournamentInvolved
                left join Countries on CoId = TiCountry
                left join InvolvedType on ItId = TiType
                where TiTournament = ' . $tournament;
        $resultSet = safe_r_sql($sql);
        $judges = array();
        while ($row = safe_fetch($resultSet)) {
            $judges[$row->JudgeRoleCode] = $row;
        }

        $pdf->SetXY(10,$pdf->GetY());
        if(!$pdf->SamePage(self::$judgeRoleCellHeight + self::$judgeSignatureCellHeight + self::$judgeNameCellHeight)) {
            $pdf->AddPage();
        }

        $pdf->SetFont($pdf->FontStd,'B',$pdf->FontSizeLines);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeRoleCellHeight, get_text('ChairmanJudge', 'Tournament'), 1, 0, 'C', 1);
        $pdf->Cell(self::$spacerCellWidth, self::$judgeRoleCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeRoleCellHeight, get_text('OrgResponsible', 'Tournament'), 1, 0, 'C', 1);
        $pdf->Cell(self::$spacerCellWidth, self::$judgeRoleCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeRoleCellHeight, get_text('ChairmanJudgeDeputy', 'Tournament'), 1, 1, 'C', 1);

        $pdf->Cell(self::$judgeCellWidth, self::$judgeSignatureCellHeight, '', 'LR', 0, 'C', 0);
        $pdf->Cell(self::$spacerCellWidth, self::$judgeSignatureCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeSignatureCellHeight, '', 'LR', 0, 'C', 0);
        $pdf->Cell(self::$spacerCellWidth, self::$judgeSignatureCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeSignatureCellHeight, '', 'LR', 1, 'C', 0);

        $pdf->SetFont($pdf->FontStd,'',$pdf->FontSizeLines);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeNameCellHeight,
            $judges['ChairmanJudge']->TiName
            . ' ('
            . $judges['ChairmanJudge']->Region
            . '), '
            . $judges['ChairmanJudge']->Credential,
            1, 0, 'C', 1);
        $pdf->Cell(self::$spacerCellWidth, self::$judgeNameCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeNameCellHeight,
            $judges['OrgResponsible']->TiName
            . ' ('
            . $judges['OrgResponsible']->Region
            . '), '
            . $judges['OrgResponsible']->Credential,
            1, 0, 'C', 1);
        $pdf->Cell(self::$spacerCellWidth, self::$judgeNameCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeNameCellHeight,
            $judges['ChairmanJudgeDeputy']->TiName
            . ' ('
            . $judges['ChairmanJudgeDeputy']->Region
            . '), '
            . $judges['ChairmanJudgeDeputy']->Credential,
            1, 1, 'C', 1);
    }

    static function getOfficialsBlockHeight() {
        return self::$judgeRoleCellHeight + self::$judgeSignatureCellHeight + self::$judgeNameCellHeight;
    }
}

?>
