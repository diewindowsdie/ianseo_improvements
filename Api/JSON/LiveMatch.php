<?php
require_once(dirname(__FILE__) . '/config.php');

$TourId = 0;
if(isset($_REQUEST['CompCode']) && preg_match("/^[a-z0-9_.-]+$/i", $_REQUEST['CompCode'])) {
	$TourId = getIdFromCode($_REQUEST['CompCode']);
}

$json_array=array();

require_once('Common/Lib/CommonLib.php');

if($r=getMatchLive($TourId)) {
    $json_array["LiveMatch"] = true;
    $json_array["Event"] = $r->Event;
    $json_array["Type"] = $r->Team;
    $json_array["MatchId"] = (($r->MatchNo % 2) == 0 ? $r->MatchNo : ($r->MatchNo-1));
} else {
    $json_array["LiveMatch"] = false;
}



// Return the json structure with the callback function that is needed by the app
SendResult($json_array);