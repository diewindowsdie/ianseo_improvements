<?php
require_once('Accreditation/Lib.php');

$Options=GetParameter('AccessApp', false, array(), true);
if(empty($Options)) {
    $res = array('action' => 'gatepictures', 'error' => 1, 'apiVersion' => $req->apiVersion, 'device' => $req->device);
    return;
}

// get all the accreditation QRcodes for this competition...
$regexpList = array();
$q=safe_r_sql("select IceContent, ToCode from IdCardElements inner join Tournament on ToId=IceTournament where IceType IN ('AthQrCode') and IceTournament in (".implode(',', array_keys($Options)).")");
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
    $RegArray['competition'] = $r->ToCode;
    if (!in_array($RegArray, $regexpList)) {
        $regexpList[$r->ToCode] = $RegArray;
    }
}

// get all entries allowed by the setup
$tmpList=array();
$qEntry = "select EnId, IFNULL(localbib.EdExtra,EnCode) as AthCode, ToId, ToCode, CoCode, EnDivision, EnClass
		from Entries
		inner join Tournament on ToId=EnTournament
		inner join Countries on CoTournament=EnTournament and CoId=EnCountry
		LEFT JOIN ExtraData as localbib ON localbib.EdId=EnId and localbib.EdType='Z'
		WHERE EnTournament in (".implode(',', array_keys($Options)).")";
$q=safe_r_sql($qEntry);
while($r=safe_fetch($q)) {
    $key = $r->ToCode . '|' . $r->AthCode  . '|' . $r->CoCode . '|' . $r->EnDivision;
    if(array_key_exists($r->ToCode,$regexpList)) {
        $key = $r->ToCode . '|' . $r->AthCode . ($regexpList[$r->ToCode]["country"] != -1 ? '|' . $r->CoCode : '') . ($regexpList[$r->ToCode]["division"] != -1 ? '|' . $r->EnDivision : '');
    }
    if(file_exists($im=$CFG->DOCUMENT_PATH.'TV/Photos/'.$r->ToCode.'-En-'.$r->EnId.'.jpg')) {
        $tmpList[$key]='data:image/jpeg;base64,'.base64_encode(file_get_contents($im));
    }
}


$res = array(
    'action' => 'gatepictures',
    'error' => 0,
    'apiVersion' => $req->apiVersion,
    'device' => $req->device,
    'pictures' => $tmpList
);
