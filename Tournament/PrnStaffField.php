<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Common/pdf/ResultPDF.inc.php');
require_once('Common/Fun_FormatText.inc.php');

require_once('Common/TournamentOfficials.php');
checkACL(AclCompetition, AclReadOnly);
define("HideCols", GetParameter("IntEvent"));

$CatJudge=isset($_REQUEST['judge']);
$CatDos=isset($_REQUEST['dos']);
$CatJury=isset($_REQUEST['jury']);
$CatOC=isset($_REQUEST['oc']);

if(!isset($isCompleteResultBook))
	$pdf = new ResultPDF((get_text('StaffOnField','Tournament')),true,'',false);


$Ses=StrSafe_DB($_SESSION['TourId']);

	$Filter="";

    $pdf->SetY($pdf->GetY() - 8);

//высота одной строки
$rowHeight = 6;
//размер шрифта по умолчанию
$fontSize = 9;
//минимальное количество строк на следующей странице, если отчет не помещается на текущее количество страниц
$numberOfJudgesOnNextPage = 3;
//отступ перед блоком с подписями судей
$marginBeforeSignatures = 10;
//высота ячейки под текстовый заголовок
$titleRowHeight = 15;

function getNumberOfRowsStillFittingPage($pdf, $rowHeight, $additionalSpaceUsed = 0) {
    $startNumber = 34; //получено эмпирически
    while ($pdf->SamePage($startNumber * $rowHeight + $additionalSpaceUsed)) {
        $startNumber++;
    }
    while(!$pdf->SamePage($startNumber * $rowHeight + $additionalSpaceUsed)) {
        $startNumber--;
    }

    return $startNumber;
}
$Select="
	SELECT ti.*, it.*, CoNameComplete, ucase(TiName) as TiUpperName
	FROM TournamentInvolved AS ti 
    LEFT JOIN Countries on TiCountry=CoId and TiTournament=CoTournament
    LEFT JOIN InvolvedType AS it ON ti.TiType=it.ItId
	WHERE ti.TiTournament={$Ses} AND it.ItId IS NOT NULL {$Filter}
	ORDER BY TiIsSigningProtocols desc, ItId IS NOT NULL, ItJudge=0, ItJudge, ItDoS=0, ItDoS, ItJury=0, ItJury, ItOc, TiName, TiGivenName ASC";

$resultSet=safe_r_sql($Select);
$numberOfJudges = mysqli_num_rows($resultSet);
$additionalSpaceUsed = 1 + $marginBeforeSignatures + TournamentOfficials::getOfficialsBlockHeight() + $titleRowHeight;

//проверим, помещается ли все на одну страницу
//+1 к количеству судей из-за заголовка таблицы
if ($numberOfJudges + 1 > getNumberOfRowsStillFittingPage($pdf, $rowHeight, $additionalSpaceUsed)) {
    //не помещается, подгоняем высоту так, чтобы было не меньше $numberOfJudgesOnNextPage на следующей, и увеличиваем шрифт
    while ($numberOfJudges + 1 - getNumberOfRowsStillFittingPage($pdf, $rowHeight, $titleRowHeight) < $numberOfJudgesOnNextPage) {
        $rowHeight += 0.1;
        $fontSize += 0.1;
    }
}

//заголовок и первая строка
$pdf->SetFont($pdf->FontStd,'B',$fontSize + 3);
$pdf->Cell(190, $titleRowHeight, 'Список судей', 0, 1, 'C');

$pdf->SetFont($pdf->FontStd,'B', $fontSize);
$pdf->Cell(8, $rowHeight, '№', 1, 0, 'L', 1);
$nameHeader = 'Judge name';
if (SelectLanguage() == 'ru') {
    $nameHeader = get_text('FamilyName', 'Tournament') . ', ' .
        mb_strtolower(get_text('Name', 'Tournament')) . ', ' .
        mb_strtolower(get_text('LastName', 'Tournament'));
}
$pdf->Cell(62, $rowHeight, $nameHeader, 1, 0, 'L', 1);
$pdf->Cell(55, $rowHeight, get_text('JudgeFunction', 'Tournament'), 1, 0, 'L', 1);
$pdf->Cell(25, $rowHeight, get_text('JudgeAccreditation', 'Tournament'), 1, 0, 'L', 1);
$pdf->Cell(40, $rowHeight, get_text('JudgeRegion', 'Tournament'), 1, 1, 'L', 1);



$pdf->SetFont($pdf->FontStd,'',$fontSize);
$index = 1;
while ($judge=safe_fetch($resultSet)) {
    if (!$pdf->SamePage($rowHeight + 1)) {
        $pdf -> AddPage();
    }

    $pdf->Cell(8, $rowHeight, $index, 1, 0, 'L');
    $pdf->Cell(62, $rowHeight, $judge->TiName . ' ' . $judge->TiGivenName . ' ' . $judge -> TiLastName, 1, 0, 'L');
    $pdf->Cell(55, $rowHeight, get_text($judge->ItDescription, 'Tournament'), 1, 0, 'L');
    $pdf->Cell(25, $rowHeight, $judge->TiAccreditation, 1, 0, 'C');
    $pdf->Cell(40, $rowHeight, $judge->CoNameComplete, 1, 1, 'L');
    $index++;
}

$pdf->SetY($pdf->GetY() + $marginBeforeSignatures);
TournamentOfficials::printOfficials($pdf);

if(!isset($isCompleteResultBook))
{
	if(isset($_REQUEST['ToFitarco']))
	{
		$Dest='D';
		if (isset($_REQUEST['Dest']))
			$Dest=$_REQUEST['Dest'];
		$pdf->Output($_REQUEST['ToFitarco'],$Dest);
	}
	else
		$pdf->Output();
}
