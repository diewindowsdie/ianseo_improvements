<?php
require_once(dirname(__FILE__, 3) . '/config.php');
if ($log) fwrite($log, "Executing <i>2026-05-03_add_judges_fixed_order.php</i> update script...<br/><br/>\n");
//сначала, проверим что в таблице InvolvedType еще нет столбца ItOrder
$newColumns = [
    'LastName' => [
        'name' => 'ItOrder',
        'table' => 'InvolvedType',
        'type' => 'smallint not null default 0',
        'after' => 'ItDescription',
    ]
];

if ($log) fwrite($log, "Checking <b><i>InvolvedType</i></b> table structure...<br />\n");
foreach ($newColumns as $Column) {
    $checkQuery = "SELECT `COLUMN_NAME`
    FROM `INFORMATION_SCHEMA`.`COLUMNS`
    WHERE `TABLE_SCHEMA`='" . $CFG->DB_NAME . "'
    AND `TABLE_NAME`='" . $Column["table"] . "'
    AND `COLUMN_NAME` = '" . $Column['name'] . "'";
    $resultSet = safe_r_sql($checkQuery);
    $numRows = mysqli_num_rows($resultSet);
    if ($numRows == 0) {
        //нужно добавить столбцы
        safe_w_SQL('alter table ' . $Column["table"] . ' add column ' . $Column['name'] . ' ' . $Column['type'] . ' after ' . $Column['after']);
        if ($log) fwrite($log, "Added column <b>" . $Column['name'] . "</b> to table <b><i>" . $Column["table"] . "</i></b> table.<br />\n");
    } else {
        if ($log) fwrite($log, "Table <b><i>" . $Column['table'] . "</i></b> already has additional column <b>" . $Column['name'] . "</b>, skipping...<br />\n");
    }
}

$rolesOrder = [
    "ChairmanJudge" => 0,
    "ChiefSecretary" => 10,
    "ChairmanJudgeDeputy" => 20,
    "ChiefSecretaryDeputy" => 30,
    "TecDelegate" => 40,
    "FieldResp" => 50,
    "Dos" => 60,
    "Secretary" => 70,
    "FieldJudge" => 80,
    "LineJudge" => 90,
    "TargetJudge" => 100,
    "Judge" => 110,
    "DosAssistant" => 120,
    "ResVerifier" => 130,

    //это - те, кто у нас не использ
    "OrgResponsible" => 140,
    "ChairmanJury" => 150,
    "AlternateJury" => 160,
    "ResultResp" => 170,
    "LogisticResp" => 180,
    "MediaResp" => 190,
    "SportPres" => 200,
    "Announcer" => 210,
    "ADOfficer" => 220,
    "MedOfficer" => 230,
    "CompManager" => 240,
    "RaceOfficer" => 250,
    "Spotter" => 260,
    "Jury" => 270
];

foreach ($rolesOrder as $role => $order) {
    safe_w_SQL('update InvolvedType set ItOrder = ' . $order . ' where ItDescription = \'' . $role . '\'');
}

if ($log) fwrite($log, "<i>2026-05-03_add_judges_fixed_order.php</i> script finished successfully.<br/><br/>\n");
?>