<?php
/*
11 	3D 	(1 distance)
13  3D  (2 distances)

$TourId is the ID of the tournament!
$SubRule is the eventual subrule (see sets.php for the order)
$TourType is the Tour Type (11, 13)

*/

/**
 * Modified by helmut.poell@gmx.at
 * 2026-09-07
 */

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

// default Divisions
CreateStandardDivisions($TourId, '3D', $SubRule);

// default SubClasses
//CreateSubClass($TourId, 1, '00', '00');

// default Classes
CreateStandardClasses($TourId, $SubRule, $TourType);

// default Distances
switch($TourType) {
	case 11:
		CreateDistanceNew($TourId, $TourType, '%', array(array('Runde',0)));
		break;
	case 13:
		CreateDistanceNew($TourId, $TourType, '%', array(array('Runde 1',0), array('Runde 2',0)));
		break;
}

// default Events
CreateStandard3DEvents($TourId, $SubRule);

// insert class in events
InsertStandard3DEvents($TourId, $SubRule);

// Elimination rounds
InsertStandard3DEliminations($TourId, $SubRule);

// Finals & TeamFinals
CreateFinals($TourId);

$i=1;

// Default Target
switch($TourType) {
	case 11:
        CreateTargetFace($TourId, $i++, 'Gelb', 'REG-^RU8|^RU10|CU8|^CU10|BU8|^BU10|^LU8|^LU10|^TU8|^TU10', '1', TGT_3D, 0);
        CreateTargetFace($TourId, $i++, 'Weiß', 'REG-^BU13|^BU15|^LU13|^LU15|^TU13|^TU15', '1', TGT_3D, 0);
        CreateTargetFace($TourId, $i++, 'Blau', 'REG-^RU13|^RU15|^CU13|^CU15', '1', TGT_3D, 0);
        CreateTargetFace($TourId, $i++, 'Blau', 'REG-^BU18|^BU21|^B50|^B65|^B[M|W|G]$|^LU18|^LU21|^L50|^L65|^L[M|W|G]$|^TU18|^TU21|^T50|^T65|^T[M|W|G]$', '1', TGT_3D, 0);
        CreateTargetFace($TourId, $i++, 'Rot', 'REG-^RU18|^RU21|^R50|^R65|^R[M|W|G]$|^CU18|^CU21|^C50|^C65|^C[M|W|G]$', '1', TGT_3D, 0);
        break;
    case 13:
        CreateTargetFace($TourId, $i++, 'Weiß', 'REG-^BU13|^BU15|^LU13|^LU15|^TU13|^TU15', '1', TGT_3D, 0, TGT_3D, 0);
        CreateTargetFace($TourId, $i++, 'Blau', 'REG-^RU13|^RU15|^CU13|^CU15', '1', TGT_3D, 0, TGT_3D, 0);
        CreateTargetFace($TourId, $i++, 'Blau', 'REG-^BU18|^BU21|^B50|^B65|^B[M|W|G]$|^LU18|^LU21|^L50|^L65|^L[M|W|G]$|^TU18|^TU21|^T50|^T65|^T[M|W|G]$', '1', TGT_3D, 0, TGT_3D, 0);
        CreateTargetFace($TourId, $i++, 'Rot', 'REG-^RU18|^RU21|^R50|^R65|^R[M|W|G]$|^CU18|^CU21|^C50|^C65|^C[M|W|G]$', '1', TGT_3D, 0, TGT_3D, 0);
        break;
}

// create a first distance prototype
CreateDistanceInformation($TourId, $DistanceInfoArray, $tourDetNumEnds, 6);

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

