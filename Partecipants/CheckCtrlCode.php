<?php
/*
													- UpdateCtrlCode.php -
	Controlla il codice fiscale dei tizio in Partecipants.php
	Decide anche come gestire le tendine delle classi
*/

define('debug',false);
$JSON=array('error' => 1, 'div'=>array(), 'age'=>array(),'clas'=>array(), 'dob' =>'');
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Common/Lib/Fun_DateTime.inc.php');
require_once('Fun_Partecipants.local.inc.php');

if (!CheckTourSession() or !hasFullACL(AclParticipants, 'pEntries',  AclReadOnly) or !isset($_REQUEST['d_e_EnCtrlCode']) or !isset($_REQUEST['d_e_EnSex'])) {
	JsonOut($JSON);
}

	$ctrlCode='';

	$Age='';
	$Sex=intval($_REQUEST['d_e_EnSex']);
	$Div=(empty($_REQUEST['d_e_EnDiv']) ? '' : $_REQUEST['d_e_EnDiv']);
	$Clas=(empty($_REQUEST['d_e_EnAgeClass']) ? '' : $_REQUEST['d_e_EnAgeClass']);
	if(!empty($_REQUEST['d_e_EnCtrlCode']) and $ctrlCode=ConvertDateLoc($_REQUEST['d_e_EnCtrlCode'])) {
		$Age=intval(substr($_SESSION['TourRealWhenTo'], 0, 4) - substr($ctrlCode, 0, 4));
	}

// Get the Divisions allowed based on Age (if any restriction applies)
// Age check not done if not an athlete
$Select1 = "select distinct DivId from Classes"
	. " inner join Divisions on DivTournament=ClTournament and DivAthlete=ClAthlete"
	. " where ClTournament={$_SESSION['TourId']}"
	. " AND (ClDivisionsAllowed='' or find_in_set(DivId, ClDivisionsAllowed))"
	. " AND ClSex in (-1, {$Sex})"
	. ($Age ? " and (ClAthlete!='1' or (ClAgeFrom<=$Age and ClAgeTo>=$Age))" : '')
	. " order by DivViewOrder ";

$RsCl = safe_r_sql($Select1);
while($MyRow=safe_fetch($RsCl)) {
	$JSON['div'][]=$MyRow->DivId;
}

// get the Age classes based on the division selected
$Select2 = "select distinct ClId, ClValidClass from Classes"
	. " inner join Divisions on DivTournament=ClTournament and DivAthlete=ClAthlete"
	. ($Div ? " AND DivId='$Div'" : '')
	. " where ClTournament={$_SESSION['TourId']}"
	. " AND (ClDivisionsAllowed='' or find_in_set(DivId, ClDivisionsAllowed))"
	. " AND ClSex in (-1, {$Sex})"
	. ($Age ? " and (ClAthlete!='1' or (ClAgeFrom<=$Age and ClAgeTo>=$Age))" : '')
	. " order by (ClAgeTo-ClAgeFrom) ASC, ClViewOrder, DivViewOrder ";
$RsCl = safe_r_sql($Select2);
$validClasses=array();
while($MyRow=safe_fetch($RsCl)) {
    if(!in_array($MyRow->ClId, $validClasses)) {
        $JSON['age'][] = $MyRow->ClId;
    }
    $validClasses = array_merge($validClasses, explode(',', $MyRow->ClValidClass));
}

if($JSON['age'] and !in_array($Clas, $JSON['age']) and $Clas!='') {
	$Clas=$JSON['age'][0];
}

// get the VALID classes based on the division and class selected
$Select3 = "select distinct ClValidClass from Classes"
	. " inner join Divisions on DivTournament=ClTournament and DivAthlete=ClAthlete"
	. ($Div ? " AND DivId='$Div'" : '')
	. " where ClTournament={$_SESSION['TourId']}"
	. " AND (ClDivisionsAllowed='' or find_in_set(DivId, ClDivisionsAllowed))"
	. " AND ClSex in (-1, {$Sex})"
	. ($Clas ? " AND ClId='$Clas'" : '')
	. ($Age ? " and (ClAthlete!='1' or (ClAgeFrom<=$Age and ClAgeTo>=$Age))" : '')
	. " order by ClViewOrder, DivViewOrder ";
$RsCl = safe_r_sql($Select3);
while($MyRow=safe_fetch($RsCl)) {
	$JSON['clas']=array_merge($JSON['clas'], explode(',', $MyRow->ClValidClass));
}

$JSON['clas'] = array_unique($JSON['clas']);

$JSON['dob']= RevertDate($ctrlCode);


$JSON['error']=0;
JsonOut($JSON);
