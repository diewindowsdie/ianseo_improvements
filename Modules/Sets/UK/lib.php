<?php

/*

STANDARD DEFINITIONS (Target TournaOpents)

*/

// these go here as it is a "global" definition, used or not
$tourCollation = '';
$tourDetIocCode = 'UK';

if($_POST['TourReset'] =='on'){
$UpdClasses = "UPDATE entries SET EnClass = REPLACE(EnClass, 'M', 'O'),EnAgeClass = REPLACE(EnAgeClass, 'M', 'O') WHERE EnTournament = $TourId AND (EnClass LIKE '%M%' OR EnAgeClass LIKE '%M%')";
$run = safe_w_SQL($UpdClasses);
}

if(empty($SubRule)) $SubRule='1';

function CreateStandardDivisions($TourId, $Type, $subRuleName) {
	$i=1;
    $optionDivs = array(
        'R'=>'Recurve',
        'C'=>'Compound',
        'B'=>'Barebow',
        'L'=>'Longbow',
    );
    if ($Type == 21) {
        $optionDivs = array('C' => 'Compound','R' => 'Recurve');
    } else if (($Type!=40) && ($subRuleName == 'SetUkNationals') && ($Type !='FIELD') && ($Type != '3D')) {
        $optionDivs = array('R'=>'Recurve','C'=>'Compound','L'=>'Longbow','B'=>'Barebow');
    }
    else if (($Type!=40) && ($subRuleName == 'SetUK_Field') && $Type =='FIELD') {
        $optionDivs = array(
            'R'=>'Recurve',
            'C'=>'Compound',
            'L'=>'Longbow',
            'B'=>'Barebow',
            'F'=>'Flatbow',
            'A'=>'Asiatic',
            'CL'=>'Compound Limited',
            'CB'=>'Compound Barebow',
            'T'=>'Traditional',
        );
    }
    else if (($Type!=40) && ($SubRule == 'SetAllClass') && $Type =='3D') {
        $optionDivs = array(
            'R' => 'Recurve',
            'C' => 'Compound',
            'L' => 'Longbow',
            'B' => 'Barebow',
            'F' => 'Flatbow',
            'A' => 'Asiatic',
            'CL' => 'Compound Limited',
            'CB' => 'Compound Barebow',
            'T' => 'Traditional',
        );
    }

    foreach ($optionDivs as $k => $v){
        CreateDivision($TourId, $i++, $k, $v);
    }

}

