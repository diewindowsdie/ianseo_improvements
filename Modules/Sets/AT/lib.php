<?php

/*

STANDARD DEFINITIONS (Target Tournaments)

*/

/**
 * Modified by helmut.poell@gmx.at
 * 2026-09-07
 */

// these go here as it is a "global" definition, used or not
$tourCollation = '';
$tourDetIocCode = 'AUT';
if(empty($SubRule)) {
    $SubRule='1';
}

function CreateStandardDivisions($TourId, $Type='FITA', $SubRule) {
	$i=1;
//                  TourId  Order Id   Description (other parms (see below) are optional)       
    CreateDivision($TourId, $i++, 'L', 'Langbogen');
    CreateDivision($TourId, $i++, 'T', 'Traditional');
    CreateDivision($TourId, $i++, 'B', 'Blankbogen');
    CreateDivision($TourId, $i++, 'C', 'Compound');
    CreateDivision($TourId, $i++, 'R', 'Recurve');

// Add Para-Divisions in Target    
    if ($Type == 'FITA') {
//      A=IsAthlete (fix 1)
//      BB=RecDivision
//      CC=WaDivision
//      D=IsPara(!)
//                      TourId  Order Id    Desc. A   BB    CC   D
        CreateDivision($TourId, $i++, 'W0', 'W0', 1, 'W0', 'W0', 1);
        CreateDivision($TourId, $i++, 'W1', 'W1', 1, 'W1', 'W1', 1);
        CreateDivision($TourId, $i++, 'PC', 'Para Compound', 1, 'C', 'C', 1);
        CreateDivision($TourId, $i++, 'PR', 'Para Recurve', 1, 'R', 'R', 1);
    }
}

function CreateStandardClasses($TourId, $SubRule, $TourType) {
    $i=1;
    
    if ($TourType==3 or $TourType==6 or $TourType==9 or $TourType==11) {
        CreateClass($TourId, $i++, 1, 7, 1, 'U8W', 'U8W,U10W', 'U8 weiblich');
        CreateClass($TourId, $i++, 1, 7, 0, 'U8M', 'U8M,U10M', 'U8 männlich');
        CreateClass($TourId, $i++, 1, 7, -1, 'U8', 'U8,U10', 'U8');

        CreateClass($TourId, $i++, 8, 9, 1, 'U10W', 'U10W', 'U10 weiblich');
        CreateClass($TourId, $i++, 8, 9, 0, 'U10M', 'U10M', 'U10 männlich');
        CreateClass($TourId, $i++, 8, 9, -1, 'U10', 'U10', 'U10');
    }
    
    CreateClass($TourId, $i++, 10, 12, 1, 'U13W', 'U13W,U15W,U18W,U21W,W', 'U13 weiblich');
    CreateClass($TourId, $i++, 10, 12, 0, 'U13M', 'U13M,U15M,U18M,U21M,M', 'U13 männlich');
    CreateClass($TourId, $i++, 10, 12, -1, 'U13', 'U13,U15', 'U13');

    CreateClass($TourId, $i++, 13, 14, 1, 'U15W', 'U15W,U18W,U21W,W', 'U15 weiblich');
    CreateClass($TourId, $i++, 13, 14, 0, 'U15M', 'U15M,U18M,U21M,M', 'U15 männlich');
    CreateClass($TourId, $i++, 13, 14, -1, 'U15', 'U15', 'U15');

    CreateClass($TourId, $i++, 15, 17, 1, 'U18W', 'U18W,U21W,W', 'U18 weiblich');
    CreateClass($TourId, $i++, 15, 17, 0, 'U18M', 'U18M,U21M,M', 'U18 männlich');
    CreateClass($TourId, $i++, 18, 20, 1, 'U21W', 'U21W,W', 'U21 weiblich');
    CreateClass($TourId, $i++, 18, 20, 0, 'U21M', 'U21M,M', 'U21 männlich');

    CreateClass($TourId, $i++, 50, 64, 1, '50W', '50W,W', '50+ weiblich');
    CreateClass($TourId, $i++, 50, 64, 0, '50M', '50M,M', '50+ männlich');
    CreateClass($TourId, $i++, 65, 99, 1, '65W', '65W,50W,W', '65+ weiblich');
    CreateClass($TourId, $i++, 65, 99, 0, '65M', '65M,50M,M', '65+ männlich');

    CreateClass($TourId, $i++, 21, 49, 1, 'W', 'W', 'Damen');
    CreateClass($TourId, $i++, 21, 49, 0, 'M', 'M', 'Herren');
    
    CreateClass($TourId, $i++, 1, 99, -1, 'G', 'G', 'Gästeklasse');
}

