<?php
/*
Common Setup for "Field/3D" Archery
*/

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__, 2) .'/lib.php');

// default Divisions
CreateStandardDivisions($TourId, 'FIELD_3D', $SubRule);

CreateStandardClasses($TourId, $SubRule, ($TourType==9 ? 'FIELD':'3D'));

// default pegs

switch($TourType) {
    case 9:
        CreateDistanceNew($TourId, $TourType, '%', array(array('Dráha',0)));
        CreateTargetFace($TourId, 1, 'Biely', 'REG-U13|^(D|H|T)L60|70', '1', TGT_FIELD, 0);
        CreateTargetFace($TourId, 2, 'Modrý', 'REG-^(OL|KL|HU)U18|^(D|H|T)L(M|W)', '1', TGT_FIELD, 0);
        CreateTargetFace($TourId, 3, 'Žltý', 'REG-^(D|H|T)L(U18|50)|^(OL|KL|HU)60', '1', TGT_FIELD, 0);
        CreateTargetFace($TourId, 4, 'Červený', 'REG-^(OL|KL|HU)(50)*(M|W)', '1', TGT_FIELD, 0);
        break;
    case 11:
        CreateDistanceNew($TourId, $TourType, '%', array(array('Dráha',0)));
        CreateTargetFace($TourId, 1, 'Biely', 'REG-U13|^(D|H|T)L(60|U18)|^HL70', '1', TGT_3D, 0);
        CreateTargetFace($TourId, 2, 'Modrý', 'REG-^(OL|KL|HU)(60|U18)|^(D|H|T)L(50)*(M|W)|^(O|K)L70', '1', TGT_3D, 0);
        CreateTargetFace($TourId, 4, 'Červený', 'REG-^(OL|KL|HU)(50)*(M|W)', '1', TGT_3D, 0);
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