function CreateStandardClasses($TourId, $subRuleName,$TourType) {
    $i=1;
	switch($TourType) {
        case 40:
            CreateClass($TourId, $i++, 21, 49, -1, 'O', 'O', 'Open');
            CreateClass($TourId, $i++, 21, 49, 1, 'W', 'W', 'Women');
            CreateClass($TourId, $i++, 18, 20, -1, 'U21O', 'U21O,O', 'Open U21');
            CreateClass($TourId, $i++, 18, 20, 1, 'U21W', 'U21W,W', 'Women U21');
            CreateClass($TourId, $i++, 16, 17, -1, 'U18O', 'U18O,U21O,O', 'Open U18');
            CreateClass($TourId, $i++, 16, 17, 1, 'U18W', 'U21W,U18W,W', 'Women U18');
            CreateClass($TourId, $i++, 14, 15, -1, 'U16O', 'U21O,U18O,U16O,O', 'Open U16');
            CreateClass($TourId, $i++, 14, 15, 1, 'U16W', 'U21W,U18W,U16W,W', 'Women U16');
            CreateClass($TourId, $i++, 14, 14, -1, 'U15O', 'U21O,U18O,U16O,U15O,O', 'Open U15');
            CreateClass($TourId, $i++, 14, 14, 1, 'U15W', 'U21W,U18W,U16W,U15W,W', 'Women U15');
            CreateClass($TourId, $i++, 12, 13, -1, 'U14O', 'U21O,U18O,U16O,U15O,U14O,O', 'Open U14');
            CreateClass($TourId, $i++, 12, 13, 1, 'U14W', 'U21W,U18W,U16W,U15W,U14W,W', 'Women U14');
            CreateClass($TourId, $i++, 1, 12, -1, 'U12O', 'U21O,U18O,U16O,U15O,U14O,U12O,O', 'Open U12');
            CreateClass($TourId, $i++, 1, 12, 1, 'U12W', 'U21W,U18W,U16W,U15W,U14W,U12W,W', 'Women U12');
            CreateClass($TourId, $i++, 50, 110, -1, '50O', '50O,O', '50+ Open');
            CreateClass($TourId, $i++, 50, 110, 1, '50W', '50W,W', '50+ Women');
            break;
        case 10:
        case 12:
        default:
		if (empty($subRuleName)) {
    		$subRuleName = 'SetUK_Metric';
			}
            switch ($subRuleName) {
                case 'SetUkNationals': // National Championships
                    CreateClass($TourId, $i++, 1, 99, -1, 'O', 'O', 'Open');
                    CreateClass($TourId, $i++, 1, 99, 1, 'W', 'W', 'Women');
                    break;
                case 'SetUkJunNationals': // Junior National Championships
                    CreateClass($TourId, $i++, 18, 20, -1, 'U21O', 'U21O', 'Open U21');
                    CreateClass($TourId, $i++, 18, 20, 1, 'U21W', 'U21W', 'Women U21');
                    CreateClass($TourId, $i++, 16, 17, -1, 'U18O', 'U18O', 'Open U18');
                    CreateClass($TourId, $i++, 16, 17, 1, 'U18W', 'U18W', 'Women U18');
                    CreateClass($TourId, $i++, 14, 15, -1, 'U16O', 'U16O', 'Open U16');
                    CreateClass($TourId, $i++, 14, 15, 1, 'U16W', 'U16W', 'Women U16');
                    CreateClass($TourId, $i++, 14, 14, -1, 'U15O', 'U15O', 'Open U15');
                    CreateClass($TourId, $i++, 14, 14, 1, 'U15W', 'U15W', 'Women U15');
                    CreateClass($TourId, $i++, 12, 13, -1, 'U14O', 'U14O', 'Open U14');
                    CreateClass($TourId, $i++, 12, 13, 1, 'U14W', 'U14W', 'Women U14');
                    CreateClass($TourId, $i++, 1, 12, -1, 'U12O', 'U12O', 'Open U12');
                    CreateClass($TourId, $i++, 1, 12, 1, 'U12W', 'U12W', 'Women U12');
                    break;
                case 'SetUK_Metric':
                    CreateClass($TourId, $i++, 21, 49, -1, 'O', ',O', 'Open');
                    CreateClass($TourId, $i++, 21, 49, 1, 'W', 'W', 'Women');
                    CreateClass($TourId, $i++, 18, 20, -1, 'U21O', 'U21O,O', 'Open U21');
                    CreateClass($TourId, $i++, 18, 20, 1, 'U21W', 'U21W,W', 'Women U21');
                    CreateClass($TourId, $i++, 16, 17, -1, 'U18O', 'U21O,U18O,O', 'Open U18');
                    CreateClass($TourId, $i++, 16, 17, 1, 'U18W', 'U21W,U18W,W', 'Women U18');
                    CreateClass($TourId, $i++, 14, 15, -1, 'U16O', 'U21O,U18O,U16O,O', 'Open U16');
                    CreateClass($TourId, $i++, 14, 15, 1, 'U16W', 'U21W,U18W,U16W,W', 'Women U16');
                    CreateClass($TourId, $i++, 14, 14, -1, 'U15O', 'U21O,U18O,U16O,U15O,O', 'Open U15');
                    CreateClass($TourId, $i++, 14, 14, 1, 'U15W', 'U21W,U18W,U16W,U15W,W', 'Women U15');
                    CreateClass($TourId, $i++, 12, 13, -1, 'U14O', 'U21O,U18O,U16O,U15O,U14O,O', 'Open U14');
                    CreateClass($TourId, $i++, 12, 13, 1, 'U14W', 'U21W,U18W,U16W,U15W,U14W,W', 'Women U14');
                    CreateClass($TourId, $i++, 1, 12, -1, 'U12O', 'U21O,U18O,U16O,U15O,U14O,U12O,O', 'Open U12');
                    CreateClass($TourId, $i++, 1, 12, 1, 'U12W', 'U21W,U18W,U16W,U15W,U14W,U12W,W', 'Women U12');
                    CreateClass($TourId, $i++, 50, 110, -1, '50O', '50O,O', '50+ Open');
                    CreateClass($TourId, $i++, 50, 110, 1, '50W', '50W,W', '50+ Women');
                    break;

            }
            break;
	}

}


