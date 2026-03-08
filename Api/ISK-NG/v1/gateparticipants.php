<?php
require_once('Accreditation/Lib.php');

$Options=GetParameter('AccessApp', false, array(), true);
if(empty($Options)) {
    $res = array('action' => 'gateparticipants', 'error' => 1, 'device' => $req->device);
    return;
}

// get all the accreditation QRcodes for this competition...
$regexpList = array();
$q=safe_r_sql("select IceContent, ToCode from IdCardElements inner join Tournament on ToId=IceTournament where IceType IN ('AthQrCode') and IceTournament in (".implode(',', array_keys($Options)).")");
while ($r = safe_fetch($q)) {
    $replacements = array(
        '\\{ENCODE\\}' => '(.+?)',
        '\\{COUNTRY\\}' => '(.+?)',
        '\\{DIVISION\\}' => '(.+?)',
        '\\{CLASS\\}' => '(.+?)',
        '\\{TOURNAMENT\\}' => '(.+?)',
    );
    $RegExp = preg_quote('{ENCODE}-{DIVISION}-{CLASS}', '/');
    if ($r->IceContent != '') {
        $RegExp = preg_quote($r->IceContent, '/');
    }
    $RegExp = '^' . str_replace(array_keys($replacements), array_values($replacements), $RegExp) . '$';
    $RegArray = getIceRegExpMatches($r->IceContent);
    $RegArray['formula'] = $RegExp;
    $RegArray['competition'] = $r->ToCode;
    if (!in_array($RegArray, $regexpList)) {
        $regexpList[$r->ToCode] = $RegArray;
    }
}

// get all entries allowed by the setup
$tmpList=array();
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
		LEFT JOIN ExtraData as localbib ON localbib.EdId=EnId and localbib.EdType='Z' and localbib.EdExtra!=''
		LEFT JOIN ExtraData as extras ON extras.EdId=EnId and extras.EdType='P'
		WHERE EnTournament in (".implode(',', array_keys($Options)).")";
