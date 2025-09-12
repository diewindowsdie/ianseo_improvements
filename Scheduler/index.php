<?php
require_once(dirname(__FILE__, 2) . '/config.php');
CheckTourSession(true);
$aclLevel = checkFullACL(AclCompetition, 'cSchedule', AclReadOnly);

require_once('Common/Fun_Sessions.inc.php');
require_once('Common/Lib/Fun_Modules.php');
require_once('Common/Lib/Fun_Scheduler.php');
require_once('./LibScheduler.php');

global $CFG;

if(!empty($_REQUEST['fop'])) {
	$Sched=new Scheduler();

    if($_REQUEST['includeUnscheduled']??'') {
        $Sched->FopIncludeUnscheduledDistances=true;
    }
	// defines the days
	if(!empty($_REQUEST['Days'])) {
		$DaysToPrint=array();
		foreach($_REQUEST['Days'] as $k => $v) {
            if($v=='on') {
                $Sched->DaysToPrint[]=date('Y-m-d', $_SESSION['ToWhenFromUTS'] + $k*86400);
            } else {
                $Sched->DaysToPrint[]=$v;
            }
		}
	}

	// defines the Locations by target (these will be printed on a single page)
	if(!empty($_REQUEST['Locations'])) {
		foreach($_REQUEST['Locations'] as $k=>$v) {
			$Sched->LocationsToPrint[]=$Sched->FopLocations[$k];
		}
		$Sched->SplitLocations=true;
	}

	if(!empty($_REQUEST['SesLocations'])) {
		foreach($_REQUEST['SesLocations'] as $v) {
			$Sched->SesLocations[]=$v;
		}
//		$Sched->SplitLocations=true;
	}

	if(!empty($_REQUEST['day'])) {
		if(strtolower(substr($_REQUEST['day'], 0, 1))=='d') {
			$Date=date('Y-m-d', strtotime(sprintf('%+d days', substr($_REQUEST['day'], 1) -1), $_SESSION['ToWhenFromUTS']));
		} else {
			$Date=CleanDate($_REQUEST['day']);
		}
		if($Date) $Sched->SingleDay=$Date;
	}
	$Sched->FOP();
	die();
}

if(!empty($_REQUEST['ods'])) {
	$Schedule=new Scheduler();
	$Schedule->exportODS($_SESSION['TourCode'].'.ods', 'a');
	die();
}

if(!empty($_REQUEST['ics'])) {
	$Schedule=new Scheduler();
	$Schedule->getScheduleICS(true);
	die();
}

$edit=(empty($_REQUEST['key']) ? '' : preg_replace('#[^0-9:| -]#sim', '', $_REQUEST['key']));

$JS_SCRIPT=array(
	'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Scheduler/Fun_AJAX_Scheduler.js"></script>',
	'<link href="'.$CFG->ROOT_DIR.'Scheduler/Scheduler.css" media="screen" rel="stylesheet" type="text/css">',
	phpVars2js([
		'titAdvanced' => get_text('Advanced'),
		'labelTargets' => get_text('Targets', 'Tournament').': #1-#N@Dist[@Cat[@Face]]',
		'labelLocation' => get_text('Location', 'Tournament'),
		'btnSubmit' => get_text('CmdUpdate'),
		'btnCancel' => get_text('CmdCancel'),
		'btnOk' => get_text('CmdOk'),
		'msgAreYouSure' => get_text('MsgAreYouSure'),
	]),
);
$PageBreaks=getModuleParameter('Schedule', 'PageBreaks', '');

$IncludeFA=true;
$IncludeJquery=true;
include('Common/Templates/head.php');