function CreateStandardEvents($TourId, $subRuleName, $Outdoor=true,$TourType) {
	$TargetR=($Outdoor?5:2);
	$TargetC=($Outdoor?9:4);
	$SetC=($Outdoor?0:1);
	switch($TourType) {
        case 40:
            switch ($subRuleName) {
                case 'SetUK_YHB':
                    $M = "York";
                    $W = "Hereford";
                    $B1 = "Bristol 1";
                    $B2 = "Bristol 2";
                    $B3 = "Bristol 3";
                    $B4 = "Bristol 4";
                    $B5 = "Bristol 5";
                    break;
                case 'SetUK_WINDS':
                    $M = "St George";
                    $W = "Albion";
                    $B1 = "Albion";
                    $B2 = "Windsor";
                    $B3 = "Windsor 50";
                    $B4 = "Windsor 40";
                    $B5 = "Windsor 30";
                    break;
                case 'SetUK_AME':
                    $M = "American";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 'SetUK_NATS':
                    $M = "New National";
                    $W = "Long National";
                    $B1 = "Long National";
                    $B2 = "National";
                    $B3 = "National 50";
                    $B4 = "National 40";
                    $B5 = "National 30";
                    break;
                case 'SetUK_WEST':
                    $M = "New Western";
                    $W = "Long Western";
                    $B1 = "Long Western";
                    $B2 = "Western";
                    $B3 = "Western 50";
                    $B4 = "Western 40";
                    $B5 = "Western 30";
                    break;
                case 'SetUK_WARS':
                    $M = "New Warwick";
                    $W = "Long Warwick";
                    $B1 = "Long Warwick";
                    $B2 = "Warwick";
                    $B3 = "Warwick 50";
                    $B4 = "Warwick 40";
                    $B5 = "Warwick 30";
                    break;
                case 'SetUK_STNIC':
                    $M = "St Nicholas";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 'SetUK_ONT':
                    $M = "ontarget";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 'SetUK_SHMET':
                    $M = "Short Metric";
                    $W = "Short Metric";
                    $B1 = "Short Metric 1";
                    $B2 = "Short Metric 2";
                    $B3 = "Short Metric 3";
                    $B4 = "Short Metric 4";
                    $B5 = "Short Metric 5";
                    break;
                case 'SetUK_LGMET':
                    $M = "Long Metric";
                    $W = "Long Metric";
                    $B1 = "Long Metric 1";
                    $B2 = "Long Metric 2";
                    $B3 = "Long Metric 3";
                    $B4 = "Long Metric 4";
                    $B5 = "Long Metric 5";
                    break;
                case 'SetUK_WRCS':
                    $M = "Worcester";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 'SetUK_BR1':
                    $M = "Bray 1";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 'SetUK_BR2':
                    $M = "Bray 2";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 'SetUK_STFD':
                    $M = "Stafford";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 'SetUK_PMTH':
                    $M = "Portsmouth";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
            }
            $i = 1;
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RO', $M . ' - Recurve Open', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CO', $M . '  - Compound Open', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LO', $M . ' - Longbow Open', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BO', $M . ' - Barebow Open', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RW', $W . ' - Recurve Women', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CW', $W . ' - Compound Women', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LW', $W . ' - Longbow Women', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BW', $W . ' - Barebow Women', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU21O', $M . ' - Recurve Open Under 21', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU21O', $M . ' - Compound Open Under 21', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU21O', $M . ' - Longbow Open Under 21', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU21O', $M . ' - Barebow Open Under 21', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU21W', $W . ' - Recurve Women Under 21', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU21W', $W . ' - Compound Women Under 21', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU21W', $W . ' - Longbow Women Under 21', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU21W', $W . ' - Barebow Women Under 21', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU18O', $B1 . ' - Recurve Open Under 18', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU18O', $B1 . ' - Compound Open Under 18', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU18O', $B1 . ' - Longbow Open Under 18', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU18O', $B1 . ' - Barebow Open Under 18', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU18W', $B2 . ' - Recurve Women Under 18', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU18W', $B2 . ' - Compound Women Under 18', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU18W', $B2 . ' - Longbow Women Under 18', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU18W', $B2 . ' - Barebow Women Under 18', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU16O', $B2 . ' - Recurve Open Under 16', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU16O', $B2 . ' - Compound Open Under 16', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU16O', $B2 . ' - Longbow Open Under 16', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU16O', $B2 . ' - Barebow Open Under 16', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU16W', $B3 . ' - Recurve Women Under 16', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU16W', $B3 . ' - Compound Women Under 16', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU16W', $B3 . ' - Longbow Women Under 16', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU16W', $B3 . ' - Barebow Women Under 16', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU15O', $B3 . ' - Recurve Open Under 15', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU15O', $B3 . ' - Compound Open Under 15', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU15O', $B3 . ' - Longbow Open Under 15', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU15O', $B3 . ' - Barebow Open Under 15', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU15W', $B3 . ' - Recurve Women Under 15', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU15W', $B3 . ' - Compound Women Under 15', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU15W', $B3 . ' - Longbow Women Under 15', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU15W', $B3 . ' - Barebow Women Under 15', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU14O', $B4 . ' - Recurve Open Under 14', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU14O', $B4 . ' - Compound Open Under 14', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU14O', $B4 . ' - Longbow Open Under 14', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU14O', $B4 . ' - Barebow Open Under 14', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU14W', $B4 . ' - Recurve Women Under 14', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU14W', $B4 . ' - Compound Women Under 14', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU14W', $B4 . ' - Longbow Women Under 14', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU14W', $B4 . ' - Barebow Women Under 14', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU12O', $B5 . ' - Recurve Open Under 12', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU12O', $B5 . ' - Compound Open Under 12', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU12O', $B5 . ' - Longbow Open Under 12', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU12O', $B5 . ' - Barebow Open Under 12', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU12W', $B5 . ' - Recurve Women Under 12', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU12W', $B5 . ' - Compound Women Under 12', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU12W', $B5 . ' - Longbow Women Under 12', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU12W', $B5 . ' - Barebow Women Under 12', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'R50O', $W . ' - Recurve Open 50+', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'C50O', $W . ' - Compound Open 50+', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'L50O', $W . ' - Longbow Open 50+', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'B50O', $W . ' - Barebow Open 50+', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'R50W', $B2 . ' - Recurve Women 50+', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'C50W', $B2 . ' - Compound Women 50+', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'L50W', $B2 . ' - Longbow Women 50+', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'B50W', $B2 . ' - Barebow Women 50+', 1, 240);
            break;

        default:
		if (empty($subRuleName)) {
    		$subRuleName = 'SetUK_Metric';
			}
            switch ($subRuleName) {
                case 'SetUKNationals':// National Championships
                    $i = 1;
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'RO', 'Recurve Open', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'RW', 'Recurve Women', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetC, 5, 3, 1, 5, 3, 1, 'CO', 'Compound Open', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetC, 5, 3, 1, 5, 3, 1, 'CW', 'Compound Women', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'LO', 'Longbow Open', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'LW', 'Longbow Women', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'BO', 'Barebow Open', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'BW', 'Barebow Women', 1, 240);
                    break;
                case 'SetUKJunNationals':
                case 'SetUK_Metric':
                    if ($TourType == 1) {
                        $appAdult='1440';
                        $app1 = 'Metric 1';
                        $app2 = 'Metric 2';
                        $app3 = 'Metric 3';
                        $app4 = 'Metric 4';
                        $app5 = 'Metric 5';
                    }
                    elseif ($TourType == 2) {
                        $appAdult = 'Double 1440';
                        $app1 = 'Double Metric 1';
                        $app2 = 'Double Metric 2';
                        $app3 = 'Double Metric 3';
                        $app4 = 'Double Metric 4';
                        $app5 = 'Double Metric 5';
                    }
                    elseif ($TourType == 5) {
                        $appAdult = '900-70';
                        $app1 = 'WA 900';
                        $app2 = 'WA 900';
                        $app3 = '900-50';
                        $app4 = '900-40';
                        $app5 = '900-30';
                    }

                    else {
                        $app1 = '';
                        $app2 = '';
                        $app3 = '';
                        $app4 = '';
                        $app5 = '';
                        $appAdult = '';

                    }

                    $i=1;
                    if($subRuleName=='SetUkJunNationals'){
                        //if is Junior Champs, do not add Senior Categories
                    }
                    else{
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RO', $appAdult.' - Recurve Open', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RW', $appAdult.' - Recurve Women', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CO', $appAdult.' - Compound Open', $SetC, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CW', $appAdult.' - Compound Women', $SetC, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LO', $appAdult.' - Longbow Open', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LW', $appAdult.' - Longbow Women', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BO', $appAdult.' - Barebow Open', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BW', $appAdult.' - Barebow Women', 1, 240);
                    }
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU21O', $appAdult.' - Recurve Open Under 21', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU21O', $appAdult.' - Compound Open Under 21', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU21O', $appAdult.' - Longbow Open Under 21', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU21O', $appAdult.' - Barebow Open Under 21', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU21W', $appAdult.' - Recurve Women Under 21', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU21W', $appAdult.' - Compound Women Under 21', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU21W', $appAdult.' - Longbow Women Under 21', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU21W', $appAdult.' - Barebow Women Under 21', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU18O', $app1.' - Recurve Open Under 18', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU18O', $app1.' - Compound Open Under 18', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU18O', $app1.' - Longbow Open Under 18', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU18O', $app1.' - Barebow Open Under 18', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU18W', $app2.' - Recurve Women Under 18', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU18W', $app2.' - Compound Women Under 18', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU18W', $app2.' - Longbow Women Under 18', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU18W', $app2.' - Barebow Women Under 18', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU16O', $app2.' - Recurve Open Under 16', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU16O', $app2.' - Compound Open Under 16', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU16O', $app2.' - Longbow Open Under 16', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU16O', $app2.' - Barebow Open Under 16', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU16W', $app3.' - Recurve Women Under 16', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU16W', $app3.' - Compound Women Under 16', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU16W', $app3.' - Longbow Women Under 16', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU16W', $app3.' - Barebow Women Under 16', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU15O', $app3.' - Recurve Open Under 15', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU15O', $app3.' - Compound Open Under 15', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU15O', $app3.' - Longbow Open Under 15', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU15O', $app3.' - Barebow Open Under 15', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU15W', $app3.' - Recurve Women Under 15', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU15W', $app3.' - Compound Women Under 15', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU15W', $app3.' - Longbow Women Under 15', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU15W', $app3.' - Barebow Women Under 15', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU14O', $app4. ' - Recurve Open Under 14', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU14O', $app4. ' - Compound Open Under 14', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU14O', $app4. ' - Longbow Open Under 14', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU14O', $app4. ' - Barebow Open Under 14', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU14W', $app4. ' - Recurve Women Under 14', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU14W', $app4. ' - Compound Women Under 14', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU14W', $app4. ' - Longbow Women Under 14', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU14W', $app4. ' - Barebow Women Under 14', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU12O', $app5. ' - Recurve Open Under 12', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU12O', $app5. ' - Compound Open Under 12', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU12O', $app5. ' - Longbow Open Under 12', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU12O', $app5. ' - Barebow Open Under 12', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU12W', $app5. ' - Recurve Women Under 12', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU12W', $app5. ' - Compound Women Under 12', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU12W', $app5. ' - Longbow Women Under 12', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU12W', $app5. ' - Barebow Women Under 12', 1, 240);
                    if($subRuleName=='SetUkJunNationals'){
                        //if is Junior Champs, do not add 50+
                    }
                    else{
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'R50O', $appAdult . ' - Recurve Open 50+', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'C50O', $appAdult . ' - Compound Open 50+', $SetC, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'L50O', $appAdult . ' - Longbow Open 50+', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'B50O', $appAdult . ' - Barebow Open 50+', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'R50W', $appAdult . ' - Recurve Women 50+', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'C50W', $appAdult . ' - Compound Women 50+', $SetC, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'L50W', $appAdult . ' - Longbow Women 50+', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'B50W', $appAdult . ' - Barebow Women 50+', 1, 240);
                    }
                    break;

            }
            break;

            }
}

