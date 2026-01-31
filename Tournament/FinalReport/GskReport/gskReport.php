<?php

global $CFG;

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/pdf/LabelPDF.inc.php');
require_once("Common/Lib/Normative/NormativeStatistics.php");
require_once("Common/Lib/TournamentOfficials.php");
require_once('Common/pdf/PdfChunkLoader.php');
require_once("Tournament/FinalReport/GskReport/GskFields.php");
require_once("Tournament/FinalReport/GskReport/fields/IsBasicRegionGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/NumberOfCoachesFromRegion.php");

checkFullACL(AclCompetition, 'acStandard', AclReadWrite);

if (!CheckTourSession()) {
    CD_redirect($CFG->ROOT_DIR);
}

function getResetInputJs(GskField $gskField): string {
    return "resetInput('" . $gskField->getParameterName() . "', '" . htmlspecialchars($gskField->getDefaultValue()) . "')";
}

//общая информация о соревновании
$query = "select * from Tournament where ToId = " . $_SESSION["TourId"];
$rs = safe_r_SQL($query);
$tournamentData = safe_fetch($rs);

//статистика по спортсменам по полу, и по регионам - тут только спортсмены
$query = "select count(Males) Males, count(Females) Females, count(*) Total, count(distinct EnCountry) RegionsCount from (
    select e.EnId Males, null Females, e.EnCountry from Entries e 
        inner join Divisions d on e.EnDivision = d.DivId and d.DivTournament = e.EnTournament
        inner join Classes cl on e.EnClass = cl.ClId and cl.ClTournament = e.EnTournament
            where d.DivAthlete = 1 and cl.ClAthlete = 1 and e.EnSex = 0 and e.EnTournament = " . $_SESSION["TourId"] . "
        union
        select null Males, e.EnId Females, e.EnCountry from Entries e 
        inner join Divisions d on e.EnDivision = d.DivId and d.DivTournament = e.EnTournament
        inner join Classes cl on e.EnClass = cl.ClId and cl.ClTournament = e.EnTournament
            where d.DivAthlete = 1 and cl.ClAthlete = 1 and e.EnSex = 1 and e.EnTournament = " . $_SESSION["TourId"] . ") t";
$rs = safe_r_SQL($query);
$athletesStatistics = safe_fetch($rs);

//общая статистика по судьям
$query = "select count(NonLocal) NonLocal, count(Total) Total from (
    select TiId Total, null NonLocal from TournamentInvolved where TiTournament = " . $_SESSION["TourId"] . "
        union
    select null Total, ti.TiId NonLocal from TournamentInvolved ti left join Countries c on ti.TiCountry = c.CoId and ti.TiTournament = c.CoTournament where ti.TiTournament = " . $_SESSION["TourId"] . " and c.CoCode != '" . GskFields::getLocalRegionCodeForJudges()->getValue() . "') t";
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
$query = "select c.CoCode, c.CoNameComplete, count(*) FromRegion from TournamentInvolved ti 
    left join Countries c on ti.TiCountry = c.CoId and ti.TiTournament = c.CoTournament where ti.TiTournament = " . $_SESSION["TourId"] . " group by ti.TiCountry, c.CoNameComplete order by c.CoNameComplete";
$rs = safe_r_SQL($query);
$judgeRegions = array();
while ($row = safe_fetch($rs)) {
    $judgeRegions[$row->CoCode]["Name"] = $row->CoNameComplete;
    $judgeRegions[$row->CoCode]["FromRegion"] = $row->FromRegion;
}

//главный судья и главный секретарь
$query = "select TiName, TiGivenName, TiLastName from TournamentInvolved where TiType = '5' and TiTournament = " . $_SESSION["TourId"];
$rs = safe_r_SQL($query);
$chairpersonOfJudges = safe_fetch($rs);
$query = "select TiName, TiGivenName, TiLastName from TournamentInvolved where TiType = '23' and TiTournament = " . $_SESSION["TourId"];
$rs = safe_r_SQL($query);
$chiefSecretary = safe_fetch($rs);

