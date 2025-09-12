<?php
require_once(dirname(__FILE__, 2) . '/config.php');
require_once('Common/Lib/CommonLib.php');
require_once('Common/Lib/Fun_Modules.php');
$JSON=[
    'error'=>1,
    'msg'=>get_text('ErrGenericError', 'Errors'),
];

if(!CheckTourSession() or !hasFullACL(AclCompetition, 'cSchedule', AclReadWrite)) {
    JsonOut($JSON);
}

if(empty($_SESSION['ActiveSession'])) {
    $_SESSION['ActiveSession']=Get_Tournament_Option('ActiveSession',[]);
}

$JSON['error']=0;
if(in_array($_REQUEST['Activate'], $_SESSION['ActiveSession'])) {
    unset($_SESSION['ActiveSession'][array_search($_REQUEST['Activate'], $_SESSION['ActiveSession'])]);
    $JSON['active']=0;
} else {
    $_SESSION['ActiveSession'][]=$_REQUEST['Activate'];
    $JSON['active']=1;
}
Set_Tournament_Option('ActiveSession', $_SESSION['ActiveSession']);
runJack("ScheduleRunUpdate", $_SESSION['TourId'], array("ActiveSession"=>$_SESSION['ActiveSession'], "TourId"=>$_SESSION['TourId']));

JsonOut($JSON);
