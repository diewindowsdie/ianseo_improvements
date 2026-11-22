<?php
require_once(dirname(__FILE__, 3) . '/config.php');
if ($log) fwrite($log, "Executing <i>2026-11-22_add_senior_secretary_role.php</i> update script...<br/><br/>\n");

$roleToOrder = [
    "SeniorSecretary" => 65
];

foreach ($roleToOrder as $role => $order) {
    $query = "select ItDescription from InvolvedType where ItDescription='$role'";
    $numRows = mysqli_num_rows(safe_r_SQL($query));
    if ($numRows == 0) {
        $query = "insert into InvolvedType (ItDescription, ItOrder, ItJudge, ItDoS, ItJury, ItOC) " .
            "values ('" . $role . "', " . $order . ", 6, 0, 0, 0);";
        safe_w_SQL($query);
        if ($log) fwrite($log, "Added judge role <b>$role</b> to table <b><i>InvolvedType</i></b>...<br />\n");
    } else {
        if ($log) fwrite($log, "Table <b><i>InvolvedType</i></b> already has a row for judge role <b>$role</b>, skipping...<br />\n");
    }
}

if ($log) fwrite($log, "<i>2026-11-22_add_senior_secretary_role.php</i> script finished successfully.<br/><br/>\n");
?>