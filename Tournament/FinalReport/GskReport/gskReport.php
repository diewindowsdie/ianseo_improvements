<?php

error_reporting(E_ALL);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/pdf/LabelPDF.inc.php');
require_once("Common/Lib/Normative/NormativeStatistics.php");
require_once('Common/pdf/PdfChunkLoader.php');
require_once("Tournament/FinalReport/GskReport/GskFields.php");
require_once("Tournament/FinalReport/GskReport/fields/IsBasicRegionGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/NumberOfCoachesFromRegion.php");

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
$totalCoaches = 0;
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
    $participantsByRegion[$row->CoId]["isBasicSport"] = (new IsBasicRegionGskField($row->CoId))->getValue();
    $participantsByRegion[$row->CoId]["Males"] = $row->Males;
    $participantsByRegion[$row->CoId]["Females"] = $row->Females;
    $participantsByRegion[$row->CoId]["Coaches"] = (new NumberOfCoachesFromRegion($row->CoId))->getValue();
    $totalCoaches += $participantsByRegion[$row->CoId]["Coaches"];
}

$query = "select ClId, ClDescription from Classes where ClTournament = " . StrSafe_DB($_SESSION['TourId']);
$rs = safe_r_SQL($query);
$classes = array();
while ($row = safe_fetch($rs)) {
    $classes[$row->ClId] = $row->ClDescription;
}

//звания
$query = "select ScId, ScDescription from SubClass where ScTournament = " . StrSafe_DB($_SESSION['TourId']) . " order by ScViewOrder desc";
$rs = safe_r_SQL($query);
$subclasses = array();
while ($row = safe_fetch($rs)) {
    $subclasses[$row->ScId] = $row->ScDescription;
}

//статистика по имеющимся разрядам
$query = "SELECT EnClass, EnSubClass, ScId, count(*) as numArchers 
    FROM Entries 
    LEFT JOIN Classes ON EnClass=ClId AND ClTournament=EnTournament
    LEFT JOIN SubClass ON EnSubClass=ScId AND ScTournament=EnTournament
    WHERE EnTournament=" . StrSafe_DB($_SESSION['TourId']) . " 
    GROUP BY ClViewOrder, ScViewOrder, EnClass, EnSubClass 
    ORDER BY ClViewOrder, ScViewOrder, EnClass, EnSubClass";
$rs = safe_r_SQL($query);
$subclassStatistics = array();
while ($row = safe_fetch($rs)) {
    $subclassStatistics[$row->ScId][$row->EnClass] = $row->numArchers;
}

function getParticipansFromOrganisationCount($patterns) {
    $firstQueryPart = true;
    $queryPart = "";
    foreach ($patterns as $pattern) {
        if (!$firstQueryPart) {
            $queryPart .= " or ";
        }
        $firstQueryPart = false;
        $queryPart .= "c2.CoNameComplete rlike '" . $pattern . "' or c2.CoName rlike '" . $pattern . "' or c3.CoNameComplete rlike '" . $pattern . "' or c3.CoName rlike '" . $pattern . "'";
    }
    $query = "select count(*) Count from Entries e
                         left join Countries c2 on c2.CoId = e.EnCountry2 and c2.CoTournament = e.EnTournament
                         left join Countries c3 on c3.CoId = e.EnCountry3 and c3.CoTournament = e.EnTournament
                where e.EnTournament = " . StrSafe_DB($_SESSION['TourId']) . " and (" . $queryPart . ")";
    return safe_fetch(safe_r_SQL($query))->Count;
}

