<?php

require_once('../../config.php');
require_once('Common/Lib/Fun_Modules.php');
include('Common/Templates/head.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs
    $fullteam       = isset($_POST['TeamMode'])      ? max(0, (int)$_POST['TeamMode']) : 0;
    $menComponent   = isset($_POST['menComponent'])  ? max(0, (int)$_POST['menComponent']) : 0;
    $womenComponent = isset($_POST['womenComponent'])? max(0, (int)$_POST['womenComponent']) : 0;
    $mixedComponent = isset($_POST['mixedComponent'])? max(0, (int)$_POST['mixedComponent']) : 0;

    // Save using module parameters
    SetModuleParameter('SetUK', 'TeamMode', $fullteam);
    SetModuleParameter('SetUK', 'MenComponent', $menComponent);
    SetModuleParameter('SetUK', 'WomenComponent', $womenComponent);
    SetModuleParameter('SetUK', 'MixedComponent', $mixedComponent);

    $message = "Team counts updated successfully!";
}


// Load curret values for form defaults
$TeamSet = GetModuleParameter('SetUK', 'TeamMode');

if ($TeamSet === null || $TeamSet === '') {
    SetModuleParameter('SetUK', 'TeamMode', 0);
    SetModuleParameter('SetUK', 'MenComponent', 0);
    SetModuleParameter('SetUK', 'WomenComponent', 0);
    SetModuleParameter('SetUK', 'MixedComponent', 0);
}

$current = array(
    'TeamMode'       => (int) (GetModuleParameter('SetUK', 'TeamMode')       ?: 0),
    'MenComponent'   => (int) (GetModuleParameter('SetUK', 'MenComponent')   ?: 0),
    'WomenComponent' => (int) (GetModuleParameter('SetUK', 'WomenComponent') ?: 0),
    'MixedComponent' => (int) (GetModuleParameter('SetUK', 'MixedComponent') ?: 0)
);
?>

<h2>Update Team Components</h2>
<?php if ($message): ?>
    <p style="color:green;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

Fill in the form below, detailing how many Men, Women and Mixed athletes form a team.
Then go back to the <a href="../../../Qualification/index.php">Qualification Input Screen</a>, click on Rebuild Teams, and Recalculate final rank.

<p></p>
<form method="post" action="">
    <labelhidden="1" >Mode: </label>
    <input type="number" name="TeamMode" hidden="1" value="<?php echo htmlspecialchars($current['TeamMode']); ?>" required /><br><br>

    <label>Open Team Members: </label>
    <input type="number" name="menComponent" min="0" value="<?php echo htmlspecialchars($current['MenComponent']); ?>" required /><br><br>

    <label>Female Team Members: </label>
    <input type="number" name="womenComponent" min="0" value="<?php echo htmlspecialchars($current['WomenComponent']); ?>" required /><br><br>

    <label>Mixed Team Members: </label>
    <input type="number" name="mixedComponent" min="0" value="<?php echo htmlspecialchars($current['MixedComponent']); ?>" required /><br><br>

    <button type="submit">Save Settings</button>
</form>
