<?php

function get_ind_diploma_events() {
	// config
	$IndividualScores = array();
	$ReturnEvents = array();
	
	// fetch athletes in affected events
	$IndividualScores = get_ind_diploma_athletes();
	
	// create list of events with diplomas
	foreach ($IndividualScores as $TempIndividual) {
		// check if the minima is met
		if ($TempIndividual['Minima'] > $TempIndividual['QuScore']) {
			// add the event to the return list
			$ReturnEvents[$TempIndividual['IndEvent']] = $TempIndividual['EvEventName'];
		}
	}

	return ($ReturnEvents);
}

function get_ind_qualification_events() {
	// config
	$IndividualScores = array();
	$ReturnEvents = array();
	
	// fetch athletes in affected events
	$IndividualScores = get_ind_diploma_athletes();
	
	// create list of events with diplomas
	foreach ($IndividualScores as $TempIndividual) {
		// add the event to the return list
		$ReturnEvents[$TempIndividual['IndEvent']] = $TempIndividual['EvEventName'];
	}

	return ($ReturnEvents);
}


function get_ind_diploma_athletes($Events=array()) {
	global $SAADiplMinima;
	
	// config
	$ReturnIndividuals = array();

	// fetch athletes in events with less than 4 entries
	$MySql  = "SELECT Individuals.IndId, CONCAT(Entries.EnName, ' ', Entries.EnFirstName) AS EnFullName, Countries.CoName, TempEvents.IndEvent, Events.EvEventName, Qualifications.QuScore, Qualifications.QuClRank ";
	$MySql .= "FROM (SELECT IndEvent, COUNT(IndEvent) AS cnt ";
	$MySql .= "  FROM Individuals ";
	$MySql .= "  WHERE IndTournament = " . StrSafe_DB($_SESSION['TourId']) . " ";
	// filter on events if required
	if (count($Events)) {
		$TempEventString = '';
		foreach ($Events as $ThisEvent) {
			// add separator
			$TempEventString .= ($TempEventString) ? ',' : '';
			$TempEventString .= StrSafe_DB($ThisEvent);
		}
		$MySql .= "  AND IndEvent IN (" . $TempEventString . ") ";	
	}
	$MySql .= "  GROUP BY IndEvent ";
	$MySql .= "  HAVING cnt < 4 ";
	$MySql .= "  ORDER BY IndEvent ASC) AS TempEvents, Individuals, Entries, Qualifications, Countries, Events ";
	$MySql .= "WHERE Individuals.IndId = Entries.EnId ";
	$MySql .= "AND Individuals.IndEvent = Events.EvCode ";
	$MySql .= "AND Events.EvTournament = " . StrSafe_DB($_SESSION['TourId']) . " ";
	$MySql .= "AND Events.EvTeamEvent = 0 ";
	$MySql .= "AND Entries.EnCountry = Countries.CoId ";
	$MySql .= "AND Individuals.IndEvent = TempEvents.IndEvent ";
	$MySql .= "AND Individuals.IndId = Qualifications.QuId ";
	$MySql .= "AND Individuals.IndTournament = " . StrSafe_DB($_SESSION['TourId']) . " ";
	$MySql .= "ORDER BY TempEvents.IndEvent, Qualifications.QuClRank ASC";
	$Rs = safe_r_sql($MySql);
	if(safe_num_rows($Rs)>0) {
		while($MyRow=safe_fetch($Rs))
		array_push($ReturnIndividuals, array(
			'EnFullName' => get_text($MyRow->EnFullName,'','',true),
			'CoName' => get_text($MyRow->CoName,'','',true),
			'IndEvent' => $MyRow->IndEvent,
			'EvEventName' => get_text($MyRow->EvEventName,'','',true),
			'QuScore' => $MyRow->QuScore,
			'QuClRank' => $MyRow->QuClRank,
			'Minima' => (isset($SAADiplMinima[$_SESSION['TourType']][$MyRow->IndEvent]) ? $SAADiplMinima[$_SESSION['TourType']][$MyRow->IndEvent] : 0)
		));
		safe_free_result($Rs);
	}

	return ($ReturnIndividuals);
}


function get_team_diploma_events() {
	// config
	$TeamScores = array();
	$ReturnEvents = array();
	
	// fetch teams in affected events
	$TeamScores = get_team_diploma_athletes();
	
	// create list of events with diplomas
	foreach ($TeamScores as $TeamScore) {
		// check if the minima is met
		if ($TeamScore['Score'] < $TeamScore['Minima']) {
			// add the event to the return list
			$ReturnEvents[$TeamScore['EventId']] = $TeamScore['EventName'];
		}
	}

	return ($ReturnEvents);
}

