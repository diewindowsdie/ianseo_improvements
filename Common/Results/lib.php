<?php

const RESULTS_PUBLICATION_MODULE_NAME = "ResultsPublication";
const COMPETITION_HIDDEN_PARAM_NAME = "isCompetitionHidden";
const COMPETITION_EVENT_HIDDEN_PARAM_NAME_PREFIX = "eventHidden_";
const SHOW_FINAL_SESSIONS_IN_SCHEDULE_PARAM_NAME = "showSessionsInSchedule";
const PROTOCOL_PDF_PARAM_NAME = "ProtocolPdf";
const PUBLISH_SCORECARDS_PARAM_NAME = "PublishScorecards";

const PROTOCOL_MODULE = "Protocol";
const STAT_HEADER_PARAM_NAME_PREFIX = "StatHeader";

const Header1ParameterName = STAT_HEADER_PARAM_NAME_PREFIX . "1";
const Header2ParameterName = STAT_HEADER_PARAM_NAME_PREFIX . "2";
const Header3ParameterName = STAT_HEADER_PARAM_NAME_PREFIX . "3";

const CHECKED_COUNTRY_PARAM_PREFIX = "CheckedCountry";
const Checked1ParameterName = CHECKED_COUNTRY_PARAM_PREFIX . "1";
const Checked2ParameterName = CHECKED_COUNTRY_PARAM_PREFIX . "2";
const Checked3ParameterName = CHECKED_COUNTRY_PARAM_PREFIX . "3";

function exitNotFound()
{
    http_response_code(404);
    exit('Not Found');
}

function getRegionStatisticsHeaderParameter($competition, $index) {
    return getModuleParameter(PROTOCOL_MODULE, STAT_HEADER_PARAM_NAME_PREFIX . $index, getTextAtCompetitionLanguage('Country', $competition, 'Common'), $competition->ToId);
}

function getPdfPrefix() {
    global $CFG;
    
    return (isset($CFG->PDF_REFIX) ? $CFG->PDF_REFIX : ($CFG->ROOT_DIR . PDFPrefix));
}

function isAnyAthleteStartedFinals($competition, $event = null): bool
{
    $query = "select count(1) StartedFinals from Finals
                where FinArrowstring != ''";
    if ($event != null) {
        $query .= " and FinEvent = " . StrSafe_DB($event->EvCode);
    }
    $query .= " and FinTournament = " . StrSafe_DB($competition->ToId);
    $resultSet = safe_r_SQL($query);
    $data = safe_fetch($resultSet);

    return $data->StartedFinals > 0;
}

function isAnyTeamStartedFinals($competition, $event = null): bool
{
    $query = "select count(1) StartedFinals from TeamFinals
                where TfArrowstring != ''";
    if ($event != null) {
        $query .= " and TfEvent = " . StrSafe_DB($event->EvCode);
    }
    $query .= " and TfTournament = " . StrSafe_DB($competition->ToId);
    $resultSet = safe_r_SQL($query);
    $data = safe_fetch($resultSet);

    return $data->StartedFinals > 0;
}

function isIndividualBracketsBuilt($competition, $event = null): bool
{
    $query = "select count(1) InBrackets from Finals
                where FinAthlete != 0";
    if ($event != null) {
        $query .= " and FinEvent = " . StrSafe_DB($event->EvCode);
    }
    $query .= " and FinTournament = " . StrSafe_DB($competition->ToId);
    $resultSet = safe_r_SQL($query);
    $data = safe_fetch($resultSet);

    return $data->InBrackets > 0;
}

function isTeamBracketsBuilt($competition, $event = null): bool
{
    $query = "select count(1) InBrackets from TeamFinals
                where TfTeam != 0";
    if ($event != null) {
        $query .= " and TfEvent = " . StrSafe_DB($event->EvCode);
    }
    $query .= " and TfTournament = " . StrSafe_DB($competition->ToId);
    $resultSet = safe_r_SQL($query);
    $data = safe_fetch($resultSet);

    return $data->InBrackets > 0;
}

function isAllWinnersAreKnown($competition, $events): bool
{
    $result = true;
    foreach ($events as $event) {
        $result &= ($event->EvTeamEvent === "0" ? isIndividualWinnerIsKnown($competition, $event) : isTeamWinnerIsKnown($competition, $event));
    }

    return $result;
}

