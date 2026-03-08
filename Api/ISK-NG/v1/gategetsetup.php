<?php
require_once('Accreditation/Lib.php');

$Options=GetParameter('AccessApp', false, array(), true);
if(empty($Options)) {
    $res = array('action' => 'gategetsetup', 'error' => 1, 'device' => $req->device);
    return;
}

// get all the accreditation QRcodes for this competition...
$regexpList = array();
$q=safe_r_sql("select IceContent, ToId, ToCode, ToName
    from IdCardElements 
    inner join Tournament on ToId=IceTournament 
    where IceType IN ('AthQrCode','AthBarCode') and IceTournament in (".implode(',', array_keys($Options)).")");
while ($r = safe_fetch($q)) {
    $replacements = array(
        '\\{ENCODE\\}' => '(.+?)',
        '\\{COUNTRY\\}' => '(.+?)',
        '\\{DIVISION\\}' => '(.+?)',
        '\\{CLASS\\}' => '(.+?)',
        '\\{TOURNAMENT\\}' => '(.+?)',
    );
    $RegExp = preg_quote('{ENCODE}-{DIVISION}-{CLASS}', '/');
    if ($r->IceContent != '') {
        $RegExp = preg_quote($r->IceContent, '/');
    }
    $RegExp = '^' . str_replace(array_keys($replacements), array_values($replacements), $RegExp) . '$';
    $RegArray = getIceRegExpMatches($r->IceContent);
    $RegArray['formula'] = $RegExp;
    $RegArray['key'] = array();
    if($RegArray["tocode"] != -1) {
        $RegArray['key'][] = 'tocode';
    }
    $RegArray['key'][] = 'encode';
    if($RegArray["country"] != -1) {
        $RegArray['key'][] = 'country';
    }
    if($RegArray["division"] != -1) {
        $RegArray['key'][] = 'division';
    }

    $RegArray['keyseparator'] = '|';
    $RegArray['competition'] = $r->ToCode;
    if (!in_array($RegArray, $regexpList)) {
        if(getModuleParameter("ExtraAddOns","AddOnsEnable","0", $r->ToId)) {
            $RegArray['addOns'] = (object) getModuleParameter("ExtraAddOns", "AddOnsList", array(),$r->ToId);
        }
        $regexpList[$r->ToCode] = $RegArray;
    }
}

$AccZones = array();
foreach(range(0,6) as $zone) {
    $AccZones[$zone] = get_text('Area_'.$zone, 'Tournament');
}
$res = array(
    'action' => 'gategetsetup',
    'error' => 0,
    'device' => $req->device,
    'lookupMode' => 0,
    'validated' => 0,
    'checkGateFlow' => 0,
    'competingOnly' => 0,
    'showPictures' => 1,
    'accessZones' => (object) $AccZones,
    'competitions' => $regexpList
);