$participantsPerOrganisation = array();
//вооруженные силы
$participantsPerOrganisation["armedForces"] = getParticipansFromOrganisationCount(["^(?i).*ЦСКА.*$", "^(?i).*Динамо.*$"]); //todo (?i) не работает на кириллице
//Динамо
$participantsPerOrganisation["dinamo"] = getParticipansFromOrganisationCount(["^(?i).*Динамо.*$"]);
//спортивные клубы - из поиска исключим паттерн "ЦСКА"
$participantsPerOrganisation["clubs"] = getParticipansFromOrganisationCount(["^(?!.*ЦСКА)(?=.*СК).*$"]);
//спортивные школы
$participantsPerOrganisation["sportSchools"] = getParticipansFromOrganisationCount(["^.*СШ.*$"]);
//спортивные школы олимпийского резерва
$participantsPerOrganisation["sportSchoolsOlympic"] = getParticipansFromOrganisationCount(["^.*СШОР.*$"]);
//училища олимпийского резерва
$participantsPerOrganisation["sportFacilitiesOlympic"] = getParticipansFromOrganisationCount(["^(?i).*УОР.*$"]);

if (array_key_exists("doPrint", $_REQUEST)) {
    $pdf = new LabelPDF();
    $pdf->setMargins(10, 10, 10);
    $pdf->setFontSize(10);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->setAutoPageBreak(true);

    $pdf->AddPage();

    $pdf->Cell(190, 6, "Отчет", 0, 1, 'C');
    $pdf->Cell(190, 6, "о проведении " . GskFields::getCompetitionTitle()->getValue(), 0, 1, 'C');

    $pdf->Cell(190, 10, "", 0, 1, 'C');

    $pdf->writeHTMLCell(190, 7, null, null, "1. Сроки проведения: <b>" . $_SESSION['TourWhenFrom'] . " - " . $_SESSION['TourWhenTo'] . "</b>", 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 7, null, null, "2. Место проведения: <b>" . $_SESSION['TourWhere'] . "</b>", 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 7, null, null, "3. Наименование спортивного сооружения: <b>" . $tournamentData->ToVenue . "</b>", 0, 1, 0, 1, 'L');

    $pdf->writeHTMLCell(190, 5, null, null, "4. Всего участников соревнований: <b>" . $participantsStatistics->Total + $totalCoaches . "</b>, из <b>" . $participantsStatistics->RegionsCount . "</b> регион(ов);", 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 5, 10, null, "Спортсменов <b>" . $participantsStatistics->Total . "</b> чел., в том числе <b>" . $participantsStatistics->Males . "</b> муж., <b>" . $participantsStatistics->Females . "</b> жен.", 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 7, 10, null, "Представителей, тренеров <b>" . $totalCoaches . "</b> чел.", 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 5, null, null, "5. Количество судей: <b>" . $judgesData->Total . "</b>, в том числе иногородних: <b>" . $judgesData->NonLocal . "</b>" , 0, 1, 0, 1, 'L');
    $judgesDetails = "Уровень подготовки судей по судейским категориям: ";
    $isFirst = true;
    foreach ($judgesAccreditation as $accreditation => $count) {
        if (!$isFirst) {
            $judgesDetails .= ", ";
        }
        $judgesDetails .= get_text("JudgeAccreditation_" . $accreditation, "Tournament") . ": <b>" . $count . "</b>";
        $isFirst = false;
    }
    $pdf->writeHTMLCell(190, 7, 10, null, $judgesDetails, 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 5, null, null, "6. Состав участвующих команд (регионов), в том числе количество спортсменов, тренеров и другого обслуживающего персонала:", 0, 1, 0, 1, 'L');
    $pdf->setY($pdf->GetY() + 1);
    $pdf->SetFont($pdf->FontStd,'B', 9);
    $pdf->Cell(10, 10, "№ п/п", 1, 0, "C");
    $pdf->Cell(65, 10, "Команда (субъект РФ)", 1, 0, "C");
    $pdf->Cell(15, 5, "Базовый", "RLT", 0, "C", 0, "", 1, false, "T", "B");
    $pdf->Cell(60, 5, "Спортсмены, чел.", 1, 0, "C");
    $pdf->Cell(20, 5, "Тренеры и др.", "RLT", 0, "C", 0, "", 1, false, "T", "B");
    $pdf->Cell(20, 10, "Всего", 1, 1, "C");
    $pdf->SetXY($pdf->getX() + 75, $pdf->GetY() - 5);
    $pdf->Cell(15, 5, "вид", "RLB", 0, "C", 0, "", 1, false, "T", "T");
    $pdf->Cell(20, 5, "М", 1, 0, "C");
    $pdf->Cell(20, 5, "Ж", 1, 0, "C");
    $pdf->Cell(20, 5, "Всего", 1, 0, "C");
    $pdf->Cell(20, 5, "обсл. персонал, чел.", "RLB", 1, "C", 0, "", 1, false, "T", "T");
    $pdf->SetFont($pdf->FontStd,'', 9);
    $index = 1;
    foreach ($participantsByRegion as $id => $data) {
        $pdf->Cell(10, 5, $index, 1, 0, 'C');
        $pdf->Cell(65, 5, $data["Name"], 1, 0, 'L');
        $pdf->Cell(15, 5, $data["isBasicSport"] ? "✔" : "", 1, 0, 'C');
        $pdf->Cell(20, 5, $data["Males"], 1, 0, 'C');
        $pdf->Cell(20, 5, $data["Females"], 1, 0, 'C');
        $pdf->Cell(20, 5, $data["Males"] + $data["Females"], 1, 0, 'C');
        $pdf->Cell(20, 5, $data["Coaches"], 1, 0, 'C');
        $pdf->Cell(20, 5, $data["Males"] + $data["Females"] + $data["Coaches"], 1, 1, 'C');
        ++$index;
    }
    $pdf->SetFont($pdf->FontStd,'B', 9);
    $pdf->Cell(10, 5, "", 1, 0, 'C');
    $pdf->Cell(65, 5, "Всего", 1, 0, 'L');
    $pdf->Cell(15, 5, "", 1, 0, 'C');
    $pdf->Cell(20, 5, $participantsStatistics->Males, 1, 0, 'C');
    $pdf->Cell(20, 5, $participantsStatistics->Females, 1, 0, 'C');
    $pdf->Cell(20, 5, $participantsStatistics->Total, 1, 0, 'C');
    $pdf->Cell(20, 5, $totalCoaches, 1, 0, 'C');
    $pdf->Cell(20, 5, $participantsStatistics->Total + $totalCoaches, 1, 1, 'C');

    if (!$pdf->SamePage(2+5+1+10+5*count($subclasses))) { //отступ от предыдущей таблицы + текст + отступ до таблицы + заголовок таблицы + 5 пунктов на каждый разряд
        $pdf->AddPage();
    }
    $pdf->SetFont($pdf->FontStd,'', 10);
    $pdf->writeHTMLCell(190, 2, null, null, "", 0, 1, 0, 1, 'L'); //отступ
    $pdf->writeHTMLCell(190, 5, null, null, "7. Уровень подготовки спортсменов:", 0, 1, 0, 1, 'L');
    $groupSize = (190 - 15 - 15) / count($classes);
    $pdf->setY($pdf->GetY() + 1);
    $pdf->SetFont($pdf->FontStd,'B', 9);
    $pdf->Cell(15, 10, "", 1, 0, "C");
    $pdf->Cell($groupSize * count($classes), 5, "Возрастные группы в соответствии с ЕВСК", 1, 0, "C");
    $pdf->Cell(15, 10, "Всего", 1, 1, "C");
    $pdf->setY($pdf->GetY() - 5);
    $pdf->setX($pdf->GetX() + 15);
    foreach ($classes as $id => $description) {
        $pdf->Cell($groupSize, 5, $description, 1, 0, "C");
    }
    $pdf->ln();
    foreach ($subclasses as $subclassId => $subclassDescription) {
        $pdf->SetFont($pdf->FontStd,'B', 9);
        $pdf->Cell(15, 5, $subclassDescription, 1, 0, "C");
        $subclassTotal = 0;
        $pdf->SetFont($pdf->FontStd,'', 9);
        foreach ($classes as $id => $description) {
            $subclassForGroup = $subclassStatistics[$subclassId][$id] ?? "0";
            $subclassTotal += $subclassForGroup;
            $pdf->Cell($groupSize, 5, $subclassTotal, 1, 0, "C");
        }
        $pdf->Cell(15, 5, $subclassTotal, 1, 1, "C");
    }
    $pdf->SetFont($pdf->FontStd,'', 10);

    $pdf->writeHTMLCell(190, 2, null, null, "", 0, 1, 0, 1, 'L'); //отступ
    $pdf->writeHTMLCell(190, 5, null, null, "8. Представительство спортивных организаций:", 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 7, 10, null, "Вооруженные силы: <b>" . $participantsPerOrganisation["armedForces"] . "</b>, \"Динамо\": <b>" . $participantsPerOrganisation["dinamo"] . "</b>, спортивные клубы (СК): <b>" . $participantsPerOrganisation["clubs"] . "</b>", 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 5, null, null, "9. Принадлежность к спортивной школе:", 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 7, 10, null, "СШ: <b>" . $participantsPerOrganisation["sportSchools"] . "</b>, СШОР: <b>" . $participantsPerOrganisation["sportSchoolsOlympic"] . "</b>, УОР: <b>" . $participantsPerOrganisation["sportFacilitiesOlympic"] . "</b>", 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 5, null, null, "10. Выполнение (подтверждение) нормативов (количество показанных результатов):", 0, 1, 0, 1, 'L');

    $athleteNormatives = "";
    $first = true;
    foreach(NormativeStatistics::getNormativeStatistics()["normativeTotals"] as $normative => $count) {
        if (!$first) {
            $athleteNormatives .= ", ";
        }
        $first = false;
        $athleteNormatives .= ($normative . ": <b> " . $count . "</b>");
    }
    $pdf->writeHTMLCell(190, 7, 10, null, $athleteNormatives, 0, 1, 0, 1, 'L');

    $pdf->writeHTMLCell(190, 5, null, null, "11. Результаты соревнований:", 0, 1, 0, 1, 'L');
    $PdfData=getMedalList();
    $PdfData->HideOfficials = true;
    require_once(PdfChunkLoader('MedalList.inc.php'));

    $pdf->setY($pdf->GetY() + 4);
    $pdf->SetFont($pdf->FontStd,'', 10);
    $pdf->writeHTMLCell(190, 5, null, null, "12. Количество субъектов Российской Федерации команд (перечислить территории согласно  занятым местам):", 0, 1, 0, 1, 'L');
    $PdfData=getMedalStand();
    require_once(PdfChunkLoader('MedalStand.inc.php'));

    $pdf->Output("Отчет ГСК.pdf");
} else {
    $IncludeFA = true;
    $IncludeJquery = true;
    $JS_SCRIPT[] = '<script src="'.$CFG->ROOT_DIR.'Tournament/FinalReport/GskReport/gskReport.js"></script>';
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
    ?>};

    const coachesPerRegion = {
        <?php
            $isFirst = true;
            foreach ($participantsByRegion as $id => $data) {
                if (!$isFirst) {
                    echo ",\n";
                }
                echo $id . ": " . $data["Coaches"];
                $isFirst = false;
            }
        ?>};
