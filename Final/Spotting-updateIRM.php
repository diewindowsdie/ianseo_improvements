<?php

require_once(dirname(__FILE__, 2) . '/config.php');
require_once('Fun_MatchTotal.inc.php');
require_once('Common/Lib/CommonLib.php');

global $CFG;

$JSON=array('error'=>1, 'msg'=>get_text('Error'));

if(empty($_REQUEST['event']) or !isset($_REQUEST['team']) or !isset($_REQUEST['matchno']) or !isset($_REQUEST['value']) or !CheckTourSession()) {
	JsonOut($JSON);
}

$event=$_REQUEST['event'];
$team=intval($_REQUEST['team']);
$match=intval($_REQUEST['matchno']);
$irm=intval($_REQUEST['value']);

if(($team==0 ? IsBlocked(BIT_BLOCK_IND) : IsBlocked(BIT_BLOCK_TEAM)) or !hasFullACL(($team ? AclTeams : AclIndividuals), '', AclReadWrite)) {
	JsonOut($JSON);
}

$JSON['msg']=get_text('CmdOk');
if ($team) {
	safe_w_sql("UPDATE `TeamFinals` SET `TfIrmType`=$irm WHERE `TfMatchNo`=$match and `TfEvent`='$event' and `TfTournament`={$_SESSION['TourId']}");
	switch($irm) {
        case 0:
		case 5: // DNF: rank is OK, lost the match, move opponent to next phase
		case 10: // DNS: rank is OK, lost the match, move opponent to next phase
			break;
		case 15: // DSQ: rank is OK but not shown, lost the match, goes last of his phase
			$q=safe_r_sql("select TfTeam, TfSubTeam
				from TeamFinals
				WHERE TfEvent='$event' and TfMatchNo=$match and TfTournament={$_SESSION['TourId']}");
			if($r=safe_fetch($q)) {
				// updates all the team result with DSQ!
				safe_w_sql("UPDATE Teams SET TeIrmTypeFinal=15 WHERE (TeCoId, TeSubTeam, TeEvent, TeTournament) = ($r->TfTeam, $r->TfSubTeam, '$event', {$_SESSION['TourId']})");
			}
			break;
		case 20: // DQB: Disqualified by behaviour, Virtually removed from any rank,
			// gets the Team ID
			$q=safe_r_sql("select TfTeam, TfSubTeam
				from TeamFinals
				WHERE TfEvent='$event' and TfMatchNo=$match and TfTournament={$_SESSION['TourId']}");
			if($r=safe_fetch($q)) {
				// updates all the team result with DSQ!
				safe_w_sql("UPDATE TeamFinals SET TfIrmType=20 WHERE (TfTeam, TfSubTeam, TfEvent, TfTournament) = ($r->TfTeam, $r->TfSubTeam, '$event', {$_SESSION['TourId']})");
				safe_w_sql("UPDATE Teams SET TeRank=$CFG->DERANKING, TeRankFinal=$CFG->DERANKING, TeIrmType=20, TeIrmTypeFinal=20 WHERE (TeCoId, TeSubTeam, TeEvent, TeTournament) = ($r->TfTeam, $r->TfSubTeam, '$event', {$_SESSION['TourId']})");
				$JSON['msg']='Please Check teams and individual rankings';
			}
			break;
		default:
			$JSON['msg']=get_text('Error');
			JsonOut($JSON);
	}

} else {
	safe_w_sql("UPDATE `Finals` SET `FinIrmType`=$irm WHERE `FinMatchNo`=$match and `FinEvent`='$event' and `FinTournament`={$_SESSION['TourId']}");
	switch($irm) {
        case 0:
		case 5: // DNF: rank is OK, lost the match, move opponent to next phase
		case 10: // DNS: rank is OK, lost the match, move opponent to next phase
			break;
		case 15: // DSQ: rank is OK but not shown, lost the match, goes last of his phase
			$q=safe_r_sql("select FinAthlete
				from Finals
				WHERE FinEvent='$event' and FinMatchNo=$match and FinTournament={$_SESSION['TourId']}");
			if($r=safe_fetch($q)) {
				// updates the Individual Final IRM with DSQ!
				safe_w_sql("UPDATE Individuals SET IndIrmTypeFinal=15 WHERE (IndId, IndEvent, IndTournament) = ($r->FinAthlete, '$event', {$_SESSION['TourId']})");
			}
			break;
		case 20: // DQB: Disqualified by behaviour, Virtually removed from any rank,
			// gets the Team ID
			$q=safe_r_sql("select FinAthlete
				from Finals
				WHERE FinEvent='$event' and FinMatchNo=$match and FinTournament={$_SESSION['TourId']}");
			if($r=safe_fetch($q)) {
				// updates all the Individual result with DQB!
				safe_w_sql("UPDATE Finals SET FinIrmType=20 WHERE (FinAthlete, FinEvent, FinTournament) = ($r->FinAthlete, '$event', {$_SESSION['TourId']})");
				safe_w_sql("UPDATE Individuals SET IndRank=$CFG->DERANKING, IndRankFinal=$CFG->DERANKING, IndIrmType=20, IndIrmTypeFinal=20 WHERE (IndId, IndEvent, IndTournament) = ($r->FinAthlete, '$event', {$_SESSION['TourId']})");
				safe_w_sql("UPDATE Qualifications SET QuIrmType=20 WHERE QuId=$r->FinAthlete");

				// check if he is part of a team in qualifications
				$t=safe_r_sql("select * from TeamComponent WHERE TcId=$r->FinAthlete");
				while($u=safe_fetch($t)) {
					// The team gets a DQB too!!!
					safe_w_sql("UPDATE Teams SET TeRank=$CFG->DERANKING, TeIrmType=20 WHERE (TeCoId, TeSubTeam, TeEvent, TeTournament) = ($u->TcCoId, $u->TcSubTeam, '$u->TcEvent', {$_SESSION['TourId']})");
				}

				// check if he is part of a team in Matches
				$t=safe_r_sql("select * from TeamFinComponent WHERE TfcId=$r->FinAthlete");
				while($u=safe_fetch($t)) {
					// The team gets a DQB too!!!
					safe_w_sql("UPDATE TeamFinals SET TfIrmType=20 WHERE (TfTeam, TfSubTeam, TfEvent, TfTournament) = ($u->TfcCoId, $u->TfcSubTeam, '$u->TfcEvent', {$_SESSION['TourId']})");
					safe_w_sql("UPDATE Teams SET TeRankFinal=$CFG->DERANKING, TeIrmTypeFinal=20 WHERE (TeCoId, TeSubTeam, TeEvent, TeTournament) = ($u->TfcCoId, $u->TfcSubTeam, '$u->TfcEvent', {$_SESSION['TourId']})");
				}
				$JSON['msg']='Please Check teams and individual rankings';
			}
			break;
		default:
			$JSON['msg']=get_text('Error');
			JsonOut($JSON);
	}
}

EvaluateMatch($event, $team, $match);
CheckDoubleIrm($event, $team, $match);

$JSON['error']=0;
JsonOut($JSON);

