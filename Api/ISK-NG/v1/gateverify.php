<?php
require_once('Accreditation/Lib.php');

$Options=GetParameter('AccessApp', false, array(), true);
if(empty($Options)) {
    $res = array('action' => 'gateverify', 'error' => 1, 'apiVersion' => $req->apiVersion, 'device' => $req->device);
    return;
}

$q=safe_r_sql("select IceContent, ToCode from IdCardElements inner join Tournament on ToId=IceTournament where IceType IN ('AthQrCode') and IceTournament in (".implode(',', array_keys($Options)).")");
$regexpList = array();
while ($r = safe_fetch($q)) {
    $RegExp = preg_quote('{ENCODE}-{DIVISION}-{CLASS}', '/');
    if ($r->IceContent != '') {
        $RegExp = preg_quote($r->IceContent, '/');
    }
    $regexpList[$r->ToCode] = getIceRegExpMatches($r->IceContent);
}

$EnId=0;
if(!empty($req->id)) {
    $EnId=CheckAccreditationCode($req->id, $Options, true);
}

if(empty($EnId)) {
    $res = array('action' => 'gateverify', 'error' => 1, 'apiVersion' => $req->apiVersion, 'device' => $req->device);
    return;
}

$qEntry = "select EnId, IFNULL(localbib.EdExtra,EnCode) as AthCode, QuSession, '' as ScheduledSession, ToId, ToCode, EnName, EnFirstName, EnCountry, CoCode, CoName, AeId is not null as Accredited, 
		AcArea0, AcArea1, AcArea2, AcArea3, AcArea4, AcArea5, AcArea6, AcArea7, AcAreaStar, caption.EdExtra as EnCaption, IFNULL(extras.EdExtra,0) as AthExtras, 
		(ClAthlete*DivAthlete) AS  AcIsAthlete, DivDescription, ClDescription, DivId, ClId
		from Entries
		inner join Qualifications on QuId=EnId
		inner join Tournament on ToId=EnTournament
		inner join Countries on CoTournament=EnTournament and CoId=EnCountry
		left join Divisions on DivTournament=EnTournament and DivId=EnDivision
		left join Classes on ClTournament=EnTournament and ClId=EnClass
		left join Eliminations on ElId=EnId
		LEFT JOIN AccEntries ON AeId=EnId AND AEOperation=1
		LEFT JOIN AccColors ON AcTournament=EnTournament AND CONCAT(TRIM(EnDivision),TRIM(EnClass)) LIKE AcDivClass
		LEFT JOIN ExtraData as caption ON caption.EdId=EnId and caption.EdType='C'
		LEFT JOIN ExtraData as localbib ON localbib.EdId=EnId and localbib.EdType='Z'
		LEFT JOIN ExtraData as extras ON extras.EdId=EnId and extras.EdType='P'
		WHERE EnId=$EnId";
