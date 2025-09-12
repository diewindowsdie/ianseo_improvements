<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
if ($log) fwrite($log, "Executing <i>2025-09-10_add_device_note.php</i> update script...<br/><br/>\n");
//сначала, проверим что в таблице IskDevices еще нет столбца IskDvNote
$newColumns = [
    'Note' => [
        'name' => 'IskDvNote',
        'table' => 'IskDevices',
        'type' => 'varchar(20)',
        'after' => 'IskDvDevice'
    ]
];

if ($log) fwrite($log, "Checking <b><i>IskDevices</i></b> table structure...<br />\n");
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

if ($log) fwrite($log, "<i>2025-09-10_add_device_note.php</i> script finished successfully.<br/><br/>\n");
?><?php
