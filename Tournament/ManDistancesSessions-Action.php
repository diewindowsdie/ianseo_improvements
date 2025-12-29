<?php

require_once(dirname(__FILE__, 2) . '/config.php');
require_once('Common/Lib/Fun_DateTime.inc.php');
require_once('Scheduler/LibScheduler.php');

$JSON=array('error'=>1);

if(!CheckTourSession() or !hasFullACL(AclCompetition, 'cSchedule', AclReadWrite) or empty($_REQUEST['act'])) {
    $JSON['msg']=get_text('NoPrivilege', 'Errors');
    JsonOut($JSON);
}

switch($_REQUEST['act']) {
    case 'getDistanceSessions':
        $JSON['error']=0;
        $Value=array();
        $q=safe_r_sql("SELECT `ToNumDist`, `SesOrder`, `SesName`, `DistanceInformation`.* 
            FROM `Tournament`
            INNER JOIN `Session` on `SesTournament`=`ToId` and SesType='Q'
            INNER JOIN `DistanceInformation` on DiTournament=ToId and DiSession=SesOrder AND DiType=SesType
            where `ToId`={$_SESSION['TourId']}");
        while($r=safe_fetch($q)) {
            if(!array_key_exists($r->DiSession, $Value)) {
                $Value[$r->DiSession] = array('Order'=>$r->SesOrder, 'Name'=>$r->SesName, 'Distances'=>array());
            }
            $Value[$r->DiSession]['Distances'][$r->DiDistance]=array(
                "Ends"=>$r->DiEnds,
                "Arrows"=>$r->DiArrows,
                "ScoringEnds"=>$r->DiScoringEnds,
                "ScoringOffset"=>$r->DiScoringOffset,
                "Day"=>$r->DiDay=='0000-00-00' ? '' : $r->DiDay,
                "Start"=>$r->DiStart=='00:00:00' ? '' : substr($r->DiStart, 0, 5),
                "Duration"=>$r->DiDuration,
                "WarmStart"=>$r->DiWarmStart=='00:00:00' ? '' : substr($r->DiWarmStart, 0, 5),
                "WarmDuration"=>$r->DiWarmDuration,
                "Options"=>$r->DiOptions
            );
        }
        break;
	case 'update':
		$JSON['value']='';
		if(!empty($_REQUEST['end'])) {
			foreach($_REQUEST['end'] as $Session => $Distances) {
				foreach($Distances as $Dist => $Value) {
					safe_w_sql("insert into DistanceInformation set
						DiTournament={$_SESSION['TourId']},
						DiDistance=$Dist,
						DiSession=$Session,
						DiType='Q',
						DiEnds=$Value
						on duplicate key update
						DiEnds=$Value,
						DiTourRules=''
						");
					$JSON['error']=0;
				}
			}
		} elseif(!empty($_REQUEST['arr'])) {
			foreach($_REQUEST['arr'] as $Session => $Distances) {
				foreach($Distances as $Dist => $Value) {
					safe_w_sql("insert into DistanceInformation set
						DiTournament={$_SESSION['TourId']},
						DiDistance=$Dist,
						DiSession=$Session,
						DiType='Q',
						DiArrows=$Value
						on duplicate key update
						DiArrows=$Value,
						DiTourRules=''
						");
					$JSON['error']=0;
				}
			}
		} elseif(!empty($_REQUEST['startday'])) {
			$ret=InsertSchedDate($_REQUEST['startday']);
			$JSON['error']=$ret['error'];
			$Value=$ret['day'];
		} elseif(!empty($_REQUEST['starttime'])) {
			$ret=InsertSchedTime($_REQUEST['starttime']);
			$JSON['error']=$ret['error'];
			$Value=$ret['start'];
		} elseif(!empty($_REQUEST['warmtime'])) {
			$ret=InsertSchedTime($_REQUEST['warmtime'], 'Warm');
			$JSON['error']=$ret['error'];
			$Value=$ret['warmtime'];
		} elseif(!empty($_REQUEST['duration'])) {
			$ret=InsertSchedDuration($_REQUEST['duration']);
			$JSON['error']=$ret['error'];
			$Value=$ret['duration'];
		} elseif(!empty($_REQUEST['warmduration'])) {
			$ret=InsertSchedDuration($_REQUEST['warmduration'], 'Warm');
			$JSON['error']=$ret['error'];
			$Value=$ret['warmduration'];
		} elseif(!empty($_REQUEST['comment'])) {
			$ret=InsertSchedComment($_REQUEST['comment']);
			$JSON['error']=$ret['error'];
			$Value=$ret['options'];
		} elseif(!empty($_REQUEST['shoot'])) {
			foreach($_REQUEST['shoot'] as $Session => $Distances) {
				foreach($Distances as $Dist => $Value) {
					safe_w_sql("insert into DistanceInformation set
						DiTournament={$_SESSION['TourId']},
						DiDistance=$Dist,
						DiSession=$Session,
						DiType='Q',
						DiScoringEnds=$Value
						on duplicate key update
						DiScoringEnds=$Value,
						DiTourRules=''
						");
					$JSON['error']=0;
				}
			}
		} elseif(!empty($_REQUEST['offset'])) {
			foreach($_REQUEST['offset'] as $Session => $Distances) {
				foreach($Distances as $Dist => $Value) {
					safe_w_sql("insert into DistanceInformation set
						DiTournament={$_SESSION['TourId']},
						DiDistance=$Dist,
						DiSession=$Session,
						DiType='Q',
						DiScoringOffset=$Value
						on duplicate key update
						DiScoringOffset=$Value,
						DiTourRules=''
						");
					$JSON['error']=0;
				}
			}
		}
		break;
	default:
		JsonOut($JSON);
}

$JSON['value']=$Value;
JsonOut($JSON);