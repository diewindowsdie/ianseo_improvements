<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
checkFullACL(AclAccreditation, 'acStandard', AclReadOnly);
require_once('Common/pdf/ResultPDF.inc.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Fun_Number.inc.php');

$OpDetails = "Accreditation";
$payDetails = false;
if(isset($_REQUEST["OperationType"])) {
    $OpDetails = $_REQUEST["OperationType"];
    $payDetails = ($_REQUEST["OperationType"] == 'Payments');
}

if(!isset($isCompleteResultBook))
	$pdf = new ResultPDF(get_text($OpDetails,'Tournament'));

$TmpWhere="";
if(isset($_REQUEST["ArcherName"]) && preg_match("/^[-,0-9A-Z]*$/i",str_replace(" ","",$_REQUEST["ArcherName"]))) {
	foreach(explode(",",$_REQUEST["ArcherName"]) as $Value) {
		$Tmp=NULL;
		if(preg_match("/^([0-9A-Z]*)\-([0-9A-Z]*)$/i",str_replace(" ","",$Value),$Tmp))
			$TmpWhere .= "(EnFirstName >= " . StrSafe_DB(strtoupper($Tmp[1]) ) . " AND EnFirstName <= " . StrSafe_DB(strtoupper($Tmp[2].chr(255))) . ") OR ";
		else
			$TmpWhere .= "EnFirstName LIKE " . StrSafe_DB(strtoupper(trim($Value)) . "%") . " OR ";
	}
	$TmpWhere = substr($TmpWhere,0,-3);
}

$MyQuery = "SELECT EnCode as Bib, EnName AS Name, left(EnFirstName,1) AS Initial, upper(EnFirstName) AS FirstName, EnMiddleName, QuSession AS Session, SUBSTRING(QuTargetNo,2) AS TargetNo, c.CoCode AS NationCode, c.CoNameComplete AS Nation, c2.CoNameComplete as Nation2, c3.CoNameComplete as Nation3, EnClass AS ClassCode, EnDivision AS DivCode, EnAgeClass as AgeClass, EnSubClass as SubClass, sc.ScDescription as SubclassDescription, EnStatus as Status, EnIndClEvent AS `IC`, EnTeamClEvent AS `TC`, EnIndFEvent AS `IF`, EnTeamFEvent as `TF`, EnTeamMixEvent as `TM`, IF(AEId IS NULL, 0, 1) as OpDone, EnPays, APPrice  ";
$MyQuery.= "FROM Entries AS e ";
$MyQuery.= "LEFT JOIN Countries AS c ON e.EnCountry=c.CoId AND e.EnTournament=c.CoTournament ";
$MyQuery.= "LEFT JOIN Countries AS c2 ON e.EnCountry2=c2.CoId AND e.EnTournament=c2.CoTournament ";
$MyQuery.= "LEFT JOIN Countries AS c3 ON e.EnCountry3=c3.CoId AND e.EnTournament=c3.CoTournament ";
$MyQuery.= "LEFT JOIN SubClass AS sc ON e.EnSubClass=sc.ScId AND e.EnTournament=sc.ScTournament ";
$MyQuery.= "LEFT JOIN Qualifications AS q ON e.EnId=q.QuId ";
$MyQuery.= "LEFT JOIN AccEntries AS ae ON e.EnId=ae.AEId AND e.EnTournament=ae.AETournament AND ae.AEOperation=(SELECT AOTId FROM AccOperationType WHERE AOTDescr=" . StrSafe_DB($OpDetails) . ") ";
$MyQuery.= "LEFT JOIN AccPrice AS ap ON CONCAT(EnDivision,EnClass) LIKE ap.APDivClass AND e.EnTournament=ap.APTournament ";
$MyQuery.= "WHERE EnAthlete=1 AND EnTournament = " . StrSafe_DB($_SESSION['TourId']) . " ";
if(isset($_REQUEST["Session"]) && is_numeric($_REQUEST["Session"]))
	$MyQuery .= "AND QuSession = " . StrSafe_DB($_REQUEST["Session"]) . " ";
if($TmpWhere != "")
	$MyQuery .= "AND (" . $TmpWhere . ")";
