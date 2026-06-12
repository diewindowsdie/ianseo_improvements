<?php

function insertRunParticipant($Team, $Event, $Entry, $NumLaps, $SubTeam=0) {
	if($Team) {
		safe_w_sql("insert ignore into RunArcheryParticipants 
			set RapParticipates=1, RapTournament={$_SESSION['TourId']}, RapEntry=$Entry, RapSubTeam=$SubTeam, RapEvent='$Event', RapTeamEvent=$Team");
		// get maxbib for teams
		$q=safe_r_sql("select max(RarBib+0)+1 as MaxBib from RunArcheryRank where RarTournament={$_SESSION['TourId']} and RarTeam=1");
		$r=safe_fetch($q);

		safe_w_sql("insert ignore into RunArcheryRank 
            (RarTournament, RarEntry, RarSubTeam, RarTeam, RarEvent, RarPhase, RarBib, RarLastUpdate) 
			select {$_SESSION['TourId']}, $Entry, $SubTeam, 1, '$Event', 0, ".($r->MaxBib??1).", now()
			from Countries
			where CoId=$Entry and CoTournament={$_SESSION['TourId']}");
		for($i=1;$i<=$NumLaps; $i++) {
			safe_w_sql("insert ignore into RunArchery 
				set RaTournament={$_SESSION['TourId']}, 
					RaEntry=$Entry,
					RaSubTeam=$SubTeam,
					RaTeam=1,
					RaEvent='$Event',
					RaPhase=0,
					RaLap=$i,
					RaLastUpdate=now()");
		}

		// get the startlist of the TEAM
		$SQL="select coalesce(date_format(RarStartlist, '%Y-%m-%dT%H:%i:%s'),'') as StartList, RarBib
			from RunArcheryRank 
			where RarTournament={$_SESSION['TourId']} and RarTeam=$Team and RarEvent=".StrSafe_DB($Event)." and RarEntry=$Entry and RarPhase=0 and RarSubTeam=$SubTeam ";
	} else {
		safe_w_sql("insert ignore into RunArcheryParticipants set RapParticipates=1, RapTournament={$_SESSION['TourId']}, RapEntry=$Entry, RapEvent='$Event', RapTeamEvent=$Team");
		safe_w_sql("insert ignore into RunArcheryRank 
	        (RarTournament, RarEntry, RarSubTeam, RarTeam, RarEvent, RarPhase, RarBib, RarLastUpdate) 
			select {$_SESSION['TourId']}, $Entry, 0, 0, '$Event', 0, coalesce(EdExtra,EnCode), now()
			from Entries
			left join ExtraData on EdId=EnId and EdType='Z' and EdExtra!=''
			where EnId=$Entry and EnTournament={$_SESSION['TourId']}");
		for($i=1;$i<=$NumLaps; $i++) {
			safe_w_sql("insert ignore into RunArchery 
				set RaTournament={$_SESSION['TourId']}, 
					RaEntry=$Entry,
					RaSubTeam=0,
					RaTeam=0,
					RaEvent='$Event',
					RaPhase=0,
					RaLap=$i,
					RaArcher=$Entry,
					RaLastUpdate=now()");
		}
		$SQL="select coalesce(date_format(RarStartlist, '%Y-%m-%dT%H:%i:%s'),'') as StartList, RarBib
			from Entries
            inner join EventClass on EcTournament=EnTournament and EcDivision=EnDivision and EcClass=EnClass
            inner join Events on EvTournament=EcTournament and EvCode=EcCode and EvTeamEvent=(EcTeamEvent>0)
            inner join RunArcheryParticipants on RapTournament=EnTournament and RapEntry=EnId and RapTeamEvent=EvTeamEvent and RapEvent=EvCode
            left join RunArcheryRank on RarTournament=EnTournament and RarEntry=EnId and RarSubTeam=0 and RarTeam=EvTeamEvent and RarEvent=EvCode and RarPhase=0
			where EnTournament={$_SESSION['TourId']} and EvTeamEvent=$Team and EvCode=".StrSafe_DB($Event)." and EnId=$Entry
			";
	}
	$q=safe_r_sql($SQL);
	if($r=safe_fetch($q)) {
		return $r;
	}
	return '';
}

/**
 * @param $Event Event to search for duplicate teamcomponents
 * @return array of duplicate EnIds (part of more teams on the same event)
 */
function searchRedComps($Event) {
	$ret=[];
	$q=safe_r_sql("select count(*) as TotalNum, TfcId as id
		from TeamFinComponent
		where TfcTournament={$_SESSION['TourId']} and TfcEvent=".StrSafe_DB($Event)."
		group by TfcId
		having TotalNum>1");
	while($r=safe_fetch($q)) {
		$ret[]=$r->id;
	}
	return $ret;
}

function searchReds($Team, $Event, $BibNum='') {
	$ret=[];
	$q=safe_r_sql("select count(*) as TotalNum, group_concat(".($Team ? "concat_ws('-', RarEvent, RarEntry, RarSubTeam)" : 'RarEntry')." separator '|') as id
		from RunArcheryRank
		where RarTournament={$_SESSION['TourId']} and RarTeam=$Team and RarPhase=0 ".($BibNum ? "and RarBib=".StrSafe_DB($BibNum) : '')."
		group by RarBib
		having TotalNum>1");
	while($r=safe_fetch($q)) {
		$tmp=[];
		foreach(explode('|', $r->id) as $a) {
			$b=explode('-', $a, 2);
			if($b[0]==$Event) {
				$ret[]=$b[1];
			}
		}
	}
	return array_unique($ret);
}

function searchTimes($Time='') {
	$ret=[];
	$q=safe_r_sql("select SesAth4Target from Session where SesTournament={$_SESSION['TourId']} and SesOrder=1 and SesType='Q'");
	$r=safe_fetch($q);
	$MaxGroup=$r->SesAth4Target;
	$q=safe_r_sql("select date_format(RarStartlist, '%Y-%m-%dT%H:%i:%s') as StartList, count(*) as TotalNum
		from RunArcheryRank
		where RarTournament={$_SESSION['TourId']} and RarStartlist>0 
		group by RarStartlist
		having TotalNum>$MaxGroup");
	while($r=safe_fetch($q)) {
		$ret[]=$r->StartList;
		if(substr($r->StartList,-3)==':00') {
			$ret[]=substr($r->StartList,0,-3);
		}
	}
	return $ret;
}

/**
 * This function checks when group/target are set that there are less than 2 (group start) or SesAth4Target (target availability in single start)
 * @param $Team
 * @param $Event
 * @return array
 */
function searchRedTargets($Team, $Event) {
	$ret=[];
	$Concat=($Team ? "concat_ws('-', RarEvent, RarEntry, RarSubTeam)" : "concat_ws('-', RarEvent, RarEntry)");
	$SQL=[];
	// Group starting, each has a single target to shoot on!
	// group starting is for phase > 0 or EvElimType=0
	$SQL[]="select count(*) as TotalNum, SesAth4Target, group_concat($Concat separator '|') as id
		from RunArcheryRank
		inner join Events on EvTournament=RarTournament and EvCode=RarEvent and EvTeamEvent=RarTeam
		inner join Session on SesTournament=RarTournament and SesType='Q' and SesOrder=1
		where RarTournament={$_SESSION['TourId']} and RarTarget>0 and (EvElimType=0 or RarPhase>0)
		group by RarStartlist, RarTarget
		having TotalNum>1";
	// Individual start, the number of people in the same group must not be more than the number of targets available
	$SQL[]="select count(*) as TotalNum, SesAth4Target, group_concat($Concat separator '|') as id
		from RunArcheryRank
		inner join Events on EvTournament=RarTournament and EvCode=RarEvent and EvTeamEvent=RarTeam
		inner join Session on SesTournament=RarTournament and SesType='Q' and SesOrder=1
		where RarTournament={$_SESSION['TourId']} and RarGroup>0 and EvElimType=1 and RarPhase=0
		group by RarGroup
		having TotalNum>SesAth4Target";
	$q=safe_r_sql("(" . implode(') UNION (', $SQL) . ")");
	while($r=safe_fetch($q)) {
		$tmp=[];
		foreach(explode('|', $r->id) as $a) {
			$b=explode('-', $a, 2);
			if($b[0]==$Event) {
				$ret[]=$b[1];
			}
		}
	}
	return $ret;
}

