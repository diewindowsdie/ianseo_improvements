<?php
/*
Common Setup for "Target" Archery
*/

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

// default Divisions
CreateStandardDivisions($TourId, $TourType, $SubRule);

// default Classes
CreateStandardClasses($TourId, $SubRule, $TourType);

// default Subclasses
CreateStandardSubClasses($TourId);

// default Distances
switch($TourType) {
case 3:	// Outdoor - 70mRound
	if($SubRule == 1){
		CreateDistanceNew($TourId, $TourType, 'R_', array(array('70m-1',70), array('70m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'C_', array(array('50m-1',50), array('50m-2',50)));
		CreateDistanceNew($TourId, $TourType, 'B_', array(array('50m-1',50), array('50m-2',50)));
	}

	if($SubRule == 2){
		CreateDistanceNew($TourId, $TourType, 'R_', array(array('70m-1',70), array('70m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU24_', array(array('70m-1',70), array('70m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU21_', array(array('70m-1',70), array('70m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU18_', array(array('60m-1',70), array('60m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU15_', array(array('40m',70), array('20m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU12_', array(array('20m-1',70), array('20m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'C_', array(array('50m-1',50), array('50m-2',50)));
		CreateDistanceNew($TourId, $TourType, 'B_', array(array('50m-1',50), array('50m-2',50)));
	}

	if($SubRule == 3){
		CreateDistanceNew($TourId, $TourType, 'RU24_', array(array('70m-1',70), array('70m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU21_', array(array('70m-1',70), array('70m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU18_', array(array('60m-1',70), array('60m-2',70)));
	}

	if($SubRule == 4){
		CreateDistanceNew($TourId, $TourType, 'RU15_', array(array('40m',70), array('20m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU12_', array(array('20m-1',70), array('20m-2',70)));
	}
	break;
case 37://double 70m Round
	if($SubRule == 1){
		CreateDistanceNew($TourId, $TourType, 'R_', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
		CreateDistanceNew($TourId, $TourType, 'C_', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
		CreateDistanceNew($TourId, $TourType, 'B_', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
	}

	if($SubRule == 2){
		CreateDistanceNew($TourId, $TourType, 'R_', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
		CreateDistanceNew($TourId, $TourType, 'RU24_', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
		CreateDistanceNew($TourId, $TourType, 'RU21_', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
		CreateDistanceNew($TourId, $TourType, 'RU18_', array(array('60m-1',70), array('60m-2',70), array('60m-3',70), array('60m-4',70)));
		CreateDistanceNew($TourId, $TourType, 'RU15_', array(array('40m-1',70), array('20m-1',70), array('40m-2',70), array('20m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU12_', array(array('20m-1',70), array('20m-2',70), array('20m-3',70), array('20m-4',70)));
		CreateDistanceNew($TourId, $TourType, 'C_', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
		CreateDistanceNew($TourId, $TourType, 'B_', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
	}

	if($SubRule == 3){
		CreateDistanceNew($TourId, $TourType, 'RU24_', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
		CreateDistanceNew($TourId, $TourType, 'RU21_', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
		CreateDistanceNew($TourId, $TourType, 'RU18_', array(array('60m-1',70), array('60m-2',70), array('60m-3',70), array('60m-4',70)));
	}

	if($SubRule == 4){
		CreateDistanceNew($TourId, $TourType, 'RU15_', array(array('40m-1',70), array('20m-1',70), array('40m-2',70), array('20m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU12_', array(array('20m-1',70), array('20m-2',70), array('20m-3',70), array('20m-4',70)));
	}
	break;

case 1: //1440ROUND
	if($SubRule == 1){
		CreateDistanceNew($TourId, $TourType, 'RM', array(array('90m',70), array('70m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RW', array(array('70m',70), array('60m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'CM', array(array('90m',70), array('70m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'CW', array(array('70m',70), array('60m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'B_', array(array('60m',70), array('50m',70), array('40m',70), array('30m',70)));
	}

	if($SubRule == 2){
		CreateDistanceNew($TourId, $TourType, 'RM', array(array('90m',70), array('70m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RW', array(array('70m',70), array('60m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU24M', array(array('90m',70), array('70m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU24W', array(array('70m',70), array('60m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU21M', array(array('90m',70), array('70m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU21W', array(array('70m',70), array('60m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU18M', array(array('70m',70), array('60m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU18W', array(array('60m',70), array('50m',70), array('40m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU15_', array(array('50m',70), array('40m',70), array('30m',70), array('20m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU12_', array(array('25m',70), array('20m',70), array('15m',70), array('10m',70)));
		CreateDistanceNew($TourId, $TourType, 'CM', array(array('90m',70), array('70m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'CW', array(array('70m',70), array('60m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'B_', array(array('60m',70), array('50m',70), array('40m',70), array('30m',70)));

	}

	if($SubRule == 3){
		CreateDistanceNew($TourId, $TourType, 'RU24M', array(array('90m',70), array('70m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU24W', array(array('70m',70), array('60m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU21M', array(array('90m',70), array('70m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU21W', array(array('70m',70), array('60m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU18M', array(array('70m',70), array('60m',70), array('50m',70), array('30m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU18W', array(array('60m',70), array('50m',70), array('40m',70), array('30m',70)));
	}

	if($SubRule == 4){
		CreateDistanceNew($TourId, $TourType, 'RU15_', array(array('50m',70), array('40m',70), array('30m',70), array('20m',70)));
		CreateDistanceNew($TourId, $TourType, 'RU12_', array(array('25m',70), array('20m',70), array('15m',70), array('10m',70)));
	}
	break;

case 6:	// INDOOR
	if($SubRule == 1){
		CreateDistanceNew($TourId, $TourType, 'R_', array(array('18m-1',70), array('18m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'C_', array(array('18m-1',50), array('18m-2',50)));
		CreateDistanceNew($TourId, $TourType, 'B_', array(array('18m-1',50), array('18m-2',50)));
	}

	if($SubRule == 2){
		CreateDistanceNew($TourId, $TourType, 'R_', array(array('18m-1',70), array('18m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU24_', array(array('18m-1',70), array('18m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU21_', array(array('18m-1',70), array('18m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU18_', array(array('18m-1',70), array('18m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU15_', array(array('18m-1',70), array('18m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU12_', array(array('10m-1',70), array('10m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'C_', array(array('18m-1',50), array('18m-2',50)));
		CreateDistanceNew($TourId, $TourType, 'B_', array(array('18m-1',50), array('18m-2',50)));
	}

	if($SubRule == 3){
		CreateDistanceNew($TourId, $TourType, 'RU24_', array(array('18m-1',70), array('18m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU21_', array(array('18m-1',70), array('18m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU18_', array(array('18m-1',70), array('18m-2',70)));
	}

	if($SubRule == 4){
		CreateDistanceNew($TourId, $TourType, 'RU15_', array(array('18m-1',70), array('18m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'RU12_', array(array('10m-1',70), array('10m-2',70)));
	}
	break;
}

// default Events
CreateStandardEvents($TourId, $TourType, $SubRule, $tourDetCategory=='1');

// Classes in Events
InsertStandardEvents($TourId, $TourType, $SubRule, $tourDetCategory=='1');

// Finals & TeamFinals
CreateFinals($TourId);

// Default Target
$i=1;
switch($TourType) {
	case 6: // Indoor - 
        if ($SubRule==1) { 
			CreateTargetFace($TourId, $i++, '~40cm (6-10)', 'R_', 1, 2, 40, 2, 40);
			CreateTargetFace($TourId, $i++, '~40cm (6-small10)', 'C_', 1, 4, 40, 4, 40);
			CreateTargetFace($TourId, $i++, '~40cm (1-10)', 'B_', 1, 1, 40, 1, 40);
		}
		if ($SubRule==2) {
			CreateTargetFace($TourId, $i++, '~40cm (6-10)', 'R_', 1, 2, 40, 2, 40);
			CreateTargetFace($TourId, $i++, '~40cm (6-10)', 'RU24_', 1, 2, 40, 2, 40);
			CreateTargetFace($TourId, $i++, '~40cm (6-10)', 'RU21_', 1, 2, 40, 2, 40);
			CreateTargetFace($TourId, $i++, '~40cm (6-10)', 'RU18_', 1, 2, 40, 2, 40);
			CreateTargetFace($TourId, $i++, '~60cm (1-10)', 'RU15_', 1, 1, 60, 1, 60);
			CreateTargetFace($TourId, $i++, '~80cm (1-10)', 'RU12_', 1, 1, 80, 1, 80);
			CreateTargetFace($TourId, $i++, '~40cm (6-small10)', 'C_', 1, 4, 40, 4, 40);
			CreateTargetFace($TourId, $i++, '~40cm (1-10)', 'B_', 1, 1, 40, 1, 40);
		}
		if ($SubRule==3) {
			CreateTargetFace($TourId, $i++, '~40cm (6-10)', 'RU24_', 1, 2, 40, 2, 40);
			CreateTargetFace($TourId, $i++, '~40cm (6-10)', 'RU21_', 1, 2, 40, 2, 40);
			CreateTargetFace($TourId, $i++, '~40cm (6-10)', 'RU18_', 1, 2, 40, 2, 40);
		}
		if ($SubRule==4) {
			CreateTargetFace($TourId, $i++, '~80cm (1-10)', 'RU15_', 1, 1, 60, 1, 60);
			CreateTargetFace($TourId, $i++, '~80cm (1-10)', 'RU12_', 1, 1, 80, 1, 80);
		}		
        break;
    case 3: // 70m Round
        CreateTargetFace($TourId, $i++, '122cm (1-10)', 'R%', '1', 5, 122, 5, 122);
        CreateTargetFace($TourId, $i++, '80cm (5-10)', 'C%', '1', 9, 80, 9, 80);
        CreateTargetFace($TourId, $i++, '122cm (1-10)', 'B%', '1', 5, 122, 5, 122);
		break;

	case 37: // Double 70m Round
        CreateTargetFace($TourId, $i++, '122cm (1-10)', 'R%', '1', 5, 122, 5, 122, 5, 122, 5, 122);
        CreateTargetFace($TourId, $i++, '80cm (5-10)', 'C%', '1', 9, 80, 9, 80, 9, 80, 9, 80);
        CreateTargetFace($TourId, $i++, '122cm (1-10)', 'B%', '1', 5, 122, 5, 122, 5, 122, 5, 122);
		break;

	case 1: // 1440 Round
		if ($SubRule==1) {
			CreateTargetFace($TourId, $i++, '122cm (1-10)', 'R_', '1', 5, 122, 5, 122, 9, 80, 9, 80);
		}
        
		if ($SubRule==2) {
			CreateTargetFace($TourId, $i++, '122cm (1-10)', 'R_', '1', 5, 122, 5, 122, 9, 80, 9, 80);
			CreateTargetFace($TourId, $i++, '122cm (1-10)', 'RU24_', '1', 5, 122, 5, 122, 9, 80, 9, 80);
			CreateTargetFace($TourId, $i++, '122cm (1-10)', 'RU21_', '1', 5, 122, 5, 122, 9, 80, 9, 80);
			CreateTargetFace($TourId, $i++, '122cm (1-10)', 'RU18_', '1', 5, 122, 5, 122, 9, 80, 9, 80);
			CreateTargetFace($TourId, $i++, '122cm (1-10)', 'RU15_', '1', 5, 122, 5, 122, 5, 80, 5, 80);
			CreateTargetFace($TourId, $i++, '122cm (1-10)', 'RU12_', '1', 5, 122, 5, 122, 5, 80, 5, 80);
		}
			if ($SubRule==3) {
			CreateTargetFace($TourId, $i++, '122cm (1-10)', 'RU24_', '1', 5, 122, 5, 122, 9, 80, 9, 80);
			CreateTargetFace($TourId, $i++, '122cm (1-10)', 'RU21_', '1', 5, 122, 5, 122, 9, 80, 9, 80);
			CreateTargetFace($TourId, $i++, '122cm (1-10)', 'RU18_', '1', 5, 122, 5, 122, 9, 80, 9, 80);
		}
		if ($SubRule==4) {
			CreateTargetFace($TourId, $i++, '122cm (1-10)', 'RU15_', '1', 5, 122, 5, 122, 5, 80, 5, 80);
			CreateTargetFace($TourId, $i++, '122cm (1-10)', 'RU12_', '1', 5, 122, 5, 122, 5, 80, 5, 80);
		}
        CreateTargetFace($TourId, $i++, '122cm (1-10)', 'C%', '1', 5, 122, 5, 122, 9, 80, 9, 80);
        CreateTargetFace($TourId, $i++, '122cm (1-10)', 'B%', '1', 5, 122, 5, 122, 5, 80, 5, 80);
		break;
}

// create a first distance prototype
CreateDistanceInformation($TourId, $DistanceInfoArray, 10, 2);

// Update Tour details
$tourDetails=array(
    'ToCollation' => $tourCollation,
    'ToTypeName' => $tourDetTypeName,
    'ToNumDist' => $tourDetNumDist,
    'ToNumEnds' => $tourDetNumEnds,
    'ToMaxDistScore' => $tourDetMaxDistScore,
    'ToMaxFinIndScore' => $tourDetMaxFinIndScore,
    'ToMaxFinTeamScore' => $tourDetMaxFinTeamScore,
    'ToCategory' => $tourDetCategory,
    'ToElabTeam' => $tourDetElabTeam,
    'ToElimination' => $tourDetElimination,
    'ToGolds' => $tourDetGolds,
    'ToXNine' => $tourDetXNine,
    'ToGoldsChars' => $tourDetGoldsChars,
    'ToXNineChars' => $tourDetXNineChars,
    'ToDouble' => $tourDetDouble,
   'ToIocCode'	=> $tourDetIocCode,
	);
UpdateTourDetails($TourId, $tourDetails);
?>