function get_team_qualification_events() {
	// config
	$TeamScores = array();
	$ReturnEvents = array();
	
	// fetch teams in affected events
	$TeamScores = get_team_diploma_athletes();
	
	// create list of events with diplomas
	foreach ($TeamScores as $TeamScore) {
		// add the event to the return list
		$ReturnEvents[$TeamScore['EventId']] = $TeamScore['EventName'];
	}

	return ($ReturnEvents);
}


function get_team_diploma_athletes($Events=array()) {
	global $SAADiplMinima;
	
	// config
	$ReturnTeams = array();

	// fetch athletes participating in teams that are ranked in the top 3
	$MySql  = "SELECT Individuals.IndId, Teams.TeCoId, Teams.TeEvent, Teams.TeRank, Individuals.IndEvent, Events.EvEventName, CONCAT(Entries.EnName, ' ', Entries.EnFirstName) AS EnFullName, Countries.CoName, Qualifications.QuScore ";
	$MySql .= "FROM Teams, TeamComponent, Individuals, Events, Entries, Qualifications, Countries ";
	$MySql .= "WHERE Teams.TeCoId= TeamComponent.TcCoId ";
	// filter on Events if required
	if (count($Events)) {
		$TempEventString = '';
		foreach ($Events as $ThisEvent) {
			// add separator
			$TempEventString .= ($TempEventString) ? ',' : '';
			$TempEventString .= StrSafe_DB($ThisEvent);
		}
		$MySql .= "AND Teams.TeEvent IN (" . $TempEventString . ") ";
	}
	$MySql .= "AND Teams.TeEvent = TeamComponent.TcEvent ";
	$MySql .= "AND Teams.TeTournament = " . StrSafe_DB($_SESSION['TourId']) . " ";
	$MySql .= "AND Teams.TeFinEvent = 1 ";
	$MySql .= "AND Teams.TeRank < 4 ";
	$MySql .= "AND Teams.TeEvent = Events.EvCode ";
	$MySql .= "AND Events.EvTournament = " . StrSafe_DB($_SESSION['TourId']) . " ";
	$MySql .= "AND Events.EvTeamEvent = 1 ";
	$MySql .= "AND TeamComponent.TcId = Individuals.IndId ";
	$MySql .= "AND TeamComponent.TcId = Entries.EnId ";
	$MySql .= "AND Entries.EnCountry = Countries.CoId ";
	$MySql .= "AND Entries.EnId = Qualifications.QuId ";
	$MySql .= "ORDER BY Teams.TeEvent ASC, Teams.TeRank ASC, TeamComponent.TcOrder ASC ";
	$Rs = safe_r_sql($MySql);
	if(safe_num_rows($Rs)>0) {
		while($MyRow=safe_fetch($Rs)) {
		
			// build temporary string
			$TempIndex = $MyRow->TeCoId . $MyRow->TeEvent;

			// add array for new team
			if (!isset($ReturnTeams[$TempIndex])) {
				$ReturnTeams[$TempIndex] = array(
					'EventId' => $MyRow->TeEvent,
					'EventName' => get_text($MyRow->EvEventName,'','',true),
					'Rank' => $MyRow->TeRank,
					'Club' => get_text($MyRow->CoName,'','',true),
					'Score' => 0,
					'Minima' => 0,
					'Athletes' => array()
				);
			}
			
			// push the athlete to the array
			$TempMinima = isset($SAADiplMinima[$_SESSION['TourType']][$MyRow->IndEvent]) ? $SAADiplMinima[$_SESSION['TourType']][$MyRow->IndEvent] : 0;
			array_push($ReturnTeams[$TempIndex]['Athletes'], array(
				'EnFullName' => get_text($MyRow->EnFullName,'','',true),
				'IndEvent' => $MyRow->IndEvent,
				'QuScore' => $MyRow->QuScore,
				'Minima' => $TempMinima
			));
			
			// add the score and minima
			$ReturnTeams[$TempIndex]['Score'] += $MyRow->QuScore;
			$ReturnTeams[$TempIndex]['Minima'] += $TempMinima;
			
		}
		safe_free_result($Rs);
	}
	
	return ($ReturnTeams);
}

