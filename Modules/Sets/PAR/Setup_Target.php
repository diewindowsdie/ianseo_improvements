<?php
/*

Common setup for Target

*/

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

// default Divisions
CreateStandardDivisions($TourId, $SubRule);

// default Classes
CreateStandardClasses($TourId, $SubRule);

// default Distances
switch($TourType) {
	case 1:
	case 4:
		// 1440 and 72 round
		CreateDistanceNew($TourId, $TourType, 'P%M', array(array('90 m',90), array('70 m',70), array('50 m',50), array('30 m',30)));
		CreateDistanceNew($TourId, $TourType, 'P%W', array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
		CreateDistanceNew($TourId, $TourType, 'W1%',  array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
		CreateDistanceNew($TourId, $TourType, 'VI%',  array(array('60cm face',30), array('80cm face',30), array('80cm face',30), array('122cm face',30)));
		break;
	case 18:
		// 1440 + 50m
		CreateDistanceNew($TourId, $TourType, 'PR%M', array(array('90 m',90), array('70 m',70), array('50 m',50), array('30 m',30)));
		CreateDistanceNew($TourId, $TourType, 'PR%W', array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
		CreateDistanceNew($TourId, $TourType, 'VI%',  array(array('60cm face',30), array('80cm face',30), array('80cm face',30), array('122cm face',30)));
		CreateDistanceNew($TourId, $TourType, 'PC%',  array(array('50m-1',50), array('50m-2',50), array('-',0), array('-',0)));
		CreateDistanceNew($TourId, $TourType, 'W1%', array(array('50m-1',50), array('50m-2',50), array('-',0), array('-',0)));
		break;
	case 2:
		// 2x1440 round
		CreateDistanceNew($TourId, $TourType, 'P%M', array(array('90 m',90), array('70 m',70), array('50 m',50), array('30 m',30), array('90 m',90), array('70 m',70), array('50 m',50), array('30 m',30)));
		CreateDistanceNew($TourId, $TourType, 'P%W', array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30), array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
		CreateDistanceNew($TourId, $TourType, 'W1%',  array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30), array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
		CreateDistanceNew($TourId, $TourType, 'VI%',  array(array('60cm face',30), array('80cm face',30), array('80cm face',30), array('122cm face',30), array('60cm face',30), array('80cm face',30), array('80cm face',30), array('122cm face',30)));
		break;
	case 3:
		// 70m round
		CreateDistanceNew($TourId, $TourType, 'PR_', array(array('70m-1',70), array('70m-2',70)));
		CreateDistanceNew($TourId, $TourType, 'PC_', array(array('50m-1',50), array('50m-2',50)));
        if($SubRule=='2') {
            CreateDistanceNew($TourId, $TourType, '%U21%', array(array('18m-1',18), array('18m-2',18)));
        } else  if($SubRule=='3') {
            CreateDistanceNew($TourId, $TourType, 'R%', array(array('70m-1',70), array('70m-2',70)));
            CreateDistanceNew($TourId, $TourType, 'C%', array(array('50m-1',50), array('50m-2',50)));
        }
        CreateDistanceNew($TourId, $TourType, 'W1_', array(array('50m-1',50), array('50m-2',50)));
		CreateDistanceNew($TourId, $TourType, 'VI_', array(array('30m-1',30), array('30m-2',30)));
		break;
	case 5:
		// 900 round
		CreateDistanceNew($TourId, $TourType, 'P%', array(array('60 m',60), array('50 m',50), array('40 m',40)));
		CreateDistanceNew($TourId, $TourType, 'W1%', array(array('60 m',60), array('50 m',50), array('40 m',40)));
		CreateDistanceNew($TourId, $TourType, 'VI%', array(array('30m-1',30), array('30m-2',30), array('30m-3',30)));
		break;
	case 6:
		CreateDistanceNew($TourId, $TourType, '%', array(array('18m-1',18), array('18m-2',18)));
		break;
	case 7:
		CreateDistanceNew($TourId, $TourType, '%', array(array('25m-1',25), array('25m-2',25)));
		break;
	case 8:
		CreateDistanceNew($TourId, $TourType, '%', array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
		break;
    case 37:
		CreateDistanceNew($TourId, $TourType, 'R%', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
		CreateDistanceNew($TourId, $TourType, 'C%', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
		CreateDistanceNew($TourId, $TourType, 'W1%', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
        CreateDistanceNew($TourId, $TourType, 'VI%', array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
        break;
}

if($TourType==3 or $TourType==6 or $TourType==18 or $TourType==37) {
	// default Events
	CreateStandardEvents($TourId, $SubRule, $TourType!=6);

	// Classes in Events
	InsertStandardEvents($TourId, $SubRule, $TourType!=6);

	// Finals & TeamFinals
	CreateFinals($TourId);
}

// Default Target
switch($TourType) {
	case 1:
	case 4:
		CreateTargetFace($TourId, 1, '~Default', '%', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
		CreateTargetFace($TourId, 2, '~Default', 'VI%', '1', TGT_OUT_FULL, 60, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80, TGT_OUT_FULL, 122);
		// optional target faces
		CreateTargetFace($TourId, 3, '~Option1', '%', '',  TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
		break;
	case 18:
		CreateTargetFace($TourId, 1, '~Default', '%', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
		CreateTargetFace($TourId, 2, '~Default', 'VI%', '1', TGT_OUT_FULL, 60, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80, TGT_OUT_FULL, 122);
		CreateTargetFace($TourId, 3, '~Default', 'C%', '1',  TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80, 0, 0, 0, 0);
		CreateTargetFace($TourId, 4, '~Default', 'W1%', '1',  TGT_OUT_FULL, 80, TGT_OUT_FULL, 80, 0, 0, 0, 0);
		// optional target faces
		CreateTargetFace($TourId, 5, '~Option1', 'R%', '',  TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
		break;
	case 2:
		CreateTargetFace($TourId, 1, '~Default', '%', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
		CreateTargetFace($TourId, 2, '~Default', 'VI%', '1', TGT_OUT_FULL, 60, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80, TGT_OUT_FULL, 122, TGT_OUT_FULL, 60, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80, TGT_OUT_FULL, 122);
		// optional target faces
		CreateTargetFace($TourId, 3, 'Option1', '%', '',  TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80,  TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
		break;
	case 3:
		CreateTargetFace($TourId, 1, '~Default', '%R_', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
		CreateTargetFace($TourId, 2, '~Default', '%C_', '1', TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
		CreateTargetFace($TourId, 3, '~Default', 'W1_', '1', TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
		CreateTargetFace($TourId, 4, '~Default', 'VI_', '1', TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
        if($SubRule=='2') {
            CreateTargetFace($TourId, 5, '~Default', 'P_U21_', '1', TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
            CreateTargetFace($TourId, 6, '~Default', 'W1U21_', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
            CreateTargetFace($TourId, 7, '~Default', 'VI_U21', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
            TargetFaceGoldsXnines($TourId, 5, '10', '9', 'L', 'J');
            TargetFaceGoldsXnines($TourId, 6, '10', '9', 'L', 'J');
            TargetFaceGoldsXnines($TourId, 7, '10', '9', 'L', 'J');
        }
		break;
	case 5:
		CreateTargetFace($TourId, 1, '~Default', '%', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
		CreateTargetFace($TourId, 2, '~Default', 'VI%', '1', TGT_OUT_FULL, 80, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
		break;
	case 6:
		CreateTargetFace($TourId, 1, '~Default', '%R_', '1', TGT_IND_6_big10, 40, TGT_IND_6_big10, 40);
		CreateTargetFace($TourId, 2, '~Default', '%C_', '1', TGT_IND_6_small10, 40, TGT_IND_6_small10, 40);
		CreateTargetFace($TourId, 3, '~Default', 'W1_', '1', TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
		CreateTargetFace($TourId, 4, '~Default', 'VI_', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
		// optional target faces
		CreateTargetFace($TourId, 5, '~Option1', '%R%', '',  TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
        CreateTargetFace($TourId, 6, '~Option1', 'W1_', '', TGT_IND_6_big10, 40, TGT_IND_6_big10, 40);
        if($SubRule=='2') {
            CreateTargetFace($TourId, 7, '~Default', 'P_U21_', '1', TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
            CreateTargetFace($TourId, 8, '~Default', 'W1U21_', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
            CreateTargetFace($TourId, 9, '~Default', 'VI_U21', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
        }
		break;
	case 7:
		CreateTargetFace($TourId, 1, '~Default', 'PR%', '1', TGT_IND_6_big10, 60, TGT_IND_6_big10, 60);
		CreateTargetFace($TourId, 2, '~Default', 'PC%', '1', TGT_IND_6_small10, 60, TGT_IND_6_small10, 60);
		CreateTargetFace($TourId, 3, '~Default', 'W1%', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
		CreateTargetFace($TourId, 4, '~Default', 'VI%', '1', TGT_IND_1_big10, 80, TGT_IND_1_big10, 80);
		// optional target faces
		CreateTargetFace($TourId, 5, '~Option1', '%R%', '',  TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
        CreateTargetFace($TourId, 6, '~Option1', 'W1%', '',  TGT_IND_6_big10, 60, TGT_IND_6_big10, 60);
		break;
	case 8:
		CreateTargetFace($TourId, 1, '~Default', 'PR%', '1', TGT_IND_6_big10, 60, TGT_IND_6_big10, 60, TGT_IND_6_big10, 40, TGT_IND_6_big10, 40);
		CreateTargetFace($TourId, 2, '~Default', 'PC%', '1', TGT_IND_6_small10, 60, TGT_IND_6_small10, 60, TGT_IND_6_small10, 40, TGT_IND_6_small10, 40);
		CreateTargetFace($TourId, 3, '~Default', 'W1%', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60, TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
		CreateTargetFace($TourId, 4, '~Default', 'VI%', '1', TGT_IND_1_big10, 80, TGT_IND_1_big10, 80, TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
		// optional target faces
		CreateTargetFace($TourId, 5, '~Option1', 'PR%', '',  TGT_IND_1_big10, 60, TGT_IND_1_big10, 60,  TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
        CreateTargetFace($TourId, 6, '~Option1', 'W1%', '', TGT_IND_6_big10, 60, TGT_IND_6_big10, 60, TGT_IND_6_big10, 40, TGT_IND_6_big10, 40);
		break;
    case 37:
        CreateTargetFace($TourId, 1, '~Default', 'R%', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
        CreateTargetFace($TourId, 2, '~Default', 'C%', '1', TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
        CreateTargetFace($TourId, 3, '~Default', 'W1%', '1', TGT_OUT_FULL, 80, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
        CreateTargetFace($TourId, 4, '~Default', 'VI%', '1', TGT_OUT_FULL, 80, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
        break;
}

// create a first distance prototype
CreateDistanceInformation($TourId, $DistanceInfoArray, 32, 2);

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
//	'ToIocCode'	=> $tourDetIocCode,
	);
UpdateTourDetails($TourId, $tourDetails);

