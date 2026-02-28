<?php
require_once dirname(__FILE__, 4) . '/config.php';
require_once dirname(__FILE__, 2) . "/lib.php";
global $CFG;

$competition = findCompetitionByCode($_REQUEST["code"]);

$JS_SCRIPT=array(
    '<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Common/Results/public/results.js"></script>',
    '<link href="'.$CFG->ROOT_DIR.'Common/Results/public/results.css" media="screen" rel="stylesheet" type="text/css">',
);

$IncludeFA=true;
$IncludeJquery=true;

global $PAGE_TITLE;
$PAGE_TITLE = "Результаты соревнования " . htmlspecialchars($competition->ToName);

include('Common/Templates/head-min.php');

echo '<table class="w-70 competitionDetails" id="competitionsList">';

$titleColspan = 3;
echo "<tr>";
if (isset($competition->LeftImageUrl)) {
    --$titleColspan;
    echo '<td class="w-5"><img src="' . $competition->LeftImageUrl . '" width="100" alt=""/></td>';
}

if (isset($competition->RightImageUrl)) {
    --$titleColspan;
}

echo '<td class="w-100 title" colspan="' . $titleColspan . '">
        <span class="competitionTitle">' . $competition->ToName . '</span><br/>
        <span class="secondLevelTitle">' . $competition->ToComDescr . '</span><br/>
        <span class="secondLevelTitle">' . $competition->ToWhere . ($competition->ToVenue ? (' (' . $competition->ToVenue . ')') : '') . ', ' . formatTournamentDateAtCompetitionLanguage($competition) . '</span><br/>
        </td>';

//два раза проверять нужно потому, что нам нужно рисовать ячейку с текстом выше этой проверки
if (isset($competition->RightImageUrl)) {
    echo '<td class="w-5"><img src="' . $competition->RightImageUrl . '" width="100"  alt="" /></td>';
}
echo "</tr>";

echo "<tr><td colspan='3' class='details'>";
echo getCompetitionDetailsHtml($competition);
echo "</td></tr>";

if (isset($competition->BottomImageUrl)) {
    echo '<tr><td colspan="3" align="center"><img src="' . $competition->BottomImageUrl . '" height="40" alt="" /></td>';
}

echo '</table>';

include('Common/Templates/tail-base.php');
