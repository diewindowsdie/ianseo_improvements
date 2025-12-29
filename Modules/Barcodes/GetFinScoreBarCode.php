<?php
define('IN_PHP', true);

require_once(dirname(__FILE__, 3) . '/config.php');
require_once('Common/Fun_Number.inc.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Fun_Sessions.inc.php');
require_once('Final/Fun_Final.local.inc.php');
require_once('Common/Lib/ArrTargets.inc.php');

CheckTourSession(true);
checkFullACL(array(AclIndividuals,AclTeams),'', AclReadWrite);
$Match='';

// Check the correct separator (as barcode reader may interpret «-» as a «'» !)
//
if(empty($_SESSION['BarCodeSeparator'])) {
	require_once('./GetBarCodeSeparator.php');
	die();
}

$ShowMiss=(!empty($_GET['ShowMiss']));
$T=0;
$Turno='';

if($_GET) {
	if(!empty($_GET['BARCODESEPARATOR'])) {
		unset($_SESSION['BarCodeSeparator']);
		CD_redirect($_SERVER['PHP_SELF']);
	}

	if(!empty($_GET['T'])) $Turno='&T='.($T=$_GET['T']);

	// sets the autoedit feature
	if(!empty($_GET['AutoEdit']) and empty($_GET['return']) and empty($_GET['C'])) $_GET['C']='EDIT';
	unset($_GET['return']);

	if(!empty($_GET['B'])) {
		// get the match
		$Match=getScore($_GET['B']);
		if(!empty($Match->FsDate1) and !empty($Match->FsTime1)) $_GET['T']=$Match->FsDate1.'|'.$Match->FsTime1;

		// if we have a "C" input (beware of autoedit!) then do the action
		if(!empty($_GET['C'])) {
			$C=$_GET['C'];
			unset($_GET['C']);
			if($Match and (!IsBlocked(BIT_BLOCK_IND) or !IsBlocked(BIT_BLOCK_TEAM))) {
				switch(strtoupper($C)) {
					case 'EDIT':
						$GoBack=$_SERVER['SCRIPT_NAME'].go_get().'&return=1';

						// edit the scorecard
						$_REQUEST['Team']=$Match->teamEvent;
						$_REQUEST['d_Event']=$Match->event;
						$_REQUEST['d_Match']=$Match->match1;
						//require_once('Final/WriteScoreCard.php');
						require_once('Final/Spotting.php');
						die();
						break;
					case strtoupper($_GET['B']):
						ConfirmMatch($Match);
						unset($_GET['B']);
						cd_redirect(basename(__FILE__).go_get());
						break;
					default:
						// reads another barcode
						$_GET['B']=$C;
						cd_redirect(basename(__FILE__).go_get());
				}
			} elseif(getScore($C)) {
				// reads another barcode
				$_GET['B']=$C;
				cd_redirect(basename(__FILE__).go_get());
			}
		}
	}
}

$JS_SCRIPT=array(
        '<script type="text/javascript" src="./barcode.js"></script>',
        '<link href="./barcode.css" media="screen" rel="stylesheet" type="text/css">'
);

$IncludeFA=true;
$IncludeJquery=true;
include('Common/Templates/head.php');

echo '<div id="bcodeContainer" class="bcodeContainer"><div class="bcodeOp"><form id="Frm" method="get" action="">
<table class="Tabella2 w-100">
<tr><th class="Title" colspan="4">'.get_text('CheckScorecards','Tournament').'</th></tr>
<tr class="h-0">
	<th colspan="3" class="w-60">' . get_text('BarcodeSeparator','BackNumbers') . ': <span style="font-size:150%">' . $_SESSION['BarCodeSeparator'] . '</span>' . '</th>
	<th colspan="1" class="w-10"><a href="' . $_SERVER["PHP_SELF"]. '?BARCODESEPARATOR=1">' . get_text('ResetBarcodeSeparator','BackNumbers') . '</a></th>
</tr>
<tr>
	<th class="w-10">'.get_text('AutoEdits','Tournament').'</th>
	<th class="w-10">'.get_text('ShowMissing','Tournament').'</th>
	<th class="w-30">'.get_text('Barcode','BackNumbers').'</th>
	<th class="w-50">'.get_text('Session').'</th>