function isIndividualWinnerIsKnown($competition, $event = null): bool
{
    //если мы хотим проверить для соревнования в целом - поищем такую группу, у которой есть финалы и определен их победитель
    if ($event === null) {
        $query = "select count(1) WinnerKnown from Finals f
                    left join Events e on f.FinEvent = e.EvCode
                    where FinWinLose = 1
                    and e.EvFinalFirstPhase != '0'
                    and FinMatchNo in (0, 1)
                    and FinTournament = " . StrSafe_DB($competition->ToId);
        $resultSet = safe_r_SQL($query);
        $data = safe_fetch($resultSet);

        if ($data->WinnerKnown > 0) {
            return true;
        }

        //если мы не нашли такой группы - поищем такую группу, у которой финалов нет
        $query = "select count(1) WinnerKnown from Finals f
                    left join Events e on f.FinEvent = e.EvCode
                    where e.EvFinalFirstPhase = '0'
                    and FinTournament = " . StrSafe_DB($competition->ToId);
        $resultSet = safe_r_SQL($query);
        $data = safe_fetch($resultSet);

        return $data->WinnerKnown > 0;
    }

    //если же мы проверяем, известен ли победитель для конкретной группы - если есть финалы, проверяем известен ли победитель в финалах, иначе считаем что результаты доступны
    if ($event->EvFinalFirstPhase !== '0') {
        $query = "select count(1) WinnerKnown from Finals f
                    where FinWinLose = 1
                    and FinMatchNo in (0, 1)
                    and FinEvent = " . StrSafe_DB($event->EvCode) . "
                    and FinTournament = " . StrSafe_DB($competition->ToId);
        $resultSet = safe_r_SQL($query);
        $data = safe_fetch($resultSet);

        return $data->WinnerKnown > 0;
    }

    return true;
}

function isTeamWinnerIsKnown($competition, $event = null): bool
{
    //если мы хотим проверить для соревнования в целом - поищем такую группу, у которой есть финалы и определен их победитель
    if ($event === null) {
        $query = "select count(1) WinnerKnown from TeamFinals f
                    left join Events e on f.FinEvent = e.EvCode
                    where TfWinLose = 1
                    and e.EvFinalFirstPhase != '0'
                    and TfMatchNo in (0, 1)
                    and TfTournament = " . StrSafe_DB($competition->ToId);
        $resultSet = safe_r_SQL($query);
        $data = safe_fetch($resultSet);

        if ($data->WinnerKnown > 0) {
            return true;
        }

        //если мы не нашли такой группы - поищем такую группу, у которой финалов нет
        $query = "select count(1) WinnerKnown from TeamFinals tf
                    left join Events e on tf.TfEvent = e.EvCode
                    where e.EvFinalFirstPhase = '0'
                    and TfTournament = " . StrSafe_DB($competition->ToId);
        $resultSet = safe_r_SQL($query);
        $data = safe_fetch($resultSet);

        return $data->WinnerKnown > 0;
    }

    //если же мы проверяем, известен ли победитель для конкретной группы - если есть финалы, проверяем известен ли победитель в финалах, иначе считаем что результаты доступны
    if ($event->EvFinalFirstPhase !== '0') {
        $query = "select count(1) WinnerKnown from TeamFinals tf
                    where TfWinLose = 1
                    and TfMatchNo in (0, 1)
                    and TfEvent = " . StrSafe_DB($event->EvCode) . "
                    and TfTournament = " . StrSafe_DB($competition->ToId);
        $resultSet = safe_r_SQL($query);
        $data = safe_fetch($resultSet);

        return $data->WinnerKnown > 0;
    }

    return true;
}

function isAthleteOrTeamInBronzeOrGoldFinals($competition): bool
{
    $query = "select count(1) Finalists from (
                select FinAthlete from Finals
                    where FinMatchNo in (0, 1)
                    and FinAthlete != '0'
                    and FinTournament = " . StrSafe_DB($competition->ToId) . "
                union all    
                select TfTeam from TeamFinals
                    where TfMatchNo in (0, 1)
                    and TfTeam != '0'
                    and TfTournament = " . StrSafe_DB($competition->ToId) . "
            ) t";

    $resultSet = safe_r_SQL($query);
    $data = safe_fetch($resultSet);

    $showFinalSessions = getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, SHOW_FINAL_SESSIONS_IN_SCHEDULE_PARAM_NAME, array(), $competition->ToId);
    return $data->Finalists > 0 && count($showFinalSessions) > 0;
}

