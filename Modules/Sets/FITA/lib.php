<?php

/*

STANDARD DEFINITIONS (Target Tournaments)

*/

// these go here as it is a "global" definition, used or not
$tourCollation = '';
$tourDetIocCode = 'FITA';
if(empty($SubRule)) {
    $SubRule='1';
}

/**
 * Локализованное название дивизиона. Значения берутся из языкового конфига Tournament.
 * @param string $division Код дивизиона
 */
function getDivisionName($division): string {
    return get_text('Div-' . $division, 'Tournament');
}

/**
 * Локализованное название класса. Значения берутся из языкового конфига Tournament. Если указан флаг $is3D - берется значение для соревнований по 3D.
 * @param string $class Код класса
 * @param boolean $is3D Признак того, что значение нужно для соревнований по 3D
 */
function getClassName($class, $is3D=false): string {
    if ($is3D) {
        return get_text('CL-' . $class . '-3D', 'Tournament');
    }

    return get_text('CL-' . $class, 'Tournament');
}

/**
 * Локализованное название эвента. В случае если тип эвента поддерживается, состоит из локализованного названия дивизиона, класса и указанного в скобках названия упражнения из ЕВСК по стрельбе из лука.
 * @param string $classWithDivision Код класса с указанием дивизиона
 * @param int $tournamentType Идентификатор типа соревнования. Определяет выбор названий упражнения.
 * @return string Построенное название дивизиона, если имеются данные для указанного типа соревнования, иначе null.
 */
function getEventName($classWithDivision, $tournamentType): mixed {
    $eventDescriptions=array();
    switch ($tournamentType) {
        case 1:
            //M-1
            $eventDescriptions['RM']='КЛ - 90 м, 70 м, 50 м, 30 м (144 выстрелов) + 70 м финал';
            $eventDescriptions['CM']='БЛ - 90 м, 70 м, 50 м, 30 м (144 выстрелов) + 70 м финал';
            $eventDescriptions['RW']='КЛ - 70 м, 60 м, 50 м, 30 м (144 выстрелов) + 70 м финал';
            $eventDescriptions['CW']='БЛ - 70 м, 60 м, 50 м, 30 м (144 выстрелов) + 70 м финал';
            $eventDescriptions['RU21M']='КЛ - 90 м, 70 м, 50 м, 30 м (144 выстрелов) + 70 м финал';
            $eventDescriptions['CU21M']='БЛ - 90 м, 70 м, 50 м, 30 м (144 выстрелов) + 70 м финал';
            $eventDescriptions['RU21W']='КЛ - 70 м, 60 м, 50 м, 30 м (144 выстрелов) + 70 м финал';
            $eventDescriptions['CU21W']='БЛ - 70 м, 60 м, 50 м, 30 м (144 выстрелов) + 70 м финал';
            $eventDescriptions['RU18M']='КЛ - 70 м, 60 м, 50 м, 30 м (144 выстрелов) + 60 м финал';
            $eventDescriptions['CU18M']='БЛ - 70 м, 60 м, 50 м, 30 м (144 выстрелов) + 60 м финал';
            $eventDescriptions['RU18W']='КЛ - 60 м, 50 м, 40 м, 30 м (144 выстрелов) + 60 м финал';
            $eventDescriptions['CU18W']='БЛ - 60 м, 50 м, 40 м, 30 м (144 выстрелов) + 60 м финал';
            break;
        case 3:
            //50/70(60) m
            $eventDescriptions['RM']='КЛ - 70 м (36+36 выстрелов) + финал';
            $eventDescriptions['CM']='БЛ - 50 м (36+36 выстрелов) + финал';
            $eventDescriptions['RW']='КЛ - 70 м (36+36 выстрелов) + финал';
            $eventDescriptions['CW']='БЛ - 50 м (36+36 выстрелов) + финал';
            $eventDescriptions['RU21M']='КЛ - 70 м (36+36 выстрелов) + финал';
            $eventDescriptions['CU21M']='БЛ - 50 м (36+36 выстрелов) + финал';
            $eventDescriptions['RU21W']='КЛ - 70 м (36+36 выстрелов) + финал';
            $eventDescriptions['CU21W']='БЛ - 50 м (36+36 выстрелов) + финал';
            $eventDescriptions['RU18M']='КЛ - 60 м (36+36 выстрелов) + финал';
            $eventDescriptions['CU18M']='БЛ - 50 м (36+36 выстрелов) + финал';
            $eventDescriptions['RU18W']='КЛ - 60 м (36+36 выстрелов) + финал';
            $eventDescriptions['CU18W']='БЛ - 50 м (36+36 выстрелов) + финал';
            break;
        case 6:
            //18м две дистанции по 30 выстрелов
            $eventDescriptions['RM']='КЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['RW']='КЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['RU21M']='КЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['RU21W']='КЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['RU18M']='КЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['RU18W']='КЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CM']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CW']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CU21M']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CU21W']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CU18M']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CU18W']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BM']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BW']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BU21M']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BU21W']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BU18M']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BU18W']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            break;
        case 13:
            //3d - два круга
            $eventDescriptions['C']='3Д - БЛ - 5-45 м (квалификация + финал)';
            $eventDescriptions['B']='3Д - КЛ - 3-30 м (квалификация + финал)';
            $eventDescriptions['L']='3Д - длинный лук - 3-30 м (квалификация + финал)';
            $eventDescriptions['T']='3Д - составной лук - 3-30 м (квалификация + финал)';
            break;
    }
    $division = substr($classWithDivision, 0, 1);
    $eventNameWithoutClassAndDivision = $eventDescriptions[$classWithDivision] ?? $eventDescriptions[$division];
    return isset($eventNameWithoutClassAndDivision)
        ? getDivisionName($division) . ' ' . getClassName(substr($classWithDivision, 1)) . ' (' . $eventNameWithoutClassAndDivision . ')'
        : null;
}

