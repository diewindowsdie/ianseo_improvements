<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/pdf/ResultPDF.inc.php');

const moduleName = "GSK-Report";
const prefix = "field_";

if (!CheckTourSession()) { //todo check some acl
//todo redirect
}

function getField($field, $defaultValue = null) {
    return getModuleParameter(moduleName, prefix . $field, $defaultValue, $_SESSION["TourId"]);
}

//общая информация о соревновании
$query = "select * from Tournament where ToId = " . $_SESSION["TourId"];
$rs = safe_r_SQL($query);
$tournamentData = safe_fetch($rs);

//статистика по участникам по полу, и по регионам
$query = "select count(Males) Males, count(Females) Females, count(*) Total, count(distinct EnCountry) RegionsCount from (
    select Entries.EnId Males, null Females, EnCountry from Entries where EnSex = 0 and EnTournament = " . $_SESSION["TourId"] . "
        union
        select null Males, Entries.EnId Females, EnCountry from Entries where EnSex = 1 and EnTournament = " . $_SESSION["TourId"] . ") t";
$rs = safe_r_SQL($query);
$participantsStatistics = safe_fetch($rs);

//общая статистика по судьям
$query = "select count(NonLocal) NonLocal, count(Total) Total from (
    select TiId Total, null NonLocal from TournamentInvolved where TiTournament = " . $_SESSION["TourId"] . "
        union
    select null Total, TiId NonLocal from TournamentInvolved where TiTournament = " . $_SESSION["TourId"] . " and TiCountry != " . getField("localCountryIdForJudges", "''") . ") t";
$rs = safe_r_SQL($query);
$judgesData = safe_fetch($rs);

//статистика по имеющимся судейским категориям
$query = "select TournamentInvolved.TiAccreditation, count(TournamentInvolved.TiAccreditation) Count from TournamentInvolved where TiTournament = " . $_SESSION["TourId"] . " group by TiAccreditation";
$rs = safe_r_SQL($query);
$judgesAccreditation = array();
while ($row = safe_fetch($rs)) {
    $judgesAccreditation[$row->TiAccreditation] = $row->Count;
}

//регионы судей
$query = "select c.CoId, c.CoNameComplete, count(*) FromRegion from TournamentInvolved ti 
    left join Countries c on ti.TiCountry = c.CoId and ti.TiTournament = c.CoTournament where ti.TiTournament = " . $_SESSION["TourId"] . " group by ti.TiCountry, c.CoNameComplete order by c.CoNameComplete";
$rs = safe_r_SQL($query);
$judgeRegions = array();
while ($row = safe_fetch($rs)) {
    $judgeRegions[$row->CoId]["Name"] = $row->CoNameComplete;
    $judgeRegions[$row->CoId]["FromRegion"] = $row->FromRegion;
}

//статистика по регионам
$query = "select CoId, CoNameComplete, sum(coalesce(Males, 0)) Males, sum(coalesce(Females, 0)) Females from
(select c.CoId, c.CoNameComplete, count(e.EnId) Males, null Females from Entries e left join Countries c on c.CoId = e.EnCountry and c.CoTournament = e.EnTournament
where e.EnTournament = " . $_SESSION["TourId"] . " and e.EnSex = 0 group by c.CoId
union all
 select c.CoId, c.CoNameComplete, null Males, count(e.EnId) Females from Entries e left join Countries c on c.CoId = e.EnCountry and c.CoTournament = e.EnTournament
 where e.EnTournament = " . $_SESSION["TourId"] . " and e.EnSex = 1 group by c.CoId) t group by CoId, CoNameComplete order by CoNameComplete";
$rs = safe_r_SQL($query);
$participantsByRegion = array();
while ($row = safe_fetch($rs)) {
    $participantsByRegion[$row->CoId]["Name"] = $row->CoNameComplete;
    $participantsByRegion[$row->CoId]["isBasicSport"] = getField("is_basic_sport_" . $row->CoId, false);
    $participantsByRegion[$row->CoId]["Males"] = $row->Males;
    $participantsByRegion[$row->CoId]["Females"] = $row->Females;
    $participantsByRegion[$row->CoId]["Coaches"] = getField("coaches_" . $row->CoId, 0);
}

