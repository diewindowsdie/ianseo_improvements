<?php
/*

Common setup for Field

*/

/**
 * Modified by helmut.poell@gmx.at
 * 2026-09-07
 */

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

// default Divisions
CreateStandardDivisions($TourId, 'FIELD', $SubRule);

// default Classes
CreateStandardClasses($TourId, $SubRule, $TourType);

// default Distances
switch($TourType) {
	case 9:
		CreateDistanceNew($TourId, $TourType, '%', array(array('12+12',0)));
		break;
	case 10:
	case 12:
		CreateDistanceNew($TourId, $TourType, '%', array(array('Unbekannt',0), array('Bekannt',0)));
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

// create Groups finals
// The use of this function is not clear, theese SubRules did never exist. I keep it unchanged, you never know.
switch($SubRule) {
	case 3:
		$query = "INSERT INTO Finals (FinEvent,FinMatchNo,FinTournament,FinDateTime) 
			SELECT EvCode,GrMatchNo, $TourId, " . StrSafe_DB(date('Y-m-d H:i')) . " 
			FROM Events 
			INNER JOIN Grids ON GrMatchNo in (".implode(',', getPoolMatchNos()).") AND EvTeamEvent='0' AND EvTournament=$TourId
			where EvElimType=3";
		$rs=safe_w_sql($query);
		break;
	case 4:
		$query = "INSERT INTO Finals (FinEvent,FinMatchNo,FinTournament,FinDateTime) 
			SELECT EvCode,GrMatchNo, $TourId, " . StrSafe_DB(date('Y-m-d H:i')) . " 
			FROM Events 
			INNER JOIN Grids ON GrMatchNo in (".implode(',', getPoolMatchNosWA()).") AND EvTeamEvent='0' AND EvTournament=$TourId
			where EvElimType=4";
		$rs=safe_w_sql($query);
		break;
}

$i=1;

// Default Target
switch($TourType) {
    case 9:
        CreateTargetFace($TourId, $i++, 'Grün', 'REG-^RU8|^RU10|CU8|^CU10|BU8|^BU10|^LU8|^LU10|^TU8|^TU10', '1', TGT_FIELD, 0);
        CreateTargetFace($TourId, $i++, 'Weiß', 'REG-^RU13|^RU15|^CU13|^CU15|^BU13|^BU15|^LU13|^LU15|^TU13|^TU15', '1', TGT_FIELD, 0);
        CreateTargetFace($TourId, $i++, 'Gelb', 'REG-^BU18|^LU18|^TU18|^LU21|^L50|^L65|^L[M|W|G]$', '1', TGT_FIELD, 0);
        CreateTargetFace($TourId, $i++, 'Blau', 'REG-^RU18|^CU18|^BU21|^B50|^B65|^B[M|W|G]$|^TU21|^T50|^T65|^T[M|W|G]$', '1', TGT_FIELD, 0);
        CreateTargetFace($TourId, $i++, 'Rot', 'REG-^RU21|^R50|^R65|^R[M|W|G]$|^CU21|^C50|^C65|^C[M|W|G]$', '1', TGT_FIELD, 0);
        break;
    case 10:
    case 12:
        CreateTargetFace($TourId, $i++, 'Weiß', 'REG-^RU13|^RU15|^CU13|^CU15|^BU13|^BU15|^LU13|^LU15|^TU13|^TU15', '1', TGT_FIELD, 0, TGT_FIELD, 0);
        CreateTargetFace($TourId, $i++, 'Gelb', 'REG-^BU18|^LU18|^TU18|^LU21|^L50|^L65|^L[M|W|G]$', '1', TGT_FIELD, 0, TGT_FIELD, 0);
        CreateTargetFace($TourId, $i++, 'Blau', 'REG-^RU18|^CU18|^BU21|^B50|^B65|^B[M|W|G]$|^TU21|^T50|^T65|^T[M|W|G]$', '1', TGT_FIELD, 0, TGT_FIELD, 0);
        CreateTargetFace($TourId, $i++, 'Rot', 'REG-^RU21|^R50|^R65|^R[M|W|G]$|^CU21|^C50|^C65|^C[M|W|G]$', '1', TGT_FIELD, 0, TGT_FIELD, 0);
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

