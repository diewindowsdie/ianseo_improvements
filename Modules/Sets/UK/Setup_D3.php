<?php
/*
11 	3D 	(1 distance)

$TourId is the ID of the tournament!
$SubRule is the eventual subrule (see sets.php for the order)
$TourType is the Tour Type (11)

*/

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

// default Divisions
CreateStandardDivisions($TourId, '3D',$SubRule);

// default SubClasses
//CreateSubClass($TourId, 1, '00', '00');

// default Classes
CreateStandard3DClasses($TourId, $SubRule);

// default Distances
switch($TourType) {
	case 11:
		CreateDistanceNew($TourId, $TourType, '%', array(array('Course',0)));
		break;
	case 13:
		CreateDistanceNew($TourId, $TourType, '%', array(array('Course 1',0), array('Course 2',0)));
		break;
}

// default Events
CreateStandard3DEvents($TourId, $SubRule);

// insert class in events
InsertStandard3DEvents($TourId, $SubRule);

// Elimination rounds
//InsertStandard3DEliminations($TourId, $SubRule);

// Finals & TeamFinals
CreateFinals($TourId);

// Default Target
switch($TourType) {
	case 11:
        CreateTargetFace($TourId, 1, get_text('FieldPegYellow', 'Install'), 'REG-^(((A|F|L|T|B|CB)U16[OW])|((C|R|CL)U15[OW]))$', '1', 8, 0);
        CreateTargetFace($TourId, 2, get_text('FieldPegBlue', 'Install'), 'REG-^(((A|F|L|T|B|CB)(U21[OW]|U18[OW]|[OW]|50[OW]))|((R|C|CL)U16[OW]))$', '1', 8, 0);
        CreateTargetFace($TourId, 3, 'White Peg', 'REG-^(([A-Z]{1,3}U(12|14)[OW])|((A|F|L|T|B|CB)U15[OW]))$', '1', 8, 0);
        CreateTargetFace($TourId, 4, get_text('FieldPegRed', 'Install'), 'REG-^((R|C|CL)(U21[OW]|U18[OW]|[OW]|50[OW]))$', '1', 8, 0);
		break;
	case 13:
        CreateTargetFace($TourId, 1, get_text('FieldPegYellow', 'Install'), 'REG-^(((A|F|L|T|B|CB)U16[OW])|((C|R|CL)U15[OW]))$', '1', 8, 0, 8, 0);
        CreateTargetFace($TourId, 2, get_text('FieldPegBlue', 'Install'), 'REG-^(((A|F|L|T|B|CB)(U21[OW]|U18[OW]|[OW]|50[OW]))|((R|C|CL)U16[OW]))$', '1', 8, 0, 8, 0);
        CreateTargetFace($TourId, 3, 'White Peg', 'REG-^(([A-Z]{1,3}U(12|14)[OW])|((A|F|L|T|B|CB)U15[OW]))$', '1', 8, 0, 8, 0);
        CreateTargetFace($TourId, 4, get_text('FieldPegRed', 'Install'), 'REG-^((R|C|CL)(U21[OW]|U18[OW]|[OW]|50[OW]))$', '1', 8, 0, 8, 0);
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
