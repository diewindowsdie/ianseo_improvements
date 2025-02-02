<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/pdf/ResultPDF.inc.php');

checkACL(AclIndividuals, AclReadOnly);

//в этом отчете печатаем все
$isCompleteResultBook = true;

$pdf = new ResultPDF('');

//по умолчанию в лесенках нет данных по каждому попаданию
$_REQUEST["ShowSetArrows"] = 1;

//медалисты в личке и командах
$pdf->Titolo = get_text('MedallistsByEvent', 'Tournament');
include '../../Final/PDFMedalList.php';
$pdf->AddPage();

//личная квалификация
//todo здесь позже будет отчет обо всей квалификации, не только личка - название нужно будет поменять
$pdf->Titolo = get_text('ResultIndAbs','Tournament');
include '../../Qualification/PrnIndividualAbs.php';
$pdf->AddPage();

//сетки
$pdf->Titolo = get_text('VersionBracketsInd', 'Tournament');
include '../../Final/Individual/PrnBracket.php';
$pdf->AddPage();

//лесенки
$pdf->Titolo = get_text('RankingInd');
include '../../Final/Individual/PrnRanking.php';
$pdf->AddPage();

//todo добавить командные сетки
//todo добавить командные лесенки

//судьи
$pdf->Titolo = get_text('StaffOnField','Tournament');
include '../../Tournament/PrnStaffField.php';

$pdf->Output();
?>