<?php
require_once(dirname(__FILE__) . '/config.php');
require_once('Common/Lib/Fun_Phases.inc.php');
require_once('Common/Lib/ArrTargets.inc.php');

$TourId=0;
if(isset($_REQUEST['CompCode']) && preg_match("/[a-z0-9_.-]+$/i", $_REQUEST['CompCode'])) {
	$TourId = getIdFromCode($_REQUEST['CompCode']);
}

$json_array=array();

$SQL="SELECT EvCode, EvEventName, EvTeamEvent, EvFinalFirstPhase FROM Events WHERE EvTournament=$TourId AND EvFinalFirstPhase!=0 order by EvTeamEvent, EvProgr";
$SQL="(" . "SELECT 0 as RoundRobin, EvCode, EvEventName, EvTeamEvent, EvProgr, EvFinalFirstPhase, coalesce(SubQualified, 0) as EvLastPhaseOrLevels 
        FROM Events 
        left join (select EvCodeParent as ParentCode, EvNumQualified as SubQualified from Events where EvTournament=$TourId and EvCodeParentWinnerBranch=1) SubEvent on ParentCode=EvCode
        WHERE EvTournament=$TourId AND EvFinalFirstPhase!=0 
    ) UNION (
        SELECT 1 as RoundRobin, EvCode, EvEventName, EvTeamEvent, EvProgr, EvFinalFirstPhase, group_concat(concat_ws('§', RrLevLevel, RrLevName) order by RrLevLevel separator '|') as EvLastPhaseOrLevels
        FROM Events 
        inner join RoundRobinLevel on RrLevEvent=EvCode and RrLevTeam=EvTeamEvent and RrLevTournament=EvTournament
        WHERE EvTournament=$TourId AND EvFinalFirstPhase=0 and EvElimType=5
        group by EvCode, EvTeamEvent
    )
    order by EvTeamEvent, EvProgr";
// Retrieve the Event List
$q=safe_r_sql($SQL);
while($r=safe_fetch($q)) {
    if($r->RoundRobin) {
        // get the Round Robin Events
        foreach (explode('|', $r->EvLastPhaseOrLevels) as $ph) {
            $phase=explode('§', $ph);
            $tmpPhases[]=[
                "PhCode"=>"R{$phase[0]}",
                "PhPhase"=>$phase[1],
            ];
        }

        $tgtInfo = GetMaxScores($r->EvCode, 0, $r->EvTeamEvent, $TourId);
//        $tgtTargetFace = array('Radius'=>$tgtInfo['TargetRadius'], 'Rings'=>array());
        $tgtTargetFace = array(
            'Radius'=>$tgtInfo['TargetRadius'],
            'smallTen'=>in_array($tgtInfo['TargetId'],array(3,4)),
            'hasX'=>in_array($tgtInfo['TargetId'],array(5,9,10,13,14,15,16,20,21,24,25,29,30,31)),
            'convertX'=>in_array($tgtInfo['TargetId'],array(5,9,10,13,14,15,16,20,21)) ? 'X' : (in_array($tgtInfo['TargetId'],array(24,25,29,30,31)) ? '11' : ''),
            'hasDot'=>$tgtInfo['HasDot'],
            'Rings'=>array()
        );
        foreach( $tgtInfo['Arrows'] as $item) {
            if($item['size']) {
                unset($item['size']);
                unset($item['letter']);
                $tgtTargetFace['Rings'][$item['print']] = $item;
            }
        }
    } else {
        $tmpPhases = array();
        $cntPhase = 1;
        $phases = getPhasesId($r->EvFinalFirstPhase);
        foreach ($phases as $ph) {
            $tmpPhases[] = array("PhCode" => strval(bitwisePhaseId($ph)), "PhPhase" => get_text(namePhase($r->EvFinalFirstPhase, $ph) . "_Phase"), "PhNameShort" => getPhaseTV($ph, $cntPhase), "PhName" => get_text(getPhaseTV($ph, $cntPhase) . "_Phase", "Tournament"));
            $cntPhase++;
        }

        $tgtInfo = GetMaxScores($r->EvCode, 0, $r->EvTeamEvent, $TourId);
        $tgtTargetFace = array('Radius' => $tgtInfo['TargetRadius'], 'Rings' => array());
        foreach ($tgtInfo['Arrows'] as $item) {
            if ($item['size']) {
                unset($item['size']);
                unset($item['letter']);
                $tgtTargetFace['Rings'][$item['print']] = $item;
            }
        }
    }
    $json_array[] = array("Event" => $r->EvCode, "Type" => $r->EvTeamEvent, "EvName" => $r->EvEventName, "Distance" => $tgtInfo['Distance'], "Phases" => $tmpPhases, "TargetFace" => $tgtTargetFace);
}


// Return the json structure with the callback function that is needed by the app
SendResult($json_array);
