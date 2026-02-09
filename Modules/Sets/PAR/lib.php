<?php

// these go here as it is a "global" definition, used or not
$tourCollation = '';
$tourDetIocCode = '';
if(empty($SubRule)) {
    $SubRule='1';
}

function CreateStandardDivisions($TourId, $SubRule, $Type='FITA') {
	$i=1;
    CreateDivision($TourId, $i++, 'PR', 'Para Recurve', '1', 'PR', 'PR', 1);
    CreateDivision($TourId, $i++, 'PC', 'Para Compound', '1', 'PC', 'PC', 1);
    if($SubRule == '3') {
        CreateDivision($TourId, $i++, 'R', 'Recurve', '1', 'R', 'R', 0);
        CreateDivision($TourId, $i++, 'C', 'Compound', '1', 'C', 'C', 0);
    }
	CreateDivision($TourId, $i++, 'W1', 'W1','1','W1','W1',1);
	CreateDivision($TourId, $i++, 'VI', 'Visually Impaired','1','VI','VI',1);
}

function CreateStandardClasses($TourId, $SubRule) {
    $i=1;
	CreateClass($TourId, $i++, ($SubRule=='2'? 21 : 1),100, 0, 'M', 'M', 'Men', 1, ($SubRule==3 ? 'R,C,' : '') . 'PC,PR,W1','M','M',1);
	CreateClass($TourId, $i++, ($SubRule=='2'? 21 : 1),100, 1, 'W', 'W', 'Women', 1, ($SubRule==3 ? 'R,C,' : '') . 'PC,PR,W1','W','W',1);
    if($SubRule == '2') {
        CreateClass($TourId, $i++, 1, 20, 0, 'U21M', 'U21M,M', 'Under 21 Men', 1, ($SubRule == 3 ? 'R,C,' : '') . 'PC,PR,W1', 'U21M', 'U21M', 1);
        CreateClass($TourId, $i++, 1, 20, 1, 'U21W', 'U21W,W', 'Under 21 Women', 1, ($SubRule == 3 ? 'R,C,' : '') . 'PC,PR,W1', 'U21W', 'U21W', 1);
    }
    CreateClass($TourId, $i++, ($SubRule=='2'? 21 : 1),100, -1, '1', '1', '1', 1, 'VI','1','1',1);
	CreateClass($TourId, $i++, ($SubRule=='2'? 21 : 1),100, -1, '2', '2', '2', 1, 'VI','2','2',1);
    if($SubRule == '2') {
        CreateClass($TourId, $i++, 1, 20, -1, '1U21', '1,1U21', '1 Under 21', 1, 'VI', '1U21', '1U21', 1);
        CreateClass($TourId, $i++, 1, 20, -1, '2U21', '2,2U21', '2 Under 21', 1, 'VI', '23U21', '2U21', 1);
    }
}

