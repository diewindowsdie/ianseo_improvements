<?php
/*
Common Setup for "Target" Archery
*/

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__, 2) .'/lib.php');

// default Divisions
CreateStandardDivisions($TourId,'', $SubRule);

// default Classes
CreateStandardClasses($TourId, $SubRule, (in_array($TourType,array(3,37)) ? '70M': (in_array($TourType,array(1,2,6,7,8)) ? 'TARGET':'')));

// default Distances

switch($TourType) {
    case 1:
        CreateDistanceNew($TourId, $TourType, '__U11_', array(array('20m',20), array('15m',15), array('10m',10), array('5m',5)));

        CreateDistanceNew($TourId, $TourType, 'OLU13_', array(array('30m',30), array('25m',25), array('20m',20), array('15m',15)));
        CreateDistanceNew($TourId, $TourType, 'KLU13_', array(array('30m',30), array('25m',25), array('20m',20), array('15m',15)));
        CreateDistanceNew($TourId, $TourType, 'HLU13_', array(array('20m',20), array('15m',15), array('10m',10), array('5m',5)));
        CreateDistanceNew($TourId, $TourType, 'DLU13_', array(array('20m',20), array('15m',15), array('10m',10), array('5m',5)));
        CreateDistanceNew($TourId, $TourType, 'TLU13_', array(array('20m',20), array('15m',15), array('10m',10), array('5m',5)));

        CreateDistanceNew($TourId, $TourType, 'OLU15_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'KLU15_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'HLU15_', array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'DLU15_', array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'TLU15_', array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));

        CreateDistanceNew($TourId, $TourType, 'OLU18W', array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'OLU18M', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLU18W', array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLU18M', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'HLU18_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'DLU18_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'TLU18_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'OLU21W', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'OLU21M', array(array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLU21W', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLU21M', array(array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'HLU21_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'DLU21_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'TLU21_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'OLW',    array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'OLM',    array(array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLW',    array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLM',    array(array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'HL_',    array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'DL_',    array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'TL_',    array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'OL50W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'OL50M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KL50W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KL50M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'HL50_',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'DL50_',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'TL50_',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'OL60W',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'OL60M',  array(array('60m',60), array('50m',50), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'KL60W',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'KL60M',  array(array('60m',60), array('50m',50), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'HL60_',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'DL60_',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'TL60_',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));

        CreateDistanceNew($TourId, $TourType, 'OL70W',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'OL70M',  array(array('60m',60), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'KL70W',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'KL70M',  array(array('60m',60), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'HL70_',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'DL70_',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'TL70_',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        break;
    case 2:
        CreateDistanceNew($TourId, $TourType, '__U11_', array(array('20m',20), array('15m',15), array('10m',10), array('5m',5), array('20m',20), array('15m',15), array('10m',10), array('5m',5)));

        CreateDistanceNew($TourId, $TourType, 'OLU13_', array(array('30m',30), array('25m',25), array('20m',20), array('15m',15), array('30m',30), array('25m',25), array('20m',20), array('15m',15)));
        CreateDistanceNew($TourId, $TourType, 'KLU13_', array(array('30m',30), array('25m',25), array('20m',20), array('15m',15), array('30m',30), array('25m',25), array('20m',20), array('15m',15)));
        CreateDistanceNew($TourId, $TourType, 'HLU13_', array(array('20m',20), array('15m',15), array('10m',10), array('5m',5), array('20m',20), array('15m',15), array('10m',10), array('5m',5)));
        CreateDistanceNew($TourId, $TourType, 'DLU13_', array(array('20m',20), array('15m',15), array('10m',10), array('5m',5), array('20m',20), array('15m',15), array('10m',10), array('5m',5)));
        CreateDistanceNew($TourId, $TourType, 'TLU13_', array(array('20m',20), array('15m',15), array('10m',10), array('5m',5), array('20m',20), array('15m',15), array('10m',10), array('5m',5)));

        CreateDistanceNew($TourId, $TourType, 'OLU15_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'KLU15_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'HLU15_', array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'DLU15_', array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'TLU15_', array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));

        CreateDistanceNew($TourId, $TourType, 'OLU18W', array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'OLU18M', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLU18W', array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLU18M', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'HLU18_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'DLU18_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'TLU18_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'OLU21W', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'OLU21M', array(array('90m',90), array('70m',70), array('50m',50), array('30m',30), array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLU21W', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLU21M', array(array('90m',90), array('70m',70), array('50m',50), array('30m',30), array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'HLU21_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'DLU21_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'TLU21_', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'OLW',    array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'OLM',    array(array('90m',90), array('70m',70), array('50m',50), array('30m',30), array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLW',    array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLM',    array(array('90m',90), array('70m',70), array('50m',50), array('30m',30), array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'DL_',    array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'HL_',    array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'TL_',    array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'OL50W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'OL50M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KL50W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KL50M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'HL50_',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'DL50_',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'TL50_',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'OL60W',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'OL60M',  array(array('60m',60), array('50m',50), array('30m',30), array('20m',20), array('60m',60), array('50m',50), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'KL60W',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'KL60M',  array(array('60m',60), array('50m',50), array('30m',30), array('20m',20), array('60m',60), array('50m',50), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'HL60_',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'DL60_',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'TL60_',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));

        CreateDistanceNew($TourId, $TourType, 'OL70W',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'OL70M',  array(array('60m',60), array('40m',40), array('30m',30), array('20m',20), array('60m',60), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'KL70W',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'KL70M',  array(array('60m',60), array('40m',40), array('30m',30), array('20m',20), array('60m',60), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'HL70_',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'DL70_',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'TL70_',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        break;
	case 3:
        CreateDistanceNew($TourId, $TourType, '__U11_', array(array('20m',20), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'OLU13_', array(array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLU13_', array(array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'HLU13_', array(array('20m',20), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'DLU13_', array(array('20m',20), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'TLU13_', array(array('20m',20), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'OL%U14_', array(array('40m',40), array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'OL%U15_', array(array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'KLU15_', array(array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'HLU15_', array(array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'DLU15_', array(array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'TLU15_', array(array('30m',30), array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'OLU18_', array(array('60m',60), array('60m',60)));
        CreateDistanceNew($TourId, $TourType, 'KLU18_', array(array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'HLU18_', array(array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'DLU18_', array(array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'TLU18_', array(array('40m',40), array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'OLU21_', array(array('70m',70), array('70m',70)));
        CreateDistanceNew($TourId, $TourType, 'KLU21_', array(array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'HLU21_', array(array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'DLU21_', array(array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'TLU21_', array(array('40m',40), array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'OL_', array(array('70m',70), array('70m',70)));
        CreateDistanceNew($TourId, $TourType, 'KL_', array(array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'HL_', array(array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'DL_', array(array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'TL_', array(array('40m',40), array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'OL50_', array(array('70m',70), array('70m',70)));
        CreateDistanceNew($TourId, $TourType, 'KL50_', array(array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'HL50_', array(array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'DL50_', array(array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'TL50_', array(array('40m',40), array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'OL60_', array(array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'KL60_', array(array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'HL60_', array(array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'DL60_', array(array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'TL60_', array(array('30m',30), array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'OL70_', array(array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'KL70_', array(array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'HL70_', array(array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'DL70_', array(array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'TL70_', array(array('30m',30), array('30m',30)));
        break;
	case 6:
        CreateDistanceNew($TourId, $TourType, '__U11_', array(array('10m',10), array('10m',10)));

        CreateDistanceNew($TourId, $TourType, 'OLU13_', array(array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, 'KLU13_', array(array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, 'HLU13_', array(array('10m',10), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'DLU13_', array(array('10m',10), array('20m',10)));
        CreateDistanceNew($TourId, $TourType, 'TLU13_', array(array('10m',10), array('10m',10)));

        CreateDistanceNew($TourId, $TourType, '__U15_', array(array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, '__U18_', array(array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, '__U21_', array(array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, '___', array(array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, '___0_', array(array('18m',18), array('18m',18)));
		break;
	case 7:
        CreateDistanceNew($TourId, $TourType, '__U11_', array(array('15m',15), array('15m',15)));

        CreateDistanceNew($TourId, $TourType, 'OLU13M', array(array('25m',25), array('25m',25)));
        CreateDistanceNew($TourId, $TourType, 'OLU13W', array(array('15m',15), array('15m',15)));
        CreateDistanceNew($TourId, $TourType, 'KLU13_', array(array('15m',15), array('15m',15)));
        CreateDistanceNew($TourId, $TourType, 'HLU13_', array(array('15m',15), array('15m',15)));
        CreateDistanceNew($TourId, $TourType, 'DLU13_', array(array('15m',15), array('15m',15)));
        CreateDistanceNew($TourId, $TourType, 'TLU13_', array(array('15m',15), array('15m',15)));

        CreateDistanceNew($TourId, $TourType, 'OLU15_', array(array('25m',25), array('25m',25)));
        CreateDistanceNew($TourId, $TourType, 'KLU15_', array(array('25m',25), array('25m',25)));
        CreateDistanceNew($TourId, $TourType, 'HLU15_', array(array('15m',15), array('15m',15)));
        CreateDistanceNew($TourId, $TourType, 'DLU15_', array(array('15m',15), array('15m',15)));
        CreateDistanceNew($TourId, $TourType, 'TLU15_', array(array('15m',15), array('15m',15)));

        CreateDistanceNew($TourId, $TourType, '__U18_', array(array('25m',25), array('25m',25)));
        CreateDistanceNew($TourId, $TourType, '__U21_', array(array('25m',25), array('25m',25)));
        CreateDistanceNew($TourId, $TourType, '___', array(array('25m',25), array('25m',25)));
        CreateDistanceNew($TourId, $TourType, '___0_', array(array('25m',25), array('25m',25)));
		break;
	case 8:
        CreateDistanceNew($TourId, $TourType, '__U11_', array(array('15m',15), array('15m',15),array('10m',10), array('10m',10)));

        CreateDistanceNew($TourId, $TourType, 'OLU13M', array(array('25m',25), array('25m',25), array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, 'OLU13W', array(array('15m',15), array('15m',15), array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, 'KLU13_', array(array('15m',15), array('15m',15), array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, 'HLU13_', array(array('15m',15), array('15m',15), array('10m',10), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'DLU13_', array(array('15m',15), array('15m',15), array('10m',10), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'TLU13_', array(array('15m',15), array('15m',15), array('10m',10), array('10m',10)));

        CreateDistanceNew($TourId, $TourType, 'OLU15_', array(array('25m',25), array('25m',25), array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, 'KLU15_', array(array('25m',25), array('25m',25), array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, 'HLU15_', array(array('15m',15), array('15m',15), array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, 'DLU15_', array(array('15m',15), array('15m',15), array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, 'TLU15_', array(array('15m',15), array('15m',15), array('18m',18), array('18m',18)));

        CreateDistanceNew($TourId, $TourType, '__U18_', array(array('25m',25), array('25m',25), array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, '__U21_', array(array('25m',25), array('25m',25), array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, '___', array(array('25m',25), array('25m',25), array('18m',18), array('18m',18)));
        CreateDistanceNew($TourId, $TourType, '___0_', array(array('25m',25), array('25m',25), array('18m',18), array('18m',18)));
		break;
    case 37:
        CreateDistanceNew($TourId, $TourType, '__U11_', array(array('20m',20), array('20m',20), array('20m',20), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'OLU13_', array(array('30m',30), array('30m',30), array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'KLU13_', array(array('30m',30), array('30m',30), array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'HLU13_', array(array('20m',20), array('20m',20), array('20m',20), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'DLU13_', array(array('20m',20), array('20m',20), array('20m',20), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'TLU13_', array(array('20m',20), array('20m',20), array('20m',20), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'OL%U14_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'OL%U15_', array(array('50m',50), array('50m',50), array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'KLU15_', array(array('50m',50), array('50m',50), array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'HLU15_', array(array('30m',30), array('30m',30), array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'DLU15_', array(array('30m',30), array('30m',30), array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'TLU15_', array(array('30m',30), array('30m',30), array('30m',30), array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'OLU18_', array(array('60m',60), array('60m',60), array('60m',60), array('60m',60)));
        CreateDistanceNew($TourId, $TourType, 'KLU18_', array(array('50m',50), array('50m',50), array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'HLU18_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'DLU18_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'TLU18_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'OLU21_', array(array('70m',70), array('70m',70), array('70m',70), array('70m',70)));
        CreateDistanceNew($TourId, $TourType, 'KLU21_', array(array('50m',50), array('50m',50), array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'HLU21_', array(array('50m',50), array('50m',50), array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'DLU21_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'TLU21_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'OL_', array(array('70m',70), array('70m',70), array('70m',70), array('70m',70)));
        CreateDistanceNew($TourId, $TourType, 'KL_', array(array('50m',50), array('50m',50), array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'HL_', array(array('50m',50), array('50m',50), array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'DL_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'TL_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'OL50_', array(array('70m',70), array('70m',70), array('70m',70), array('70m',70)));
        CreateDistanceNew($TourId, $TourType, 'KL50_', array(array('50m',50), array('50m',50), array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'HL50_', array(array('50m',50), array('50m',50), array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'DL50_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'TL50_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'OL60_', array(array('50m',50), array('50m',50), array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'KL60_', array(array('50m',50), array('50m',50), array('50m',50), array('50m',50)));
        CreateDistanceNew($TourId, $TourType, 'HL60_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'DL60_', array(array('30m',30), array('30m',30), array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'TL60_', array(array('30m',30), array('30m',30), array('30m',30), array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'OL70_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'KL70_', array(array('40m',40), array('40m',40), array('40m',40), array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'HL70_', array(array('30m',30), array('30m',30), array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'DL70_', array(array('30m',30), array('30m',30), array('30m',30), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'TL70_', array(array('30m',30), array('30m',30), array('30m',30), array('30m',30)));
        break;
}


if($TourType==3 or $TourType==6) {
	// default Events
	CreateStandardEvents($TourId, $SubRule, $TourType==3);

	// Classes in Events
	InsertStandardEvents($TourId, $SubRule, $TourType!=6);
	// Finals & TeamFinals
	CreateFinals($TourId);
}

// Default Target
switch($TourType) {
    case 1:
        CreateTargetFace($TourId, 1, '~Default', 'REG-^O|^K', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
        CreateTargetFace($TourId, 2, '~Option', 'REG-^O|^K', '',TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
        CreateTargetFace($TourId, 3, '~Default', 'REG-^D|^H|^T', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
        break;
    case 2:
        CreateTargetFace($TourId, 1, '~Default', 'REG-^O|^K', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
        CreateTargetFace($TourId, 2, '~Option', 'REG-^O|^K', '',TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_FULL, 80, TGT_OUT_FULL, 80,TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
        CreateTargetFace($TourId, 3, '~Default', 'REG-^D|^H|^T', '1',TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
        break;
    case 3:
        CreateTargetFace($TourId, 1, '~Default', 'REG-^O|^D|^H|^T', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
        CreateTargetFace($TourId, 2, '~Default', 'REG-^KLU15|^KLU18|^KLU21|^KL50|^KLM|^KLW', '1', TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
        CreateTargetFace($TourId, 3, '~Default', 'REG-^KLU11|^KLU13|^KL60|^KL70', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
	    break;
	case 6:
        CreateTargetFace($TourId, 1, '~Default', 'REG-^D|^T|^HL60|^HL70|^HLU11|^HLU15|^OLU11|^OLU13', '1', TGT_IND_5_big10, 80, TGT_IND_5_big10, 80);
        CreateTargetFace($TourId, 2, '~Default', 'REG-^OL70|^OLU15|^HLU13|^HLU18', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
        CreateTargetFace($TourId, 3, '~Default', 'REG-^KLU11|^KLU13', '1', TGT_IND_1_small10, 80, TGT_IND_1_small10, 80); //5-Small10
        CreateTargetFace($TourId, 4, '~Default', 'REG-^KLU15|^KL70', '1', TGT_IND_1_small10, 60, TGT_IND_1_small10, 60);
        CreateTargetFace($TourId, 5, '~Default', 'REG-^KL60', '1', TGT_IND_1_small10, 40, TGT_IND_1_small10, 40);
        CreateTargetFace($TourId, 6, '~Default', 'REG-^KLU18|^KLU21|^KL50|^KLM|^KLW', '1', TGT_IND_6_small10, 40, TGT_IND_6_small10, 40);
        CreateTargetFace($TourId, 7, '~Default', 'REG-^OL60|^HLU21|^HL50|^HLM|^HLW', '1', TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
        CreateTargetFace($TourId, 8, '~Default', 'REG-^OLU18|^OLU21|^OL50|^OLM|^OLW', '1', TGT_IND_6_big10, 40, TGT_IND_6_big10, 40);
		break;
    case 7:
        CreateTargetFace($TourId, 1, '~Default', 'REG-^D|^T|^HL60|^HL70|^HLU11|^HLU13|^OL70|^OLU11|^OLU13', '1', TGT_IND_5_big10, 80, TGT_IND_5_big10, 80);
        CreateTargetFace($TourId, 2, '~Default', 'REG-^HLU15|^HLU18|^HLU21|^HL50|^HLM|^HLW|^OL50|^OL60|^OLU15|^OLU18|^OLU21|^OLM|^OLW', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
        CreateTargetFace($TourId, 3, '~Default', 'REG-^KLU11|^KLU13|^KL70', '1', TGT_IND_1_small10, 80, TGT_IND_1_small10, 80); //5-Small10
        CreateTargetFace($TourId, 4, '~Default', 'REG-^KLU15|^KLU18|^KLU21|^KLM|^KLW|^KL50|^KL60', '1', TGT_IND_1_small10, 60, TGT_IND_1_small10, 60);
        break;
    case 8:
        CreateTargetFace($TourId, 1, '~Default', 'REG-^D|^T|^HL60|^HL70|^HLU11|^OLU11|^OLU13', '1', TGT_IND_5_big10, 80, TGT_IND_5_big10, 80, TGT_IND_5_big10, 80, TGT_IND_5_big10, 80);
        CreateTargetFace($TourId, 2, '~Default', 'REG-^HLU13|^OL70', '1', TGT_IND_5_big10, 80, TGT_IND_5_big10, 80, TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
        CreateTargetFace($TourId, 3, '~Default', 'REG-^HLU15', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60, TGT_IND_5_big10, 80, TGT_IND_5_big10, 80);
        CreateTargetFace($TourId, 4, '~Default', 'REG-^HLU18', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60, TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
        CreateTargetFace($TourId, 5, '~Default', 'REG-^OL60|^HLU21|^HL50|^HLM|^HLW', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60, TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
        CreateTargetFace($TourId, 6, '~Default', 'REG-^OLU15', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60, TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
        CreateTargetFace($TourId, 7, '~Default', 'REG-^OLU18|^OLU21|^OL50|^OLM|^OLW', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60, TGT_IND_6_big10, 40, TGT_IND_6_big10, 40);
        CreateTargetFace($TourId, 8, '~Default', 'REG-^KLU11|^KLU13', '1', TGT_IND_1_small10, 80, TGT_IND_1_small10, 80, TGT_IND_1_small10, 80, TGT_IND_1_small10, 80); //5-Small10
        CreateTargetFace($TourId, 9, '~Default', 'REG-^KL70', '1', TGT_IND_1_small10, 80, TGT_IND_1_small10, 80, TGT_IND_1_small10, 60, TGT_IND_1_small10, 60); //5-Small10
        CreateTargetFace($TourId, 10, '~Default', 'REG-^KLU15', '1', TGT_IND_1_small10, 60, TGT_IND_1_small10, 60, TGT_IND_1_small10, 60, TGT_IND_1_small10, 60);
        CreateTargetFace($TourId, 11, '~Default', 'REG-^KL60', '1', TGT_IND_1_small10, 60, TGT_IND_1_small10, 60, TGT_IND_1_small10, 40, TGT_IND_1_small10, 40);
        CreateTargetFace($TourId, 12, '~Default', 'REG-^KLU18|^KLU21|^KLM|^KLW|^KL50', '1', TGT_IND_1_small10, 60, TGT_IND_1_small10, 60, TGT_IND_6_small10, 40, TGT_IND_6_small10, 40);
        break;
    case 37:
        CreateTargetFace($TourId, 1, '~Default', 'REG-^O|^D|^H|^T', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
        CreateTargetFace($TourId, 2, '~Default', 'REG-^KLU15|^KLU18|^KLU21|^KL50|^KLM|^KLW', '1', TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
        CreateTargetFace($TourId, 3, '~Default', 'REG-^KLU11|^KLU13|^KL60|^KL70', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
        break;
}

// create a first distance prototype
CreateDistanceInformation($TourId, $DistanceInfoArray, 32, 4);

// Update Tour details
$tourDetails=array(
	'ToCollation' => $tourCollation,
	'ToTypeName' => $tourDetTypeName,
	'ToNumDist' => $tourDetNumDist,
	'ToNumEnds' => $tourDetNumEnds,
	'ToMaxDistScore' => $tourDetMaxDistScore,
	'ToMaxFinIndScore' => $tourDetMaxFinIndScore,
	'ToMaxFinTeamScore' => $tourDetMaxFinTeamScore,
	'ToCategory' => $tourDetCategory,
	'ToElabTeam' => $tourDetElabTeam,
	'ToElimination' => $tourDetElimination,
	'ToGolds' => $tourDetGolds,
	'ToXNine' => $tourDetXNine,
	'ToGoldsChars' => $tourDetGoldsChars,
	'ToXNineChars' => $tourDetXNineChars,
	'ToDouble' => $tourDetDouble,
//	'ToIocCode'	=> $tourDetIocCode,
	);
UpdateTourDetails($TourId, $tourDetails);
