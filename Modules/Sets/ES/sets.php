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
$version='2026-05-31 00:00:00';

$AllowedTypes=array(1,3,6,9,10,12,11,13);

$SetType['ES']['descr']=get_text('Setup-ES', 'Install');
$SetType['ES']['noc'] = 'ESP';
$SetType['ES']['types']=array();
$SetType['ES']['rules']=array();

foreach($AllowedTypes as $val) {
	$SetType['ES']['types']["$val"]=$TourTypes[$val];
	$SetType['ES']['rules']["$val"]=array(
		'SetESAbsRC',
		'SetESAbsOtras',
		'SetESAbsTodas',
		'SetESMenoresNac',
		'SetESMenoresAut',
		'SetESTodas',
		);
}
