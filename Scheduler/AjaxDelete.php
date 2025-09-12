<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
$JSON=[
    'error'=>1,
    'msg'=>get_text('ErrGenericError', 'Errors'),
];

if(!CheckTourSession() or !hasFullACL(AclCompetition, 'cSchedule', AclReadWrite)) {
    JsonOut($JSON);
}

require_once('./LibScheduler.php');

if(empty($_REQUEST['id']) or empty($_REQUEST['val'])) {
    JsonOut($JSON);
}
switch($_REQUEST['id']) {
    case 'Fld':
        // SchDay.'|'.$r->SchStart.'|'.$r->SchOrder
        list($Date, $Time, $Order)=explode('|', $_REQUEST['val']);
        safe_w_sql("delete from Scheduler where SchTournament={$_SESSION['TourId']} and SchDay=".StrSafe_DB($Date)." and SchStart=".StrSafe_DB($Time)." and SchOrder=".StrSafe_DB($Order));
        if(safe_w_affected_rows()) {
            $JSON=DistanceInfoData(true);
        }
        break;
    case 'WarmDelete':
        list($TeamEvent, $Phase, $Day, $MatchTime, $Time)=explode('|', $_REQUEST['val']);
        safe_w_sql("delete from FinWarmup
            where FwTournament={$_SESSION['TourId']}
                and FwTeamEvent='".($TeamEvent=='T' ? 1 : 0)."'
                and FwDay=".StrSafe_DB($Day)."
                and FwMatchTime=".StrSafe_DB($MatchTime)."
                and FwTime=".StrSafe_DB($Time.':00')."");
        if(safe_w_affected_rows()) {
            $JSON=DistanceInfoData(true);
        }
        break;
}

JsonOut($JSON);