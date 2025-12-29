<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once('Common/Fun_FormatText.inc.php');


// Check connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

//Get Active Tournamnet
$Tourcode = $_SESSION['TourId'];

//Get the parameters for the Event Name, Event Level and Group (A-F for the B2B is numbered 1-xxx)
$Evcode = $_POST['EvCode'];
$EvLevel = (int) $_POST['EvLevel'];
$GridGr =  (int) $_POST['GridGr'];

//Delete Existing grid
$expongeGrid = "DELETE FROM RoundRobinGrids WHERE RrGridEvent = '$Evcode' AND RrGridLevel = '$EvLevel' AND RrGridTournament='$Tourcode' AND RrGridGroup='$GridGr'";
  $Es = safe_w_sql($expongeGrid);

//Create array of group assignments for 6 atheletes, rank 1 moves everytime.

$groupData = array(
    1 => array(1=>0, 2=>1, 3=>2, 4=>3, 5=>4, 6=>5),
    2 => array(4=>0, 6=>1, 1=>2, 5=>3, 2=>4, 3=>5),
    3 => array(2=>0, 5=>1, 6=>2, 3=>3, 1=>4, 4=>5),
    4 => array(3=>0, 1=>1, 2=>2, 6=>3, 4=>4, 5=>5),
    5 => array(5=>0, 3=>1, 4=>2, 2=>3, 6=>4, 1=>5)
);

$values = array();

//store each combination as an array ready for explosion into the query
foreach ($groupData as $group => $itemMatch) {
    foreach ($itemMatch as $item => $match) {
        $values[] = "($Tourcode,0,'$Evcode',1,$GridGr,$group,$item,$match)";
    }
}

//SQL to create the correct grouping assignments in the defined level.

$changeRR = "REPLACE INTO RoundRobinGrids (
    RrGridTournament,
    RrGridTeam,
    RrGridEvent,
    RrGridLevel,
    RrGridGroup,
    RrGridRound,
    RrGridItem,
    RrGridMatchno
) VALUES " . implode(",\n", $values) . ";";


// Optional: print to check
// echo $changeRR;

//Commit changes in a safe way
  $Rs = safe_w_sql($changeRR);


?> 
