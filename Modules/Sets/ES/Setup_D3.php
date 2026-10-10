<?php

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

// Divisiones
CreateStandardDivisions($TourId, $SubRule, $TourType);

// Clases
CreateStandardClasses($TourId, $SubRule, $TourType);

// Recorridos
switch($TourType) {
	case 11:
		CreateDistanceNew($TourId, $TourType, '%', array(array('Recorrido', 0)));
		break;
	case 13:
		CreateDistanceNew($TourId, $TourType, '%', array(array('Recorr. 1', 0), array('Recorr. 2', 0)));
		break;
}

Create3DEvents($TourId, $SubRule, $TourType);

InsertCourseEvents($TourId, $SubRule, $TourType);

CreateFinals($TourId);

switch($TourType) {
	case 11:
		CreateTargetFace($TourId, 1, '~Default', '%', '1', 8, 0);
		break;
	case 13:
		CreateTargetFace($TourId, 1, '~Default', '%', '1', 8, 0, 8, 0);
		break;
}

CreateDistanceInformation($TourId, $DistanceInfoArray, $tourDetNumEnds, 6);

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
	);
UpdateTourDetails($TourId, $tourDetails);
