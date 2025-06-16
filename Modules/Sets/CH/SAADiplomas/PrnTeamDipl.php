<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once('Minimas.php');
require_once('Fun_Diploma.php');



// fetch request data
$ScriptAction = isset($_GET['Button']) ? substr($_GET['Button'],0,64) : '';
$ThisEvents = isset($_GET['Event']) ? $_GET['Event'] : array();
$templateOnly = (bool) (isset($_GET['templateOnly']) ?? 0);
$namesOnly = (bool) (isset($_GET['namesOnly']) ?? 0);
if($templateOnly && $namesOnly){
	// if both options are selected, just do standard behavior: print full diplomas
	$templateOnly = 0;
	$namesOnly = 0;
}

// some basic parsing to avoid the worst
foreach ($ThisEvents as &$TempEvent) {
	$TempEvent = substr($TempEvent,0,6);
}

// catch 'all' events
if ((count($ThisEvents) == 1) && ($ThisEvents[0] == '.')) {$ThisEvents = array();}

// fetch individual information
$TeamScores = get_team_diploma_athletes($ThisEvents);

// execute action
switch ($ScriptAction) {
	case 'Team Top 3 Ranking':
		CreateRankingPDFs();
		break;
	case 'Team Diplomas':
		CreateDiplomaPDFs($templateOnly, $namesOnly);
		break;
	default:
		CreateRankingPDFs();
}

function CreateRankingPDFs() {
	global $TeamScores;

	// use Ianseo PDF
	require_once('Common/pdf/IanseoPdf.php');
	
	// ATTENTION!
	// MUST BE called $PdfData
	$PdfData=array(
		'Description' => 'Team Reanking',
		'Order' => 1,
		'Continue' => 'Continue',
		'IndexName' => 'Team Ranking'
	);
	
	$pdf = new IanseoPdf($PdfData->Description);
	$pdf->Titolo = "Team Ranking";
	
	
	// some basic definitions
	$PDFAvailableWidth = $pdf->GetPageWidth() - ($pdf->getSideMargin() * 2);
	$w = array(20, 2, $PDFAvailableWidth - 62 , 20, 20);
	
	// default line width
	$pdf->SetLineWidth(0.2);
	
	// start page group
	$pdf->startPageGroup();
	
	// loop through the teams and output ranking
	$OldEvent = '';
	foreach ($TeamScores as $Team) {
		// start each event on a new page
		if ($Team['EventId'] != $OldEvent){
			// add page
			$pdf->AddPage();
			
			// reset to default colors
			$pdf->SetDefaultColor();
					
			// Print event title
			$pdf->SetY($pdf->GetY()+10);
			$pdf->SetFont($pdf->FontStd,'B', 18);
			$pdf->Cell(0, 6, $Team['EventName'] , 1, 1, 'C', 1);
			
			// titel
			$pdf->SetFont($pdf->FontStd,'B', 8);
			$pdf->Cell($w[0], 6, 'Rank', 0, 0, 'C', 0);
			$pdf->Cell($w[1], 6, '', 0, 0, 'L', 0);
			$pdf->Cell($w[2], 6, 'Club/Athletes', 0, 0, 'L', 0);
			$pdf->Cell($w[3], 6, 'Score', 0, 0, 'R', 0);
			$pdf->Cell($w[4], 6, 'Minima', 0, 0, 'R', 0);
			$pdf->Ln();
			$pdf->Line($pdf->getSideMargin(), $pdf->GetY(), $pdf->GetPageWidth() - $pdf->getSideMargin(), $pdf->GetY(), '');
			$pdf->SetXY($pdf->GetX(), $pdf->GetY()+0.1);
		}
			
		// print team information
		$pdf->SetFont($pdf->FontStd,'B', 48);
		$pdf->SetTextColor(0, 0, 0);
		$TempMinimaNotMet = ($Team['Minima'] > $Team['Score']) ? true : false;
		if ($TempMinimaNotMet) {
			$pdf->SetTextColor(255, 255, 255);
			$pdf->SetFillColor(0x33, 0x33, 0x33);
			$TempFill = 1;
		} else {
			$TempFill = 0;
		}
		$pdf->Cell($w[0], 40, $Team['Rank'], 0, 0, 'C', $TempFill);
		if ($TempMinimaNotMet) {
			$pdf->Ln(0);
			$pdf->SetFont($pdf->FontStd,'B', 8);
			$pdf->Cell($w[0], 6, 'Diploma', 0, 0, 'C', $TempFill);
		}
		$pdf->SetTextColor(0, 0, 0);
		$pdf->Cell($w[1], 6, '', 0, 0, 'C', 0);
		$pdf->SetFont($pdf->FontStd,'B', 18);
		$pdf->SetXY($pdf->GetX(), $pdf->GetY()+4.5);
		$pdf->Cell($w[2], 9, $Team['Club'], 0, 0, 'L', 0);
		if ($Team['Minima'] > $Team['Score']) {
			$pdf->SetTextColor(200, 0, 0);
		}
		$pdf->Cell($w[3], 9, $Team['Score'], 0, 0, 'R', 0);
		$pdf->SetFont($pdf->FontStd,'', 12);
		$pdf->Cell($w[4], 9, $Team['Minima'], 0, 0, 'R', 0);
		$pdf->Ln(10);
	
		// loop through athletes
		foreach ($Team['Athletes'] as $Athlete) {
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont($pdf->FontStd,'', 14);
			$pdf->Cell($w[0]+$w[1], 9, '', 0, 0, 'L', 0);
			$pdf->Cell($w[2], 7, $Athlete['EnFullName'], 0, 0, 'L', 0);
			if ($Athlete['Minima'] > $Athlete['QuScore']) {
				$pdf->SetTextColor(200, 0, 0);
			}
			$pdf->Cell($w[3], 7, $Athlete['QuScore'], 0, 0, 'R', 0);
			$pdf->SetFont($pdf->FontStd,'', 9);
			$pdf->Cell($w[4], 7, $Athlete['Minima'], 0, 0, 'R', 0);
			$pdf->Ln(7);
		}
		$pdf->Ln(4.5);
		$pdf->Line($pdf->getSideMargin(), $pdf->GetY(), $pdf->GetPageWidth() - $pdf->getSideMargin(), $pdf->GetY(), '');
		$pdf->SetXY($pdf->GetX(), $pdf->GetY()+0.1);
		
		$OldEvent = $Team['EventId'];
		
	}
	
	$pdf->Output();
}

