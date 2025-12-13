<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/pdf/ResultPDF.inc.php');
require_once('Common/OrisFunctions.php');

checkACL(array(AclIndividuals, AclTeams, AclCompetition), AclReadOnly);

function landscapePagesNeccesary() {
    return array_key_exists("country1", $_REQUEST) || array_key_exists("country2", $_REQUEST) || array_key_exists("country3", $_REQUEST);
}

const ModuleName = "Protocol";

const Header1ParameterName = "StatHeader1";
const Header2ParameterName = "StatHeader2";
const Header3ParameterName = "StatHeader3";

const Checked1ParameterName = "CheckedCountry1";
const Checked2ParameterName = "CheckedCountry2";
const Checked3ParameterName = "CheckedCountry3";

$isInternationalProtocol = getModuleParameter("Tournament", "InternationalProtocol", false, $_SESSION['TourId']);

if (array_key_exists("doPrint", $_REQUEST)) {
    //в этом отчете печатаем все
    $isCompleteResultBook = true;

    $pdf = new ResultPDF('');

    $_REQUEST["ShowSetArrows"] = 1;

    $pageOrientation = 'P';

    $pagesPresent = [
        "Medalists" => true,
        "IndividualQualification" => count(getQualificationIndividual()->rankData['sections']),
        "TeamQualification" => count(getQualificationTeam()->rankData['sections']),
        "IndividualBrackets" => count(getBracketsIndividual('', false, 0, 0, 1)->rankData['sections']),
        "IndividualRankings" => count(getRankingIndividual()->rankData['sections']),
        "TeamBrackets" => count(getBracketsTeams('', false, 0, 0, 1)->rankData['sections']),
        "TeamRankings" => count(getRankingTeams()->rankData['sections']),
        "Judges" => !$isInternationalProtocol,
    ];

    //медалисты в личке и командах
    $pdf->Titolo = get_text('MedallistsByEvent', 'Tournament');
    include '../../Final/PDFMedalList.php';
    //проверим, что есть хотя бы один отчет после этого и до статистики по регионам, где нужна портретная ориентация страницы, и есть статистика по регионам
    if (!in_array(true, array(
            $pagesPresent["IndividualQualification"],
            $pagesPresent["TeamQualification"],
            $pagesPresent["IndividualBrackets"],
            $pagesPresent["IndividualRankings"],
            $pagesPresent["TeamBrackets"],
            $pagesPresent["TeamRankings"]
        )) && landscapePagesNeccesary()) {
        $pageOrientation = 'L';
    }

    //личная квалификация
    if ($pagesPresent["IndividualQualification"]) {
        $pdf->AddPage($pageOrientation);
        $pdf->Titolo = get_text('ResultIndAbs', 'Tournament');
        $hideTempHeader = true;
        include '../../Qualification/PrnIndividualAbs.php';
        //проверим, что есть хотя бы один отчет после этого и до статистики по регионам, где нужна портретная ориентация страницы, и есть статистика по регионам
        if (!in_array(true, array(
                $pagesPresent["TeamQualification"],
                $pagesPresent["IndividualBrackets"],
                $pagesPresent["IndividualRankings"],
                $pagesPresent["TeamBrackets"],
                $pagesPresent["TeamRankings"]
            )) && landscapePagesNeccesary()) {
            $pageOrientation = 'L';
        }
    }

    //командная квалификация
    if ($pagesPresent["TeamQualification"]) {
        $pdf->AddPage($pageOrientation);
        $pdf->Titolo = get_text('ResultSqAbs', 'Tournament');
        $hideTempHeader = true;
        include '../../Qualification/PrnTeamAbs.php';
        //проверим, что есть хотя бы один отчет после этого и до статистики по регионам, где нужна портретная ориентация страницы, и есть статистика по регионам
        if (!in_array(true, array(
                $pagesPresent["IndividualBrackets"],
                $pagesPresent["IndividualRankings"],
                $pagesPresent["TeamBrackets"],
                $pagesPresent["TeamRankings"]
            )) && landscapePagesNeccesary()) {
            $pageOrientation = 'L';
        }
    }

    //личные сетки
    if ($pagesPresent["IndividualBrackets"]) {
        $pdf->AddPage($pageOrientation);
        $pdf->Titolo = get_text('VersionBracketsInd', 'Tournament');
        include '../../Final/Individual/PrnBracket.php';
        //проверим, что есть хотя бы один отчет после этого и до статистики по регионам, где нужна портретная ориентация страницы, и есть статистика по регионам
        if (!in_array(true, array(
                $pagesPresent["IndividualRankings"],
                $pagesPresent["TeamBrackets"],
                $pagesPresent["TeamRankings"]
            )) && landscapePagesNeccesary()) {
            $pageOrientation = 'L';
        }
    }

    //личные лесенки
    if ($pagesPresent["IndividualRankings"]) {
        $pdf->AddPage($pageOrientation);
        $pdf->Titolo = get_text('RankingInd');
        include '../../Final/Individual/PrnRanking.php';
        //проверим, что есть хотя бы один отчет после этого и до статистики по регионам, где нужна портретная ориентация страницы, и есть статистика по регионам
        if (!in_array(true, array(
                $pagesPresent["TeamBrackets"],
                $pagesPresent["TeamRankings"]
            )) && landscapePagesNeccesary()) {
            $pageOrientation = 'L';
        }
    }

    //командные сетки
    if ($pagesPresent["TeamBrackets"]) {
        $pdf->AddPage($pageOrientation);
        //unset($_REQUEST["ShowSetArrows"]);
        $pdf->Titolo = get_text('VersionBracketsTeam', 'Tournament');
        include '../../Final/Team/PrnBracket.php';
        //проверим, что есть хотя бы один отчет после этого и до статистики по регионам, где нужна портретная ориентация страницы, и есть статистика по регионам
        if (!in_array(true, array(
                $pagesPresent["TeamRankings"]
            )) && landscapePagesNeccesary()) {
            $pageOrientation = 'L';
        }
    }

    //командные лесенки
    if ($pagesPresent["TeamRankings"]) {
        $pdf->AddPage($pageOrientation);
        $pdf->Titolo = get_text('RankingSq');
        include '../../Final/Team/PrnRanking.php';
        //если есть хотя бы один отчет со статистикой по "регионам" - ставим альбомную ориентацию
        if (landscapePagesNeccesary()) {
            $pageOrientation = 'L';
        }
    }

    //статистика по "регионам" по полю 1
    if (array_key_exists("country1", $_REQUEST)) {
        $pdf->AddPage($pageOrientation);
        setModuleParameter(ModuleName, Checked1ParameterName, "1", $_SESSION['TourId']);
        if ($_REQUEST["StatHeader1"] != "") {
            setModuleParameter(ModuleName, Header1ParameterName, $_REQUEST["StatHeader1"], $_SESSION['TourId']);
        }
        $_REQUEST["countryIndex"] = 1;
        $pdf->Titolo = get_text('Statistics', 'Tournament');
        include '../../Partecipants/PrnStatCountry.php';
        //если больше нет отчетов со статистикой по "регионам" - возвращаем портретную ориентацию
        if (!array_key_exists("country2", $_REQUEST) && !array_key_exists("country3", $_REQUEST)) {
            $pageOrientation = 'P';
        }
    } else {
        setModuleParameter(ModuleName, Checked1ParameterName, "0", $_SESSION['TourId']);
    }

    //статистика по "регионам" по полю 2
    if (array_key_exists("country2", $_REQUEST)) {
        $pdf->AddPage($pageOrientation);
        setModuleParameter(ModuleName, Checked2ParameterName, "1", $_SESSION['TourId']);
        if ($_REQUEST["StatHeader2"] != "") {
            setModuleParameter(ModuleName, Header2ParameterName, $_REQUEST["StatHeader2"], $_SESSION['TourId']);
        }
        $_REQUEST["countryIndex"] = 2;
        $pdf->Titolo = get_text('Statistics', 'Tournament');
        include '../../Partecipants/PrnStatCountry.php';
        //если больше нет отчетов со статистикой по "регионам" - возвращаем портретную ориентацию
        if (!array_key_exists("country3", $_REQUEST)) {
            $pageOrientation = 'P';
        }
    } else {
        setModuleParameter(ModuleName, Checked2ParameterName, "0", $_SESSION['TourId']);
    }

    //статистика по "регионам" по полю 3
    if (array_key_exists("country3", $_REQUEST)) {
        $pdf->AddPage($pageOrientation);
        setModuleParameter(ModuleName, Checked3ParameterName, "1", $_SESSION['TourId']);
        if ($_REQUEST["StatHeader3"] != "") {
            setModuleParameter(ModuleName, Header3ParameterName, $_REQUEST["StatHeader3"], $_SESSION['TourId']);
        }
        $_REQUEST["countryIndex"] = 3;
        $pdf->Titolo = get_text('Statistics', 'Tournament');
        include '../../Partecipants/PrnStatCountry.php';
        //других отчетов со статистикой уже точно больше не будет
        $pageOrientation = 'P';
    } else {
        setModuleParameter(ModuleName, Checked3ParameterName, "0", $_SESSION['TourId']);
    }

    //судьи
    if ($pagesPresent["Judges"]) {
        $pdf->AddPage($pageOrientation);
        $pdf->Titolo = get_text('StaffOnField', 'Tournament');
        include '../../Tournament/PrnStaffField.php';
    }

    $pdf->Output();
} else {
    $IncludeFA = true;
    $IncludeJquery = true;
    include('Common/Templates/head.php');

    ?>
    <form id="printProtocol" method="POST" action="Protocol.php?doPrint=1" target="_blank">
        <table class="Tabella">
            <tr>
                <th style="width: 10%; text-align: left; padding-left: 20px">Включить в протокол соревнований информацию
                    о странах/регионах участников?
                </th>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px; padding-top: 15px"><input type="checkbox" name="country1" id="country1"
                        <?= getModuleParameter(ModuleName, Checked1ParameterName, "1", $_SESSION['TourId']) === "1" ? ' checked' : ''?> onchange="$('#StatHeader1').prop('disabled', !this.checked)"><label style="padding-left: 5px"
                                                                                  for="country1">Включить отчет о
                        странах/регионах первого уровня</label></td>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px">
                    Заголовок отчета о странах/регионах первого уровня. Например, "Субъекты РФ":<br/>
                    <input style="width: 500px; height: 25px" type="text" name="StatHeader1" id="StatHeader1"
                           value="<?= getModuleParameter(ModuleName, Header1ParameterName, get_text('RegionsAndCountries', 'Tournament'), $_SESSION['TourId']) ?>" <?= getModuleParameter(ModuleName, Checked1ParameterName, "1", $_SESSION['TourId']) !== "1" ? ' disabled' : ''?>/>
                </td>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px; padding-top: 15px"><input type="checkbox" name="country2" id="country2"
                        <?= getModuleParameter(ModuleName, Checked2ParameterName, "1", $_SESSION['TourId']) === "1"  ? ' checked' : ''?> onchange="$('#StatHeader2').prop('disabled', !this.checked)"><label style="padding-left: 5px"
                                                                                  for="country2">Включить отчет о
                        странах/регионах второго уровня</label></td>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px">
                    Заголовок отчета о странах/регионах второго уровня. Например, "Спортивные школы":<br/>
                    <input style="width: 500px; height: 25px" type="text" name="StatHeader2" id="StatHeader2"
                           value="<?= getModuleParameter(ModuleName, Header2ParameterName, get_text('RegionsAndCountries', 'Tournament'), $_SESSION['TourId']) ?>" <?= getModuleParameter(ModuleName, Checked2ParameterName, "1", $_SESSION['TourId']) !== "1" ? ' disabled' : ''?>/>
                </td>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px; padding-top: 15px"><input type="checkbox" name="country3" id="country3"
                        <?= getModuleParameter(ModuleName, Checked3ParameterName, "0", $_SESSION['TourId']) === "1"  ? ' checked' : ''?> onchange="$('#StatHeader3').prop('disabled', !this.checked)"><label
                            style="padding-left: 5px" for="country3">Включить отчет о странах/регионах третьего
                        уровня</label></td>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px; padding-top: 15px; padding-bottom: 15px">
                    Заголовок отчета о странах/регионах третьего уровня. Например, "Спортивные клубы" или "Команды":<br/>
                    <input style="width: 500px; height: 25px" type="text" name="StatHeader3" id="StatHeader3"
                           value="<?= getModuleParameter(ModuleName, Header3ParameterName, get_text('RegionsAndCountries', 'Tournament'), $_SESSION['TourId']) ?>" <?= getModuleParameter(ModuleName, Checked3ParameterName, "0", $_SESSION['TourId']) !== "1" ? ' disabled' : ''?>/></td>
            </tr>
            <tr>
                <th style="width: 10%; text-align: left; padding-left: 20px">Какие индивидуальные и командные события необходимо включить в протокол?
                </th>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px; padding-top: 15px"><?php
                    $MySql = "SELECT EvCode, EvEventName FROM Events WHERE EvTeamEvent='0' AND EvTournament=" . StrSafe_DB($_SESSION['TourId']) . " and EvCodeParent='' ORDER BY EvProgr";
                    $Rs = safe_r_sql($MySql);
                    if(safe_num_rows($Rs)>0) {
                        echo 'Включить в протокол индивидуальные события:<br><select id="IndividualEvents" name="Event[]" multiple="multiple" size="10">';
                        echo '<option value=".">' . get_text('AllEvents')  . '</option>';
                        while($MyRow=safe_fetch($Rs))
                            echo '<option value="' . $MyRow->EvCode . '">' . $MyRow->EvCode . ' - ' . get_text($MyRow->EvEventName,'','',true)  . '</option>';
                        echo '</select>';
                        safe_free_result($Rs);
                    }
                ?></td>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px; padding-top: 15px""><?php
                    $MySql = "SELECT EvCode, EvEventName FROM Events WHERE EvTeamEvent='1' AND EvTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND EvFinalFirstPhase!=0 and EvCodeParent='' ORDER BY EvProgr";
                    $Rs = safe_r_sql($MySql);
                    if(safe_num_rows($Rs)>0) {
                        echo 'Включить в протокол командные события:<br><select id="TeamEvents" name="Event[]" multiple="multiple" size="10">';
                        echo '<option value=".">' . get_text('AllEvents')  . '</option>';
                        while($MyRow=safe_fetch($Rs))
                            echo '<option value="' . $MyRow->EvCode . '">' . $MyRow->EvCode . ' - ' . get_text($MyRow->EvEventName,'','',true)  . '</option>';
                        echo '</select>';
                        safe_free_result($Rs);
                    }
                ?></td>
            </tr>
            <tr>
                <th class="Left" style="padding-left: 50px; padding-top: 10px; padding-bottom: 10px">
                    <div class="Button" onclick="$('#printProtocol').submit()">Распечатать</div>
                </th>
            </tr>
        </table>
    </form>

    <?php
    include('Common/Templates/tail.php');
}
?>
