<?php
/*

STANDARD THINGS

*/

// these go here as it is a "global" definition, used or not
$tourCollation = 'swedish';
$tourDetIocCode = 'SWE';
if(empty($SubRule)) $SubRule='1';

function CreateStandardDivisions($TourId, $Type=1, $SubRule=0) {
	$i=1;
	CreateDivision($TourId,$i++,'R','Recurve');
	CreateDivision($TourId,$i++,'B','Barebow');
	CreateDivision($TourId,$i++,'C','Compound');
	CreateDivision($TourId,$i++,'L','Longbow');
	CreateDivision($TourId,$i++,'T','Traditional');
}

function CreateStandardClasses($TourId, $SubRule, $Field='', $Type=0) {
	$i=1;
	CreateClass($TourId, $i++,  0,  12, 0, 'U13M', 'U13M,U15M', 'Under 13 Men',1);
	CreateClass($TourId, $i++,  0,  12, 1, 'U13W', 'U13W,U15W,U13M,U15M', 'Under 13 Women',1);
	CreateClass($TourId, $i++, 13,  14, 0, 'U15M', 'U15M,U18M,U21M,M,21', 'Under 15 Men',1);
	CreateClass($TourId, $i++, 13,  14, 1, 'U15W', 'U15W,U18W,U21W,W,U15M,U18M,U21M,M,21', 'Under 15 Women',1);
    CreateClass($TourId, $i++, 15,  17, 0, 'U18M', 'U18M,U21M,M,21', 'Under 18 Men',1);
    CreateClass($TourId, $i++, 15,  17, 1, 'U18W', 'U18W,U21W,W,U18M,U21M,M,21', 'Under 18 Women',1);
    CreateClass($TourId, $i++, 18,  20, 0, 'U21M', 'U21M,M,21', 'Under 21 Men',1);
    CreateClass($TourId, $i++, 18,  20, 1, 'U21W', 'U21W,W,U21M,M,21', 'Under 21 Women',1);

    // RU21 Open only for outdoor target comps
    if (in_array($Type, array(1, 3, 5, 37, 39))) {
        CreateClass($TourId, $i++, 13,  20, -1, 'U21O', 'U21O', 'Under 21 Open (13-20)',1, 'R');
    }

	CreateClass($TourId, $i++, 21,  49, 0, 'M', 'M,21', 'Men',1);
	CreateClass($TourId, $i++, 21,  49, 1, 'W', 'W,M,21', 'Women',1);
    CreateClass($TourId, $i++, 21,  49, -1, '21', '21', '21',1);
	CreateClass($TourId, $i++, 50,  59, 0, '50M', '50M,M,21', '50+ Men',1);
	CreateClass($TourId, $i++, 50,  59, 1, '50W', '50W,W,50M,M,21', '50+ Women',1);
	CreateClass($TourId, $i++, 60, 100, 0, '60M', '60M,50M,M,21', '60+ Men',1);
	CreateClass($TourId, $i++, 60, 100, 1, '60W', '60W,50W,W,60M,50M,M,21', '60+ Women',1);
}

function CreateStandardSubClasses($TourId) {
	$i=1;
	CreateSubClass($TourId, $i++, 'M', 'Motion');
	CreateSubClass($TourId, $i++, 'DM', 'Distriktsmästerskap');
    CreateSubClass($TourId, $i++, 'DT', 'Distanstävlande');
}

