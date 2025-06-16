<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once('Minimas.php');
require_once('Fun_Diploma.php');
require_once('Common/pdf/IanseoPdf.php');



// fetch requested events
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
$IndividualScores = get_ind_diploma_athletes($ThisEvents);

// execute action
switch ($ScriptAction) {
	case 'Individual Top 3 Ranking':
		CreateRankingPDFs();
		break;
	case 'Individual Diplomas':
		CreateDiplomaPDFs($templateOnly, $namesOnly);
		break;
	default:
		CreateRankingPDFs();
}


function CreateRankingPDFs() {
	global $IndividualScores;

	// ATTENTION!
	// MUST BE called $PdfData
	$PdfData=array(
		'Description' => 'Individual Ranking',
		'Order' => 1,
		'Continue' => 'Continue',
		'IndexName' => 'Individual Ranking'
	);
	
	$pdf = new IanseoPdf($PdfData->Description);
	$pdf->Titolo = "Individual Ranking";
	
	
	// some basic definitions
	$PDFAvailableWidth = $pdf->GetPageWidth() - ($pdf->getSideMargin() * 2);
	$w = array(20, 2, $PDFAvailableWidth - 62 , 20, 20);
	
	// default line width
	$pdf->SetLineWidth(0.2);
	
	// start page group
	$pdf->startPageGroup();
	
	// loop through the individuals and output ranking
	$OldEvent = '';
	foreach ($IndividualScores as $Individual) {
		// start each event on a new page
		if ($Individual['IndEvent'] != $OldEvent){
			// add page
			$pdf->AddPage();
			
			// reset to default colors
			$pdf->SetDefaultColor();
					
			// Print event title
			$pdf->SetY($pdf->GetY()+10);
			$pdf->SetFont($pdf->FontStd,'B', 18);
			$pdf->Cell(0, 6, $Individual['EvEventName'] , 1, 1, 'C', 1);
			
			// titel
			$pdf->SetFont($pdf->FontStd,'B', 8);
			$pdf->Cell($w[0], 6, 'Rank', 0, 0, 'C', 0);
			$pdf->Cell($w[1], 6, '', 0, 0, 'L', 0);
			$pdf->Cell($w[2], 6, 'Athlete/Club', 0, 0, 'L', 0);
			$pdf->Cell($w[3], 6, 'Score', 0, 0, 'R', 0);
			$pdf->Cell($w[4], 6, 'Minima', 0, 0, 'R', 0);
			$pdf->Ln();
			$pdf->Line($pdf->getSideMargin(), $pdf->GetY(), $pdf->GetPageWidth() - $pdf->getSideMargin(), $pdf->GetY(), '');
			$pdf->SetXY($pdf->GetX(), $pdf->GetY()+0.1);
		}
			
		// print athlet information
		$pdf->SetFont($pdf->FontStd,'B', 48);
		$TempMinimaNotMet = ($Individual['Minima'] > $Individual['QuScore']) ? true : false;
		if ($TempMinimaNotMet) {
			$pdf->SetTextColor(255, 255, 255);
			$pdf->SetFillColor(0x33, 0x33, 0x33);
			$TempFill = 1;
		} else {
			$TempFill = 0;
		}
		$pdf->Cell($w[0], 25, $Individual['QuClRank'], 0, 0, 'C', $TempFill);
		if ($TempMinimaNotMet) {
			$pdf->Ln(0);
			$pdf->SetFont($pdf->FontStd,'B', 8);
			$pdf->Cell($w[0], 6, 'Diploma', 0, 0, 'C', $TempFill);
		}
		$pdf->SetTextColor(0, 0, 0);
		$pdf->Cell($w[1], 6, '', 0, 0, 'C', 0);
		$pdf->SetFont($pdf->FontStd,'B', 18);
		$pdf->SetXY($pdf->GetX(), $pdf->GetY()+4.5);
		$pdf->Cell($w[2], 9, $Individual['EnFullName'], 0, 0, 'L', 0);
		if ($Individual['Minima'] > $Individual['QuScore']) {
			$pdf->SetTextColor(200, 0, 0);
		}
		$pdf->Cell($w[3], 9, $Individual['QuScore'], 0, 0, 'R', 0);
		$pdf->SetFont($pdf->FontStd,'', 12);
		$pdf->Cell($w[4], 9, $Individual['Minima'], 0, 0, 'R', 0);
		$pdf->Ln(8);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->Cell($w[0]+$w[1], 9, '', 0, 0, 'L', 0);
		$pdf->SetFont($pdf->FontStd,'', 14);
		$pdf->Cell($w[2], 9, $Individual['CoName'], 0, 0, 'L', 0);
		$pdf->Ln(12.5);
		$pdf->Line($pdf->getSideMargin(), $pdf->GetY(), $pdf->GetPageWidth() - $pdf->getSideMargin(), $pdf->GetY(), '');
		$pdf->SetXY($pdf->GetX(), $pdf->GetY()+0.1);
		
		$OldEvent = $Individual['IndEvent'];
		
	}
	
	$pdf->Output();
}

function CreateDiplomaPDFs(bool $templatesOnly=false, bool $namesOnly = false) {
	global $IndividualScores;
	
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
	$pdf->SetTitle('Individual Diplomas');
	$pdf->SetSubject('Individual Diplomas');
	$pdf->SetKeywords('SAA, Individual, Diplomas, I@NSEO');

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
	foreach ($IndividualScores as $Individual) {
		
		// skip if minima is met
		if ($Individual['Minima'] <= $Individual['QuScore']) {continue;}
		
		// start each diploma on a new page
		// add page
		$pdf->AddPage();

		if($templatesOnly) {
			// Only print one single template page
			break;
		}

		// Print event title
		//$pdf->SetFont($pdf->FontStd,'', 24);
		//$pdf->Cell(0, 6, 'Schweizermeisterschaft' , 0, 1, 'C', 0);
		//$pdf->Ln(30);

		$pdf->SetXY($pdf->GetX(), 125);
		$pdf->SetFont($pdf->FontStd,'', 28);
		
		// event
		$pdf->Cell(0, 6, $Individual['EvEventName'] , 0, 1, 'C', 0);
		$pdf->Ln(5);
			
		// rank
		$pdf->Cell(0, 6, $Individual['QuClRank'] . '. Rang', 0, 1, 'C', 0);
		$pdf->Ln(5);
		
		// full name
		$pdf->SetFont($pdf->FontStd,'B', 30);
		$pdf->Cell(0, 6, $Individual['EnFullName'], 0, 1, 'C', 0);
		$pdf->Ln(5);
		//$pdf->SetXY($pdf->GetX(), $pdf->GetY()+12);
		
		// team mebers
		$pdf->SetFont($pdf->FontStd,'', 26);
		$pdf->Cell(0, 6, $Individual['CoName'], 0, 0, 'C', 0);

		// Date value
		$pdf->SetFont('helvetica', '', 11);
		$pdf->Text(21, 250, strftime('%d.%m.%Y', time()));

	}	

	$pdf->Output();
}
?>