</tr>
<tr class="h-0">
    <td class="Center"><input type="checkbox" onclick="refreshForm()" name="AutoEdit"'.(!empty($_GET['AutoEdit']) ? ' checked="checked"' : '').'></td>
    <td class="Center"><input type="checkbox" onclick="refreshForm()" name="ShowMiss"'.((empty($_GET) or !empty($_GET['ShowMiss'])) ? ' checked="checked"' : '').'></td><td class="Center">';
    if(!empty($_GET['B'])) {
        echo '<input type="hidden" name="B" value="'.$_GET['B'].'">';
        echo '<input class="w-95" type="text" name="C" id="bib" tabindex="1">';
    } else {
        echo '<input class="w-95" type="text" name="B" id="bib" tabindex="1">';
    }
    echo '</td><td class="Center"><select id="Session" name="T"  onchange="refreshForm(true)"><option value="0"></option>';

    $q=safe_r_sql("Select distinct `FSTeamEvent`, CONCAT(date_format(FSScheduledDate, '%e %b '),date_format(FSScheduledTime, '%H:%i'), ' ', group_concat(distinct concat('--', `EvFinalFirstPhase`,'|',`GrPhase`, '-- ', `FsEvent`) separator ' + ')) AS `Description`, `EvElimType`, `FSScheduledDate`, `FSScheduledTime` 
        from `FinSchedule` 
        inner join `Grids` on `GrMatchNo`=`FsMatchNo` 
        inner join `Events` on `EvTournament`=`FSTournament` and `EvCode`=`FSEvent` and `EvTeamEvent`=`FSTeamEvent`
        where `FsTournament`={$_SESSION['TourId']} and `FSScheduledDate`>0 
        group by `FSScheduledDate`,`FSScheduledTime` 
        order by `FSScheduledDate`,`FSScheduledTime`");
    while($r=safe_fetch($q)) {
        preg_match_all('/--([0-9]+\|[0-9]+)--/', $r->Description, $m);
        $n=array_unique($m[1]);
        foreach($n as $v) {
            list($first,$current) = explode('|',$v);
            $tmp=get_text(namePhase($first, $current).'_Phase');
            if($r->EvElimType==3 and isset($PoolMatches[$v])) {
                $tmp=$PoolMatches[$v];
            } elseif($r->EvElimType==4 and isset($PoolMatchesWA[$v])) {
                $tmp=$PoolMatchesWA[$v];
            }
            $r->Description=str_replace("--{$v}--", $tmp, $r->Description);
        }
        echo '<option value="'.$r->FSScheduledDate.'|'.$r->FSScheduledTime.'" '.(!empty($_GET['T']) && $_GET['T']==$r->FSScheduledDate.'|'.$r->FSScheduledTime ? ' selected="selected"' : '').'>'.get_text(($r->FSTeamEvent ? 'FinTeam' : 'FinInd'), 'HTT').': '.$r->Description.'</option>';
    }
    echo '</select></td></tr><tr>
		<td class="Center" colspan="2"><input type="submit" value="'.get_text('CmdGo','Tournament').'" id="Vai" onClick="refreshForm();"></td>
		<td class="Center" colspan="2"><input type="button" value="'.get_text('BarcodeMissing','Tournament').'" onClick="window.open(\'./GetScoreBarCodeMissing.php?S=F&T=\'+document.getElementById(\'Session\').value);"></td>
	</tr>';

if($Match) {
    // check who is winner...
    $Win1='';
    $Win2='';
    $Score1=($Match->matchMode ? $Match->setScore1:$Match->score1);
    $Score2=($Match->matchMode ? $Match->setScore2:$Match->score2);
    $XChar=($Match->checkGolds ? $Match->goldChars : ($Match->checkXNines? $Match->xNineChars : null));
    $TB1=ValutaArrowStringSO($Match->tiebreak1, $XChar, $XChar ? 'A' : null);
    $TB2=ValutaArrowStringSO($Match->tiebreak2, $XChar, $XChar ? 'A' : null);
    $Closest1=($Match->tiebreak1!=strtoupper($Match->tiebreak1) or $Match->closest1);
    $Closest2=($Match->tiebreak2!=strtoupper($Match->tiebreak2) or $Match->closest2);

    if($Match->win1) {
        $Win1=' matchWinner';
    } elseif($Match->win2) {
        $Win2=' matchWinner';
    } else {
        $Win1=' matchTie';
        $Win2=' matchTie';
    }

    echo '<tr><td colspan="4"><br><table class="Tabella TabellaScore">';
    echo '<tr><th class="Title" colspan="5">'.get_text('Target'). ' ' . ltrim($Match->target1, '0') . ($Match->target1!=$Match->target2 ? ' - ' . ltrim($Match->target2,'0') : '') . '</th></tr>';
    echo '<tr>';

    // Opponent 1
    echo '<td colspan="2" class="'.$Win1.'">';
    echo '<div class="matchHighlight"><div>'.$Match->name1.'</div></div>';
    echo '<div class="matchHighlight"><div>'.get_text('Score', 'Tournament').'</div><div class="Score"> '.$Score1.'</div></div>';
    if($Match->matchMode) {
        echo '<div class="matchHighlight"><div>'.get_text('SetPoints', 'Tournament').'</div>';
        echo '<div class="Score">'.str_replace("|",",&nbsp;",$Match->setPoints1).'</div>';
        echo '</div>';
    }
    if($Match->checkGolds) {
        echo '<div class="matchHighlight"><div>'.$Match->goldLabel.'</div><div class="Score">'.$Match->golds1.'</div></div>';
    }
    if($Match->checkXNines) {
        echo '<div class="matchHighlight"><div>'.$Match->xNineLabel.'</div><div class="Score">'.$Match->xnines1.'</div></div>';
    }
    if($Match->tiebreak1 or $Match->tiebreak2) {
        echo '<div class="matchHighlight"><div>'.get_text('ShotOffShort', 'Tournament').'</div><div class="Score">'.implode(',', DecodeFromString(trim($Match->tiebreak1), false, true)).'</div></div>';
    }
    if($Closest1 or $Closest2) {
        echo '<div class="matchHighlight"><div>'.get_text('ClosestShort', 'Tournament').'</div><div class="Score">'.($Closest1 ? '<i class="fa fa-check-circle txtGreen"></i>' :'&nbsp;').'</div></div>';
    }

    echo '</td>';

    echo '<td>&nbsp;</td>';

    // Opponent 2
    echo '<td colspan="2" class="'.$Win2.'">';
    echo '<div class="matchHighlight"><div>'.$Match->name2.'</div></div>';
    echo '<div class="matchHighlight"><div>'.get_text('Score', 'Tournament').'</div><div class="Score"> '.$Score2.'</div></div>';
    if($Match->matchMode) {
        echo '<div class="matchHighlight"><div>'.get_text('SetPoints', 'Tournament').'</div>';
        echo '<div class="Score">'.str_replace("|",",&nbsp;",$Match->setPoints2).'</div>';
        echo '</div>';
    }
    if($Match->checkGolds) {
        echo '<div class="matchHighlight"><div>'.$Match->goldLabel.'</div><div class="Score">'.$Match->golds2.'</div></div>';
    }
    if($Match->checkXNines) {
        echo '<div class="matchHighlight"><div>'.$Match->xNineLabel.'</div><div class="Score">'.$Match->xnines2.'</div></div>';
    }
    if($Match->tiebreak1 or $Match->tiebreak2) {
        echo '<div class="matchHighlight"><div>'.get_text('ShotOffShort', 'Tournament').'</div><div class="Score">'.implode(',', DecodeFromString(trim($Match->tiebreak2), false, true)).'</div></div>';
    }
    if($Closest1 or $Closest2) {
        echo '<div class="matchHighlight"><div>'.get_text('ClosestShort', 'Tournament').'</div><div class="Score">'.($Closest2 ? '<i class="fa fa-check-circle txtGreen"></i>' :'&nbsp;').'</div></div>';
    }
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="2" class="Command Bold"><a href="'.go_get(array('C'=>$_REQUEST['B'])).'">CONFIRM</a></td>';
    echo '<td>&nbsp;</td>';
    echo '<td colspan="2" class="Command"><a href="'.go_get(array('C'=> 'EDIT')).'">Edit arrows</a>';
    echo '</td>';
    echo '</tr>';
    echo '</table></td></tr>';
}

echo '<tr class="divider"><td colspan="4"></td></tr>
    <tr><th colspan="4"><img class="p-2" src="beiter.png" alt="Beiter Logo" /><br>' . get_text('Credits-BeiterCredits', 'Install') . '</th></tr>';
echo '</table></div>
    <div id="bcodeMissingContainer">';
if($ShowMiss and !empty($_GET['T'])) {
    list($FsDate, $FsTime)=explode('|', $_GET['T']);
    $cnt = 0;
    $tmpRow = '';
    $Q=GetFinMatches_sql(" and fs1.FSScheduledDate='$FsDate' and fs1.FSScheduledTime='$FsTime' and f1.FinConfirmed=0", 0, ' CAST(target1 as SIGNED)');
    while($r=safe_fetch($Q)) {
        if(!$r->familyName1 or !$r->familyName2) continue;
        $lnk=' onclick="location.href=\''.go_get('B', $r->match1.$_SESSION['BarCodeSeparator'].$r->teamEvent.$_SESSION['BarCodeSeparator'].$r->event).'\'"';
        if($r->win1 or $r->win2) {
            $lnk.=' style="font-weight:bold;"';
        }
        $tmpRow .= '<tr'.$lnk.'><td>'.ltrim($r->target1,'0').($r->target1!=$r->target2 ? '-'.ltrim($r->target2,'0') : '').'</td><td nowrap="nowrap">'.$r->familyName1.'</td><td nowrap="nowrap">'.$r->familyName2.'</td></tr>';
        $cnt++;
    }
    $Q=GetFinMatches_sql(" and fs1.FSScheduledDate='$FsDate' and fs1.FSScheduledTime='$FsTime' and tf1.TfConfirmed=0", 1, ' target1');
    while($r=safe_fetch($Q)) {
        if(!$r->familyName1 or !$r->familyName2) continue;
        $lnk=' onclick="location.href=\''.go_get('B',$r->match1.$_SESSION['BarCodeSeparator'].$r->teamEvent.$_SESSION['BarCodeSeparator'].$r->event).'\'"';
        if($r->win1 or $r->win2) {
            $lnk.=' style="font-weight:bold;"';
        }
        $tmpRow .= '<tr'.$lnk.'><td nowrap="nowrap">'.ltrim($r->target1,'0').($r->target1!=$r->target2 ? '-'.ltrim($r->target2,'0') : '').'</td><td nowrap="nowrap">'.$r->familyName1.'</td><td nowrap="nowrap">'.$r->familyName2.'</td></tr>';
        $cnt++;
    }

    echo '<div class="fixedHead">' . get_text('TotalMissingScorecars','Tournament',$cnt) . '</div>';
    echo '<div id="bcodeMissing"><table id="bcodeMissingTable">';
    echo '<colgroup><col class="w-10 pl-1"><col class="w-40"><col class="w-40"></colgroup>';
    echo '<tbody class="scrollBody">'.$tmpRow.'</tbody>';
    echo '</table></div>';
}
echo '</div></div>';
include('Common/Templates/tail.php');


function getScore($barcode, $strict=false) {
	@list($matchno, $team, $event) = @explode($_SESSION['BarCodeSeparator'], $barcode, 3);
    if(!is_numeric($matchno)) {
        $matchno = -1;
    }
    $matchno = ($matchno % 2 ? $matchno-1 : $matchno);
	$event=str_replace($_SESSION['BarCodeSeparator'], "-", $event??'');
	$rs=GetFinMatches($event, null, $matchno, $team, false);

	if($r= safe_fetch($rs)) {
        $obj = getEventArrowsParams($event, $r->phase ?? 0, $team);
        $r->winAt = $obj->winAt;
    }
	return $r;
}

function ConfirmMatch($Match) {
	require_once('Final/Fun_ChangePhase.inc.php');
	$prefix=($Match->teamEvent ? 'Tf' : 'Fin');
	$SQL= "update ".($Match->teamEvent ? 'Team' : '')."Finals
		set {$prefix}Confirmed=1,
		{$prefix}Status=1
		where {$prefix}Tournament={$_SESSION['TourId']}
			and {$prefix}Event='$Match->event'
			and {$prefix}Matchno in ($Match->match1, $Match->match2) ";
	safe_w_sql($SQL);
    if(safe_w_affected_rows()) {
	    updateOdfTiming('O', $_SESSION['TourId'], $Match->event, $Match->teamEvent, $Match->match1);
    }

	// sends the events for the confirmation of the match
	runJack("MatchFinished", $_SESSION['TourId'], array("Event"=>$Match->event ,"Team"=>$Match->teamEvent,"MatchNo"=>min($Match->match1, $Match->match2) ,"TourId"=>$_SESSION['TourId']));

	// promote the winner to the next phase
	if($Match->teamEvent) {
		move2NextPhaseTeam(null, $Match->event, $Match->match1);
	} else {
		move2NextPhase(null, $Match->event, $Match->match1);
	}

	runJack("FinConfirmEnd", $_SESSION['TourId'], array("Event"=>$Match->event, "Team"=>$Match->teamEvent, "MatchNo"=>min($Match->match1, $Match->match2), "Side"=>0, "TourId"=>$_SESSION['TourId']));
//ToBeVerified    runJack("FinConfirmEnd", $_SESSION['TourId'], array("Event"=>$Match->event, "Team"=>$Match->teamEvent, "MatchNo"=>min($Match->match1, $Match->match2), "Side"=>1, "TourId"=>$_SESSION['TourId']));
	runJack("MatchConfirmed", $_SESSION['TourId'], array("Event"=>$Match->event ,"Team"=>$Match->teamEvent,"MatchNo"=>min($Match->match1, $Match->match2), "TourId"=>$_SESSION['TourId']));
}
