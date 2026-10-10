<?php

/*
	Tipos de competición:
	 1  Round FITA (WA 1440) - 4 distancias
	 3  Round 70/50 m (WA 70m Round) - 2 distancias
	 6  Sala 18 m - dos series de 18 m
	 9  Campo 12+12 - 1 recorrido
	10  Campo 24+24 - 2 recorridos
	12  Campo 12+12 - 2 recorridos
	11  3D - 1 recorrido
	13  3D - 2 recorridos
*/

$tourCollation = 'spanish';
$tourDetIocCode = 'ESP';
if(empty($SubRule)) {
	$SubRule='1';
}

/*
	Las divisiones de una subregla. Arco y Salud solo está en la de todas las
	clases y solo se tira en diana; en 3D no se tira recurvo.
*/
function StandardDivisions($SubRule, $TourType=0) {
	switch("$SubRule") {
		case '1':
			$Ret=array('R'=>'Recurvo', 'C'=>'Compuesto');
			break;
		case '2':
			$Ret=array('B'=>'Desnudo', 'T'=>'Tradicional', 'L'=>'Longbow');
			break;
		case '4':
		case '5':
			$Ret=array('R'=>'Recurvo', 'C'=>'Compuesto', 'B'=>'Desnudo');
			break;
		case '6':
			$Ret=array('R'=>'Recurvo', 'C'=>'Compuesto', 'B'=>'Desnudo', 'T'=>'Tradicional', 'L'=>'Longbow', 'AS'=>'Arco y Salud');
			break;
		default:
			$Ret=array('R'=>'Recurvo', 'C'=>'Compuesto', 'B'=>'Desnudo', 'T'=>'Tradicional', 'L'=>'Longbow');
			break;
	}
	if(in_array($TourType, array(9, 10, 11, 12, 13))) unset($Ret['AS']);
	if(in_array($TourType, array(11, 13))) unset($Ret['R']);
	return $Ret;
}

/** Arco y Salud no va por edades ni por sexo: dos clases mixtas. */
function HealthClasses() {
	return array(
		'1' => array('Con linfedema', 1, 100),
		'2' => array('Sin linfedema', 1, 100),
		);
}

/** Las clases de una división: Arco y Salud tiene las suyas. */
function DivisionClasses($div, $SubRule, $TourType=0) {
	return $div=='AS' ? HealthClasses() : StandardClasses($SubRule, $TourType);
}

/** Las dos clases de Arco y Salud. PHP guarda '1' y '2' como enteros. */
function IsHealthClass($pre) {
	return in_array((string)$pre, array('1', '2'), true);
}

/** Los códigos de clase de una división y grupo: mixta en Arco y Salud. */
function ClassIds($div, $pre) {
	return $div=='AS' ? array($pre) : array($pre.'M', $pre.'W');
}

function StandardClasses($SubRule, $TourType=0) {
	$Menores=array(
		'U21' => array('Sub 21', 18, 20),
		'U18' => array('Sub 18', 15, 17),
		'U15' => array('Sub 15', 13, 14),
		'U13' => array('Sub 13', 11, 12),
		'U11' => array('Sub 11', 9, 10),
		'U9'  => array('Sub 9', 1, 8),
		);
	$Absoluto=array('' => array('', 1, 100));
	$Plus50=array('50' => array('Plus 50', 50, 100));

	switch("$SubRule") {
		case '4':
			$Ret=array_slice($Menores, 0, 3, true);
			break;
		case '5':
			$Ret=$Menores;
			break;
		case '6':
			$Ret=$Absoluto + $Plus50 + $Menores;
			break;
		default:
			$Ret=$Absoluto;
			break;
	}

	// El Round FITA no se tira por debajo de Sub 15.
	if($TourType==1) {
		unset($Ret['U13'], $Ret['U11'], $Ret['U9']);
	}
	return $Ret;
}

function DivisionsIn($SubRule, $Divs, $TourType=0) {
	return array_values(array_intersect($Divs, array_keys(StandardDivisions($SubRule, $TourType))));
}

function ClassesIn($SubRule, $TourType, $Pres) {
	return array_values(array_intersect($Pres, array_keys(StandardClasses($SubRule, $TourType))));
}

function IsYoungest($pre) {
	return in_array($pre, array('U13', 'U11', 'U9'), true);
}