function CreateStandardEvents($TourId, $SubRule, $Outdoor=true) {
	$TargetR=($Outdoor ? TGT_OUT_FULL : TGT_IND_6_big10);
	$TargetC=($Outdoor ? TGT_OUT_5_big10 : TGT_IND_6_small10);
    $TargetW1=($Outdoor? TGT_OUT_FULL : TGT_IND_1_big10);
	$TargetSizeR=($Outdoor ? 122 : 40);
	$TargetSizeC=($Outdoor ? 80 : 40);
	$TargetSizeV=($Outdoor ? 80 : 60);
	$DistanceR=($Outdoor ? 70 : 18);
	$DistanceC=($Outdoor ? 50 : 18);
	$DistanceV=($Outdoor ? 30 : 18);

	$Settings=array(
		'EvElimEnds'=>5,
		'EvElimArrows'=>3,
		'EvElimSO'=>1,
		'EvFinEnds'=>5,
		'EvFinArrows'=>3,
		'EvFinSO'=>1,
		'EvFinalAthTarget'=>240,
		'EvMatchArrowsNo'=>240,
		'EvIsPara'=>1,
		'EvMatchMode'=>1,
		'EvFinalFirstPhase' => 16,
		'EvFinalTargetType'=>$TargetR,
		'EvTargetSize'=>$TargetSizeR,
		'EvDistance'=>$DistanceR,
	);

	$i=1;
	CreateEventNew($TourId, 'PRM', 'Para Recurve Men', $i++, $Settings);
	CreateEventNew($TourId, 'PRW', 'Para Recurve Women', $i++, $Settings);
    if($SubRule=='3') {
        $Settings['EvIsPara']=0;
        CreateEventNew($TourId, 'RM', 'Recurve Men', $i++, $Settings);
        CreateEventNew($TourId, 'RW', 'Recurve Women', $i++, $Settings);
    }
    $Settings['EvIsPara']=1;
	$Settings['EvMatchMode']=0;
	$Settings['EvFinalFirstPhase']=32;
	$Settings['EvFinalTargetType']=$TargetC;
	$Settings['EvTargetSize']=$TargetSizeC;
	$Settings['EvDistance']=$DistanceC;
	CreateEventNew($TourId, 'PCM', 'Para Compound Men', $i++, $Settings);
    $Settings['EvFinalFirstPhase']=16;
    CreateEventNew($TourId, 'PCW', 'Para Compound Women', $i++, $Settings);
    if($SubRule=='3') {
        $Settings['EvIsPara']=0;
        $Settings['EvFinalFirstPhase']=32;
        CreateEventNew($TourId, 'CM', 'Compound Men', $i++, $Settings);
        $Settings['EvFinalFirstPhase']=16;
        CreateEventNew($TourId, 'CW', 'Compound Women', $i++, $Settings);
    }
    $Settings['EvIsPara']=1;
    $Settings['EvMatchMode']=1;
	$Settings['EvFinalFirstPhase']=8;
	$Settings['EvFinalTargetType']=$TargetW1;
	CreateEventNew($TourId, 'W1M', 'W1 Men', $i++, $Settings);
	CreateEventNew($TourId, 'W1W', 'W1 Women', $i++, $Settings);
	$Settings['EvFinalFirstPhase']=4;
	$Settings['EvTargetSize']=$TargetSizeV;
	$Settings['EvDistance']=$DistanceV;
	CreateEventNew($TourId, 'VI1', 'Visually Impaired 1', $i++, $Settings);
	CreateEventNew($TourId, 'VI2', 'Visually Impaired 2', $i++, $Settings);
    //If U21, add them
    if($SubRule=='2') {
        $Settings['EvIsPara']=1;
        $Settings['EvMatchMode']=1;
        $Settings['EvFinalTargetType']=TGT_IND_6_big10;
        $Settings['EvTargetSize']=40;
        $Settings['EvDistance']=18;

        $Settings['EvFinalFirstPhase'] = 16;
        CreateEventNew($TourId, 'PRU21M', 'Para Recurve U21 Men', $i++, $Settings);
        CreateEventNew($TourId, 'PRU21W', 'Para Recurve U21 Women', $i++, $Settings);

        $Settings['EvMatchMode']=0;
        $Settings['EvFinalTargetType']=TGT_IND_6_small10;
        $Settings['EvFinalFirstPhase'] = 16;
        CreateEventNew($TourId, 'PCU21M', 'Para Compound U21 Men', $i++, $Settings);
        CreateEventNew($TourId, 'PCU21W', 'Para Compound U21 Women', $i++, $Settings);

        $Settings['EvMatchMode']=1;
        $Settings['EvFinalTargetType']=TGT_IND_1_big10;
        $Settings['EvTargetSize']=60;
        $Settings['EvFinalFirstPhase']=8;
        CreateEventNew($TourId, 'W1U21M', 'W1 U21 Men W1', $i++, $Settings);
        CreateEventNew($TourId, 'W1U21W', 'W1 U21 Women W1', $i++, $Settings);
        $Settings['EvFinalFirstPhase']=4;
        CreateEventNew($TourId, 'VI1U21', 'Visually Impaired 1 U21', $i++, $Settings);
        CreateEventNew($TourId, 'VI2U21', 'Visually Impaired 2 U21', $i++, $Settings);

    }
	//Team
    $i=1;
	$Settings['EvTeamEvent']=1;
	$Settings['EvFinalAthTarget']=0;
	$Settings['EvElimEnds']=4;
	$Settings['EvElimArrows']=4;
	$Settings['EvElimSO']=2;
	$Settings['EvFinEnds']=4;
	$Settings['EvFinArrows']=4;
	$Settings['EvFinSO']=2;
	$Settings['EvMatchArrowsNo']=0;
	$Settings['EvMatchMode']=1;
	$Settings['EvFinalFirstPhase']=8;
	$Settings['EvFinalTargetType']=$TargetR;
	$Settings['EvTargetSize']=$TargetSizeR;
	$Settings['EvDistance']=$DistanceR;
	CreateEventNew($TourId, 'PRM', 'Para Recurve Men Doubles', $i++, $Settings);
	CreateEventNew($TourId, 'PRW', 'Para Recurve Women Doubles', $i++, $Settings);
    if($SubRule=='3') {
        $Settings['EvIsPara']=0;
        $Settings['EvElimArrows']=6;
        $Settings['EvElimSO']=3;
        $Settings['EvFinArrows']=6;
        $Settings['EvFinSO']=3;
        CreateEventNew($TourId, 'RM', 'Recurve Men Team', $i++, $Settings);
        CreateEventNew($TourId, 'RW', 'Recurve Women Team', $i++, $Settings);

    }
    $Settings['EvIsPara']=1;
    $Settings['EvMixedTeam']=1;
    $Settings['EvElimArrows']=4;
    $Settings['EvElimSO']=2;
    $Settings['EvFinArrows']=4;
    $Settings['EvFinSO']=2;
    CreateEventNew($TourId, 'PRX', 'Para Recurve Mixed Team', $i++, $Settings);
    if($SubRule=='3') {
        $Settings['EvIsPara']=0;
        CreateEventNew($TourId, 'RX', 'Recurve Mixed Team', $i++, $Settings);
    }
    $Settings['EvIsPara']=1;
    $Settings['EvMixedTeam']=0;
    $Settings['EvMatchMode']=0;
    $Settings['EvTargetSize']=$TargetSizeC;
    $Settings['EvDistance']=$DistanceC;
    $Settings['EvFinalTargetType']=$TargetC;
    CreateEventNew($TourId, 'PCM', 'Para Compound Men Doubles', $i++, $Settings);
    CreateEventNew($TourId, 'PCW', 'Para Compound Women Doubles', $i++, $Settings);
    if($SubRule=='3') {
        $Settings['EvIsPara']=0;
        $Settings['EvElimArrows']=6;
        $Settings['EvElimSO']=3;
        $Settings['EvFinArrows']=6;
        $Settings['EvFinSO']=3;
        CreateEventNew($TourId, 'CM', 'Compound Men Team', $i++, $Settings);
        CreateEventNew($TourId, 'CW', 'Compound Women Team', $i++, $Settings);
    }
    $Settings['EvIsPara']=1;
    $Settings['EvMixedTeam']=1;
    $Settings['EvElimArrows']=4;
    $Settings['EvElimSO']=2;
    $Settings['EvFinArrows']=4;
    $Settings['EvFinSO']=2;
    CreateEventNew($TourId, 'PCX', 'Para Compound Mixed Team', $i++, $Settings);
    if($SubRule=='3') {
        $Settings['EvIsPara']=0;
        CreateEventNew($TourId, 'CX', 'Compound Mixed Team', $i++, $Settings);
    }
    $Settings['EvMatchMode']=1;
    $Settings['EvIsPara']=1;
    $Settings['EvMixedTeam']=0;
    $Settings['EvFinalFirstPhase']=4;
	$Settings['EvFinalTargetType']=$TargetW1;
	CreateEventNew($TourId, 'W1M', 'W1 Men Doubles', $i++, $Settings);
	CreateEventNew($TourId, 'W1W', 'W1 Women Doubles', $i++, $Settings);
    $Settings['EvMixedTeam']=1;
    CreateEventNew($TourId, 'W1X', 'W1 Mixed Team', $i++, $Settings);

    //If U21, add them
    if($SubRule=='2') {
        $Settings['EvIsPara']=1;
        $Settings['EvMatchMode']=1;
        $Settings['EvMixedTeam']=0;
        $Settings['EvFinalFirstPhase']=4;
        $Settings['EvFinalTargetType']=TGT_IND_6_big10;
        $Settings['EvTargetSize']=40;
        $Settings['EvDistance']=18;
        CreateEventNew($TourId, 'PRU21M', 'Para Recurve U21 Men Doubles', $i++, $Settings);
        CreateEventNew($TourId, 'PRU21W', 'Para Recurve U21 Women Doubles', $i++, $Settings);
        $Settings['EvMixedTeam']=1;
        CreateEventNew($TourId, 'PRU21X', 'Para Recurve U21 Mixed Team', $i++, $Settings);
        $Settings['EvMixedTeam']=0;
        $Settings['EvMatchMode']=0;
        CreateEventNew($TourId, 'PCU21M', 'Para Compound U21 Men Doubles', $i++, $Settings);
        CreateEventNew($TourId, 'PCU21W', 'Para Compound U21 Women Doubles', $i++, $Settings);
        $Settings['EvMixedTeam']=1;
        CreateEventNew($TourId, 'PCU21X', 'Para Compound U21 Mixed Team', $i++, $Settings);
        $Settings['EvMixedTeam']=0;
        $Settings['EvMatchMode']=1;
        $Settings['EvFinalTargetType']=TGT_IND_1_big10;
        $Settings['EvTargetSize']=60;
        CreateEventNew($TourId, 'W1U21M', 'W1 U21 Men Doubles', $i++, $Settings);
        CreateEventNew($TourId, 'W1U21W', 'W1 U21 Women Doubles', $i++, $Settings);
        $Settings['EvMixedTeam']=1;
        CreateEventNew($TourId, 'W1U21X', 'W1 U21 Mixed Team', $i++, $Settings);
    }
}

