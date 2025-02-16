<?php
define('debug',false);	// settare a true per l'output di debug

require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Tournament/Fun_Tournament.local.inc.php');

$JSON=array('error' => 1, 'errormsg'=>get_text('MissingData','Errors'));

if (!CheckTourSession() or !hasFullACL(AclCompetition, 'cData', AclReadWrite) or IsBlocked(BIT_BLOCK_TOURDATA)
    or empty($_REQUEST['New_ScId']) or empty($_REQUEST['New_ScDescription'])or !isset($_REQUEST['New_ScViewOrder'])) {
	JsonOut($JSON);
}


if (preg_match('/[^a-z0-9]/i', $_REQUEST['New_ScId'])) {
    $JSON['errormsg']=get_text('InvalidCharacters','Errors', '<span class="text-danger">a-z A-Z 0-9</span>');
    JsonOut($JSON);
}

$Insert
	= "INSERT ignore INTO SubClass (ScId,ScTournament,ScDescription,ScViewOrder) "
	. "VALUES("
	. StrSafe_DB($_REQUEST['New_ScId']) . ","
	. intval($_SESSION['TourId']) . ","
	. StrSafe_DB($_REQUEST['New_ScDescription']) . ","
	. intval($_REQUEST['New_ScViewOrder']) . " "
	. ") ";
safe_w_sql($Insert);

if (!safe_w_affected_rows()) {
	$JSON['errormsg']=get_text('DuplicateEntry','Tournament');
    JsonOut($JSON);
}

$JSON['error']=0;

$JSON['scid'] =  $_REQUEST['New_ScId'];
$JSON['scdescr'] = $_REQUEST['New_ScDescription'];
$JSON['scprogr'] =  intval($_REQUEST['New_ScViewOrder']) ;

JsonOut($JSON);