function CreateStandardEvents($TourId, $SubRule, $Outdoor=true) {
	$TargetR=($Outdoor ? TGT_OUT_FULL : TGT_IND_6_big10);
	$TargetC=($Outdoor ? TGT_OUT_5_big10 : TGT_IND_6_small10);
    $TargetB=($Outdoor ? TGT_OUT_FULL : TGT_IND_6_big10);
    $TargetLT=($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10);
	$TargetSizeR=($Outdoor ? 122 : 40);
	$TargetSizeC=($Outdoor ? 80 : 40);
    $TargetSizeB=($Outdoor ? 122 : 40);
    $TargetSizeL=($Outdoor ? 122 : 60);
    $TargetSizeT=($Outdoor ? 122 : 40);
	$DistanceR=($Outdoor ? 70 : 18);
	$DistanceC=($Outdoor ? 50 : 18);
    $DistanceB=($Outdoor ? 50 : 18);
    $DistanceL=($Outdoor ? 30 : 18);
    $DistanceT=($Outdoor ? 40 : 18);
	$FirstPhase = ($Outdoor ? 16 : 16);   // 48 : 16
	$TeamFirstPhase = ($Outdoor ? 12 : 8);

    $i=1;
    CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetLT, 5, 3, 1, 5, 3, 1, 'LW', 'Langbogen Damen', 1, 240, 240, 0, 0, '', '', $TargetSizeL, $DistanceL);
    CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetLT, 5, 3, 1, 5, 3, 1, 'LM', 'Langbogen Herren', 1, 240, 240, 0, 0, '', '', $TargetSizeL, $DistanceL);
    CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetLT, 5, 3, 1, 5, 3, 1, 'TW', 'Traditional Damen', 1, 240, 240, 0, 0, '', '', $TargetSizeT, $DistanceT);
    CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetLT, 5, 3, 1, 5, 3, 1, 'TM', 'Traditional Herren', 1, 240, 240, 0, 0, '', '', $TargetSizeT, $DistanceT);
    CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BW', 'Blankbogen Damen', 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
    CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BM', 'Blankbogen Herren', 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
    CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CW', 'Compound Damen', 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
    CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CM', 'Compound Herren', 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
    CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RW', 'Recurve Damen', 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);
    CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RM', 'Recurve Herren', 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);

    $i=1;
    CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetLT, 4, 6, 3, 4, 6, 3, 'LW',  'Langbogen Damen Teams', 1, 0, 0, 0, 0, '', '', $TargetSizeL, $DistanceL, '', 1, 0, 1, 2,2,1);
    CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetLT, 4, 6, 3, 4, 6, 3, 'LM',  'Langbogen Herren Teams', 1, 0, 0, 0, 0, '', '', $TargetSizeL, $DistanceL, '', 1, 0, 1, 2,2,1);
    CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetLT, 4, 4, 2, 4, 4, 2, 'LX',  'Langbogen Mixed Teams', 1, 0, 0, 0, 0, '', '', $TargetSizeL, $DistanceL, '', 1, 0, 1, 2,3);
    CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetLT, 4, 6, 3, 4, 6, 3, 'TW',  'Traditional Damen Teams', 1, 0, 0, 0, 0, '', '', $TargetSizeT, $DistanceT, '', 1, 0, 1, 2,2,1);
    CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetLT, 4, 6, 3, 4, 6, 3, 'TM',  'Traditional Herren Teams', 1, 0, 0, 0, 0, '', '', $TargetSizeT, $DistanceT, '', 1, 0, 1, 2,2,1);
    CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetLT, 4, 4, 2, 4, 4, 2, 'TX',  'Traditional Mixed Teams', 1, 0, 0, 0, 0, '', '', $TargetSizeT, $DistanceT, '', 1, 0, 1, 2,3);
    CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetB, 4, 6, 3, 4, 6, 3, 'BW',  'Blankbogen Damen Teams', 1, 0, 0, 0, 0, '', '', $TargetSizeB, $DistanceB, '', 1, 0, 1, 2,2,1);
    CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetB, 4, 6, 3, 4, 6, 3, 'BM',  'Blankbogen Herren Teams', 1, 0, 0, 0, 0, '', '', $TargetSizeB, $DistanceB, '', 1, 0, 1, 2,2,1);
    CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetB, 4, 4, 2, 4, 4, 2, 'BX',  'Blankbogen Mixed Teams', 1, 0, 0, 0, 0, '', '', $TargetSizeB, $DistanceB, '', 1, 0, 1, 2,3);
    CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetC, 4, 6, 3, 4, 6, 3, 'CW',  'Compound Damen Teams', 0, 0, 0, 0, 0, '', '', $TargetSizeC, $DistanceC, '', 1, 0, 1, 2,2,1);
    CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetC, 4, 6, 3, 4, 6, 3, 'CM',  'Compound Herren Teams', 0, 0, 0, 0, 0, '', '', $TargetSizeC, $DistanceC, '', 1, 0, 1, 2,2,1);
    CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetC, 4, 4, 2, 4, 4, 2, 'CX',  'Compound Mixed Teams', 0, 0, 0, 0, 0, '', '', $TargetSizeC, $DistanceC, '', 1, 0, 1, 2,3);
    CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetR, 4, 6, 3, 4, 6, 3, 'RW',  'Recurve Damen Teams', 1, 0, 0, 0, 0, '', '', $TargetSizeR, $DistanceR, '', 1, 0, 1, 2,2,1);
    CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetR, 4, 6, 3, 4, 6, 3, 'RM',  'Recurve Herren Teams', 1, 0, 0, 0, 0, '', '', $TargetSizeR, $DistanceR, '', 1, 0, 1, 2,2,1);
    CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetR, 4, 4, 2, 4, 4, 2, 'RX',  'Recurve Mixed Teams', 1, 0, 0, 0, 0, '', '', $TargetSizeR, $DistanceR, '', 1, 0, 1, 2,3);
}