$q=safe_r_sql($qEntry);
if($r=safe_fetch($q)) {
    $Template=array(
        'key' => '',
        'enCode' => '',
        'givName' => '',
        'famName' => '',
        'coCode' => '',
        'coName' => '',
        'caption' => '',
        'status' => '',
        'direction' => 0,
        'zones' => array(),
        'extras' => 0,
        'hash' => ''
    );
    $zones=array();
    if($r->AcArea0) $zones[]='0'.($r->AcAreaStar ? '*' : '');
    if($r->AcArea1) $zones[]='1'.($r->AcAreaStar ? '*' : '');
    if($r->AcArea2) $zones[]='2';
    if($r->AcArea3) $zones[]='3';
    if($r->AcArea4) $zones[]='4';
    if($r->AcArea5) $zones[]='5';
    if($r->AcArea6) $zones[]='6';
    if($r->AcArea7) $zones[]='7';
    $status=0; // present, not accredited
    if($r->Accredited) {
        $status = 1;
        // check if the entry is in a wrong session
        if(!empty($Options[$r->ToId])) {
            // we have sessions so check if session=0 and is not athlete... it is a coach
            $status = CheckStatus($r, $EnId, $Options);
            /*if($status!=2) {
                // check if this upgrade is linked to someone else's bib in another competition?
                // if yes completely swap the accreditatoion
                // select the extradata of the other competition
                $t=safe_r_sql("select EdExtra, CoCode
					from ExtraData 
					inner join Entries on EnId=EdId and EnTournament in (".implode(', ', array_keys($Options)).")
					inner join Countries on CoId=EnCountry
					where EdType='Z' and EdId=$EnId");

                if($u=safe_fetch($t) and $u->EdExtra) {
                    $bits=explode('-', $u->EdExtra);
                    $TmpEnCode = $bits[0];
                    if(count($bits)>1) {
                        // this is a coach upgrade
                        $IsCoach=1;
                        $TmpCoCode = $bits[1];
                        $t=safe_r_sql("select EnId, EnCode, 0 QuSession, '' as ScheduledSession, ToId, ToCode, EnName, EnFirstName, CoId as EnCountry, CoCode, CoName, AeId is not null as Accredited, 
							AcArea0, AcArea1, AcArea2, AcArea3, AcArea4, AcArea5, AcArea6, AcArea7, AcAreaStar, EdExtra as EnCaption, 
							0 AS  AcIsAthlete, DivDescription, ClDescription, DivId
							from Entries
							inner join Qualifications on QuId=EnId
							inner join Tournament on ToId=EnTournament
							inner join Countries on CoTournament=EnTournament and CoCode='$TmpCoCode'
							left join Divisions on DivTournament=EnTournament and DivId=EnDivision
							left join Classes on ClTournament=EnTournament and ClId=EnClass
							left join Eliminations on ElId=EnId
							LEFT JOIN AccEntries ON AeId=EnId AND AEOperation=1
							LEFT JOIN AccColors ON AcTournament=EnTournament AND CONCAT(TRIM(EnDivision),TRIM(EnClass)) LIKE AcDivClass
							LEFT JOIN ExtraData ON EdId=EnId and EdType='C'
							where EnCode='$TmpEnCode' and EnTournament in (".implode(', ', array_keys($Options)).")");
                    } else {
                        // normal upgrade linked to somebody
                        $IsCoach=0;
                        $t=safe_r_sql("select EnId, EnCode, QuSession, '' as ScheduledSession, ToId, ToCode, EnName, EnFirstName, CoId as EnCountry, CoCode, CoName, AeId is not null as Accredited, 
							AcArea0, AcArea1, AcArea2, AcArea3, AcArea4, AcArea5, AcArea6, AcArea7, AcAreaStar, EdExtra as EnCaption, 
							0 AS  AcIsAthlete, DivDescription, ClDescription, DivId
							from Entries
							inner join Qualifications on QuId=EnId
							inner join Tournament on ToId=EnTournament
							inner join Countries on CoTournament=EnTournament and CoId=EnCountry
							left join Divisions on DivTournament=EnTournament and DivId=EnDivision
							left join Classes on ClTournament=EnTournament and ClId=EnClass
							left join Eliminations on ElId=EnId
							LEFT JOIN AccEntries ON AeId=EnId AND AEOperation=1
							LEFT JOIN AccColors ON AcTournament=EnTournament AND CONCAT(TRIM(EnDivision),TRIM(EnClass)) LIKE AcDivClass
							LEFT JOIN ExtraData ON EdId=EnId and EdType='C'
							where EnCode='$TmpEnCode' and EnTournament in (".implode(', ', array_keys($Options)).")");
                    }

                    if($u=safe_fetch($t)) {
                        if($IsCoach) {
                            $status=CheckStatus($u, $EnId, $Options);
                        }
                        if($status==1) {
                            // gets name and picture of the linked entry
                            if(!$r->AcAreaStar) $r->AcAreaStar = $u->AcAreaStar;
                            if(!$r->AcArea0) $r->AcArea0 = $u->AcArea0;
                            if(!$r->AcArea1) $r->AcArea1 = $u->AcArea1;
                            if(!$r->AcArea2) $r->AcArea2 = $u->AcArea2;
                            if(!$r->AcArea3) $r->AcArea3 = $u->AcArea3;
                            if(!$r->AcArea4) $r->AcArea4 = $u->AcArea4;
                            if(!$r->AcArea5) $r->AcArea5 = $u->AcArea5;
                            if(!$r->AcArea6) $r->AcArea6 = $u->AcArea6;
                            if(!$r->AcArea7) $r->AcArea7 = $u->AcArea7;
                            //$r->EnCaption=$u->EnCaption;
                            $ToCode=$u->ToCode;
                            $CoCode=$u->CoCode;
                            $CoName=$u->CoName;
                            $r->EnId=$u->EnId;
                        }
                    }
                }
            }*/
        }
    }

    $Caption=$r->EnCaption;
    if(empty($Caption)) {
        if($r->AcIsAthlete) {
            $Caption=$r->DivDescription . ' ' . $r->ClDescription;
        } else {
            $Caption=$r->ClDescription;
        }
    }
    $Direction=0;
    if(!empty($req->isgate)) {
        $Direction=GetGateAccess($EnId);
        $Direction=($Direction ? -1*$Direction : ($status==1 ? 1 : 0));
    }
    $Template['direction']=$Direction;

    $Template['key'] = $r->ToCode . '|' . $r->AthCode  . '|' . $r->CoCode . '|' . $r->DivId;
    if(array_key_exists($r->ToCode,$regexpList)) {
        $Template['key'] = $r->ToCode . '|' . $r->AthCode . ($regexpList[$r->ToCode]["country"] != -1 ? '|' . $r->CoCode : '') . ($regexpList[$r->ToCode]["division"] != -1 ? '|' . $r->DivId : '');
    }
    $Template['enCode']=$r->AthCode;
    $Template['famName']=$r->EnFirstName;
    $Template['givName']=$r->EnName;
    $Template['coCode']=$r->CoCode;
    $Template['coName']=$r->CoName;
    $Template['caption']=$Caption;
    $Template['status']=$status;
    $Template['zones']=$zones;
    $Template['extras']=intval($r->AthExtras);



    $Template['hash'] = md5($Template['enCode'].$Template['famName'].$Template['givName'].$Template['coCode'].$Template['coName'].$Template['caption'].implode(',',$Template['zones']).$Template['extras']);
    $tmpList[$Template['key']]=$Template;

}

