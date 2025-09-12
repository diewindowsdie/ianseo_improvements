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
global $CFG;

if(!isset($_REQUEST['Day']) or !isset($_REQUEST['Start']) or !isset($_REQUEST['Order']) or !isset($_REQUEST['Duration']) or !isset($_REQUEST['Title']) or !isset($_REQUEST['SubTitle']) or !isset($_REQUEST['Text']) or !isset($_REQUEST['Shift'])) {
    JsonOut($JSON);
}

$q=array("SchTournament={$_SESSION['TourId']}");
$Order=1;
$day='';
$start='00:00:00';

// Day
$Value=$_REQUEST['Day'];
if(!$Value or $Value=='-') {
    $Value='';
} elseif(strtolower(substr($Value, 0, 1))=='d') {
    $Value=date('Y-m-d', strtotime(sprintf('%+d days', substr($Value, 1) -1), $_SESSION['ToWhenFromUTS']));
} else {
    $Value=CleanDate($Value);
}
if($Value) {
    $q[]="SchDay='$Value'";
    $day=$Value;
} else {
    $JSON['msg']=get_text('ErrDateGeneric', 'Errors');
    JsonOut($JSON);
}

// Start
$Value=$_REQUEST['Start'];
if(!$Value or $Value=='-') {
    $Value='';
} else {
    $t=explode(':', $Value);
    if(count($t)==1) {
        $t[1]=$t[0]%60;
        $t[0]= intval($t[0]/60);
    }
    $Value=sprintf('%02d:%02d:00', $t[0], $t[1]);
    $start=$Value;
}
$q[] = "SchStart='$Value'";

// Duration
$q[] = "SchDuration=".intval($_REQUEST['Duration']);

// Title, SubTitle, Text
$q[]= "SchTitle=".StrSafe_DB($_REQUEST['Title']);
$q[]= "SchSubTitle=".StrSafe_DB($_REQUEST['SubTitle']);
$q[]= "SchText=".StrSafe_DB($_REQUEST['Text']);

// Shift if any
if($_REQUEST['Shift']==='') {
    $q[]= "SchShift=null";
} else {
    $q[]= "SchShift=".intval($_REQUEST['Shift']);
}

// Order
$Order=intval($_REQUEST['Order']);
if(!$Order) {
    $rs=safe_r_sql("select max(SchOrder) as MaxOrder from Scheduler where SchTournament={$_SESSION['TourId']} and SchDay='$day' and SchStart='$start'");
    if($r=safe_fetch($rs)) {
        $Order=$r->MaxOrder+1;
    }
}
$q[] = "SchOrder=$Order";

$SchUid=md5(uniqid(mt_rand(), true));
safe_w_SQL("insert into Scheduler set SchUID='$SchUid', ".implode(',', $q)." on duplicate key update SchOrder=SchOrder+1, ".implode(',', $q));

$Schedule=new Scheduler();
$Schedule->ROOT_DIR=$CFG->ROOT_DIR;
$JSON['error']=0;
$JSON['txt']=getScheduleTexts();
$JSON['sch']=$Schedule->getScheduleHTML('SET');
JsonOut($JSON);
