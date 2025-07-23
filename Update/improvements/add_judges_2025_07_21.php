<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
if ($log) fwrite($log, "Executing <i>add_judges_2025_07_21.php</i> update script...<br/><br/>\n");

//переопределим порядок вывода судейских должностей
if ($log) fwrite($log, "Updating judge roles display order in table <b><i>InvolvedType</i></b>...<br/>\n");

safe_w_SQL("update InvolvedType set ItOc = 5 where ItDescription = 'FieldJudge';");
safe_w_SQL("update InvolvedType set ItOc = 6 where ItDescription = 'Secretary';");
safe_w_SQL("update InvolvedType set ItOc = 7 where ItDescription = 'MediaResp';");
safe_w_SQL("update InvolvedType set ItOc = 8 where ItDescription = 'SportPres';");
safe_w_SQL("update InvolvedType set ItOc = 9 where ItDescription = 'LogisticResp';");
safe_w_SQL("update InvolvedType set ItOc = 10 where ItDescription = 'ResultResp';");
safe_w_SQL("update InvolvedType set ItOc = 11 where ItDescription = 'Announcer';");
safe_w_SQL("update InvolvedType set ItOc = 12 where ItDescription = 'ADOfficer';");
safe_w_SQL("update InvolvedType set ItOc = 13 where ItDescription = 'MedOfficer';");
safe_w_SQL("update InvolvedType set ItOc = 14 where ItDescription = 'CompManager';");
safe_w_SQL("update InvolvedType set ItOc = 15 where ItDescription = 'ResVerifier';");

$additionalOfficialsRoles = [
    'LineJudge' => 4,
    'TargetJudge' => 3
];
foreach ($additionalOfficialsRoles as $role => $order) {
    $query = "select ItDescription from InvolvedType where ItDescription='$role'";
    $numRows = mysqli_num_rows(safe_r_SQL($query));
    if ($numRows == 0) {
        $query = "insert into InvolvedType (ItDescription, ItJudge, ItDoS, ItJury, ItOC) " .
            "values ('" . $role . "', 0, 0, 0, " . $order . ");";
        safe_w_SQL($query);
        if ($log) fwrite($log, "Added judge role <b>$role</b> to table <b><i>InvolvedType</i></b>...<br />\n");
    } else {
        if ($log) fwrite($log, "Table <b><i>InvolvedType</i></b> already has a row for judge role <b>$role</b>, skipping...<br />\n");
    }
}

if ($log) fwrite($log, "<i>add_judges_2025_07_21.php</i> script finished successfully.<br/><br/>\n");
?>