<?php
	require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
	CheckTourSession(true);
	require_once('Minimas.php');
	require_once('Fun_Diploma.php');

	// check if minimas are available
	if (!isset($SAADiplMinima[$_SESSION['TourType']])) {
		die('Minimas not defined for this TourType(' . $_SESSION['TourType'] . ')!');
	}


	$PAGE_TITLE='SwissArchery Qualification Rankings and Diplomas';

	$JS_SCRIPT=array(
		'<script type="text/javascript">
		</script>');

	include('Common/Templates/head.php');

	echo '<table class="Tabella">';
	echo '<tr><th class="Title" colspan="2">SwissArchery Diplomas</th></tr>';
	echo '<tr><th class="SubTitle" width="50%">Qualification Round - Individuals</th>';
	echo '<th class="SubTitle" width="50%">Qualification Round - Teams</th>';
	echo '<tr>';

//Filtered list - Individual
	echo '<tr>';
	echo '<td class="Center"><div align="center"><br><form id="PrnParametersInd" action="PrnIndividualDipl.php" method="get" target="PrintOut">';
	echo '<table class="Tabella" style="width:95%">';
	echo '<tr>';
	echo '<td class="Center">';
	$IndEvents = get_ind_qualification_events();

	if(count($IndEvents)) {
		echo 'Event<br><select name="Event[]" multiple="multiple" size="'.min(15,count($IndEvents) + 1).'">';
		echo '<option value=".">All</option>';
		foreach($IndEvents as $TempCode => $TempName)
			echo '<option value="' . $TempCode . '">' . $TempCode . ' - ' . $TempName  . '</option>';
		echo '</select>';
	}
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '<table><tr><td></td>';
	echo '<td><br><input type="checkbox" name="templateOnly" />&nbsp;Print template only';
	echo '<br><input type="checkbox" name="namesOnly" />&nbsp;Print names only</td>';
	echo '</tr><tr>';
	echo '<td><input type="submit" name="Button" value="Individual Top 3 Ranking"></td>';
	echo '<td><input type="submit" name="Button" value="Individual Diplomas"></td>';
	echo '</tr></table>';
	echo '</form></div><br></td>';


//Filtered list - Team
	echo '<td class="Center"><div align="center"><br><form id="PrnParametersTeam" action="PrnTeamDipl.php" method="get" target="PrintOut">';
	echo '<table class="Tabella" style="width:95%">';
	echo '<tr>';
	echo '<td class="Center">';
	$TeEvents = get_team_qualification_events();
	
	if(count($TeEvents)) {
		echo 'Event<br><select name="Event[]" multiple="multiple" size="'.min(15,count($TeEvents) + 1).'">';
		echo '<option value=".">All</option>';
		foreach($TeEvents as $TempCode => $TempName)
			echo '<option value="' . $TempCode . '">' . $TempCode . ' - ' . $TempName  . '</option>';
		echo '</select>';
	}
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '<table><tr><td></td>';
	echo '<td><br><input type="checkbox" name="templateOnly" />&nbsp;Print template only';
	echo '<br><input type="checkbox" name="namesOnly" />&nbsp;Print names only</td>';
	echo '</tr><tr>';
	echo '<td><input type="submit" name="Button" value="Team Top 3 Ranking"></td>';
	echo '<td><input type="submit" name="Button" value="Team Diplomas"></td>';
	echo '</tr></table>';
	echo '</form></div><br></td>';
	echo '</tr>';
	echo '</table>';

	include('Common/Templates/tail.php');
?>