$res = array(
    'action' => 'gateverify',
    'error' => 0,
    'apiVersion' => $req->apiVersion,
    'device' => $req->device,
    'id' => $req->id,
    'status' => 999,
    'direction' => 999,
    'participant' => array_values($tmpList)
);

function CheckStatus($r, $EnId, $Options) {
    if($r->QuSession==0 and !$r->AcIsAthlete) {
        // coach, so check if there are  archers from that NOC in one of the sessions selected
        $SQL = "(SELECT DISTINCT CONCAT('Q',ToNumDist,QuSession) as keyValue, '' as Bye
			FROM Entries 
			inner join Qualifications on QuId=EnId 
			INNER JOIN Tournament ON ToId=EnTournament
			WHERE EnCountry=$r->EnCountry
			) UNION ALL (
			SELECT DISTINCT CONCAT('E1',ElSession) as keyValue, '' as Bye
			FROM Eliminations 
			inner join Entries on EnId=ElId
			INNER JOIN Events ON EvTournament=ElTournament and EvCode=ElEventCode and EvTeamEvent=0 and EvElim1>0
			WHERE EnCountry=$r->EnCountry and ElElimPhase=0
			) UNION ALL (
			SELECT DISTINCT CONCAT('E2',ElSession) as keyValue, '' as Bye
			FROM Eliminations 
			inner join Entries on EnId=ElId
			INNER JOIN Events ON EvTournament=ElTournament and EvCode=ElEventCode and EvTeamEvent=0 and EvElim1>0
			WHERE EnCountry=$r->EnCountry and ElElimPhase=1
			) UNION ALL (
			SELECT DISTINCT CONCAT('I', FSScheduledDate, FSScheduledTime) AS keyValue, '' as Bye
			FROM Finals
			inner join Entries on EnId=FinAthlete
			inner join FinSchedule on FSEvent=FinEvent and FSTeamEvent=0 and FsTournament=FinTournament and FsMatchNo=FinMatchNo
			WHERE EnCountry=$r->EnCountry
			) UNION ALL (
			SELECT DISTINCT CONCAT('T', FSScheduledDate, FSScheduledTime) AS keyValue, '' as Bye
			FROM TeamFinComponent
			inner join Entries on EnId=TfcId
			inner join TeamFinals on TfTeam=TfcCoId and TfSubTeam=TfcSubTeam and TfTournament=TfcTournament and TfEvent=TfcEvent
			inner join FinSchedule on FSEvent=TfEvent and FSTeamEvent=1 and FsTournament=TfTournament and FsMatchNo=TfMatchNo
			WHERE EnCountry=$r->EnCountry
			)";
    } else {
        $SQL = "(SELECT DISTINCT CONCAT('Q',ToNumDist,QuSession) as keyValue, '' as Bye
			FROM Entries 
			inner join Qualifications on QuId=EnId 
			INNER JOIN Tournament ON ToId=EnTournament
			WHERE EnId=$EnId
			) UNION ALL (
			SELECT DISTINCT CONCAT('E1',ElSession) as keyValue, '' as Bye
			FROM Eliminations 
			INNER JOIN Events ON EvTournament=ElTournament and EvCode=ElEventCode and EvTeamEvent=0 and EvElim1>0
			WHERE ElId=$EnId and ElElimPhase=0
			) UNION ALL (
			SELECT DISTINCT CONCAT('E2',ElSession) as keyValue, '' as Bye
			FROM Eliminations 
			INNER JOIN Events ON EvTournament=ElTournament and EvCode=ElEventCode and EvTeamEvent=0 and EvElim1>0
			WHERE ElId=$EnId and ElElimPhase=1
			) UNION ALL (
			SELECT DISTINCT CONCAT('I', FSScheduledDate, FSScheduledTime) AS keyValue, FinTie as Bye
			FROM Finals
			inner join FinSchedule on FSEvent=FinEvent and FSTeamEvent=0 and FsTournament=FinTournament and FsMatchNo=FinMatchNo
			WHERE FinAthlete=$EnId
			) UNION ALL (
			SELECT DISTINCT CONCAT('T', FSScheduledDate, FSScheduledTime) AS keyValue, TfTie as Bye
			FROM TeamFinComponent
			inner join TeamFinals on TfTeam=TfcCoId and TfSubTeam=TfcSubTeam and TfTournament=TfcTournament and TfEvent=TfcEvent
			inner join FinSchedule on FSEvent=TfEvent and FSTeamEvent=1 and FsTournament=TfTournament and FsMatchNo=TfMatchNo
			WHERE TfcId=$EnId
			)";
    }

    $t=safe_r_sql($SQL);
    $status='2';
    while($u=safe_fetch($t)) {
        if(in_array($u->keyValue, $Options[$r->ToId])) {
            $status='1';
            break;
        } elseif(in_array(strtolower($u->keyValue), $Options[$r->ToId]) and $u->Bye!=2) {
            $status='1';
            break;
        }
    }

    return $status;
}