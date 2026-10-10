<?php

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

// Divisiones
CreateStandardDivisions($TourId, $SubRule, $TourType);

// Clases
CreateStandardClasses($TourId, $SubRule, $TourType);

// Distancias
switch($TourType) {
	case 1: // Round FITA - 4 distancias
		foreach(StandardDivisions($SubRule, $TourType) as $div => $divName) {
			foreach(DivisionClasses($div, $SubRule, $TourType) as $pre => $group) {
				if(!StandardEvent($div, $pre, true)) continue;
				foreach(ClassIds($div, $pre) as $cl) {
					CreateDistanceNew($TourId, $TourType, $div.$cl, FitaDistances($div, $pre, substr($cl, -1)));
				}
			}
		}
		break;
	case 3: // Round 70/50 m - dos distancias iguales
		foreach(StandardDivisions($SubRule, $TourType) as $div => $divName) {
			foreach(DivisionClasses($div, $SubRule, $TourType) as $pre => $group) {
				if(!StandardEvent($div, $pre, true)) continue;
				$d=StandardDistance($div, $pre, true);
				$patron=($div=='AS' ? $div.$pre : $div.$pre.'_');
				CreateDistanceNew($TourId, $TourType, $patron, array(array($d.'m-1', $d), array($d.'m-2', $d)));
			}
		}
		break;
	case 6: // Sala - dos series a la distancia de cada clase
		foreach(StandardDivisions($SubRule, $TourType) as $div => $divName) {
			foreach(DivisionClasses($div, $SubRule, $TourType) as $pre => $group) {
				if(!StandardEvent($div, $pre, false)) continue;
				$d=StandardDistance($div, $pre, false);
				$patron=($div=='AS' ? $div.$pre : $div.$pre.'_');
				CreateDistanceNew($TourId, $TourType, $patron, array(array($d.'m-1', $d), array($d.'m-2', $d)));
			}
		}
		break;
}

CreateStandardEvents($TourId, $SubRule, $TourType);

InsertStandardEvents($TourId, $SubRule, $TourType);

CreateFinals($TourId);

if($TourType==1) {
	CreateTargetFace($TourId, 1, '~Default', '%', '1', 5, 122, 5, 122, 5, 80, 10, 80);
	// opcionales
	CreateTargetFace($TourId, 2, '~Option1', '%', '',  5, 122, 5, 122, 5, 80,  5, 80);
	CreateTargetFace($TourId, 3, '~Option2', '%', '',  5, 122, 5, 122, 9, 80, 10, 80);
} else {
	$Caras=array();
	$Cuantas=array();
	foreach(StandardDivisions($SubRule, $TourType) as $div => $divName) {
		foreach(DivisionClasses($div, $SubRule, $TourType) as $pre => $group) {
			if(!StandardEvent($div, $pre, $TourType!=6)) continue;
			list($tipo, $tam)=StandardTarget($div, $pre, $TourType!=6);
			foreach(ClassIds($div, $pre) as $cl) {
				$Caras["$tipo|$tam"][$div][]=$cl;
				$Cuantas[$div]=(isset($Cuantas[$div]) ? $Cuantas[$div] : 0) + 1;
			}
		}
	}

	$repetidos=array();
	foreach($Caras as $clave => $x) {
		list($tipo, $tam)=explode('|', $clave);
		$n=TargetName($tipo, $tam);
		$repetidos[$n]=isset($repetidos[$n]);
	}

	$i=1;
	foreach($Caras as $clave => $porDivision) {
		list($tipo, $tam)=explode('|', $clave);
		$patrones=array();
		foreach($porDivision as $div => $clases) {
			if(count($clases)==$Cuantas[$div]) {
				$patrones[]='^'.$div;
			} else {
				$patrones[]='^'.$div.(count($clases)>1 ? '('.implode('|', $clases).')' : $clases[0]).'$';
			}
		}
		$nombre=TargetName($tipo, $tam);
		if($repetidos[$nombre]) $nombre.=' '.implode('', array_keys($porDivision));
		CreateTargetFace($TourId, $i++, $nombre, 'REG-'.implode('|', $patrones), '1', $tipo, $tam, $tipo, $tam);
	}

	// en sala, la completa de recurvo queda como opcional
	if($TourType==6 and count(DivisionsIn($SubRule, array('R')))) {
		CreateTargetFace($TourId, $i++, '~Option1', 'REG-^R', '', TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
	}
}

CreateDistanceInformation($TourId, $DistanceInfoArray, 24, 4);

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
