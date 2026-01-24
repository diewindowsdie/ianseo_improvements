<?php
$version='2020-03-27 13:33:00';

$AllowedTypes=array(1,2,3,5,6,7,8,9,10,12,37,40,11,13);

$SetType['UK']['descr']=get_text('Setup-UK', 'Install');
$SetType['UK']['noc'] = 'GBR';
$SetType['UK']['types']=array();
$SetType['UK']['rules']=array();

foreach($AllowedTypes as $val) {
    $SetType['UK']['types']["$val"]=$TourTypes[$val];
}

foreach(array(1,2,3,5,6,7,8,37) as $val) {
    if($_SESSION)
        $SetType['UK']['rules']["$val"] =
            (ProgramRelease === 'TESTING')
                ? array('SetUkNationals', 'SetUkJunNationals','SetUK_Metric')
                : array('SetUK_Metric');
}
	foreach(array(40) as $val) {
        $SetType['UK']['rules']["$val"]=array(
            'SetUK_YHB',
            'SetUK_WINDS',
            'SetUK_AME',
            'SetUK_NATS',
            'SetUK_WEST',
            'SetUK_WARS',
            'SetUK_STNIC',
            'SetUK_ONT',
            'SetUK_SHMET',
            'SetUK_LGMET',
            'SetUK_WRCS',
            'SetUK_BR1',
            'SetUK_BR2',
            'SetUK_STFD',
            'SetUK_PMTH',

        );
}
foreach(array(9,10,12) as $val) {
    $SetType['UK']['rules']["$val"] = array(
        'SetUK_Field',

    );
    }

// 3D (both types)
foreach(array(11, 13) as $val) {
    $SetType['UK']['rules']["$val"]=array(
        'SetAllClass',
    );
}


