<?php
$version='2016-05-29 08:13:00';

$AllowedTypes=array(3,37,1,6);
//$SetType['PL']['descr']=get_text('Setup-PL', 'Install');
$SetType['PL']['descr']='Polski Związek Łuczniczy';
$SetType['PL']['noc'] = 'POL';
$SetType['PL']['types']=array();
$SetType['PL']['rules']=array();

foreach($AllowedTypes as $val) {
	$SetType['PL']['types']["$val"]=$TourTypes[$val];
	$SetType['PL']['rules']["$val"]=array(
		'SetOneClass',
		'SetAllClass',
		'SetYouthClass',
		'SetKidClass',
		);
		
}
