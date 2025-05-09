<?php
define('debug',false);	// settare a true per l'output di debug

require_once(dirname(dirname(__FILE__)) . '/config.php');
CheckTourSession(true);

checkFullACL(array(AclIndividuals,AclTeams), '', AclReadWrite);

require_once('Common/Lib/CommonLib.php');
require_once('Common/Fun_Sessions.inc.php');
// require_once('Common/Lib/ArrTargets.inc.php');
// require_once('Common/Fun_FormatText.inc.php');
// require_once('Common/Fun_Various.inc.php');

$JS_SCRIPT=array(
	'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Final/WriteArrows.js"></script>',
	'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Common/js/Fun_JS.inc.js"></script>',
	phpVars2js(array(
		'CmdPostUpdate'=>get_text('CmdPostUpdate'),
		'PostUpdating'=>get_text('PostUpdating'),
		'PostUpdateEnd'=>get_text('PostUpdateEnd'),
		'RootDir'=>$CFG->ROOT_DIR,
		'MsgAreYouSure' => get_text('MsgAreYouSure'),
	)),
	'<style>select {max-width:25em; overflow:hidden;}
		.Bye td {background-color:#888; color:yellow;}
		.divider {background-color:#ddd;}</style>',
);

$PAGE_TITLE=get_text('Finals', 'Tournament');

$Events=array();
$Phases=array();
$q=safe_r_sql("select e1.EvCode, e1.EvFinalFirstPhase, e1.EvTeamEvent, coalesce(e2.EvNumQualified/2,0) as EvSecondaryStartPhase
    from Events e1
    left join Events e2 on e2.EvTournament=e1.EvTournament and e2.EvTeamEvent=e1.EvTeamEvent and e2.EvCodeParent=e1.EvCode and e2.EvCodeParentWinnerBranch=1
    where e1.EvTournament={$_SESSION['TourId']} and e1.EvElimType!=5
    order by e1.EvTeamEvent, e1.EvProgr");
while($r=safe_fetch($q)) {
	$Events[$r->EvTeamEvent][$r->EvCode]='<input type="checkbox" class="EventCheck" name="'.$r->EvTeamEvent.'" value="'.$r->EvCode.'">'.$r->EvCode;
	if($r->EvFinalFirstPhase and empty($Phases[$r->EvTeamEvent][$r->EvFinalFirstPhase])) {
		for($n=valueFirstPhase($r->EvFinalFirstPhase); $n>=$r->EvSecondaryStartPhase; $n/=2) {
			$Phases[$r->EvTeamEvent][$n]='<input type="checkbox" class="PhaseCheck" name="'.$r->EvTeamEvent.'" value="'.valueFirstPhase($n).'">'.get_text(namePhase($r->EvFinalFirstPhase,$n).'_Phase');
			if($n==0) break; // escape from this zoo :D
			if($n==1) $n=0; // makes the gold medal match ;)
		}
	}
}

if(!empty($Phases[0])) {
    krsort($Phases[0], SORT_NUMERIC );
}
if(!empty($Phases[1])) {
    krsort($Phases[1], SORT_NUMERIC );
}

$IncludeJquery=1;
include('Common/Templates/head.php');

?>

<table class="Tabella Speaker">
<tr onClick="showOptions();"><th class=Title colspan="6"><?php print get_text('Finals', 'Tournament');?></th></tr>
<TR><Th class="SubTitle" colspan="6"><?php print get_text('SingleArrow','Tournament');?></Th></TR>
<tr class="Divider"><td colspan="6"></td></tr>
<tbody id="options">
<tr>
<th class="SubTitle" width="25%"><?php print get_text('Schedule', 'Tournament');?></th>
<th class="SubTitle" width="25%"><?php print get_text('Events', 'Tournament');?></th>
<th class="SubTitle" width="25%"><?php print get_text('Phase');?></th>
<th class="SubTitle" width="8%"><?php print get_text('End (volee)');?></th>
<th class="SubTitle" width="8%"><?php print get_text('ArrowsPerEndForInput', 'Tournament');?></th>
<th class="SubTitle" width="9%">&nbsp;</th>
</tr>
<tr>
	<td class="Center">
		<select name="x_Schedule" id="x_Schedule"></select>
	</td>
	<td class="Center"><?php echo (empty($Events[0]) ? '' : get_text('Individual').': '.implode(str_repeat('&nbsp;',2), $Events[0])); ?></td>
	<td class="Center" nowrap="nowrap"><?php echo (empty($Phases[0]) ? '' : implode(str_repeat('&nbsp;',2), $Phases[0])); ?></td>
	<td class="Center" rowspan="2"><input type="text" name="x_Volee" id="x_Volee" size="3" maxlength="3" ></td>
	<td class="Center" rowspan="2"><input type="text" name="x_Arrows" id="x_Arrows" size="3" maxlength="3" ></td>
	<td class="Center" rowspan="2"><input type="button" value="<?php  print get_text('CmdOk');?>" onClick="getArrows()"></td>
</tr>

<tr>
	<td class="Center"><?php
		if($_SESSION["MenuHHT"]) {
			echo '<input type="checkbox" id="useHHT" checked="checked" onClick="GetSchedule();">'.get_text('FollowHHT','Tournament').str_repeat('&nbsp;',2);
		} else {
			if($IskSequence=getModuleParameter('ISK-NG', 'Sequence')) {
				echo '<input type="button" id="currentSession" onClick="GetSchedule(true);" value="'.get_text('GoToRunning','Tournament').'">'.str_repeat('&nbsp;',2);
			}
		}
		?>
		<input type="checkbox" id="onlyToday" checked onClick="GetSchedule();"><?php print get_text('OnlyToday','Tournament');?></td>
	<td class="Center" nowrap="nowrap"><?php echo (empty($Events[1]) ? '' : get_text('Team').': '.implode(str_repeat('&nbsp;',2), $Events[1])); ?></td>
	<td class="Center" nowrap="nowrap"><?php echo (empty($Phases[1]) ? '' : implode(str_repeat('&nbsp;',2), $Phases[1])); ?></td>
</tr>

<tr>
</tr>
</tbody>
</table>

<div id="idOutput"></div>
<?php
	include('Common/Templates/tail.php');
?>
