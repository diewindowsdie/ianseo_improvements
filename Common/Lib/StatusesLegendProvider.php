<?php

/**
 * Класс предназначен для вывода легенды об обозначениях перестрелок, жеребьевки и IRM статусов
 */
class StatusLegendProvider
{
    private static $codeCellWidth = 10;
    private static $legendCellWidth = 85;
    private static $rowHeight = 4;

    private $pdf;
    private bool $printShootOffLegend;

    /**
     * @param mixed $pdf Документ, в который необходимо добавить легенду IRM-статусов
     * @param bool $printShootOffLegend Нужно ли печатать поля с легендой для обозначений перестрелок и жеребьевки
     */
    function __construct($pdf, $printShootOffLegend = false)
    {
        $this->pdf = $pdf;
        $this->printShootOffLegend = $printShootOffLegend;
    }

    /**
     * Вывести легенду
     */
    public function printLegend()
    {
        $this->pdf->SetXY(10, $this->pdf->GetY() + 5);
        $this->pdf->SetFont($this->pdf->FontStd, 'B', $this->pdf->FontSizeLines);
        $this->pdf->Cell(190, self::$rowHeight, $this->pdf->LegendSO, 1, 1, 'C', 1);

        if ($this->printShootOffLegend) {
            $this->pdf->SetFont($this->pdf->FontStd,'B',$this->pdf->FontSizeHead);
            $this->pdf->Cell(10, self::$rowHeight, $this->pdf->CoinTossShort, 'TBL', 0, 'C', 1);
            $this->pdf->SetFont($this->pdf->FontStd,'',$this->pdf->FontSizeHead);
            $this->pdf->Cell(85, self::$rowHeight, $this->pdf->CoinToss, 'TBR', 0, 'L', 0);
            $this->pdf->SetFont($this->pdf->FontStd,'B',$this->pdf->FontSizeHead);
            $this->pdf->Cell(10, self::$rowHeight, $this->pdf->ShotOffShort, 'TBL', 0, 'C', 1);
            $this->pdf->SetFont($this->pdf->FontStd,'',$this->pdf->FontSizeHead);
            $this->pdf->Cell(85, self::$rowHeight, $this->pdf->ShotOff, 'TBR', 1, 'L', 0);
        }

        $this->pdf->SetFont($this->pdf->FontStd, 'B', $this->pdf->FontSizeHead);
        $this->pdf->Cell(self::$codeCellWidth, self::$rowHeight, "DNS", 'TBL', 0, 'C', 1);
        $this->pdf->SetFont($this->pdf->FontStd, '', $this->pdf->FontSizeHead);
        $this->pdf->Cell(self::$legendCellWidth, self::$rowHeight, get_text('DNS', 'Tournament'), 'TBR', 0, 'L', 0);
        $this->pdf->SetFont($this->pdf->FontStd, 'B', $this->pdf->FontSizeHead);
        $this->pdf->Cell(self::$codeCellWidth, self::$rowHeight, "DNF", 'TBL', 0, 'C', 1);
        $this->pdf->SetFont($this->pdf->FontStd, '', $this->pdf->FontSizeHead);
        $this->pdf->Cell(self::$legendCellWidth, self::$rowHeight, get_text('DNF', 'Tournament'), 'TBR', 1, 'L', 0);
        $this->pdf->SetXY(10, $this->pdf->GetY());
        $this->pdf->SetFont($this->pdf->FontStd, 'B', $this->pdf->FontSizeHead);
        $this->pdf->Cell(self::$codeCellWidth, self::$rowHeight, "DSQ", 'TBL', 0, 'C', 1);
        $this->pdf->SetFont($this->pdf->FontStd, '', $this->pdf->FontSizeHead);
        $this->pdf->Cell(self::$legendCellWidth, self::$rowHeight, get_text('DSQ', 'Tournament'), 'TBR', 0, 'L', 0);
        $this->pdf->SetFont($this->pdf->FontStd, 'B', $this->pdf->FontSizeHead);
        $this->pdf->Cell(self::$codeCellWidth, self::$rowHeight, "DQB", 'TBL', 0, 'C', 1);
        $this->pdf->SetFont($this->pdf->FontStd, '', $this->pdf->FontSizeHead);
        $this->pdf->Cell(self::$legendCellWidth, self::$rowHeight, get_text('DQB', 'Tournament'), 'TBR', 1, 'L', 0);
    }

    /**
     * @return int Высота блока легенды
     */
    function getLegendBlockHeight(): int
    {
        return $this->printShootOffLegend ? self::$rowHeight * 4 : self::$rowHeight * 3;
    }
}