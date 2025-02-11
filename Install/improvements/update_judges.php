<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
echo "Executing <i>update_judges.php</i> update script...<br/><br/>";

//сначала, проверим что в таблице TournamentInvolved еще нет столбцов с отчеством и аккредитацией судьи
$newColumns = [
        'LastName' => [
            'name' => 'TiLastName',
            'table' => 'TournamentInvolved',
            'type' => 'varchar(255) not null',
            'after' => 'TiGivenName'
        ],
        'Accreditation' => [
            'name' => 'TiAccreditation',
            'table' => 'TournamentInvolved',
            'type' => 'varchar(10) not null',
            'after' => 'TiLastName'
        ],
        'IsSigningProtocols' => [
            'name' => 'TiIsSigningProtocols',
            'table' => 'TournamentInvolved',
            'type' => 'TINYINT not null default 0',
            'after' => 'TiAccreditation'
        ]
    ];

echo "Checking <b><i>TournamentInvolved</i></b> table structure...<br />";
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
        echo "Added column <b>" . $Column['name'] . "</b> to table <b><i>" . $Column["table"] . "</i></b> table.<br />";
    } else {
        echo "Table <b><i>" . $Column['table'] . "</i></b> already has additional column <b>" . $Column['name'] . "</b>, skipping...<br />";
    }
}

//переопределим порядок вывода судейских должностей
echo "Updating judge roles display order in table <b><i>InvolvedType</i></b>...<br/>";
safe_w_SQL("update InvolvedType set ItJudge = 3 where ItDescription = 'ChairmanJudgeDeputy';");
safe_w_SQL("update InvolvedType set ItJudge = 5 where ItDescription = 'Judge';");
safe_w_SQL("update InvolvedType set ItJudge = 6 where ItDescription = 'RaceOfficer';");
safe_w_SQL("update InvolvedType set ItJudge = 7 where ItDescription = 'Spotter';");

safe_w_SQL("update InvolvedType set ItOc = 6 where ItDescription = 'MediaResp';");
safe_w_SQL("update InvolvedType set ItOc = 7 where ItDescription = 'SportPres';");
safe_w_SQL("update InvolvedType set ItOc = 8 where ItDescription = 'LogisticResp';");
safe_w_SQL("update InvolvedType set ItOc = 9 where ItDescription = 'ResultResp';");
safe_w_SQL("update InvolvedType set ItOc = 10 where ItDescription = 'Announcer';");
safe_w_SQL("update InvolvedType set ItOc = 11 where ItDescription = 'ADOfficer';");
safe_w_SQL("update InvolvedType set ItOc = 12 where ItDescription = 'MedOfficer';");
safe_w_SQL("update InvolvedType set ItOc = 13 where ItDescription = 'CompManager';");
safe_w_SQL("update InvolvedType set ItOc = 14 where ItDescription = 'ResVerifier';");

//теперь, проверим что все дополнительные судейские должности добавлены в базу данных
echo "Checking if table <b><i>InvolvedType</i></b> has rows for all additional judge roles...<br/>";
$additionalJudgeRoles = [
    'ChiefSecretary' => 2,
    'ChiefSecretaryDeputy' => 4
];
foreach ($additionalJudgeRoles as $role => $order) {
    $query = "select ItDescription from InvolvedType where ItDescription='$role'";
    $numRows = mysqli_num_rows(safe_r_SQL($query));
    if ($numRows == 0) {
        $query = "insert into InvolvedType (ItDescription, ItJudge, ItDoS, ItJury, ItOC) " .
            "values ('" . $role . "', " . $order . ", 0, 0, 0);";
        safe_w_SQL($query);
        echo "Added judge role <b>$role</b> to table <b><i>InvolvedType</i></b>...<br />";
    }
    else {
        echo "Table <b><i>InvolvedType</i></b> already has a row for judge role <b>$role</b>, skipping...<br />";
    }
}

$additionalOfficialsRoles = [
    'Secretary' => 4,
    'FieldJudge' => 3
];
foreach ($additionalOfficialsRoles as $role => $order) {
    $query = "select ItDescription from InvolvedType where ItDescription='$role'";
    $numRows = mysqli_num_rows(safe_r_SQL($query));
    if ($numRows == 0) {
        $query = "insert into InvolvedType (ItDescription, ItJudge, ItDoS, ItJury, ItOC) " .
            "values ('" . $role . "', 0, 0, 0, " . $order . ");";
        safe_w_SQL($query);
        echo "Added judge role <b>$role</b> to table <b><i>InvolvedType</i></b>...<br />";
    }
    else {
        echo "Table <b><i>InvolvedType</i></b> already has a row for judge role <b>$role</b>, skipping...<br />";
    }
}
?>