<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
if ($log) fwrite($log, "Executing <i>2026-03-12_add_12_12_RUS.php</i> update script...<br/><br/>\n");

$query = "select TtId from TourTypes where TtId = 1001";
$numRows = mysqli_num_rows(safe_r_SQL($query));
if ($numRows == 0) {
    $query = "insert into TourTypes (TtId, TtType, TtDistance, TtOrderBy, TtWaEquivalent) " .
        "values (1001, 'Type_50_30mRound', 2, 55, 0);";
    safe_w_SQL($query);
    if ($log) fwrite($log, "Added round type 50m+30m for RUS to table <b><i>TourTypes</i></b>...<br />\n");
} else {
    if ($log) fwrite($log, "Table <b><i>TourTypes</i></b> already has a row for 50m+30m RUS round, skipping...<br />\n");
}

if ($log) fwrite($log, "<i>2026-03-12_add_12_12_RUS.php</i> script finished successfully.<br/><br/>\n");
?><?php
