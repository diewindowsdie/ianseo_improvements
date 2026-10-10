<?php
/**
 * SweFunctions - shared functions.
 *
 * Included by send.php. Contains:
 *  - the v1 payload builders (prepareIndQualification / prepareIndFinals), unchanged in output
 *  - the v2 payload builders (individual + team + tournament header)
 *  - the HTTP transport (sendInformation)
 *
 * API versions:
 *  v1  POST {host}/api/CompetitionResults       {auth, qualification, final}
 *  v2  POST {host}/api/v2/CompetitionResults    {apiVersion, module, tournament, auth,
 *                                               qualification, final, teamQualification, teamFinal,
 *                                               runArchery}  - always all blocks; a v2 POST replaces
 *                                               the competition's complete result set in ARS.
 *
 * See API_v2.md in this directory for the full v2 contract.
 */

/* =====================================================================
 *  Rank readers
 * ===================================================================== */

function getIndQualification() {
    $rank = Obj_RankFactory::create('DivClass', array());
    $rank->read();
    return $rank->getData();
}

function getIndFinals() {
    $rank = Obj_RankFactory::create('FinalInd', array());
    $rank->read();
    return $rank->getData();
}

/**
 * Qualification ranking of the team EVENTS (the events that have team finals, e.g. "LSR").
 * This is the ranking the team finals are seeded from (Obj_Rank_AbsTeam, TeFinEvent=1). IANSEO also
 * derives an automatic per-division/class team ranking (Obj_Rank_DivClassTeam, TeFinEvent=0, e.g. "RM"
 * next to a real "LSR" event); that one is NOT sent — it is not a team event the organizer set up and
 * it duplicated the real event's teams under another code (seen 2026-09-06).
 */
function getTeamEventQualification() {
    $rank = Obj_RankFactory::create('AbsTeam', array('components' => true));
    $rank->read();
    return $rank->getData();
}

function getTeamFinals() {
    $rank = Obj_RankFactory::create('FinalTeam', array('components' => true));
    $rank->read();
    return $rank->getData();
}

/**
 * Tournament header (v2 only). Read straight from the Tournament table.
 */
