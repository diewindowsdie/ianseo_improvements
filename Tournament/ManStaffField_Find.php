<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');

$JSON=array('error' => 0, 'rows' => array());

if(!CheckTourSession() or !hasFullACL(AclCompetition, 'cData', AclReadWrite) or empty($_REQUEST['act'])) {
	JsonOut($JSON);
}

switch($_REQUEST['act']) {
    case 'searchCountry':
        if(empty($_REQUEST['CountryCode'])) {
            JsonOut($JSON);
        }
        $query = "select CoNameComplete from Countries where CoCode = " . StrSafe_DB($_REQUEST['CountryCode']) . " and CoTournament = " . $_SESSION['TourId'] . ";";
        $result = safe_r_SQL($query);
        $JSON['rows'][] = safe_fetch($result)->CoNameComplete;
        break;
	case 'find':
		if(empty($_REQUEST['Code'])) {
			JsonOut($JSON);
		}
		$q=safe_r_sql("("
			."select '1' as LUE, LueFamilyName as FamName, LueName as GivName, '' as LastName, '' as Accred, LueSex as Gender, LueCountry as CoCode, LueCoShort as CoName from LookUpEntries inner join Tournament on ToId={$_SESSION['TourId']} and ToIocCode=LueIocCode where LueCode=".StrSafe_DB($_REQUEST['Code'])
			.") union ("
			."select '0' as LUE, EnFirstName as FamName, EnName as GivName, '' as LastName, '' as Accred, EnSex as Gender, CoCode, CoName from Entries inner join Countries on CoId=EnCountry and CoTournament=EnTournament where EnTournament={$_SESSION['TourId']} and EnCode=".StrSafe_DB($_REQUEST['Code'])."
			)");

		while($r=safe_fetch($q)) {
			$JSON['rows'][]=$r;
		}

		if(!$JSON['rows']) {
			$JSON['rows'][]=array(
				'LUE'=>'1',
				'FamName'=>'',
				'GivName' =>'',
                'LastName' =>'',
                'Accred' =>'',
				'Gender'=>'',
				'CoCode'=>'',
				'CoName'=>''
			);
		}
		break;
	case 'search':
		$Where=array(array(),array());
		if(!empty($_REQUEST['Code'])) {
			$Where[1][]="LueCode = ".StrSafe_DB($_REQUEST['Code']);
			$Where[0][]="EnCode = ".StrSafe_DB($_REQUEST['Code']);
		}
		if(!empty($_REQUEST['FamilyName'])) {
			$Where[1][]="LueFamilyName like '%".StrSafe_DB($_REQUEST['FamilyName'], true)."%'";
			$Where[0][]="EnFirstName like '%".StrSafe_DB($_REQUEST['FamilyName'], true)."%'";
		}
		if(!empty($_REQUEST['GivenName'])) {
			$Where[1][]="LueName like '%".StrSafe_DB($_REQUEST['GivenName'], true)."%'";
			$Where[0][]="EnName like '%".StrSafe_DB($_REQUEST['GivenName'], true)."%'";
		}
        //tournament entries has no patronymic/last name and no accreditation info, so we cannot search by it and no need to add conditions like above
		if(isset($_REQUEST['Gender']) and preg_match("/^[01]$/",$_REQUEST['Gender'])) {
			$Where[1][]="LueSex = ".intval($_REQUEST['Gender']);
			$Where[0][]="EnSex = ".intval($_REQUEST['Gender']);
		}
		if(!empty($_REQUEST['CountryCode'])) {
			$Where[1][]="LueCountry like '%".StrSafe_DB($_REQUEST['CountryCode'], true)."%'";
			$Where[0][]="CoCode like '%".StrSafe_DB($_REQUEST['CountryCode'], true)."%'";
		}
		if(!empty($_REQUEST['CountryName'])) {
			$Where[1][]="LueCoShort like '%".StrSafe_DB($_REQUEST['CountryName'], true)."%'";
			$Where[0][]="CoName like '%".StrSafe_DB($_REQUEST['CountryName'], true)."%'";
		}
		if(!$Where[0]) {
			JsonOut($JSON);
		}
		$q=safe_r_sql("("
			."SELECT '1' as LUE, LueCode as Code, LueFamilyName as FamName, LueName as GivName, LueSex as Gender, LueCountry as CoCode, LueCoShort as CoName, if(LueCtrlCode=0,'',DATE_FORMAT(LueCtrlCode,'" . get_text('DateFmtDB') . "')) as DOB 
				from LookUpEntries 
			    inner join Tournament on ToId={$_SESSION['TourId']} and ToIocCode=LueIocCode 
				where ".implode(' and ', $Where[1])
			.") union ("
			."SELECT '0' as LUE, EnCode as Code, EnFirstName as FamName, EnName as GivName, EnSex as Gender, CoCode, CoName, if(EnDob=0,'',DATE_FORMAT(EnDob,'" . get_text('DateFmtDB') . "')) as DOB 
				from Entries 
			    inner join Countries on CoId=EnCountry and CoTournament=EnTournament 
				where EnTournament={$_SESSION['TourId']} and ".implode(' and ', $Where[0])."
			)
			order by FamName, GivName, LUE");

		while($r=safe_fetch($q)) {
			$JSON['rows'][$r->Code]=$r;
		}
		break;
}

JsonOut($JSON);
