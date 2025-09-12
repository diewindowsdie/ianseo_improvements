<?php

/*

STANDARD DEFINITIONS (Target Tournaments)

*/

// these go here as it is a "global" definition, used or not
$tourCollation = '';
$tourDetIocCode = 'UK';
if(empty($SubRule)) $SubRule='1';

function CreateStandardDivisions($TourId, $Type, $SubRule) {
	$i=1;
    $optionDivs = array(
        'R'=>'Recurve',
        'C'=>'Compound',
        'B'=>'Barebow',
        'L'=>'Longbow',
    );
    if ($Type == 21) {
        $optionDivs = array('C' => 'Compound','R' => 'Recurve');
    } else if (($Type!=40) && ($SubRule == 1) && ($Type !='FIELD') && ($Type != '3D')) {
        $optionDivs = array('R'=>'Recurve','C'=>'Compound','L'=>'Longbow','B'=>'Barebow');
    }
    else if (($Type!=40) && ($SubRule == 1) && $Type =='FIELD') {
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
    else if (($Type!=40) && ($SubRule == 1) && $Type =='3D') {
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

function CreateStandardClasses($TourId, $SubRule,$TourType) {
    $i=1;
	switch($TourType) {
        case 40:
            CreateClass($TourId, $i++, 21, 110, 0, 'M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men');
            CreateClass($TourId, $i++, 21, 110, 1, 'W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women');
            CreateClass($TourId, $i++, 18, 20, 0, 'U21M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U21');
            CreateClass($TourId, $i++, 18, 20, 1, 'U21W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U21');
            CreateClass($TourId, $i++, 16, 17, 0, 'U18M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U18');
            CreateClass($TourId, $i++, 16, 17, 1, 'U18W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U18');
            CreateClass($TourId, $i++, 14, 15, 0, 'U16M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U16');
            CreateClass($TourId, $i++, 14, 15, 1, 'U16W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U16');
            CreateClass($TourId, $i++, 14, 14, 0, 'U15M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U15');
            CreateClass($TourId, $i++, 14, 14, 1, 'U15W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U15');
            CreateClass($TourId, $i++, 12, 13, 0, 'U14M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U14');
            CreateClass($TourId, $i++, 12, 13, 1, 'U14W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U14');
            CreateClass($TourId, $i++, 1, 12, 0, 'U12M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U12');
            CreateClass($TourId, $i++, 1, 12, 1, 'U12W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U12');
            CreateClass($TourId, $i++, 50, 110, 0, '50M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', '50+ Men');
            CreateClass($TourId, $i++, 50, 110, 1, '50W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', '50+ Women');
            break;
        case 10:
        case 12:
        default:
            switch ($SubRule) {
                case '1': // National Championships
                    CreateClass($TourId, $i++, 1, 99, 0, 'M', 'M', 'Men');
                    CreateClass($TourId, $i++, 1, 99, 1, 'W', 'W', 'Women');
                    break;
                case '2': // Junior National Championships
                    CreateClass($TourId, $i++, 18, 20, 0, 'U21M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U21');
                    CreateClass($TourId, $i++, 18, 20, 1, 'U21W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U21');
                    CreateClass($TourId, $i++, 16, 17, 0, 'U18M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U18');
                    CreateClass($TourId, $i++, 16, 17, 1, 'U18W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U18');
                    CreateClass($TourId, $i++, 14, 15, 0, 'U16M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U16');
                    CreateClass($TourId, $i++, 14, 15, 1, 'U16W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U16');
                    CreateClass($TourId, $i++, 14, 14, 0, 'U15M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U15');
                    CreateClass($TourId, $i++, 14, 14, 1, 'U15W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U15');
                    CreateClass($TourId, $i++, 12, 13, 0, 'U14M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U14');
                    CreateClass($TourId, $i++, 12, 13, 1, 'U14W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U14');
                    CreateClass($TourId, $i++, 1, 12, 0, 'U12M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U12');
                    CreateClass($TourId, $i++, 1, 12, 1, 'U12W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U12');
                    break;
                case 3:
                    CreateClass($TourId, $i++, 21, 110, 0, 'M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men');
                    CreateClass($TourId, $i++, 21, 110, 1, 'W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women');
                    CreateClass($TourId, $i++, 18, 20, 0, 'U21M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U21');
                    CreateClass($TourId, $i++, 18, 20, 1, 'U21W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U21');
                    CreateClass($TourId, $i++, 16, 17, 0, 'U18M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U18');
                    CreateClass($TourId, $i++, 16, 17, 1, 'U18W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U18');
                    CreateClass($TourId, $i++, 14, 15, 0, 'U16M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U16');
                    CreateClass($TourId, $i++, 14, 15, 1, 'U16W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U16');
                    CreateClass($TourId, $i++, 14, 14, 0, 'U15M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U15');
                    CreateClass($TourId, $i++, 14, 14, 1, 'U15W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U15');
                    CreateClass($TourId, $i++, 12, 13, 0, 'U14M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U14');
                    CreateClass($TourId, $i++, 12, 13, 1, 'U14W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U14');
                    CreateClass($TourId, $i++, 1, 12, 0, 'U12M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', 'Men U12');
                    CreateClass($TourId, $i++, 1, 12, 1, 'U12W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', 'Women U12');
                    CreateClass($TourId, $i++, 50, 110, 0, '50M', 'U21M,U18M,U16M,U15M,U14M,U12M,50M,M', '50+ Men');
                    CreateClass($TourId, $i++, 50, 110, 1, '50W', 'U21W,U18W,U16W,U15W,U14W,U12W,50W,W', '50+ Women');
                    break;

            }
            break;
	}

}


function CreateStandardEvents($TourId, $SubRule, $Outdoor=true,$TourType) {
	$TargetR=($Outdoor?5:2);
	$TargetC=($Outdoor?9:4);
	$SetC=($Outdoor?0:1);
	switch($TourType) {
        case 40:
            switch ($SubRule) {
                case 1:
                    $M = "York";
                    $W = "Hereford";
                    $B1 = "Bristol 1";
                    $B2 = "Bristol 2";
                    $B3 = "Bristol 3";
                    $B4 = "Bristol 4";
                    $B5 = "Bristol 5";
                    break;
                case 2:
                    $M = "St George";
                    $W = "Albion";
                    $B1 = "Albion";
                    $B2 = "Windsor";
                    $B3 = "Windsor 50";
                    $B4 = "Windsor 40";
                    $B5 = "Windsor 30";
                    break;
                case 3:
                    $M = "American";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 4:
                    $M = "New National";
                    $W = "Long National";
                    $B1 = "Long National";
                    $B2 = "National";
                    $B3 = "National 50";
                    $B4 = "National 40";
                    $B5 = "National 30";
                    break;
                case 5:
                    $M = "New Western";
                    $W = "Long Western";
                    $B1 = "Long Western";
                    $B2 = "Western";
                    $B3 = "Western 50";
                    $B4 = "Western 40";
                    $B5 = "Western 30";
                    break;
                case 6:
                    $M = "New Warwick";
                    $W = "Long Warwick";
                    $B1 = "Long Warwick";
                    $B2 = "Warwick";
                    $B3 = "Warwick 50";
                    $B4 = "Warwick 40";
                    $B5 = "Warwick 30";
                    break;
                case 7:
                    $M = "St Nicholas";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 8:
                    $M = "ontarget";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 9:
                    $M = "Short Metric";
                    $W = "Short Metric";
                    $B1 = "Short Metric 1";
                    $B2 = "Short Metric 2";
                    $B3 = "Short Metric 3";
                    $B4 = "Short Metric 4";
                    $B5 = "Short Metric 5";
                    break;
                case 10:
                    $M = "Long Metric";
                    $W = "Long Metric";
                    $B1 = "Long Metric 1";
                    $B2 = "Long Metric 2";
                    $B3 = "Long Metric 3";
                    $B4 = "Long Metric 4";
                    $B5 = "Long Metric 5";
                    break;
                case 11:
                    $M = "Worcester";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 12:
                    $M = "Bray 1";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 13:
                    $M = "Bray 2";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 14:
                    $M = "Stafford";
                    $W = $M;
                    $B1 = $M;
                    $B2 = $M;
                    $B3 = $M;
                    $B4 = $M;
                    $B5 = $M;
                    break;
                case 15:
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
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RM', $M . ' - Recurve Men', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CM', $M . '  - Compound Men', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LM', $M . ' - Longbow Men', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BM', $M . ' - Barebow Men', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RW', $W . ' - Recurve Women', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CW', $W . ' - Compound Women', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LW', $W . ' - Longbow Women', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BW', $W . ' - Barebow Women', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU21M', $M . ' - Recurve Men Under 21', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU21M', $M . ' - Compound Men Under 21', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU21M', $M . ' - Longbow Men Under 21', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU21M', $M . ' - Barebow Men Under 21', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU21W', $W . ' - Recurve Women Under 21', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU21W', $W . ' - Compound Women Under 21', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU21W', $W . ' - Longbow Women Under 21', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU21W', $W . ' - Barebow Women Under 21', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU18M', $B1 . ' - Recurve Men Under 18', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU18M', $B1 . ' - Compound Men Under 18', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU18M', $B1 . ' - Longbow Men Under 18', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU18M', $B1 . ' - Barebow Men Under 18', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU18W', $B2 . ' - Recurve Women Under 18', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU18W', $B2 . ' - Compound Women Under 18', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU18W', $B2 . ' - Longbow Women Under 18', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU18W', $B2 . ' - Barebow Women Under 18', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU16M', $B2 . ' - Recurve Men Under 16', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU16M', $B2 . ' - Compound Men Under 16', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU16M', $B2 . ' - Longbow Men Under 16', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU16M', $B2 . ' - Barebow Men Under 16', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU16W', $B3 . ' - Recurve Women Under 16', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU16W', $B3 . ' - Compound Women Under 16', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU16W', $B3 . ' - Longbow Women Under 16', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU16W', $B3 . ' - Barebow Women Under 16', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU15M', $B3 . ' - Recurve Men Under 15', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU15M', $B3 . ' - Compound Men Under 15', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU15M', $B3 . ' - Longbow Men Under 15', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU15M', $B3 . ' - Barebow Men Under 15', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU15W', $B3 . ' - Recurve Women Under 15', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU15W', $B3 . ' - Compound Women Under 15', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU15W', $B3 . ' - Longbow Women Under 15', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU15W', $B3 . ' - Barebow Women Under 15', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU14M', $B4 . ' - Recurve Men Under 14', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU14M', $B4 . ' - Compound Men Under 14', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU14M', $B4 . ' - Longbow Men Under 14', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU14M', $B4 . ' - Barebow Men Under 14', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU14W', $B4 . ' - Recurve Women Under 14', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU14W', $B4 . ' - Compound Women Under 14', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU14W', $B4 . ' - Longbow Women Under 14', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU14W', $B4 . ' - Barebow Women Under 14', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU12M', $B5 . ' - Recurve Men Under 12', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU12M', $B5 . ' - Compound Men Under 12', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU12M', $B5 . ' - Longbow Men Under 12', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU12M', $B5 . ' - Barebow Men Under 12', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU12W', $B5 . ' - Recurve Women Under 12', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU12W', $B5 . ' - Compound Women Under 12', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU12W', $B5 . ' - Longbow Women Under 12', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU12W', $B5 . ' - Barebow Women Under 12', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'R50M', $W . ' - Recurve Men 50+', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'C50M', $W . ' - Compound Men 50+', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'L50M', $W . ' - Longbow Men 50+', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'B50M', $W . ' - Barebow Men 50+', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'R50W', $B2 . ' - Recurve Women 50+', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'C50W', $B2 . ' - Compound Women 50+', $SetC, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'L50W', $B2 . ' - Longbow Women 50+', 1, 240);
            CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'B50W', $B2 . ' - Barebow Women 50+', 1, 240);
            break;

        default:
            switch ($SubRule) {
                case 1:// National Championships
                    $i = 1;
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'RM', 'Recurve Men', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'RW', 'Recurve Women', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetC, 5, 3, 1, 5, 3, 1, 'CM', 'Compound Men', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetC, 5, 3, 1, 5, 3, 1, 'CW', 'Compound Women', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'LM', 'Longbow Men', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'LW', 'Longbow Women', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'BM', 'Barebow Men', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'BW', 'Barebow Women', 1, 240);
                    break;
                case 2:
                case 3:
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
                    if($SubRule==2){
                        //if is Junior Champs, do not add Senior Categories
                    }
                    else{
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RM', $appAdult.' - Recurve Men', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RW', $appAdult.' - Recurve Women', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CM', $appAdult.' - Compound Men', $SetC, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CW', $appAdult.' - Compound Women', $SetC, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LM', $appAdult.' - Longbow Men', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LW', $appAdult.' - Longbow Women', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BM', $appAdult.' - Barebow Men', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BW', $appAdult.' - Barebow Women', 1, 240);
                    }
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU21M', $appAdult.' - Recurve Men Under 21', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU21M', $appAdult.' - Compound Men Under 21', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU21M', $appAdult.' - Longbow Men Under 21', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU21M', $appAdult.' - Barebow Men Under 21', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU21W', $appAdult.' - Recurve Women Under 21', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU21W', $appAdult.' - Compound Women Under 21', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU21W', $appAdult.' - Longbow Women Under 21', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU21W', $appAdult.' - Barebow Women Under 21', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU18M', $app1.' - Recurve Men Under 18', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU18M', $app1.' - Compound Men Under 18', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU18M', $app1.' - Longbow Men Under 18', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU18M', $app1.' - Barebow Men Under 18', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU18W', $app2.' - Recurve Women Under 18', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU18W', $app2.' - Compound Women Under 18', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU18W', $app2.' - Longbow Women Under 18', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU18W', $app2.' - Barebow Women Under 18', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU16M', $app2.' - Recurve Men Under 16', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU16M', $app2.' - Compound Men Under 16', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU16M', $app2.' - Longbow Men Under 16', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU16M', $app2.' - Barebow Men Under 16', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU16W', $app3.' - Recurve Women Under 16', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU16W', $app3.' - Compound Women Under 16', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU16W', $app3.' - Longbow Women Under 16', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU16W', $app3.' - Barebow Women Under 16', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU15M', $app3.' - Recurve Men Under 15', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU15M', $app3.' - Compound Men Under 15', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU15M', $app3.' - Longbow Men Under 15', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU15M', $app3.' - Barebow Men Under 15', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU15W', $app3.' - Recurve Women Under 15', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU15W', $app3.' - Compound Women Under 15', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU15W', $app3.' - Longbow Women Under 15', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU15W', $app3.' - Barebow Women Under 15', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU14M', $app4. ' - Recurve Men Under 14', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU14M', $app4. ' - Compound Men Under 14', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU14M', $app4. ' - Longbow Men Under 14', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU14M', $app4. ' - Barebow Men Under 14', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU14W', $app4. ' - Recurve Women Under 14', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU14W', $app4. ' - Compound Women Under 14', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU14W', $app4. ' - Longbow Women Under 14', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU14W', $app4. ' - Barebow Women Under 14', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU12M', $app5. ' - Recurve Men Under 12', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU12M', $app5. ' - Compound Men Under 12', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU12M', $app5. ' - Longbow Men Under 12', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU12M', $app5. ' - Barebow Men Under 12', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RU12W', $app5. ' - Recurve Women Under 12', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CU12W', $app5. ' - Compound Women Under 12', $SetC, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'LU12W', $app5. ' - Longbow Women Under 12', 1, 240);
                    CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'BU12W', $app5. ' - Barebow Women Under 12', 1, 240);
                    if($SubRule==2){
                        //if is Junior Champs, do not add 50+
                    }
                    else{
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'R50M', $appAdult . ' - Recurve Men 50+', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'C50M', $appAdult . ' - Compound Men 50+', $SetC, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'L50M', $appAdult . ' - Longbow Men 50+', 1, 240);
                        CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'B50M', $appAdult . ' - Barebow Men 50+', 1, 240);
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

function InsertStandardEvents($TourId, $SubRule,$TourType){

    switch ($TourType) {
        case 40:
            EventInserts($TourId);
            break;
        default:
            switch($SubRule){
                case 1:
                case 2:
                case 3:
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
        $clsTmpArr = array('M', 'U21M', 'U18M', 'U16M', 'U15M', 'U14M','U12M','50M');
        foreach ($clsTmpArr as $kClass => $vClass) {
            InsertClassEvent($TourId, 0, 1, $vDiv . $vClass, $kDiv, $vClass);

        }
    }

}

/*

FIELD DEFINITIONS (Target Tournaments)

*/


require_once(dirname(__FILE__).'/lib-Field.php');
/*

3D DEFINITIONS (Target Tournaments)

*/
require_once(dirname(__FILE__).'/lib-3D.php');

//FIELD DEFINITIONS (Target Tournaments)

?>