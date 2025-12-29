<?php

require_once('../../config.php');
require_once('Common/Lib/Fun_Modules.php');
include('Common/Templates/head.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
$message = '';

?>

Fill in the form below, Detail the Event Code (i.e. CO for Compound Open) the event level, and the grid group number. 

You can check this has worked in the grid assingments. Make sure all assignments have been corrected at all levels before qualifying the atheltes.

<p></p>
<form method="post" action="/Functions/NewGridAssignment.php">
   
    <label>Event Code </label>
    <input type="text" name="EvCode" min="0" value="" required /><br><br>

    <label>Event Level (1,2,3)</label>
    <input type="number" name="EvLevel" min="0" value="" required /><br><br>

    <label>Event Grid (A,B,C etc as 1,2,3..): </label>
    <input type="number" name="GridGr" min="0" value="" required /><br><br>

    <button type="submit">Save Settings</button>
</form>
