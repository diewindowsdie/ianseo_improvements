<?php

/*

STANDARD DEFINITIONS (Target Tournaments)

*/

// these go here as it is a "global" definition, used or not
$tourCollation = '';
$tourDetIocCode = 'RUS';
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
 * В случае если тип эвента поддерживается, вернет название упражнения из ЕВСК по стрельбе из лука (в скобках)
 * @param string $classWithDivision Код класса с указанием дивизиона
 * @param int $tournamentType Идентификатор типа соревнования. Определяет выбор названий упражнения.
 */
function getEventTitleSuffix(string $classWithDivision, $tournamentType): ?string {
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
            $eventDescriptions['RU14M']='КЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['RU14W']='КЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CM']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CW']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CU21M']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CU21W']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CU18M']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CU18W']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CU14M']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['CU14W']='БЛ - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BM']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BW']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BU21M']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BU21W']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BU18M']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BU18W']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BU14M']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            $eventDescriptions['BU14W']='КЛ - бесприцельный - 18 м (30+30 выстрелов) + финал';
            break;
        case 13:
            //3d - два круга
            $eventDescriptions['C']='3Д - БЛ - 5-45 м (квалификация + финал)';
            $eventDescriptions['B']='3Д - КЛ - 3-30 м (квалификация + финал)';
            $eventDescriptions['L']='3Д - длинный лук - 3-30 м (квалификация + финал)';
            $eventDescriptions['T']='3Д - составной лук - 3-30 м (квалификация + финал)';
            break;
        case 1001:
            $eventDescriptions['RM']='КЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['RW']='КЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['RU21M']='КЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['RU21W']='КЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['RU18M']='КЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['RU18W']='КЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['RU14M']='КЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['RU14W']='КЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['CM']='БЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['CW']='БЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['CU21M']='БЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['CU21W']='БЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['CU18M']='БЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['CU18W']='БЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['CU14M']='БЛ - 50м, 30м (72 выстрела)';
            $eventDescriptions['CU14W']='БЛ - 50м, 30м (72 выстрела)';
            break;
        case 1002:
            $eventDescriptions['RM']='КЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['RW']='КЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['RU21M']='КЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['RU21W']='КЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['RU18M']='КЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['RU18W']='КЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['RU14M']='КЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['RU14W']='КЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['CM']='БЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['CW']='БЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['CU21M']='БЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['CU21W']='БЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['CU18M']='БЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['CU18W']='БЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['CU14M']='БЛ - 12м (30+30 выстрелов)';
            $eventDescriptions['CU14W']='БЛ - 12м (30+30 выстрелов)';
            break;
    }
    $division = substr($classWithDivision, 0, 1);
    $eventSuffix = $eventDescriptions[$classWithDivision] ?? $eventDescriptions[$division];
    return isset($eventSuffix)
        ? '(' . $eventSuffix . ')'
        : null;
}

/**
 * Построить локализованное название эвента вида "Дивизион Класс"
 * @param string $classWithDivision Код класса с указанием дивизиона
 */