function isAnyAthletesInCompetition($competition): bool
{
    $query = "select count(1) as Athletes
                from Entries e
                left join Divisions d on e.EnDivision = d.DivId and e.EnTournament = d.DivTournament
                left join Classes cl on e.EnClass = cl.ClId and e.EnTournament = cl.ClTournament
                where e.EnAthlete = '1' and d.DivAthlete = '1' and cl.ClAthlete = '1'
                and e.EnTournament = " . StrSafe_DB($competition->ToId);
    $resultSet = safe_r_SQL($query);
    $data = safe_fetch($resultSet);

    return $data->Athletes > 0;
}

function isAnyJudgeInCompetition($competition): bool
{
    $query = "select count(1) as Judges
                from TournamentInvolved
                where TiTournament = " . StrSafe_DB($competition->ToId);
    $resultSet = safe_r_SQL($query);
    $data = safe_fetch($resultSet);

    return $data->Judges > 0;
}

function isAthleteTargetsAssigned($competition, $sessionId = null): bool
{
    $query = "select count(1) as HasTargets
                from Entries e
                left join Qualifications q on e.EnId = q.QuId
                where q.QuTarget != ''
                and q.QuLetter != ''";
    if ($sessionId != null) {
        $query .= " and q.QuSession = " . StrSafe_DB($sessionId);
    }
    $query .= " and e.EnTournament = ". StrSafe_DB($competition->ToId);
    $resultSet = safe_r_SQL($query);
    $data = safe_fetch($resultSet);

    return $data->HasTargets > 0;
}

function isQualificationsDataPresentForGroup($competition, $event): bool
{
    //нас интересует наличие пробоин или введённого результата
    $query = "select count(1) as StartedQuals
                from EventClass ec
                inner join Entries e
                    on e.EnTournament = ec.EcTournament and ec.EcDivision = e.EnDivision and ec.EcClass = e.EnClass
                left join Qualifications q on e.EnId = q.QuId
                where (q.QuD1Arrowstring != '' || q.QuD1Score > 0)
                and ec.EcTeamEvent = " . StrSafe_DB($event->EvTeamEvent) . "
                and ec.EcTournament = " . StrSafe_DB($competition->ToId) . "
                and ec.EcCode = " . StrSafe_DB($event->EvCode);
    $resultSet = safe_r_SQL($query);
    $data = safe_fetch($resultSet);

    return $data->StartedQuals > 0;
}


function isQualificationsDataPresentForSession($competition, $sessionId): bool
{
    //тут нужно проверять именно наличие пробоин, потому что без них карточки записи будут пустые
    $query = "select count(1) as StartedQuals
                from Entries e
                left join Qualifications q on e.EnId = q.QuId
                left join Session s on s.SesTournament = e.EnTournament and s.SesOrder = q.QuSession
                where q.QuD1Arrowstring != ''
                and e.EnTournament = " . StrSafe_DB($competition->ToId) . "
                and s.SesOrder = " . StrSafe_DB($sessionId);
    $resultSet = safe_r_SQL($query);
    $data = safe_fetch($resultSet);

    return $data->StartedQuals > 0;
}

function formatTournamentDateAtCompetitionLanguage($competition)
{
    $dateFormat = $competition->ToPrintLang
        ? get_text('DateFmt', false, false, $competition->ToPrintLang)
        : get_text('DateFmt');

    $DateFrom = $competition->DtFrom;
    $DateTo = $competition->DtTo;

    if (is_numeric($DateFrom)) {
        if ($DateFrom == $DateTo)            //Inizio e Fine Coincidono
        {
            $TmpData = date($dateFormat, $DateFrom);
        } else {
            if ($competition->ToPrintLang) {
                $TmpData = get_text('DateFmtMoreDays', 'Common', array(date($dateFormat, $DateFrom), date($dateFormat, $DateTo)), false, false, $competition->ToPrintLang);
            } else {
                $TmpData = get_text('DateFmtMoreDays', 'Common', array(date($dateFormat, $DateFrom), date($dateFormat, $DateTo)));
            }
        }
    } else {
        if ($DateFrom == $DateTo)            //Inizio e Fine Coincidono
        {
            $TmpData = $DateFrom;
        } else {
            if ($competition->ToPrintLang) {
                $TmpData = get_text('DateFmtMoreDays', 'Common', array($DateFrom, $DateTo), false, false, $competition->ToPrintLang);
            } else {
                $TmpData = get_text('DateFmtMoreDays', 'Common', array($DateFrom, $DateTo));
            }
        }
    }
    return $TmpData;
}