//статистика по регионам
$totalCoaches = 0;
$query = "select CoCode, CoNameComplete, sum(coalesce(Males, 0)) Males, sum(coalesce(Females, 0)) Females from
(select c.CoCode, c.CoNameComplete, count(e.EnId) Males, null Females from Entries e 
    left join Countries c on c.CoId = e.EnCountry and c.CoTournament = e.EnTournament
    inner join Divisions d on e.EnDivision = d.DivId and d.DivTournament = e.EnTournament
    inner join Classes cl on e.EnClass = cl.ClId and cl.ClTournament = e.EnTournament
        where d.DivAthlete = 1 and cl.ClAthlete = 1 and e.EnTournament = " . $_SESSION["TourId"] . " and e.EnSex = 0 group by c.CoCode
union all
 select c.CoCode, c.CoNameComplete, null Males, count(e.EnId) Females from Entries e 
    left join Countries c on c.CoId = e.EnCountry and c.CoTournament = e.EnTournament
    inner join Divisions d on e.EnDivision = d.DivId and d.DivTournament = e.EnTournament
    inner join Classes cl on e.EnClass = cl.ClId and cl.ClTournament = e.EnTournament
        where d.DivAthlete = 1 and cl.ClAthlete = 1 and e.EnTournament = " . $_SESSION["TourId"] . " and e.EnSex = 1 group by c.CoCode) t group by CoCode, CoNameComplete order by CoNameComplete";
$rs = safe_r_SQL($query);
$participantsByRegion = array();
while ($row = safe_fetch($rs)) {
    $participantsByRegion[$row->CoCode]["Name"] = $row->CoNameComplete;
    $participantsByRegion[$row->CoCode]["isBasicSport"] = (new IsBasicRegionGskField($row->CoCode))->getValue();
    $participantsByRegion[$row->CoCode]["Males"] = $row->Males;
    $participantsByRegion[$row->CoCode]["Females"] = $row->Females;
    $participantsByRegion[$row->CoCode]["Coaches"] = (new NumberOfCoachesFromRegion($row->CoCode))->getValue();
    $totalCoaches += $participantsByRegion[$row->CoCode]["Coaches"];
}

$query = "select ClId, ClDescription from Classes where ClAthlete = 1 and ClTournament = " . StrSafe_DB($_SESSION['TourId']);
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
$query = "SELECT e.EnClass, e.EnSubClass, s.ScId, count(*) as numArchers 
    FROM Entries e
        inner join Divisions d on d.DivId = e.EnDivision and d.DivTournament = e.EnTournament
        inner join Classes cl ON e.EnClass=cl.ClId AND cl.ClTournament=e.EnTournament
        LEFT JOIN SubClass s ON e.EnSubClass=s.ScId AND s.ScTournament=e.EnTournament
    WHERE d.DivAthlete = 1 and cl.ClAthlete = 1 and e.EnTournament=" . StrSafe_DB($_SESSION['TourId']) . " 
    GROUP BY cl.ClViewOrder, s.ScViewOrder, e.EnClass, e.EnSubClass 
    ORDER BY cl.ClViewOrder, s.ScViewOrder, e.EnClass, e.EnSubClass";
$rs = safe_r_SQL($query);
$subclassStatistics = array();
while ($row = safe_fetch($rs)) {
    $subclassStatistics[$row->ScId][$row->EnClass] = $row->numArchers;
}

function getParticipantsFromOrganisationCount($patterns) {
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
                inner join Divisions d on d.DivId = e.EnDivision and d.DivTournament = e.EnTournament
                inner join Classes cl ON e.EnClass=cl.ClId AND cl.ClTournament=e.EnTournament
                left join Countries c2 on c2.CoId = e.EnCountry2 and c2.CoTournament = e.EnTournament
                left join Countries c3 on c3.CoId = e.EnCountry3 and c3.CoTournament = e.EnTournament
                    where d.DivAthlete = 1 and cl.ClAthlete = 1 and e.EnTournament = " . StrSafe_DB($_SESSION['TourId']) . " and (" . $queryPart . ")";
    return safe_fetch(safe_r_SQL($query))->Count;
}

