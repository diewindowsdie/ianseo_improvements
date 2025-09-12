<?php

if (!empty($on) && $_SESSION["TourLocRule"] == 'UK' && ($_SESSION["TourLocSubRule"] == 'SetUkJunNationals' || $_SESSION["TourLocSubRule"] == 'SetUkNationals')) {
    $ret['AGBR1'][] = 'Archery GB';
    $ret['AGBR1']['Teams'] = 'AGB Teams' . '|' . $CFG->ROOT_DIR . 'Modules/Sets/UK/TeamSetup.php';
}