echo '<table class="Tabella w-100" id="mainSchedulerTable">
<tr><th class="Title">'.get_text('Schedule', 'Tournament').'</th></tr>
<tr class="Divider"><th></th></tr>
<tr><th class="TitleLeft p-2">
    <i id="cmdTogglePrintSchedule" class="fa-solid fa-caret-down fa-lg mr-1" onclick="togglePrintSchedule()"></i>'.get_text('MenuLM_PrintScheduling').'</th></tr><tr id="viewPrintSchedule"><td><div class="prnWrapper">
    <div class="mb-3 ml-1"><i id="cmd_printComplete" class="fa-solid fa-caret-down mr-1" onclick="togglePrintScheduleDetails(\'printComplete\')"></i><a onclick="printSchedule()" class="bold">'.get_text('CompleteSchedule', 'Tournament').'</a><input type="button" class="ml-5" value="'.get_text('Print', 'Tournament').'" onclick="printSchedule()">
        <ul id="opt_printComplete">
        <li><input type="checkbox" id="Finalists" checked="checked">'.get_text('SchIncFinalists','Tournament').'</li>
        <li><input type="checkbox" id="Ranking">'.get_text('SchAddRank','Tournament').'</li>
        <li><input type="checkbox" id="Daily">'.get_text('DailySchedule', 'Tournament').'</li>
        <li><input type="checkbox" id="NoLocations">'.get_text('NoLocations', 'Tournament').'</li>
        <li class="mb-2">'.get_text('PageBreakBeforeDays', 'Tournament').' (yyyy-mm-dd[,yyyy-mm-dd...])<input type="text" class="ml-3" size="40" id="PageBreaks" value="'.$PageBreaks.'"></li>
        <li><input type="checkbox" id="Today" onchange="chkOptions(this)">'.get_text('DaySchedule', 'Tournament').'<input type="date" class="ml-3" id="singleDaySchedule" min="'.$_SESSION['TourRealWhenFrom'].'" max="'.$_SESSION['TourRealWhenTo'].'"><i class="fa-solid fa-xmark ml-2" onclick="$(\'#singleDaySchedule\').val(\'\')"></i></li>
        <li><input type="checkbox" id="FromDay" onchange="chkOptions(this)">'.get_text('ScheduleFromDay', 'Tournament').'</a><input class="ml-3" type="date" id="fromDaySchedule" min="'.$_SESSION['TourRealWhenFrom'].'" max="'.$_SESSION['TourRealWhenTo'].'"><i class="fa-solid fa-xmark ml-2" onclick="$(\'#fromDaySchedule\').val(\'\')"></i></li>
        </ul>
    </div>
    <div class="mb-3 ml-1"><i id="cmd_export" class="fa-solid fa-caret-down mr-1" onclick="togglePrintScheduleDetails(\'export\')"></i><span class="bold">'.get_text('CmdExport', 'Tournament').'</span>
        <ul id="opt_export">
        <li><a onclick="exportODS()">'.get_text('MenuLM_OdsExport').'</a><input type="button" class="ml-5" value="'.get_text('CmdExport', 'Tournament').'" onclick="exportODS()"></li>
        <li><a onclick="exportICS()">'.get_text('ExportICS', 'Tournament').'</a><input type="button" class="ml-5" value="'.get_text('CmdExport', 'Tournament').'" onclick="exportICS()"></li>
        </ul>
    </div>
    <div class="mb-3 ml-1"><i id="cmd_printC58" class="fa-solid fa-caret-down mr-1" onclick="togglePrintScheduleDetails(\'printC58\')"></i><a onclick="printC58()" class="bold">'.get_text('DailySchedule', 'Tournament').' - C58</a><input type="button" class="ml-5" value="'.get_text('Print', 'Tournament').'" onclick="printC58()">
        <ul id="opt_printC58"><li><input type="checkbox" id="TeamComponents" checked="checked">'.get_text('SchIncFinalists','Tournament').'</li>';
$locationList = array();
foreach(GetSessions('F') as $ses) {
    echo '<li><input type="checkbox" sesValue="'.$ses->SesOrder.'" class="mr-2">'.dateRenderer($ses->SesDtStart, 'j M H:i') . ' - '. $ses->SesName.'</li>';
    $locationList[] = $ses->SesLocation;
}
$locationList = array_unique($locationList);
if(count($locationList) > 1) {
    echo '<li><input type="radio" name="locList" value="" locValue="" class="mr-2">'.get_text('AllLocations', 'Tournament').'</li>';
    foreach($locationList as $loc) {
        echo '<li><input type="radio" name="locList" value="'.$loc.'" locValue="'.$loc.'" class="mr-2">'.$loc.'</li>';
    }
}
echo '</ul></div>';

echo '<div class="mb-3 ml-1"><i id="cmd_printFOP" class="fa-solid fa-caret-down mr-1" onclick="togglePrintScheduleDetails(\'printFOP\')"></i><a onclick="printFOP()" class="bold">'.get_text('FopSetup').'</a><input type="button" class="ml-5" value="'.get_text('Print', 'Tournament').'" onclick="printFOP()">
        <ul id="opt_printFOP">';