function StandardTarget($div, $pre, $Outdoor) {
	if($div=='AS') return $Outdoor ? array(TGT_OUT_FULL, 122) : array(TGT_IND_1_big10, 80);
	if($pre=='50') $pre='';
	if(IsYoungest($pre)) return array($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10, 122);
	if($Outdoor) {
		if($div=='C' or $div=='T') return array(TGT_OUT_5_big10, 80);
		return array(TGT_OUT_FULL, 122);
	}
	if($div=='R') return array($pre=='U15' ? TGT_IND_1_big10 : TGT_IND_6_big10, 40);
	if($div=='C') return array($pre=='U15' ? TGT_IND_1_small10 : TGT_IND_6_small10, 40);
	return array(TGT_IND_1_big10, 40);
}

function StandardEvent($div, $pre, $Outdoor) {
	if($div=='AS' or IsHealthClass($pre)) return ($div=='AS' and IsHealthClass($pre));
	if($pre==='' or $pre=='50') return true;
	if($div=='T' or $div=='L') return false;
	if($div=='B') return $Outdoor;
	return true;
}

function TargetName($tipo, $tam) {
	$reducidas=array(TGT_IND_6_big10, TGT_IND_6_small10, TGT_OUT_5_big10, TGT_OUT_6_big10);
	return (in_array($tipo, $reducidas) ? 'Reducida ' : 'Completa ').$tam;
}

function CreateStandardDivisions($TourId, $SubRule, $TourType=0) {
	$Outdoor=($TourType!=6);
	$i=1;
	foreach(StandardDivisions($SubRule, $TourType) as $id => $descr) {
		// una división sin ninguna prueba no pinta nada en la competición
		$hay=false;
		foreach(array_keys(DivisionClasses($id, $SubRule, $TourType)) as $pre) {
			if(StandardEvent($id, $pre, $Outdoor)) { $hay=true; break; }
		}
		if($hay) CreateDivision($TourId, $i++, $id, $descr);
	}
}

function CreateStandardClasses($TourId, $SubRule, $TourType=0) {
	$Outdoor=($TourType!=6);
	$Divs=array_keys(StandardDivisions($SubRule, $TourType));
	$Groups=StandardClasses($SubRule, $TourType);
	$Keys=array_keys($Groups);
	$i=1;

	// Arco y Salud tiene dos clases propias, mixtas y solo suyas
	if(in_array('AS', $Divs)) {
		foreach(HealthClasses() as $id => $c) {
			CreateClass($TourId, $i++, $c[1], $c[2], -1, $id, $id, $c[0], '1', 'AS');
		}
	}

	foreach($Keys as $ix => $pre) {
		list($name, $from, $to)=$Groups[$pre];

		// La clase dice en qué divisiones se tira cuando no están todas: el
		// desnudo de menores no existe en sala y así IANSEO no lo echa en falta.
		$permitidas=array();
		foreach($Divs as $div) {
			if(StandardEvent($div, $pre, $Outdoor)) $permitidas[]=$div;
		}
		$alDivision=(count($permitidas)==count($Divs) ? '' : implode(',', $permitidas));

		foreach(array('M'=>array(0, 'Hombre'), 'W'=>array(1, 'Mujer')) as $sx => $sex) {
			// clases válidas: la suya y todas las mayores, menos el Plus 50,
			// que es cosa de la edad y no una categoría a la que subir
			$chain=array();
			for($j=$ix; $j>=0; $j--) {
				if($j!=$ix and $Keys[$j]=='50') continue;
				$chain[]=$Keys[$j].$sx;
			}
			CreateClass($TourId, $i++, $from, $to, $sex[0], $pre.$sx, implode(',', $chain), trim($name.' '.$sex[1]), '1', $alDivision);
		}
	}
}

function StandardDistance($div, $pre, $Outdoor) {
	if($div=='AS') return $Outdoor ? 22 : 18;
	if($pre=='50') {
		return ($Outdoor and $div=='R') ? 60 : StandardDistance($div, '', $Outdoor);
	}
	$Pequenos=array('U13'=>30, 'U11'=>18, 'U9'=>12);
	if(!$Outdoor) {
		return ($pre=='U11' or $pre=='U9') ? 12 : 18;
	}
	if(isset($Pequenos[$pre])) {
		return $Pequenos[$pre];
	}
	$D=array(
		'R'=>array(''=>70, 'U21'=>70, 'U18'=>60, 'U15'=>40),
		'C'=>array(''=>50, 'U21'=>50, 'U18'=>50, 'U15'=>40),
		'B'=>array(''=>50, 'U21'=>50, 'U18'=>50, 'U15'=>30),
		'T'=>array(''=>30, 'U21'=>30, 'U18'=>25, 'U15'=>20),
		'L'=>array(''=>30, 'U21'=>30, 'U18'=>25, 'U15'=>20),
		);
	return $D[$div][$pre];
}