function CreateStandardDivisions($TourId, $Type='FITA') {
	$i=1;
	if($Type!='3D') {
	    CreateDivision($TourId, $i++, 'R', getDivisionName('R'));
    }
	CreateDivision($TourId, $i++, 'C', getDivisionName('C'));
    if($Type!='FITA') {
        CreateDivision($TourId, $i++, 'B', getDivisionName('B'));
    }
    if($Type=='3D') {
		CreateDivision($TourId, $i++, 'L', getDivisionName('L'));
		CreateDivision($TourId, $i++, 'T', getDivisionName('T'));
	}
}

function CreateStandardClasses($TourId, $SubRule, $Type='FITA') {
    $i=1;
	switch($SubRule) {
		case '1':
			CreateClass($TourId, $i++, 11, 100, 0, 'M', 'M', getClassName('M'));
			CreateClass($TourId, $i++, 11, 100, 1, 'W', 'W', getClassName('W'));
			CreateClass($TourId, $i++, 11, 20, 0, 'U21M', 'U21M,M', getClassName('U21M'));
			CreateClass($TourId, $i++, 11, 20, 1, 'U21W', 'U21W,W', getClassName('U21W'));
            if(in_array($Type, [3,6,37])) {
                // 70m and 2x70m have U15 too
                CreateClass($TourId, $i++,  11, 17, 0, 'U18M', 'U18M,U21M,M', getClassName('U18M'));
                CreateClass($TourId, $i++,  11, 17, 1, 'U18W', 'U18W,U21W,W', getClassName('U18W'));
                CreateClass($TourId, $i++,  11, 13, 0, 'U14M', 'U14M,U18M,U21M,M', getClassName('U14M'));
                CreateClass($TourId, $i++,  11, 13, 1, 'U14W', 'U14W,U18W,U21W,W', getClassName('U14W'));
            } else {
                // only U18
                CreateClass($TourId, $i++,  11, 17, 0, 'U18M', 'U18M,U21M,M', getClassName('U18M'));
                CreateClass($TourId, $i++,  11, 17, 1, 'U18W', 'U18W,U21W,W', getClassName('U18W'));
            }
			CreateClass($TourId, $i++, 50,100, 0, '50M', '50M,M', '50+ Men');
			CreateClass($TourId, $i++, 50,100, 1, '50W', '50W,W', '50+ Women');
			break;
		case '2':
		case '5':
			CreateClass($TourId, 1, 11,100, 0, 'M', 'M', getClassName('M'));
			CreateClass($TourId, 2, 11,100, 1, 'W', 'W', getClassName('W'));
			break;
		case '3':
			CreateClass($TourId, 1, 11,100, 0, 'M', 'M', getClassName('M'));
			CreateClass($TourId, 2, 11,100, 1, 'W', 'W', getClassName('W'));
			CreateClass($TourId, 3, 11, 20, 0, 'U21M', 'U21M,M', getClassName('U21M'));
			CreateClass($TourId, 4, 11, 20, 1, 'U21W', 'U21W,W', getClassName('U21W'));
			break;
		case '4':
			CreateClass($TourId, $i++, 11, 20, 0, 'U21M', 'U21M', getClassName('U21M'));
			CreateClass($TourId, $i++, 11, 20, 1, 'U21W', 'U21W', getClassName('U21W'));
            if(in_array($Type, [3,6,37])) {
                // 70m and 2x70m have U15 too
                CreateClass($TourId, $i++,  11, 17, 0, 'U18M', 'U18M,U21M,M', getClassName('U18M'));
                CreateClass($TourId, $i++,  11, 17, 1, 'U18W', 'U18W,U21W,W', getClassName('U18W'));
                CreateClass($TourId, $i++,  11, 13, 0, 'U14M', 'U14M,U18M,U21M,M', getClassName('U14M'));
                CreateClass($TourId, $i++,  11, 13, 1, 'U14W', 'U14W,U18W,U21W,W', getClassName('U14W'));
            } else {
                // only U18
                CreateClass($TourId, $i++,  1, 17, 0, 'U18M', 'U18M,U21M,M', getClassName('U18M'));
                CreateClass($TourId, $i++,  1, 17, 1, 'U18W', 'U18W,U21W,W', getClassName('U18W'));
            }
			break;
	}
}

