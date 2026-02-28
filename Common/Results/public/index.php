<?php
require_once(dirname(__FILE__, 4) . '/config.php');
require_once dirname(__FILE__, 2) . "/lib.php";

global $CFG;

$JS_SCRIPT=array(
    '<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Common/Results/public/results.js"></script>',
    '<link href="'.$CFG->ROOT_DIR.'Common/Results/public/results.css" media="screen" rel="stylesheet" type="text/css">',
);

$IncludeFA=true;
$IncludeJquery=true;

global $PAGE_TITLE;
$PAGE_TITLE = "Результаты соревнований";

include('Common/Templates/head-min.php');

echo '<table class="Tabella w-100 competitionsList" id="competitionsList">
    <tr>
        <th class="Title" colspan="2">Результаты соревнований</th>
    </tr>';
echo '<tr class="Divider"><th colspan="2"></th></tr>';

$competitionsQuery = "SELECT ToId,ToCode,ToPrintLang,ToName,ToWhere,ToVenue,DATE_FORMAT(ToWhenFrom,'" . get_text('DateFmtDB') . "') AS DtFrom, "
    . "DATE_FORMAT(ToWhenTo,'" . get_text('DateFmtDB') . "') AS DtTo "
    . "FROM Tournament "
    . "ORDER BY ToWhenTo DESC, ToWhenFrom DESC, ToCode ASC";
$competitionsResultSet = safe_r_SQL($competitionsQuery);
while ($competition = safe_fetch($competitionsResultSet)) {
    //пропускаем соревнования, у которых стоит флаг "скрыто"
    if (getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, COMPETITION_HIDDEN_PARAM_NAME, "0", $competition->ToId) === "1") {
        continue;
    }

    echo '<tr>
            <th><i id="toggleCompetition_' . $competition->ToId . '" class="fa-solid fa-caret-right" onclick="toggle(\'toggleCompetition_\', \'competition_\', ' . $competition->ToId . ', true)"></i></th>
            <th class="competitionTitle" onclick="toggle(\'toggleCompetition_\', \'competition_\', ' . $competition->ToId . ', true)">' . htmlspecialchars($competition->ToName) . '</th>
    </tr>
    <tr>
            <th></th>
            <th class="competitionLocation">' . htmlspecialchars($competition->ToWhere) . ($competition->ToVenue ? (' (' . $competition->ToVenue . ')') : '') . ', ' . formatTournamentDateAtCompetitionLanguage($competition) . '</th>
    </tr>
    <tr id="competition_' . $competition->ToId . '" class="display-none">
        <td colspan="2" class="competitionDetails">';

    echo '<div class="competitionDetailsLink"><a target="_blank" href="' . (isset($CFG->COMPETITION_RESULTS_PAGE_PREFIX) ? $CFG->COMPETITION_RESULTS_PAGE_PREFIX : ($CFG->ROOT_DIR . DefaultCompetitionResultsPagePrefix)) . $competition->ToCode . '">' . getTextAtCompetitionLanguage('OpenInNewTab', $competition, 'Help'). '</a></div><br />';

    echo getCompetitionDetailsHtml($competition);
    echo '</td>
    </tr>';
    echo '<tr class="Divider" id="divider_' . $competition->ToId . '"><th colspan="2"></th></tr>';
}

echo '</table>';

include('Common/Templates/tail-base.php');
