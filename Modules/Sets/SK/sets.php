<?php
require_once('Common/Fun_Modules.php');
$version = '2025-12-01 00:00:00';

$AllowedTypes=array(1,2,3,37,6,7,8,9,11);

$SetType['SK']['descr']=get_text('Setup-SK', 'Install');
$SetType['SK']['noc'] = 'SVK';
$SetType['SK']['types']=array();
$SetType['SK']['rules']=array();

foreach($AllowedTypes as $val) {
	$SetType['SK']['types']["$val"]=$TourTypes[$val];
}

$SetType['SK']['rules'][3] = array(
    'SetAllClassNoMatches',
    'SetSK_Elim',
    'SetSK_ChampAdult',
    'SetSK_ChampYouth',
    'SetSK_ChampVeteran'
);

$SetType['SK']['rules'][6] = array(
    'SetAllClassNoMatches',
    'SetSK_Elim',
    'SetSK_ChampAdult',
    'SetSK_ChampYouth'
);
