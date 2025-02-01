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

        $sql = 'select TiName as Surname, TiGivenName as FirstName, TiLastName as Patronymic, TiAccreditation as Credential, CoNameComplete as Region, ItDescription as JudgeRoleCode from TournamentInvolved
                left join Countries on CoId = TiCountry
                left join InvolvedType on ItId = TiType
                where TiIsSigningProtocols = 1 and TiTournament = ' . $tournament .
                ' ORDER BY TiIsSigningProtocols desc, ItId IS NOT NULL, ItJudge=0, ItJudge, ItDoS=0, ItDoS, ItJury=0, ItJury, ItOc, TiName, TiGivenName';
        $resultSet = safe_r_sql($sql);
        $judges = array();
        $i = 0;
        while ($row = safe_fetch($resultSet)) {
            $judges[$i] = $row;
            $i++;
        }

        //если нам не хватает судей, у которых стоит галочка "Подписывает протокол", не выводим ничего
        if (count($judges) < 3) {
            return;
        }

        $pdf->SetXY(10,$pdf->GetY());
        if(!$pdf->SamePage(self::$judgeRoleCellHeight + self::$judgeSignatureCellHeight + self::$judgeNameCellHeight)) {
            $pdf->AddPage();
        }

        //если судей с галочкой "подписывает протокол" хватает, выводим поля для подписей первых трех
        $pdf->SetFont($pdf->FontStd,'B',$pdf->FontSizeLines);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeRoleCellHeight, get_text($judges[0]->JudgeRoleCode, 'Tournament'), 1, 0, 'C', 1);
        $pdf->Cell(self::$spacerCellWidth, self::$judgeRoleCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeRoleCellHeight, get_text($judges[1]->JudgeRoleCode, 'Tournament'), 1, 0, 'C', 1);
        $pdf->Cell(self::$spacerCellWidth, self::$judgeRoleCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeRoleCellHeight, get_text($judges[2]->JudgeRoleCode, 'Tournament'), 1, 1, 'C', 1);

        $pdf->Cell(self::$judgeCellWidth, self::$judgeSignatureCellHeight, '', 'LR', 0, 'C', 0);
        $pdf->Cell(self::$spacerCellWidth, self::$judgeSignatureCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeSignatureCellHeight, '', 'LR', 0, 'C', 0);
        $pdf->Cell(self::$spacerCellWidth, self::$judgeSignatureCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeSignatureCellHeight, '', 'LR', 1, 'C', 0);

        $pdf->SetFont($pdf->FontStd,'',$pdf->FontSizeLines);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeNameCellHeight,
            $judges[0]->Surname .
            ' ' .
            mb_substr($judges[0]->FirstName, 0, 1) .
            '. ' .
            mb_substr($judges[0]->Patronymic, 0, 1) .
            '. ('
            . $judges[0]->Region
            . '), '
            . $judges[0]->Credential,
            1, 0, 'C', 1);
        $pdf->Cell(self::$spacerCellWidth, self::$judgeNameCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeNameCellHeight,
            $judges[1]->Surname .
            ' ' .
            mb_substr($judges[1]->FirstName, 0, 1) .
            '. ' .
            mb_substr($judges[1]->Patronymic, 0, 1) .
            '. ('
            . $judges[1]->Region
            . '), '
            . $judges[1]->Credential,
            1, 0, 'C', 1);
        $pdf->Cell(self::$spacerCellWidth, self::$judgeNameCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell(self::$judgeCellWidth, self::$judgeNameCellHeight,
            $judges[2]->Surname .
            ' ' .
            mb_substr($judges[2]->FirstName, 0, 1) .
            '. ' .
            mb_substr($judges[2]->Patronymic, 0, 1) .
            '. ('
            . $judges[2]->Region
            . '), '
            . $judges[2]->Credential,
            1, 1, 'C', 1);
    }

    static function getOfficialsBlockHeight() {
        return self::$judgeRoleCellHeight + self::$judgeSignatureCellHeight + self::$judgeNameCellHeight;
    }
}

?>
