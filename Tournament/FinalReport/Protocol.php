<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/pdf/ResultPDF.inc.php');
require_once('Common/OrisFunctions.php');

checkACL(array(AclIndividuals, AclTeams, AclCompetition), AclReadOnly);

//в этом отчете печатаем все
$isCompleteResultBook = true;

$pdf = new ResultPDF('');

$_REQUEST["ShowSetArrows"] = 1;

//медалисты в личке и командах
$pdf->Titolo = get_text('MedallistsByEvent', 'Tournament');
include '../../Final/PDFMedalList.php';
$pdf->AddPage();

//личная квалификация
$PdfData = getQualificationIndividual();
if (count($PdfData->rankData['sections'])) {
    $pdf->Titolo = get_text('ResultIndAbs', 'Tournament');
    include '../../Qualification/PrnIndividualAbs.php';
    $pdf->AddPage();
}

//командная квалификация
$PdfData = getQualificationTeam();
if (count($PdfData->rankData['sections'])) {
    $pdf->Titolo = get_text('ResultSqAbs', 'Tournament');
    include '../../Qualification/PrnTeamAbs.php';
    $pdf->AddPage();
}

//сетки
$PdfData = getBracketsIndividual('', false, 0, 0, 1);
if (count($PdfData->rankData['sections'])) {
    $pdf->Titolo = get_text('VersionBracketsInd', 'Tournament');
    include '../../Final/Individual/PrnBracket.php';
    $pdf->AddPage();
}

//лесенки
$PdfData = getRankingIndividual();
if (count($PdfData->rankData['sections'])) {
    $pdf->Titolo = get_text('RankingInd');
    include '../../Final/Individual/PrnRanking.php';
    $pdf->AddPage();
}

//командные сетки
$PdfData = $PdfData = getBracketsTeams('', false, 0, 0, 1);
if (count($PdfData->rankData['sections'])) {
    $pdf->Titolo = get_text('VersionBracketsTeam', 'Tournament');
    include '../../Final/Team/PrnBracket.php';
    $pdf->AddPage();
}

//командные лесенки
$PdfData = getRankingTeams();
if (count($PdfData->rankData['sections'])) {
    $pdf->Titolo = get_text('RankingSq');
    include '../../Final/Team/PrnRanking.php';
    $pdf->AddPage();
}

//судьи
$pdf->Titolo = get_text('StaffOnField', 'Tournament');
include '../../Tournament/PrnStaffField.php';

$pdf->Output();
?>