$MyQuery.= "ORDER BY FirstName, Name, TargetNo ";
$Rs=safe_r_sql($MyQuery);
$StartLetter = ".";
$ShowStatusLegend = false;
while($MyRow=safe_fetch($Rs)) {
    if ($StartLetter != $MyRow->Initial || !$pdf->SamePage(4)) {
        $TmpSegue = !($pdf->SamePage(4));

        if($StartLetter != $MyRow->Initial && !$pdf->SamePage(20)) {
            $TmpSegue=false;
            $pdf->AddPage();
        } else if($StartLetter != ".") {
            $pdf->SetY($pdf->GetY() + 5);
        }
        $StartLetter = $MyRow->Initial;
        $pdf->SetDefaultColor();
        $pdf->SetFont($pdf->FontStd,'B',10);
        $pdf->Cell(190, 6,  $StartLetter, 1, 1, 'C', 1);
        if($TmpSegue){
            $pdf->SetXY(170,$pdf->GetY()-6);
            $pdf->SetFont($pdf->FontStd,'I',6);
            $pdf->Cell(30, 6,  (get_text('Continue')), 0, 1, 'R', 0);
        }
        $pdf->SetFont($pdf->FontStd,'B',7);
        $pdf->Cell(60, 4,  (get_text('Athlete')), 1, 0, 'L', 1);
        $pdf->Cell(46+($payDetails ? 0:15), 4,  (get_text('Country')), 1, 0, 'L', 1);
        $pdf->Cell(7, 4,  (get_text('SessionShort','Tournament')), 1, 0, 'C', 1);
        $pdf->Cell(10, 4,  (get_text('Target')), 1, 0, 'C', 1);
        $pdf->Cell(8, 4,  (get_text('SubCl','Tournament')), 1, 0, 'C', 1);
        $pdf->Cell(10, 4,  (get_text('Division')), 1, 0, 'C', 1);
        $pdf->Cell(10, 4,  (get_text('Cl')), 1, 0, 'C', 1);
        //Disegna i Pallini
        $pdf->DrawParticipantHeader();
        $pdf->SetFont($pdf->FontStd,'B',7);
        $pdf->Cell(10, 4,  (get_text('Status','Tournament')), 1,  ($payDetails ? 0:1), 'C', 1);
        if($payDetails) {
            $pdf->Cell(15, 4, get_text('Price', 'Tournament'), 1, 1, 'C', 1);
        }
        $pdf->SetFont($pdf->FontStd,'',1);
        $pdf->Cell(190, 0.5,  '', 1, 1, 'C', 0);

    }
    //Disegno il quadrato pieno per gli accreditati
    if($MyRow->OpDone!=0)
        $pdf->SetFillColor(0xCC,0xCC,0xCC);
    else
        $pdf->SetDefaultColor();
//Inizio dei dati "scritti"
    if($MyRow->OpDone!=0) {
        $pdf->SetAccreditedColor();
    } else {
        $pdf->SetDefaultColor();
    }
    $pdf->SetFont($pdf->FontStd,'',7);
    $pdf->SetFont($pdf->FontStd,'B',7);
    $pdf->Cell(60, 4, getFullAthleteName($MyRow->FirstName, $MyRow->Name, $MyRow->EnMiddleName), 1, 0, 'L', $MyRow->OpDone);
    $pdf->SetFont($pdf->FontStd,'',7);
    $pdf->Cell(46+($payDetails ? 0:15), 4, getFullCountryName($MyRow->Nation, $MyRow->Nation2, $MyRow->Nation3), 'RTB', 0, 'L', $MyRow->OpDone);
    $pdf->Cell(7, 4,  ($MyRow->Session), 1, 0, 'R', $MyRow->OpDone);
    $pdf->Cell(10, 4,  ($MyRow->TargetNo), 1, 0, 'R', $MyRow->OpDone);
    $pdf->Cell(8, 4,  ($MyRow->SubclassDescription), 1, 0, 'C', $MyRow->OpDone);
    $pdf->Cell(10, 4,  ($MyRow->DivCode), 1, 0, 'C', $MyRow->OpDone);
    $pdf->Cell(10, 4,  ($MyRow->ClassCode), 1, 0, 'C', $MyRow->OpDone);
    //Disegna i Pallini per la partecipazione
    $pdf->DrawParticipantDetails($MyRow->IC, $MyRow->IF, $MyRow->TC, $MyRow->TF, $MyRow->TM, $MyRow->OpDone);
    if($MyRow->OpDone!=0)
        $pdf->SetAccreditedColor();
    else
        $pdf->SetDefaultColor();
    $pdf->SetFont($pdf->FontStd,'',7);
    $ShowStatusLegend = ($ShowStatusLegend || ($MyRow->Status!=0));
    $pdf->Cell(10, 4,  ($MyRow->Status==0 ? '' : ($MyRow->Status)) , 1, ($payDetails ? 0:1), 'C', $MyRow->OpDone);
    if($payDetails) {
        $pdf->Cell(15, 4, ($MyRow->EnPays == 1 ? NumFormat($MyRow->APPrice, 2) : NumFormat(0, 2)) . " " . $pdf->writeCurrency(), 1, 1, 'C', $MyRow->OpDone);
    }
}

//Legenda per la partecipazione alle varie fasi
$pdf->DrawPartecipantLegend();
//Legenda per lo stato di ammisisone alle gare
if($ShowStatusLegend)
    $pdf->DrawStatusLegend();

safe_free_result($Rs);

if(!isset($isCompleteResultBook))
	$pdf->Output();
