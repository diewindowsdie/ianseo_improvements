<?php
//Check for UK ruleset and Present the Clubs CSV downloader
if (!empty($on) && $_SESSION["TourLocRule"] == 'UK'){
    $ret['AGBR1'][] = 'Archery GB';
    $ret['AGBR1']['CLD'] = 'Club List Download' . '|' . $CFG->ROOT_DIR . 'Modules/Sets/UK/Functions/ClubDownload.php';
}
//Check for National Champs rulesets and show the team selector.

if (!empty($on) && $_SESSION["TourLocRule"] == 'UK' && ($_SESSION["TourLocSubRule"] == 'SetUkJunNationals' || $_SESSION["TourLocSubRule"] == 'SetUkNationals')) {
  $ret['AGBR1']['Teams'] = 'AGB Teams' . '|' . $CFG->ROOT_DIR . 'Modules/Sets/UK/TeamSetup.php';
}
//If Testing release, show the Round Robin Reconfiguration. Add the print by alpha order for all on one scorecards here. specific to big AGB events
if(ProgramRelease ==='TESTING'){
    if(!empty($_SESSION["TourCode"]) && strpos($_SESSION["TourCode"], "B2B") !== false) {
               $ret['AGBR1']['RRSetup'] = 'AGB Round Robin Setup' . '|' . $CFG->ROOT_DIR . 'Modules/Sets/UK/RRSetup.php';
    }
}


