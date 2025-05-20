<?php
/*

Common setup for Field

*/

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

// default Divisions
CreateStandardDivisions($TourId, 'FIELD',$SubRule);

// default SubClasses
//CreateSubClass($TourId, 1, '00', '00');

// default Classes
CreateStandardFieldClasses($TourId, $SubRule);

// default Distances
switch($TourType) {
	case 9:
		CreateDistanceNew($TourId, $TourType, '%', array(array('Course',0)));
		break;
	case 10:
	case 12:
		CreateDistanceNew($TourId, $TourType, '%', array(array('Unmarked',0), array('Marked',0)));
		break;
}

// default Events
CreateStandardFieldEvents($TourId, $SubRule);

// insert class in events
InsertStandardFieldEvents($TourId, $SubRule);

// Elimination rounds
InsertStandardFieldEliminations($TourId, $SubRule);

// Finals & TeamFinals
CreateFinals($TourId);


// Default Target
switch($TourType) {
	case 9:
        CreateTargetFace($TourId, 1, get_text('FieldPegYellow', 'Install'), 'REG-^(((A|F|L|T|B|CB)U16[MW])|((C|R|CL)U15[MW]))$', '1', 6, 0);
        CreateTargetFace($TourId, 2, get_text('FieldPegBlue', 'Install'), 'REG-^(((A|F|L|T|B|CB)(U21[MW]|U18[MW]|[MW]|50[MW]))|((R|C|CL)U16[MW]))$', '1', 6, 0);
        CreateTargetFace($TourId, 3, 'White Peg', 'REG-^(([A-Z]{1,3}U(12|14)[MW])|((A|F|L|T|B|CB)U15[MW]))$', '1', 6, 0);
        CreateTargetFace($TourId, 4, get_text('FieldPegRed', 'Install'), 'REG-^((R|C|CL)(U21[MW]|U18[MW]|[MW]|50[MW]))$', '1', 6, 0);
		break;
	case 10:
	case 12:
			CreateTargetFace($TourId, 1, get_text('FieldPegYellow', 'Install'), 'REG-^(((A|F|L|T|B|CB)U16[MW])|((C|R|CL)U15[MW]))$', '1', 6, 0, 6, 0);
			CreateTargetFace($TourId, 2, get_text('FieldPegBlue', 'Install'), 'REG-^(((A|F|L|T|B|CB)(U21[MW]|U18[MW]|[MW]|50[MW]))|((R|C|CL)U16[MW]))$', '1', 6, 0, 6, 0);
            CreateTargetFace($TourId, 3, 'White Peg', 'REG-^(([A-Z]{1,3}U(12|14)[MW])|((A|F|L|T|B|CB)U15[MW]))$', '1', 6, 0, 6, 0);
		    CreateTargetFace($TourId, 4, get_text('FieldPegRed', 'Install'), 'REG-^((R|C|CL)(U21[MW]|U18[MW]|[MW]|50[MW]))$', '1', 6, 0, 6, 0);
		break;
}

// create a first distance prototype
CreateDistanceInformation($TourId, $DistanceInfoArray, $tourDetNumEnds, 4);

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