function CreateStandardEvents($TourId, $SubRule, $TourType) {
    $Outdoor=($TourType!=6);
    $allowBB=(in_array($TourType,array(3,6,7,8,37)));
    $allowU15=(in_array($TourType, [3,6,37]));
	$TargetR=($Outdoor?5:2);
	$TargetC=($Outdoor?($TourType==1 ? 5 : 9):4);
    $TargetB=($Outdoor?5:1);
	$TargetSizeR=($Outdoor ? 122 : 40);
	$TargetSizeC=($Outdoor ? ($TourType==1 ? 122 : 80) : 40);
    $TargetSizeB=($Outdoor ? 122 : 40);
	$DistanceR=($Outdoor ? 70 : 18);
	$DistanceRcm=($Outdoor ? 60 : 18);
	$DistanceU15=($Outdoor ? 40 : 12);
	$DistanceU15B=($Outdoor ? 30 : 12);
	$DistanceC=($Outdoor ? ($TourType==1 ? 70 : 50) : 18);
    $DistanceCcm=($Outdoor ? ($TourType==1 ? 60 : 50) : 18);
    $DistanceB=($Outdoor ? 50 : 18);
	$FirstPhase = ($Outdoor ? 48 : 16);
	$TeamFirstPhase = ($Outdoor ? 12 : 8);
    $Options=[
        'EvFinalFirstPhase' => $FirstPhase,
        'EvFinalTargetType' => $TargetR,
        'EvElimEnds'=>5,
        'EvElimArrows'=>3,
        'EvElimSO'=>1,
        'EvFinEnds'=>5,
        'EvFinArrows'=>3,
        'EvFinSO'=>1,
        'EvMatchMode'=>1,
        'EvMatchArrowsNo'=>240,
        'EvFinalAthTarget' => 240,
        'EvTargetSize' => $TargetSizeR,
        'EvDistance' => $DistanceR,
        'EvGolds' => $Outdoor ? '10+X' : '10',
        'EvXNine' => $Outdoor ? 'X' : '9',
        'EvGoldsChars' => $Outdoor ? 'KL' : 'L',
        'EvXNineChars' => $Outdoor ? 'K' : 'J',
        'EvCheckGolds' => 0,
        'EvCheckXNines' => 0,
    ];
	switch($SubRule) {
		case '1':
		case '4':
			$i=1;
            // RECURVE
            if($SubRule==1) {
                CreateEventNew($TourId, 'RM',  getEventName('RM', $TourType) ?? 'Recurve Men', $i++, $Options);
                CreateEventNew($TourId, 'RW',  getEventName('RW', $TourType) ?? 'Recurve Women', $i++, $Options);
            }
			CreateEventNew($TourId, 'RU21M', getEventName('RU21M', $TourType) ?? 'Recurve Under 21 Men', $i++, $Options);
			CreateEventNew($TourId, 'RU21W', getEventName('RU21W', $TourType) ?? 'Recurve Under 21 Women', $i++, $Options);
            $Options['EvDistance']=$DistanceRcm;
            CreateEventNew($TourId, 'RU18M', getEventName('RU18M', $TourType) ?? 'Recurve Under 18 Men', $i++, $Options);
            CreateEventNew($TourId, 'RU18W', getEventName('RU18W', $TourType) ?? 'Recurve Under 18 Women', $i++, $Options);
            if($allowU15) {
                $Options['EvDistance']=$DistanceU15;
                CreateEventNew($TourId, 'RU14M', getEventName('RU14M', $TourType) ?? 'Recurve Under 14 Men', $i++, $Options);
                CreateEventNew($TourId, 'RU14W', getEventName('RU14W', $TourType) ?? 'Recurve Under 14 Women', $i++, $Options);
            }
            if($SubRule==1) {
                $Options['EvDistance']=$DistanceRcm;
                CreateEventNew($TourId, 'R50M', 'Recurve 50+ Men', $i++, $Options);
                CreateEventNew($TourId, 'R50W', 'Recurve 50+ Women', $i++, $Options);
            }

            // COMPOUND
            $Options['EvMatchMode']=0;
            $Options['EvFinalTargetType']=$TargetC;
            $Options['EvTargetSize']=$TargetSizeC;
            $Options['EvDistance']=$DistanceC;
            if($SubRule==1) {
                CreateEventNew($TourId, 'CM',  getEventName('CM', $TourType) ?? 'Compound Men', $i++, $Options);
                CreateEventNew($TourId, 'CW',  getEventName('CW', $TourType) ?? 'Compound Women', $i++, $Options);
            }
            CreateEventNew($TourId, 'CU21M', getEventName('CU21M', $TourType) ?? 'Compound Under 21 Men', $i++, $Options);
			CreateEventNew($TourId, 'CU21W', getEventName('CU21W', $TourType) ?? 'Compound Under 21 Women', $i++, $Options);
            $Options['EvDistance']=$DistanceCcm;
			CreateEventNew($TourId, 'CU18M', getEventName('CU18M', $TourType) ?? 'Compound Under 18 Men', $i++, $Options);
			CreateEventNew($TourId, 'CU18W', getEventName('CU18W', $TourType) ?? 'Compound Under 18 Women', $i++, $Options);
            if($allowU15) {
                $Options['EvDistance']=$DistanceU15;
                CreateEventNew($TourId, 'CU14M', getEventName('CU14M', $TourType) ?? 'Compound Under 14 Men', $i++, $Options);
                CreateEventNew($TourId, 'CU14W', getEventName('CU14W', $TourType) ?? 'Compound Under 14 Women', $i++, $Options);
            }
            if($SubRule==1) {
                $Options['EvDistance']=$DistanceC;
                CreateEventNew($TourId, 'C50M', 'Compound 50+ Men', $i++, $Options);
                CreateEventNew($TourId, 'C50W', 'Compound 50+ Women', $i++, $Options);
            }

            // BAREBOW
			if($allowBB) {
                $Options['EvMatchMode']=1;
                $Options['EvFinalTargetType']=$TargetB;
                $Options['EvTargetSize']=$TargetSizeB;
                $Options['EvDistance']=$DistanceB;
                if($SubRule==1) {
                    CreateEventNew($TourId, 'BM', getEventName('BM', $TourType) ?? 'Barebow Men', $i++, $Options);
                    CreateEventNew($TourId, 'BW', getEventName('BW', $TourType) ?? 'Barebow Women', $i++, $Options);
                }
                CreateEventNew($TourId,'BU21M', getEventName('BU21M', $TourType) ?? 'Barebow Under 21 Men', $i++, $Options);
                CreateEventNew($TourId,'BU21W', getEventName('BU21W', $TourType) ?? 'Barebow Under 21 Women', $i++, $Options);
                CreateEventNew($TourId,'BU18M', getEventName('BU18M', $TourType) ?? 'Barebow Under 18 Men', $i++, $Options);
                CreateEventNew($TourId,'BU18W', getEventName('BU18W', $TourType) ?? 'Barebow Under 18 Women', $i++, $Options);
                if($allowU15) {
                    $Options['EvDistance']=$DistanceU15B;
                    CreateEventNew($TourId, 'BU14M', getEventName('BU14M', $TourType) ?? 'Barebow Under 14 Men', $i++, $Options);
                    CreateEventNew($TourId, 'BU14W', getEventName('BU14W', $TourType) ?? 'Barebow Under 14 Women', $i++, $Options);
                }
                if($SubRule==1) {
                    $Options['EvDistance'] = $DistanceB;
                    CreateEventNew($TourId, 'B50M', 'Barebow 50+ Men', $i++, $Options);
                    CreateEventNew($TourId, 'B50W', 'Barebow 50+ Women', $i++, $Options);
                }
            }

            // TEAMS
            // RECURVE
			$i=1;
            $Options['EvTeamEvent']=1;
            $Options['EvMatchArrowsNo']=0;
            $Options['EvFinalAthTarget']=0;
            $Options['EvFinalFirstPhase']=$TeamFirstPhase;
            $Options['EvElimEnds']=4;
            $Options['EvFinEnds']=4;
            $Options['EvMatchMode']=1;
            $Options['EvMaxTeamPerson']=3;
            $Options['EvMixedTeam']=0;
            $Options['EvFinalTargetType']=$TargetR;
            $Options['EvElimArrows']=6;
            $Options['EvElimSO']=3;
            $Options['EvFinArrows']=6;
            $Options['EvFinSO']=3;
            $Options['EvTargetSize']=$TargetSizeR;
            $Options['EvDistance']=$DistanceR;
            if($SubRule==1) {
                CreateEventNew($TourId, 'RM', 'Recurve Men Team', $i++, $Options);
                CreateEventNew($TourId, 'RW', 'Recurve Women Team', $i++, $Options);
            }
			CreateEventNew($TourId,'RU21M', 'Recurve Under 21 Men Team', $i++, $Options);
			CreateEventNew($TourId,'RU21W', 'Recurve Under 21 Women Team', $i++, $Options);
            $Options['EvDistance']=$DistanceRcm;
			CreateEventNew($TourId,'RU18M', 'Recurve Under 18 Men Team', $i++, $Options);
			CreateEventNew($TourId,'RU18W', 'Recurve Under 18 Women Team', $i++, $Options);
            if($allowU15) {
                $Options['EvDistance']=$DistanceU15;
                CreateEventNew($TourId,'RU14M', 'Recurve Under 14 Men Team', $i++, $Options);
                CreateEventNew($TourId,'RU14W', 'Recurve Under 14 Women Team', $i++, $Options);
            }
            if($SubRule==1) {
                $Options['EvDistance'] = $DistanceRcm;
                CreateEventNew($TourId, 'R50M', 'Recurve 50+ Men Team', $i++, $Options);
                CreateEventNew($TourId, 'R50W', 'Recurve 50+ Women Team', $i++, $Options);
            }

			if($Outdoor) {
                $Options['EvMixedTeam']=1;
                $Options['EvDistance']=$DistanceR;
                $Options['EvElimArrows']=4;
                $Options['EvElimSO']=2;
                $Options['EvFinArrows']=4;
                $Options['EvFinSO']=2;
                $Options['EvMaxTeamPerson']=2;
                if($SubRule==1) {
                    CreateEventNew($TourId, 'RX', 'Recurve Mixed Team', $i++, $Options);
                }
				CreateEventNew($TourId,'RU21X', 'Recurve Under 21 Mixed Team', $i++, $Options);
                $Options['EvDistance']=$DistanceRcm;
				CreateEventNew($TourId,'RU18X', 'Recurve Under 18 Mixed Team', $i++, $Options);
                if($allowU15) {
                    $Options['EvDistance']=$DistanceU15;
                    CreateEventNew($TourId,'RU14X', 'Recurve Under 14 Mixed Team', $i++, $Options);
                }
                if($SubRule==1) {
                    $Options['EvDistance'] = $DistanceRcm;
                    CreateEventNew($TourId, 'R50X', 'Recurve 50+ Mixed Team', $i++, $Options);
                }
			}

            // COMPOUND
            $Options['EvMatchMode']=0;
            $Options['EvMaxTeamPerson']=3;
            $Options['EvMixedTeam']=0;
            $Options['EvFinalTargetType']=$TargetC;
            $Options['EvElimArrows']=6;
            $Options['EvElimSO']=3;
            $Options['EvFinArrows']=6;
            $Options['EvFinSO']=3;
            $Options['EvTargetSize']=$TargetSizeC;
            $Options['EvDistance']=$DistanceC;
            if($SubRule==1) {
                CreateEventNew($TourId, 'CM', 'Compound Men Team', $i++, $Options);
                CreateEventNew($TourId, 'CW', 'Compound Women Team', $i++, $Options);
            }
			CreateEventNew($TourId,'CU21M', 'Compound Under 21 Men Team', $i++, $Options);
			CreateEventNew($TourId,'CU21W', 'Compound Under 21 Women Team', $i++, $Options);
			CreateEventNew($TourId,'CU18M', 'Compound Under 18 Men Team', $i++, $Options);
			CreateEventNew($TourId,'CU18W', 'Compound Under 18 Women Team', $i++, $Options);
            if($allowU15) {
                $Options['EvDistance']=$DistanceU15;
                CreateEventNew($TourId,'CU14M', 'Compound Under 14 Men Team', $i++, $Options);
                CreateEventNew($TourId,'CU14W', 'Compound Under 14 Women Team', $i++, $Options);
            }
            if($SubRule==1) {
                $Options['EvDistance'] = $DistanceC;
                CreateEventNew($TourId, 'C50M', 'Compound 50+ Men Team', $i++, $Options);
                CreateEventNew($TourId, 'C50W', 'Compound 50+ Women Team', $i++, $Options);
            }
			if($Outdoor) {
                $Options['EvMixedTeam']=1;
                $Options['EvDistance']=$DistanceC;
                $Options['EvElimArrows']=4;
                $Options['EvElimSO']=2;
                $Options['EvFinArrows']=4;
                $Options['EvFinSO']=2;
                $Options['EvMaxTeamPerson']=2;
                if($SubRule==1) {
                    CreateEventNew($TourId, 'CX', 'Compound Mixed Team', $i++, $Options);
                }
				CreateEventNew($TourId, 'CU21X', 'Compound Under 21 Mixed Team',$i++, $Options);
				CreateEventNew($TourId, 'CU18X', 'Compound Under 18 Mixed Team',$i++, $Options);
                if($allowU15) {
                    $Options['EvDistance']=$DistanceU15;
                    CreateEventNew($TourId,'CU14X', 'Compound Under 14 Mixed Team', $i++, $Options);
                }
                if($SubRule==1) {
                    $Options['EvDistance'] = $DistanceC;
                    CreateEventNew($TourId, 'C50X', 'Compound 50+ Mixed Team', $i++, $Options);
                }
			}

            if($allowBB) {
                $Options['EvMatchMode']=1;
                $Options['EvMaxTeamPerson']=3;
                $Options['EvMixedTeam']=0;
                $Options['EvFinalTargetType']=$TargetB;
                $Options['EvElimArrows']=6;
                $Options['EvElimSO']=3;
                $Options['EvFinArrows']=6;
                $Options['EvFinSO']=3;
                $Options['EvTargetSize']=$TargetSizeB;
                $Options['EvDistance']=$DistanceB;
                if($SubRule==1) {
                    CreateEventNew($TourId, 'BM', 'Barebow Men Team', $i++, $Options);
                    CreateEventNew($TourId, 'BW', 'Barebow Women Team', $i++, $Options);
                }
                CreateEventNew($TourId,'BU21M', 'Barebow Under 21 Men Team', $i++, $Options);
                CreateEventNew($TourId,'BU21W', 'Barebow Under 21 Women Team', $i++, $Options);
                CreateEventNew($TourId,'BU18M', 'Barebow Under 18 Men Team', $i++, $Options);
                CreateEventNew($TourId,'BU18W', 'Barebow Under 18 Women Team', $i++, $Options);
                if($allowU15) {
                    $Options['EvDistance']=$DistanceU15B;
                    CreateEventNew($TourId,'BU14M', 'Barebow Under 14 Men Team', $i++, $Options);
                    CreateEventNew($TourId,'BU14W', 'Barebow Under 14 Women Team', $i++, $Options);
                }
                if($SubRule==1) {
                    $Options['EvDistance'] = $DistanceB;
                    CreateEventNew($TourId, 'B50M', 'Barebow 50+ Men Team', $i++, $Options);
                    CreateEventNew($TourId, 'B50W', 'Barebow 50+ Women Team', $i++, $Options);
                }
                if ($Outdoor) {
                    $Options['EvMixedTeam']=1;
                    $Options['EvDistance']=$DistanceB;
                    $Options['EvElimArrows']=4;
                    $Options['EvElimSO']=2;
                    $Options['EvFinArrows']=4;
                    $Options['EvFinSO']=2;
                    $Options['EvMaxTeamPerson']=2;
                    if($SubRule==1) {
                        CreateEventNew($TourId, 'BX', 'Barebow Mixed Team', $i++, $Options);
                    }
                    CreateEventNew($TourId,'BU21X', 'Barebow Under 21 Mixed Team', $i++, $Options);
                    CreateEventNew($TourId,'BU18X', 'Barebow Under 18 Mixed Team', $i++, $Options);
                    if($allowU15) {
                        $Options['EvDistance']=$DistanceU15B;
                        CreateEventNew($TourId,'BU14X', 'Barebow Under 14 Mixed Team', $i++, $Options);
                    }
                    if($SubRule==1) {
                        $Options['EvDistance'] = $DistanceB;
                        CreateEventNew($TourId, 'B50X', 'Barebow 50+ Mixed Team', $i++, $Options);
                    }
                }
            }
            break;
		case '2':
		case '5':
			$i=1;
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RM',  getEventName('RM', $TourType) , 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RW',  getEventName('RW', $TourType) , 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CM',  getEventName('CM', $TourType) , 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CW',  getEventName('CW', $TourType) , 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
            if($allowBB) {
                CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BM', getEventName('BM', $TourType) , 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
                CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BW', getEventName('BW', $TourType) , 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
            }
            $i=1;
			CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetR, 4, 6, 3, 4, 6, 3, 'RM',  'Recurve Men Team', 1, 0, 0, 0, 0, '', '', $TargetSizeR, $DistanceR);
			CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetR, 4, 6, 3, 4, 6, 3, 'RW',  'Recurve Women Team', 1, 0, 0, 0, 0, '', '', $TargetSizeR, $DistanceR);
			if($Outdoor) {
				CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetR, 4, 4, 2, 4, 4, 2, 'RX',  'Recurve Mixed Team', 1, 0, 0, 0, 0, '', '', $TargetSizeR, $DistanceR);
			}
			CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetC, 4, 6, 3, 4, 6, 3, 'CM',  'Compound Men Team', 0, 0, 0, 0, 0, '', '', $TargetSizeC, $DistanceC);
			CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetC, 4, 6, 3, 4, 6, 3, 'CW',  'Compound Women Team', 0, 0, 0, 0, 0, '', '', $TargetSizeC, $DistanceC);
			if($Outdoor) {
				CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetC, 4, 4, 2, 4, 4, 2, 'CX',  'Compound Mixed Team', 0, 0, 0, 0, 0, '', '', $TargetSizeC, $DistanceC);
			}
            if($allowBB) {
                CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetB, 4, 6, 3, 4, 6, 3, 'BM', 'Barebow Men Team', 1, 0, 0, 0, 0, '', '', $TargetSizeB, $DistanceB);
                CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetB, 4, 6, 3, 4, 6, 3, 'BW', 'Barebow Women Team', 1, 0, 0, 0, 0, '', '', $TargetSizeB, $DistanceB);
                if ($Outdoor) {
                    CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetB, 4, 4, 2, 4, 4, 2, 'BX', 'Barebow Mixed Team', 1, 0, 0, 0, 0, '', '', $TargetSizeB, $DistanceB);
                }
            }
        break;
		case '3':
			$i=1;
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RM',  getEventName('RM', $TourType), 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RW',  getEventName('RW', $TourType), 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RU21M', getEventName('RU21M', $TourType), 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RU21W', getEventName('RU21W', $TourType), 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CM',  getEventName('CM', $TourType), 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CW',  getEventName('CW', $TourType), 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CU21M', getEventName('CU21M', $TourType), 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CU21W', getEventName('CU21W', $TourType), 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
            if($allowBB) {
                CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BM', getEventName('BM', $TourType), 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
                CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BW', getEventName('BW', $TourType), 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
                CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BU21M', getEventName('BU21M', $TourType), 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
                CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BU21W', getEventName('BU21W', $TourType), 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
            }
            $i=1;
			CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetR, 4, 6, 3, 4, 6, 3, 'RM',  'Recurve Men Team', 1, 0, 0, 0, 0, '', '', $TargetSizeR, $DistanceR);
			CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetR, 4, 6, 3, 4, 6, 3, 'RW',  'Recurve Women Team', 1, 0, 0, 0, 0, '', '', $TargetSizeR, $DistanceR);
			CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetR, 4, 6, 3, 4, 6, 3, 'RU21M', 'Recurve Under 21 Men Team', 1, 0, 0, 0, 0, '', '', $TargetSizeR, $DistanceR);
			CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetR, 4, 6, 3, 4, 6, 3, 'RU21W', 'Recurve Under 21 Women Team', 1, 0, 0, 0, 0, '', '', $TargetSizeR, $DistanceR);
			if($Outdoor) {
				CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetR, 4, 4, 2, 4, 4, 2, 'RX',  'Recurve Mixed Team', 1, 0, 0, 0, 0, '', '', $TargetSizeR, $DistanceR);
				CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetR, 4, 4, 2, 4, 4, 2, 'RU21X', 'Recurve Under 21 Mixed Team', 1, 0, 0, 0, 0, '', '', $TargetSizeR, $DistanceR);
			}
			CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetC, 4, 6, 3, 4, 6, 3, 'CM',  'Compound Men Team', 0, 0, 0, 0, 0, '', '', $TargetSizeC, $DistanceC);
			CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetC, 4, 6, 3, 4, 6, 3, 'CW',  'Compound Women Team', 0, 0, 0, 0, 0, '', '', $TargetSizeC, $DistanceC);
			CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetC, 4, 6, 3, 4, 6, 3, 'CU21M', 'Compound Under 21 Men Team', 0, 0, 0, 0, 0, '', '', $TargetSizeC, $DistanceC);
			CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetC, 4, 6, 3, 4, 6, 3, 'CU21W', 'Compound Under 21 Women Team', 0, 0, 0, 0, 0, '', '', $TargetSizeC, $DistanceC);
			if($Outdoor) {
				CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetC, 4, 4, 2, 4, 4, 2, 'CX',  'Compound Mixed Team', 0, 0, 0, 0, 0, '', '', $TargetSizeC, $DistanceC);
				CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetC, 4, 4, 2, 4, 4, 2, 'CU21X', 'Compound Under 21 Mixed Team', 0, 0, 0, 0, 0, '', '', $TargetSizeC, $DistanceC);
			}
            if($allowBB) {
                CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetB, 4, 6, 3, 4, 6, 3, 'BM', 'Barebow Men Team', 1, 0, 0, 0, 0, '', '', $TargetSizeB, $DistanceB);
                CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetB, 4, 6, 3, 4, 6, 3, 'BW', 'Barebow Women Team', 1, 0, 0, 0, 0, '', '', $TargetSizeB, $DistanceB);
                CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetB, 4, 6, 3, 4, 6, 3, 'BU21M', 'Barebow Under 21 Men Team', 1, 0, 0, 0, 0, '', '', $TargetSizeB, $DistanceB);
                CreateEvent($TourId, $i++, 1, 0, $TeamFirstPhase, $TargetB, 4, 6, 3, 4, 6, 3, 'BU21W', 'Barebow Under 21 Women Team', 1, 0, 0, 0, 0, '', '', $TargetSizeB, $DistanceB);
                if ($Outdoor) {
                    CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetB, 4, 4, 2, 4, 4, 2, 'BX', 'Barebow Mixed Team', 1, 0, 0, 0, 0, '', '', $TargetSizeB, $DistanceB);
                    CreateEvent($TourId, $i++, 1, 1, $TeamFirstPhase, $TargetB, 4, 4, 2, 4, 4, 2, 'BU21X', 'Barebow Under 21 Mixed Team', 1, 0, 0, 0, 0, '', '', $TargetSizeB, $DistanceB);
                }
            }
			break;
	}
}