function InsertStandardEvents($TourId, $subRuleName,$TourType){

    switch ($TourType) {
        case 40:
            EventInserts($TourId);
            break;
        default:
            switch($subRuleName){
                case 'SetUkNationals':
                case 'SetUkJunNationals':
                case 'SetUK_Metric':
                   EventInserts($TourId);
                break;


            }
    }
}

function EventInserts($TourId){
    foreach (array('R' => 'R', 'C' => 'C', 'B' => 'B', 'L' => 'L') as $kDiv => $vDiv) {
        $clsTmpArr = array('W', 'U21W', 'U18W', 'U16W', 'U15W','U14W','U12W','50W');

        foreach ($clsTmpArr as $kClass => $vClass) {
            InsertClassEvent($TourId, 0, 1, $vDiv . $vClass, $kDiv, $vClass);
        }
        $clsTmpArr = array('O', 'U21O', 'U18O', 'U16O', 'U15O', 'U14O','U12O','50O');
        foreach ($clsTmpArr as $kClass => $vClass) {
            InsertClassEvent($TourId, 0, 1, $vDiv . $vClass, $kDiv, $vClass);

        }
    }

}

/*

FIELD DEFINITIONS (Target TournaOpents)

*/


require_once(dirname(__FILE__).'/lib-Field.php');
/*

3D DEFINITIONS (Target TournaOpents)

*/
require_once(dirname(__FILE__).'/lib-3D.php');

//FIELD DEFINITIONS (Target TournaOpents)

?>