// Las cuatro distancias del Round FITA.
function FitaDistances($div, $pre, $sx) {
	if($div=='AS') {
		$d=array(22, 22, 22, 22);
	} elseif($pre=='U15') {
		$d=array(50, 40, 30, 20);
	} elseif($div=='T' or $div=='L') {
		$d=array(40, 30, 25, 20);
	} elseif($div=='B') {
		$d=array(60, 50, 40, 30);
	} elseif($pre=='U18') {
		$d=($sx=='M' ? array(70, 60, 50, 30) : array(60, 50, 40, 30));
	} else {
		$d=($sx=='M' ? array(90, 70, 50, 30) : array(70, 60, 50, 30));
	}
	$ret=array();
	foreach($d as $dist) {
		$ret[]=array($dist.' m', $dist);
	}
	return $ret;
}

// Round FITA, 70/50m y Sala

function CreateStandardEvents($TourId, $SubRule, $TourType) {
	$Outdoor=($TourType!=6);
	$FirstPhase=16;
	$TeamFirstPhase=4;

	// el compuesto se resuelve por puntos; el resto, por sets
	$Sets=array('R'=>1, 'C'=>0, 'B'=>1, 'T'=>1, 'L'=>1);

	$Divs=StandardDivisions($SubRule, $TourType);

	$i=1;
	foreach($Divs as $div => $divName) {
		foreach(DivisionClasses($div, $SubRule, $TourType) as $pre => $group) {
			if(!StandardEvent($div, $pre, $Outdoor)) continue;
			list($tipoDiana, $tamDiana)=StandardTarget($div, $pre, $Outdoor);
			$Options=array(
				'EvFinalFirstPhase' => $FirstPhase,
				'EvFinalTargetType' => $tipoDiana,
				'EvElimEnds'=>5,
				'EvElimArrows'=>3,
				'EvElimSO'=>1,
				'EvFinEnds'=>5,
				'EvFinArrows'=>3,
				'EvFinSO'=>1,
				'EvMatchMode'=>(isset($Sets[$div]) ? $Sets[$div] : 1),
				'EvMatchArrowsNo'=>240,
				'EvFinalAthTarget'=>240,
				'EvTargetSize'=>$tamDiana,
				'EvDistance'=>StandardDistance($div, $pre, $Outdoor),
				'EvGolds' => $Outdoor ? 'X' : '10',
				'EvXNine' => $Outdoor ? '10' : '9',
				'EvGoldsChars' => $Outdoor ? 'K' : 'L',
				'EvXNineChars' => $Outdoor ? 'L' : 'J',
				);
			$clName=trim($divName.' '.$group[0]);
			if($div=='AS') {
				// clase mixta: una sola prueba
				CreateEventNew($TourId, $div.$pre, $clName, $i++, $Options);
			} else {
				CreateEventNew($TourId, $div.$pre.'M', $clName.' Hombre', $i++, $Options);
				CreateEventNew($TourId, $div.$pre.'W', $clName.' Mujer', $i++, $Options);
			}
		}
	}

	// Equipos: Arco y Salud se tira solo individual
	$i=1;
	foreach($Divs as $div => $divName) {
		if($div=='AS') continue;
		foreach(DivisionClasses($div, $SubRule, $TourType) as $pre => $group) {
			if(!StandardEvent($div, $pre, $Outdoor)) continue;
			list($tipoDiana, $tamDiana)=StandardTarget($div, $pre, $Outdoor);
			$Options=array(
				'EvTeamEvent'=>1,
				'EvFinalFirstPhase' => $TeamFirstPhase,
				'EvFinalTargetType' => $tipoDiana,
				'EvElimEnds'=>4,
				'EvElimArrows'=>6,
				'EvElimSO'=>3,
				'EvFinEnds'=>4,
				'EvFinArrows'=>6,
				'EvFinSO'=>3,
				'EvMatchMode'=>$Sets[$div],
				'EvMaxTeamPerson'=>3,
				'EvTargetSize'=>$tamDiana,
				'EvDistance'=>StandardDistance($div, $pre, $Outdoor),
				'EvGolds' => $Outdoor ? 'X' : '10',
				'EvXNine' => $Outdoor ? '10' : '9',
				'EvGoldsChars' => $Outdoor ? 'K' : 'L',
				'EvXNineChars' => $Outdoor ? 'L' : 'J',
				);
			$clName=trim($divName.' '.$group[0]);
			CreateEventNew($TourId, $div.$pre.'M', 'Equipo '.$clName.' Hombre', $i++, $Options);
			CreateEventNew($TourId, $div.$pre.'W', 'Equipo '.$clName.' Mujer', $i++, $Options);

			// el mixto es un hombre y una mujer, con la misma diana
			$Options['EvMixedTeam']=1;
			$Options['EvMaxTeamPerson']=2;
			$Options['EvElimArrows']=4;
			$Options['EvElimSO']=2;
			$Options['EvFinArrows']=4;
			$Options['EvFinSO']=2;
			CreateEventNew($TourId, $div.$pre.'X', 'Equipo '.$clName.' Mixto', $i++, $Options);
		}
	}
}