$participantsPerOrganisation = array();
//вооруженные силы
$participantsPerOrganisation["armedForces"] = getParticipantsFromOrganisationCount(["^(?i).*ЦСКА.*$", "^(?i).*Динамо.*$"]); //todo (?i) не работает на кириллице
//Динамо
$participantsPerOrganisation["dinamo"] = getParticipantsFromOrganisationCount(["^(?i).*Динамо.*$"]);
//спортивные клубы - из поиска исключим паттерн "ЦСКА"
$participantsPerOrganisation["clubs"] = getParticipantsFromOrganisationCount(["^(?!.*ЦСКА)(?=.*СК).*$"]);
//спортивные школы
$participantsPerOrganisation["sportSchools"] = getParticipantsFromOrganisationCount(["^.*СШ.*$"]);
//спортивные школы олимпийского резерва
$participantsPerOrganisation["sportSchoolsOlympic"] = getParticipantsFromOrganisationCount(["^.*СШОР.*$"]);
//училища олимпийского резерва
$participantsPerOrganisation["sportFacilitiesOlympic"] = getParticipantsFromOrganisationCount(["^(?i).*УОР.*$"]);

if (array_key_exists("doPrint", $_REQUEST)) {
    $pdf = new LabelPDF();
    $pdf->setMargins(10, 10, 10, );
    $pdf->setAutoPageBreak(true, 10);
    $pdf->setFontSize(10);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->AddPage();

    $pdf->Cell(190, 6, "Отчет", 0, 1, 'C');
    $pdf->writeHTMLCell(190, 6, null, null, "о проведении <b>" . GskFields::getCompetitionTitle()->getValue() . "</b>", 0, 1, 0, 1, 'C');

    $pdf->Cell(190, 10, "", 0, 1, 'C');

    $pdf->writeHTMLCell(190, 7, null, null, "1. Сроки проведения: <b>" . $_SESSION['TourWhenFrom'] . " - " . $_SESSION['TourWhenTo'] . "</b>", 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 7, null, null, "2. Место проведения: <b>" . $_SESSION['TourWhere'] . "</b>", 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 7, null, null, "3. Наименование спортивного сооружения: <b>" . $tournamentData->ToVenue . "</b>", 0, 1, 0, 1, 'L');

    $pdf->writeHTMLCell(190, 5, null, null, "4. Всего участников соревнований: <b>" . $athletesStatistics->Total + $totalCoaches . "</b>, из <b>" . $athletesStatistics->RegionsCount . "</b> регион(ов);", 0, 1, 0, 1, 'L');
    $pdf->writeHTMLCell(190, 5, 10, null, "Спортсменов <b>" . $athletesStatistics->Total . "</b> чел., в том числе <b>" . $athletesStatistics->Males . "</b> муж., <b>" . $athletesStatistics->Females . "</b> жен.", 0, 1, 0, 1, 'L');
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
    foreach ($participantsByRegion as $regionCode => $data) {
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
    $pdf->Cell(20, 5, $athletesStatistics->Males, 1, 0, 'C');
    $pdf->Cell(20, 5, $athletesStatistics->Females, 1, 0, 'C');
    $pdf->Cell(20, 5, $athletesStatistics->Total, 1, 0, 'C');
    $pdf->Cell(20, 5, $totalCoaches, 1, 0, 'C');
    $pdf->Cell(20, 5, $athletesStatistics->Total + $totalCoaches, 1, 1, 'C');

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
    foreach ($classes as $regionCode => $description) {
        $pdf->Cell($groupSize, 5, $description, 1, 0, "C");
    }
    $pdf->ln();
    foreach ($subclasses as $subclassId => $subclassDescription) {
        $pdf->SetFont($pdf->FontStd,'B', 9);
        $pdf->Cell(15, 5, $subclassDescription, 1, 0, "C");
        $subclassTotal = 0;
        $pdf->SetFont($pdf->FontStd,'', 9);
        foreach ($classes as $regionCode => $description) {
            $subclassForGroup = $subclassStatistics[$subclassId][$regionCode] ?? "0";
            $subclassTotal += $subclassForGroup;
            $pdf->Cell($groupSize, 5, $subclassForGroup, 1, 0, "C");
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
    $pdf->setY($pdf->GetY() + 1);
    $PdfData=getMedalList();
    $PdfData->HideOfficials = true;
    require_once(PdfChunkLoader('MedalList.inc.php'));
    global $totalMedalsAwarded;
    $pdf->setY($pdf->GetY() + 2);
    $pdf->writeHTMLCell(190, 5, null, null, "Количество награжденных: <b>" . $totalMedalsAwarded . "</b> чел.;", 0, 1, 0, 1, 'L');

    $pdf->setY($pdf->GetY() + 4);
    $pdf->SetFont($pdf->FontStd,'', 10);
    $pdf->writeHTMLCell(190, 5, null, null, "12. Количество субъектов Российской Федерации команд (перечислить территории согласно  занятым местам):", 0, 1, 0, 1, 'L');
    $pdf->setY($pdf->GetY() + 1);
    $PdfData=getMedalStand();
    require_once(PdfChunkLoader('MedalStand.inc.php'));

    $pdf->setY($pdf->GetY() + 3);
    $pdf->SetFont($pdf->FontStd,'', 10);
    $pdf->writeHTMLCell(190, 5, null, null, "13. Общая оценка состояния спортивной базы, наличие и состояние спортивного оборудования и инвентаря, возможности для разминки и тренировок: <b>" . GskFields::getGeneralIssues()->getValue() . "</b>", 0, 1, 0, 1, 'J');

    $pdf->setY($pdf->GetY() + 3);
    $pdf->writeHTMLCell(190, 5, null, null, "14. Общая оценка состояния и оснащения служебных помещений - раздевалок для спортсменов, помещений для судей и других служб: <b>" . GskFields::getServiceRoomIssues()->getValue() . "</b>", 0, 1, 0, 1, 'J');

    $pdf->setY($pdf->GetY() + 3);
    $pdf->writeHTMLCell(190, 5, null, null, "15. Информационное обеспечение соревнований - табло, радиоинформация, своевременность и доступность стартовых протоколов и результатов соревнований, обеспечение судейской коллегии средствами вычислительной техники и множительной аппаратурой: <b>" . GskFields::getInformationServices()->getValue() . "</b>", 0, 1, 0, 1, 'J');

    $pdf->setY($pdf->GetY() + 3);
    $pdf->writeHTMLCell(190, 5, null, null, "16. Обеспечение работы средств массовой информации - места на трибунах, помещение для пресс-центра и т.д., в том числе освещение соревнования в местных СМИ (копии публикаций в СМИ прилагаются): <b>" . GskFields::getPressIssues()->getValue() . "</b>", 0, 1, 0, 1, 'J');

    $pdf->setY($pdf->GetY() + 3);
    $pdf->writeHTMLCell(190, 5, null, null, "17. Количество зрителей: <b>" . GskFields::getViewersAmount()->getValue() . "</b> чел.", 0, 1, 0, 1, 'J');

    $pdf->setY($pdf->GetY() + 3);
    $pdf->writeHTMLCell(190, 5, null, null, "18. Общая оценка качества проведения соревнований - точность соблюдения расписания, объективность судейства (с указанием нарушений правил соревнований и т.д.): <b>" . GskFields::getGeneralOrganisation()->getValue() . "</b>", 0, 1, 0, 1, 'J');

    $pdf->setY($pdf->GetY() + 3);
    $pdf->writeHTMLCell(190, 5, null, null, "19. Медицинское обеспечение соревнований, в том числе сведения о травмах и других несчастных случаях: <b>" . GskFields::getMedicalIssues()->getValue() . "</b>", 0, 1, 0, 1, 'J');

    $pdf->setY($pdf->GetY() + 3);
    $pdf->writeHTMLCell(190, 5, null, null, "20. Общая оценка качества размещения, питания, транспортного обслуживания, организации встреч и проводов спортивных делегаций, шефская работа и т.п.: <b>" . GskFields::getGuestDelegationIssues()->getValue() . "</b>", 0, 1, 0, 1, 'J');

    $pdf->setY($pdf->GetY() + 3);
    $pdf->writeHTMLCell(190, 5, null, null, "21. Общая оценка соблюдения мер по обеспечению безопасности при проведении соревнования: <b>" . GskFields::getSecurityNotes()->getValue() . "</b>", 0, 1, 0, 1, 'J');

    $pdf->setY($pdf->GetY() + 3);
    $pdf->writeHTMLCell(190, 5, null, null, "22. Выводы и предложения (замечания) по подготовке и проведению соревнования: <b>" . GskFields::getOtherNotes()->getValue() . "</b>", 0, 1, 0, 1, 'J');

    $pdf->setY($pdf->GetY() + 3);
    $pdf->writeHTMLCell(190, 5, null, null, "Приложения:", 0, 1, 0, 1, 'J');
    $pdf->writeHTMLCell(190, 5, null, null, "1. Полный состав судейской коллегии с указанием выполняемых на соревновании функций (судейская категория, субъект РФ, город).", 0, 1, 0, 1, 'J');
    $pdf->writeHTMLCell(190, 5, null, null, "2. Итоги командного первенства.", 0, 1, 0, 1, 'J');
    $pdf->writeHTMLCell(190, 5, null, null, "3. Протоколы (результаты) соревнований, подписанные главным судьей и главным секретарем.", 0, 1, 0, 1, 'J');

    $pdf->setY($pdf->GetY() + 10);
    $pdf->setX($pdf->GetX() + 20);
    $pdf->SetFont($pdf->FontStd,'B', 10);
    $pdf->Cell(50, 5, "Главный судья", 0, 0, 'L');
    $pdf->Cell(20, 5, "", 0, 0, 'L');
    $pdf->Cell(45, 5, "", "B", 0, 'L');
    $pdf->Cell(5, 5, "", "0", 0, 'L');
    $pdf->Cell(60, 5, TournamentOfficials::getJudgetSurnameWithInitials($chairpersonOfJudges->TiName, $chairpersonOfJudges->TiGivenName, $chairpersonOfJudges->TiLastName), "0", 1, 'L');
    $pdf->setX($pdf->GetX() + 20 + 70);
    $pdf->SetFont($pdf->FontStd,'', 7);
    $pdf->Cell(45, 5, "(подпись)", "0", 1, 'C', 0, 0, 0, false, 'T', 'T');

    $pdf->setX($pdf->GetX() + 20 + 50);
    $pdf->Cell(20, 5, "М. П.", 0, 1, 'C');
    $pdf->setY($pdf->GetY() + 3);

    $pdf->setX($pdf->GetX() + 20);
    $pdf->SetFont($pdf->FontStd,'B', 10);
    $pdf->Cell(50, 5, "Главный секретарь", 0, 0, 'L');
    $pdf->Cell(20, 5, "", 0, 0, 'L');
    $pdf->Cell(45, 5, "", "B", 0, 'L');
    $pdf->Cell(5, 5, "", "0", 0, 'L');
    $pdf->Cell(60, 5, TournamentOfficials::getJudgetSurnameWithInitials($chiefSecretary->TiName, $chiefSecretary->TiGivenName, $chiefSecretary->TiLastName), "0", 1, 'L');
    $pdf->setX($pdf->GetX() + 20 + 70);
    $pdf->SetFont($pdf->FontStd,'', 7);
    $pdf->Cell(45, 5, "(подпись)", "0", 1, 'C', 0, 0, 0, false, 'T', 'T');

    $pdf->setY($pdf->GetY() + 10);
    $pdf->setX($pdf->GetX() + 30);
    $pdf->SetFont($pdf->FontStd,'', 10);
    $pdf->Cell(50, 5, $_SESSION['TourWhenTo'] . " г.", 0, 0, 'L');

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
            foreach ($judgeRegions as $regionCode => $region) {
                if (!$isFirst) {
                    echo ",\n";
                }
                echo $regionCode . ": " . $region["FromRegion"];
                $isFirst = false;
            }
    ?>};

    const coachesPerRegion = {
        <?php
            $isFirst = true;
            foreach ($participantsByRegion as $regionCode => $data) {
                if (!$isFirst) {
                    echo ",\n";
                }
                echo $regionCode . ": " . $data["Coaches"];
                $isFirst = false;
            }
        ?>};
</script>

<form id="gskReport">
    <table class="Tabella">
        <tr><td colspan="3" style="padding: 20px; font-weight: bold">При необходимости укажите ниже данные, нужные для формирования отчета ГСК:</td></tr>
        <tr>
            <td style="text-align: left; padding-left: 40px; white-space: nowrap">Отчет о проведении</td><td class="w-100"><input class="w-100" type="text" id="title" name="<?php echo GskFields::getCompetitionTitle()->getParameterName(); ?>" value="<?php echo GskFields::getCompetitionTitle()->getValue(); ?>" onblur="updateField(this.name, this.value)"/></td><td style="white-space: nowrap"><div class="Button" onclick="<?php echo getResetInputJs(GskFields::getCompetitionTitle()); ?>">Вернуть стандартное значение</div></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left; padding-left: 40px">4. Всего участников соревнований: <span style="font-weight: bold" id="totalParticipants"><?php echo $athletesStatistics->Total + $totalCoaches; ?></span>, из <b><?php echo $athletesStatistics->RegionsCount; ?></b> региона(ов);</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left; padding-left: 40px">Спортсменов <span style="font-weight: bold" id="totalAthletes"><?php echo $athletesStatistics->Total; ?></span> чел., в том числе <b><?php echo $athletesStatistics->Males; ?></b> муж., <b><?php echo $athletesStatistics->Females; ?></b> жен.</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left; padding-left: 40px">Представителей, тренеров <span style="font-weight: bold" id="totalCoaches"><?php echo $totalCoaches; ?></span> чел.</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left; padding-left: 40px">5. Количество судей: <span style="font-weight: bold" id="judgesTotal"><?php echo $judgesData->Total; ?></span>, в том числе иногородних: <span style="font-weight: bold" id="nonLocalJudges"><?php echo $judgesData->NonLocal; ?></span></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left; padding-left: 80px">Выберите регион, судьи из которого <b>не</b> считаются иногородними:
            <?php
                $localRegionCodeForJudgesField = GskFields::getLocalRegionCodeForJudges();
                echo '<select id="judgesRegions" onchange="judgesHomeRegionChanged(\'' . $localRegionCodeForJudgesField->getParameterName() . '\', this)">';
                $homeRegionId = $localRegionCodeForJudgesField->getValue();
                foreach ($judgeRegions as $regionCode => $region) {
                    echo "<option value='" . $regionCode . "'" . ($regionCode == $homeRegionId ? " selected='selected'" : "") . ">" . $region["Name"] . "</option>";
                }
            ?></select></td>
        </tr>
        <tr><td colspan="3" style="text-align: left; padding-left: 40px">6. Состав участвующих команд (регионов), в том числе количество спортсменов, тренеров и другого обслуживающего персонала:</td></tr>
        <tr>
            <td colspan="3" style="text-align: left; padding-left: 40px">
                <table class="Tabella w-60">
                    <tr><td rowspan="2" style="border: 1px solid" class="w-5">№<br/>п/п</td><td rowspan="2" style="border: 1px solid">Команда (субъект РФ)</td><td rowspan="2" style="border: 1px solid">Базовый вид</td><td colspan="3" style="border: 1px solid">Спортсмены, чел.</td><td rowspan="2" style="border: 1px solid">Тренеры и др. обсл. персонал, чел.</td><td rowspan="2" style="border: 1px solid; padding-right: 10px">Всего</td></tr>
                    <tr><td style="border: 1px solid">М</td><td style="border: 1px solid">Ж</td><td style="border: 1px solid;">Всего</td></tr>
                    <?php
                        $index = 1;
                        $tabIndexOffset = count($participantsByRegion);
                        foreach ($participantsByRegion as $regionCode => $data) {
                            $isBasicParameterName = IsBasicRegionGskField::getParameterNameForRegion($regionCode);
                            $numberOfCoachesParameterName = NumberOfCoachesFromRegion::getParameterNameForRegion($regionCode);
                            echo "<tr><td style='border: 1px solid'>" . $index . "</td><td style='border: 1px solid'>" . $data["Name"] . "</td><td style='border: 1px solid'><input type='checkbox' tabindex='" . $index . "' name='" . $isBasicParameterName . "'" . ($data["isBasicSport"] ? "checked='checked'" : '') . " onclick='toggleBasicSport(this)'/></td><td style='border: 1px solid'>" . $data["Males"] . "</td><td style='border: 1px solid'>" . $data["Females"] . "</td><td id='athletesTotal_" . $regionCode . "' style='border: 1px solid'>" . $data["Males"] + $data["Females"] . "</td><td style='border: 1px solid'><input class='w-25' id='" . $regionCode . "' type='text' tabIndex='" . $index + $tabIndexOffset . "' name='" . $numberOfCoachesParameterName . "' value='" . $data["Coaches"] . "' onblur='coachesChanged(this)'/></td><td id='regionTotal_" . $regionCode . "' style='border: 1px solid'>" . $data["Males"] + $data["Females"] + $data["Coaches"] . "</td></tr>\n";
                            ++$index;
                        }
                    ?>
                </table>
            </td>
        </tr>
        <tr><td colspan="3" style="text-align: left; padding-left: 40px">13. Общая оценка состояния спортивной базы, наличие и состояние спортивного оборудования и инвентаря, возможности для разминки и тренировок:</td></tr>
        <tr><td colspan="2" style="text-align: left; padding-left: 40px;"><textarea class="w-100" rows=5 name="<?php echo GskFields::getGeneralIssues()->getParameterName(); ?>" onblur="updateField(this.name, this.value)"><?php echo GskFields::getGeneralIssues()->getValue(); ?></textarea></td><td style="vertical-align: top"><div class="Button" onclick="<?php echo getResetInputJs(GskFields::getGeneralIssues()); ?>">Вернуть стандартное значение</div></td></tr>
        <tr><td colspan="3" style="text-align: left; padding-left: 40px">14. Общая оценка состояния и оснащения служебных помещений - раздевалок для спортсменов, помещений для судей и других служб:</td></tr>
        <tr><td colspan="2" style="text-align: left; padding-left: 40px"><textarea class="w-100"  rows=5 name="<?php echo GskFields::getServiceRoomIssues()->getParameterName(); ?>" onblur="updateField(this.name, this.value)"><?php echo GskFields::getServiceRoomIssues()->getValue(); ?></textarea></td><td style="vertical-align: top"><div class="Button" onclick="<?php echo getResetInputJs(GskFields::getServiceRoomIssues()); ?>">Вернуть стандартное значение</div></td></tr>
        <tr><td colspan="3" style="text-align: left; padding-left: 40px">15. Информационное обеспечение соревнований - табло, радиоинформация, своевременность и доступность стартовых протоколов и результатов соревнований, обеспечение судейской коллегии средствами вычислительной техники и множительной аппаратурой:</td></tr>
        <tr><td colspan="2" style="text-align: left; padding-left: 40px"><textarea class="w-100"  rows=5 name="<?php echo GskFields::getInformationServices()->getParameterName(); ?>" onblur="updateField(this.name, this.value)"><?php echo GskFields::getInformationServices()->getValue(); ?></textarea></td><td style="vertical-align: top"><div class="Button" onclick="<?php echo getResetInputJs(GskFields::getInformationServices()); ?>">Вернуть стандартное значение</div></td></tr>
        <tr><td colspan="3" style="text-align: left; padding-left: 40px">16. Обеспечение работы средств массовой информации - места на трибунах, помещение для пресс-центра и т.д., в том числе освещение соревнования в местных СМИ (копии публикаций в СМИ прилагаются):</td></tr>
        <tr><td colspan="2" style="text-align: left; padding-left: 40px"><textarea class="w-100"  rows=5 name="<?php echo GskFields::getPressIssues()->getParameterName(); ?>" onblur="updateField(this.name, this.value)"><?php echo GskFields::getPressIssues()->getValue(); ?></textarea></td><td style="vertical-align: top"><div class="Button" onclick="<?php echo getResetInputJs(GskFields::getPressIssues()); ?>">Вернуть стандартное значение</div></td></tr>
        <tr><td colspan="3" style="text-align: left; padding-left: 40px">17. Количество зрителей: <input type="text" name="<?php echo GskFields::getViewersAmount()->getParameterName(); ?>" value="<?php echo GskFields::getViewersAmount()->getValue(); ?>" onblur="updateField(this.name, this.value)"/> чел.</td></tr>
        <tr><td colspan="3" style="text-align: left; padding-left: 40px">18. Общая оценка качества проведения соревнований - точность соблюдения расписания, объективность судейства (с указанием нарушений правил соревнований и т.д.):</td></tr>
        <tr><td colspan="2" style="text-align: left; padding-left: 40px"><textarea class="w-100"  rows=5 name="<?php echo GskFields::getGeneralOrganisation()->getParameterName(); ?>" onblur="updateField(this.name, this.value)"><?php echo GskFields::getGeneralOrganisation()->getValue(); ?></textarea></td><td style="vertical-align: top"><div class="Button" onclick="<?php echo getResetInputJs(GskFields::getGeneralOrganisation()); ?>">Вернуть стандартное значение</div></td></tr>
        <tr><td colspan="3" style="text-align: left; padding-left: 40px">19. Медицинское обеспечение соревнований, в том числе сведения о травмах и других несчастных случаях:</td></tr>
        <tr><td colspan="2" style="text-align: left; padding-left: 40px"><textarea  class="w-100"  rows=5 name="<?php echo GskFields::getMedicalIssues()->getParameterName(); ?>" onblur="updateField(this.name, this.value)"><?php echo GskFields::getMedicalIssues()->getValue(); ?></textarea></td><td style="vertical-align: top"><div class="Button" onclick="<?php echo getResetInputJs(GskFields::getMedicalIssues()); ?>">Вернуть стандартное значение</div></td></tr>
        <tr><td colspan="3" style="text-align: left; padding-left: 40px">20. Общая оценка качества размещения, питания, транспортного обслуживания, организации встреч и проводов спортивных делегаций, шефская работа и т.п.:</td></tr>
        <tr><td colspan="2" style="text-align: left; padding-left: 40px"><textarea class="w-100"  rows=5 name="<?php echo GskFields::getGuestDelegationIssues()->getParameterName(); ?>" onblur="updateField(this.name, this.value)"><?php echo GskFields::getGuestDelegationIssues()->getValue(); ?></textarea></td><td style="vertical-align: top"><div class="Button" onclick="<?php echo getResetInputJs(GskFields::getGuestDelegationIssues()); ?>">Вернуть стандартное значение</div></td></tr>
        <tr><td colspan="3" style="text-align: left; padding-left: 40px">21. Общая оценка соблюдения мер по обеспечению безопасности при проведении соревнования:</td></tr>
        <tr><td colspan="2" style="text-align: left; padding-left: 40px"><textarea class="w-100" rows=10 name="<?php echo GskFields::getSecurityNotes()->getParameterName(); ?>" onblur="updateField(this.name, this.value)"><?php echo GskFields::getSecurityNotes()->getValue(); ?></textarea></td><td style="vertical-align: top"><div class="Button" onclick="<?php echo getResetInputJs(GskFields::getSecurityNotes()); ?>">Вернуть стандартное значение</div></td></tr>
        <tr><td colspan="3" style="text-align: left; padding-left: 40px">22. Выводы и предложения (замечания) по подготовке и проведению соревнования:</td></tr>
        <tr><td colspan="2" style="text-align: left; padding-left: 40px"><textarea class="w-100" rows=5 name="<?php echo GskFields::getOtherNotes()->getParameterName(); ?>" onblur="updateField(this.name, this.value)"><?php echo GskFields::getOtherNotes()->getValue(); ?></textarea></td><td style="vertical-align: top"><div class="Button" onclick="<?php echo getResetInputJs(GskFields::getOtherNotes()); ?>">Вернуть стандартное значение</div></td></tr>
        <tr>
            <th colspan="3" class="Left" style="padding-left: 50px; padding-top: 10px; padding-bottom: 10px">
                <div class="Button" onclick="window.open('gskReport.php?doPrint', '_blank')">Распечатать</div>
            </th>
        </tr>
    </table>
</form>


<?php
    include('Common/Templates/tail.php');
}
?>