function InsertStandardEvents($TourId, $SubRule, $Outdoor=true) {
    foreach (array('R'=>'R_','C'=>'C_','B'=>'B_','L'=>'L_','T'=>'T_','PC'=>'C_') as $kDiv=>$vDiv) {
        $clsTmpArr = array('W','U21W','U18W','50W','65W');
        if($kDiv == 'R' AND $Outdoor) {
            $clsTmpArr = array('W','U21W');
        } else if($kDiv == 'T') {
            $clsTmpArr = array('W','U21W','50W','65W');
        }
        foreach($clsTmpArr as $kClass=>$vClass) {
            InsertClassEvent($TourId, 0, 1, str_replace('_','W',$vDiv), $kDiv,  $vClass);   // Individuals
            InsertClassEvent($TourId, 1, 3, str_replace('_','W',$vDiv), $kDiv,  $vClass);   // Teams
            InsertClassEvent($TourId, 1, 1, str_replace('_','X',$vDiv), $kDiv,  $vClass);   // Mixed Team (Part one)
        }
        $clsTmpArr = array('M','U21M','U18M','50M','65M');
        if($kDiv == 'R' AND $Outdoor) {
            $clsTmpArr = array('M','U21M');
        } else if($kDiv == 'T') {
            $clsTmpArr = array('M','U21M','50M','65M');
        }
        foreach($clsTmpArr as $kClass=>$vClass) {
            InsertClassEvent($TourId, 0, 1, str_replace('_','M',$vDiv), $kDiv,  $vClass);   // Individuals
            InsertClassEvent($TourId, 1, 3, str_replace('_','M',$vDiv), $kDiv,  $vClass);   // Teams
            InsertClassEvent($TourId, 2, 1, str_replace('_','X',$vDiv), $kDiv,  $vClass);   // Mixed Team (Part two)
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