function InsertStandardEvents($TourId, $SubRule, $TourType) {
	$Outdoor=($TourType!=6);
	foreach(StandardDivisions($SubRule, $TourType) as $div => $divName) {
		foreach(DivisionClasses($div, $SubRule, $TourType) as $pre => $group) {
			if(!StandardEvent($div, $pre, $Outdoor)) continue;
			if($div=='AS') {
				InsertClassEvent($TourId, 0, 1, "{$div}{$pre}", $div, "{$pre}");
				continue;
			}
			InsertClassEvent($TourId, 0, 1, "{$div}{$pre}M", $div, "{$pre}M");
			InsertClassEvent($TourId, 0, 1, "{$div}{$pre}W", $div, "{$pre}W");
			InsertClassEvent($TourId, 1, 3, "{$div}{$pre}M", $div, "{$pre}M");
			InsertClassEvent($TourId, 1, 3, "{$div}{$pre}W", $div, "{$pre}W");
			InsertClassEvent($TourId, 1, 1, "{$div}{$pre}X", $div, "{$pre}W");
			InsertClassEvent($TourId, 2, 1, "{$div}{$pre}X", $div, "{$pre}M");
		}
	}
}

// Campo y 3D

function CreateFieldEvents($TourId, $SubRule, $TourType) {
	$SettingsInd=array(
		'EvFinalFirstPhase' => 8,
		'EvFinalTargetType'=>TGT_FIELD,
		'EvElimEnds'=>6,
		'EvElimArrows'=>3,
		'EvElimSO'=>1,
		'EvFinEnds'=>4,
		'EvFinArrows'=>3,
		'EvFinSO'=>1,
		'EvFinalAthTarget'=>255,
		'EvMatchArrowsNo'=>FINAL_FROM_2,
		);
	$SettingsTeam=array(
		'EvTeamEvent' => 1,
		'EvFinalFirstPhase' => 4,
		'EvFinalTargetType'=>TGT_FIELD,
		'EvElimEnds'=>4,
		'EvElimArrows'=>3,
		'EvElimSO'=>3,
		'EvFinEnds'=>4,
		'EvFinArrows'=>3,
		'EvFinSO'=>3,
		'EvFinalAthTarget'=>255,
		'EvMatchArrowsNo'=>FINAL_FROM_2,
		);
	$SettingsMixedTeam=array(
		'EvTeamEvent' => 1,
		'EvMixedTeam' => 1,
		'EvFinalFirstPhase' => 4,
		'EvFinalTargetType'=>TGT_FIELD,
		'EvElimEnds'=>4,
		'EvElimArrows'=>4,
		'EvElimSO'=>2,
		'EvFinEnds'=>4,
		'EvFinArrows'=>4,
		'EvFinSO'=>2,
		'EvFinalAthTarget'=>15,
		'EvMatchArrowsNo'=>FINAL_FROM_2,
		);

	CreateCourseEvents($TourId, $SubRule, $TourType, $SettingsInd, $SettingsTeam, $SettingsMixedTeam);
}

