<?php

class TournamentOfficials
{
    private static $judgeRoleCellHeight = 5;
    private static $judgeSignatureCellHeight = 12;
    private static $judgeNameCellHeight = 5;
    private static $offsetBeforeOfficials = 5;

    private static $spacerCellWidthPortrait = 5;
    private static $spacerCellWidthLandscape = 15;
    private static $horizontalMargin = 10;

    static function printOfficials($pdf)
    {
        $tournament = $_SESSION['TourId'];

        if (getModuleParameter("Tournament", "InternationalProtocol", false, $tournament)) {
            return;
        }

        $spacerCellWidth = self::$spacerCellWidthPortrait;
        if ($pdf->getPageWidth() > $pdf->getPageHeight()) {
            $spacerCellWidth = self::$spacerCellWidthLandscape;
        }
        $cellWidth = ($pdf->getPageWidth() - 2 * $spacerCellWidth - 2 * self::$horizontalMargin) / 3;

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

        $pdf->SetY($pdf->GetY() + self::$offsetBeforeOfficials);
        $pdf->SetXY(self::$horizontalMargin, $pdf->GetY());
        if (!$pdf->SamePage(self::$judgeRoleCellHeight + self::$judgeSignatureCellHeight + self::$judgeNameCellHeight)) {
            $pdf->AddPage();
        }

        //если судей с галочкой "подписывает протокол" хватает, выводим поля для подписей первых трех
        $pdf->SetFont($pdf->FontStd, 'B', $pdf->FontSizeLines);
        $pdf->Cell($cellWidth, self::$judgeRoleCellHeight, get_text($judges[0]->JudgeRoleCode, 'Tournament'), 1, 0, 'C', 1);
        $pdf->Cell($spacerCellWidth, self::$judgeRoleCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell($cellWidth, self::$judgeRoleCellHeight, get_text($judges[1]->JudgeRoleCode, 'Tournament'), 1, 0, 'C', 1);
        $pdf->Cell($spacerCellWidth, self::$judgeRoleCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell($cellWidth, self::$judgeRoleCellHeight, get_text($judges[2]->JudgeRoleCode, 'Tournament'), 1, 1, 'C', 1);

        $pdf->Cell($cellWidth, self::$judgeSignatureCellHeight, '', 'LR', 0, 'C', 0);
        $pdf->Cell($spacerCellWidth, self::$judgeSignatureCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell($cellWidth, self::$judgeSignatureCellHeight, '', 'LR', 0, 'C', 0);
        $pdf->Cell($spacerCellWidth, self::$judgeSignatureCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell($cellWidth, self::$judgeSignatureCellHeight, '', 'LR', 1, 'C', 0);

        $pdf->SetFont($pdf->FontStd, '', $pdf->FontSizeLines);
        $pdf->Cell($cellWidth, self::$judgeNameCellHeight,
            $judges[0]->Surname .
            ' ' .
            mb_substr($judges[0]->FirstName, 0, 1) .
            '. ' .
            mb_substr($judges[0]->Patronymic, 0, 1) .
            '. ('
            . $judges[0]->Region
            . '), '
            . get_text("JudgeAccreditation_" . $judges[0]->Credential, "Tournament"),
            1, 0, 'C', 1);
        $pdf->Cell($spacerCellWidth, self::$judgeNameCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell($cellWidth, self::$judgeNameCellHeight,
            $judges[1]->Surname .
            ' ' .
            mb_substr($judges[1]->FirstName, 0, 1) .
            '. ' .
            mb_substr($judges[1]->Patronymic, 0, 1) .
            '. ('
            . $judges[1]->Region
            . '), '
            . get_text("JudgeAccreditation_" . $judges[1]->Credential, "Tournament"),
            1, 0, 'C', 1);
        $pdf->Cell($spacerCellWidth, self::$judgeNameCellHeight, '', 0, 0, 'R', 0);
        $pdf->Cell($cellWidth, self::$judgeNameCellHeight,
            $judges[2]->Surname .
            ' ' .
            mb_substr($judges[2]->FirstName, 0, 1) .
            '. ' .
            mb_substr($judges[2]->Patronymic, 0, 1) .
            '. ('
            . $judges[2]->Region
            . '), '
            . get_text("JudgeAccreditation_" . $judges[2]->Credential, "Tournament"),
            1, 1, 'C', 1);
    }

    static function getOfficialsBlockHeight()
    {
        if (getModuleParameter("Tournament", "InternationalProtocol", false, $_SESSION['TourId'])) {
            return 0;
        }

        return self::$offsetBeforeOfficials + self::$judgeRoleCellHeight + self::$judgeSignatureCellHeight + self::$judgeNameCellHeight;
    }
}

?>