function CreateDiplomaPDFs(bool $templatesOnly=false, bool $namesOnly = false) {
	global $TeamScores;
	
	// use Ianseo PDF
	require_once('Common/tcpdf/tcpdf.php');
	require_once('SaaDiplomaPdf.php');

	// create new PDF document
	$pdf = new SaaDiplomaPdf('Portrait', PDF_UNIT, 'A4', true, 'UTF-8', false);
	if($namesOnly) {
		$pdf->setPrintHeader( false );
		$pdf->setPrintFooter( false );
	}

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Swiss Archery Association');
	$pdf->SetTitle('Team Diplomas');
	$pdf->SetSubject('Team Diplomas');
	$pdf->SetKeywords('SAA, Team, Diplomas, I@NSEO');

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	
	// set margins
	$pdf->SetMargins(10, 50, 10);
	$pdf->SetHeaderMargin(10);
	$pdf->SetFooterMargin(20);
	
	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, 30);
	
	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	
	// set default font subsetting mode
	$pdf->setFontSubsetting(true);
	
	// set text color
	$pdf->SetTextColor(0, 0, 0);
	
	// ---------------------------------------------------------
	
	// loop through the teams and output Diplomas
	$OldEvent = '';
	foreach ($TeamScores as $Team) {
		
		// skip if minima is met
		if ($Team['Minima'] <= $Team['Score']) {continue;}
		
		// start each diploma on a new page
		// add page
		$pdf->AddPage();

		if($templatesOnly) {
			// Only print one single template page
			break;
		}

		// Print event title
		//$pdf->SetFont($pdf->FontStd,'B', 24);
		//$pdf->Cell(0, 6, 'Schweizermeisterschaft' , 0, 1, 'C', 0);
		//$pdf->Ln(30);

		$pdf->SetXY($pdf->GetX(), 110);
		$pdf->SetFont($pdf->FontStd,'', 28);
		
		// event
		$pdf->Cell(0, 6, $Team['EventName'] , 0, 1, 'C', 0);
		$pdf->Ln(5);
			
		// rank
		$pdf->SetFont($pdf->FontStd,'B', 30);
		$pdf->Cell(0, 6, $Team['Rank'] . '. Rang', 0, 1, 'C', 0);
		$pdf->Ln(5);

		// team mebers
		$pdf->SetFont($pdf->FontStd,'', 24);
		foreach ($Team['Athletes'] as $Athlete) {
			$pdf->Cell(0, 10, $Athlete['EnFullName'], 0, 0, 'C', 0);
			$pdf->Ln(12);
		}
		$pdf->Ln(5);

		// club name
		$pdf->SetFont($pdf->FontStd,'B', 24);
		$pdf->Cell(0, 9, $Team['Club'], 0, 1, 'C', 0);
		//$pdf->Ln(5);
		//$pdf->SetXY($pdf->GetX(), $pdf->GetY()+10);

		// Date value
		$pdf->SetFont('helvetica', '', 11);
		$pdf->Text(21, 250, strftime('%d.%m.%Y', time()));
	}	

	$pdf->Output();
}
?>