<?php

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

// Divisiones
CreateStandardDivisions($TourId, $SubRule, $TourType);

// Clases
CreateStandardClasses($TourId, $SubRule, $TourType);

// Recorridos por defecto
switch($TourType) {
	case 9:
		CreateDistanceNew($TourId, $TourType, '%', array(array('Recorrido', 0)));
		break;
	case 10:
		CreateDistanceNew($TourId, $TourType, '%', array(array('Sin marcar', 0), array('Marcadas', 0)));
		break;
	case 12:
		CreateDistanceNew($TourId, $TourType, '%', array(array('Recorr. 1', 0), array('Recorr. 2', 0)));
		break;
}

CreateFieldEvents($TourId, $SubRule, $TourType);

InsertCourseEvents($TourId, $SubRule, $TourType);

CreateFinals($TourId);

$rc=DivisionsIn($SubRule, array('R', 'C'), $TourType);
$b=DivisionsIn($SubRule, array('B'), $TourType);
$tl=DivisionsIn($SubRule, array('T', 'L'), $TourType);
// tradicional y longbow no tienen menores
$conMenores=DivisionsIn($SubRule, array('R', 'C', 'B'), $TourType);
$absolutas=ClassesIn($SubRule, $TourType, array('', '50'));
$mayores=ClassesIn($SubRule, $TourType, array('', '50', 'U21'));
$u18=ClassesIn($SubRule, $TourType, array('U18'));
$menores=ClassesIn($SubRule, $TourType, array('U15', 'U13', 'U11', 'U9'));

$claseRegExp=function($pres) {
	$sufijos=array();
	foreach($pres as $pre) $sufijos[]=$pre.'M';
	foreach($pres as $pre) $sufijos[]=$pre.'W';
	return '('.implode('|', $sufijos).')';
};

$PegRed = (count($rc) and count($mayores)) ? 'REG-^['.implode('', $rc).']'.$claseRegExp($mayores).'$' : '';

$azul=array();
if(count($rc) and count($u18)) $azul[]='^['.implode('', $rc).']'.$claseRegExp($u18).'$';
if(count($b) and (count($mayores) or count($u18))) $azul[]='^B'.$claseRegExp(array_merge($mayores, $u18)).'$';
if(count($tl) and count($absolutas)) $azul[]='^['.implode('', $tl).']'.$claseRegExp($absolutas).'$';
$PegBlue = count($azul) ? 'REG-'.implode('|', $azul) : '';

$PegYellow = (count($conMenores) and count($menores)) ? 'REG-^['.implode('', $conMenores).']('.implode('|', $menores).')[MW]$' : '';
switch($TourType) {
	case 9:
		$i=1;
		foreach(array('FieldPegYellow'=>$PegYellow, 'FieldPegBlue'=>$PegBlue, 'FieldPegRed'=>$PegRed) as $nombre => $reg) {
			if($reg!=='') CreateTargetFace($TourId, $i++, get_text($nombre, 'Install'), $reg, '1', 6, 0);
		}
		break;
	case 10:
	case 12:
		$i=1;
		foreach(array('FieldPegYellow'=>$PegYellow, 'FieldPegBlue'=>$PegBlue, 'FieldPegRed'=>$PegRed) as $nombre => $reg) {
			if($reg!=='') CreateTargetFace($TourId, $i++, get_text($nombre, 'Install'), $reg, '1', 6, 0, 6, 0);
		}
		break;
}

CreateDistanceInformation($TourId, $DistanceInfoArray, $tourDetNumEnds, 4);

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
