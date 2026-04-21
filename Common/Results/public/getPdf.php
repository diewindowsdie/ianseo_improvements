<?php
require_once dirname(__FILE__, 4) . '/config.php';
require_once dirname(__FILE__, 2) . "/lib.php";

function getVisibleEventCodesConsideringTeams($competitionId, $isTeam): array
{
    $events = array();
    foreach(getCompetitionEvents($competitionId) as $event) {
        if ($event->EvTeamEvent === $isTeam) {
            //если просили конкретный эвент - зафильтруем по нему
            if (!isset($_REQUEST["event"]) || $_REQUEST["event"] === $event->EvCode) {
                $events[] = $event->EvCode;
            }
        }
    }

    return $events;
}

//проверим, что передан "код" отчета, который мы знаем и поддерживаем
if (!in_array($_REQUEST["report"], array("qI", "qT", "bI", "bT", "rI", "rT", "qcI", "fcI", "fcT", "j", "d", "s", "r1", "r2", "r3", "sac", "sar", "sacd", "sqf", "fs", "m", "w", "p"))) {
    exitNotFound();
}

//проверим, что передан код соревнования, и соревнование не скрыто от публикации
if (!isset($_REQUEST["id"])) {
    exitNotFound();
}
$competition = findCompetitionByCode($_REQUEST["id"]);
if (!$competition || getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, COMPETITION_HIDDEN_PARAM_NAME, "0", $competition->TourId) === "1") {
    exitNotFound();
}

//засунем в реквест общие параметры для всех отчетов
$_REQUEST["TourId"] = $competition->TourId;

