<?php

/**
 * Modified by helmut.poell@gmx.at
 * 2026-09-07
 */

require_once('Common/Fun_Modules.php');
$version = '2020-01-01 00:00:00';

$AllowedTypes=array(1,2,3,37,5,6,7,8,9,12,10,11,13);

$SetType['AT']['descr']=get_text('Setup-AT', 'Install');
$SetType['AT']['noc'] = 'AUT';
$SetType['AT']['types']=array();
$SetType['AT']['rules']=array();

foreach($AllowedTypes as $val) {
	$SetType['AT']['types']["$val"]=$TourTypes[$val];
}

foreach($AllowedTypes as $val) {
    if($val!=1 AND $val!=2 AND $val!=5 AND $val!=7 AND $val!=8) {
        $SetType['AT']['rules']["$val"] = array(
            'SetOrdinary',
            'SetOrdinaryFinals'
        );
    }
}


$SetType['AT']['rules'][3] = array(
    'SetOrdinary',
    'SetOrdinaryFinals',
    'LooserBrackets'
);

$SetType['AT']['rules'][6] = array(
    'SetOrdinary',
    'SetOrdinaryFinals',
    'LooserBrackets'
);


/* No subrules requests as of today */