function InsertStandardEvents($TourId, $SubRule, $Outdoor=true) {
    InsertClassEvent($TourId, 0, 1, 'PRM', 'PR', 'M');
    InsertClassEvent($TourId, 0, 1, 'PRW', 'PR', 'W');
    InsertClassEvent($TourId, 0, 1, 'PCM', 'PC', 'M');
    InsertClassEvent($TourId, 0, 1, 'PCW', 'PC', 'W');
	if($SubRule=='3') {
        InsertClassEvent($TourId, 0, 1, 'RM', 'R', 'M');
        InsertClassEvent($TourId, 0, 1, 'RW', 'R', 'W');
        InsertClassEvent($TourId, 0, 1, 'CM', 'C', 'M');
        InsertClassEvent($TourId, 0, 1, 'CW', 'C', 'W');
    }
	InsertClassEvent($TourId, 0, 1, 'W1M', 'W1', 'M');
	InsertClassEvent($TourId, 0, 1, 'W1W', 'W1', 'W');
	InsertClassEvent($TourId, 0, 1, 'VI1', 'VI', '1');
	InsertClassEvent($TourId, 0, 1, 'VI2', 'VI', '2');
    InsertClassEvent($TourId, 1, 2, 'PRM', 'PR', 'M');
    InsertClassEvent($TourId, 1, 2, 'PRW', 'PR', 'W');
    InsertClassEvent($TourId, 1, 2, 'PCM', 'PC', 'M');
    InsertClassEvent($TourId, 1, 2, 'PCW', 'PC', 'W');
    if($SubRule=='3') {
        InsertClassEvent($TourId, 1, 3, 'RM', 'R', 'M');
        InsertClassEvent($TourId, 1, 3, 'RW', 'R', 'W');
        InsertClassEvent($TourId, 1, 3, 'CM', 'C', 'M');
        InsertClassEvent($TourId, 1, 3, 'CW', 'C', 'W');
    }
	InsertClassEvent($TourId, 1, 2, 'W1M', 'W1', 'M');
	InsertClassEvent($TourId, 1, 2, 'W1W', 'W1', 'W');
    InsertClassEvent($TourId, 1, 1, 'PRX', 'PR', 'W');
    InsertClassEvent($TourId, 2, 1, 'PRX', 'PR', 'M');
    InsertClassEvent($TourId, 1, 1, 'PCX', 'PC', 'W');
    InsertClassEvent($TourId, 2, 1, 'PCX', 'PC', 'M');

    if($SubRule=='3') {
        InsertClassEvent($TourId, 1, 1, 'RX', 'R', 'W');
        InsertClassEvent($TourId, 2, 1, 'RX', 'R', 'M');
        InsertClassEvent($TourId, 1, 1, 'CX', 'C', 'W');
        InsertClassEvent($TourId, 2, 1, 'CX', 'C', 'M');
    }
    InsertClassEvent($TourId, 1, 1, 'W1X', 'W1', 'W');
    InsertClassEvent($TourId, 2, 1, 'W1X', 'W1', 'M');
    if($SubRule=='2') {
        InsertClassEvent($TourId, 0, 1, 'PRU21M', 'PR', 'U21M');
        InsertClassEvent($TourId, 0, 1, 'PRU21W', 'PR', 'U21W');
        InsertClassEvent($TourId, 0, 1, 'PCU21M', 'PC', 'U21M');
        InsertClassEvent($TourId, 0, 1, 'PCU21W', 'PC', 'U21W');
        InsertClassEvent($TourId, 0, 1, 'W1U21M', 'W1', 'U21M');
        InsertClassEvent($TourId, 0, 1, 'W1U21W', 'W1', 'U21W');
        InsertClassEvent($TourId, 0, 1, 'VI1U21', 'VI', '1U21');
        InsertClassEvent($TourId, 0, 1, 'VI2U21', 'VI', '2U21');

        InsertClassEvent($TourId, 1, 2, 'PRU21M', 'PR', 'U21M');
        InsertClassEvent($TourId, 1, 2, 'PRU21W', 'PR', 'U21W');
        InsertClassEvent($TourId, 1, 2, 'PCU21M', 'PC', 'U21M');
        InsertClassEvent($TourId, 1, 2, 'PCU21W', 'PC', 'U21W');
        InsertClassEvent($TourId, 1, 2, 'W1U21M', 'W1', 'U21M');
        InsertClassEvent($TourId, 1, 2, 'W1U21W', 'W1', 'U21W');
        InsertClassEvent($TourId, 1, 1, 'PRU21X', 'PR', 'U21W');
        InsertClassEvent($TourId, 2, 1, 'PRU21X', 'PR', 'U21M');
        InsertClassEvent($TourId, 1, 1, 'PCU21X', 'PC', 'U21W');
        InsertClassEvent($TourId, 2, 1, 'PCU21X', 'PC', 'U21M');
        InsertClassEvent($TourId, 1, 1, 'W1U21X', 'W1', 'U21W');
        InsertClassEvent($TourId, 2, 1, 'W1U21X', 'W1', 'U21M');
    }

}

