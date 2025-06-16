<?php

// Extend the TCPDF class to create custom Header and Footer
class SaaDiplomaPdf extends TCPDF {
    //Page header
    public function Header() {
        // Logo
        $this->Image('swissarchery-header.png', 10, 10, 120, '', 'PNG', 2);
		
		// Title
		$discipline = '';
		$eventYear = strftime('%Y', time());
		switch($_SESSION['TourType'])
		{
			case 3: $discipline = 'Outdoor'; break;
			case 6: $discipline = 'Indoor'; break;
			case 9: $discipline = 'Field'; break;
			case 11: $discipline = '3D'; break;
			default: $discipline = '';
		}
		$titleFR = sprintf('Championnat Suisse %s %d', $discipline, $eventYear );
		$titleDE = sprintf('%s Schweizermeisterschaft %d', $discipline, $eventYear );
		$titleIT = sprintf('Campionato Svizzero %s %d', $discipline, $eventYear );
	
        $this->SetXY(10, 50);
		$this->SetFont('helvetica', 'B', 22);
        $this->setCellPadding(2);
		$this->Cell(0, 0, $titleFR, 0, 2, 'C', 0, '', 0, false, 'T', 'T');
		$this->Cell(0, 0, $titleDE, 0, 2, 'C', 0, '', 0, false, 'T', 'T');
		$this->Cell(0, 0, $titleIT, 0, 2, 'C', 0, '', 0, false, 'T', 'T');
		
		// Watermark Logo
		$this->Image('swissarchery-logo-watermark.png', 55, 110, 100, '', 'PNG', 2);
		
		// Date / Signature
		$this->SetFont('helvetica', '', 11);
		$this->Text(20, 240, "Date / Datum / Data");
		$this->Text(110, 240, "Pour le CC / für den ZV / per il CC");
	    //$this->Text(20, 248, strftime('%d.%m.%Y', time()));

	    $lineStyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'phase' => 10, 'color' => array(0, 0, 0));
		$this->Line(111, 255, 171, 255, $lineStyle);
		
    }

    // Page footer
    public function Footer() {
		
		// Divider Line
		$lineStyle = array('width' => 0.5, 'color' => array(255, 0, 0));
		$this->Line(10, 273, 190, 273, $lineStyle);
		
		$this->Image('swissarchery-association-textlogo.png', 10, 275, 60, '', 'PNG', 2);
		$this->Image('swissolympic-member-logo.jpg', 135, 277, 30, '', 'JPG', 2);
		$this->Image('j-s-logo.png', 169, 276, 8, '', 'PNG', 2);
		$this->Image('worldarchery-logo.jpg', 180, 275, 10, '', 'JPG', 2);
		
		
    }

}