if (array_key_exists("doPrint", $_REQUEST)) {

} else {
    $IncludeFA = true;
    $IncludeJquery = true;
    $JS_SCRIPT[] = '<script src="'.$CFG->ROOT_DIR.'Tournament/FinalReport/gskReport.js"></script>';
    include('Common/Templates/head.php');
    ?>

<script language="JavaScript">
    const judgesPerRegion = {
        <?php
            $isFirst = true;
            foreach ($judgeRegions as $id => $region) {
                if (!$isFirst) {
                    echo ",\n";
                }
                echo $id . ": " . $region["FromRegion"];
                $isFirst = false;
            }
    ?>}
</script>

<form id="printProtocol" method="GET" action="gskReport.php?doPrint=1" target="_blank">
    <table class="Tabella">
        <tr>
            <td colspan="2" style="text-align: center">Отчет</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center">о проведении <input style="width: 40%" type="text" name="gsk_competitionTitle" value="<?php echo getField("competitionTitle", $_SESSION["TourName"]); ?>" onblur="updateField('competitionTitle', this.value)"/></td>
        </tr>
        <tr>
            <td style="text-align: left; padding-left: 40px">1. Сроки проведения: <b><?php echo $_SESSION['TourWhenFrom'] . " - " . $_SESSION['TourWhenTo']; ?> г.</b></td>
        </tr>
        <tr>
            <td style="text-align: left; padding-left: 40px">2. Место проведения: <b><?php echo $_SESSION['TourWhere']; ?></b></td>
        </tr>
        <tr>
            <td style="text-align: left; padding-left: 40px">3. Наименование спортивного сооружения: <b><?php echo $tournamentData->ToVenue; ?></b></td>
        </tr>
        <tr>
            <td style="text-align: left; padding-left: 40px">4. Всего участников соревнований: <span style="font-weight: bold" id="totalParticipants"><?php echo $participantsStatistics->Total + getField("coachesAndRepresentativesCount", 0); ?></span>, из <b><?php echo $participantsStatistics->RegionsCount; ?></b> региона(ов);</td>
        </tr>
        <tr>
            <td style="text-align: left; padding-left: 40px">Спортсменов <span style="font-weight: bold" id="totalAthletes"><?php echo $participantsStatistics->Total; ?></span> чел., в том числе <b><?php echo $participantsStatistics->Males; ?></b> муж., <b><?php echo $participantsStatistics->Females; ?></b> жен.</td>
        </tr>
        <tr>
            <td style="text-align: left; padding-left: 40px">Представителей, тренеров <input style="width: 3%" type="text" name="coachesAndRepresentatives" value="<?php echo getField("coachesAndRepresentativesCount", 0); ?>" onblur="representativesOnChange(this)" /> чел.</td>
        </tr>
        <tr>
            <td style="text-align: left; padding-left: 40px">5. Количество судей: <span style="font-weight: bold" id="judgesTotal"><?php echo $judgesData->Total; ?></span>, в том числе иногородних: <span style="font-weight: bold" id="nonLocalJudges"><?php echo $judgesData->NonLocal; ?></span></td>
        </tr>
        <tr>
            <td style="text-align: left; padding-left: 40px">Уровень подготовки судей по судейским категориям: <?php
                    $isFirst = true;
                    foreach ($judgesAccreditation as $accreditation => $count) {
                        if (!$isFirst) {
                            echo ", ";
                        }
                        echo get_text("JudgeAccreditation_" . $accreditation, "Tournament") . ": <b>" . $count . "</b>";
                        $isFirst = false;
                    }
                ?>
        </tr>
        <tr>
            <td style="text-align: left; padding-left: 80px">Выберите регион, судьи из которого не считаются иногородними: <select id="judgesRegions" onchange="judgesHomeRegionChanged(this)"><?php
                    $homeRegionId = getField("localCountryIdForJudges");
                    foreach ($judgeRegions as $id => $region) {
                        echo "<option value='" . $id . "'" . ($id == $homeRegionId ? " selected='selected'" : "") . ">" . $region["Name"] . "</option>";
                    }
                    ?></select>
        </tr>
        <tr><td style="text-align: left; padding-left: 40px">6. Состав участвующих команд (регионов), в том числе количество спортсменов, тренеров и другого обслуживающего персонала:</td></tr>
        <tr>
            <td style="text-align: left; padding-left: 40px">
                <table class="Tabella w-30">
                    <tr><td rowspan="2">№ п/п</td><td rowspan="2">Команда (субъект РФ)</td><td rowspan="2">Базовый вид</td><td colspan="3">Спортсмены, чел.</td><td rowspan="2">Тренеры и др. обсл. персонал, чел.</td><td rowspan="2">Всего</td></tr>
                    <tr><td>М</td><td>Ж</td><td>Всего</td></tr>
                    <?php
                        $index = 1;
                        $tabIndexOffset = count($participantsByRegion);
                        foreach ($participantsByRegion as $id => $data) {
                            echo "<tr><td>" . $index . "</td><td>" . $data["Name"] . "</td><td><input type='checkbox' tabindex='" . $index . "' name='is_basic_sport_" . $id . "'" . ($data["isBasicSport"] ? "checked='checked'" : '') . " onclick='toggleBasicSport(this)'/></td><td>" . $data["Males"] . "</td><td>" . $data["Females"] . "</td><td id='athletesTotal_" . $id . "'>" . $data["Males"] + $data["Females"] . "</td><td><input class='w-15' id='" . $id . "' type='text' tabIndex='" . $index + $tabIndexOffset . "' name='coaches_" . $id . "' value='" . $data["Coaches"] . "' onblur='coachesChanged(this)'/></td><td id='regionTotal_" . $id . "'>" . $data["Males"] + $data["Females"] + $data["Coaches"] . "</td></tr>\n";
                            ++$index;
                        }
                    ?>
                </table>
            </td>
        </tr>
    </table>
</form>


<?php
    include('Common/Templates/tail.php');
}
?>
