<?php
    require_once(dirname(dirname(__FILE__)) . '/config.php');
    if (!CheckTourSession()) {
        exit;
    }
    checkFullACL(AclParticipants, 'pTarget', AclReadWrite, false);

    $onTarget = array();
    $query = "select q.QuId, q.QuLetter from Qualifications q left join Entries e on q.QuId = e.EnId where e.EnTournament = " . StrSafe_DB($_SESSION["TourId"]) .
        " and q.QuSession = " . StrSafe_DB($_REQUEST["session"]) . " and q.QuTarget = " . ltrim(StrSafe_DB($_REQUEST["target"], "0")) . " order by QuLetter asc";
    $resultSet = safe_r_SQL($query);
    while ($row = safe_fetch($resultSet)) {
        $onTarget[$row->QuLetter] = $row->QuId;
    }

    //свободные места на щите
    for ($key = 'A'; $key <= 'D'; ++$key) {
        if (!array_key_exists($key, $onTarget)) {
            $onTarget[$key] = "";
        }
    }

    foreach ($_REQUEST['data'] as $pair) {
        $tmp = $onTarget[$pair[0]];
        $onTarget[$pair[0]] = $onTarget[$pair[1]];
        $onTarget[$pair[1]] = $tmp;
    }

    foreach($onTarget as $letter => $id) {
        //обновляем только записи, где есть спортсмен
        if ($id) {
            $query = "update Qualifications set QuLetter = '" . $letter . "', QuTargetNo = '" . $_REQUEST["session"] . str_pad($_REQUEST['target'], 3, "0", STR_PAD_LEFT) . $letter . "' where QuId = " . $id;
            safe_w_sql($query);
        }
    }

    echo "{}";
?>