function getEventName(string $classWithDivision): ?string {
    return getDivisionName($classWithDivision[0]) . ' ' . getClassName(substr($classWithDivision, 1));
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
    //в 50+30 и 12+12 нет финалов
    if ($TourType == 1001 || $TourType == 1002) {
        $FirstPhase = 0;
        $TeamFirstPhase = 0;
    }
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
                CreateEventNew($TourId, 'RM',  getEventName('RM') ?? 'Recurve Men', $i++, $Options);
                setEventQualificationTableSuffix($TourId, $TourType,'RM');
                CreateEventNew($TourId, 'RW',  getEventName('RW') ?? 'Recurve Women', $i++, $Options, );
                setEventQualificationTableSuffix($TourId, $TourType,'RW');
            }
			CreateEventNew($TourId, 'RU21M', getEventName('RU21M') ?? 'Recurve Under 21 Men', $i++, $Options);
            setEventQualificationTableSuffix($TourId, $TourType,'RU21M');
			CreateEventNew($TourId, 'RU21W', getEventName('RU21W') ?? 'Recurve Under 21 Women', $i++, $Options);
            setEventQualificationTableSuffix($TourId, $TourType,'RU21W');
            $Options['EvDistance']=$DistanceRcm;
            CreateEventNew($TourId, 'RU18M', getEventName('RU18M') ?? 'Recurve Under 18 Men', $i++, $Options);
            setEventQualificationTableSuffix($TourId, $TourType,'RU18M');
            CreateEventNew($TourId, 'RU18W', getEventName('RU18W') ?? 'Recurve Under 18 Women', $i++, $Options);
            setEventQualificationTableSuffix($TourId, $TourType,'RU18W');
            if($allowU15) {
                $Options['EvDistance']=$DistanceU15;
                CreateEventNew($TourId, 'RU14M', getEventName('RU14M') ?? 'Recurve Under 14 Men', $i++, $Options);
                setEventQualificationTableSuffix($TourId, $TourType,'RU14M');
                CreateEventNew($TourId, 'RU14W', getEventName('RU14W') ?? 'Recurve Under 14 Women', $i++, $Options);
                setEventQualificationTableSuffix($TourId, $TourType,'RU14W');
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
                CreateEventNew($TourId, 'CM',  getEventName('CM') ?? 'Compound Men', $i++, $Options);
                setEventQualificationTableSuffix($TourId, $TourType,'CM');
                CreateEventNew($TourId, 'CW',  getEventName('CW') ?? 'Compound Women', $i++, $Options);
                setEventQualificationTableSuffix($TourId, $TourType,'CW');
            }
            CreateEventNew($TourId, 'CU21M', getEventName('CU21M') ?? 'Compound Under 21 Men', $i++, $Options);
            setEventQualificationTableSuffix($TourId, $TourType,'CU21M');
			CreateEventNew($TourId, 'CU21W', getEventName('CU21W') ?? 'Compound Under 21 Women', $i++, $Options);
            setEventQualificationTableSuffix($TourId, $TourType,'CU21W');
            $Options['EvDistance']=$DistanceCcm;
			CreateEventNew($TourId, 'CU18M', getEventName('CU18M') ?? 'Compound Under 18 Men', $i++, $Options);
            setEventQualificationTableSuffix($TourId, $TourType,'CU18M');
			CreateEventNew($TourId, 'CU18W', getEventName('CU18W') ?? 'Compound Under 18 Women', $i++, $Options);
            setEventQualificationTableSuffix($TourId, $TourType,'CU18W');
            if($allowU15) {
                $Options['EvDistance']=$DistanceU15;
                CreateEventNew($TourId, 'CU14M', getEventName('CU14M') ?? 'Compound Under 14 Men', $i++, $Options);
                setEventQualificationTableSuffix($TourId, $TourType,'CU14M');
                CreateEventNew($TourId, 'CU14W', getEventName('CU14W') ?? 'Compound Under 14 Women', $i++, $Options);
                setEventQualificationTableSuffix($TourId, $TourType,'CU14W');
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
                    CreateEventNew($TourId, 'BM', getEventName('BM') ?? 'Barebow Men', $i++, $Options);
                    setEventQualificationTableSuffix($TourId, $TourType,'BM');
                    CreateEventNew($TourId, 'BW', getEventName('BW') ?? 'Barebow Women', $i++, $Options);
                    setEventQualificationTableSuffix($TourId, $TourType,'BW');
                }
                CreateEventNew($TourId,'BU21M', getEventName('BU21M') ?? 'Barebow Under 21 Men', $i++, $Options);
                setEventQualificationTableSuffix($TourId, $TourType,'BU21M');
                CreateEventNew($TourId,'BU21W', getEventName('BU21W') ?? 'Barebow Under 21 Women', $i++, $Options);
                setEventQualificationTableSuffix($TourId, $TourType,'BU21W');
                CreateEventNew($TourId,'BU18M', getEventName('BU18M') ?? 'Barebow Under 18 Men', $i++, $Options);
                setEventQualificationTableSuffix($TourId, $TourType,'BU18M');
                CreateEventNew($TourId,'BU18W', getEventName('BU18W') ?? 'Barebow Under 18 Women', $i++, $Options);
                setEventQualificationTableSuffix($TourId, $TourType,'BU18W');
                if($allowU15) {
                    $Options['EvDistance']=$DistanceU15B;
                    CreateEventNew($TourId, 'BU14M', getEventName('BU14M') ?? 'Barebow Under 14 Men', $i++, $Options);
                    setEventQualificationTableSuffix($TourId, $TourType,'BU14M');
                    CreateEventNew($TourId, 'BU14W', getEventName('BU14W') ?? 'Barebow Under 14 Women', $i++, $Options);
                    setEventQualificationTableSuffix($TourId, $TourType,'BU14W');
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
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RM',  getEventName('RM') , 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);
            setEventQualificationTableSuffix($TourId, $TourType,'RM');
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RW',  getEventName('RW') , 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);
            setEventQualificationTableSuffix($TourId, $TourType,'RW');
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CM',  getEventName('CM') , 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
            setEventQualificationTableSuffix($TourId, $TourType,'CM');
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CW',  getEventName('CW') , 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
            setEventQualificationTableSuffix($TourId, $TourType,'CW');
            if($allowBB) {
                CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BM', getEventName('BM') , 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
                setEventQualificationTableSuffix($TourId, $TourType,'BM');
                CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BW', getEventName('BW') , 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
                setEventQualificationTableSuffix($TourId, $TourType,'BW');
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
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RM',  getEventName('RM'), 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR); //обратить внимание на CreateEvent
            setEventQualificationTableSuffix($TourId, $TourType,'RM');
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RW',  getEventName('RW'), 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);
            setEventQualificationTableSuffix($TourId, $TourType,'RW');
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RU21M', getEventName('RU21M'), 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);
            setEventQualificationTableSuffix($TourId, $TourType,'RU21M');
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetR, 5, 3, 1, 5, 3, 1, 'RU21W', getEventName('RU21W'), 1, 240, 240, 0, 0, '', '', $TargetSizeR, $DistanceR);
            setEventQualificationTableSuffix($TourId, $TourType,'RU21W');
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CM',  getEventName('CM'), 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
            setEventQualificationTableSuffix($TourId, $TourType,'CM');
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CW',  getEventName('CW'), 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
            setEventQualificationTableSuffix($TourId, $TourType,'CW');
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CU21M', getEventName('CU21M'), 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
            setEventQualificationTableSuffix($TourId, $TourType,'CU21M');
			CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetC, 5, 3, 1, 5, 3, 1, 'CU21W', getEventName('CU21W'), 0, 240, 240, 0, 0, '', '', $TargetSizeC, $DistanceC);
            setEventQualificationTableSuffix($TourId, $TourType,'CU21W');
            if($allowBB) {
                CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BM', getEventName('BM'), 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
                setEventQualificationTableSuffix($TourId, $TourType,'BM');
                CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BW', getEventName('BW'), 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
                setEventQualificationTableSuffix($TourId, $TourType,'BW');
                CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BU21M', getEventName('BU21M'), 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
                setEventQualificationTableSuffix($TourId, $TourType,'BU21M');
                CreateEvent($TourId, $i++, 0, 0, $FirstPhase, $TargetB, 5, 3, 1, 5, 3, 1, 'BU21W', getEventName('BU21W'), 1, 240, 240, 0, 0, '', '', $TargetSizeB, $DistanceB);
                setEventQualificationTableSuffix($TourId, $TourType,'BU21W');
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

require_once(__DIR__ .'/../FITA/lib-Field.php');

/*

3D DEFINITIONS (Target Tournaments)

*/

require_once(__DIR__ .'/lib-3D.php');

/*

Run Archery DEFINITIONS (Target Tournaments)

*/

require_once(__DIR__ .'/../FITA/lib-Run.php');

