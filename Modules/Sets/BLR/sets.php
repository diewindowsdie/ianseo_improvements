<?php
$version='2026-01-20 22:22:00';

$AllowedTypes=array(1,3,6,37,1000);

// prepare the available sets
$SetType['BLR']['descr']=get_text('Setup-BLR', 'Install');
$SetType['BLR']['types']=array();
$SetType['BLR']['rules']=array();

foreach($AllowedTypes as $val) {
	$SetType['BLR']['types'][$val]=$TourTypes[$val];
}
foreach($AllowedTypes as $val) {
    $SetType['BLR']['rules'][$val]=array(
        'SetAllClass',
        'SetOneClass',
        'SetJ-SClass',
        'SetYouthClass',
    );
}