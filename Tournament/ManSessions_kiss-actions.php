<?php

require_once(dirname(__FILE__, 2) . '/config.php');
$JSON=['error'=>1];

if(!CheckTourSession() or !hasFullACL(AclCompetition, 'cSchedule', AclReadWrite)) {
    jsonout($JSON);
}

require_once('Common/Fun_Sessions.inc.php');
require_once('Tournament/Fun_ManSessions.inc.php');

switch($_REQUEST['act']??'') {
    case 'a4t':
        if (!IsBlocked(BIT_BLOCK_TOURDATA)) {
            if (!empty($_REQUEST['session']) and ($reqSession = intval($_REQUEST['session']) and $reqSession <= 255) AND
                !empty($_REQUEST['value']) and ($reqValue = intval($_REQUEST['value']) and $reqValue <= 26)) {
                $curValue = GetSessions('Q',false,array($reqSession.'_Q'));
                if(count($curValue) == 1) {
                    if($reqValue<$curValue[0]->SesAth4Target) {
                        $q = safe_r_SQL("SELECT COUNT(*) as numEntries
                            FROM `Qualifications` INNER JOIN `Entries` ON `QuId`=`EnId` 
                            WHERE `EnTournament`={$curValue[0]->SesTournament} AND `QuSession`={$curValue[0]->SesOrder} AND `QuLetter`>CHAR(64+$reqValue)");
                        if($r=safe_fetch($q) and $r->numEntries==0) {
                            updateSession(
                                $curValue[0]->SesTournament,
                                $curValue[0]->SesOrder,
                                $curValue[0]->SesType,
                                $curValue[0]->SesName,
                                $curValue[0]->SesLocation,
                                $curValue[0]->SesTar4Session,
                                $reqValue,
                                $curValue[0]->SesFirstTarget,
                                $curValue[0]->SesFollow,
                                $curValue[0]->SesDtStart,
                                $curValue[0]->SesDtEnd,
                                $curValue[0]->SesOdfCode,
                                $curValue[0]->SesOdfPeriod,
                                $curValue[0]->SesOdfVenue,
                                $curValue[0]->SesOdfLocation,
                                $curValue[0]->SesEvents
                            );
                            $JSON['error']=0;
                            $JSON['sessions'] = sessionList();
                        } else {
                            $JSON['msg'] = get_text('StillEntriesInSession', 'Errors');
                        }
                    } else {
                        updateSession(
                            $curValue[0]->SesTournament,
                            $curValue[0]->SesOrder,
                            $curValue[0]->SesType,
                            $curValue[0]->SesName,
                            $curValue[0]->SesLocation,
                            $curValue[0]->SesTar4Session,
                            $reqValue,
                            $curValue[0]->SesFirstTarget,
                            $curValue[0]->SesFollow,
                            $curValue[0]->SesDtStart,
                            $curValue[0]->SesDtEnd,
                            $curValue[0]->SesOdfCode,
                            $curValue[0]->SesOdfPeriod,
                            $curValue[0]->SesOdfVenue,
                            $curValue[0]->SesOdfLocation,
                            $curValue[0]->SesEvents
                        );
                        $JSON['error']=0;
                        $JSON['sessions'] = sessionList();
                    }
                }
            }
        } else {
            $JSON['msg'] = get_text('EditLocked', 'Errors');
        }
        break;
    case 't4s':
        if (!IsBlocked(BIT_BLOCK_TOURDATA)) {
            if (!empty($_REQUEST['session']) and ($reqSession = intval($_REQUEST['session']) and $reqSession <= 255) AND
                !empty($_REQUEST['value']) and ($reqValue = intval($_REQUEST['value']) and $reqValue <= 9999)) {
                $curValue = GetSessions('Q',false,array($reqSession.'_Q'));
                if(count($curValue) == 1) {
                    if($reqValue<$curValue[0]->SesTar4Session) {
                        $q = safe_r_SQL("SELECT COUNT(*) as numEntries 
                            FROM `Qualifications` INNER JOIN `Entries` ON `QuId`=`EnId` 
                            WHERE `EnTournament`={$curValue[0]->SesTournament} AND `QuSession`={$curValue[0]->SesOrder} AND `QuTarget`>=({$curValue[0]->SesFirstTarget}+$reqValue)");
                        if($r=safe_fetch($q) and $r->numEntries==0) {
                            updateSession(
                                $curValue[0]->SesTournament,
                                $curValue[0]->SesOrder,
                                $curValue[0]->SesType,
                                $curValue[0]->SesName,
                                $curValue[0]->SesLocation,
                                $reqValue,
                                $curValue[0]->SesAth4Target,
                                $curValue[0]->SesFirstTarget,
                                $curValue[0]->SesFollow,
                                $curValue[0]->SesDtStart,
                                $curValue[0]->SesDtEnd,
                                $curValue[0]->SesOdfCode,
                                $curValue[0]->SesOdfPeriod,
                                $curValue[0]->SesOdfVenue,
                                $curValue[0]->SesOdfLocation,
                                $curValue[0]->SesEvents
                            );
                            $JSON['error']=0;
                            $JSON['sessions'] = sessionList();
                        } else {
                            $JSON['msg'] = get_text('StillEntriesInSession', 'Errors');
                        }
                    } else {
                        updateSession(
                            $curValue[0]->SesTournament,
                            $curValue[0]->SesOrder,
                            $curValue[0]->SesType,
                            $curValue[0]->SesName,
                            $curValue[0]->SesLocation,
                            $reqValue,
                            $curValue[0]->SesAth4Target,
                            $curValue[0]->SesFirstTarget,
                            $curValue[0]->SesFollow,
                            $curValue[0]->SesDtStart,
                            $curValue[0]->SesDtEnd,
                            $curValue[0]->SesOdfCode,
                            $curValue[0]->SesOdfPeriod,
                            $curValue[0]->SesOdfVenue,
                            $curValue[0]->SesOdfLocation,
                            $curValue[0]->SesEvents
                        );
                        $JSON['error']=0;
                        $JSON['sessions'] = sessionList();
                    }
                }
            }
        } else {
            $JSON['msg'] = get_text('EditLocked', 'Errors');
        }
        break;
    case 'updateSessions':
        if (!IsBlocked(BIT_BLOCK_TOURDATA)) {
            if (!empty($_REQUEST['reqSession']) and ($reqSession = intval($_REQUEST['reqSession']) and $reqSession <= 255)) {
                if($reqSession<GetNumQualSessions()) {
                    $q = safe_r_SQL("SELECT COUNT(*) as numEntries from `Qualifications` INNER JOIN `Entries` ON `QuId`=`EnId` WHERE `EnTournament`=".$_SESSION['TourId']." AND `QuSession`>$reqSession");
                    if($r=safe_fetch($q) and $r->numEntries==0) {
                        foreach(range(GetNumQualSessions(),$reqSession+1) as $ses) {
                            deleteSession($_SESSION['TourId'], $ses, 'Q');
                        }
                        $JSON['error']=0;
                        $JSON['sessions'] = sessionList();
                    } else {
                        $JSON['msg'] = get_text('StillEntriesInSession', 'Errors');
                    }
                } else if($reqSession>GetNumQualSessions()) {
                    $currentSessions = GetSessions('Q');
                    $lastSession = $currentSessions[count($currentSessions)-1];
                    foreach(range(GetNumQualSessions()+1,$reqSession) as $ses) {
                        $x=insertSession(
                            $_SESSION['TourId'],
                            $ses,
                            'Q',
                            '',
                            null,
                            $lastSession->SesTar4Session,
                            $lastSession->SesAth4Target,
                            $lastSession->SesFirstTarget,
                            0
                        );
                    }
                    $JSON['error']=0;
                    $JSON['sessions'] = sessionList();
                } else {
                    $JSON['error']=0;
                    $JSON['sessions'] = sessionList();
                }
                $tmpSesDist = array();
                $tmpNumDist = 0;
                $Sql = "SELECT `SesOrder`, `DiDistance`, `DiEnds`, `DiArrows`, `ToNumDist`, `SesTournament`
                    from `Session`
                    inner join `Tournament` on `ToId`=`SesTournament`
                    inner join `DistanceInformation` on `SesTournament`=`DiTournament` and `SesOrder`=`DiSession` and SesType='Q'
                    where `SesTournament`=".$_SESSION['TourId']." 
                    order by `SesOrder`, `DiDistance`";
                $q = safe_r_SQL($Sql);
                while($r=safe_fetch($q)) {
                    $tmpNumDist = $r->ToNumDist;
                    $tmpSesDist[$r->SesOrder.'.'.$r->DiDistance] = $r;
                }
                foreach(GetSessions('Q') as $ses) {
                    foreach(range(1,$tmpNumDist) as $dist) {
                        if(!isset($tmpSesDist[$ses->SesOrder.'.'.$dist])) {
                            safe_w_sql("insert ignore into DistanceInformation set
                                DiTournament=" . $tmpSesDist[($ses->SesOrder-1).'.'.$dist]->SesTournament . ",
                                DiSession=$ses->SesOrder,
                                DiDistance=$dist,
                                DiEnds=" . $tmpSesDist[($ses->SesOrder-1).'.'.$dist]->DiEnds . ",
                                DiArrows=" . $tmpSesDist[($ses->SesOrder-1).'.'.$dist]->DiArrows . ",
                                DiType='Q'");
                        }
                    }
                }

            } else {
                $JSON['msg'] = get_text('TooManySessions', 'Errors');
            }
        } else {
            $JSON['msg'] = get_text('EditLocked', 'Errors');
        }
        break;
    case 'getSessions':
        $JSON['error']=0;
        $JSON['sessions'] = sessionList();
        break;
}

jsonout($JSON);

function sessionList() {
    $list = array();
    $sessions=GetSessions('Q');
    foreach ($sessions as $session) {
        $list[] = array(
            "Order"=>$session->SesOrder,
            "Name"=>$session->SesName,
            "Tar4Session"=>$session->SesTar4Session,
            "Ath4Target"=>$session->SesAth4Target,
            "FirstTarget"=>$session->SesFirstTarget
        );
    }
    return $list;
}