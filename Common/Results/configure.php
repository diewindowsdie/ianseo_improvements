<?php
require_once dirname(__FILE__, 3) . '/config.php';
require_once "lib.php";

global $CFG;

if (!CheckTourSession()) {
    CD_redirect($CFG->ROOT_DIR);
}

checkACL(array(AclCompetition), AclReadWrite);

$IncludeFA = true;
$IncludeJquery = true;

$JS_SCRIPT=array(
    '<script type="text/javascript" src="public/results.js"></script>'
);

include('Common/Templates/head.php');
echo '
    <form>
        <table class="Tabella w-40" style="margin: 20px">
            <tr><th colspan="2" style="padding: 10px">Настройки публикации результатов соревнования:</th></tr>
            <tr><td><input type="checkbox" id="' . COMPETITION_HIDDEN_PARAM_NAME . '"' . (getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, COMPETITION_HIDDEN_PARAM_NAME, "0", $_SESSION['TourId']) === "0" ? ' checked="checked"' : '') . ' onchange="toggleResultsCheckbox(this, true)"></td><td>Публиковать результаты этого соревнования</td></tr>
            <tr><th colspan="2" style="padding: 5px"><span style="font-weight: normal">Укажите индивидуальные и командные события, результаты которых <b>должны быть</b> опубликованы (квалификация и результаты):</span></th></tr>';

foreach (getCompetitionEvents($_SESSION['TourId'], true) as $event) {
    echo '<tr><td><input type="checkbox" id="' . COMPETITION_EVENT_HIDDEN_PARAM_NAME_PREFIX . $event->EvCode . '_' . $event->EvTeamEvent . '"' . (getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, COMPETITION_EVENT_HIDDEN_PARAM_NAME_PREFIX . $event->EvCode . '_' . $event->EvTeamEvent, "0", $_SESSION['TourId']) === "0" ? ' checked="checked"' : '') . ' onchange="toggleResultsCheckbox(this, true)"></td><td>' . $event->EvEventName . '</td></tr>';

}
echo '<tr><th colspan="2" style="padding: 5px"><span style="font-weight: normal">Публикация карточек записи:</span></th></tr>';
echo '<tr><td><input type="checkbox" id="' . PUBLISH_SCORECARDS_PARAM_NAME . '"' . (getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, PUBLISH_SCORECARDS_PARAM_NAME, "1", $_SESSION['TourId']) === "1" ? ' checked="checked"' : '') . ' onchange="toggleResultsCheckbox(this)"></td><td>Публиковать заполненные карточки записи квалификации, личных и командных финалов</td></tr>';
echo '<tr><th colspan="2" style="padding: 5px"><span style="font-weight: normal">Статистика по количеству спортсменов, собранная по полям Регион/Команда 1, 2 и 3:</span></th></tr>';
echo '<tr><td colspan="2">Публикация данных по региону/команде 1, и заголовок таблицы:</td></tr>';
echo '<tr><td><input type="checkbox" id="' . Checked1ParameterName . '"' . (getModuleParameter(PROTOCOL_MODULE, Checked1ParameterName, "0", $_SESSION['TourId']) === "1" ? ' checked="checked"' : '') . ' onchange="toggleProtocolRelatedCheckbox(this)"/></td>
        <td><input style="width: 500px; height: 25px" type="text" id="' . Header1ParameterName . '" value="' . getModuleParameter(PROTOCOL_MODULE, Header1ParameterName, get_text('RegionsAndCountries', 'Tournament'), $_SESSION['TourId']) . '" onblur="saveProtocolRegionFieldTitle(this)"></td></tr>';
echo '<tr><td colspan="2">Публикация данных по региону/команде 2, и заголовок таблицы:</td></tr>';
echo '<tr><td><input type="checkbox" id="' . Checked2ParameterName . '"' . (getModuleParameter(PROTOCOL_MODULE, Checked2ParameterName, "0", $_SESSION['TourId']) === "1" ? ' checked="checked"' : '') . ' onchange="toggleProtocolRelatedCheckbox(this)"/></td>
        <td><input style="width: 500px; height: 25px" type="text" id="' . Header2ParameterName . '" value="' . getModuleParameter(PROTOCOL_MODULE, Header3ParameterName, get_text('RegionsAndCountries', 'Tournament'), $_SESSION['TourId']) . '" onblur="saveProtocolRegionFieldTitle(this)"></td></tr>';
echo '<tr><td colspan="2">Публикация данных по региону/команде 3, и заголовок таблицы:</td></tr>';
echo '<tr><td><input type="checkbox" id="' . Checked3ParameterName . '"' . (getModuleParameter(PROTOCOL_MODULE, Checked3ParameterName, "0", $_SESSION['TourId']) === "1" ? ' checked="checked"' : '') . ' onchange="toggleProtocolRelatedCheckbox(this)"/></td>
        <td><input style="width: 500px; height: 25px" type="text" id="' . Header3ParameterName . '" value="' . getModuleParameter(PROTOCOL_MODULE, Header3ParameterName, get_text('RegionsAndCountries', 'Tournament'), $_SESSION['TourId']) . '" onblur="saveProtocolRegionFieldTitle(this)"></td></tr>';

echo '<tr><th colspan="2" style="padding: 5px"><span style="font-weight: normal">Укажите смены финалов, которые <b>должны</b> попасть в подробную программу финалов:</span></th></tr>';

$displayedSessions = getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, SHOW_FINAL_SESSIONS_IN_SCHEDULE_PARAM_NAME, array(), $_SESSION["TourId"]);
foreach (getFinalSessions($_SESSION['TourId']) as $session) {
    echo '<tr><td><input type="checkbox" id="session_' . $session->SesOrder . '"' . (in_array($session->SesOrder, $displayedSessions, true) ? ' checked="checked"' : '') . ' onchange="setDisplayedSessions(this)"></td><td>' . $session->SesName . '</td></tr>';
}

    echo '<tr><th colspan="2" style="padding: 5px"><span style="font-weight: normal">Адрес, по которому можно получить протокол соревнования с подписями и печатями:</span></th></tr>';
    echo '<tr><td colspan="2"><input class="w-100" type="text" id="' . PROTOCOL_PDF_PARAM_NAME . '" value="' . getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, PROTOCOL_PDF_PARAM_NAME, "", $_SESSION['TourId']) . '" onblur="saveProtocolUrl(this)"></td></tr>';

    echo '</table>
    </form>';
include('Common/Templates/tail.php');