function Create3DEvents($TourId, $SubRule, $TourType) {
	$SettingsInd=array(
		'EvFinalFirstPhase' => 8,
		'EvFinalTargetType' => TGT_3D,
		'EvElimEnds' => 6,
		'EvElimArrows' => 2,
		'EvElimSO' => 1,
		'EvFinEnds' => 4,
		'EvFinArrows' => 2,
		'EvFinSO' => 1,
		'EvFinalAthTarget' => MATCH_NO_SEP,
		'EvMatchArrowsNo' => FINAL_FROM_2,
		);
	$SettingsTeam=array(
		'EvTeamEvent' => 1,
		'EvFinalFirstPhase' => 4,
		'EvFinalTargetType' => TGT_3D,
		'EvElimEnds' => 4,
		'EvElimArrows' => 4,
		'EvElimSO' => 4,
		'EvFinEnds' => 4,
		'EvFinArrows' => 4,
		'EvFinSO' => 4,
		'EvFinalAthTarget' => MATCH_NO_SEP,
		'EvMatchArrowsNo' => FINAL_FROM_2,
		);
	$SettingsMixedTeam=array(
		'EvTeamEvent' => 1,
		'EvMixedTeam' => 1,
		'EvFinalFirstPhase' => 4,
		'EvFinalTargetType' => TGT_3D,
		'EvElimEnds' => 4,
		'EvElimArrows' => 4,
		'EvElimSO' => 2,
		'EvFinEnds' => 4,
		'EvFinArrows' => 4,
		'EvFinSO' => 2,
		'EvFinalAthTarget' => MATCH_NO_SEP,
		'EvMatchArrowsNo' => FINAL_FROM_2,
		);

	CreateCourseEvents($TourId, $SubRule, $TourType, $SettingsInd, $SettingsTeam, $SettingsMixedTeam);
}


// Las divisiones que tira una clase: tradicional y longbow no tienen menores.
function CourseDivisions($SubRule, $TourType, $pre) {
	$Ret=array();
	foreach(StandardDivisions($SubRule, $TourType) as $div => $divName) {
		if(StandardEvent($div, $pre, true)) $Ret[$div]=$divName;
	}
	return $Ret;
}

function CreateCourseEvents($TourId, $SubRule, $TourType, $SettingsInd, $SettingsTeam, $SettingsMixedTeam) {
	$Divs=StandardDivisions($SubRule, $TourType);
	$Groups=StandardClasses($SubRule, $TourType);

	$i=1;
	foreach($Divs as $div => $divName) {
		foreach($Groups as $pre => $group) {
			if(!StandardEvent($div, $pre, true)) continue;
			$clName=trim($divName.' '.$group[0]);
			CreateEventNew($TourId, $div.$pre.'M', $clName.' Hombre', $i++, $SettingsInd);
			CreateEventNew($TourId, $div.$pre.'W', $clName.' Mujer', $i++, $SettingsInd);
		}
	}

	// el equipo lleva un arquero de cada división que tira esa clase
	$i=1;
	foreach($Groups as $pre => $group) {
		$ClassDivs=CourseDivisions($SubRule, $TourType, $pre);
		$SettingsTeam['EvMaxTeamPerson']=count($ClassDivs);
		CreateEventNew($TourId, 'M'.$pre.'T', trim('Equipo '.$group[0]).' Hombre', $i++, $SettingsTeam);
		CreateEventNew($TourId, 'W'.$pre.'T', trim('Equipo '.$group[0]).' Mujer', $i++, $SettingsTeam);
		foreach($ClassDivs as $div => $divName) {
			CreateEventNew($TourId, $div.$pre.'X', trim('Equipo '.$divName.' '.$group[0]).' Mixto', $i++, $SettingsMixedTeam);
		}
	}
}

function InsertCourseEvents($TourId, $SubRule, $TourType) {
	foreach(StandardClasses($SubRule, $TourType) as $pre => $group) {
		$n=1;
		foreach(array_keys(CourseDivisions($SubRule, $TourType, $pre)) as $div) {
			InsertClassEvent($TourId, 0, 1, "{$div}{$pre}M", $div, "{$pre}M");
			InsertClassEvent($TourId, 0, 1, "{$div}{$pre}W", $div, "{$pre}W");
			// equipos: un arquero de cada división
			InsertClassEvent($TourId, $n, 1, "M{$pre}T", $div, "{$pre}M");
			InsertClassEvent($TourId, $n, 1, "W{$pre}T", $div, "{$pre}W");
			InsertClassEvent($TourId, 1, 1, "{$div}{$pre}X", $div, "{$pre}W");
			InsertClassEvent($TourId, 2, 1, "{$div}{$pre}X", $div, "{$pre}M");
			$n++;
		}
	}
}