function findCompetitionByCode($code)
{
    global $CFG;

    $codeSafe = preg_replace('/[^a-z0-9_.-]+/sim', '', $code);
    $competitionQuery = "SELECT ToId,ToPrintLang,ToComDescr,ToName,ToWhere,ToVenue,DATE_FORMAT(ToWhenFrom,'" . get_text('DateFmtDB') . "') AS DtFrom, "
        . "DATE_FORMAT(ToWhenTo,'" . get_text('DateFmtDB') . "') AS DtTo "
        . "FROM Tournament where ToCode = " . StrSafe_DB($code);
    $competitionResultSet = safe_r_SQL($competitionQuery);
    while ($competition = safe_fetch($competitionResultSet)) {
        if (getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, COMPETITION_HIDDEN_PARAM_NAME, "0", $competition->ToId) === "1") {
            exitNotFound();
        }

        if (file_exists($CFG->DOCUMENT_PATH . 'TV/Photos/' . $codeSafe . '-ToLeft.jpg')) $competition->LeftImageUrl = $CFG->ROOT_DIR . 'TV/Photos/' . $codeSafe . '-ToLeft.jpg';
        if (file_exists($CFG->DOCUMENT_PATH . 'TV/Photos/' . $codeSafe . '-ToRight.jpg')) $competition->RightImageUrl = $CFG->ROOT_DIR . 'TV/Photos/' . $codeSafe . '-ToRight.jpg';
        if (file_exists($CFG->DOCUMENT_PATH . 'TV/Photos/' . $codeSafe . '-ToBottom.jpg')) $competition->BottomImageUrl = $CFG->ROOT_DIR . 'TV/Photos/' . $codeSafe . '-ToBottom.jpg';

        return $competition;
    }

    exitNotFound();
}

function getTextAtCompetitionLanguage($key, $competition, $module = null, $argument = null)
{
    if ($competition->ToPrintLang) {
        return get_text($key, $module, $argument, false, false, $competition->ToPrintLang);
    } else {
        return get_text($key, $module, $argument);
    }
}

function getCompetitionEvents($competitionId, $showHidden = false): array
{
    $events = array();
    $competitionEventsQuery = "SELECT EvCode, EvEventName, EvTeamEvent, EvFinalFirstPhase FROM Events WHERE EvTournament=" . StrSafe_DB($competitionId) . " and EvCodeParent='' and EvMedals = '1' ORDER BY EvTeamEvent, EvProgr";
    $competitionEventsResultSet = safe_r_sql($competitionEventsQuery);
    while ($competitionEvent = safe_fetch($competitionEventsResultSet)) {
        if (!$showHidden && getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, COMPETITION_EVENT_HIDDEN_PARAM_NAME_PREFIX . $competitionEvent->EvCode . '_' . $competitionEvent->EvTeamEvent, "0", $competitionId) === "1") {
            //пропускаем эвенты, у которых стоит флаг "скрыто"
            continue;
        }

        $events[] = $competitionEvent;
    }

    return $events;
}

function getFinalSessions($competitionId): array
{
    $sessions = array();

    $query = "select SesOrder, SesName from Session where SesType='F' and SesTournament = " . StrSafe_DB($competitionId) . " order by SesOrder asc";
    $resultSet = safe_r_SQL($query);
    while ($session = safe_fetch($resultSet)) {
        $sessions[] = $session;
    }

    return $sessions;
}

