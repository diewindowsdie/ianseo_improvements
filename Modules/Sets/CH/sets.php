<?php
$version='2025-05-25 17:00:00';

$AllowedTypes=array(1,2,3,44,6,7,8,9,11);

$SetType['CH']['descr']=get_text('Setup-CH', 'Install');
$SetType['CH']['noc'] = 'SUI';
$SetType['CH']['types']=array();
$SetType['CH']['rules']=array();

foreach($AllowedTypes as $val) {
	$SetType['CH']['types']["$val"]=$TourTypes[$val];
}

//ML 25.05.2025

foreach($AllowedTypes as $val) {
	switch($val) {

		//Outdoor
		case 1:
			$SetType['CH']['types']["$val"]=get_text('SetCHOutdoor1440','Install');
		break;
		case 2:
			$SetType['CH']['types']["$val"]=get_text('SetCHOutdoor2880','Install');
		break;
		case 3:
			$SetType['CH']['types']["$val"]=get_text('SetCHOutdoor720','Install');
		break;
		case 44:
			$SetType['CH']['types']["$val"]=get_text('SetCHFederal','Install');
		break;

		//Indoor
		case 6:
			$SetType['CH']['types']["$val"]=get_text('SetCHIndoor18','Install');
		break;
		case 7:
			$SetType['CH']['types']["$val"]=get_text('SetCHIndoor25','Install');
		break;
		case 8:
			$SetType['CH']['types']["$val"]=get_text('SetCHIndoor18+25','Install');
		break;

		//Field
		case 9:
			$SetType['CH']['types']["$val"]=get_text('SetCHField','Install');
			$SetType['CH']['rules']["$val"]=array(
				'Set12',
				'Set16',
				'Set20',
				'Set24',
				'Set12+12',
				'Set16+16',
				'Set20+20',
				'Set24+24');
		break;

		//Parcours
		case 11:
			$SetType['CH']['types']["$val"]=get_text('SetCHParcours','Install');
			$SetType['CH']['rules']["$val"]=array(
				'Set24',
				'Set28',
				'Set32',
				'Set24+24');
			break;

		default:
			$SetType['CH']['types']["$val"]=$TourTypes[$val];

	}
}


