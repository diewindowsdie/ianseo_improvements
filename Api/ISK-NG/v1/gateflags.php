<?php
require_once('Accreditation/Lib.php');

$Options=GetParameter('AccessApp', false, array(), true);
if(empty($Options)) {
    $res = array('action' => 'gateflags', 'error' => 1, 'device' => $req->device);
    return;
}

// get all entries allowed by the setup
$tmpList=array();
$qEntry = "select distinct ToCode, CoCode
		from Entries
        inner join Tournament on ToId=EnTournament
		inner join Countries on CoTournament=EnTournament and CoId=EnCountry
		WHERE EnTournament in (".implode(',', array_keys($Options)).")";
$q=safe_r_sql($qEntry);
while($r=safe_fetch($q)) {
    $key = $r->ToCode . '|' . $r->CoCode;
    if(file_exists($im=$CFG->DOCUMENT_PATH.'TV/Photos/'.$r->ToCode.'-Fl-'.$r->CoCode.'.jpg')) {
        $tmpList[$r->ToCode . '|' . $r->CoCode]='data:image/jpeg;base64,'.base64_encode(file_get_contents($im));
    }
}


$res = array(
    'action' => 'gateflags',
    'error' => 0,
    'device' => $req->device,
    'flags' => $tmpList
);