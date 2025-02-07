<?php

class IRMStatusLegendProvider {
    private static $codeCellWidth = 10;
    private static $legendCellWidth = 85;
    private static $rowHeight = 4;

    public static function printLegend($pdf) {
        $pdf->SetXY(10, $pdf->GetY());

        $pdf->SetFont($pdf->FontStd, 'B', $pdf->FontSizeHead);
        $pdf->Cell(self::$codeCellWidth, self::$rowHeight, "DNS", 'TBL', 0, 'C', 1);
        $pdf->SetFont($pdf->FontStd, '', $pdf->FontSizeHead);
        $pdf->Cell(self::$legendCellWidth, self::$rowHeight, get_text('DNS', 'Tournament'), 'TBR', 0, 'L', 0);
        $pdf->SetFont($pdf->FontStd, 'B', $pdf->FontSizeHead);
        $pdf->Cell(self::$codeCellWidth, self::$rowHeight, "DNF", 'TBL', 0, 'C', 1);
        $pdf->SetFont($pdf->FontStd, '', $pdf->FontSizeHead);
        $pdf->Cell(self::$legendCellWidth, self::$rowHeight, get_text('DNF', 'Tournament'), 'TBR', 1, 'L', 0);
        $pdf->SetXY(10, $pdf->GetY());
        $pdf->SetFont($pdf->FontStd, 'B', $pdf->FontSizeHead);
        $pdf->Cell(self::$codeCellWidth, self::$rowHeight, "DSQ", 'TBL', 0, 'C', 1);
        $pdf->SetFont($pdf->FontStd, '', $pdf->FontSizeHead);
        $pdf->Cell(self::$legendCellWidth, self::$rowHeight, get_text('DSQ', 'Tournament'), 'TBR', 0, 'L', 0);
        $pdf->SetFont($pdf->FontStd, 'B', $pdf->FontSizeHead);
        $pdf->Cell(self::$codeCellWidth, self::$rowHeight, "DQB", 'TBL', 0, 'C', 1);
        $pdf->SetFont($pdf->FontStd, '', $pdf->FontSizeHead);
        $pdf->Cell(self::$legendCellWidth, self::$rowHeight, get_text('DQB', 'Tournament'), 'TBR', 1, 'L', 0);
    }

    static function getLegendBlockHeight() {
        return self::$rowHeight * 4;
    }
}