// flag to include unscheduled qualifications
$q=safe_r_sql("select count(*) as Items from DistanceInformation where DiTournament={$_SESSION['TourId']} and DiDay=0");
if($r=safe_fetch($q) and $r->Items>0) {
    echo '<li><input type="checkbox" id="fopIncludUnscheduled" class="mr-2">'.get_text('IncludeUnscheduledQualification', 'Tournament').'</li>';
}

if(!$FopLocations=Get_Tournament_Option('FopLocations')) {
    $FopLocations=array();
    Set_Tournament_Option('FopLocations', $FopLocations);
}
$SesLocations=getSesLocations();

if($FopLocations or $SesLocations) {
    echo '<div><b>'.get_text('Location', 'Tournament').'</b></div>';
    echo '<li><input type="radio" name="fopLoc" value="" class="mr-2">'.get_text('AllLocations', 'Tournament').'</li>';
    if($FopLocations) {
    // FOP Location "old style"
        echo '<div><b>'.get_text('LocationByTarget', 'Tournament').'</b></div>';
        foreach($FopLocations as $idx => $loc) {
            echo '<li><input type="radio" name="fopLoc" ref="tgt" value="'.$idx.'" class="mr-2">'.$loc->Loc.' ('.get_text('TargetShort','Tournament').' '.$loc->Tg1.'-'.$loc->Tg2.')</li>';
        }
    }
    if($SesLocations) {
        // Session Locations
        echo '<div><b>'.get_text('LocationByPlace', 'Tournament').'</b></div>';
        foreach(getSesLocations() as $loc) {
            echo '<li><input type="radio" name="fopLoc" ref="loc" value="'.$loc['location'].'" class="mr-2">'.$loc['location'].'</li>';
        }
    }
}


echo '</ul>
    </div>
  </td></tr>
<tr class="Divider"><th></th></tr>
<tr><th class="TitleLeft p-2"><i id="cmdToggleSchedule" class="fa-solid fa-caret-down fa-lg mr-1" onclick="toggleSchedule()"></i>'.get_text('CompleteSchedule', 'Tournament').'<i id="cmdDetachSchedule" class="fa-solid fa-lg fa-square-arrow-up-right ml-3" onclick="detachSchedule()"></i></th></tr><tr id="viewSchedule" style="display: none;"><td>';
$Schedule=new Scheduler();
$Schedule->ROOT_DIR=$CFG->ROOT_DIR;
echo '<div id="dayLinks"></div>';
echo '<div id="TrueScheduler">'.$Schedule->getScheduleHTML('SET').'</div>';
echo '</div></></tr>';

