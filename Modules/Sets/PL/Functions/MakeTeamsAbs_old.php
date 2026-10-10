<?php
{

        if (is_null($Societa)) {
            $Errore = CreateTmpTeamsAndSnap($ToId);

            if ($Errore == 1)
                return 1;
        }

        $Delete
            = "DELETE FROM "
            . "te, tc "
            . "USING "
            . "Teams AS te INNER JOIN TeamComponent AS tc "
            . "ON te.TeCoId=tc.TcCoId AND te.TeEvent=tc.TcEvent AND te.TeTournament=tc.TcTournament AND te.TeFinEvent=tc.TcFinEvent  "
            . "INNER JOIN ("
            . "SELECT EcCode AS sqEcCode, EcTournament AS sqEcTournament "
            . "FROM EventClass "
            . "WHERE "
            . "EcTournament=" . StrSafe_DB($ToId) . " AND EcTeamEvent<>0 "
            . (!is_null($Societa) ? " AND EcClass=" . StrSafe_DB($Cl) . " AND EcDivision=" . StrSafe_DB($Div) . " " : "")
            . ") AS sq ON te.TeEvent=sqEcCode AND te.TeTournament=sqEcTournament "
            . "WHERE "
            . "te.TeFinEvent=1 AND te.TeTournament=" . StrSafe_DB($ToId) . " "
            . (!is_null($Societa) ? "AND te.TeCoId=" . StrSafe_DB($Societa) . " " : "");

        $Rs = safe_w_sql($Delete);
        //
        // I'm extracting the list of events for the team finals
        $Select = "SELECT DISTINCT EcCode, EvPartialTeam, EvMultiTeam, EvMultiTeamNo, EvMixedTeam, EvTeamCreationMode, EvRunning, EvLockResults
		FROM EventClass 
	    INNER JOIN Events ON EcCode=EvCode AND EvTeamEvent=1 AND EcTeamEvent!='0' AND EcTournament=EvTournament 
		WHERE EcTournament=" . StrSafe_DB($ToId);
        if (!is_null($Societa)) {
            $Select
                .= " AND EcDivision=" . StrSafe_DB($Div) . " AND EcClass=" . StrSafe_DB($Cl) . " ";
        }
        $RsSel = safe_r_sql($Select);

        if (safe_num_rows($RsSel) > 0) {
            $ToCode = getCodeFromId($ToId);

            while ($RowEv = safe_fetch($RsSel)) {
                $EventCode = $RowEv->EcCode;
                if (!in_array($EventCode, $events4abs)) {
                    $events4abs[] = $EventCode;
                }

                if($RowEv->EvMixedTeam){
                    // creating teams for mixed team events
                $MyQuery = 'SELECT EcCode, EcTeamEvent, EcNumber, EcDivision, EcClass, EcSubClass, EcExtraAddons  '
                    . ' FROM EventClass '
                    . 'WHERE EcTournament = ' . StrSafe_DB($ToId) . ' AND EcTeamEvent!=0 and EcCode=' . StrSafe_DB($EventCode) . ' '
                    . 'ORDER BY EcCode, EcTeamEvent, EcDivision, EcClass';
                $RsDef = safe_r_sql($MyQuery);
                if (!$RsDef) {
                    $Errore = 1;
                } else {
                    $TeamDef = array();
                    $TeamNum = array();
                    while ($MyRowDef = safe_fetch($RsDef)) {
                        if (!array_key_exists($MyRowDef->EcTeamEvent, $TeamDef))
                            $TeamDef[$MyRowDef->EcTeamEvent] = array();
                        $TeamDef[$MyRowDef->EcTeamEvent][] = array($MyRowDef->EcDivision, $MyRowDef->EcClass, $MyRowDef->EcSubClass, $MyRowDef->EcExtraAddons);
                        $TeamPar[$MyRowDef->EcCode] = $RowEv->EvPartialTeam;

                        if (!array_key_exists($MyRowDef->EcTeamEvent, $TeamNum))
                            $TeamNum[$MyRowDef->EcTeamEvent] = $MyRowDef->EcNumber;

                    }
                    $tmp = array(array(),array(),array(),array());
                    if($RowEv->EvLockResults) {
                        foreach (range(1, $RowEv->EvLockResults) as $n) {
                            $tmp[0][] = "QuD{$n}Score";
                            $tmp[1][] = "QuD{$n}Gold";
                            $tmp[2][] = "QuD{$n}Xnine";
                            $tmp[3][] = "QuD{$n}Hits";
                        }
                        $tmp[0] = "(".implode('+', $tmp[0]).")";
                        $tmp[1] = "(".implode('+', $tmp[1]).")";
                        $tmp[2] = "(".implode('+', $tmp[2]).")";
                        $tmp[3] = "(".implode('+', $tmp[3]).")";

                    } else {
                        $tmp = array('QuScore', 'QuGold', 'QuXnine', 'QuHits');
                    }
                    $MyQuery = 'select * from (';
                    foreach ($TeamDef as $key => $value) {
                        $ifc = ifSqlForCountry($RowEv->EvTeamCreationMode);
                        $MyQuery .= "(SELECT {$ifc} AS Country, " . $key . " as CheQuery, EnId,  0 AS EnSubTeam,
						{$tmp[0]} as QuScore, {$tmp[1]} as QuGold, {$tmp[2]} as QuXnine, {$tmp[3]} as QuHits, " . ($RowEv->EvRunning == 1 ? "({$tmp[0]}/{$tmp[3]})" : "{$tmp[0]}") . " As ScoreCalc
						FROM Entries 
							INNER JOIN Qualifications ON EnId=QuId and (QuScore>0 or QuHits>0)
						    inner join IrmTypes on IrmId=QuIrmType and IrmShowRank=1 
						    LEFT JOIN ExtraData on EdId=EnId and EdType='P' "
                            . "WHERE EnAthlete=1 AND {$ifc}<>0 "
                            . " AND " . ($RowEv->EvMixedTeam ? "EnTeamMixEvent" : "EnTeamFEvent") . "=1 "
                            . " AND EnStatus <= 1 AND (QuScore!=0 OR QuHits!=0) AND EnTournament = " . StrSafe_DB($ToId) . " AND ";
                        $tmpQuery = array();
                        foreach ($value as $vDef) {
                            $tmpQuery[] = "(".
                                "EnDivision='".$vDef[0]."' ".
                                "AND EnClass='".$vDef[1]."' ".
                                (empty($vDef[2]) ? "" : "AND EnSubClass='".$vDef[2]."' ").
                                (empty($vDef[3]) ? "" : "AND (EdExtra & ".$vDef[3].") != 0").
                                ")";
                        }
                        $MyQuery .= '(' . implode(' OR ', $tmpQuery) . ')';
                        $MyQuery .= (!is_null($Societa) ? " AND {$ifc}=" . StrSafe_DB($Societa) : '') . ') ';
                        $MyQuery .= "UNION ALL ";
                    }
                    $MyQuery = substr($MyQuery, 0, -1 * strlen("UNION ALL "))
                        . ") TeamEntries 
					group by EnId
					ORDER BY Country, EnSubTeam, CheQuery, ScoreCalc DESC, QuGold DESC, QuXnine DESC,EnId ASC ";
                    //print $RowEv->EcCode . ":<br>" . $MyQuery. "<br><br>\n";//exit;
                    $Rs = safe_r_sql($MyQuery);
                    if (!$Rs) {
                        $Errore = 1;
                    } else {
                        $CurTeam = 0;                                    //Codice team attuale
                        $CurSubTeam = ($RowEv->EvMultiTeam ? 1 : 0);    //Codice Subteam attuale
                        $CurSubQuery = 0;                                //Codice team attuale
                        $TeamCount = array();            //contatore degli elementi trovati
                        $CurComponent = array();        //Componenti della Squadra
                        $Scores = array();
                        $Golds = array();
                        $XNines = array();
                        $Hits = array();
                        while ($MyRow = safe_fetch($Rs)) {
                            
                            $MyRow->EnSubTeam = 0;
                            
                            // Change Team
                            if ($CurTeam != $MyRow->Country or ($MyRow->EnSubTeam != 0 and $CurSubTeam != $MyRow->EnSubTeam)) {
                                //If Partial Team I have to scroll the complete array
                                if ($RowEv->EvPartialTeam == 1) {
                                    foreach ($TeamCount as $k => $value) {
                                        if (array_sum($TeamCount[$k]) > 0 and count($CurComponent[$k]) > 1 and ($k == 0 or ($k > 0 and $RowEv->EvMultiTeam != 0 and ($RowEv->EvMultiTeamNo == 0 or $RowEv->EvMultiTeamNo >= $k))))    //Insert Partial Team if not complete yet
                                            WriteTeamAbs($CurTeam, $k, $CurComponent[$k], $EventCode, $Scores[$k], $Golds[$k], $XNines[$k], $Hits[$k], $ToId);
                                    }
                                }
                                $CurTeam = $MyRow->Country;
                                $CurSubTeam = ($MyRow->EnSubTeam != 0 ? $MyRow->EnSubTeam : ($RowEv->EvMultiTeam ? 1 : 0));
                                $CurSubQuery = $MyRow->CheQuery;
                                $TeamCount = array();
                                $CurComponent = array();
                                $Scores = array();
                                $Golds = array();
                                $XNines = array();
                                $Hits = array();
                                $TeamCount[$CurSubTeam] = $TeamNum;
                                $CurComponent[$CurSubTeam] = array();
                                $Scores[$CurSubTeam] = array();
                                $Golds[$CurSubTeam] = array();
                                $XNines[$CurSubTeam] = array();
                                $Hits[$CurSubTeam] = array();
                            }
                            //Multiteam Management
                            if ($MyRow->EnSubTeam == 0 and $RowEv->EvMultiTeam and ($TeamCount[$CurSubTeam][$MyRow->CheQuery] == 0 || $CurSubQuery != $MyRow->CheQuery)) {
                                if ($CurSubQuery == $MyRow->CheQuery) {
                                    $CurSubTeam++;
                                } else {
                                    $CurSubTeam = 1;
                                }

                                if (empty($TeamCount[$CurSubTeam])) {
                                    $TeamCount[$CurSubTeam] = $TeamNum;
                                    $CurComponent[$CurSubTeam] = array();
                                    $Scores[$CurSubTeam] = array();
                                    $Golds[$CurSubTeam] = array();
                                    $XNines[$CurSubTeam] = array();
                                    $Hits[$CurSubTeam] = array();
                                }
                                $CurSubQuery = $MyRow->CheQuery;        //Manage the subQueryCounter
                            }
                            //Looking for team components
                            if (array_sum($TeamCount[$CurSubTeam]) > 0) {
                                if ($TeamCount[$CurSubTeam][$MyRow->CheQuery] > 0) {
                                    if (!in_array($MyRow->EnId, $CurComponent[$CurSubTeam])) {
                                        if(1){
                                        $CurComponent[$CurSubTeam][] = $MyRow->EnId;
                                        $Scores[$CurSubTeam][] = $MyRow->QuScore;
                                        $Golds[$CurSubTeam][] = $MyRow->QuGold;
                                        $XNines[$CurSubTeam][] = $MyRow->QuXnine;
                                        $Hits[$CurSubTeam][] = $MyRow->QuHits;
                                        $TeamCount[$CurSubTeam][$MyRow->CheQuery]--;
                                        }
                                        //If we have everybody, we save....
                                        if (array_sum($TeamCount[$CurSubTeam]) == 0 and ($RowEv->EvMultiTeamNo == 0 or $RowEv->EvMultiTeamNo >= $CurSubTeam)) {
                                            $Scores[$CurSubTeam][3] = 0;
                                            $Golds[$CurSubTeam][3] = 0;
                                            $XNines[$CurSubTeam][3] = 0;
                                            $Hits[$CurSubTeam][3] = 0;
                                            WriteTeamAbs($CurTeam, $CurSubTeam, $CurComponent[$CurSubTeam], $EventCode, $Scores[$CurSubTeam], $Golds[$CurSubTeam], $XNines[$CurSubTeam], $Hits[$CurSubTeam], $ToId);
                                        }
                                    }
                                }
                            }

                        }
                        //If Partial Team I have to scroll the complete array
                        if ($RowEv->EvPartialTeam == 1) {
                            foreach ($TeamCount as $k => $value) {
                                //Insert Partial Team if not complete yet
                                if (array_sum($TeamCount[$k]) > 0 and count($CurComponent[$k]) > 1 and ($k == 0 or ($k > 0 and $RowEv->EvMultiTeam != 0 and ($RowEv->EvMultiTeamNo == 0 or $RowEv->EvMultiTeamNo >= $k)))) {
                                    WriteTeamAbs($CurTeam, $k, $CurComponent[$k], $EventCode, $Scores[$k], $Golds[$k], $XNines[$k], $Hits[$k], $ToId);
                                }
                            }
                        }
                    }
                }
            }else{
                //For non mixed team event
                $MyQuery = 'SELECT EcCode, EcTeamEvent, EcNumber, EcDivision, EcClass, EcSubClass, EcExtraAddons  '
                    . ' FROM EventClass '
                    . 'WHERE EcTournament = ' . StrSafe_DB($ToId) . ' AND EcTeamEvent!=0 and EcCode=' . StrSafe_DB($EventCode) . ' '
                    . 'ORDER BY EcCode, EcTeamEvent, EcDivision, EcClass';
                $RsDef = safe_r_sql($MyQuery);
                if (!$RsDef) {
                    $Errore = 1;
                } else {
                    $TeamDef = array();
                    $TeamNum = array();
                    while ($MyRowDef = safe_fetch($RsDef)) {
                        if (!array_key_exists($MyRowDef->EcTeamEvent, $TeamDef))
                            $TeamDef[$MyRowDef->EcTeamEvent] = array();
                        $TeamDef[$MyRowDef->EcTeamEvent][] = array($MyRowDef->EcDivision, $MyRowDef->EcClass, $MyRowDef->EcSubClass, $MyRowDef->EcExtraAddons);
                        $TeamPar[$MyRowDef->EcCode] = $RowEv->EvPartialTeam;

                        if (!array_key_exists($MyRowDef->EcTeamEvent, $TeamNum))
                            $TeamNum[$MyRowDef->EcTeamEvent] = $MyRowDef->EcNumber;

                    }
                    $tmp = array(array(),array(),array(),array());
                    if($RowEv->EvLockResults) {
                        foreach (range(1, $RowEv->EvLockResults) as $n) {
                            $tmp[0][] = "QuD{$n}Score";
                            $tmp[1][] = "QuD{$n}Gold";
                            $tmp[2][] = "QuD{$n}Xnine";
                            $tmp[3][] = "QuD{$n}Hits";
                        }
                        $tmp[0] = "(".implode('+', $tmp[0]).")";
                        $tmp[1] = "(".implode('+', $tmp[1]).")";
                        $tmp[2] = "(".implode('+', $tmp[2]).")";
                        $tmp[3] = "(".implode('+', $tmp[3]).")";

                    } else {
                        $tmp = array('QuScore', 'QuGold', 'QuXnine', 'QuHits');
                    }
                    $MyQuery = 'select * from (';
                    foreach ($TeamDef as $key => $value) {
                        $ifc = ifSqlForCountry($RowEv->EvTeamCreationMode);
                        $MyQuery .= "(SELECT {$ifc} AS Country, " . $key . " as CheQuery, EnId, EnSubTeam,
						{$tmp[0]} as QuScore, {$tmp[1]} as QuGold, {$tmp[2]} as QuXnine, {$tmp[3]} as QuHits, " . ($RowEv->EvRunning == 1 ? "({$tmp[0]}/{$tmp[3]})" : "{$tmp[0]}") . " As ScoreCalc
						FROM Entries 
							INNER JOIN Qualifications ON EnId=QuId and (QuScore>0 or QuHits>0)
						    inner join IrmTypes on IrmId=QuIrmType and IrmShowRank=1 
						    LEFT JOIN ExtraData on EdId=EnId and EdType='P' "
                            . "WHERE EnAthlete=1 AND {$ifc}<>0 "
                            . " AND " . ($RowEv->EvMixedTeam ? "EnTeamMixEvent" : "EnTeamFEvent") . "=1 "
                            . " AND EnStatus <= 1 AND (QuScore!=0 OR QuHits!=0) AND EnSubTeam <> 0 AND EnTournament = " . StrSafe_DB($ToId) . " AND ";
                        $tmpQuery = array();
                        foreach ($value as $vDef) {
                            $tmpQuery[] = "(".
                                "EnDivision='".$vDef[0]."' ".
                                "AND EnClass='".$vDef[1]."' ".
                                (empty($vDef[2]) ? "" : "AND EnSubClass='".$vDef[2]."' ").
                                (empty($vDef[3]) ? "" : "AND (EdExtra & ".$vDef[3].") != 0").
                                ")";
                        }
                        $MyQuery .= '(' . implode(' OR ', $tmpQuery) . ')';
                        $MyQuery .= (!is_null($Societa) ? " AND {$ifc}=" . StrSafe_DB($Societa) : '') . ') ';
                        $MyQuery .= "UNION ALL ";
                    }
                    $MyQuery = substr($MyQuery, 0, -1 * strlen("UNION ALL "))
                        . ") TeamEntries 
					group by EnId
					ORDER BY Country, EnSubTeam, CheQuery, ScoreCalc DESC, QuGold DESC, QuXnine DESC,EnId ASC ";
                    //print $RowEv->EcCode . ":<br>" . $MyQuery. "<br><br>\n";//exit;
                    $Rs = safe_r_sql($MyQuery);
                    if (!$Rs) {
                        $Errore = 1;
                    } else {
                        $CurTeam = 0;                                    //Codice team attuale
                        $CurSubTeam = ($RowEv->EvMultiTeam ? 1 : 0);    //Codice Subteam attuale
                        $CurSubQuery = 0;                                //Codice team attuale
                        $TeamCount = array();            //contatore degli elementi trovati
                        $CurComponent = array();        //Componenti della Squadra
                        $Scores = array();
                        $Golds = array();
                        $XNines = array();
                        $Hits = array();
                        while ($MyRow = safe_fetch($Rs)) {
                            if($RowEv->EvMixedTeam){
                                $MyRow->EnSubTeam = 0;
                            }
                            // Change Team
                            if ($CurTeam != $MyRow->Country or ($MyRow->EnSubTeam != 0 and $CurSubTeam != $MyRow->EnSubTeam)) {
                                //If Partial Team I have to scroll the complete array
                                if ($RowEv->EvPartialTeam == 1) {
                                    foreach ($TeamCount as $k => $value) {
                                        if (array_sum($TeamCount[$k]) > 0 and count($CurComponent[$k]) > 1 and ($k == 0 or ($k > 0 and $RowEv->EvMultiTeam != 0 and ($RowEv->EvMultiTeamNo == 0 or $RowEv->EvMultiTeamNo >= $k))))    //Insert Partial Team if not complete yet
                                            WriteTeamAbs($CurTeam, $k, $CurComponent[$k], $EventCode, $Scores[$k], $Golds[$k], $XNines[$k], $Hits[$k], $ToId);
                                    }
                                }
                                $CurTeam = $MyRow->Country;
                                $CurSubTeam = ($MyRow->EnSubTeam != 0 ? $MyRow->EnSubTeam : ($RowEv->EvMultiTeam ? 1 : 0));
                                $CurSubQuery = $MyRow->CheQuery;
                                $TeamCount = array();
                                $CurComponent = array();
                                $Scores = array();
                                $Golds = array();
                                $XNines = array();
                                $Hits = array();
                                $TeamCount[$CurSubTeam] = $TeamNum;
                                $CurComponent[$CurSubTeam] = array();
                                $Scores[$CurSubTeam] = array();
                                $Golds[$CurSubTeam] = array();
                                $XNines[$CurSubTeam] = array();
                                $Hits[$CurSubTeam] = array();
                            }
                            //Multiteam Management
                            if ($MyRow->EnSubTeam == 0 and $RowEv->EvMultiTeam and ($TeamCount[$CurSubTeam][$MyRow->CheQuery] == 0 || $CurSubQuery != $MyRow->CheQuery)) {
                                if ($CurSubQuery == $MyRow->CheQuery) {
                                    $CurSubTeam++;
                                } else {
                                    $CurSubTeam = 1;
                                }

                                if (empty($TeamCount[$CurSubTeam])) {
                                    $TeamCount[$CurSubTeam] = $TeamNum;
                                    $CurComponent[$CurSubTeam] = array();
                                    $Scores[$CurSubTeam] = array();
                                    $Golds[$CurSubTeam] = array();
                                    $XNines[$CurSubTeam] = array();
                                    $Hits[$CurSubTeam] = array();
                                }
                                $CurSubQuery = $MyRow->CheQuery;        //Manage the subQueryCounter
                            }
                            //Looking for team components
                            if (array_sum($TeamCount[$CurSubTeam]) > 0) {
                                if ($TeamCount[$CurSubTeam][$MyRow->CheQuery] > 0) {
                                    if (!in_array($MyRow->EnId, $CurComponent[$CurSubTeam])) {
                                        if(1){
                                        $CurComponent[$CurSubTeam][] = $MyRow->EnId;
                                        $Scores[$CurSubTeam][] = $MyRow->QuScore;
                                        $Golds[$CurSubTeam][] = $MyRow->QuGold;
                                        $XNines[$CurSubTeam][] = $MyRow->QuXnine;
                                        $Hits[$CurSubTeam][] = $MyRow->QuHits;
                                        $TeamCount[$CurSubTeam][$MyRow->CheQuery]--;
                                        }
                                        //If we have everybody, we save....
                                        if (array_sum($TeamCount[$CurSubTeam]) == 0 and ($RowEv->EvMultiTeamNo == 0 or $RowEv->EvMultiTeamNo >= $CurSubTeam)) {
                                            $Scores[$CurSubTeam][3] = 0;
                                            $Golds[$CurSubTeam][3] = 0;
                                            $XNines[$CurSubTeam][3] = 0;
                                            $Hits[$CurSubTeam][3] = 0;
                                            WriteTeamAbs($CurTeam, $CurSubTeam, $CurComponent[$CurSubTeam], $EventCode, $Scores[$CurSubTeam], $Golds[$CurSubTeam], $XNines[$CurSubTeam], $Hits[$CurSubTeam], $ToId);
                                        }
                                    }
                                }
                            }

                        }
                        //If Partial Team I have to scroll the complete array
                        if ($RowEv->EvPartialTeam == 1) {
                            foreach ($TeamCount as $k => $value) {
                                //Insert Partial Team if not complete yet
                                if (array_sum($TeamCount[$k]) > 0 and count($CurComponent[$k]) > 1 and ($k == 0 or ($k > 0 and $RowEv->EvMultiTeam != 0 and ($RowEv->EvMultiTeamNo == 0 or $RowEv->EvMultiTeamNo >= $k)))) {
                                    WriteTeamAbs($CurTeam, $k, $CurComponent[$k], $EventCode, $Scores[$k], $Golds[$k], $XNines[$k], $Hits[$k], $ToId);
                                }
                            }
                        }
                    }
                }
            }
        }
        }
        Obj_RankFactory::create('AbsTeam', array(
            'tournament' => $ToId,
            'events' => $events4abs
        ))->calculate();

        if ($Errore == 0 and is_null($Societa)) {
            $tmp = FindTeamEventsWithNull($ToId);
            if ($tmp === false) {
                $Errore = 1;
            } else {
                $Errore = SetupShootoff($tmp, $ToId);

                if ($Errore == 0) {
                    $toRemove = SetupTeamsRank($tmp, $ToId);

                    if ($toRemove !== false) {
                        foreach ($toRemove as $e) {
                            if (($x = array_search($e, $events4abs)) !== false) {
                                unset($events4abs[$x]);
                            }
                        }
                    }
                }
            }
            DropTmpTeams();
        }
        foreach ($events4abs as $eventAbs) {
            runJack("QRRankUpdate", $ToId, array("Event" => $eventAbs, "Team" => 1, "TourId" => $ToId));
        }
    }
