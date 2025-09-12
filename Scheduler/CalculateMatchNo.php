<?php
require_once(dirname(__FILE__, 2) . '/config.php');
$JSON=[
    'error'=>1,
];

if(!CheckTourSession() or !hasFullACL(AclCompetition, 'cSchedule', AclReadWrite)) {
    JsonOut($JSON);
}
$n=1;
safe_w_sql("update FinSchedule set FsOdfMatchName=0 where FsTournament={$_SESSION['TourId']}");
$q=safe_r_sql("select FSMatchNo, FSEvent, FSTeamEvent
		from FinSchedule 
		inner join Events on EvTournament=FSTournament and EvTeamEvent=FSTeamEvent and EvCode=FSEvent
		where FSMatchNo%2=0 and FSScheduledDate!=0 and FsTournament={$_SESSION['TourId']} 
		order by FsScheduledDate, FsScheduledTime, FsTeamEvent desc, EvProgr, FsMatchno");
while($r=safe_fetch($q)) {
    safe_w_sql("update FinSchedule 
			set FsOdfMatchName=$n 
			where FsMatchNo=$r->FSMatchNo 
			  	and FSEvent='$r->FSEvent'
			  	and FSTeamEvent=$r->FSTeamEvent
				and FsTournament={$_SESSION['TourId']}");
    $n++;
}
$JSON['error']=0;
JsonOut($JSON);