function getCompetitionDetailsHtml($competition): string
{
    $events = getCompetitionEvents($competition->ToId);
    $competitionHasTeamEvents = false;
    foreach ($events as $event) {
        if ($event->EvTeamEvent) {
            $competitionHasTeamEvents = true;
            break;
        }
    }

    $qualificationSessions = array();
    $qualificationSessionsQuery = "select SesOrder, SesName from Session where SesType = 'Q' and SesTournament = " . StrSafe_DB($competition->ToId) . " order by SesOrder";
    $qualificationSessionsResultSet = safe_r_SQL($qualificationSessionsQuery);
    while ($qualificationSession = safe_fetch($qualificationSessionsResultSet)) {
        $qualificationSessions[] = $qualificationSession;
    }

    $result = '<span class="competitionResults">';

    $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=s&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('IntSCHED', $competition, 'ODF') . '</a><br /><br />';
    if (isAthleteOrTeamInBronzeOrGoldFinals($competition)) {
        $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=fs&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('FinalScheduleDetailed', $competition, 'Tournament') . '</a><br /><br />';
    }

    //отчеты статистики
    if (isAnyAthletesInCompetition($competition)) {
        $result .= '<i id="toggleStatistics_' . $competition->ToId . '" class="l2 fa-solid fa-caret-right" onclick="toggle(\'toggleStatistics_\', \'statistics_\', ' . $competition->ToId . ')"></i>
                <span class="resultsGroupHeader" onclick="toggle(\'toggleStatistics_\', \'statistics_\', ' . $competition->ToId . ')">' . getTextAtCompetitionLanguage('Statistics', $competition, 'Tournament') . '</span>';
        $result .= '<div class="results display-none" id="statistics_' . $competition->ToId . '">';

        //статистика - регионы/команды
        $first = true;
        if (getModuleParameter(PROTOCOL_MODULE, Checked1ParameterName, "0", $competition->ToId) === "1") {
            $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=r1&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('StatisticsWithComment', $competition, 'Tournament', getRegionStatisticsHeaderParameter($competition, 1)) . '</a>';
            $first = false;
        }
        if (getModuleParameter(PROTOCOL_MODULE, Checked2ParameterName, "0", $competition->ToId) === "1") {
            if (!$first) {
                $result .= "&nbsp;&nbsp;";
            }
            $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=r2&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('StatisticsWithComment', $competition, 'Tournament', getRegionStatisticsHeaderParameter($competition, 2)) . '</a>';
            $first = false;
        }
        if (getModuleParameter(PROTOCOL_MODULE, Checked3ParameterName, "0", $competition->ToId) === "1") {
            if (!$first) {
                $result .= "&nbsp;&nbsp;";
            }
            $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=r3&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('StatisticsWithComment', $competition, 'Tournament', getRegionStatisticsHeaderParameter($competition, 3)) . '</a>';
        }
        $result .= '<br />';
        //Статистика (Квалификация и Финалы)
        $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=sacd&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('StatEvents', $competition, 'Tournament') . '</a><br />';
        //статистика участников по классу и дивизиону
        $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=sacd&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('StatClasses', $competition, 'Tournament') . '</a><br /><br />';

        //список спортсменов по регионам
        $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=sar&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('StartlistCountry', $competition, 'Tournament') . '</a><br /><br />';

        //список спортсменов по категориям
        $result .= '<i id="toggleAthletesByCategory_' . $competition->ToId . '" class="l3 fa-solid fa-caret-right" onclick="toggle(\'toggleAthletesByCategory_\', \'athletesByCategory_\', ' . $competition->ToId . ')"></i>
                <span class="resultsGroupHeader" onclick="toggle(\'toggleAthletesByCategory_\', \'athletesByCategory_\', ' . $competition->ToId . ')">' . getTextAtCompetitionLanguage('StartlistCategory', $competition, 'Tournament') . '</span>';
        $result .= '<div class="results display-none" id="athletesByCategory_' . $competition->ToId . '">';

        $result .= '<a target="_blank" href="' . getPdfPrefix() . 'getPdf.php?report=sac&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('StartlistCategory', $competition, 'Tournament') . ' (' . getTextAtCompetitionLanguage('AllCategories', $competition, 'Common') . ')</a><br />';

        $result .= '<br /><b>' . getTextAtCompetitionLanguage('ByCategory', $competition, 'Tournament') . ':</b><br />';

        foreach ($events as $event) {
            if ($event->EvTeamEvent === "0") {
                $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=sac&id=' . $competition->ToId . '&event=' . $event->EvCode . '">' . htmlspecialchars($event->EvEventName) . '</a></br>';
            }
        }

        $result .= '</div><br />';

        $result .= '</div><br />';
    }

    //квалификация
    if (isAthleteTargetsAssigned($competition)) {
        $result .= '<i id="toggleQualification_' . $competition->ToId . '" class="l2 fa-solid fa-caret-right" onclick="toggle(\'toggleQualification_\', \'qualResults_\', ' . $competition->ToId . ')"></i>
                <span class="resultsGroupHeader" onclick="toggle(\'toggleQualification_\', \'qualResults_\', ' . $competition->ToId . ')">' . getTextAtCompetitionLanguage('QualRound', $competition) . '</span>';
        $result .= '<div class="results display-none" id="qualResults_' . $competition->ToId . '">';

        $targetsAssignedFor = array();
        foreach ($qualificationSessions as $session) {
            if (isAthleteTargetsAssigned($competition, $session->SesOrder)) {
                $targetsAssignedFor[] = $session;
            }
        }

        //если хоть у кого-то есть назначенная мишень - покажем жеребьевку
        if (count($targetsAssignedFor) > 0) {
            $result .= '<i id="toggleQualDraw_' . $competition->ToId . '" class="l3 fa-solid fa-caret-right" onclick="toggle(\'toggleQualDraw_\', \'qualDraw_\', ' . $competition->ToId . ')"></i>
                <span class="resultsGroupHeader" onclick="toggle(\'toggleQualDraw_\', \'qualDraw_\', ' . $competition->ToId . ')">' . getTextAtCompetitionLanguage('DrawResults', $competition, 'Tournament') . '</span>';
            $result .= '<div class="results display-none" id="qualDraw_' . $competition->ToId . '">';

            foreach ($targetsAssignedFor as $session) {
                $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=d&id=' . $competition->ToId . '&session=' . $session->SesOrder . '">' . getTextAtCompetitionLanguage('Session', $competition) . ' ' . $session->SesOrder . ': ' . htmlspecialchars($session->SesName) . '</a></br>';
            }

            $result .= '</div><br /><br />';
        }

        //проверим, что хоть у одной группы есть результаты
        $qualStartedFor = array();
        foreach ($events as $event) {
            if (isQualificationsDataPresentForGroup($competition, $event)) {
                $qualStartedFor[] = $event;
            }
        }
        if (count($qualStartedFor) > 0) {
            $result .= '<a target="_blank" href="' . getPdfPrefix() . 'getPdf.php?report=qI&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('ResultIndAbs', $competition, 'Tournament') . '</a><br />';
            if ($competitionHasTeamEvents) {
                $result .= '<a target="_blank" href="' . getPdfPrefix() . 'getPdf.php?report=qT&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('ResultSqAbs', $competition, 'Tournament') . '</a><br />';
            }

            $result .= '<br /><b>' . getTextAtCompetitionLanguage('ByCategory', $competition, 'Tournament') . ':</b><br />';

            foreach ($qualStartedFor as $event) {
                if ($event->EvTeamEvent === "0") {
                    $result .= '<a target="_blank" class="link-l3" href="' . getPdfPrefix() . 'getPdf.php?report=qI&id=' . $competition->ToId . '&event=' . $event->EvCode . '">' . htmlspecialchars($event->EvEventName) . '</a></br>';
                } else {
                    $result .= '<a target="_blank" class="link-l3" href="' . getPdfPrefix() . 'getPdf.php?report=qT&id=' . $competition->ToId . '&event=' . $event->EvCode . '">' . htmlspecialchars($event->EvEventName) . '</a></br>';
                }
            }

            $result .= "<br />";

            //в квалификации - карточки записи
            if (getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, PUBLISH_SCORECARDS_PARAM_NAME, "1", $_REQUEST["id"]) === "1") {
                //проверим, в каких сменах уже есть карточки записи
                $qualStartedForSession = array();
                foreach ($qualificationSessions as $session) {
                    if (isQualificationsDataPresentForSession($competition, $session->SesOrder)) {
                        $qualStartedForSession[] = $session;
                    }
                }

                if (count($qualStartedForSession) > 0) {
                    $result .= '<i id="toggleQualScorecards_' . $competition->ToId . '" class="l3 fa-solid fa-caret-right" onclick="toggle(\'toggleQualScorecards_\', \'qualScorecards_\', ' . $competition->ToId . ')"></i>
                <span class="resultsGroupHeader" onclick="toggle(\'toggleQualScorecards_\', \'qualScorecards_\', ' . $competition->ToId . ')">' . getTextAtCompetitionLanguage('Scorecards', $competition, 'Tournament') . '</span>';
                    $result .= '<div class="results display-none" id="qualScorecards_' . $competition->ToId . '">';

                    foreach ($qualStartedForSession as $session) {
                        $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=qcI&id=' . $competition->ToId . '&session=' . $session->SesOrder . '">' . getTextAtCompetitionLanguage('Session', $competition) . ' ' . $session->SesOrder . ': ' . htmlspecialchars($session->SesName) . '</a></br>';
                    }

                    $result .= '</div><br />';
                }
            }
        }

        $result .= "</div><br />";
    }

    //финалы
    if (isIndividualBracketsBuilt($competition) || isTeamBracketsBuilt($competition) || isIndividualWinnerIsKnown($competition) || isTeamWinnerIsKnown($competition)) {
        $result .= '<i id="toggleFinals_' . $competition->ToId . '" class="l2 fa-solid fa-caret-right" onclick="toggle(\'toggleFinals_\', \'finals_\', ' . $competition->ToId . ')"></i>
            <span class="resultsGroupHeader" onclick="toggle(\'toggleFinals_\', \'finals_\', ' . $competition->ToId . ')">' . getTextAtCompetitionLanguage('CompetitionResults', $competition) . '</span>';
        $result .= '<div class="results display-none" id="finals_' . $competition->ToId . '">';

        //в финалах - список победителей
        if (isAllWinnersAreKnown($competition, $events)) {
            $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=p&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('Protocol', $competition, 'Common') . '</a><br />';
            $pdfUrl = getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, PROTOCOL_PDF_PARAM_NAME, "", $competition->ToId);
            if (trim($pdfUrl) !== "") {
                $result .= '<a target="_blank" class="link-l4" href="' . trim($pdfUrl) . '">' . getTextAtCompetitionLanguage('ProtocolWithSignatures', $competition, 'Common') . '</a><br />';
            }
            $result .= '<br />';
            $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=w&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('MedalList', $competition, 'Common') . '</a><br />';
            $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=m&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('MedalStanding', $competition, 'Common') . '</a><br /><br />';
        }

        //в финалах - сетки
        if (isIndividualBracketsBuilt($competition) || isTeamBracketsBuilt($competition)) {
            $result .= '<i id="toggleBrackets_' . $competition->ToId . '" class="l3 fa-solid fa-caret-right" onclick="toggle(\'toggleBrackets_\', \'brackets_\', ' . $competition->ToId . ')"></i>
            <span class="resultsGroupHeader" onclick="toggle(\'toggleBrackets_\', \'brackets_\', ' . $competition->ToId . ')">' . getTextAtCompetitionLanguage('Brackets', $competition) . ' </span>';
            $result .= '<div class="results display-none" id="brackets_' . $competition->ToId . '">';
            $result .= '<a target="_blank" href="' . getPdfPrefix() . 'getPdf.php?report=bI&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('VersionBracketsInd', $competition, 'Tournament') . '</a><br />';
            if ($competitionHasTeamEvents) {
                $result .= '<a target="_blank" href="' . getPdfPrefix() . 'getPdf.php?report=bT&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('VersionBracketsTeam', $competition, 'Tournament') . '</a><br />';
            }

            $result .= '<br /><b>' . getTextAtCompetitionLanguage('ByCategory', $competition, 'Tournament') . ':</b><br />';
            foreach ($events as $event) {
                if ($event->EvTeamEvent === "0") {
                    if (isIndividualBracketsBuilt($competition, $event)) {
                        $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=bI&id=' . $competition->ToId . '&event=' . $event->EvCode . '">' . htmlspecialchars($event->EvEventName) . '</a></br>';
                    }
                } else {
                    if (isTeamBracketsBuilt($competition, $event)) {
                        $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=bT&id=' . $competition->ToId . '&event=' . $event->EvCode . '">' . htmlspecialchars($event->EvEventName) . '</a></br>';
                    }
                }
            }

            $result .= '</div><br />';
        }

        //в финалах - лесенки
        if (isIndividualWinnerIsKnown($competition) || isTeamWinnerIsKnown($competition)) {
            $result .= '<i id="toggleRankings_' . $competition->ToId . '" class="l3 fa-solid fa-caret-right" onclick="toggle(\'toggleRankings_\', \'rankings_\', ' . $competition->ToId . ')"></i>
                <span class="resultsGroupHeader" onclick="toggle(\'toggleRankings_\', \'rankings_\', ' . $competition->ToId . ')">' . getTextAtCompetitionLanguage('FinalRankings', $competition, 'Tournament') . '</span>';
            $result .= '<div class="results display-none" id="rankings_' . $competition->ToId . '">';
            $result .= '<a target="_blank" href="' . getPdfPrefix() . 'getPdf.php?report=rI&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('FinalRankInd', $competition, 'Tournament') . '</a><br />';
            if ($competitionHasTeamEvents) {
                $result .= '<a target="_blank" href="' . getPdfPrefix() . 'getPdf.php?report=rT&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('FinalRankTeams', $competition, 'Tournament') . '</a><br />';
            }

            $result .= '<br /><b>' . getTextAtCompetitionLanguage('ByCategory', $competition, 'Tournament') . ':</b><br />';
            foreach ($events as $event) {
                if ($event->EvTeamEvent === "0") {
                    if (isIndividualWinnerIsKnown($competition, $event)) {
                        $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=rI&id=' . $competition->ToId . '&event=' . $event->EvCode . '">' . htmlspecialchars($event->EvEventName) . '</a></br>';
                    }
                } else {
                    if (isTeamWinnerIsKnown($competition, $event)) {
                        $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=rT&id=' . $competition->ToId . '&event=' . $event->EvCode . '">' . htmlspecialchars($event->EvEventName) . '</a></br>';
                    }
                }
            }

            $result .= '</div><br />';
        }

        //карточки записи - финалы
        if (getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, PUBLISH_SCORECARDS_PARAM_NAME, "1", $_REQUEST["id"]) === "1") {
            if (isAnyAthleteStartedFinals($competition) || isAnyTeamStartedFinals($competition)) {
                $result .= '<i id="toggleFinalIndScorecards_' . $competition->ToId . '" class="l3 fa-solid fa-caret-right" onclick="toggle(\'toggleFinalIndScorecards_\', \'finalIndScorecards_\', ' . $competition->ToId . ')"></i>
                <span class="resultsGroupHeader" onclick="toggle(\'toggleFinalIndScorecards_\', \'finalIndScorecards_\', ' . $competition->ToId . ')">' . getTextAtCompetitionLanguage('Scorecards', $competition, 'Tournament') . '</span>';
                $result .= '<div class="results display-none" id="finalIndScorecards_' . $competition->ToId . '">';

                foreach ($events as $event) {
                    if ($event->EvTeamEvent === "0") {
                        if (isAnyAthleteStartedFinals($competition, $event)) {
                            $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=fcI&id=' . $competition->ToId . '&event=' . $event->EvCode . '">' . htmlspecialchars($event->EvEventName) . '</a></br>';
                        }
                    } else {
                        if (isAnyTeamStartedFinals($competition, $event)) {
                            $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=fcT&id=' . $competition->ToId . '&event=' . $event->EvCode . '">' . htmlspecialchars($event->EvEventName) . '</a></br>';
                        }
                    }
                }

                $result .= '</div><br />';
            }
        }

        $result .= "</div><br />";
    }

    //судьи
    if (isAnyJudgeInCompetition($competition)) {
        $result .= "<br />";
        $result .= '<a target="_blank" class="link-l4" href="' . getPdfPrefix() . 'getPdf.php?report=j&id=' . $competition->ToId . '">' . getTextAtCompetitionLanguage('CompetitionOfficials', $competition, 'Tournament') . '</a><br />';
    }

    $result .= "</span>";

    return $result;
}
