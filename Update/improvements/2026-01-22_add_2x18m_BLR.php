<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
if ($log) fwrite($log, "Executing <i>2026-01-22_add_2x18m_BLR.php</i> update script...<br/><br/>\n");

$query = "select TtId from TourTypes where TtId = 1000";
$numRows = mysqli_num_rows(safe_r_SQL($query));
if ($numRows == 0) {
    $query = "insert into TourTypes (TtId, TtType, TtDistance, TtOrderBy, TtWaEquivalent) " .
        "values (1000, 'Type_2x18mRound', 4, 51, 0);";
    safe_w_SQL($query);
    if ($log) fwrite($log, "Added round type 18mx2 for BLR to table <b><i>TourTypes</i></b>...<br />\n");
} else {
    if ($log) fwrite($log, "Table <b><i>TourTypes</i></b> already has a row for 18mx2 BLR round, skipping...<br />\n");
}

if ($log) fwrite($log, "<i>2026-01-22_add_2x18m_BLR.php</i> script finished successfully.<br/><br/>\n");
?><?php
