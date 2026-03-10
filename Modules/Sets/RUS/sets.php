<?php
// global $TourTypes;
require_once('Common/Fun_Modules.php');
$version='2011-05-13 08:13:00';

$AllowedTypes=array(1,3,6,9,10,11,12,13,48,1001,1002);

$SetType['RUS']['descr']=get_text('Setup-RUS', 'Install');
$SetType['RUS']['types']=array();
$SetType['RUS']['rules']=array();

foreach($AllowedTypes as $val) {
	$SetType['RUS']['types']["$val"]=$TourTypes[$val];
}

// FITA, 70m Round, 18m, 50+30, 12m
foreach(array(1, 3, 6, 1001, 1002) as $val) {
	$SetType['RUS']['rules']["$val"]=array(
		'SetAllClass',
		'SetOneClass',
		'SetJ-SClass',
		'SetYouthClass',
		);
	if(module_exists('QuotaTournament'))
		$SetType['RUS']['rules']["$val"][]='QuotaTournm';
}

// HF (all 3 types)
foreach(array(9, 10, 12) as $val) {
	$SetType['RUS']['rules']["$val"]=array(
		'SetAllClass',
		'SetJ-SClass',
		);
}

// 3D (both types)
foreach(array(11, 13) as $val) {
	$SetType['RUS']['rules']["$val"]=array(
		'SetAllClass',
		'SetOneClass',
		);
}