</script>

<form id="printProtocol" method="GET" action="gskReport.php?doPrint=1" target="_blank">
    <table class="Tabella">
        <tr>
            <td colspan="2" style="text-align: left; padding-left: 40px">Отчет о проведении <input style="width: 40%" type="text" name="gsk_competitionTitle" value="<?php echo GskFields::getCompetitionTitle()->getValue(); ?>" onblur="updateField('<?php echo GskFields::getCompetitionTitle()->getParameterName(); ?>', this.value)"/></td>
        </tr>
        <tr></tr>
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
            <td style="text-align: left; padding-left: 40px">4. Всего участников соревнований: <span style="font-weight: bold" id="totalParticipants"><?php echo $participantsStatistics->Total + $totalCoaches; ?></span>, из <b><?php echo $participantsStatistics->RegionsCount; ?></b> региона(ов);</td>
        </tr>
        <tr>
            <td style="text-align: left; padding-left: 40px">Спортсменов <span style="font-weight: bold" id="totalAthletes"><?php echo $participantsStatistics->Total; ?></span> чел., в том числе <b><?php echo $participantsStatistics->Males; ?></b> муж., <b><?php echo $participantsStatistics->Females; ?></b> жен.</td>
        </tr>
        <tr>
            <td style="text-align: left; padding-left: 40px">Представителей, тренеров <span style="font-weight: bold" id="totalCoaches"><?php echo $totalCoaches; ?></span> чел.</td>
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
            <td style="text-align: left; padding-left: 80px">Выберите регион, судьи из которого не считаются иногородними:
            <?php
                $localRegionIdForJudgesField = GskFields::getLocalRegionIdForJudges();
                echo '<select id="judgesRegions" onchange="judgesHomeRegionChanged(\'' . $localRegionIdForJudgesField->getParameterName() . '\', this)">';
                $homeRegionId = $localRegionIdForJudgesField->getValue();
                foreach ($judgeRegions as $id => $region) {
                    echo "<option value='" . $id . "'" . ($id == $homeRegionId ? " selected='selected'" : "") . ">" . $region["Name"] . "</option>";
                }
            ?></select></td>
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
                            $isBasicParameterName = IsBasicRegionGskField::getParameterNameForRegion($id);
                            $numberOfCoachesParameterName = NumberOfCoachesFromRegion::getParameterNameForRegion($id);
                            echo "<tr><td>" . $index . "</td><td>" . $data["Name"] . "</td><td><input type='checkbox' tabindex='" . $index . "' name='" . $isBasicParameterName . "'" . ($data["isBasicSport"] ? "checked='checked'" : '') . " onclick='toggleBasicSport(this)'/></td><td>" . $data["Males"] . "</td><td>" . $data["Females"] . "</td><td id='athletesTotal_" . $id . "'>" . $data["Males"] + $data["Females"] . "</td><td><input class='w-15' id='" . $id . "' type='text' tabIndex='" . $index + $tabIndexOffset . "' name='" . $numberOfCoachesParameterName . "' value='" . $data["Coaches"] . "' onblur='coachesChanged(this)'/></td><td id='regionTotal_" . $id . "'>" . $data["Males"] + $data["Females"] + $data["Coaches"] . "</td></tr>\n";
                            ++$index;
                        }
                    ?>
                </table>
            </td>
        </tr>
        <tr><td style="text-align: left; padding-left: 40px">7. Уровень подготовки спортсменов:</td></tr>
        <tr>
            <td style="text-align: left; padding-left: 40px">
                <table class="Tabella w-30">
                    <tr><td rowspan="2"></td><td colspan="<?php echo count($classes); ?>">Возрастные группы в соответствии с ЕВСК</td><td rowspan="2">Всего</td></tr>
                    <?php
                        echo "<tr>\n";
                        foreach ($classes as $id => $description) {
                            echo "<td>" . $description . "</td>";
                        }
                        echo "</tr>\n";
                        foreach ($subclasses as $subclassId => $subclassDescription) {
                            echo "<tr><td>" . $subclassDescription . "</td>";
                            $subclassTotal = 0;
                            foreach ($classes as $id => $description) {
                                $subclassForGroup = $subclassStatistics[$subclassId][$id] ?? "0";
                                $subclassTotal += $subclassForGroup;
                                echo "<td>" . $subclassForGroup . "</td>";
                            }
                            echo "<td>" . $subclassTotal . "</td></tr>\n";
                        }
                    ?>
                </table>
            </td>
        </tr>
        <tr><td style="text-align: left; padding-left: 40px">8. Представительство спортивных организаций:</td></tr>
        <tr><td style="text-align: left; padding-left: 40px">Вооруженные силы: <b><?php echo $participantsPerOrganisation["armedForces"]; ?></b>, "Динамо": <b><?php echo $participantsPerOrganisation["dinamo"]; ?></b>, спортивные клубы (СК): <b><?php echo $participantsPerOrganisation["clubs"]; ?></b></td></tr>
        <tr><td style="text-align: left; padding-left: 40px">9. Принадлежность к спортивной школе:</td></tr>
        <tr><td style="text-align: left; padding-left: 40px">СШ: <b><?php echo $participantsPerOrganisation["sportSchools"]; ?></b>, СШОР: <b><?php echo $participantsPerOrganisation["sportSchoolsOlympic"]; ?></b>, УОР: <b><?php echo $participantsPerOrganisation["sportFacilitiesOlympic"]; ?></b></td></tr>
        <tr><td style="text-align: left; padding-left: 40px">10. Выполнение (подтверждение) нормативов (количество показанных результатов):</td></tr>
        <tr><td style="text-align: left; padding-left: 40px">
                <?php
                    $first = true;
                    foreach(NormativeStatistics::getNormativeStatistics()["normativeTotals"] as $normative => $count) {
                        if (!$first) {
                            echo ", ";
                        }
                        $first = false;
                        echo $normative . ": <b> " . $count . "</b>";
                    }
                ?>
            </td></tr>
        <tr><td style="text-align: left; padding-left: 40px">13. Общая оценка состояния спортивной базы, наличие и состояние спортивного оборудования и инвентаря, возможности для разминки и тренировок:</td></tr>
        <tr><td style="text-align: left; padding-left: 40px"><textarea style="width: 40%"  rows=3 name="general_issues" onblur="updateField('general_issues', this.value)"><?php echo getField("general_issues", "Состояние спортивной базы соответствует требованиям техники безопасности и правил проведения соревнований по стрельбе из лука"); ?></textarea></td></tr>
        <tr><td style="text-align: left; padding-left: 40px">14. Общая оценка состояния и оснащения служебных помещений - раздевалок для спортсменов, помещений для судей и других служб:</td></tr>
        <tr><td style="text-align: left; padding-left: 40px"><textarea style="width: 40%"  rows=3 name="service_room_issues" onblur="updateField('service_room_issues', this.value)"><?php echo getField("service_room_issues", "Состояние и оснащение служебных помещений соответствует требованиям"); ?></textarea></td></tr>
        <tr><td style="text-align: left; padding-left: 40px">15. Информационное обеспечение соревнований - табло, радиоинформация, своевременность и доступность стартовых протоколов и результатов соревнований, обеспечение судейской коллегии средствами вычислительной техники и множительной аппаратурой:</td></tr>
        <tr><td style="text-align: left; padding-left: 40px"><textarea style="width: 40%"  rows=3 name="information_services" onblur="updateField('information_services', this.value)"><?php echo getField("information_services", "Информационное обеспечение соревнований – радио-информация, выпуск стартовых протоколов, результатов соревнований, а также обеспечение вычислительной техникой и множительной аппаратурой предоставлялось своевременно"); ?></textarea></td></tr>
        <tr><td style="text-align: left; padding-left: 40px">16. Обеспечение работы средств массовой информации - места на трибунах, помещение для пресс-центра и т.д., в том числе освещение соревнования в местных СМИ (копии публикаций в СМИ прилагаются):</td></tr>
        <tr><td style="text-align: left; padding-left: 40px"><textarea style="width: 40%"  rows=3 name="smi_issues" onblur="updateField('smi_issues', this.value)"><?php echo getField("smi_issues", "Сотрудникам СМИ были предоставлены места на трибунах, а также помещения для расположения пресс-центра. Ход соревнований освещался в местных СМИ"); ?></textarea></td></tr>
        <tr><td style="text-align: left; padding-left: 40px">17. Количество зрителей: <input type="text" name="viewers_amount" value="<?php echo getField("viewers_amount", "0"); ?>" onblur="updateField('viewers_amount', this.value)"/> чел.</td></tr>
        <tr><td style="text-align: left; padding-left: 40px">18. Общая оценка качества проведения соревнований - точность соблюдения расписания, объективность судейства (с указанием нарушений правил соревнований и т.д.):</td></tr>
        <tr><td style="text-align: left; padding-left: 40px"><textarea style="width: 40%"  rows=3 name="general_organisation" onblur="updateField('general_organisation', this.value)"><?php echo getField("general_organisation", "Объективность судейства и точность расписания соблюдались на протяжении всех дней соревнований"); ?></textarea></td></tr>
        <tr><td style="text-align: left; padding-left: 40px">19. Медицинское обеспечение соревнований, в том числе сведения о травмах и других несчастных случаях:</td></tr>
        <tr><td style="text-align: left; padding-left: 40px"><textarea style="width: 40%"  rows=3 name="medical_issues" onblur="updateField('medical_issues', this.value)"><?php echo getField("medical_issues", "Травм и заболеваний не было, на соревнованиях присутствовал врач с дежурной бригадой скорой помощи"); ?></textarea></td></tr>
        <tr><td style="text-align: left; padding-left: 40px">20. Общая оценка качества размещения, питания, транспортного обслуживания, организации встреч и проводов спортивных делегаций, шефская работа и т.п.:</td></tr>
        <tr><td style="text-align: left; padding-left: 40px"><textarea style="width: 40%"  rows=3 name="guest_delegation_issues" onblur="updateField('guest_delegation_issues', this.value)"><?php echo getField("guest_delegation_issues", "Размещением все делегации были обеспечены, транспортное обслуживание осуществлялось в соответствии с расписанием"); ?></textarea></td></tr>
        <tr><td style="text-align: left; padding-left: 40px">21. Общая оценка соблюдения мер по обеспечению безопасности при проведении соревнования:</td></tr>
        <tr><td style="text-align: left; padding-left: 40px"><textarea style="width: 40%"  rows=3 name="security_notes" onblur="updateField('security_notes', this.value)"><?php echo getField("security_notes", 'Обеспечение безопасности участников соревнований осуществлялось в соответствии с правилами вида спорта «стрельба из лука», утвержденных приказом Министерства спорта Российской Федерации от 29.12.2020г. N 984, Постановлением Правительства Российской Федерации N 353 от 18.04.2014г. «Об утверждении Правил обеспечения безопасности при проведении официальных спортивных соревнований» и приказом Министерства здравоохранения Российской Федерации от 23.10.2020 N 1144н "Об утверждении порядка организации оказания медицинской помощи лицам, занимающимся физической культурой и спортом (в том числе при подготовке и проведении физкультурных мероприятий и спортивных мероприятий), включая порядок медицинского осмотра лиц, желающих пройти спортивную подготовку, заниматься физической культурой и спортом в организациях и (или) выполнить нормативы испытаний (тестов) Всероссийского физкультурно-спортивного комплекса "Готов к труду и обороне" (ГТО)" и форм медицинских заключений о допуске к участию физкультурных и спортивных мероприятиях'); ?></textarea></td></tr>
        <tr><td style="text-align: left; padding-left: 40px">22. Выводы и предложения (замечания) по подготовке и проведению соревнования:</td></tr>
        <tr><td style="text-align: left; padding-left: 40px"><textarea style="width: 40%"  rows=3 name="other_notes" onblur="updateField('other_notes', this.value)"><?php echo getField("other_notes", 'Замечаний нет'); ?></textarea></td></tr>

    </table>
</form>


<?php
    include('Common/Templates/tail.php');
}
?>
