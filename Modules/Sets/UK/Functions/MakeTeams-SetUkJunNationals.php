<?php

/**
* MakeTeams per la generazione delle squadre nelle Regole UK - Campionati Junior
*/

$divisions = array('R');
if(!is_null($Category))
{
	$Category = substr($Category,0,1);

    if($_SESSION['TourType']==40 && $_SESSION['TourLocSubrule']=="SetUK_PMTH") {
        //If the round is a portsmouth
        switch($Category)
        {
            case 'B':
            case 'R':
                $divisions = array('R');
                $Category = 'R';
                break;
            case 'C':
                $divisions = array('C');
                break;
            default:
                $divisions = array();
        }
    }
    {
        switch($Category)
        {
            case 'R':
                $divisions = array('R');
                $Category = 'R';
                break;
            case 'C':
                $divisions = array('C');
                break;
            default:
                $divisions = array();
        }
    }
}

$startLevel = 2;
$endLevel = 2;

include("Common_MakeTeams.php");

?>