switch ($_REQUEST["report"]) {
    case 'qI':
        $_REQUEST["Event"] = getVisibleEventCodesConsideringTeams($competition->TourId, "0");

        require_once "../../../Qualification/PrintIndividual.php";
        break;
    case 'qT':
        $_REQUEST["Event"] = getVisibleEventCodesConsideringTeams($competition->TourId, "1");

        require_once "../../../Qualification/PrintTeam.php";
        break;
    case 'bI':
        $_REQUEST["Event"] = getVisibleEventCodesConsideringTeams($competition->TourId, "0");

        $_REQUEST["ShowTargetNo"] = "1";
        $_REQUEST["ShowSchedule"] = "1";
        $_REQUEST["ShowSetArrows"] = "1";
        require_once "../../../Final/Individual/PrnBracket.php";
        break;
    case 'bT':
        $_REQUEST["Event"] = getVisibleEventCodesConsideringTeams($competition->TourId, "1");

        $_REQUEST["ShowTargetNo"] = "1";
        $_REQUEST["ShowSchedule"] = "1";
        $_REQUEST["ShowSetArrows"] = "1";
        require_once "../../../Final/Team/PrnBracket.php";
        break;
    case 'rI':
        $_REQUEST["Event"] = getVisibleEventCodesConsideringTeams($competition->TourId, "0");

        require_once "../../../Final/Individual/PrnRanking.php";
        break;
    case 'rT':
        $_REQUEST["Event"] = getVisibleEventCodesConsideringTeams($competition->TourId, "1");

        require_once "../../../Final/Team/PrnRanking.php";
        break;
    case "qcI":
        if (getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, PUBLISH_SCORECARDS_PARAM_NAME, "1", $competition->TourId) === "0") {
            exitNotFound();
        }

        $_REQUEST["ScoreFilled"] = "1";
        $_REQUEST["ScorePageHeaderFooter"] = "1";
        $_REQUEST["ScoreFlags"] = "1";
        $_REQUEST["noEmpty"] = "1";

        if (!isset($_REQUEST["session"]) || !filter_var($_REQUEST["session"], FILTER_VALIDATE_INT) || $_REQUEST["session"] <= 0) {
            exitNotFound();
        }
        $_REQUEST["x_Session"] = $_REQUEST["session"];

        //нужно определить количество дистанций в смене
        $maxDistancesQuery = "select max((QuD1Arrowstring != '') + (QuD2Arrowstring != '') + (QuD3Arrowstring != '') + (QuD4Arrowstring != '') +
                                        (QuD5Arrowstring != '') + (QuD6Arrowstring != '') + (QuD7Arrowstring != '') +
                                        (QuD7Arrowstring != '')) as MaxDistances
                                from Qualifications q
                                left join Entries e on q.QuId = e.EnId
                                where e.EnTournament = " . StrSafe_DB($competition->TourId) . " and q.QuSession = " . $_REQUEST["session"];
        $resultSet = safe_r_SQL($maxDistancesQuery);
        $_REQUEST["ScoreDist"] = range(1, safe_fetch($resultSet)->MaxDistances);

        require_once "../../../Qualification/PDFScore.php";
        break;
    case "j":
        require_once "../../../Tournament/PrnStaffField.php";
        break;
    case "d":
        $_REQUEST["Session"] = $_REQUEST["session"];
        $_REQUEST["Filled"] = "1";

        require_once "../../../Partecipants/PrnSession.php";
        break;
    case "s":
        $_REQUEST["Finalists"] = "1";

        require_once "../../../Scheduler/PrnScheduler.php";
        break;
    case "r1":
    case "r2":
    case "r3":
        $index = substr($_REQUEST["report"], 1);

        $c = new stdClass();
        $c->ToId = $competition->TourId;

        if (getModuleParameter(PROTOCOL_MODULE, CHECKED_COUNTRY_PARAM_PREFIX . $index, "0", $c->ToId) === "0") {
            exitNotFound();
        } else {

            $_REQUEST["countryIndex"] = $index;
            $_REQUEST["StatHeader" . $index] = getRegionStatisticsHeaderParameter($c, $index);
            $_REQUEST["AthletesOnly"] = "1";

            require_once "../../../Partecipants/PrnStatCountry.php";
        }
        break;
    case "sac":
        $_REQUEST["SinglePage"] = "1";
        $_REQUEST["TeamEvents"] = getVisibleEventCodesConsideringTeams($competition->TourId, "0");
        unset($_REQUEST["Event"]);

        require_once "../../../Partecipants/PrnCategory.php";
        break;
    case "sar":
        $_REQUEST["SinglePage"] = "On";
        $_REQUEST["Session"] = "All";

        require_once "../../../Partecipants/PrnCountry.php";
        break;
    case "sacd":
        require_once "../../../Partecipants/PrnStatClasses.php";
        break;
    case "sqf":
        require_once "../../../Partecipants/PrnStatEvents.php";
        break;
    case "fcI":
        if (getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, PUBLISH_SCORECARDS_PARAM_NAME, "1", $competition->TourId) === "0") {
            exitNotFound();
        }

        $_REQUEST["Event"] = getVisibleEventCodesConsideringTeams($competition->TourId, "0");
        $_REQUEST["ScoreFilled"] = "1";

        require_once "../../../Final/Individual/PDFScoreMatch.php";
        break;
    case "fcT":
        if (getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, PUBLISH_SCORECARDS_PARAM_NAME, "1", $competition->TourId) === "0") {
            exitNotFound();
        }

        $_REQUEST["Event"] = getVisibleEventCodesConsideringTeams($competition->TourId, "1");
        $_REQUEST["ScoreFilled"] = "1";

        require_once "../../../Final/Team/PDFScoreMatch.php";
        break;
    case "fs":
        $_REQUEST["TeamComponents"] = "1";
        $_REQUEST["ses"] = getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, SHOW_FINAL_SESSIONS_IN_SCHEDULE_PARAM_NAME, array(), $competition->TourId);

        require_once "../../../Scheduler/OrisSchedule.php";
        break;
    case "m":
        require_once "../../../Final/PDFMedalStanding.php";
        break;
    case "w":
        require_once "../../../Final/PDFMedalList.php";
        break;
    case "p":
        $_REQUEST["doPrint"] = "1";

        $_REQUEST["Events"] = getVisibleEventCodesConsideringTeams($competition->TourId, "0");
        $_REQUEST["TeamEvents"] = getVisibleEventCodesConsideringTeams($competition->TourId, "1");

        $c = new stdClass();
        $c->ToId = $competition->TourId;

        if (getModuleParameter(PROTOCOL_MODULE, Checked1ParameterName, "0", $competition->TourId) === "1") {
            $_REQUEST["country1"] = "1";
            $_REQUEST["StatHeader1"] = getRegionStatisticsHeaderParameter($c, "1");
        }
        if (getModuleParameter(PROTOCOL_MODULE, Checked2ParameterName, "0", $competition->TourId) === "1") {
            $_REQUEST["country2"] = "1";
            $_REQUEST["StatHeader2"] = getRegionStatisticsHeaderParameter($c, "2");
        }
        if (getModuleParameter(PROTOCOL_MODULE, Checked3ParameterName, "0", $competition->TourId) === "1") {
            $_REQUEST["country3"] = "1";
            $_REQUEST["StatHeader3"] = getRegionStatisticsHeaderParameter($c, "3");
        }

        require_once "../../../Tournament/FinalReport/Protocol.php";
        break;
}