if($aclLevel == AclReadWrite) {
    echo '<tr class="Divider"><th></th></tr><tr><th class="TitleLeft p-2"><i id="cmdToggleViewSchedule" class="fa-solid fa-caret-right fa-lg mr-1" onclick="toggleViewSchedule()"></i>' . get_text('Scheduler') . '</th></tr><tr id="viewEditSchedule" style="display: none;"><td>';
    //Free Text
    echo '<table class="Tabella">
        <tr><th class="Title w-20">'.get_text('MatchNo').'</th><th class="Left p-2 w-80"><input type="button" id="btnSetMatchNo" class="ml-5" value="'.get_text('MatchNoBySchedule', 'Tournament').'" onclick="calculateMatchNo()"></th></tr>
        </table>';

    echo '<table id="ScheduleTexts">'.getScheduleTexts().'</table>';
    echo '<table>';
    // Get all the qualification items with date & time
    if($_SESSION['TourType']==48) {
        // Run ARchery uses totally different tables!
        $q = safe_r_sql("select RarPhase,
			RarPool,
			min(RarGroup) as RarGroup,
			if(RarStartlist=0, '', date(RarStartlist)) RarDay,
			if(RarStartlist=0, '', date_format(min(RarStartlist), '%H:%i')) RarStart,
			RarDuration,
			RarWarmup,
			RarWarmupDuration,
			RarNotes,
			RarShift,
			RarCallTime,
			min(RarStartList) as RarStartList,
			EvElimType,
			group_concat(distinct RarEvent separator ', ') as RarEvents,
			group_concat(distinct concat_ws('-', RarTeam, RarEvent) separator ',') as RarEventCodes
		from RunArcheryRank
		inner join Events on EvTournament=RarTournament and EvTeamEvent=RarTeam and EvCode=RarEvent
		where RarTournament={$_SESSION['TourId']}
		group by if(EvElimType=0 or RarPhase>0, RarStartlist, EvCode), RarPhase, RarPool, if(EvElimType=0 or RarPhase>0, '', RarGroup)
		order by RarStartlist");

        echo '<tr>
			<th class=Title" colspan="9">' . get_text('RA-Session', 'Tournament') . '</th>
			<th class="Title w-30" rowspan="2">' . get_text('ScheduleNotes', 'Tournament') . '</th>
		</tr>
		<tr>
			<th class="w-5">' . get_text('Session') . '</th>
			<th class="w-5">' . get_text('Distance', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Date', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Time', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Length', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Delayed', 'Tournament') . '</th>
			<th class="w-5">' . get_text('CallTime', 'RunArchery') . '</th>
			<th class="w-5">' . get_text('WarmUp', 'Tournament') . '</th>
			<th class="w-5">' . get_text('WarmUpMins', 'Tournament') . '</th>
		</tr>';
        while ($r = safe_fetch($q)) {
            $Session='';
            if($r->RarPhase==1) {
                $Session=get_text('Final'.$r->RarPool, 'RunArchery');
            } elseif($r->RarPhase==2) {
                $Session=get_text('PoolName', 'Tournament', $r->RarPool);
            } elseif($r->RarGroup) {
                $Session=get_text('GroupNum','RoundRobin', $r->RarGroup);
            } else {
                $Session=get_text('AllEntries','Tournament');
            }
            echo '<tr>
				<th>' . $r->RarEvents . '</td>
				<th>' . $Session . '</td>
				<td>'.$r->RarDay.'</td>
				<td>'.$r->RarStart.'</td>
				<td><input max="999" min="0" type="number" name="Fld[RA][Duration][' . $r->RarEventCodes . '][' . $r->RarStartList . ']" value="' . $r->RarDuration . '" onchange="DiUpdate(this)"></td>
				<td><input max="999" min="-1" type="number" name="Fld[RA][Shift][' . $r->RarEventCodes . '][' . $r->RarStartList . ']" value="' . $r->RarShift . '" onchange="DiUpdate(this)"></td>
				<td><input name="Fld[RA][Calltime][' . $r->RarEventCodes . '][' . $r->RarStartList . ']" value="' . ($r->RarCallTime=='00:00:00' ? '' : substr($r->RarCallTime, 0, 5)) . '" onchange="DiUpdate(this)"></td>
				<td><input name="Fld[RA][Warmtime][' . $r->RarEventCodes . '][' . $r->RarStartList . ']" value="' . ($r->RarWarmup=='00:00:00' ? '' : substr($r->RarWarmup, 0, 5)) . '" onchange="DiUpdate(this)"></td>
				<td><input name="Fld[RA][WarmtimeDuration][' . $r->RarEventCodes . '][' . $r->RarStartList . ']" value="' . ($r->RarWarmupDuration=='0' ? '' : $r->RarWarmupDuration) . '" onchange="DiUpdate(this)"></td>
				<td><input style="width:100%" type="text" name="Fld[RA][Options][' . $r->RarEventCodes . '][' . $r->RarStartList . ']" value="' . $r->RarNotes . '" onchange="DiUpdate(this)"></td>
				</tr>';
        }
    } else  {
        $q = safe_r_sql("select DiSession,
			DiDistance,
			if(DiDay=0, '', DiDay) DiDay,
			if(DiStart=0, '', date_format(DiStart, '%H:%i')) DiStart,
			DiDuration,
			DiTargets,
			if(DiWarmStart=0, '', date_format(DiWarmStart, '%H:%i')) DiWarmStart,
			DiWarmDuration,
			DiOptions,
			if(SesName!='', SesName, DiSession) Session,
			DiShift
		from DistanceInformation
		inner join Session on SesTournament=DiTournament and SesOrder=DiSession and SesType=DiType and SesType='Q'
		where DiTournament={$_SESSION['TourId']}
		order by DiSession, DiDistance");
        echo '<tr>
			<th class="Title w-50" colspan="6">' . get_text('Q-Session', 'Tournament') . '</th>
			<th class="Title w-30" colspan="3">' . get_text('WarmUp', 'Tournament') . '</th>
			<th class="Title">' . get_text('Targets', 'Tournament') . '</th>
		</tr>
		<tr>
			<th class="w-20">' . get_text('Session') . '</th>
			<th class="w-5">' . get_text('Distance', 'Tournament') . '</th>
			<th class="w-10"><img src="' . $CFG->ROOT_DIR . 'Common/Images/Tip.png" title="' . get_Text('TipDate', 'Tournament') . '" align="right">' . get_text('Date', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Time', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Length', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Delayed', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Time', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Length', 'Tournament') . '</th>
			<th class="w-20">' . get_text('ScheduleNotes', 'Tournament') . '</th>
			<th class="w-20">#1-#N@Dist<br>[@Cat[@Face]]</th>
		</tr>';
        while ($r = safe_fetch($q)) {
            echo '<tr>
			<th>' . $r->Session . '</td>
			<th>' . $r->DiDistance . '</td>
			<td><input type="date" name="Fld[Q][Day][' . $r->DiSession . '][' . $r->DiDistance . ']" value="' . $r->DiDay . '" onblur="DiUpdate(this)"></td>
			<td><input type="time" name="Fld[Q][Start][' . $r->DiSession . '][' . $r->DiDistance . ']" value="' . $r->DiStart . '" onblur="DiUpdate(this)"></td>
			<td><input max="999" min="0" type="number" name="Fld[Q][Duration][' . $r->DiSession . '][' . $r->DiDistance . ']" value="' . $r->DiDuration . '" onchange="DiUpdate(this)"></td>
			<td><input max="999" min="-1" type="number" name="Fld[Q][Shift][' . $r->DiSession . '][' . $r->DiDistance . ']" value="' . $r->DiShift . '" onchange="DiUpdate(this)"></td>
			<td><input size="5" type="text" name="Fld[Q][WarmTime][' . $r->DiSession . '][' . $r->DiDistance . ']" value="' . $r->DiWarmStart . '" onchange="DiUpdate(this)"></td>
			<td><input max="999" min="0" type="number" name="Fld[Q][WarmDuration][' . $r->DiSession . '][' . $r->DiDistance . ']" value="' . $r->DiWarmDuration . '" onchange="DiUpdate(this)"></td>
			<td><input class="w-100" type="text" name="Fld[Q][Options][' . $r->DiSession . '][' . $r->DiDistance . ']" value="' . $r->DiOptions . '" onchange="DiUpdate(this)"></td>
			<td><input class="w-100" type="text" name="Fld[Q][Targets][' . $r->DiSession . '][' . $r->DiDistance . ']" value="' . $r->DiTargets . '" onchange="DiUpdate(this)"></td>
			</tr>';
        }
    }
// Get all the Elimination items with date & time
    $q = safe_r_sql("select SesOrder,
		ElElimPhase,
		if(DiDay=0, '', DiDay) DiDay,
		if(DiStart=0, '', date_format(DiStart, '%H:%i')) DiStart,
		DiDuration,
		if(DiWarmStart=0, '', date_format(DiWarmStart, '%H:%i')) DiWarmStart,
		DiWarmDuration,
		DiOptions,
		if(SesName!='', SesName, SesOrder) Session, Events, DiShift
        from Session
        inner join (select distinct ElSession, ElTournament, ElElimPhase, group_concat(distinct ElEventCode order by ElEventCode separator ', ') Events from Eliminations where ElTournament={$_SESSION['TourId']} group by ElTournament, ElSession, ElElimPhase) Phase on ElSession=SesOrder and ElTournament=SesTournament
        left join DistanceInformation on SesTournament=DiTournament and SesOrder=DiSession and ElElimPhase=DiDistance and DiType='E'
        where SesTournament={$_SESSION['TourId']}
        and SesType='E'
        order by SesOrder, ElElimPhase");
    if (safe_num_rows($q)) {
        echo '<tr class="Divider"><td colspan="10"></td></tr>
		<tr>
			<th class="Title" colspan="6">' . get_text('E-Session', 'Tournament') . '</th>
			<th class="Title w-10" colspan="4"' . get_text('WarmUp', 'Tournament') . '</th>
		</tr>
		<tr>
			<th class="w-20">' . get_text('Session') . '</th>
			<th class="w-5">' . get_text('Eliminations') . '</th>
			<th class="w-10">' . get_text('Date', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Time', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Length', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Delayed', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Time', 'Tournament') . '</th>
			<th class="w-5">' . get_text('Length', 'Tournament') . '</th>
			<th class="w-40" colspan="2">' . get_text('ScheduleNotes', 'Tournament') . '</th>
		</tr>';
        while ($r = safe_fetch($q)) {
            echo '<tr>
			<th>' . $r->Session . '<br/>' . $r->Events . '</td>
			<th>' . get_text('Eliminations_' . ($r->ElElimPhase + 1)) . '</td>
			<td><input type="date" name="Fld[E][Day][' . $r->SesOrder . '][' . $r->ElElimPhase . ']" value="' . $r->DiDay . '" onblur="DiUpdate(this)"></td>
			<td><input type="time" name="Fld[E][Start][' . $r->SesOrder . '][' . $r->ElElimPhase . ']" value="' . $r->DiStart . '" onblur="DiUpdate(this)"></td>
			<td><input max="999" min="0" type="number" name="Fld[E][Duration][' . $r->SesOrder . '][' . $r->ElElimPhase . ']" value="' . $r->DiDuration . '" onchange="DiUpdate(this)"></td>
			<td><input max="999" min="-1" type="number" name="Fld[E][Shift][' . $r->SesOrder . '][' . $r->ElElimPhase . ']" value="' . $r->DiShift . '" onchange="DiUpdate(this)"></td>
			<td><input size="5" type="text" name="Fld[E][WarmTime][' . $r->SesOrder . '][' . $r->ElElimPhase . ']" value="' . $r->DiWarmStart . '" onchange="DiUpdate(this)"></td>
			<td><input max="999" min="0" type="number" name="Fld[E][WarmDuration][' . $r->SesOrder . '][' . $r->ElElimPhase . ']" value="' . $r->DiWarmDuration . '" onchange="DiUpdate(this)"></td>
			<td colspan="2"><input class="w-100" type="text" name="Fld[E][Options][' . $r->SesOrder . '][' . $r->ElElimPhase . ']" value="' . $r->DiOptions . '" onchange="DiUpdate(this)"></td>
			</tr>';
        }
    }
    // Get all the Matches items with date & time
    $SQL = "select
		FsTeamEvent, GrPhase, FsScheduledDate, FsScheduledTime,
		if(FsScheduledDate=0, '', FsScheduledDate) ScheduledDate,
		if(FsScheduledTime=0, '', date_format(FsScheduledTime, '%H:%i')) ScheduledTime,
		FsScheduledLen,
		EvFinalFirstPhase,
		FwTime,
		FwDuration,
		FwOptions,
		group_concat(distinct FsEvent order by FsEvent separator ', ') Events, FsShift
	from FinSchedule
	inner join Events on FsEvent=EvCode and FsTeamEvent=EvTeamEvent and FsTournament=EvTournament
	inner join Grids on FsMatchNo=GrMatchNo
	left join (
		select
		FwTeamEvent, FwDay, FwMatchTime, FwEvent, FwTournament,
		group_concat( date_format(FwTime, '%H:%i') order by FwTime separator '|') FwTime,
		group_concat( FwDuration order by FwTime separator '|') FwDuration,
		group_concat( FwOptions order by FwTime separator '|') FwOptions
		from FinWarmup
		where FwTournament={$_SESSION['TourId']}
		group by FwTeamEvent, FwDay, FwMatchTime, FwEvent
		) FinWarmup on FsEvent=FwEvent and FsTeamEvent=FwTeamEvent and FsTournament=FwTournament and FsScheduledDate=FwDay and FsScheduledTime=FwMatchTime
	where FsTournament={$_SESSION['TourId']}
	group by FsTeamEvent, GrPhase, FsScheduledDate, FsScheduledTime
	order by FsScheduledDate, FsScheduledTime, FwTime, FsTeamEvent, GrPhase desc";
    $q = safe_r_sql($SQL);
    if (safe_num_rows($q)) {
        //
        $OldHeader = '';
        $TeamEvent = 'I';
        while ($r = safe_fetch($q)) {
            if ($OldHeader != $r->FsTeamEvent) {
                $TeamEvent = ($r->FsTeamEvent ? 'T' : 'I');
                echo '<tr class="Divider"><td colspan="10"></td></tr>
				<tr>
					<th class="Title" colspan="6">' . get_text(($r->FsTeamEvent ? 'T' : 'I') . '-Session', 'Tournament') . '</th>
					<th class="Title w-10" colspan="4">' . get_text('WarmUp', 'Tournament') . '</th>
				</tr>
				<tr>
					<th class="w-20">' . get_text('Events', 'Tournament') . '</th>
					<th class="w-5">' . get_text('Phase') . '</th>
					<th class="w-10">' . get_text('Date', 'Tournament') . '</th>
					<th class="w-5">' . get_text('Time', 'Tournament') . '</th>
					<th class="w-5">' . get_text('Length', 'Tournament') . '</th>
					<th class="w-5">' . get_text('Delayed', 'Tournament') . '</th>
					<th class="w-5">' . get_text('Time', 'Tournament') . '</th>
					<th class="w-5">' . get_text('Length', 'Tournament') . '</th>
					<th class="w-40" colspan="2">' . get_text('ScheduleNotes', 'Tournament') . '</th>
				</tr>';
                $OldHeader = $r->FsTeamEvent;
            }
            echo '<tr>
			<th>' . $r->Events . '</td>
			<th>' . get_text(namePhase($r->EvFinalFirstPhase, $r->GrPhase) . '_Phase') . '</td>
			<td><input type="date" name="Fld[' . $TeamEvent . '][Day][' . $r->GrPhase . '][' . $r->FsScheduledDate . '][' . $r->FsScheduledTime . ']" value="' . $r->ScheduledDate . '" onblur="DiUpdate(this)"></td>
			<td><input type="time" name="Fld[' . $TeamEvent . '][Start][' . $r->GrPhase . '][' . $r->FsScheduledDate . '][' . $r->FsScheduledTime . ']" value="' . $r->ScheduledTime . '" onblur="DiUpdate(this)"></td>
			<td><input max="999" min="0" type="number" name="Fld[' . $TeamEvent . '][Duration][' . $r->GrPhase . '][' . $r->FsScheduledDate . '][' . $r->FsScheduledTime . ']" value="' . $r->FsScheduledLen . '" onchange="DiUpdate(this)"></td>
			<td><input max="999" min="-1" type="number" name="Fld[' . $TeamEvent . '][Shift][' . $r->GrPhase . '][' . $r->FsScheduledDate . '][' . $r->FsScheduledTime . ']" value="' . $r->FsShift . '" onchange="DiUpdate(this)"></td>
			<td class="WTime">';
            $FwTimes = explode('|', ($r->FwTime ?? ''));
            foreach ($FwTimes as $k => $FwTime) {
                echo '<div class="item-'.$k.'"><input size="5" type="text" name="Fld[' . $TeamEvent . '][WarmTime][' . $r->GrPhase . '][' . $r->FsScheduledDate . '][' . $r->FsScheduledTime . '][' . $FwTime . ']" value="' . $FwTime . '" onchange="DiUpdate(this)"></div>';
            }
            echo '</td>
			<td class="WDuration">';
            foreach (explode('|', ($r->FwDuration ?? '')) as $k => $FwDuration) {
                echo '<div class="item-'.$k.'"><input max="999" min="0" type="number" name="Fld[' . $TeamEvent . '][WarmDuration][' . $r->GrPhase . '][' . $r->FsScheduledDate . '][' . $r->FsScheduledTime . '][' . $FwTimes[$k] . ']" value="' . $FwDuration . '" onchange="DiUpdate(this)"></div>';
            }
            echo '</td>
			<td class="WOptions" colspan="2">';
            foreach (explode('|', ($r->FwOptions ?? '')) as $k => $FwOption) {
                echo '<div class="item-'.$k.'">';
                echo '<input type="text" name="Fld[' . $TeamEvent . '][Options][' . $r->GrPhase . '][' . $r->FsScheduledDate . '][' . $r->FsScheduledTime . '][' . $FwTimes[$k] . ']" value="' . $FwOption . '" onchange="DiUpdate(this)">';
                if ($k) {
                    echo '<i class="fa fa-trash-alt text-danger ml-1" ref="WarmDelete" id="' . $TeamEvent . '|' . $r->GrPhase . '|' . $r->FsScheduledDate . '|' . $r->FsScheduledTime . '|' . $FwTimes[$k] . '" onclick="DiDelete(this)"></i>';
                } else {
                    echo '<i class="fa fa-plus-square text-success ml-1" onclick="DiAddSubRow(this)"></i>';
                }
                echo '</div>';
            }
            echo '</td>
			</tr>';
        }
    }
    echo '</td></tr>';
}
echo '</table>';
include('Common/Templates/tail.php');