function getTournamentInfo() {
    $tourId = intval($_SESSION['TourId']);
    $q = safe_r_sql("SELECT ToId, ToCode, ToName, ToNameShort, ToCommitee, ToComDescr, ToWhere, ToVenue,
                            ToWhenFrom, ToWhenTo, ToLocRule, ToType, ToTypeName, ToTypeSubRule,
                            ToNumDist, ToNumEnds, ToGoldsChars, ToXNineChars, ToIocCode
                     FROM Tournament WHERE ToId={$tourId}");
    if (!$q || safe_num_rows($q) == 0) {
        return array('ianseoId' => $tourId);
    }
    $r = safe_fetch($q);
    return array(
        'ianseoId'   => intval($r->ToId),
        'code'       => $r->ToCode,
        'name'       => $r->ToName,
        'nameShort'  => $r->ToNameShort,
        'organizer'  => $r->ToCommitee,
        'organizerDescr' => $r->ToComDescr,
        'where'      => $r->ToWhere,
        'venue'      => $r->ToVenue,
        'whenFrom'   => $r->ToWhenFrom,
        'whenTo'     => $r->ToWhenTo,
        'locRule'    => $r->ToLocRule,
        'type'       => intval($r->ToType),
        'typeName'   => $r->ToTypeName,
        'typeSubRule'=> $r->ToTypeSubRule,
        'numDist'    => intval($r->ToNumDist),
        'numEnds'    => intval($r->ToNumEnds),
        'goldsChars' => $r->ToGoldsChars,
        'xnineChars' => $r->ToXNineChars,
        'iocCode'    => $r->ToIocCode,
    );
}

/* =====================================================================
 *  v1 builders - DO NOT change the output shape, the v1 ARS endpoint
 *  (and the Norwegian / Icelandic servers) depend on it.
 * ===================================================================== */

function prepareIndQualification($data) {
    $sektions = array();
    foreach ($data['sections'] as $key => $value) {
        $item = array();
        foreach ($value['items'] as $ikey => $ivalue) {
            $item[] = array(
                'bib'        => $ivalue['bib'],
                'familyname' => $ivalue['familyname'],
                'givenname'  => $ivalue['givenname'],
                'gender'     => $ivalue['gender'],
                'div'        => $ivalue['div'],
                'class'      => $ivalue['class'],
                'ageclass'   => $ivalue['ageclass'],
                'subclass'   => $ivalue['subclass'],
                'clubId'     => $ivalue['countryCode'],
                'clubName'   => $ivalue['countryName'],
                'rank'       => $ivalue['rank'],
                'score'      => $ivalue['score'],
                'gold'       => $ivalue['gold'],
                'xnine'      => $ivalue['xnine'],
                'dist_1'     => $ivalue['dist_1'],
                'dist_2'     => $ivalue['dist_2'],
                'dist_3'     => $ivalue['dist_3'],
                'dist_4'     => $ivalue['dist_4'],
                'dist_5'     => $ivalue['dist_5'],
                'dist_6'     => $ivalue['dist_6'],
                'dist_7'     => $ivalue['dist_7'],
                'dist_8'     => $ivalue['dist_8'],
                'hits'       => $ivalue['arrowsShot'],
                'id'         => $ivalue['id']
            );
        }

        $sektions[] = array('q_class' => $key, 'q_results' => array(
            'q_meta' => array(
                'descr'     => $value['meta']['descr'],
                'numdist'   => $value['meta']['numDist'],
                'maxArrows' => $value['meta']['maxArrows'],
                'score'     => $value['meta']['fields']['score'],
                'gold'      => $value['meta']['fields']['gold'],
                'xnine'     => $value['meta']['fields']['xnine'],
                'dist_1'    => $value['meta']['fields']['dist_1'],
                'dist_2'    => $value['meta']['fields']['dist_2'],
                'dist_3'    => $value['meta']['fields']['dist_3'],
                'dist_4'    => $value['meta']['fields']['dist_4'],
                'dist_5'    => $value['meta']['fields']['dist_5'],
                'dist_6'    => $value['meta']['fields']['dist_6'],
                'dist_7'    => $value['meta']['fields']['dist_7'],
                'dist_8'    => $value['meta']['fields']['dist_8']
            ),
            'q_items' => $item
        ));
    }
    return $sektions;
}

function prepareIndFinals($data) {
    $returnData = array();
    if (empty($data['sections'])) {
        return $returnData;   // no finals in this competition
    }
    foreach ($data['sections'] as $key => $value) {
        $item = array();
        foreach ($value['items'] as $ikey => $ivalue) {
            $currentFinals = array();
            foreach ($ivalue['finals'] as $fkey => $fvalue) {
                // A bye has no opponent: IANSEO stores FinAthlete=0 for it, and items is keyed by
                // athlete id, so items[0] does not exist. Reading it emitted two warnings per bye
                // (237 of them in one 9-tournament database) and produced null anyway. Sending that
                // null explicitly keeps the payload byte for byte what it has always been — with
                // error_reporting on, which IANSEO's debug mode turns on, the warnings were printed
                // ahead of the JSON and the send stopped being parseable.
                $opp = isset($value['items'][$fvalue['oppAthlete']])
                    ? $value['items'][$fvalue['oppAthlete']]['bib'] : null;
                $currentFinals[$fkey] = array(
                    'athleteId'    => $ivalue['id'],
                    'score'        => $fvalue['score'],
                    'setScore'     => $fvalue['setScore'],
                    'tie'          => $fvalue['tie'],
                    'tiebreak'     => $fvalue['tiebreak'],
                    'oppAthleteId' => $fvalue['oppAthlete'],
                    'oppAthlete'   => $opp,
                    'oppScore'     => $fvalue['oppScore'],
                    'oppSetScore'  => $fvalue['oppSetScore'],
                    'oppTie'       => $fvalue['oppTie'],
                    'oppTiebreak'  => $fvalue['oppTiebreak']
                );
            }

            $item[] = array(
                'id'         => $ivalue['id'],
                'bib'        => $ivalue['bib'],
                'familyname' => $ivalue['familyname'],
                'givenname'  => $ivalue['givenname'],
                'gender'     => $ivalue['gender'],
                'clubId'     => $ivalue['countryCode'],
                'clubName'   => $ivalue['countryName'],
                'rank'       => $ivalue['rank'],
                'elims'      => $ivalue['elims'],
                'finals'     => $currentFinals
            );
        }
        unset($value['meta']['fields']['finals']['fields']);
        // elim1/elim2 exist only for an event that HAS elimination rounds — one warning each per
        // class otherwise, which is every class in the three tournaments this was measured against.
        // null is what the payload has always carried for them; this just stops announcing it.
        $fields = $value['meta']['fields'];
        $returnData[] = array('f_class' => $key, 'f_results' => array(
            'f_meta' => array(
                'descr'      => $value['meta']['descr'],
                'firstPhase' => $value['meta']['firstPhase'],
                'elim1'      => isset($fields['elim1']) ? $fields['elim1'] : null,
                'elim2'      => isset($fields['elim2']) ? $fields['elim2'] : null,
                'finals'     => $value['meta']['fields']['finals']
            ),
            'f_items' => $item
        ));
    }
    return $returnData;
}

/* =====================================================================
 *  v2 builders
 *
 *  Individual blocks keep the exact v1 shape and only ADD fields, so the
 *  ARS v2 endpoint can reuse the v1 parser for them. Team blocks are new.
 * ===================================================================== */

/**
 * Decode one IANSEO arrowstring into the "10|9|X|M" form the finals blocks already use.
 *
 * One letter per arrow — the same loop Obj_Rank_FinalInd runs on its own arrowstrings, so
 * qualification and finals arrive in ARS in one format rather than two. A blank in the RAW string is
 * an arrow that was not shot and is skipped. The letter table lives in a global that
 * Common/Lib/ArrTargets.inc.php fills at include time; send.php requires it, and this guards anyway
 * so a caller that forgot cannot turn a payload into a fatal.
 *
 * ALL OR NOTHING. A letter this installation's $LetterPoint cannot read decodes to '', and appending
 * that as an arrow is how Jakt SM 2026 (event 2026299) was sent with 47% of its arrows blank: the
 * Swedish forest target scores V=20, S=15, T=15, R=10, U=10, F=5, A=miss, and S, T and U are
 * commented out of the table while R is mapped to a value from a different target. Liam Hellberg's
 * 30-arrow 420 arrived as 14 arrows adding up to 190 — plausible-looking, wrong, and impossible to
 * tell apart from a round where only 14 arrows were recorded.
 *
 * This is not a fault in the module: IANSEO's own ValutaArrowString scores that same string 460
 * against the 565 stored beside it, and its own DecodeFromString prints the same holes. There is no
 * per-target letter table in the schema to consult — Targets holds ring sizes and colours. So the
 * only honest answer for a string we cannot fully read is to send none of it and let the raw letters
 * (sent alongside) carry the data instead.
 *
 * Filed upstream: UPSTREAM-letterpoint-forest-swe.md, beside this file.
 */
function sweDecodeArrowstring($raw) {
    if (!is_string($raw) || $raw === '' || !function_exists('DecodeFromLetter')) {
        return '';
    }
    $arrows = array();
    for ($i = 0; $i < strlen($raw); ++$i) {
        if (trim($raw[$i]) === '') {
            continue;                       // an arrow that was not shot
        }
        $decoded = DecodeFromLetter($raw[$i]);
        if ($decoded === '') {
            return '';                      // a letter we cannot read: send nothing rather than most
        }
        $arrows[] = $decoded;
    }
    return implode('|', $arrows);
}

/**
 * The same rule for a string IANSEO decoded before we saw it.
 *
 * The finals blocks take their arrowstrings from the rank objects, which run the identical loop and
 * so have the identical hole — Obj_Rank_FinalInd:424-427. We do not have their raw letters without
 * changing the rank query, but the symptom is unambiguous: DecodeFromLetter only ever yields '' for
 * a letter it cannot read, and an arrow that was not shot was skipped rather than emitted. So an
 * empty token in an already-decoded string means the same thing, and gets the same answer.
 */
function sweDecodedArrowstringOrEmpty($decoded) {
    if (!is_string($decoded) || $decoded === '') {
        return '';
    }
    foreach (explode('|', $decoded) as $arrow) {
        if (trim($arrow) === '') {
            return '';
        }
    }
    return $decoded;
}

/** The letters exactly as IANSEO stores them, for a payload we could not decode. */
function sweRawArrowletters($raw) {
    return is_string($raw) ? $raw : '';
}

function prepareIndQualificationV2($data) {
    $sections = prepareIndQualification($data);
    // Enrich each item with fields v1 never carried. Same order as the source data.
    $i = 0;
    foreach ($data['sections'] as $key => $value) {
        $j = 0;
        foreach ($value['items'] as $ikey => $ivalue) {
            $sections[$i]['q_results']['q_items'][$j] += array(
                'clubIanseoId' => $ivalue['countryId'],
                'arrowsShot'   => $ivalue['arrowsShot'],
                'targetHits'   => $ivalue['hits'],
                'session'      => $ivalue['session'],
                'target'       => $ivalue['target'],
                'irm'          => $ivalue['irm'],
                'irmText'      => $ivalue['irmText'],
                'notes'        => $ivalue['notes'],
            );
            // Arrow by arrow, per distance. IANSEO holds these on the same row it already gave us
            // (Qualifications.QuD{n}ArrowString) and the finals blocks have carried their equivalent
            // since v2 shipped; qualification never did, so a competition's ranking round could not
            // be shown arrow by arrow. All eight keys are always present, empty for a distance that
            // was not shot, matching dist_1..dist_8 beside them.
            //
            // Two fields per distance. dist_N_arrowstring is the decoded round and is EMPTY when any
            // letter could not be read; dist_N_arrowletters is always IANSEO's own letters, so a
            // target this installation cannot decode still reaches ARS intact and can be decoded
            // there or later. See sweDecodeArrowstring for what went wrong without them.
            $arrowstrings = array();
            foreach (range(1, 8) as $n) {
                $raw = isset($ivalue['D' . $n . 'Arrowstring']) ? $ivalue['D' . $n . 'Arrowstring'] : '';
                $arrowstrings['dist_' . $n . '_arrowstring']  = sweDecodeArrowstring($raw);
                $arrowstrings['dist_' . $n . '_arrowletters'] = sweRawArrowletters($raw);
            }
            $sections[$i]['q_results']['q_items'][$j] += $arrowstrings;
            $j++;
        }
        // div/class are on the ITEMS, not on the section meta — reading them from the meta made two
        // documented v2 fields null in every payload ever sent. A DivClass section is exactly one
        // division and one class, so any item answers for the section; the first one is taken.
        $first = reset($value['items']);
        $sections[$i]['q_results']['q_meta'] += array(
            'event' => $value['meta']['event'],
            'div'   => is_array($first) && isset($first['div']) ? $first['div'] : '',
            'class' => is_array($first) && isset($first['class']) ? $first['class'] : '',
        );
        $i++;
    }
    return $sections;
}

function prepareIndFinalsV2($data) {
    $sections = prepareIndFinals($data);
    if (empty($data['sections'])) {
        return $sections;   // no finals in this competition
    }
    $i = 0;
    foreach ($data['sections'] as $key => $value) {
        $j = 0;
        foreach ($value['items'] as $ikey => $ivalue) {
            $sections[$i]['f_results']['f_items'][$j] += array(
                'clubIanseoId' => $ivalue['countryId'],
                'qualRank'     => $ivalue['qualRank'],
                'qualScore'    => $ivalue['qualScore'],
                'irm'          => $ivalue['irm'],
                'irmText'      => $ivalue['irmText'],
            );
            foreach ($ivalue['finals'] as $fkey => $fvalue) {
                $sections[$i]['f_results']['f_items'][$j]['finals'][$fkey] += array(
                    'setPoints'      => $fvalue['setPoints'],
                    'arrowstring'    => sweDecodedArrowstringOrEmpty($fvalue['arrowstring']),
                    'irm'            => $fvalue['irm'],
                    'notes'          => $fvalue['notes'],
                    'oppSetPoints'   => $fvalue['oppSetPoints'],
                    'oppArrowstring' => sweDecodedArrowstringOrEmpty($fvalue['oppArrowstring']),
                    'oppIrm'         => $fvalue['oppIrm'],
                );
            }
            // Phase-keyed maps must stay JSON objects even when the phases are 0..n-1 (v1 emits a list then).
            $sections[$i]['f_results']['f_items'][$j]['finals'] = (object)$sections[$i]['f_results']['f_items'][$j]['finals'];
            $j++;
        }
        $sections[$i]['f_results']['f_meta'] += array(
            'event'     => $value['meta']['event'],
            'matchMode' => $value['meta']['matchMode'],
        );
        $sections[$i]['f_results']['f_meta']['finals'] = (object)$sections[$i]['f_results']['f_meta']['finals'];
        $i++;
    }
    return $sections;
}

/**
 * Team qualification. Source: Obj_Rank_AbsTeam — the team events (tq_class = event code, e.g. "LSR").
 * Each team item carries its component athletes with their own qualification totals. The team key
 * "<CoId>_<subteam>" is the same one teamFinal uses, so ARS merges qualification and finals per team.
 */
function prepareTeamQualification($eventData) {
    $sections = array();
    if (empty($eventData['sections'])) {
        return $sections;
    }
    foreach ($eventData['sections'] as $key => $value) {
        $items = array();
        foreach ($value['items'] as $ivalue) {
            $athletes = array();
            foreach ($ivalue['athletes'] as $a) {
                $athletes[] = array(
                    'id'         => $a['id'],
                    'bib'        => $a['bib'],
                    'familyname' => $a['familyname'],
                    'givenname'  => $a['givenname'],
                    'gender'     => $a['gender'],
                    'div'        => $a['div'],
                    'class'      => $a['class'],
                    'ageclass'   => $a['ageclass'],
                    'subclass'   => $a['subclass'],
                    'quscore'    => $a['quscore'],
                    'qugold'     => $a['qugolds'],
                    'quxnine'    => $a['quxnine'],
                    // The ranking round this archer shot FOR THIS TEAM, arrow by arrow. IANSEO
                    // concatenates the eight distances into one string (Obj_Rank_AbsTeam's
                    // DetailedArrows), so it is sent as one round rather than split per distance —
                    // the distance boundaries are not in the source.
                    //
                    // It has to come from here and not be inferred from the archer's individual
                    // result: an archer can enter the team event with one qualification and their
                    // individual event with another, and for a team-only competition there is no
                    // individual qualification at all.
                    'arrowstring'  => sweDecodeArrowstring(isset($a['arrowString']) ? $a['arrowString'] : ''),
                    'arrowletters' => sweRawArrowletters(isset($a['arrowString']) ? $a['arrowString'] : ''),
                );
            }
            // AbsTeam gives rank='' and score=<IRM text> for a team that did not rank; send the IRM
            // text as rank (ARS stores it in rank_text) and the numeric total as score.
            $rank = $ivalue['rank'] !== '' ? $ivalue['rank'] : $ivalue['irmText'];
            $items[] = array(
                'id'           => $ivalue['id'] . '_' . $ivalue['subteam'],
                'clubIanseoId' => $ivalue['id'],
                'clubId'       => $ivalue['countryCode'],
                'clubName'     => $ivalue['countryName'],
                'subteam'      => $ivalue['subteam'],
                'rank'         => $rank,
                'score'        => $ivalue['completeScore'],
                'gold'         => $ivalue['gold'],
                'xnine'        => $ivalue['xnine'],
                'hits'         => $ivalue['hits'],
                'tiebreak'     => $ivalue['tiebreak'],
                'shootOff'     => $ivalue['so'],
                'irm'          => $ivalue['irm'],
                'irmText'      => $ivalue['irmText'],
                'athletes'     => $athletes,
            );
        }
        $sections[] = array('tq_class' => $key, 'tq_results' => array(
            'tq_meta' => array(
                'descr'      => $value['meta']['descr'],
                'event'      => $value['meta']['event'],
                'firstPhase' => $value['meta']['firstPhase'],
                'maxPersons' => $value['meta']['maxPersons'],
                'numdist'    => $value['meta']['numDist'],
                'maxArrows'  => $value['meta']['maxArrows'],
                'score'      => $value['meta']['fields']['score'],
                'gold'       => $value['meta']['fields']['gold'],
                'xnine'      => $value['meta']['fields']['xnine'],
                'hits'       => $value['meta']['fields']['hits'],
            ),
            'tq_items' => $items
        ));
    }
    return $sections;
}

/**
 * Team finals. Source: Obj_Rank_FinalTeam.
 *
 * Items in the source are keyed "<CoId>_<subteam>" and the match rows refer to the
 * opponent by the same key, so 'oppTeamId' below always matches another item's 'id'.
 */
function prepareTeamFinals($data) {
    $sections = array();
    if (empty($data['sections'])) {
        return $sections;   // no finals in this competition
    }
    foreach ($data['sections'] as $key => $value) {
        $items = array();
        foreach ($value['items'] as $ikey => $ivalue) {
            $athletes = array();
            foreach ($ivalue['athletes'] as $a) {
                $athletes[] = array(
                    'id'         => $a['id'],
                    'bib'        => $a['bib'],
                    'familyname' => $a['familyname'],
                    'givenname'  => $a['givenname'],
                    'gender'     => $a['gender'],
                );
            }
            $matches = array();
            foreach ($ivalue['finals'] as $phase => $m) {
                $opp = isset($value['items'][$m['oppAthlete']]) ? $value['items'][$m['oppAthlete']] : null;
                $matches[$phase] = array(
                    'teamId'         => $ikey,
                    'score'          => $m['score'],
                    'setScore'       => $m['setScore'],
                    'setPoints'      => $m['setPoints'],
                    'tie'            => $m['tie'],
                    'tiebreak'       => $m['tiebreak'],
                    'arrowstring'    => sweDecodedArrowstringOrEmpty($m['arrowstring']),
                    'irm'            => $m['irm'],
                    'notes'          => $m['notes'],
                    'oppTeamId'      => $m['oppAthlete'],
                    'oppClubId'      => $opp ? $opp['countryCode'] : null,
                    'oppClubName'    => $opp ? $opp['countryName'] : null,
                    'oppSubteam'     => $opp ? $opp['subteam'] : null,
                    'oppScore'       => $m['oppScore'],
                    'oppSetScore'    => $m['oppSetScore'],
                    'oppSetPoints'   => $m['oppSetPoints'],
                    'oppTie'         => $m['oppTie'],
                    'oppTiebreak'    => $m['oppTiebreak'],
                    'oppArrowstring' => sweDecodedArrowstringOrEmpty($m['oppArrowstring']),
                    'oppIrm'         => $m['oppIrm'],
                );
            }
            $items[] = array(
                'id'           => $ikey,
                'clubIanseoId' => $ivalue['id'],
                'clubId'       => $ivalue['countryCode'],
                'clubName'     => $ivalue['countryName'],
                'subteam'      => $ivalue['subteam'],
                'qualRank'     => $ivalue['qualRank'],
                'qualScore'    => $ivalue['qualScore'],
                'rank'         => $ivalue['rank'],
                'irm'          => $ivalue['irm'],
                'irmText'      => $ivalue['irmText'],
                'athletes'     => $athletes,
                'finals'       => (object)$matches,   // keyed by phase: cast so a 0..n-1 key set is not emitted as a JSON list
            );
        }
        unset($value['meta']['fields']['finals']['fields']);
        $sections[] = array('tf_class' => $key, 'tf_results' => array(
            'tf_meta' => array(
                'descr'         => $value['meta']['descr'],
                'event'         => $value['meta']['event'],
                'firstPhase'    => $value['meta']['firstPhase'],
                'matchMode'     => $value['meta']['matchMode'],
                'maxTeamPerson' => $value['meta']['maxTeamPerson'],
                'mixedTeam'     => $value['meta']['mixedTeam'],
                'finals'        => (object)$value['meta']['fields']['finals'],
            ),
            'tf_items' => $items
        ));
    }
    return $sections;
}

/* =====================================================================
 *  Run-Archery (v2 only). Source: Obj_Rank_Run.
 *
 *  IANSEO returns sections[0] = individual events, sections[1] = team events,
 *  each keyed by event code and split into phases:
 *      0 = qualification, 2 = semifinal, 1 = final
 *  Times arrive pre-formatted from IANSEO as "[H:]M:SS.mmm" with leading
 *  zeros stripped (e.g. "12:34.567", "5:03.100"); penalties empty when zero.
 * ===================================================================== */

function getRunArchery() {
    $rank = Obj_RankFactory::create('Run', array());
    $rank->read();
    return $rank->getData();
}

function prepareRunArcheryLaps($laps) {
    $out = array();
    foreach ($laps as $lapNo => $l) {
        $lap = array(
            'lap'         => $l['lap'],
            'time'        => $l['time'],
            'shots'       => $l['shots'],
            'hits'        => $l['hits'],
            'loopsToDo'   => $l['loopsToDo'],
            'loopsDone'   => $l['loopsDone'],
            'arrowPenalty'=> $l['ArrPenalty'],
            'loopPenalty' => $l['LoopPenalty'],
        );
        // Team laps carry the archer who ran/shot that lap.
        if (isset($l['enCode'])) {
            $lap['archerBib']        = $l['enCode'];
            $lap['archerFamilyname'] = $l['famName'];
            $lap['archerGivenname']  = $l['givName'];
        }
        $out[] = $lap;
    }
    return $out;
}

function prepareRunArcheryMeta($meta) {
    return array(
        'descr'               => $meta['descr'],
        'event'               => $meta['event'],
        'lapDistance'         => $meta['lapDistance'],
        'lapNum'              => $meta['lapNum'],
        'arrowsAvailable'     => $meta['arrowsAvailable'],
        'arrowsSpare'         => isset($meta['arrowsSpare']) ? $meta['arrowsSpare'] : null,
        'targets'             => $meta['targets'],
        'penaltyLoopDistance' => $meta['penaltyLoopDistance'],
        'qualifiedSemifinals' => $meta['qualifiedSemifinals'],
        'qualifiedFinals'     => $meta['qualifiedFinals'],
        'qualEnded'           => $meta['qualEnded'],
        'semiEnded'           => $meta['semiEnded'],
        'finalEnded'          => $meta['finalEnded'],
    );
}

function prepareRunArcheryPhases($phases, $isTeam) {
    $out = array();
    foreach ($phases as $phaseNo => $p) {
        $items = array();
        foreach ($p['items'] as $ikey => $it) {
            $row = array(
                'rank'         => $it['rank'],
                'bib'          => $it['bib'],
                'finalTime'    => $it['FinalTime'],
                'runningTime'  => $it['RunningTime'],
                'penaltyTime'  => $it['PenaltyTime'],
                'arrowPenalty' => $it['ArrowShort'],
                'loopPenalty'  => $it['LoopShort'],
                'adjustedTime' => $it['AdjustedTime'],
                'laps'         => prepareRunArcheryLaps($it['laps']),
            );
            if ($isTeam) {
                $row = array(
                    'id'           => $it['id'] . '_' . $it['subteam'],   // same "<CoId>_<subteam>" key as teamQualification/teamFinal
                    'clubIanseoId' => $it['id'],
                    'clubId'       => $it['countryCode'],
                    'clubName'     => $it['countryName'],
                    'subteam'      => $it['subteam'],
                ) + $row;
            } else {
                $row = array(
                    'id'           => $it['id'],
                    'entryCode'    => $it['encode'],      // IANSEO entry code = license number (v1 "bib")
                    'familyname'   => $it['familyname'],
                    'givenname'    => $it['givenname'],
                    'gender'       => $it['gender'],
                    'div'          => $it['div'],
                    'class'        => $it['class'],
                    'ageclass'     => $it['ageclass'],
                    'subclass'     => $it['subclass'],
                    'clubIanseoId' => $it['countryId'],
                    'clubId'       => $it['countryCode'],
                    'clubName'     => $it['countryName'],
                    'irm'          => $it['irm'],
                    'pool'         => $it['Pool'],
                    'qualified'    => $it['Qualified'],
                ) + $row;
            }
            $items[] = $row;
        }
        $out[] = array(
            'phase'     => $phaseNo,
            'phaseName' => $p['name'],
            'pools'     => (object)(isset($p['Pools']) ? $p['Pools'] : array()),
            'items'     => $items,
        );
    }
    return $out;
}

/**
 * @return array {individual: [...], team: [...]}
 */
function prepareRunArchery($data) {
    $individual = array();
    if (!empty($data['sections'][0])) {
        foreach ($data['sections'][0] as $evCode => $ev) {
            $individual[] = array('ra_class' => $evCode, 'ra_results' => array(
                'ra_meta'   => prepareRunArcheryMeta($ev['meta']),
                'ra_phases' => prepareRunArcheryPhases($ev['phases'], false),
            ));
        }
    }
    $team = array();
    if (!empty($data['sections'][1])) {
        foreach ($data['sections'][1] as $evCode => $ev) {
            // Component athletes per team, keyed "id-<CoId>-<subteam>" in the source.
            $members = array();
            foreach ($ev['teams'] as $tkey => $athletes) {
                $parts = explode('-', $tkey);   // ["id", CoId, subteam]
                $teamId = $parts[1] . '_' . $parts[2];
                foreach ($athletes as $a) {
                    $members[$teamId][] = array(
                        'id'         => $a['id'],
                        'bib'        => $a['bib'],
                        'familyname' => $a['familyname'],
                        'givenname'  => $a['givenname'],
                        'gender'     => $a['gender'],
                        'div'        => $a['div'],
                        'class'      => $a['class'],
                        'ageclass'   => $a['ageclass'],
                        'subclass'   => $a['subclass'],
                    );
                }
            }
            $phases = prepareRunArcheryPhases($ev['phases'], true);
            foreach ($phases as &$ph) {
                foreach ($ph['items'] as &$it) {
                    $it['athletes'] = isset($members[$it['id']]) ? $members[$it['id']] : array();
                }
                unset($it);
            }
            unset($ph);
            $team[] = array('ra_class' => $evCode, 'ra_results' => array(
                'ra_meta'   => prepareRunArcheryMeta($ev['meta']),
                'ra_phases' => $phases,
            ));
        }
    }
    return array('individual' => $individual, 'team' => $team);
}

/**
 * Assemble the complete v2 document.
 *
 * Every block is always present: IANSEO knows which events the competition has, so the operator does
 * not choose. A block with no events is an empty array, which tells ARS the competition has none —
 * that is what lets ARS replace the whole result set instead of leaving rows from an earlier import.
 *
 * @param array $auth  {competition, password} as entered by the operator
 * @return array       the complete document, never null
 */
function buildPayloadV2($auth, $moduleVersion) {
    $indFinals = getIndFinals();
    $teamFinals = getTeamFinals();

    return array(
        'apiVersion' => 2,
        'module'     => array(
            'name'          => 'SweFunctions',
            'version'       => $moduleVersion,
            'ianseoVersion' => defined('ProgramVersion') ? ProgramVersion : null,
        ),
        'sentAt'     => date('c'),
        'tournament' => getTournamentInfo(),
        'auth'       => $auth,

        // !empty() rather than count(): a competition with no finals at all - which is how Norway and
        // Iceland run most of theirs - can leave a rank object with nothing to count, and count(null)
        // is a fatal on PHP 8, which would replace the JSON answer with a blank page.
        'qualification'     => prepareIndQualificationV2(getIndQualification()),
        'final'             => !empty($indFinals['sections']) ? prepareIndFinalsV2($indFinals) : array(),
        'teamQualification' => prepareTeamQualification(getTeamEventQualification()),
        'teamFinal'         => !empty($teamFinals['sections']) ? prepareTeamFinals($teamFinals) : array(),
        'runArchery'        => prepareRunArchery(getRunArchery()),
    );
}

/* =====================================================================
 *  Transport
 * ===================================================================== */

/**
 * POST a JSON body and return an answer the UI can always render.
 *
 * The return value is ALWAYS ARS's envelope - {status, message, ...} with 'message' a string. Anything
 * else the far end produced is wrapped into that shape, because the page prints data['message'] straight
 * into the status box: a body without one (an ASP.NET validation document, a proxy's HTML error page, an
 * empty 200) showed the operator the single word "undefined" and nothing about what went wrong.
 *
 * On a transport failure (DNS, refused, timeout) nothing answered at all, and the envelope is synthesised
 * here rather than returning false and letting the browser choke on empty output.
 */
function sendInformation($url, $data) {
    $hand = curl_init($url);
    curl_setopt($hand, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($hand, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($hand, CURLOPT_POSTFIELDS, $data);
    curl_setopt($hand, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($hand, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($hand, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($hand, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($hand, CURLOPT_TIMEOUT, 300);
    curl_setopt($hand, CURLOPT_HTTPHEADER, array(
        "Content-type: application/json",
        "Accept: application/json",
        "Content-Length: " . strlen($data),
    ));
    $result = curl_exec($hand);
    $errno  = curl_errno($hand);
    $error  = curl_error($hand);
    $http   = curl_getinfo($hand, CURLINFO_HTTP_CODE);
    curl_close($hand);

    if ($result === false) {
        error_log("SweFunctions: cURL error ($errno) posting to $url: $error");
        return json_encode(array(
            'status'  => 'Failed',
            'message' => "Connection error ($errno): $error",
        ));
    }
    if ($http >= 400) {
        error_log("SweFunctions: HTTP $http from $url. Body: " . substr($result, 0, 2000));
    }

    $decoded = json_decode($result, true);

    /*
     * ARS's own envelope has a STRING status ('Ready' / 'Failed'). An ASP.NET ProblemDetails has an
     * integer one (the HTTP code), which the old isset() test happily accepted as ours and passed
     * through to a UI that then found no 'message'. Pass through only what is really ours, and make
     * sure it carries a message even when the server left it out.
     */
    if (is_array($decoded) and isset($decoded['status']) and is_string($decoded['status'])) {
        if (!isset($decoded['message']) or !is_string($decoded['message'])) {
            $decoded['message'] = ($decoded['status'] === 'Ready') ? '' : "Server reported: {$decoded['status']}";
        }
        return json_encode($decoded, JSON_UNESCAPED_UNICODE);
    }

    return json_encode(sweServerErrorEnvelope($http, $result), JSON_UNESCAPED_UNICODE);
}

/**
 * Turn whatever the far end said into {status, message, detail}.
 *
 * The case worth naming is ASP.NET's validation document, which is what ARS answers when a block in the
 * payload will not bind to its model: {"title":"One or more validation errors occurred.","status":400,
 * "errors":{"final":["The JSON value could not be converted to ..."]}}. The field name and the reason are
 * the only things that make such a rejection diagnosable, so they are lifted into 'detail' - which the
 * page already prints under the message - rather than being thrown away with the rest of the body.
 */
function sweServerErrorEnvelope($http, $body) {
    $message = ($http >= 400) ? "Server returned HTTP $http" : "Unexpected answer from the server (HTTP $http)";
    $parts   = array();

    $decoded = json_decode((string)$body, true);
    if (is_array($decoded)) {
        if (!empty($decoded['title']) and is_string($decoded['title'])) {
            $message .= ': ' . $decoded['title'];
        }
        if (!empty($decoded['detail']) and is_string($decoded['detail'])) {
            $parts[] = $decoded['detail'];
        }
        if (!empty($decoded['exception']) and is_string($decoded['exception'])) {
            $parts[] = $decoded['exception'];
        }
        if (!empty($decoded['errors']) and is_array($decoded['errors'])) {
            foreach ($decoded['errors'] as $field => $msgs) {
                $parts[] = $field . ': ' . (is_array($msgs) ? implode(' ', $msgs) : (string)$msgs);
            }
        }
    }

    $detail = trim(implode(' | ', $parts));
    if ($detail === '') {
        // Not JSON at all, or JSON we know nothing about: the first part of it is still the best clue.
        $detail = trim(preg_replace('/\s+/', ' ', strip_tags(preg_replace('/</', ' <', (string)$body))));
    }

    return array(
        'status'  => 'Failed',
        'message' => $message,
        'detail'  => substr($detail, 0, 500),
    );
}