function CreateStandardEvents($TourId, $TourType, $SubRule, $Outdoor=true) {
	$TargetR=($Outdoor?5:2);
	$TargetC=($Outdoor?9:4);
	$TargetOther=($Outdoor?5:1);

    // Create Finals for all classes. Note that FirstPhase is set to 0 so the final is not activated until the user sets it manually
    $dv = array('R'=>'Recurve','B'=>'Barebow','C'=>'Compound','L'=>'Longbow','T'=>'Traditional');
    $cl = array('U15'=>'Under 15','U18'=>'Under 18','U21'=>'Under 21',''=>'','21'=>'21','50'=>'50','60'=>'60');
    $ge = array('M'=>'Men','W'=>'Women');
    $i=1;
    foreach($dv as $k_dv => $v_dv) {
        foreach($cl as $k_cl => $v_cl) {
            $CurrTarget = ($k_dv=='C' ? $TargetC : ($k_dv=='R' ? $TargetR : $TargetOther));
            // 241215 KS: 21 class is now gender-neutral, so just create the class and don't bother with gender
            if ($k_cl == '21') {
                $CurrCode = $k_dv . $k_cl;
                $CurrDesc = $v_dv . ' ' . $v_cl;
                CreateEvent($TourId, $i++, 0, 0, 0, $CurrTarget, 5, 3, 1, 5, 3, 1, $CurrCode,  $CurrDesc, ($k_dv=='C' ? 0 : 1), 240, 240);
                continue;
            }

            foreach($ge as $k_ge => $v_ge) {
                CreateEvent($TourId, $i++, 0, 0, 0, $CurrTarget, 5, 3, 1, 5, 3, 1, $k_dv . $k_cl . $k_ge,  $v_dv . ' ' . $v_cl . ' ' . $v_ge, ($k_dv=='C' ? 0 : 1), 240, 240);
            }
        }
    }

    // Create Team Finals
    $i=1;
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetC, 4, 6, 3, 4, 6, 3, 'LU15C',  'Lag Under 15 Compound');
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetC, 4, 6, 3, 4, 6, 3, 'LU18C',  'Lag Under 18 Compound');
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetC, 4, 6, 3, 4, 6, 3, 'LU21C',  'Lag Under 21 Compound');
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetC, 4, 6, 3, 4, 6, 3, 'LSC',  'Lag Senior Compound');
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetOther, 4, 6, 3, 4, 6, 3, 'LU15B',  'Lag Under 15 Barebow', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetOther, 4, 6, 3, 4, 6, 3, 'LU18B',  'Lag Under 18 Barebow', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetOther, 4, 6, 3, 4, 6, 3, 'LU21B',  'Lag Under 21 Barebow', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetOther, 4, 6, 3, 4, 6, 3, 'LSB',  'Lag Senior Barebow', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetR, 4, 6, 3, 4, 6, 3, 'LU15R',  'Lag Under 15 Recurve', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetR, 4, 6, 3, 4, 6, 3, 'LU18R',  'Lag Under 18 Recurve', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetR, 4, 6, 3, 4, 6, 3, 'LU21R',  'Lag Under 21 Recurve', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetR, 4, 6, 3, 4, 6, 3, 'LSR',  'Lag Senior Recurve', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetOther, 4, 6, 3, 4, 6, 3, 'LU15L',  'Lag Under 15 Longbow', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetOther, 4, 6, 3, 4, 6, 3, 'LU18L',  'Lag Under 18 Longbow', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetOther, 4, 6, 3, 4, 6, 3, 'LU21L',  'Lag Under 21 Longbow', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetOther, 4, 6, 3, 4, 6, 3, 'LSL',  'Lag Senior Longbow', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetOther, 4, 6, 3, 4, 6, 3, 'LU15T',  'Lag Under 15 Traditional', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetOther, 4, 6, 3, 4, 6, 3, 'LU18T',  'Lag Under 18 Traditional', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetOther, 4, 6, 3, 4, 6, 3, 'LU21T',  'Lag Under 21 Traditional', 1);
    CreateEvent($TourId, $i++, 1, 0, 0, $TargetOther, 4, 6, 3, 4, 6, 3, 'LST',  'Lag Senior Traditional', 1);
}

function InsertStandardEvents($TourId, $TourType, $SubRule, $Outdoor=true) {
	$dv = array('R','B','C','L','T');
	$cl = array('U13'=>array('U13M','U13W'), 'U15'=>array('U15M','U15W'), 'U18'=>array('U18M','U18W'), 'U21'=>array('U21M','U21W'), 'S'=>array('M','50M','60M','W','50W','60W','21'));

	if($TourType==6 || $TourType==3 || $TourType==37 || $TourType==1) {
		foreach($dv as $v_dv) {
			foreach($cl as $k_cl => $v_cl) {
				foreach($v_cl as $dett_cl) {
					// Individual event
					if($k_cl != 'U13') {
                        $num_archers = ($k_cl == 'S') ? 3 : 2;
						InsertClassEvent($TourId, 0, 1, $v_dv.$dett_cl, $v_dv, $dett_cl);
						// Team composition
						InsertClassEvent($TourId, 1, $num_archers, 'L' . $k_cl . $v_dv, $v_dv, $dett_cl);
					}
				}
			}
		}
	}
}
