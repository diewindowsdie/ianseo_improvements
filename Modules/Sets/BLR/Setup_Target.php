<?php
/*
Common Setup for "Target" Archery
*/

error_reporting(E_ALL);
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

// default Divisions

CreateStandardDivisions($TourId,(in_array($TourType,array(3,6,7,8,37)) ? '70M':'FITA'));
if ($_REQUEST["createSubClasses"]) {
    CreateStandardSubClasses($TourId, $_REQUEST['subclassesSet']);
}

// default SubClasses
//CreateSubClass($TourId, 1, '00', '00');

// default Classes
CreateStandardClasses($TourId, $SubRule, $TourType);

// default Distances
switch($TourType) {
	case 1:
		switch($SubRule) {
			case '1':
				CreateDistanceNew($TourId, $TourType, '_M',  array(array('90 m',90), array('70 m',70), array('50 m',50), array('30 m',30)));
				CreateDistanceNew($TourId, $TourType, '_W',  array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
				CreateDistanceNew($TourId, $TourType, '_U21M', array(array('90 m',90), array('70 m',70), array('50 m',50), array('30 m',30)));
				CreateDistanceNew($TourId, $TourType, '_U21W', array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
				CreateDistanceNew($TourId, $TourType, '_U18M', array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
				CreateDistanceNew($TourId, $TourType, '_U18W', array(array('60 m',60), array('50 m',50), array('40 m',40), array('30 m',30)));
				break;
			case '2':
			case '5':
				CreateDistanceNew($TourId, $TourType, '_M',  array(array('90 m',90), array('70 m',70), array('50 m',50), array('30 m',30)));
				CreateDistanceNew($TourId, $TourType, '_W',  array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
				break;
			case '3':
				CreateDistanceNew($TourId, $TourType, '_M',  array(array('90 m',90), array('70 m',70), array('50 m',50), array('30 m',30)));
				CreateDistanceNew($TourId, $TourType, '_W',  array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
				CreateDistanceNew($TourId, $TourType, '_U21M', array(array('90 m',90), array('70 m',70), array('50 m',50), array('30 m',30)));
				CreateDistanceNew($TourId, $TourType, '_U21W', array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
				break;
			case '4':
				CreateDistanceNew($TourId, $TourType, '_U21M', array(array('90 m',90), array('70 m',70), array('50 m',50), array('30 m',30)));
				CreateDistanceNew($TourId, $TourType, '_U21W', array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
				CreateDistanceNew($TourId, $TourType, '_U18M', array(array('70 m',70), array('60 m',60), array('50 m',50), array('30 m',30)));
				CreateDistanceNew($TourId, $TourType, '_U18W', array(array('60 m',60), array('50 m',50), array('40 m',40), array('30 m',30)));
				break;
		}
		break;
	case 3:
		switch($SubRule) {
			case '1':
				CreateDistanceNew($TourId, $TourType, 'R_', array(array('70m-1',70), array('70m-2',70)));
				CreateDistanceNew($TourId, $TourType, 'RU21_', array(array('70m-1',70), array('70m-2',70)));
				CreateDistanceNew($TourId, $TourType, 'RU18_', array(array('60m-1',60), array('60m-2',60)));
				CreateDistanceNew($TourId, $TourType, 'RU14_', array(array('40m-1',40), array('40m-2',40)));
				CreateDistanceNew($TourId, $TourType, 'R50_', array(array('60m-1',60), array('60m-2',60)));
				CreateDistanceNew($TourId, $TourType, 'C_', array(array('50m-1',50), array('50m-2',50)));
				CreateDistanceNew($TourId, $TourType, 'CU21_', array(array('50m-1',50), array('50m-2',50)));
				CreateDistanceNew($TourId, $TourType, 'CU18_', array(array('50m-1',50), array('50m-2',50)));
				CreateDistanceNew($TourId, $TourType, 'CU14_', array(array('40m-1',40), array('40m-2',40)));
				CreateDistanceNew($TourId, $TourType, 'C50_', array(array('50m-1',50), array('50m-2',50)));
				CreateDistanceNew($TourId, $TourType, 'B_', array(array('50m-1',50), array('50m-2',50)));
				CreateDistanceNew($TourId, $TourType, 'BU21_', array(array('50m-1',50), array('50m-2',50)));
				CreateDistanceNew($TourId, $TourType, 'BU18_', array(array('50m-1',50), array('50m-2',50)));
				CreateDistanceNew($TourId, $TourType, 'BU14_', array(array('30m-1',30), array('30m-2',30)));
				CreateDistanceNew($TourId, $TourType, 'B50_', array(array('50m-1',50), array('50m-2',50)));
				break;
			case '2':
			case '3':
			case '5':
				CreateDistanceNew($TourId, $TourType, 'R%', array(array('70m-1',70), array('70m-2',70)));
				CreateDistanceNew($TourId, $TourType, 'C%', array(array('50m-1',50), array('50m-2',50)));
				CreateDistanceNew($TourId, $TourType, 'B%', array(array('50m-1',50), array('50m-2',50)));
				break;
			case '4':
				CreateDistanceNew($TourId, $TourType, 'RU21_', array(array('70m-1',70), array('70m-2',70)));
				CreateDistanceNew($TourId, $TourType, 'RU18_', array(array('60m-1',60), array('60m-2',60)));
				CreateDistanceNew($TourId, $TourType, 'RU14_', array(array('40m-1',40), array('40m-2',40)));
				CreateDistanceNew($TourId, $TourType, 'CU21_', array(array('50m-1',50), array('50m-2',50)));
				CreateDistanceNew($TourId, $TourType, 'CU18_', array(array('50m-1',50), array('50m-2',50)));
				CreateDistanceNew($TourId, $TourType, 'CU14_', array(array('40m-1',40), array('40m-2',40)));
				CreateDistanceNew($TourId, $TourType, 'BU21_', array(array('50m-1',50), array('50m-2',50)));
				CreateDistanceNew($TourId, $TourType, 'BU18_', array(array('50m-1',50), array('50m-2',50)));
				CreateDistanceNew($TourId, $TourType, 'BU14_', array(array('30m-1',30), array('30m-2',30)));
				break;
		}
		break;
	case 6:
        CreateDistanceNew($TourId, $TourType, '_U18_', array(array('18m-1',18), array('18m-2',18)));
        CreateDistanceNew($TourId, $TourType, '_U21_', array(array('18m-1',18), array('18m-2',18)));
        CreateDistanceNew($TourId, $TourType, '__', array(array('18m-1',18), array('18m-2',18)));
		break;
    case 1000:
        CreateDistanceNew($TourId, $TourType, '_U18_', array(array('18m-1',18), array('18m-2',18), array('18m-3',18), array('18m-4',18)));
        CreateDistanceNew($TourId, $TourType, '_U21_', array(array('18m-1',18), array('18m-2',18), array('18m-3',18), array('18m-4',18)));
        CreateDistanceNew($TourId, $TourType, '__', array(array('18m-1',18), array('18m-2',18), array('18m-3',18), array('18m-4',18)));
        break;
    case 37:
		switch($SubRule) {
			case '1':
				CreateDistanceNew($TourId, $TourType, 'R_', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
				CreateDistanceNew($TourId, $TourType, 'RU21_', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
				CreateDistanceNew($TourId, $TourType, 'RU18_', array(array('60m-1',60), array('60m-2',60), array('60m-3',60), array('60m-4',60)));
				CreateDistanceNew($TourId, $TourType, 'C_', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
				CreateDistanceNew($TourId, $TourType, 'CU21_', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
				CreateDistanceNew($TourId, $TourType, 'CU18_', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
				break;
			case '2':
			case '3':
			case '5':
				CreateDistanceNew($TourId, $TourType, 'R%', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
				CreateDistanceNew($TourId, $TourType, 'C%', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
				break;
			case '4':
				CreateDistanceNew($TourId, $TourType, 'RU21_', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
				CreateDistanceNew($TourId, $TourType, 'RU18_', array(array('60m-1',60), array('60m-2',60), array('60m-3',60), array('60m-4',60)));
				CreateDistanceNew($TourId, $TourType, 'CU21_', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
				CreateDistanceNew($TourId, $TourType, 'CU18_', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
				break;
		}
		break;
}

// default Events
CreateStandardEvents($TourId, $SubRule, $TourType);

// Classes in Events
InsertStandardEvents($TourId, $SubRule);

// Finals & TeamFinals
CreateFinals($TourId);

// Default Target
switch($TourType) {
	case 1:
		CreateTargetFace($TourId, 1, '~Default', '%', '1', 5, 122, 5, 122, 5, 80, 10, 80);
		// optional target faces
		CreateTargetFace($TourId, 2, '~Option1', '%', '',  5, 122, 5, 122, 5, 80,  5, 80);
		CreateTargetFace($TourId, 3, '~Option2', '%', '',  5, 122, 5, 122, 9, 80, 10, 80);
		break;
	case 3: // 70m/60m/50m/40m round
		CreateTargetFace($TourId, 1, '~Default', 'REG-^R|^B', '1', 5, 122, 5, 122);
		CreateTargetFace($TourId, 2, '~DefaultCO', 'REG-^C', '1',  9, 80, 9, 80);
		break;
	case 37: // double 70m/60m/50m/40m round
		CreateTargetFace($TourId, 1, '~Default', 'REG-^R|^B', '1', 5, 122, 5, 122,5, 122, 5, 122);
		CreateTargetFace($TourId, 2, '~DefaultCO', 'REG-^C', '1',  9, 80, 9, 80,9, 80, 9, 80);
		break;
	case 6:
    case 1000:
		CreateTargetFace($TourId, 1, '~Default', 'REG-^R|^B', '1', 2, 40, 2, 40);
		CreateTargetFace($TourId, 2, '~DefaultCO', 'C%', '1', 4, 40, 4, 40);
		// optional target faces
		CreateTargetFace($TourId, 3, '~Option1', 'REG-^R|^B', '',  1, 40, 1, 40);
		break;
}

// create a first distance prototype
CreateDistanceInformation($TourId, $DistanceInfoArray, 24, 4);

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

