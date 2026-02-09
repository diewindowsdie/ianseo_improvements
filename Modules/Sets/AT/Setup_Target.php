<?php
/*
Common Setup for "Target" Archery
*/

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

// default Divisions
CreateStandardDivisions($TourId,(in_array($TourType,array(3,6,7,8,37)) ? '70M':'FITA'), $SubRule);

// default SubClasses
//CreateSubClass($TourId, 1, '00', '00');

// default Classes
CreateStandardClasses($TourId, $SubRule);

// default Distances

switch($TourType) {
    case 1:
        CreateDistanceNew($TourId, $TourType, 'RU13%',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'RU15%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'RU18W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'RU18M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'RU21W',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'RU21M',  array(array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'RW',   array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'RM',   array(array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'R50W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'R50M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'R60W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'R60M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'R65%',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'CU13%',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'CU15%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'CU18W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'CU18M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'CU21W',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'CU21M',  array(array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'CW',   array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'CM',   array(array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'C50W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'C50M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'C60W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'C60M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'C65%',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'BU13%',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'BU15%',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'BU18%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'BU21%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'B50%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'B60%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'B65%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'B_',   array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'PRU13%',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'PRU15%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PRU18W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PRU18M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PRU21%',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PR_',   array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PR50W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PR50M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PR60W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PR60M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PR65%',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'PCU13%',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'PCU15%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PCU18W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PCU18M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PCU21%',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PC_',   array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PC50W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PC50M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PC60W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PC60M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PC65%',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'PW1U13%', array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'PW1U15%', array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'PW1U18%', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PW1U21%', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PW150%', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PW160%', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PW165%', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PW1_',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'T%',   array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'L%',   array(array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'PW0%',  array(array('30m',30), array('30m',30), array('20m',20), array('10m',10)));
        break;
    case 2:
        CreateDistanceNew($TourId, $TourType, 'RU13%', array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'RU15%', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'RU18W', array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'RU18M', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'RU21W', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'RU21M', array(array('90m',90), array('70m',70), array('50m',50), array('30m',30), array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'RW',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'RM',  array(array('90m',90), array('70m',70), array('50m',50), array('30m',30), array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'R50W', array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'R50M', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'R60W', array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'R60M', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'R65%', array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'CU13%',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'CU15%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'CU18W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'CU18M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'CU21W',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'CU21M',  array(array('90m',90), array('70m',70), array('50m',50), array('30m',30), array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'CW',   array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'CM',   array(array('90m',90), array('70m',70), array('50m',50), array('30m',30), array('90m',90), array('70m',70), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'C50W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'C50M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'C60W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'C60M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'C65%',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'BU13%',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'BU15%',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'BU18%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'BU21%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'B50%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'B60%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'B65%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'B_',   array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'PRU13%', array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'PRU15%', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PRU18W', array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PRU18M', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PRU21%', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PR_',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PR50W', array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PR50M', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PR60W', array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PR60M', array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PR65%', array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'PCU13%',  array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'PCU15%',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PCU18W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PCU18M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PCU21%',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PC_',   array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PC50W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PC50M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PC60W',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PC60M',  array(array('70m',70), array('60m',60), array('50m',50), array('30m',30), array('70m',70), array('60m',60), array('50m',50), array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PC65%',  array(array('60m',60), array('50m',50), array('40m',40), array('30m',30), array('60m',60), array('50m',50), array('40m',40), array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'PW1U13%', array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'PW1U15%', array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'PW1U18%', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PW1U21%', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PW150%', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PW160%', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PW165%', array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PW1_',  array(array('50m',50), array('40m',40), array('30m',30), array('20m',20), array('50m',50), array('40m',40), array('30m',30), array('20m',20)));

        CreateDistanceNew($TourId, $TourType, 'T%',   array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'L%',   array(array('40m',40), array('30m',30), array('20m',20), array('10m',10), array('40m',40), array('30m',30), array('20m',20), array('10m',10)));
        CreateDistanceNew($TourId, $TourType, 'PW0%',  array(array('30m',30), array('30m',30), array('20m',20), array('10m',10), array('30m',30), array('30m',30), array('20m',20), array('10m',10)));
        break;
	case 3:
        switch($SubRule) {
            case '1':
            case '3':
                CreateDistanceNew($TourId, $TourType, 'RU13%',  array(array('30m-1', 30), array('30m-2', 30)));
                CreateDistanceNew($TourId, $TourType, 'RU15%',  array(array('40m-1', 40), array('40m-2', 40)));
                CreateDistanceNew($TourId, $TourType, 'RU18%',  array(array('60m-1', 60), array('60m-2', 60)));
                CreateDistanceNew($TourId, $TourType, 'RU21%',  array(array('70m-1', 70), array('70m-2', 70)));
                CreateDistanceNew($TourId, $TourType, 'R50%',  array(array('60m-1', 60), array('60m-2', 60)));
                CreateDistanceNew($TourId, $TourType, 'R60%',  array(array('60m-1', 60), array('60m-2', 60)));
                CreateDistanceNew($TourId, $TourType, 'R65%',  array(array('60m-1', 60), array('60m-2', 60)));
                CreateDistanceNew($TourId, $TourType, 'R_',   array(array('70m-1', 70), array('70m-2', 70)));

                CreateDistanceNew($TourId, $TourType, 'PRU13%',  array(array('30m-1', 30), array('30m-2', 30)));
                CreateDistanceNew($TourId, $TourType, 'PRU15%',  array(array('40m-1', 40), array('40m-2', 40)));
                CreateDistanceNew($TourId, $TourType, 'PRU18%',  array(array('60m-1', 60), array('60m-2', 60)));
                CreateDistanceNew($TourId, $TourType, 'PRU21%',  array(array('70m-1', 70), array('70m-2', 70)));
                CreateDistanceNew($TourId, $TourType, 'PR50%',  array(array('60m-1', 60), array('60m-2', 60)));
                CreateDistanceNew($TourId, $TourType, 'PR60%',  array(array('60m-1', 60), array('60m-2', 60)));
                CreateDistanceNew($TourId, $TourType, 'PR65%',  array(array('60m-1', 60), array('60m-2', 60)));
                CreateDistanceNew($TourId, $TourType, 'PR_',   array(array('70m-1', 70), array('70m-2', 70)));

                CreateDistanceNew($TourId, $TourType, 'CU13%',  array(array('30m-1', 30), array('30m-2', 30)));
                CreateDistanceNew($TourId, $TourType, 'CU15%',  array(array('40m-1', 40), array('40m-2', 40)));
                CreateDistanceNew($TourId, $TourType, 'CU18%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'CU21%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'C50%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'C60%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'C65%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'C_',  array(array('50m-1', 50), array('50m-2', 50)));

                CreateDistanceNew($TourId, $TourType, 'PCU13%',  array(array('30m-1', 30), array('30m-2', 30)));
                CreateDistanceNew($TourId, $TourType, 'PCU15%',  array(array('40m-1', 40), array('40m-2', 40)));
                CreateDistanceNew($TourId, $TourType, 'PCU18%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'PCU21%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'PC50%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'PC60%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'PC65%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'PC_',  array(array('50m-1', 50), array('50m-2', 50)));

                CreateDistanceNew($TourId, $TourType, 'PW1U13%', array(array('30m-1', 30), array('30m-2', 30)));
                CreateDistanceNew($TourId, $TourType, 'PW1U15%', array(array('40m-1', 40), array('40m-2', 40)));
                CreateDistanceNew($TourId, $TourType, 'PW1U18%', array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'PW1U21%', array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'PW150%', array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'PW160%', array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'PW165%', array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'PW1_', array(array('50m-1', 50), array('50m-2', 50)));

                CreateDistanceNew($TourId, $TourType, 'BU13%',  array(array('30m-1', 30), array('30m-2', 30)));
                CreateDistanceNew($TourId, $TourType, 'BU15%',  array(array('30m-1', 30), array('30m-2', 30)));
                CreateDistanceNew($TourId, $TourType, 'BU18%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'BU21%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'B50%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'B60%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'B65%',  array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'B_',  array(array('50m-1', 50), array('50m-2', 50)));
                
                CreateDistanceNew($TourId, $TourType, 'TU13%',   array(array('30m-1', 30), array('30m-2', 30)));
                CreateDistanceNew($TourId, $TourType, 'TU15%',   array(array('30m-1', 30), array('30m-2', 30)));
                CreateDistanceNew($TourId, $TourType, 'TU18%',   array(array('30m-1', 30), array('30m-2', 30)));
                CreateDistanceNew($TourId, $TourType, 'TU21%',   array(array('40m-1', 40), array('40m-2', 40)));
                CreateDistanceNew($TourId, $TourType, 'T50%',   array(array('40m-1', 40), array('40m-2', 40)));
                CreateDistanceNew($TourId, $TourType, 'T60%',   array(array('40m-1', 40), array('40m-2', 40)));
                CreateDistanceNew($TourId, $TourType, 'T65%',   array(array('40m-1', 40), array('40m-2', 40)));
                CreateDistanceNew($TourId, $TourType, 'T_',   array(array('40m-1', 40), array('40m-2', 40)));

                CreateDistanceNew($TourId, $TourType, 'L%',   array(array('30m-1', 30), array('30m-2', 30)));
                CreateDistanceNew($TourId, $TourType, 'PW0%',  array(array('30m-1', 30), array('30m-2', 30)));
                break;
            case '2':
                CreateDistanceNew($TourId, $TourType, 'R%', array(array('70m-1',70), array('70m-2',70)));
                CreateDistanceNew($TourId, $TourType, 'C%', array(array('50m-1',50), array('50m-2',50)));
                CreateDistanceNew($TourId, $TourType, 'PW1%', array(array('50m-1',50), array('50m-2',50)));
                CreateDistanceNew($TourId, $TourType, 'PR%', array(array('70m-1',70), array('70m-2',70)));
                CreateDistanceNew($TourId, $TourType, 'PC%', array(array('50m-1',50), array('50m-2',50)));
                CreateDistanceNew($TourId, $TourType, 'B%',   array(array('50m-1', 50), array('50m-2', 50)));
                CreateDistanceNew($TourId, $TourType, 'T%',   array(array('40m-1', 40), array('40m-2', 40)));
                CreateDistanceNew($TourId, $TourType, 'L%',   array(array('30m-1', 30), array('30m-2', 30)));
                CreateDistanceNew($TourId, $TourType, 'PW0%',  array(array('30m-1', 30), array('30m-2', 30)));
                break;
		}
		break;
    case 5:
        CreateDistanceNew($TourId, $TourType, 'BU13%', array(array('30m',30), array('25m',25),array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'BU15%', array(array('30m',30), array('25m',25),array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'BU18%', array(array('40m',40), array('35m',35),array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'BU21%', array(array('40m',40), array('35m',35),array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'B50%', array(array('40m',40), array('35m',35),array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'B60%', array(array('40m',40), array('35m',35),array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'B65%', array(array('40m',40), array('35m',35),array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'B_', array(array('40m',40), array('35m',35),array('30m',30)));

        CreateDistanceNew($TourId, $TourType, 'RU13%', array(array('30m',30), array('25m',25),array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'RU15%', array(array('40m',40), array('35m',35),array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'RU18%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'RU21%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'R50%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'R60%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'R65%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'R_', array(array('60m',60), array('50m',50),array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'PRU13%', array(array('30m',30), array('25m',25),array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PRU15%', array(array('40m',40), array('35m',35),array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PRU18%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PRU21%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PR50%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PR60%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PR65%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PR_', array(array('60m',60), array('50m',50),array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'CU13%', array(array('30m',30), array('25m',25),array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'CU15%', array(array('40m',40), array('35m',35),array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'CU18%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'CU21%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'C50%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'C60%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'C65%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'C_', array(array('60m',60), array('50m',50),array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'PCU13%', array(array('30m',30), array('25m',25),array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PCU15%', array(array('40m',40), array('35m',35),array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PCU18%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PCU21%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PC50%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PC60%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PC65%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PC_', array(array('60m',60), array('50m',50),array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'PW1U13%', array(array('30m',30), array('25m',25),array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PW1U15%', array(array('40m',40), array('35m',35),array('30m',30)));
        CreateDistanceNew($TourId, $TourType, 'PW1U18%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PW1U21%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PW150%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PW160%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PW165%', array(array('60m',60), array('50m',50),array('40m',40)));
        CreateDistanceNew($TourId, $TourType, 'PW1_', array(array('60m',60), array('50m',50),array('40m',40)));

        CreateDistanceNew($TourId, $TourType, 'T%', array(array('30m',30), array('25m',25),array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'L%', array(array('30m',30), array('25m',25),array('20m',20)));
        CreateDistanceNew($TourId, $TourType, 'PW0%', array(array('30m',30), array('25m',25),array('20m',20)));
        break;
	case 6:
        switch($SubRule) {
            case '1':
            case '3':
                CreateDistanceNew($TourId, $TourType, 'R%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'C%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PR%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PC%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'B%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'T%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'L%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW0U13%', array(array('10m-1',10), array('10m-2',10)));
                CreateDistanceNew($TourId, $TourType, 'PW0U15%', array(array('10m-1',10), array('10m-2',10)));
                CreateDistanceNew($TourId, $TourType, 'PW0U18%', array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW0U21%', array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW0_',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW050%', array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW060%', array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW065%', array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW1U13%', array(array('10m-1',10), array('10m-2',10)));
                CreateDistanceNew($TourId, $TourType, 'PW1U15%', array(array('10m-1',10), array('10m-2',10)));
                CreateDistanceNew($TourId, $TourType, 'PW1U18%', array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW1U21%', array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW1_',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW150%', array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW160%', array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW165%', array(array('18m-1',18), array('18m-2',18)));
                break;
            case '2':
                CreateDistanceNew($TourId, $TourType, 'R%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'C%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'B%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'T%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'L%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW0%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PW1%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PR%',   array(array('18m-1',18), array('18m-2',18)));
                CreateDistanceNew($TourId, $TourType, 'PC%',   array(array('18m-1',18), array('18m-2',18)));
                break;
        }
		break;
	case 7:
        CreateDistanceNew($TourId, $TourType, 'R%',   array(array('25m-1',25), array('25m-2',25)));
        CreateDistanceNew($TourId, $TourType, 'C%',   array(array('25m-1',25), array('25m-2',25)));
        CreateDistanceNew($TourId, $TourType, 'PW0_',   array(array('25m-1',25), array('25m-2',25)));
        CreateDistanceNew($TourId, $TourType, 'PW1_',   array(array('25m-1',25), array('25m-2',25)));
        CreateDistanceNew($TourId, $TourType, 'PR%',   array(array('25m-1',25), array('25m-2',25)));
        CreateDistanceNew($TourId, $TourType, 'PC%',   array(array('25m-1',25), array('25m-2',25)));
        CreateDistanceNew($TourId, $TourType, 'B%',   array(array('25m-1',25), array('25m-2',25)));
        CreateDistanceNew($TourId, $TourType, 'T%',   array(array('25m-1',25), array('25m-2',25)));
        CreateDistanceNew($TourId, $TourType, 'L%',   array(array('25m-1',25), array('25m-2',25)));
        if($SubRule==1) {
            CreateDistanceNew($TourId, $TourType, 'PW0U13%', array(array('20m-1',20), array('20m-2',20)));
            CreateDistanceNew($TourId, $TourType, 'PW0U15%', array(array('20m-1',20), array('20m-2',20)));
            CreateDistanceNew($TourId, $TourType, 'PW0U18%', array(array('25m-1',25), array('25m-2',25)));
            CreateDistanceNew($TourId, $TourType, 'PW0U21%', array(array('25m-1',25), array('25m-2',25)));
            CreateDistanceNew($TourId, $TourType, 'PW050%', array(array('25m-1',25), array('25m-2',25)));
            CreateDistanceNew($TourId, $TourType, 'PW060%', array(array('25m-1',25), array('25m-2',25)));
            CreateDistanceNew($TourId, $TourType, 'PW065%', array(array('25m-1',25), array('25m-2',25)));

            CreateDistanceNew($TourId, $TourType, 'PW1U13%', array(array('20m-1',20), array('20m-2',20)));
            CreateDistanceNew($TourId, $TourType, 'PW1U15%', array(array('20m-1',20), array('20m-2',20)));
            CreateDistanceNew($TourId, $TourType, 'PW1U18%', array(array('25m-1',25), array('25m-2',25)));
            CreateDistanceNew($TourId, $TourType, 'PW1U21%', array(array('25m-1',25), array('25m-2',25)));
            CreateDistanceNew($TourId, $TourType, 'PW150%', array(array('25m-1',25), array('25m-2',25)));
            CreateDistanceNew($TourId, $TourType, 'PW160%', array(array('25m-1',25), array('25m-2',25)));
            CreateDistanceNew($TourId, $TourType, 'PW165%', array(array('25m-1',25), array('25m-2',25)));
        }
		break;
	case 8:
        CreateDistanceNew($TourId, $TourType, 'R%',   array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
        CreateDistanceNew($TourId, $TourType, 'C%',   array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
        CreateDistanceNew($TourId, $TourType, 'PW0_',   array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
        CreateDistanceNew($TourId, $TourType, 'PW1_',   array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
        CreateDistanceNew($TourId, $TourType, 'PR%',   array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
        CreateDistanceNew($TourId, $TourType, 'PC%',   array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
        CreateDistanceNew($TourId, $TourType, 'B%',   array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
        CreateDistanceNew($TourId, $TourType, 'T%',   array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
        CreateDistanceNew($TourId, $TourType, 'L%',   array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
        if($SubRule==1) {
            CreateDistanceNew($TourId, $TourType, 'PW0U13%', array(array('20m-1',20), array('20m-2',20), array('10m-1',10), array('10m-2',10)));
            CreateDistanceNew($TourId, $TourType, 'PW0U15%', array(array('20m-1',20), array('20m-2',20), array('10m-1',10), array('10m-2',10)));
            CreateDistanceNew($TourId, $TourType, 'PW0U18%', array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
            CreateDistanceNew($TourId, $TourType, 'PW0U21%', array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
            CreateDistanceNew($TourId, $TourType, 'PW050%', array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
            CreateDistanceNew($TourId, $TourType, 'PW060%', array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
            CreateDistanceNew($TourId, $TourType, 'PW065%', array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));

            CreateDistanceNew($TourId, $TourType, 'PW1U13%', array(array('20m-1',20), array('20m-2',20), array('10m-1',10), array('10m-2',10)));
            CreateDistanceNew($TourId, $TourType, 'PW1U15%', array(array('20m-1',20), array('20m-2',20), array('10m-1',10), array('10m-2',10)));
            CreateDistanceNew($TourId, $TourType, 'PW1U18%', array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
            CreateDistanceNew($TourId, $TourType, 'PW1U21%', array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
            CreateDistanceNew($TourId, $TourType, 'PW150%', array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
            CreateDistanceNew($TourId, $TourType, 'PW160%', array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
            CreateDistanceNew($TourId, $TourType, 'PW165%', array(array('25m-1',25), array('25m-2',25), array('18m-1',18), array('18m-2',18)));
        }
		break;
    case 37:
        if($SubRule==1) {
            CreateDistanceNew($TourId, $TourType, 'RU13%',  array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
            CreateDistanceNew($TourId, $TourType, 'RU15%',  array(array('40m-1',40), array('40m-2',40), array('40m-3',40), array('40m-4',40)));
            CreateDistanceNew($TourId, $TourType, 'RU18%',  array(array('60m-1',60), array('60m-2',60), array('60m-3',60), array('60m-4',60)));
            CreateDistanceNew($TourId, $TourType, 'RU21%',  array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
            CreateDistanceNew($TourId, $TourType, 'R50%',  array(array('60m-1',60), array('60m-2',60), array('60m-3',60), array('60m-4',60)));
            CreateDistanceNew($TourId, $TourType, 'R60%',  array(array('60m-1',60), array('60m-2',60), array('60m-3',60), array('60m-4',60)));
            CreateDistanceNew($TourId, $TourType, 'R65%',  array(array('60m-1',60), array('60m-2',60), array('60m-3',60), array('60m-4',60)));
            CreateDistanceNew($TourId, $TourType, 'R_',   array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));

            CreateDistanceNew($TourId, $TourType, 'PRU13%',  array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
            CreateDistanceNew($TourId, $TourType, 'PRU15%',  array(array('40m-1',40), array('40m-2',40), array('40m-3',40), array('40m-4',40)));
            CreateDistanceNew($TourId, $TourType, 'PRU18%',  array(array('60m-1',60), array('60m-2',60), array('60m-3',60), array('60m-4',60)));
            CreateDistanceNew($TourId, $TourType, 'PRU21%',  array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
            CreateDistanceNew($TourId, $TourType, 'PR50%',  array(array('60m-1',60), array('60m-2',60), array('60m-3',60), array('60m-4',60)));
            CreateDistanceNew($TourId, $TourType, 'PR60%',  array(array('60m-1',60), array('60m-2',60), array('60m-3',60), array('60m-4',60)));
            CreateDistanceNew($TourId, $TourType, 'PR65%',  array(array('60m-1',60), array('60m-2',60), array('60m-3',60), array('60m-4',60)));
            CreateDistanceNew($TourId, $TourType, 'PR_',   array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));

            CreateDistanceNew($TourId, $TourType, 'CU13%',  array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
            CreateDistanceNew($TourId, $TourType, 'CU15%',  array(array('40m-1',40), array('40m-2',40), array('40m-3',40), array('40m-4',40)));
            CreateDistanceNew($TourId, $TourType, 'CU18%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'CU21%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'C50%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'C60%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'C65%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'C_',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));

            CreateDistanceNew($TourId, $TourType, 'PCU13%',  array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
            CreateDistanceNew($TourId, $TourType, 'PCU15%',  array(array('40m-1',40), array('40m-2',40), array('40m-3',40), array('40m-4',40)));
            CreateDistanceNew($TourId, $TourType, 'PCU18%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'PCU21%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'PC50%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'PC60%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'PC65%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'PC_',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));

            CreateDistanceNew($TourId, $TourType, 'PW1U13%', array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
            CreateDistanceNew($TourId, $TourType, 'PW1U15%', array(array('40m-1',40), array('40m-2',40), array('40m-3',40), array('40m-4',40)));
            CreateDistanceNew($TourId, $TourType, 'PW1U18%', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'PW1U21%', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'PW150%', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'PW160%', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'PW165%', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'PW1_', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));

            CreateDistanceNew($TourId, $TourType, 'BU13%',  array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
            CreateDistanceNew($TourId, $TourType, 'BU15%',  array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
            CreateDistanceNew($TourId, $TourType, 'BU18%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'BU21%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'B50%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'B60%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'B65%',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'B_',  array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));

            CreateDistanceNew($TourId, $TourType, 'TU13%', array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
            CreateDistanceNew($TourId, $TourType, 'TU15%', array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
            CreateDistanceNew($TourId, $TourType, 'TU18%', array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
            CreateDistanceNew($TourId, $TourType, 'TU21%', array(array('40m-1',40), array('40m-2',40), array('40m-3',40), array('40m-4',40)));
            CreateDistanceNew($TourId, $TourType, 'T50%',  array(array('40m-1',40), array('40m-2',40), array('40m-3',40), array('40m-4',40)));
            CreateDistanceNew($TourId, $TourType, 'T60%',  array(array('40m-1',40), array('40m-2',40), array('40m-3',40), array('40m-4',40)));
            CreateDistanceNew($TourId, $TourType, 'T65%',  array(array('40m-1',40), array('40m-2',40), array('40m-3',40), array('40m-4',40)));
            CreateDistanceNew($TourId, $TourType, 'T_',   array(array('40m-1',40), array('40m-2',40), array('40m-3',40), array('40m-4',40)));

            CreateDistanceNew($TourId, $TourType, 'L%',   array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
            CreateDistanceNew($TourId, $TourType, 'PW0%',  array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
        } else {
            CreateDistanceNew($TourId, $TourType, 'R%', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
            CreateDistanceNew($TourId, $TourType, 'C%', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'PW1%', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'PR%', array(array('70m-1',70), array('70m-2',70), array('70m-3',70), array('70m-4',70)));
            CreateDistanceNew($TourId, $TourType, 'PC%', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'B%', array(array('50m-1',50), array('50m-2',50), array('50m-3',50), array('50m-4',50)));
            CreateDistanceNew($TourId, $TourType, 'T%', array(array('40m-1',40), array('40m-2',40), array('40m-3',40), array('40m-4',40)));
            CreateDistanceNew($TourId, $TourType, 'L%', array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
            CreateDistanceNew($TourId, $TourType, 'PW0%', array(array('30m-1',30), array('30m-2',30), array('30m-3',30), array('30m-4',30)));
        }
        break;
}


if($TourType==3 or $TourType==6 or $TourType==37) {
	// default Events
	CreateStandardEvents($TourId, $SubRule, $TourType!=6, in_array($TourType,array(3,6,7,8,37)));

	// Classes in Events
	InsertStandardEvents($TourId, $SubRule, $TourType!=6);

        // Add Loosers Matches if needed
        if (($TourType == 3 and $SubRule == 3) or ($TourType == 6 and $SubRule == 3)) {
            $TargetR=(($TourType != 6) ? TGT_OUT_FULL : TGT_IND_6_big10);
            $TargetC=(($TourType != 6) ? TGT_OUT_5_big10 : TGT_IND_6_small10);
            $TargetB=(($TourType != 6) ? TGT_OUT_FULL : TGT_IND_1_big10);
            $TargetT=(($TourType != 6) ? TGT_OUT_FULL : TGT_IND_1_big10);
            $TargetL=(($TourType != 6) ? TGT_OUT_FULL : TGT_IND_1_big10);
            $TargetSizeR=(($TourType != 6) ? 122 : 40);
            $TargetSizeC=(($TourType != 6) ? 80 : 40);
            $TargetSizeB=(($TourType != 6) ? 122 : 40);
            $TargetSizeT=(($TourType != 6) ? 122 : 40);
            $TargetSizeL=(($TourType != 6) ? 122 : 60);
            $DistanceR=(($TourType != 6) ? 70 : 18);
            $DistanceC=(($TourType != 6) ? 50 : 18);
            $DistanceB=(($TourType != 6) ? 50 : 18);
            $DistanceT=(($TourType != 6) ? 40 : 18);
            $DistanceL=(($TourType != 6) ? 30 : 18);

            $i = 11;
            CreateEvent($TourId, $i++, 0, 0, 4, $TargetR, 5, 3, 1, 5, 3, 1, 'RM-1', 'Recurve Herren - 9.-12. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeR, $DistanceR, 'RM', '0', '0', 9);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetR, 5, 3, 1, 5, 3, 1, 'RM-2', 'Recurve Herren - 5.-8. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeR, $DistanceR, 'RM', '0', '0', 5);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetR, 5, 3, 1, 5, 3, 1, 'RM-3', 'Recurve Herren - 13.-16. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeR, $DistanceR, 'RM-1', '0', '0', 13);

            CreateEvent($TourId, $i++, 0, 0, 4, $TargetR, 5, 3, 1, 5, 3, 1, 'RW-1', 'Recurve Damen - 9.-12. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeR, $DistanceR, 'RW', '0', '0', 9);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetR, 5, 3, 1, 5, 3, 1, 'RW-2', 'Recurve Damen - 5.-8. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeR, $DistanceR, 'RW', '0', '0', 5);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetR, 5, 3, 1, 5, 3, 1, 'RW-3', 'Recurve Damen - 13.-16. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeR, $DistanceR, 'RW-1', '0', '0', 13);

            CreateEvent($TourId, $i++, 0, 0, 4, $TargetC, 5, 3, 1, 5, 3, 1, 'CM-1', 'Compound Herren - 9.-12. Platz', 0, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeC, $DistanceC, 'CM', '0', '0', 9);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetC, 5, 3, 1, 5, 3, 1, 'CM-2', 'Compound Herren - 5.-8. Platz', 0, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeC, $DistanceC, 'CM', '0', '0', 5);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetC, 5, 3, 1, 5, 3, 1, 'CM-3', 'Compound Herren - 13.-16. Platz', 0, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeC, $DistanceC, 'CM-1', '0', '0', 13);

            CreateEvent($TourId, $i++, 0, 0, 4, $TargetC, 5, 3, 1, 5, 3, 1, 'CW-1', 'Compound Damen - 9.-12. Platz', 0, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeC, $DistanceC, 'CW', '0', '0', 9);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetC, 5, 3, 1, 5, 3, 1, 'CW-2', 'Compound Damen - 5.-8. Platz', 0, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeC, $DistanceC, 'CW', '0', '0', 5);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetC, 5, 3, 1, 5, 3, 1, 'CW-3', 'Compound Damen - 13.-16. Platz', 0, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeC, $DistanceC, 'CW-1', '0', '0', 13);

            CreateEvent($TourId, $i++, 0, 0, 4, $TargetB, 5, 3, 1, 5, 3, 1, 'BM-1', 'Blankbogen Herren - 9.-12. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeB, $DistanceB, 'BM', '0', '0', 9);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetB, 5, 3, 1, 5, 3, 1, 'BM-2', 'Blankbogen Herren - 5.-8. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeB, $DistanceB, 'BM', '0', '0', 5);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetB, 5, 3, 1, 5, 3, 1, 'BM-3', 'Blankbogen Herren - 13.-16. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeB, $DistanceB, 'BM-1', '0', '0', 13);

            CreateEvent($TourId, $i++, 0, 0, 4, $TargetB, 5, 3, 1, 5, 3, 1, 'BW-1', 'Blankbogen Damen - 9.-12. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeB, $DistanceB, 'BW', '0', '0', 9);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetB, 5, 3, 1, 5, 3, 1, 'BW-2', 'Blankbogen Damen - 5.-8. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeB, $DistanceB, 'BW', '0', '0', 5);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetB, 5, 3, 1, 5, 3, 1, 'BW-3', 'Blankbogen Damen - 13.-16. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeB, $DistanceB, 'BW-1', '0', '0', 13);

            CreateEvent($TourId, $i++, 0, 0, 4, $TargetT, 5, 3, 1, 5, 3, 1, 'TM-1', 'Traditional Herren - 9.-12. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeT, $DistanceT, 'TM', '0', '0', 9);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetT, 5, 3, 1, 5, 3, 1, 'TM-2', 'Traditional Herren - 5.-8. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeT, $DistanceT, 'TM', '0', '0', 5);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetT, 5, 3, 1, 5, 3, 1, 'TM-3', 'Traditional Herren - 13.-16. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeT, $DistanceT, 'TM-1', '0', '0', 13);

            CreateEvent($TourId, $i++, 0, 0, 4, $TargetT, 5, 3, 1, 5, 3, 1, 'TW-1', 'Traditional Damen - 9.-12. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeT, $DistanceT, 'TW', '0', '0', 9);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetT, 5, 3, 1, 5, 3, 1, 'TW-2', 'Traditional Damen - 5.-8. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeT, $DistanceT, 'TW', '0', '0', 5);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetT, 5, 3, 1, 5, 3, 1, 'TW-3', 'Traditional Damen - 13.-16. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeT, $DistanceT, 'TW-1', '0', '0', 13);

            CreateEvent($TourId, $i++, 0, 0, 4, $TargetT, 5, 3, 1, 5, 3, 1, 'LM-1', 'Langbogen Herren - 9.-12. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeT, $DistanceT, 'LM', '0', '0', 9);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetT, 5, 3, 1, 5, 3, 1, 'LM-2', 'Langbogen Herren - 5.-8. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeT, $DistanceT, 'LM', '0', '0', 5);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetT, 5, 3, 1, 5, 3, 1, 'LM-3', 'Langbogen Herren - 13.-16. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeT, $DistanceT, 'LM-1', '0', '0', 13);

            CreateEvent($TourId, $i++, 0, 0, 4, $TargetT, 5, 3, 1, 5, 3, 1, 'LW-1', 'Langbogen Damen - 9.-12. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeT, $DistanceT, 'LW', '0', '0', 9);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetT, 5, 3, 1, 5, 3, 1, 'LW-2', 'Langbogen Damen - 5.-8. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeT, $DistanceT, 'LW', '0', '0', 5);
            CreateEvent($TourId, $i++, 0, 0, 2, $TargetT, 5, 3, 1, 5, 3, 1, 'LW-3', 'Langbogen Damen - 13.-16. Platz', 1, FINAL_NO_ELIM, MATCH_SEP_FROM_4, 0, 0, '', '', $TargetSizeT, $DistanceT, 'LW-1', '0', '0', 13);

// Create all Women Sub-Events (Individuals only)
            foreach (array('R'=>'R_','C'=>'C_','B'=>'B_','L'=>'L_','T'=>'T_') as $kDiv=>$vDiv) {
                $clsTmpArr = array('W','U21W','U18W','50W','60W','65W');
                if($kDiv == 'R' AND $TourType == 3) {
                    $clsTmpArr = array('W','U21W');
                } else if($kDiv == 'T') {
                    $clsTmpArr = array('W','U21W','50W','60W','65W');
                    }
                foreach($clsTmpArr as $kClass=>$vClass) {
                    foreach(array('-1','-2','-3') as $MySubEvents) {
                        $MyString = str_replace('_','W',$vDiv).$MySubEvents;
                        InsertClassEvent($TourId, 0, 1, $MyString, $kDiv,  $vClass);
                    }
                }
// Create all Men Sub-Events (Individuals only)
                $clsTmpArr = array('M','U21M','U18M','50M','60M','65M');
                if($kDiv == 'R' AND $TourType == 3) {
                    $clsTmpArr = array('M','U21M');
                } else if($kDiv == 'T') {
                    $clsTmpArr = array('M','U21M','50M','60M','65M');
                }
                foreach($clsTmpArr as $kClass=>$vClass) {
                    foreach(array('-1','-2','-3') as $MySubEvents) {
                        $MyString = str_replace('_','M',$vDiv).$MySubEvents;
                        InsertClassEvent($TourId, 0, 1, $MyString, $kDiv,  $vClass);
                    }
                }
            }
// END all Sub-Events

            safe_w_sql("UPDATE Events SET EvMedals=0 WHERE EvCode IN ('RM-1','RM-2','RM-3','RW-1','RW-2','RW-3','CM-1','CM-2','CM-3','CW-1','CW-2','CW-3','BM-1','BM-2','BM-3','BW-1','BW-2','BW-3','TM-1','TM-2','TM-3','TW-1','TW-2','TW-3','LM-1','LM-2','LM-3','LW-1','LW-2','LW-3') AND EvTournament=$TourId");
        }

        // END Loosers Matches

	// Finals & TeamFinals
	CreateFinals($TourId);
}


// Default Target
switch($TourType) {
    case 1:
        CreateTargetFace($TourId, 1, '~Default', '%', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
        CreateTargetFace($TourId, 2, '~50: 5-X/30: 5-X', 'REG-^R|^C', '',TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
        CreateTargetFace($TourId, 3, '~DefaultW0', 'REG-^PW0', '',TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
        break;
    case 2:
        CreateTargetFace($TourId, 1, '~Default', '%', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
        CreateTargetFace($TourId, 2, '~50: 5-X/30: 5-X', 'REG-^R|^C', '',TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80,TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
        CreateTargetFace($TourId, 3, '~DefaultW0', 'REG-^PW0', '',TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_FULL, 80, TGT_OUT_FULL, 80,TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
        break;
    case 3:
        switch($SubRule) {
            case '1':
            case '3':
                CreateTargetFace($TourId, 1, '~Default', 'REG-^R|^B|^T|^L|^PR|^CU13|^PCU13|^PW1U13|^PW0', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
                CreateTargetFace($TourId, 2, '~DefaultCO', 'REG-^CU18|^CU21|^C50|^C60|^C65|^C[M|W]$', '1', TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
                CreateTargetFace($TourId, 3, '~DefaultCO', 'REG-^PCU18|^PCU21|^PC50|^PC60|^PC65|^PC[M|W]$', '1', TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
                CreateTargetFace($TourId, 4, '~Default', 'REG-^PW1U15|^PW1U18|^PW1U21|^PW150|^PW160|^PW165|^CU15|^PCU15|^PW1[M|W]$', '1', TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
                break;
            case '2':
                CreateTargetFace($TourId, 1, '~Default', 'REG-^R|^PR|^B|^T|^L|^PW0', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
                CreateTargetFace($TourId, 2, '~DefaultCO', 'REG-^C|^PC', '1', TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
                CreateTargetFace($TourId, 3, '~Default', 'REG-^PW1', '1', TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
                break;
        }
        break;
    case 5:
        CreateTargetFace($TourId, 1, '~Default', '%', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
        break;
	case 6:
        switch($SubRule) {
            case '1':
            case '3':
                CreateTargetFace($TourId, 1, '~Default', 'REG-^RU18|^RU21|^R50|^R60|^R65|^R[M|W]$|^PR50|^PR60|^PR65|^PR[M|W]$', '1', TGT_IND_6_big10, 40, TGT_IND_6_big10, 40);
                CreateTargetFace($TourId, 2, '~DefaultCO', 'REG-^CU18|^CU21|^C50|^C60|^C65|^C[M|W]$|^PCU18|^PCU21|^PC50|^PC60|^PC65|^PC[M|W]$', '1', TGT_IND_6_small10, 40, TGT_IND_6_small10, 40);
                CreateTargetFace($TourId, 3, '~Default', 'REG-^RU13|^RU15|^CU13|^CU15|^PRU13|^PRU15|^PRU18|^PRU21|^PCU13|^PCU15', '1', TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
                CreateTargetFace($TourId, 4, '~Default', 'REG-^B|^PW1U21|^PW150|^PW160|^PW1[M|W]$', '1', TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
                CreateTargetFace($TourId, 5, '~Default', 'REG-^TU13|^TU15|^TU18|^L', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
                CreateTargetFace($TourId, 6, '~Default', 'REG-^TU21|^T50|^T60|^T65|^T[M|W]$', '1', TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
                CreateTargetFace($TourId, 7, '~Default', 'REG-^PW1U13|^PW1U15|^PW0U13|^PW0U15', '1', TGT_IND_1_big10, 80, TGT_IND_1_big10, 80);
                CreateTargetFace($TourId, 8, '~Default', 'REG-^PW1U18|^PW165|^PW0U18|^PW0U21|^PW050|^PW060|^PW065|^PW0[M|W]$', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
                break;
            case '2':
                CreateTargetFace($TourId, 1, '~Default', 'REG-^R|^PR', '1', TGT_IND_6_big10, 40, TGT_IND_6_big10, 40);
                CreateTargetFace($TourId, 2, '~DefaultCO', 'REG-^C|^PC', '1', TGT_IND_6_small10, 40, TGT_IND_6_small10, 40);
                CreateTargetFace($TourId, 3, '~Default', 'REG-^B|^T|^PW1', '1', TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
                CreateTargetFace($TourId, 4, '~Default', 'REG-^L|^PW0', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
                break;
        }
        break;
    case 7:
        if($SubRule==1) {
            CreateTargetFace($TourId, 1, '~Default', 'REG-^RU18|^RU21|^R50|^R60|^R65|^R[M|W]$|^PRU18|^PRU21|^PR50|^PR60|^PR65|^PR[M|W]$', '1', TGT_IND_6_big10, 60, TGT_IND_6_big10, 60);
            CreateTargetFace($TourId, 2, '~DefaultCO', 'REG-^CU18|^CU21|^C50|^C60|^C65|^C[M|W]$|^PCU18|^PCU21|^PC50|^PC60|^PC65|^PC[M|W]$', '1', TGT_IND_6_small10, 60, TGT_IND_6_small10, 60);
            CreateTargetFace($TourId, 3, '~Default', 'REG-^RU13|^RU15|^CU13|^CU15|^PRU13|^PRU15|^PCU13|^PCU15', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
            CreateTargetFace($TourId, 4, '~Default', 'REG-^B|^PW1U21|^PW150|^PW160|^PW1[M|W]$', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
            CreateTargetFace($TourId, 5, '~Default', 'REG-^TU13|^TU15|^TU18|^L|^PW0', '1', TGT_IND_1_big10, 80, TGT_IND_1_big10, 80);
            CreateTargetFace($TourId, 6, '~Default', 'REG-^TU21|^T50|^T60|^T65|^T[M|W]$', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
            CreateTargetFace($TourId, 7, '~Default', 'REG-^PW1U13|^PW1U15|^PW1U18|^PW165', '1', TGT_IND_1_big10, 80, TGT_IND_1_big10, 80);
        } else {
            CreateTargetFace($TourId, 1, '~Default', 'REG-^R|^PR', '1', TGT_IND_6_big10, 60, TGT_IND_6_big10, 60);
            CreateTargetFace($TourId, 2, '~DefaultCO', 'REG-^C|^PC', '1', TGT_IND_6_small10, 60, TGT_IND_6_small10, 60);
            CreateTargetFace($TourId, 3, '~Default', 'REG-^B|^T|^PW1', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
            CreateTargetFace($TourId, 4, '~Default', 'REG-^L|^PW0', '1', TGT_IND_1_big10, 80, TGT_IND_1_big10, 80);
        }
        break;
    case 8:
        if($SubRule==1) {
            CreateTargetFace($TourId, 1, '~Default', 'REG-^RU18|^RU21|^R50|^R60|^R65|^R[M|W]$|^PRU18|^PRU21|^PR50|^PR60|^PR65|^PR[M|W]$', '1', TGT_IND_6_big10, 60, TGT_IND_6_big10, 60, TGT_IND_6_big10, 40, TGT_IND_6_big10, 40);
            CreateTargetFace($TourId, 2, '~DefaultCO', 'REG-^CU18|^CU21|^C50|^C60|^C65|^C[M|W]$|^PCU18|^PCU21|^PC50|^PC60|^PC65|^PC[M|W]$', '1', TGT_IND_6_small10, 60, TGT_IND_6_small10, 60, TGT_IND_6_small10, 40, TGT_IND_6_small10, 40);
            CreateTargetFace($TourId, 3, '~Default', 'REG-^RU13|^RU15|^CU13|^CU15|^PRU13|^PRU15|^PCU13|^PCU15', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60, TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
            CreateTargetFace($TourId, 4, '~Default', 'REG-^B|^PW1U21|^PW150|^PW160|^PW1[M|W]$', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60, TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
            CreateTargetFace($TourId, 5, '~Default', 'REG-^TU13|^TU15|^TU18|^L', '1', TGT_IND_1_big10, 80, TGT_IND_1_big10, 80, TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
            CreateTargetFace($TourId, 6, '~Default', 'REG-^TU21|^T50|^T60|^T65|^T[M|W]$', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60, TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
            CreateTargetFace($TourId, 7, '~Default', 'REG-^PW1U13|^PW1U15|^PW0U13|^PW0U15', '1', TGT_IND_1_big10, 80, TGT_IND_1_big10, 80, TGT_IND_1_big10, 80, TGT_IND_1_big10, 80);
            CreateTargetFace($TourId, 8, '~Default', 'REG-^PW1U18|^PW165|^PW0U18|^PW0U21|^PW050|^PW060|^PW065|^PW0[M|W]$', '1', TGT_IND_1_big10, 80, TGT_IND_1_big10, 80, TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);
        } else {
            CreateTargetFace($TourId, 1, '~Default', 'REG-^R|^PR', '1', TGT_IND_6_big10, 60, TGT_IND_6_big10, 60, TGT_IND_6_big10, 40, TGT_IND_6_big10, 40);
            CreateTargetFace($TourId, 2, '~DefaultCO', 'REG-^C|^PC', '1', TGT_IND_6_small10, 60, TGT_IND_6_small10, 60, TGT_IND_6_small10, 40, TGT_IND_6_small10, 40);
            CreateTargetFace($TourId, 3, '~Default', 'REG-^B|^T|^PW1', '1', TGT_IND_1_big10, 60, TGT_IND_1_big10, 60, TGT_IND_1_big10, 40, TGT_IND_1_big10, 40);
            CreateTargetFace($TourId, 4, '~Default', 'REG-^L|^PW0', '1', TGT_IND_1_big10, 80, TGT_IND_1_big10, 80, TGT_IND_1_big10, 60, TGT_IND_1_big10, 60);

        }
		break;
    case 37:
        if ($SubRule==1) {
            CreateTargetFace($TourId, 1, '~Default', 'REG-^R|^B|^T|^L|^PR|^CU13|^PCU13|^PW1U13|^PW0', '1', TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
            CreateTargetFace($TourId, 2, '~DefaultCO', 'REG-^CU18|^CU21|^C50|^C60|^C65|^C[M|W]$', '1', TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80,TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
            CreateTargetFace($TourId, 3, '~DefaultCO', 'REG-^PCU18|^PCU21|^PC50|^PC60|^PC65|^PC[M|W]$', '1', TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80,TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
            CreateTargetFace($TourId, 4, '~Default', 'REG-^PW1U15|^PW1U18|^PW1U21|^PW150|^PW160|^PW165|^PW1[M|W]$|^CU15|^PCU15', '1', TGT_OUT_FULL, 80, TGT_OUT_FULL, 80,TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
        } else {
            CreateTargetFace($TourId, 1, '~Default', 'REG-^R|^PR|^B|^T|^L|^PW0', '1',  TGT_OUT_FULL, 122, TGT_OUT_FULL, 122,TGT_OUT_FULL, 122, TGT_OUT_FULL, 122);
            CreateTargetFace($TourId, 2, '~DefaultCO', 'REG-^C|^PC', '1', TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80,TGT_OUT_5_big10, 80, TGT_OUT_5_big10, 80);
            CreateTargetFace($TourId, 3, '~Default', 'REG-^PW1', '1', TGT_OUT_FULL, 80, TGT_OUT_FULL, 80,TGT_OUT_FULL, 80, TGT_OUT_FULL, 80);
        }
        break;
}

// create a first distance prototype
CreateDistanceInformation($TourId, $DistanceInfoArray, 24, 4);

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