function InsertStandardEvents($TourId, $SubRule) {
    $divs=['R','C','B'];
	switch($SubRule) {
		case '1':
            $cls=['','U21','U18','U14','50'];
			break;
		case '2':
		case '5':
            $cls=[''];
			break;
		case '3':
            $cls=['','U21'];
			break;
		case '4':
            $cls=['U21','U18','U14'];
			break;
	}
    foreach($divs as $div) {
        foreach($cls as $cl) {
            InsertClassEvent($TourId, 0, 1, "{$div}{$cl}M", $div, "{$cl}M");
            InsertClassEvent($TourId, 0, 1, "{$div}{$cl}W", $div, "{$cl}W");
            InsertClassEvent($TourId, 1, 3, "{$div}{$cl}M", $div, "{$cl}M");
            InsertClassEvent($TourId, 1, 3, "{$div}{$cl}W", $div, "{$cl}W");
            InsertClassEvent($TourId, 1, 1, "{$div}{$cl}X",  $div,  "{$cl}W");
            InsertClassEvent($TourId, 2, 1, "{$div}{$cl}X",  $div,  "{$cl}M");
        }
    }
}

require_once("Common/Lib/Normative/Normative.php");
function CreateStandardSubclasses($tournamentId, $subclassesSet="SportIdent") {
    $fieldName = "codeSportIdent";
    if ($subclassesSet == "LetterCodes") {
        $fieldName = "codeLetters";
    }

    CreateSubClass($tournamentId, Normative::Esteemed["order"], Normative::Esteemed[$fieldName], Normative::Esteemed["name"]);
    CreateSubClass($tournamentId, Normative::International["order"], Normative::International[$fieldName], Normative::International["name"]);
    CreateSubClass($tournamentId, Normative::Master["order"], Normative::Master[$fieldName], Normative::Master["name"]);
    CreateSubClass($tournamentId, Normative::Candidate["order"], Normative::Candidate[$fieldName], Normative::Candidate["name"]);
    CreateSubClass($tournamentId, Normative::First["order"], Normative::First[$fieldName], Normative::First["name"]);
    CreateSubClass($tournamentId, Normative::Second["order"], Normative::Second[$fieldName], Normative::Second["name"]);
    CreateSubClass($tournamentId, Normative::Third["order"], Normative::Third[$fieldName], Normative::Third["name"]);
    CreateSubClass($tournamentId, Normative::FirstJunior["order"], Normative::FirstJunior[$fieldName], Normative::FirstJunior["name"]);
    CreateSubClass($tournamentId, Normative::SecondJunior["order"], Normative::SecondJunior[$fieldName], Normative::SecondJunior["name"]);
    CreateSubClass($tournamentId, Normative::ThirdJunior["order"], Normative::ThirdJunior[$fieldName], Normative::ThirdJunior["name"]);
    CreateSubClass($tournamentId, Normative::None["order"], Normative::None[$fieldName], Normative::None["nameForExisting"]);
}

/*

FIELD DEFINITIONS (Target Tournaments)

*/

require_once(dirname(__FILE__).'/lib-Field.php');

/*

3D DEFINITIONS (Target Tournaments)

*/

require_once(dirname(__FILE__).'/lib-3D.php');

/*

Run Archery DEFINITIONS (Target Tournaments)

*/

require_once(dirname(__FILE__).'/lib-Run.php');

