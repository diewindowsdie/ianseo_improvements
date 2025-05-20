<?php

require_once(dirname(dirname(__FILE__)) . '/config.php');

$CardType = ($_REQUEST['CardType']??'A');
$CardNumber = intval($_REQUEST['CardNumber']??0);
$CardPage = intval($_REQUEST['CardPage']??1);

$IceFilter="IceTournament={$_SESSION['TourId']} and IceCardType='{$CardType}' and IceCardNumber={$CardNumber} and IceCardPage={$CardPage}";

if(isset($JSON)) {
	if(!CheckTourSession()) {
		JsonOut($JSON);
	}

	if(($CardType=='A' and !$lvl = hasFullACL(AclAccreditation, 'acSetup', AclReadWrite)) OR
        ($CardType=='Q' and !$lvl = hasFullACL(AclQualification, '', AclReadWrite)) OR
        ($CardType=='E' and !$lvl = hasFullACL(AclEliminations, '', AclReadWrite)) OR
        ($CardType=='I' and !$lvl = hasFullACL(AclIndividuals, '', AclReadWrite)) OR
        ($CardType=='T' and !$lvl = hasFullACL(AclTeams, '', AclReadWrite)) OR
        (($CardType=='Y' or $CardType=='Z') and !$lvl = hasFullACL(AclCompetition, 'cPrintouts', AclReadWrite))) {
		JsonOut($JSON);
	}
} else {
	CheckTourSession(true);
	if($CardType=='A') {
		checkFullACL(AclAccreditation, 'acSetup',AclReadWrite);
	} else if($CardType=='Q') {
		checkFullACL(AclQualification, '', AclReadWrite);
	} else if($CardType=='E') {
		checkFullACL(AclEliminations, '', AclReadWrite);
	} else if($CardType=='I') {
		checkFullACL(AclIndividuals, '', AclReadWrite);
	} else if($CardType=='T') {
        checkFullACL(AclTeams, '', AclReadWrite);
	} else if($CardType=='Y' OR $CardType=='Z') {
        checkFullACL(AclCompetition, 'cPrintouts', AclReadWrite);
	}
}


function switchOrder($Old, $New, $CardType, $CardNumber, $CardPage) {
	global $CFG;
	if($New==$Old or !$New) return;
	$min=min($New, $Old);
	$max=max($New, $Old);
	safe_w_sql("update IdCardElements set IceNewOrder=IceOrder where IceCardType='$CardType' and IceCardNumber='$CardNumber' and IceCardPage='$CardPage' and IceTournament={$_SESSION['TourId']}");
	if($New<$Old) {
		safe_w_sql("update IdCardElements set IceNewOrder=IceOrder+1 where IceCardType='$CardType' and IceCardNumber='$CardNumber' and IceCardPage='$CardPage' and IceTournament={$_SESSION['TourId']} and IceOrder between $min and $max");
	} else {
		safe_w_sql("update IdCardElements set IceNewOrder=IceOrder-1 where IceCardType='$CardType' and IceCardNumber='$CardNumber' and IceCardPage='$CardPage' and IceTournament={$_SESSION['TourId']} and IceOrder between $min and $max");
	}
	safe_w_sql("update IdCardElements set IceNewOrder=$New where IceCardType='$CardType' and IceCardNumber='$CardNumber' and IceCardPage='$CardPage' and IceTournament={$_SESSION['TourId']} and IceOrder=$Old");
	safe_w_sql("update IdCardElements set IceOrder=IceNewOrder where IceCardType='$CardType' and IceCardNumber='$CardNumber' and IceCardPage='$CardPage' and IceTournament={$_SESSION['TourId']}");

	// removes all pictures
	$Images=array('Image','ImageSvg','RandomImage');
	foreach($Images as $type) {

		foreach(glob($CFG->DOCUMENT_PATH . 'TV/Photos/' . $_SESSION['TourCodeSafe'] . '-' . $type . '-' . $CardType . '-'. $CardNumber . '-'. $CardPage . '-*') as $file) {
			unlink($file);
		}
	}

	// redraws all pictures
	$SQL="select * from IdCardElements where IceContent>'' and IceType in (".implode(',', StrSafe_DB($Images)).") and IceCardType='$CardType' and IceCardNumber='$CardNumber' and IceCardPage='$CardPage' and IceTournament={$_SESSION['TourId']}";
	$q=safe_r_sql($SQL);
	while($r=safe_fetch($q)) {
		if($r->IceType=='ImageSvg') {
			$ImName=$CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-'.$r->IceType.'-'.$r->IceCardType.'-'.$r->IceCardNumber . '-'. $r->IceCardPage.'-'.$r->IceOrder.'.svg';
			if($im=@gzinflate($r->IceContent)) {
				file_put_contents($ImName, $im);
			}
		} else {
			$ImName=$CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-'.$r->IceType.'-'.$r->IceCardType.'-'.$r->IceCardNumber . '-'. $r->IceCardPage.'-'.$r->IceOrder.'.jpg';
			if($im=@imagecreatefromstring($r->IceContent)) {
				imagejpeg($im, $ImName, 90);
			}
		}
	}
}
