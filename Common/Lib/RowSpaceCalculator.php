<?php
function getNumberOfRowsStillFittingPage($pdf, $rowHeight, $additionalSpaceUsed = 0)
{
    $startNumber = 30; //получено эмпирически
    while ($pdf->SamePage($startNumber * $rowHeight + $additionalSpaceUsed)) {
        $startNumber++;
    }
    while (!$pdf->SamePage($startNumber * $rowHeight + $additionalSpaceUsed)) {
        $startNumber--;
    }

    return $startNumber;
}
?>