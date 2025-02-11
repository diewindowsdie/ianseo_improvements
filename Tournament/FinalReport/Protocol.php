<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/pdf/ResultPDF.inc.php');
require_once('Common/OrisFunctions.php');

checkACL(array(AclIndividuals, AclTeams, AclCompetition), AclReadOnly);

if ($_REQUEST["doPrint"]) {
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
        "TeamRankings" => count(getRankingTeams()->rankData['sections'])
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
        )) && ($_REQUEST["country1"] || $_REQUEST["country2"] || $_REQUEST["country3"])) {
        $pageOrientation = 'L';
    }
    $pdf->AddPage($pageOrientation);

    //личная квалификация
    if ($pagesPresent["IndividualQualification"]) {
        $pdf->Titolo = get_text('ResultIndAbs', 'Tournament');
        include '../../Qualification/PrnIndividualAbs.php';
        //проверим, что есть хотя бы один отчет после этого и до статистики по регионам, где нужна портретная ориентация страницы, и есть статистика по регионам
        if (!in_array(true, array(
                $pagesPresent["TeamQualification"],
                $pagesPresent["IndividualBrackets"],
                $pagesPresent["IndividualRankings"],
                $pagesPresent["TeamBrackets"],
                $pagesPresent["TeamRankings"]
            )) && ($_REQUEST["country1"] || $_REQUEST["country2"] || $_REQUEST["country3"])) {
            $pageOrientation = 'L';
        }
        $pdf->AddPage($pageOrientation);
    }

    //командная квалификация
    if ($pagesPresent["TeamQualification"]) {
        $pdf->Titolo = get_text('ResultSqAbs', 'Tournament');
        include '../../Qualification/PrnTeamAbs.php';
        //проверим, что есть хотя бы один отчет после этого и до статистики по регионам, где нужна портретная ориентация страницы, и есть статистика по регионам
        if (!in_array(true, array(
                $pagesPresent["IndividualBrackets"],
                $pagesPresent["IndividualRankings"],
                $pagesPresent["TeamBrackets"],
                $pagesPresent["TeamRankings"]
            )) && ($_REQUEST["country1"] || $_REQUEST["country2"] || $_REQUEST["country3"])) {
            $pageOrientation = 'L';
        }
        $pdf->AddPage($pageOrientation);
    }

    //личные сетки
    if ($pagesPresent["IndividualBrackets"]) {
        $pdf->Titolo = get_text('VersionBracketsInd', 'Tournament');
        include '../../Final/Individual/PrnBracket.php';
        //проверим, что есть хотя бы один отчет после этого и до статистики по регионам, где нужна портретная ориентация страницы, и есть статистика по регионам
        if (!in_array(true, array(
                $pagesPresent["IndividualRankings"],
                $pagesPresent["TeamBrackets"],
                $pagesPresent["TeamRankings"]
            )) && ($_REQUEST["country1"] || $_REQUEST["country2"] || $_REQUEST["country3"])) {
            $pageOrientation = 'L';
        }
        $pdf->AddPage($pageOrientation);
    }

    //личные лесенки
    if ($pagesPresent["IndividualRankings"]) {
        $pdf->Titolo = get_text('RankingInd');
        include '../../Final/Individual/PrnRanking.php';
        //проверим, что есть хотя бы один отчет после этого и до статистики по регионам, где нужна портретная ориентация страницы, и есть статистика по регионам
        if (!in_array(true, array(
                $pagesPresent["TeamBrackets"],
                $pagesPresent["TeamRankings"]
            )) && ($_REQUEST["country1"] || $_REQUEST["country2"] || $_REQUEST["country3"])) {
            $pageOrientation = 'L';
        }
        $pdf->AddPage($pageOrientation);
    }

    //командные сетки
    if ($pagesPresent["TeamBrackets"]) {
        $pdf->Titolo = get_text('VersionBracketsTeam', 'Tournament');
        include '../../Final/Team/PrnBracket.php';
        //проверим, что есть хотя бы один отчет после этого и до статистики по регионам, где нужна портретная ориентация страницы, и есть статистика по регионам
        if (!in_array(true, array(
                $pagesPresent["TeamRankings"]
            )) && ($_REQUEST["country1"] || $_REQUEST["country2"] || $_REQUEST["country3"])) {
            $pageOrientation = 'L';
        }
        $pdf->AddPage($pageOrientation);
    }

    //командные лесенки
    if ($pagesPresent["TeamRankings"]) {
        $pdf->Titolo = get_text('RankingSq');
        include '../../Final/Team/PrnRanking.php';
        //если есть хотя бы один отчет со статистикой по "регионам" - ставим альбомную ориентацию
        if ($_REQUEST["country1"] || $_REQUEST["country2"] || $_REQUEST["country3"]) {
            $pageOrientation = 'L';
        }
        $pdf->AddPage($pageOrientation);
    }

    //статистика по "регионам" по полю 1
    if ($_REQUEST["country1"]) {
        setcookie($_SESSION['TourId'] . "_checkedCountry1", "1", 0, $CFG->ROOT_DIR);
        if ($_REQUEST["StatHeader1"] != "") {
            setcookie($_SESSION['TourId'] . "_StatHeader1", $_REQUEST["StatHeader1"], 0, $CFG->ROOT_DIR);
        }
        $_REQUEST["countryIndex"] = 1;
        $pdf->Titolo = get_text('Statistics', 'Tournament');
        include '../../Partecipants/PrnStatCountry.php';
        //если больше нет отчетов со статистикой по "регионам" - возвращаем портретную ориентацию
        if (!$_REQUEST["country2"] && !$_REQUEST["country3"]) {
            $pageOrientation = 'P';
        }
        $pdf->AddPage($pageOrientation);
    } else {
        setcookie($_SESSION['TourId'] . "_checkedCountry1", "0", 0, $CFG->ROOT_DIR);
    }

    //статистика по "регионам" по полю 2
    if ($_REQUEST["country2"]) {
        setcookie($_SESSION['TourId'] . "_checkedCountry2", "1", 0, $CFG->ROOT_DIR);
        if ($_REQUEST["StatHeader2"] != "") {
            setcookie($_SESSION['TourId'] . "_StatHeader2", $_REQUEST["StatHeader2"], 0, $CFG->ROOT_DIR);
        }
        $_REQUEST["countryIndex"] = 2;
        $pdf->Titolo = get_text('Statistics', 'Tournament');
        include '../../Partecipants/PrnStatCountry.php';
        //если больше нет отчетов со статистикой по "регионам" - возвращаем портретную ориентацию
        if (!$_REQUEST["country3"]) {
            $pageOrientation = 'P';
        }
        $pdf->AddPage($pageOrientation);
    } else {
        setcookie($_SESSION['TourId'] . "_checkedCountry2", "0", 0, $CFG->ROOT_DIR);
    }

    //статистика по "регионам" по полю 3
    if ($_REQUEST["country3"]) {
        setcookie($_SESSION['TourId'] . "_checkedCountry3", "1", 0, $CFG->ROOT_DIR);
        if ($_REQUEST["StatHeader3"] != "") {
            setcookie($_SESSION['TourId'] . "_StatHeader3", $_REQUEST["StatHeader3"], 0, $CFG->ROOT_DIR);
        }
        $_REQUEST["countryIndex"] = 3;
        $pdf->Titolo = get_text('Statistics', 'Tournament');
        include '../../Partecipants/PrnStatCountry.php';
        //других отчетов со статистикой уже точно больше не будет
        $pageOrientation = 'P';
        $pdf->AddPage($pageOrientation);
    } else {
        setcookie($_SESSION['TourId'] . "_checkedCountry3", "0", 0, $CFG->ROOT_DIR);
    }

    //судьи
    $pdf->Titolo = get_text('StaffOnField', 'Tournament');
    include '../../Tournament/PrnStaffField.php';

    $pdf->Output();
} else {
    $IncludeFA = true;
    $IncludeJquery = true;
    include('Common/Templates/head.php');

    ?>
    <form id="printProtocol" method="POST" action="Protocol.php?doPrint=1">
        <table class="Tabella">
            <tr>
                <th style="width: 10%; text-align: left; padding-left: 20px">Включить в протокол соревнований информацию
                    о странах/регионах участников?
                </th>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px; padding-top: 15px"><input type="checkbox" name="country1" id="country1"
                        <?= !isset($_COOKIE[$_SESSION['TourId'] . "_checkedCountry1"]) || $_COOKIE[$_SESSION['TourId'] . "_checkedCountry1"] == "1" ? ' checked' : ''?>><label style="padding-left: 5px"
                                                                                  for="country1">Включить отчет о
                        странах/регионах первого уровня</label></td>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px">
                    Заголовок отчета о странах/регионах первого уровня. Например, "Субъекты РФ":<br/>
                    <input style="width: 500px; height: 25px" type="text" name="StatHeader1" id="StatHeader1"
                           value="<?= $_COOKIE[$_SESSION['TourId'] . "_StatHeader1"] ?? get_text('RegionsAndCountries', 'Tournament'); ?>"/>
                </td>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px; padding-top: 15px"><input type="checkbox" name="country2" id="country2"
                        <?= !isset($_COOKIE[$_SESSION['TourId'] . "_checkedCountry2"]) || $_COOKIE[$_SESSION['TourId'] . "_checkedCountry2"] == "1" ? ' checked' : ''?>><label style="padding-left: 5px"
                                                                                  for="country2">Включить отчет о
                        странах/регионах второго уровня</label></td>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px">
                    Заголовок отчета о странах/регионах второго уровня. Например, "Спортивные школы":<br/>
                    <input style="width: 500px; height: 25px" type="text" name="StatHeader2" id="StatHeader2"
                           value="<?= $_COOKIE[$_SESSION['TourId'] . "_StatHeader2"] ?? get_text('RegionsAndCountries', 'Tournament'); ?>"/>
                </td>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px; padding-top: 15px"><input type="checkbox" name="country3" id="country3"
                        <?= $_COOKIE[$_SESSION['TourId'] . "_checkedCountry3"] == "1" ? ' checked' : ''?>><label
                            style="padding-left: 5px" for="country3">Включить отчет о странах/регионах третьего
                        уровня</label></td>
            </tr>
            <tr>
                <td class="Left" style="padding-left: 20px">
                    Заголовок отчета о странах/регионах третьего уровня. Например, "Спортивные клубы" или "Команды":<br/>
                    <input style="width: 500px; height: 25px" type="text" name="StatHeader3" id="StatHeader3"
                           value="<?= $_COOKIE[$_SESSION['TourId'] . "_StatHeader3"] ?? get_text('RegionsAndCountries', 'Tournament'); ?>"/></td>
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
