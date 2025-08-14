<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
checkFullACL(AclParticipants, 'pEntries', AclReadOnly);
require_once('Common/pdf/ResultPDF.inc.php');

define("HideCols", GetParameter("IntEvent"));

$internationalProtocol = getModuleParameter("Tournament", "InternationalProtocol", false, $_SESSION['TourId']);

$pdf = new ResultPDF((get_text('StartlistAlpha','Tournament')));

$MyQuery = "SELECT EnCode as Bib, EnName AS Name, upper(EnFirstName) AS FirstName, EnMiddleName, QuSession AS Session, SUBSTRING(QuTargetNo,2) AS TargetNo, c.CoCode AS NationCode, c.CoNameComplete AS Nation, EnClass AS ClassCode, EnDivision AS DivCode, EnAgeClass as AgeClass, EnSubClass as SubClass, EnStatus as Status, EnIndClEvent AS `IC`, EnTeamClEvent AS `TC`, EnIndFEvent AS `IF`, EnTeamFEvent as `TF`, EnTeamMixEvent as `TM`, ";
$MyQuery.= "s.ScDescription as SubclassDescription, co2.CoNameComplete as Nation2, co3.CoNameComplete as Nation3, date_format(if(EnDOB='0000-00-00', null, EnDOB), '".get_text('DateFmtDB')."') as DOB, ";
$MyQuery.= "ISNULL(c.CoId) as invalidCountry, ISNULL(DivId) as invalidDivision, (ISNULL(c1.ClId) OR  LOCATE(c2.ClId, c1.ClValidClass)=0) as invalidAgeClass, (ISNULL(c2.ClId) OR  LOCATE(c2.ClId, c1.ClValidClass)=0) as invalidClass, (ISNULL(EnDOB) or EnDOB = '0000-00-00') as invalidDOB ";
$MyQuery.= "FROM Entries AS e ";
$MyQuery.= "LEFT JOIN Countries AS c ON e.EnCountry=c.CoId AND e.EnTournament=c.CoTournament ";
$MyQuery.= "LEFT JOIN Countries AS co2 ON e.EnCountry2=co2.CoId AND e.EnTournament=co2.CoTournament ";
$MyQuery.= "LEFT JOIN Countries AS co3 ON e.EnCountry3=co3.CoId AND e.EnTournament=co3.CoTournament ";
$MyQuery.= "LEFT JOIN Qualifications AS q ON e.EnId=q.QuId ";
$MyQuery.= "LEFT JOIN SubClass s ON e.EnTournament=s.ScTournament AND e.EnSubclass=s.ScId ";
$MyQuery.= "LEFT JOIN Divisions ON e.EnTournament=DivTournament AND e.EnDivision=DivId ";
$MyQuery.= "LEFT JOIN Classes as c1 ON e.EnTournament=c1.ClTournament AND e.EnAgeClass=c1.ClId and (e.EnSex=c1.ClSex or c1.ClSex=-1) ";
$MyQuery.= "LEFT JOIN Classes as c2 ON e.EnTournament=c2.ClTournament AND e.EnClass=c2.ClId and (e.EnSex=c2.ClSex or c1.ClSex=-1) ";


$MyQuery.= "WHERE EnTournament = " . StrSafe_DB($_SESSION['TourId']) . " ";
$MyQuery.= "AND (EnStatus!=0 OR (EnIndClEvent=0 AND EnTeamClEvent=0 AND EnIndFEvent=0 AND EnIndFEvent=0) OR EnCountry=0 OR DivId is null OR c1.ClId is null OR c2.ClId is null OR LOCATE(c2.ClId, c1.ClValidClass)=0) or EnDOB is null ";
if(isset($_REQUEST["Session"]) && is_numeric($_REQUEST["Session"]))
	$MyQuery .= "AND QuSession = " . StrSafe_DB($_REQUEST["Session"]) . " ";
$MyQuery.= "ORDER BY Nation, Name, FirstName, EnMiddleName, TargetNo ";

