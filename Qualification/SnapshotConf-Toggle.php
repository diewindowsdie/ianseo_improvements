<?php

require_once(dirname(dirname(__FILE__)).'/config.php');

$JSON=array('error' => 1, 'status' => 0);

if(!CheckTourSession()) {
	JsonOut($JSON);
	die();
}
checkFullACL(AclQualification, '', AclReadWrite, false);

// toggle the snapshot feature
require_once('Common/Lib/Fun_Modules.php');
$status=intval(getModuleParameter('ISK-NG', 'Snapshot'));

setModuleParameter('ISK-NG', 'Snapshot', 1-$status);

$JSON['error']=0;
$JSON['status']=1-$status;

JsonOut($JSON);
