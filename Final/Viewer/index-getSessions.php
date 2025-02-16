<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('Common/Fun_Phases.inc.php');

$JSON=array('error' => 1, 'data'=>array());
if(!CheckTourSession() or !hasFullACL(AclOutput, 'outTv', AclReadOnly)) {
    JsonOut($JSON);
}

$Sql = "SELECT distinct SesType, SesOrder, SesName, date_format(SesDtStart, '%d %M %H:%i') as SesDate
  FROM Session
  WHERE SesTournament=" . StrSafe_DB($_SESSION['TourId']) . " and SesType!='Q'
  order by SesDtStart, SesType, SesOrder";

$q=safe_r_sql($Sql);
while($r=safe_fetch($q)) {
    $JSON['data'][$r->SesType.$r->SesOrder] = $r->SesDate . ': '. $r->SesName;
    $JSON['error']=0;
}

JsonOut($JSON);