//print_r($MyQuery);
$Rs=safe_r_sql($MyQuery);
if($Rs) {
	$ShowStatusLegend = false;
	$FirstTime=true;
	while($MyRow=safe_fetch($Rs))
	{
		if ($FirstTime || !$pdf->SamePage(4))
		{
			$TmpSegue = !$pdf->SamePage(4);
			$StartLetter = substr($MyRow->Name,0,1);
		   	$pdf->SetFont($pdf->FontStd,'B',10);
			$pdf->Cell(190, 6, (get_text('PartecipantListError','Tournament')), 1, 1, 'C', 1);
			if($TmpSegue)
			{
				$pdf->SetXY(170,$pdf->GetY()-6);
			   	$pdf->SetFont($pdf->FontStd,'I',6);
				$pdf->Cell(30, 6,  (get_text('Continue')), 0, 1, 'R', 0);
			}
		   	$pdf->SetFont($pdf->FontStd,'B',7);
			$pdf->Cell(45 + ($internationalProtocol ? 8 : 0), 4,  (get_text('Athlete')), 1, 0, 'L', 1);
			$pdf->Cell(48, 4,  (get_text('Country')), 1, 0, 'L', 1);
            $pdf->Cell(12, 4,  (get_text('DOB', "Tournament")), 1, 0, 'C', 1);
			$pdf->Cell(7, 4,  (get_text('SessionShort','Tournament')), 1, 0, 'C', 1);
			$pdf->Cell(11, 4,  (get_text('Target')), 1, 0, 'C', 1);
			$pdf->Cell(11, 4,  (get_text('AgeCl')), 1, 0, 'C', 1);
            if (!$internationalProtocol) {
			    $pdf->Cell(8, 4,  (get_text('SubCl','Tournament')), 1, 0, 'C', 1);
            }
			$pdf->Cell(12, 4,  (get_text('Division')), 1, 0, 'C', 1);
			$pdf->Cell(12, 4,  (get_text('Class')), 1, 0, 'C', 1);
			//Disegna i Pallini
			$pdf->DrawParticipantHeader();
		   	$pdf->SetFont($pdf->FontStd,'B',7);
			$pdf->Cell(10, 4,  (get_text('Status','Tournament')), 1, 1, 'C', 1);
			$pdf->SetFont($pdf->FontStd,'',1);
			$pdf->Cell(190, 0.5,  '', 1, 1, 'C', 0);
			$FirstTime=false;
		}
	   	$pdf->SetFont($pdf->FontStd,'',7);
	   	$pdf->SetFont($pdf->FontStd,'B',7);
        $pdf->Cell(45 + ($internationalProtocol ? 8 : 0), 4,  getFullAthleteName($MyRow->FirstName, $MyRow->Name, $MyRow->EnMiddleName), 1, 0, 'L', 0);
	   	$pdf->SetFont($pdf->FontStd,'',7);
		$pdf->Cell(48, 4,  getFullCountryName($MyRow->Nation, $MyRow->Nation2, $MyRow->Nation3), 'RTB', 0, 'L', ($MyRow->invalidCountry));
        $pdf->Cell(12, 4,  ($MyRow->DOB), 1, 0, 'R', ($MyRow->invalidDOB));
		$pdf->Cell(7, 4,  ($MyRow->Session), 1, 0, 'R', 0);
		$pdf->Cell(11, 4,  ($MyRow->TargetNo), 1, 0, 'R', 0);
		$pdf->Cell(11, 4,  ($MyRow->AgeClass), 1, 0, 'C', ($MyRow->invalidAgeClass));
        if (!$internationalProtocol) {
            $pdf->Cell(8, 4, ($MyRow->SubclassDescription), 1, 0, 'C', 0);
        }
		$pdf->Cell(12, 4,  ($MyRow->DivCode), 1, 0, 'C', ($MyRow->invalidDivision));
		$pdf->Cell(12, 4,  ($MyRow->ClassCode), 1, 0, 'C', ($MyRow->invalidClass));
		//Disegna i Pallini per la partecipazione
		$pdf->DrawParticipantDetails($MyRow->IC, $MyRow->IF, $MyRow->TC, $MyRow->TF, $MyRow->TM);
		$pdf->SetDefaultColor();
		$pdf->SetFont($pdf->FontStd,'',7);
		$ShowStatusLegend = ($ShowStatusLegend || ($MyRow->Status!=0));
		$pdf->Cell(10, 4,  ($MyRow->Status==0 ? '' : ($MyRow->Status)) , 1, 1, 'C', 0);
	}

//Legenda per la partecipazione alle varie fasi
	$pdf->DrawPartecipantLegend();
//Legenda per lo stato di ammisisone alle gare
	if($ShowStatusLegend)
		$pdf->DrawStatusLegend();

	safe_free_result($Rs);
}
if(!isset($isCompleteResultBook))
	$pdf->Output();
?>