$q=safe_r_sql($qEntry);
while($r=safe_fetch($q)) {

    $Template=array(
        'key' => '',
        'entryCode' => '',
        'givenName' => '',
        'familyName' => '',
        'countryCode' => '',
        'countryName' => '',
        'caption' => '',
        'status' => '',
        'zones' => array(),
        'extras' => 0,
        'hasPhoto' => 0,
        'tournament' => '',
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
        if (!empty($Options[$r->ToId])) {
            $status = 2;
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
    $Template['key'] = $r->AthCode  . '|' . $r->CoCode . '|' . $r->DivId;
    if(array_key_exists($r->ToCode,$regexpList)) {
        $Template['key'] = ($regexpList[$r->ToCode]["tocode"] != -1 ? $r->ToCode .'|' : '') . $r->AthCode . ($regexpList[$r->ToCode]["country"] != -1 ? '|' . $r->CoCode : '') . ($regexpList[$r->ToCode]["division"] != -1 ? '|' . $r->DivId : '');
    }
    $Template['entryCode']=$r->AthCode;
    $Template['familyName']=$r->EnFirstName;
    $Template['givenName']=$r->EnName;
    $Template['countryCode']=$r->CoCode;
    $Template['countryName']=$r->CoName;
    $Template['caption']=$Caption;
    $Template['status']=$status;
    $Template['zones']=$zones;
    $Template['extras']=intval($r->AthExtras);
    if(file_exists($im=$CFG->DOCUMENT_PATH.'TV/Photos/'.$r->ToCode.'-En-'.$r->EnId.'.jpg')) {
        $Template['hasPhoto']=1;
    }
    $Template['tournament']=$r->ToCode;
    $Template['hash'] = md5($Template['entryCode'].$Template['tournament'].$Template['hasPhoto'].$Template['familyName'].$Template['givenName'].$Template['countryCode'].$Template['countryName'].$Template['caption'].implode(',',$Template['zones']).$Template['extras']);
    $tmpList[$Template['key']]=$Template;
}
//Search for specific option competitions
foreach ($Options as $ToId=>$Sessions) {
    if(!empty($Sessions)) {
        $ToCode=getCodeFromId($ToId);
        $tmpKeyStr= "ToCode,'|',IFNULL(localbib.EdExtra,EnCode),'|',CoCode,'|',EnDivision";
        if(array_key_exists($ToCode,$regexpList)) {
            $tmpKeyStr = ($regexpList[$ToCode]["tocode"] != -1 ? "ToCode,'|'," : "")."IFNULL(localbib.EdExtra,EnCode)" . ($regexpList[$ToCode]["country"] != -1 ? ",'|',CoCode" : "") . ($regexpList[$ToCode]["division"] != -1 ? ",'|',EnDivision" : "");
        }
        $SQL = "
            (
                SELECT DISTINCT CONCAT({$tmpKeyStr}) as keyValue, EnCountry as cntOff
                FROM Entries 
                inner join Countries on CoTournament=EnTournament and CoId=EnCountry
                inner join Qualifications on QuId=EnId 
                INNER JOIN Tournament ON ToId=EnTournament
                LEFT JOIN ExtraData as localbib ON localbib.EdId=EnId and localbib.EdType='Z' and localbib.EdExtra!=''
                WHERE EnTournament=$ToId AND EnAthlete=1 AND CONCAT('Q',ToNumDist,QuSession) IN ('" . implode("','", $Sessions) . "')
            ) UNION ALL (
                SELECT DISTINCT CONCAT({$tmpKeyStr}) as keyValue, EnCountry as cntOff
                FROM Eliminations 
                INNER JOIN Entries ON ElId=EnId
                inner join Countries on CoTournament=EnTournament and CoId=EnCountry
                INNER JOIN Tournament ON ToId=EnTournament
                INNER JOIN Events ON EvTournament=ElTournament and EvCode=ElEventCode and EvTeamEvent=0 and EvElim1>0
                LEFT JOIN ExtraData as localbib ON localbib.EdId=EnId and localbib.EdType='Z' and localbib.EdExtra!=''
            WHERE ElTournament=$ToId AND EnAthlete=1 AND CONCAT('E1',ElSession) IN ('" . implode("','", $Sessions) . "') and ElElimPhase=0
            ) UNION ALL (
                SELECT DISTINCT CONCAT({$tmpKeyStr}) as keyValue, EnCountry as cntOff
                FROM Eliminations 
                INNER JOIN Entries ON ElId=EnId
                inner join Countries on CoTournament=EnTournament and CoId=EnCountry
                INNER JOIN Tournament ON ToId=EnTournament
                INNER JOIN Events ON EvTournament=ElTournament and EvCode=ElEventCode and EvTeamEvent=0 and EvElim1>0
                LEFT JOIN ExtraData as localbib ON localbib.EdId=EnId and localbib.EdType='Z' and localbib.EdExtra!=''
                WHERE ElTournament=$ToId AND EnAthlete=1 AND CONCAT('E2',ElSession) IN ('" . implode("','", $Sessions) . "') and ElElimPhase=1
            ) UNION ALL (
                SELECT DISTINCT CONCAT({$tmpKeyStr}) AS keyValue, EnCountry as cntOff
                FROM Finals
                INNER JOIN Entries ON FinAthlete=EnId
                inner join Countries on CoTournament=EnTournament and CoId=EnCountry
                INNER JOIN Tournament ON ToId=EnTournament
                inner join FinSchedule on FSEvent=FinEvent and FSTeamEvent=0 and FsTournament=FinTournament and FsMatchNo=FinMatchNo
                LEFT JOIN ExtraData as localbib ON localbib.EdId=EnId and localbib.EdType='Z' and localbib.EdExtra!=''
                WHERE FinTournament=$ToId AND EnAthlete=1 AND CONCAT('I', FSScheduledDate, FSScheduledTime) IN ('" . implode("','", $Sessions) . "')
            ) UNION ALL (
                SELECT DISTINCT CONCAT({$tmpKeyStr}) AS keyValue, EnCountry as cntOff
                FROM TeamFinComponent
                INNER JOIN Entries ON TfcId=EnId
                inner join Countries on CoTournament=EnTournament and CoId=EnCountry
                INNER JOIN Tournament ON ToId=EnTournament
                inner join TeamFinals on TfTeam=TfcCoId and TfSubTeam=TfcSubTeam and TfTournament=TfcTournament and TfEvent=TfcEvent
                inner join FinSchedule on FSEvent=TfEvent and FSTeamEvent=1 and FsTournament=TfTournament and FsMatchNo=TfMatchNo
                LEFT JOIN ExtraData as localbib ON localbib.EdId=EnId and localbib.EdType='Z' and localbib.EdExtra!=''
                WHERE TfTournament=$ToId AND EnAthlete=1 AND CONCAT('T', FSScheduledDate, FSScheduledTime) IN ('" . implode("','", $Sessions) . "')
            )";
        $cntList = Array();
        $q = safe_r_sql($SQL);
        while ($r = safe_fetch($q)) {
            if(array_key_exists($r->keyValue, $tmpList)) {
                $tmpList[$r->keyValue]['status'] = 1;
            }
            if (!in_array($r->cntOff, $cntList)) {
                $cntList[] = $r->cntOff;
            }
        }
        $SQL = "SELECT DISTINCT CONCAT({$tmpKeyStr}) as keyValue
            FROM Entries 
            inner join Qualifications on QuId=EnId 
            inner join Countries on CoTournament=EnTournament and CoId=EnCountry
            INNER JOIN Tournament ON ToId=EnTournament
            LEFT JOIN ExtraData as localbib ON localbib.EdId=EnId and localbib.EdType='Z' and localbib.EdExtra!=''
            WHERE EnTournament=$ToId AND EnAthlete=0 AND QuSession=0 ";
        if(count($cntList)) {
            $SQL .= "AND EnCountry IN (" . implode(",", $cntList) . ")";
        }
        $q = safe_r_sql($SQL);
        while ($r = safe_fetch($q)) {
            if(array_key_exists($r->keyValue, $tmpList)) {
                $tmpList[$r->keyValue]['status'] = 1;
            }
        }
    }
}

$res = array(
    'action' => 'gateparticipants',
    'error' => 0,
    'device' => $req->device,
    'participants' => array_values($tmpList)
);