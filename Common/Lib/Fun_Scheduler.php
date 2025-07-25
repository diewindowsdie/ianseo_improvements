<?php
require_once('Common/Lib/Fun_Phases.inc.php');
require_once('Common/Lib/Fun_DateTime.inc.php');
require_once('Common/Lib/CommonLib.php');

Class Scheduler {
	var $SingleDay='';
	var $FromDay='';
	var $TourId=0;
	var $TourCode='';
	var $ROOT_DIR='/';
	var $DayByDay=false;
	var $Finalists=false;
	var $Ranking=false;
	var $SesType='';
	var $SesFilter='';
	var $DateFormat= '%W, %M %D %Y';
	var $TimeFormat='%l:%i %p';
	var $Ods;
	var $SchedVersion='';
	var $SchedVersionDate='';
	var $SchedVersionNote='';
	var $SchedVersionText='';
	var $FopVersion='';
	var $FopVersionDate='';
	var $FopVersionNote='';
	var $FopVersionText='';
	var $LastUpdate='';
	var $Groups=array();
	var $ActiveSessions=array();
	var $Schedule=array();
	var $FopLocations=array();
	var $SplitLocations=false;
	var $DaysToPrint=array();
	var $LocationsToPrint=array();
	var $PageBreaks=array();
	var $RunningEvents=array();
	var $HasArchers=false;
	var $TargetsInvolved='';
    var $PoolMatchWinners=array();
    var $PoolMatchWinnersWA=array();
    var $PoolMatches=array();
    var $PoolMatchesWA=array();
    var $TimeZoneOffset='00:00';

	function __destruct() {
		DefineForcePrintouts($this->TourId, true);
	}

	function __construct($TourId=0) {
		if($TourId) {
			$q=safe_r_sql("select ToCode, ToId, ToTimeZone from Tournament where ToId={intval($TourId)}");
			if($r=safe_fetch($q)) {
				$this->TourId=$r->ToId;
				$this->TourCode=$r->ToCode;
                $this->TimeZoneOffset=$r->ToTimeZone;
			}
		} else {
			$this->TourId=$_SESSION['TourId'];
			$this->TourCode=$_SESSION['TourCode'];
            $this->TimeZoneOffset=$_SESSION['TourTimezone'];
		}
		if(!$this->TourId) {
			return false;
		}

		if(!empty($_SESSION['ActiveSession'])) {
			$this->ActiveSessions=$_SESSION['ActiveSession'];
		} elseif($tmp=Get_Tournament_Option('ActiveSession', '', $this->TourId)) {
			$this->ActiveSessions=$tmp;
		}

		DefineForcePrintouts($this->TourId);

		$q=safe_r_sql("select concat(DvMajVersion, '.', DvMinVersion) as DocVersion, DvPrintDateTime,
				date_format(DvPrintDateTime, '%e %b %Y %H:%i UTC') as DocVersionDate,
				DvPrintDateTime,
				DvNotes as DocNotes
				from DocumentVersions
				where DvTournament='{$this->TourId}' and DvFile='SCHED'");
		if($r=safe_fetch($q)) {
			$this->SchedVersion=$r->DocVersion;
			$this->SchedVersionDate=$r->DocVersionDate;
			$this->SchedVersionNote=$r->DocNotes;
			$this->SchedVersionText=trim('Vers. '.$r->DocVersion . " ($r->DocVersionDate) $r->DocNotes");
			$this->LastUpdate=$r->DvPrintDateTime;
		}
		$q=safe_r_sql("select concat(DvMajVersion, '.', DvMinVersion) as DocVersion, DvPrintDateTime,
				date_format(DvPrintDateTime, '%e %b %Y %H:%i UTC') as DocVersionDate,
				DvNotes as DocNotes
				from DocumentVersions
				where DvTournament='{$this->TourId}' and DvFile='FOP'");
		if($r=safe_fetch($q)) {
			$this->FopVersion=$r->DocVersion;
			$this->FopVersionDate=$r->DocVersionDate;
			$this->FopVersionNote=$r->DocNotes;
			$this->FopVersionText=trim('Vers. '.$r->DocVersion . " ($r->DocVersionDate) $r->DocNotes");
			$this->LastUpdate=max($this->LastUpdate, $r->DvPrintDateTime);
		}

		/// get max scheduling... based on entries, qualification, finals, teams, eliminations AND SCHEDULE CHANGES
		$sql="(select max(greatest(EnTimestamp, QuTimestamp)) LastDate from Qualifications inner join Entries on EnId=QuId where EnTournament={$this->TourId})
			union
			(select max(ElDateTime) from Eliminations where ElTournament={$this->TourId})
			union
			(select max(FinDateTime) LastDate from Finals where FinTournament={$this->TourId})
			union
			(select max(TfDateTime) LastDate from TeamFinals where TfTournament={$this->TourId})
			union
			(select max(SchTimestamp) LastDate from Scheduler where SchTournament={$this->TourId})
			order by LastDate desc";
		$q=safe_r_SQL(($sql));
		if($r=safe_fetch($q)) {
			$this->LastUpdate=max($this->LastUpdate, $r->LastDate);
		}

		$this->FopLocations=Get_Tournament_Option('FopLocations', array());

		$this->PoolMatchWinners=getPoolMatchesWinners();
		$this->PoolMatchWinnersWA=getPoolMatchesWinnersWA();
		$this->PoolMatches=getPoolMatchesShort();
		$this->PoolMatchesWA=getPoolMatchesShortWA();

		// Get all Events by Session
		$t=safe_r_SQL("select EvCode, EvTeamEvent, QuSession, EnAthlete
			from Entries
			INNER JOIN Qualifications on QuId=EnId
			INNER JOIN EventClass ON EcClass=EnClass AND EcDivision=EnDivision AND EcTournament=EnTournament and if(EcSubClass='', true, EcSubClass=EnSubClass)
			INNER JOIN Events on EvCode=EcCode AND EvTeamEvent=IF(EcTeamEvent!=0, 1,0) AND EvTournament=EcTournament
			where EnTournament=$this->TourId
			group by EvCode, EvTeamEvent, QuSession, EnAthlete
			order by EvTeamEvent, EvProgr");
		while($u=safe_fetch($t)) {
			$this->RunningEvents[$u->QuSession][$u->EvTeamEvent][]='Event[]='.$u->EvCode;
			if($u->EnAthlete) {
				$this->HasArchers=true;
			}
		}

	}

	function push($r, $Warmup=false, $HasWarmup=false) {
		static $Shift=0, $Day='';
		static $PushKey='';

		$tmpKey="$r->Day|$r->Start|$r->Events|$r->Session|$r->OrderPhase";
		if($PushKey==$tmpKey and !$Warmup) return;

		if($tmpKey and !$Warmup) $PushKey=$tmpKey;
		$tmp=new StdClass();

		// reset shift if day is different
		if($Shift and $Day!=$r->Day) {
			$Shift=0;
			$Day='';
		}

		// if a shift is defined then changes the shift
		if(!empty($r->SchDelay)) {
			if($r->SchDelay==-1) {
				$Shift=0;
				$Day='';
			} else {
				$Shift=$r->SchDelay;
				$Day=$r->Day;
			}
		}

		$tmp->Type=$r->Type;
		$tmp->Title=get_text($r->Type.'-Session', 'Tournament');
		$tmp->SubTitle=$r->SesName;
		$tmp->Text='';
		$tmp->Warmup=$Warmup;
		$tmp->Day=$r->Day;
		$tmp->Events=$r->Events;
		$tmp->Event=$r->Event;
		$tmp->Session=$r->Session;
		$tmp->Distance=$r->Distance;
		$tmp->RealDistance=$r->RealDistance;
		$tmp->DistanceName=((!empty($r->{'TD'.$r->Distance}) and !strchr($r->{'TD'.$r->Distance}, '£££')) ? $r->{'TD'.$r->Distance} : get_text('Distance', 'Tournament'). ' '.$r->Distance);
		$tmp->Order=$r->OrderPhase;
		$tmp->Shift=$Shift;
		$tmp->SO=$r->EvShootOff;
		$tmp->grPos=$r->grPos;
		$tmp->ElimType=$r->EvElimType;
		$tmp->UID=$r->UID.($Warmup ? 'W' : '');
		$tmp->RowLocation=($r->RowLocation??'');

		switch($r->Type) {
			case 'Q':
			case 'E':
				$tmp->SubTitle=$r->SesName ? $r->SesName : get_text('Session'). ' ' . $r->Session;
				if($r->Options and $Warmup) {
					$tmp->Text=$r->Options;
				} else {
					$tmp->Text=$r->SesName ? $r->SesName : get_text('Session'). ' ' . $r->Session;
				}
				$tmp->Target=$r->BestTarget;
				break;
			case 'Z':
				$tmp->Title=$r->SesName;
				$tmp->SubTitle=$r->Options;
				$tmp->Text=$r->Events;
				$tmp->Target=$r->BestTarget;
				break;
			case 'R':
				list($LevelName, $Level, $GroupName, $Group, $Round)=explode('|', $r->Session);
				$tmp->Text=($LevelName ?: get_text('LevelNum', 'RoundRobin', $Level)).' '.($GroupName ?: get_text('GroupNum', 'RoundRobin', $Group)).' '.get_text('RoundNum', 'RoundRobin', $Round);
				$tmp->SubTitle=$r->Options;
				// $tmp->Text=$r->Events;
				$tmp->Target=$r->BestTarget;
				break;
			case 'RA':
				list($Phase, $Pool, $Group)=explode('-', $r->Session);
				$tmp->Title=get_text('PhaseName-'.$Phase, 'RunArchery');
				$tmp->SubTitle=$r->Options;
				$tmp->Text=$r->Events;
				$tmp->Target=$r->BestTarget;
				if($Phase==1) {
					// finals
					$tmp->Events=get_text('Final'.$Pool,'RunArchery');
				} elseif($Phase==2) {
					$tmp->Events=get_text('SemiFinalName','RunArchery', $Pool);
				} elseif($Group) {
					$tmp->Events=get_text('GroupNum','RoundRobin', $Group);
				} else {
					// $tmp->Events=get_text('AllEntries','Tournament');
					$tmp->Events='';
				}
				break;
			default:
				$ses=namePhase($r->Distance, $r->Session);
				if(empty($tmp->Text)) $tmp->Text='';
				if($r->Type=='R') {
					list($Phase, $Round, $Group) = explode('-', $r->Session);
					$tmp->Text=', Phase '.$Phase.' Round '.$Round.' Group '.$Group;
				} else {
					if($r->EvFirstRank!=1) {
						switch($ses) {
							case 0:
								$Num=$r->EvFirstRank.'-'.($r->EvFirstRank+1);
								break;
							case 1:
								$Num=($r->EvFirstRank+2).'-'.($r->EvFirstRank+3);
								break;
							default:
								$Num=$r->EvFirstRank.'-'.($r->EvFirstRank+(2 * $ses)-1);
						}
						$tmp->Text.=', '. get_text('MatchSecFinals', 'Tournament', $Num);
					} else {
						$tmp->Text.=', '. get_text($ses . '_Phase' . (!$r->Medal && $ses<=1 ? "NM":""));
					}
				}
				if($tmp->Text[0]==',') $tmp->Text=substr($tmp->Text,2);
				// check if there is a location
				if($r->BestTarget and empty($_REQUEST['NoLocations']) and !empty($this->FopLocations) and $r->Locations) {
					$tmp->Events.= " ($r->Locations)";
				}
				break;
		}

		if($Warmup) {
			$tmp->Start=($r->WarmStart?:$r->grPos);
			$tmp->Duration=$r->WarmDuration;
			$tmp->Comments=$r->Options;
		} else {
			$tmp->Start=$r->Start;
			$tmp->Duration=$r->Duration;
			$tmp->Comments=($HasWarmup ? '' : $r->Options);
		}

		$Session=($r->EvFirstRank>1 and $r->Session==0) ? 1 : $r->Session;
		if(empty($this->Schedule[$tmp->Day][$tmp->Start][$Session][$r->Distance])) {
			$this->Schedule[$tmp->Day][$tmp->Start][$Session][$r->Distance]=array();
		}
		if(!in_array($tmp, $this->Schedule[$tmp->Day][$tmp->Start][$Session][$r->Distance])) {
			$this->Schedule[$tmp->Day][$tmp->Start][$Session][$r->Distance][] = $tmp;
		}
		$this->Groups[$tmp->Type][$Session][$r->Distance][$tmp->Day][$tmp->Start][]=$tmp;
		if($tmp->Type=='RA' and $Warmup and $r->WarmStart and $r->grPos) {
			$tmp2=clone $tmp;
			$tmp2->Start=$r->grPos;
			$tmp2->Duration=0;
			$this->Schedule[$tmp2->Day][$tmp2->Start][$Session][$r->Distance][] = $tmp2;
			$this->Groups[$tmp2->Type][$Session][$r->Distance][$tmp2->Day][$tmp2->Start][]=$tmp2;
		}
	}

	function GetSchedule() {
		$LocField="'' as Locations,";
		if(empty($_REQUEST['NoLocations']) and $this->FopLocations) {
			$LocField="";
			foreach($this->FopLocations as $loc) {
				$LocField.=" when %1\$s between {$loc->Tg1} and {$loc->Tg2} then '{$loc->Loc}' ";
			}
			$LocField="case {$LocField} end as Locations,";
		}

		$LocGrouping='';
		if($this->SplitLocations) {
			$LocGrouping='Locations, ';
		}

		$tmpExtra=array();
		$tmpButts=array();
		if($this->LocationsToPrint) {
			$tmp=array();
			foreach($this->LocationsToPrint as $k) {
				$tmp[]="Locations in ('', ".StrSafe_DB($k->Loc).")";
				$tmpButts[]='%1$s between '. $k->Tg1 .' and '. $k->Tg2;
			}
			$tmpExtra[]='('.implode(' or ', $tmp).')';
			$this->TargetsInvolved=implode(' or ', $tmpButts);
		}

		if($this->DaysToPrint) {
			$tmp=array();
			foreach($this->DaysToPrint as $k) {
				$tmp[]="Day='$k'";
			}
			$tmpExtra[]='('.implode(' or ', $tmp).')';
		}
		$ExtraWheres=implode(' and ', $tmpExtra);

		$SQL=array();
		// First gets the Texts: titles and description for a given time always go before everything else
		// getting them first to seed the array!
		if(!$this->SesType or strstr($this->SesType, 'Z')) {
			$SQL[]="select distinct
                    SchUID as UID,
					'' EvShootOff,
					'1' EvFirstRank,
					'' EvElimType,
					'' grPos,
					SchTargets as `BestTarget`,
					'Z' Type,
					SchDay Day,
					'-' Session,
					'-' Distance,
					'' RealDistance,
					'' Medal,
					if(SchStart=0, '', date_format(SchStart, '%H:%i')) Start,
					SchDuration Duration,
					'' WarmStart,
					'' WarmDuration,
					SchSubTitle Options,
					SchTitle SesName,
					SchText Events,
					'' Event,
					'' as Locations,
					SchLocation as RowLocation,
					SchOrder OrderPhase,
					SchShift SchDelay,
					'' TD1, '' TD2, '' TD3, '' TD4, '' TD5, '' TD6, '' TD7, '' TD8
				from Scheduler
				inner join Tournament on ToId=SchTournament
				where SchTournament=$this->TourId
					and SchDay>0 and SchStart>0
					".($this->SingleDay ? " and SchDay='$this->SingleDay'" : '')."
					".($this->FromDay ? " and SchDay>='$this->FromDay'" : '')."
					";
		}

		// Then gets the qualification rounds
		if(!$this->SesType or strstr($this->SesType, 'Q')) {
			/*

						$DistanceNames=' left join (select TdTournament, QuSession';
			for($i=1; $i<=8; $i++) {
				$DistanceNames.=", group_concat(distinct Td{$i} separator '£££') as Td{$i}";
			}
			$DistanceNames.=" from TournamentDistances
						inner join Entries on EnTournament=TdTournament and concat(EnDivision,EnClass) like TdClasses
						inner join Qualifications on QuId=EnId
						where TdTournament={$this->TourId}
						group by Td1, Td2, Td3, Td4, Td5, Td6, Td7, Td8, QuSession)
					Td on TdTournament=DiTournament and @col:=elt(DiDistance, Td1, Td2, Td3, Td4, Td5, Td6, Td7, Td8) and @col is not null and @col!='-' and QuSession=DiSession";

			*/
			$DistanceNames='';

			if($this->HasArchers) {
				for($i=1; $i<=8; $i++) {
					$DistanceNames.=" left join (select group_concat(distinct Td{$i} separator '£££') as Td{$i}, TdClasses as Td{$i}Classes from TournamentDistances where TdTournament={$this->TourId}) \n";
					$DistanceNames.=" Td{$i} on DiDistance={$i} and concat(EnDivision,EnClass) like Td{$i}Classes ";
				}
				$SQL[]="select distinct
    					concat_ws('-', DiDistance, DiSession, DiType) as UID,
						'' EvShootOff,
						'1' EvFirstRank,
						'' EvElimType,
						'' grPos,
						DiTargets as `BestTarget`,
						DiType Type,
						DiDay Day,
						DiSession Session,
						DiDistance Distance,
						DiDistance RealDistance,
						'' Medal,
						if(DiStart=0, '', date_format(DiStart, '%H:%i')) Start,
						DiDuration Duration,
						if(DiWarmStart=0, '', date_format(DiWarmStart, '%H:%i')) WarmStart,
						DiWarmDuration WarmDuration,
						DiOptions Options,
						SesName,
						'' Events,
						'' Event,
						'' as Locations,
						SesLocation as RowLocation,
						DiSession OrderPhase,
						DiShift SchDelay,
						if(DiDistance=1, group_concat(distinct Td1 separator '£££'), '') as TD1, 
						if(DiDistance=2, group_concat(distinct Td2 separator '£££'), '') as TD2, 
						if(DiDistance=3, group_concat(distinct Td3 separator '£££'), '') as TD3, 
						if(DiDistance=4, group_concat(distinct Td4 separator '£££'), '') as TD4, 
						if(DiDistance=5, group_concat(distinct Td5 separator '£££'), '') as TD5, 
						if(DiDistance=6, group_concat(distinct Td6 separator '£££'), '') as TD6, 
						if(DiDistance=7, group_concat(distinct Td7 separator '£££'), '') as TD7, 
						if(DiDistance=8, group_concat(distinct Td8 separator '£££'), '') as TD8 
					from DistanceInformation
					inner join Session on SesTournament=DiTournament and SesOrder=DiSession and SesType=DiType and SesType='Q'
					inner join TournamentDistances on TdTournament=DiTournament
                    inner join Tournament on ToId=DiTournament
					left join (select EnTournament, concat(EnDivision,EnClass) as Category, QuSession
						from Entries
						inner join Qualifications on QuId=EnId and QuSession>0
						Where EnTournament=$this->TourId
						group by EnDivision, EnClass, QuSession) Entries on EnTournament=SesTournament and QuSession=SesOrder and Category like TdClasses
					
					where DiTournament=$this->TourId
						and DiDay>0 and (DiStart>0 or DiWarmStart>0)
						" .($this->SingleDay ? " and DiDay='$this->SingleDay'" : '') ."
						" .($this->FromDay ? " and DiDay>='$this->FromDay'" : '') ."
						" .(strlen($this->SesFilter) ? " and DiSession='$this->SesFilter'" : '') ."  
					group by DiDistance, DiSession, DiType 
					order by DiDay, DiStart, DiWarmStart, DiSession, DiDistance";
			} else {
				for($i=1; $i<=8; $i++) {
					$DistanceNames.=" left join (select TdTournament as Td{$i}Tournament, group_concat(distinct Td{$i} order by Td{$i} desc separator '£££') as Td{$i}, QuSession as Td{$i}Session from TournamentDistances inner join Entries on EnTournament=TdTournament and concat(EnDivision,EnClass) like TdClasses inner join Qualifications on QuId=EnId where TdTournament={$this->TourId} group by Td{$i}Session) Td{$i} on Td{$i}Tournament=DiTournament and DiDistance={$i} and Td{$i}Session=DiSession \n";
				}

				$SQL[]="select distinct
    					concat_ws('-', DiDistance, DiSession, DiType) as UID,
						'' EvShootOff,
						'1' EvFirstRank,
						'' EvElimType,
						'' grPos,
						DiTargets as `BestTarget`,
						DiType Type,
						DiDay Day,
						DiSession Session,
						DiDistance Distance,
						DiDistance RealDistance,
						'' Medal,
						if(DiStart=0, '', date_format(DiStart, '%H:%i')) Start,
						DiDuration Duration,
						if(DiWarmStart=0, '', date_format(DiWarmStart, '%H:%i')) WarmStart,
						DiWarmDuration WarmDuration,
						DiOptions Options,
						SesName,
						'' Events,
						'' Event,
						'' as Locations,
						SesLocation as RowLocation,
						DiSession OrderPhase,
						DiShift SchDelay,
						TD1, TD2, TD3, TD4, TD5, TD6, TD7, TD8
					from DistanceInformation
                    inner join Tournament on ToId=DiTournament
					INNER join Session on SesTournament=DiTournament and SesOrder=DiSession and SesType=DiType and SesType='Q'
					$DistanceNames
					where DiTournament=$this->TourId
						and DiDay>0 and (DiStart>0 or DiWarmStart>0)
						".($this->SingleDay ? " and DiDay='$this->SingleDay'" : '')."
						".($this->FromDay ? " and DiDay>='$this->FromDay'" : '')."
						".(strlen($this->SesFilter) ? " and DiSession='$this->SesFilter'" : '')."
					order by DiDay, DiStart, DiWarmStart, DiSession, DiDistance";
			}

			//$DistanceNames="left join (select * from TournamentDistances where TdTournament=$this->TourId group by TdTournament having count(*)=1) TD on TdTournament=SesTournament \n";
		}

		// Then gets the Elimination rounds
		if(!$this->SesType or strstr($this->SesType, 'E')) {
			$SQL[]="select distinct
                    concat_ws('-', DiDistance, DiSession, DiType) as UID,
					'' EvShootOff,
					'1' EvFirstRank,
					'' EvElimType,
					'' grPos,
					'0' as `BestTarget`,
					'E' Type,
					DiDay Day,
					DiSession Session,
					DiDistance Distance,
					DiDistance RealDistance,
					'' Medal,
					if(DiStart=0, '', date_format(DiStart, '%H:%i')) Start,
					DiDuration Duration,
					if(DiWarmStart=0, '', date_format(DiWarmStart, '%H:%i')) WarmStart,
					DiWarmDuration WarmDuration,
					DiOptions Options,
					SesName,
					Events,
					'' Event,
					'' as Locations,
					SesLocation as RowLocation,
					DiSession OrderPhase,
					DiShift SchDelay,
					'' TD1, '' TD2, '' TD3, '' TD4, '' TD5, '' TD6, '' TD7, '' TD8
				from Session
                inner join Tournament on ToId=SesTournament
				inner join (select distinct ElSession, ElTournament, ElElimPhase, group_concat(distinct ElEventCode order by ElEventCode separator ', ') Events from Eliminations where ElTournament=$this->TourId group by ElTournament, ElSession, ElElimPhase) Phase on ElSession=SesOrder and ElTournament=SesTournament
				inner join DistanceInformation on SesTournament=DiTournament and SesOrder=DiSession and ElElimPhase=DiDistance and DiType='E'
				where DiTournament=$this->TourId
					and DiDay>0 and (DiStart>0 or DiWarmStart>0)
					".($this->SingleDay ? " and DiDay='$this->SingleDay'" : '')."
					".($this->FromDay ? " and DiDay>='$this->FromDay'" : '')."
				order by DiDay, DiStart, DiWarmStart, DiSession, DiDistance";
		}

		// Get all the Free warmups
		if(!$this->SesType or strstr($this->SesType, 'F')) {
			$SQL[]="select distinct
                group_concat(distinct FwEvent order by FwEvent separator '-') as UID,
				'' EvShootOff,
				'1' EvFirstRank,
				'' EvElimType,
				'' grPos,
				'0' as `BestTarget`,
				if(FwTeamEvent=0, 'I', 'T') Type,
				FwDay Day,
				'' Session,
				'' Distance,
				EvDistance as RealDistance,
				'' Medal,
				date_format(FwTime, '%H:%i') Start,
				FwDuration Duration,
				date_format(FwTime, '%H:%i') WarmStart,
				FwDuration WarmDuration,
				FwOptions Options,
				'' SesName,
				if(count(*)=2, group_concat(distinct EvEventName order by EvEventName separator ', '), group_concat(distinct FwEvent order by FwEvent separator ', ')) Events,
				group_concat(distinct FwEvent order by FwEvent separator '\',\'') Event,
				'' as Locations,
				'' as RowLocation,
				'' OrderPhase,
				'' SchDelay,
				'' TD1, '' TD2, '' TD3, '' TD4, '' TD5, '' TD6, '' TD7, '' TD8

				from FinWarmup
				inner join Events on FwEvent=EvCode and EvTeamEvent=FwTeamEvent and EvTournament=FwTournament
				where FwTournament=$this->TourId
					and FwMatchTime=0
				group by FwTeamEvent, FwDay, FwTime
				";
		}

		// Get all the matches
		if(!$this->SesType or strstr($this->SesType, 'F')) {
			// get all the named sessions
			$SQL[]="select distinct
	                '' as UID,
					'' EvShootOff,
					'1' EvFirstRank,
					'' EvElimType,
					'' grPos,
					'0' as `BestTarget`,
					'Z' Type,
					date_format(SesDtStart, '%Y-%m-%d') Day,
					'-' Session,
					'-' Distance,
	                '' as RealDistance,
					'' Medals,
					if(SesDtStart=0, '', date_format(SesDtStart, '%H:%i')) Start,
					0 Duration,
					'' WarmStart,
					0 WarmDuration,
					0 Options,
					SesName,
					'' Events,
					'' Event,
					'' as Locations,
					sesLocation as RowLocation,
					0 OrderPhase,
					0 SchDelay,
					'' TD1, '' TD2, '' TD3, '' TD4, '' TD5, '' TD6, '' TD7, '' TD8
				from Session
				inner join Tournament on ToId=SesTournament
				where SesTournament=$this->TourId
					and SesName!=''
					and SesDtStart>0
					".($this->SingleDay ? " and date(SesDtStart)='$this->SingleDay'" : '')."
					".($this->FromDay ? " and date(SesDtStart)>='$this->FromDay'" : '')."
				order by SesDtStart";

			$SQL[]="select distinct
                concat_ws('-', FsTeamEvent, group_concat(distinct FsEvent order by EvProgr separator '-'), sum(FsMatchNo)) as UID,
				EvShootOff,
				EvWinnerFinalRank as EvFirstRank,
				EvElimType,
				EvFinalFirstPhase=48 or EvFinalFirstPhase = 24 As grPos,
				max(FsTarget*1) as `BestTarget`,
				if(FsTeamEvent=0, 'I', 'T') Type,
				FsScheduledDate Day,
				GrPhase Session,
				if(EvWinnerFinalRank>1, 1, EvFinalFirstPhase) Distance,
				EvDistance as RealDistance,
				EvMedals as Medal,
				if(FsScheduledTime=0, '', date_format(FsScheduledTime, '%H:%i')) Start,
				FsScheduledLen Duration,
				if(FwTime=0, '', date_format(FwTime, '%H:%i')) WarmStart,
				FwDuration WarmDuration,
				FwOptions Options,
				'' SesName,
				if(count(*)<=2 and EvCodeParent='', group_concat(distinct EvEventName order by EvProgr separator ', '), group_concat(distinct FsEvent order by EvProgr separator ', ')) Events,
				group_concat(distinct FsEvent order by EvProgr separator '\',\'') Event,
				".sprintf($LocField, 'FsTarget*1')."
				'' as RowLocation,
				cast(if(EvWinnerFinalRank>1, EvWinnerFinalRank*100 + GrPhase, 1+(1/(1+GrPhase))) as decimal(15,4)) as OrderPhase,
				FsShift SchDelay,
					'' TD1, '' TD2, '' TD3, '' TD4, '' TD5, '' TD6, '' TD7, '' TD8

				from FinSchedule
				inner join Tournament on ToId=FsTournament
				inner join Events on FsEvent=EvCode and FsTeamEvent=EvTeamEvent and FsTournament=EvTournament
				inner join Grids on FsMatchNo=GrMatchNo
				left join FinWarmup on FsEvent=FwEvent and FsTeamEvent=FwTeamEvent and FsTournament=FwTournament and FsScheduledDate=FwDay and FsScheduledTime=FwMatchTime
				where FsTournament=$this->TourId
					and FsScheduledDate>0 and (FsScheduledTime>0 or FwTime>0)
					".($this->SingleDay ? " and FSScheduledDate='$this->SingleDay'" : '')."
					".($this->FromDay ? " and FSScheduledDate>='$this->FromDay'" : '')."
				group by /*if(EvElimType>=3, FsMatchNo, 0), */FsTeamEvent, FsScheduledDate, FsScheduledTime, Locations, if(EvWinnerFinalRank>1, EvWinnerFinalRank*100-GrPhase, GrPhase), FwTime
				order by FsTeamEvent, FsScheduledDate, FsScheduledTime, EvFirstRank, GrPhase, FwTime
				";
		}

		// Get all the Round Robins
		if(!$this->SesType or strstr($this->SesType, 'F')) {
			$LocationFields=sprintf($LocField, 'RrMatchTarget*1');
			$Date="RrMatchScheduledDate>0";
			if($this->SingleDay) {
				$Date="RRMatchScheduledDate='$this->SingleDay'";
			} elseif($this->FromDay) {
				$Date="RRMatchScheduledDate>='$this->FromDay'";
			}
			$SQL[]="select distinct
                concat_ws('-',group_concat(distinct RrMatchEvent order by EvProgr separator '-'), (RrMatchLevel*1000000)+(RrMatchGroup*10000)+(RrMatchRound*100), sum(RrMatchMatchNo)) as UID,
				EvShootOff,
				EvWinnerFinalRank as EvFirstRank,
				EvElimType,
				EvFinalFirstPhase=48 or EvFinalFirstPhase = 24 As grPos,
				max(RrMatchTarget*1) as `BestTarget`,
				'R' Type,
				RrMatchScheduledDate Day,
				concat_ws('|', RrLevName, RrMatchLevel, RrGrName, RrMatchGroup, RrMatchRound) Session,
				RrMatchRound Distance,
				EvDistance as RealDistance,
				EvMedals as Medal,
				if(RrMatchScheduledTime=0, '', date_format(RrMatchScheduledTime, '%H:%i')) Start,
				RrMatchScheduledLength Duration,
				if(FwTime=0, '', date_format(FwTime, '%H:%i')) WarmStart,
				FwDuration WarmDuration,
				FwOptions Options,
				'' SesName,
				if(count(*)<=2 and EvCodeParent='', group_concat(distinct EvEventName order by EvProgr separator ', '), group_concat(distinct RrMatchEvent order by EvProgr separator ', ')) Events,
				group_concat(distinct RrMatchEvent order by EvProgr separator '\',\'') Event,
				$LocationFields
				'' as RowLocation,
				(RrMatchLevel*1000000)+(RrMatchGroup*10000)+(RrMatchRound*100) as OrderPhase,
				'' SchDelay,
					'' TD1, '' TD2, '' TD3, '' TD4, '' TD5, '' TD6, '' TD7, '' TD8
				from RoundRobinMatches
                inner join Tournament on ToId=RrMatchTournament
			    inner join RoundRobinGroup on RrGrTournament=RrMatchTournament and RrGrTeam=RrMatchTeam and RrGrEvent=RrMatchEvent and RrGrLevel=RrMatchLevel and RrGrGroup=RrMatchGroup
			    inner join RoundRobinLevel on RrLevTournament=RrMatchTournament and RrLevTeam=RrMatchTeam and RrLevEvent=RrMatchEvent and RrLevLevel=RrMatchLevel
				inner join Events on EvCode=RrMatchEvent and EvTeamEvent=RrMatchTeam and EvTournament=RrMatchTournament
				left join FinWarmup on FwEvent=RrMatchEvent and FwTeamEvent=RrMatchTeam and FwTournament=RrMatchTournament and FwDay=RrMatchScheduledDate and FwMatchTime=RrMatchScheduledTime
				where RrMatchTournament=$this->TourId
					and $Date and (RrMatchScheduledTime>0 or FwTime>0) 
				group by RrMatchTeam, RrMatchScheduledDate, RrMatchScheduledTime, RrMatchLevel, RrMatchGroup, RrMatchRound, Locations, FwTime
				order by RrMatchTeam, RrMatchScheduledDate, RrMatchScheduledTime, RrMatchLevel, RrMatchGroup, RrMatchRound, OrderPhase, FwTime
				";
		}

		// Get all the RunArchery Schedules
		if(!$this->SesType or strstr($this->SesType, 'F')) {
			$LocationFields=sprintf($LocField, '0');
			$Date="RarStartlist>0";
			if($this->SingleDay) {
				$Date="date(RarStartlist)='$this->SingleDay'";
			} elseif($this->FromDay) {
				$Date="date(RarStartlist)>='$this->FromDay'";
			}
			$SQL[]="select distinct
                concat_ws('-', RarTeam, RarEvent, RarPhase, RarPool, RarGroup) as UID,
				EvShootOff,
				EvWinnerFinalRank as EvFirstRank,
				EvElimType,
				if(RarCallTime=0, '', date_format(RarCallTime, '%H:%i')) As grPos,
				max(RarGroup) as `BestTarget`,
				'RA' Type,
				date(RarStartlist) Day,
				concat_ws('-', RarPhase, RarPool, RarGroup) Session,
				18 as Distance,
				EvDistance as RealDistance,
				EvMedals as Medal,
				if(RarStartlist=0, '', date_format(min(RarStartlist), '%H:%i')) Start,
				RarDuration Duration,
				if(RarWarmup=0, '', date_format(RarWarmup, '%H:%i')) as WarmStart,
				RarWarmupDuration WarmDuration,
				RarNotes Options,
				'' SesName,
				group_concat(distinct EvEventName order by EvProgr separator ', ') as Events,
				group_concat(distinct RarEvent order by EvProgr separator '\',\'') Event,
				$LocationFields
				'' as RowLocation,
				(RarPhase*1000000)+(RarPool*10000)+(RarGroup*100) as OrderPhase,
				RarShift as SchDelay,
					'' TD1, '' TD2, '' TD3, '' TD4, '' TD5, '' TD6, '' TD7, '' TD8
				from RunArcheryRank
                inner join Tournament on ToId=RarTournament
				inner join Events on EvCode=RarEvent and EvTeamEvent=RarTeam and EvTournament=RarTournament
				where RarTournament=$this->TourId
					and $Date and RarStartlist>0
				group by RarTeam, if(EvElimType=0 or RarPhase>0, RarStartlist, EvCode), if(EvElimType=0 or RarPhase>0, RarPool, RarGroup), Locations
				order by RarTeam, RarStartlist, OrderPhase
				";
		}

		$sql='select * from (('.implode(') UNION (', $SQL).')) 
			as Schedule 
			'.($ExtraWheres ? ' where '.$ExtraWheres : '').'
			order by Day, if(Start>0, if(WarmStart>0, least(Start, WarmStart), Start), WarmStart), Type!=\'Z\', OrderPhase+0 asc, Distance, `BestTarget`=0';

		$q=safe_r_SQL($sql);
		$debug=array();

		while($r=safe_fetch($q)) {
			if($r->WarmStart or ($r->Type=='RA' and $r->grPos)) {
				$this->push($r, true);
			}
			if($r->Start) {
				$this->push($r, false, $r->WarmStart or ($r->Type=='RA' and $r->grPos));
			}
				//$debug[]=$r;
		}

		return $this->Schedule;
	}

	/**
	 * @param string $Type
	 * Default value is IS, other values: SET, SHOW
	 * @return string
	 *
	 * Returns the HTML representation of the Schedule
	 */
	function getScheduleHTML($Type='IS', $Title='', $timeOffset=0) {
		$TourCode=(empty($_SESSION['code']) ? '' : '&code='.$_SESSION['code']);
		$ret=array();
		if($Title) $ret[]='<tr><th colspan="2" class="SchHeadTitle">'.$Title.'</th></tr>';
		foreach($this->GetSchedule() as $Date => $Times) {
			$ret[]='<tr><th colspan="2" class="SchDay">'.formatTextDate($Date, true).'</th></tr>';
			$OldTitle='';
			$OldSubTitle='';
			$OldText='';
			$OldType='';
			$OldStart='';
			$OldEnd='';
			$IsTitle=false;

			$OldComment='';
			ksort($Times);
			foreach($Times as $Time => $Sessions) {
				$Singles=array();
				foreach($Sessions as $Session => $Distances) {
					foreach($Distances as $Distance => $Items) {
						foreach($Items as $k => $Item) {
							$key=$Item->Day
								.'|'.$Time
								.'|'.$Session
								.'|'.$Distance
								.'|'.round((float) $Item->Order, 4);
							if($Item->Comments) {
								$SingleKey="{$Item->Duration}-{$Item->Title}-{$Item->SubTitle}-{$Item->Comments}";
								if(in_array($SingleKey, $Singles)) continue;
								$Singles[]=$SingleKey;
							}

							$ActiveSession=in_array($key, $this->ActiveSessions);

							$timing='';
                            $extraTimeInfo = '';
                            $Item->StartView = $Item->Start;
                            if($Type=='IS' and $timeOffset!=0) {
                                $origDateTime =  new DateTime($Item->Day . ' ' . $Item->Start);
                                $Item->StartView = ($origDateTime->modify($timeOffset . ' hours'))->format('H:i');
                                if(($origDateTime->format("d")) != ((new DateTime($Item->Day . ' ' . $Item->Start))->format('d'))) {
                                    $extraTimeInfo = '&nbsp;<b>('.get_text(($timeOffset > 0 ? 'OneDayAfter' : 'OneDayBefore'),'InfoSystem'). ')</b>&nbsp;';
                                } else {
                                    $extraTimeInfo = '';
                                }
                            }
							if($Item->Type=='Z') {
								// free text
								$timing=$Item->StartView.$extraTimeInfo.($Item->Duration ? '-'.addMinutes($Item->StartView, $Item->Duration) : '');

								if(empty($Item->UID) and $Type=='SET') {
									$SchUid=md5(uniqid(mt_rand(), true));
									$Item->UID=$SchUid;
									safe_w_SQL("update ignore Scheduler set SchUID='$SchUid'
										where SchTournament={$this->TourId} 
										and SchOrder={$Item->Order} 
										and SchDay='{$Item->Day}' 
										and SchStart='{$Item->Start}'");
								}
								if($OldTitle!=$Item->Title and $Item->Title) {
									if(!$IsTitle) {
										$tmp='<tr name="'.$key.'"'.(($ActiveSession and !$Item->SubTitle and !$Item->Text) ? ' class="active"' : '').'><td>';
										$txt=$Item->Title;
										if($Type=='SET') {
											$txt='<a href="?Activate='.$key.'">'.strip_tags($txt).'</a>';
										}

										$tmp.='</td><td class="SchTitle">'.$txt.(($Item->SubTitle or $Item->Text or !$Item->RowLocation) ? '' : ' ('.$Item->RowLocation.')').'</td></tr>';
										$ret[]=$tmp;
									}
									$OldTitle=$Item->Title;
									$OldSubTitle='';
									$IsTitle=true;
								}
								if($OldSubTitle!=$Item->SubTitle and $Item->SubTitle) {
									$tmp='<tr name="'.$key.'"'.(($ActiveSession and !$Item->Text) ? ' class="active"' : '').'><td>';
									if(!$Item->Text) {
										$tmp.=$timing . ($Item->Shift && $timing ? ($Type=='IS' ? '<span class="SchDelay">' : '') . '&nbsp;+' . $Item->Shift . ($Type=='IS' ? '</span>' : ''): "");
										$timing='';
									}
									$txt=$Item->SubTitle;
									if($Type=='SET') {
										$txt='<a href="?Activate='.$key.'">'.strip_tags($txt).'</a>';
									}
									$tmp.='</td><td class="SchSubTitle">'.$txt.(($Item->Text or !$Item->RowLocation) ? '' : ' ('.$Item->RowLocation.')').'</td></tr>';
									$ret[]=$tmp;
									$OldSubTitle=$Item->SubTitle;
									$IsTitle=false;
								}
								if($Item->Text) {
									$txt=$Item->Text.($Item->RowLocation ? ' ('.$Item->RowLocation.')' : '');
                                    $OldText=$txt;
                                    if($Type=='SET') {
										$txt='<a href="?Activate='.$key.'">'.strip_tags($txt).'</a>';
									}
									$tmp='<tr name="'.$key.'"'.($ActiveSession ? ' class="active"' : '').'><td>';
									$tmp.=$timing . ($Item->Shift && $timing ? ($Type=='IS' ? '<span class="SchDelay">' : '') . '&nbsp;+' . $Item->Shift . ($Type=='IS' ? '</span>' : ''): "");
									$ret[]=$tmp.'</td><td class="SchItem">'.$txt.'</td></tr>';
									$timing='';
									$IsTitle=false;
								}
								$OldStart=$Item->Start;
								$OldEnd=$Item->Duration;
								$OldComment='';
							} else {
								// all other kind of texts have a title and the items
								if($OldTitle!=$Item->Title) {
									// Title
									if(!$IsTitle) {
										$ret[]='<tr><td></td><td class="SchTitle">'.$Item->Title.(($Item->SubTitle or $Item->Text or !$Item->RowLocation) ? '' : ' ('.$Item->RowLocation.')').'</td></tr>';
									}
									$OldTitle=$Item->Title;
									$OldSubTitle='';
									$IsTitle=true;
								}
								if($OldSubTitle!=$Item->SubTitle and $Item->Type!='RA') {
									// SubTitle
									$ret[]='<tr><td></td><td class="SchSubTitle">'.$Item->SubTitle.(($Item->Text or !$Item->RowLocation) ? '' : ' ('.$Item->RowLocation.')').'</td></tr>';
									$OldSubTitle=$Item->SubTitle;
									$IsTitle=false;
								}

								$timing='';
								if($OldStart != $Item->Start or $OldEnd != $Item->Duration) {
									$timing=$Item->StartView.$extraTimeInfo.($Item->Duration ? '-'.addMinutes($Item->StartView, $Item->Duration) : '');
									$OldStart=$Item->Start;
									$OldEnd=$Item->Duration;
								}

								$lnk=$Item->Text;
								if(!$Item->Warmup) {
									// not warmup!
									$OldComment='';
									switch($Item->Type) {
										case 'Q':
										case 'E':
											$lnk=[];
											if($Item->Comments) {
												$txt=$Item->Comments;
												if($Type=='SET') {
													$txt='<a href="?Activate='.urlencode($key).'">'.strip_tags($txt).'</a>';
												}
												$ret[]='<tr name="'.$key.'"'.($ActiveSession ? ' class="active"' : '').'><td>'
													. $timing . ($Item->Shift && $timing ? ($Type=='IS' ? '<span class="SchDelay">' : '') . '&nbsp;+' . $Item->Shift . ($Type=='IS' ? '</span>' : ''): "")
													.'</td><td class="SchWarmup">'.$txt.'</td></tr>';
												$timing='';
											}
											if($Item->Text) {

												if($Type=='IS') {
													if(!empty($this->RunningEvents[$Item->Session][0])) {
														$lnk[]='<a href="'.$this->ROOT_DIR.'Qualification/?type=0&'.implode('&',$this->RunningEvents[$Item->Session][0]).$TourCode.'">'.get_text('ViewIndividualResults', 'InfoSystem').'</a>';
													}
													if(!empty($this->RunningEvents[$Item->Session][1])) {
														$lnk[]='<a href="'.$this->ROOT_DIR.'Qualification/?type=1&'.implode('&',$this->RunningEvents[$Item->Session][1]).$TourCode.'">'.get_text('ViewTeamResults', 'InfoSystem').'</a>';
													}
												}
												if(count($this->Groups[$Item->Type][$Session])==1) {
                                                    $txt=implode('<br/>', $lnk);
                                                    if($Item->Text!=$OldText) {
                                                        $txt=$Item->Text.'<br/>'.$txt;
                                                    }
//												} elseif($Item==@end(end(end(end($this->Groups[$Item->Type][$Session]))))) {
												} else {
                                                    $txt=$Item->DistanceName;
                                                    foreach($this->Groups[$Item->Type][$Session] as $k1=>$v1) {
                                                        if(!empty($v1[$Date][$Time])) {
                                                            foreach($v1[$Date][$Time] as $tmp) {
                                                                if($tmp==$Item) {
                                                                    $txt.='<br/>'.implode('<br/>', $lnk);
                                                                    break 2;
                                                                }
                                                            }
                                                        }
                                                    }
												}

                                                if($Item->RowLocation) {
                                                    $txt.=' ('.$Item->RowLocation.')';
                                                }
                                                $OldText=$txt;
                                                if($Type=='SET') {
													$txt='<a href="?Activate='.urlencode($key).'">'.strip_tags($txt).'</a>';
												}
												$ret[]='<tr name="'.$key.'"'.($ActiveSession ? ' class="active"' : '').'><td>'
													. $timing . ($Item->Shift && $timing ? ($Type=='IS' ? '<span class="SchDelay">' : '') . '&nbsp;+' . $Item->Shift . ($Type=='IS' ? '</span>' : ''): "")
													.'</td><td class="SchItem">'.$txt.'</td></tr>';
												$IsTitle=false;
											}
											break;
										case 'I':
										case 'T':
											$lnk=$Item->Text.': '.$Item->Events;
											$Join=(($Item->ElimType==3 or $Item->ElimType==4) ? 'LEFT' : 'INNER');
											$Class='';
											if($this->Finalists or $Type=='SET') { // && $Item->Session<=1) {
												// Bronze or Gold Finals
												if($Item->Type=='I') {
													$SQL="select distinct concat(upper(e1.EnFirstname), ' ', e1.EnName, ' (', c1.CoCode, ')') LeftSide,
													concat('(', c2.CoCode, ') ', upper(e2.EnFirstname), ' ', e2.EnName) RightSide,
													GrMatchNo, tf1.FinEvent as EvCode, GrPhase
													from Finals tf1
													inner join Finals tf2 on tf1.FinEvent=tf2.FinEvent and tf1.FinTournament=tf2.FinTournament and tf2.FinMatchNo=tf1.FinMatchNo+1 and tf2.FinMatchNo%2=1
													inner join FinSchedule fs1 on tf1.FinTournament=fs1.FsTournament and tf1.FinEvent=fs1.FsEvent and tf1.FinMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=0 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
													inner join FinSchedule fs2 on tf2.FinTournament=fs2.FsTournament and tf2.FinEvent=fs2.FsEvent and tf2.FinMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=0 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
													inner join Grids on tf1.FinMatchNo=GrMatchNo and GrPhase=$Item->Session
													$Join join Entries e1 on e1.EnId=tf1.FinAthlete and tf1.FinEvent IN ('$Item->Event')
													$Join join Entries e2 on e2.EnId=tf2.FinAthlete and tf2.FinEvent IN ('$Item->Event')
													$Join join Countries c1 on e1.EnCountry=c1.CoId and c1.CoTournament=$this->TourId
													$Join join Countries c2 on e2.EnCountry=c2.CoId and c2.CoTournament=$this->TourId
													where tf1.FinTournament=$this->TourId ";
												} else {
													$SQL="select concat(c1.CoName, ' (', c1.CoCode, ')') LeftSide,
													concat('(', c2.CoCode, ') ', c2.CoName) RightSide,
													GrMatchNo, tf1.TfEvent as EvCode, GrPhase
													from TeamFinals tf1
													inner join TeamFinals tf2 on tf1.TfEvent=tf2.TfEvent and tf1.TfTournament=tf2.TfTournament and tf2.TfMatchNo=tf1.TfMatchNo+1 and tf2.TfMatchNo%2=1
													inner join FinSchedule fs1 on tf1.TfTournament=fs1.FsTournament and tf1.TfEvent=fs1.FsEvent and tf1.TfMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=1 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
													inner join FinSchedule fs2 on tf2.TfTournament=fs2.FsTournament and tf2.TfEvent=fs2.FsEvent and tf2.TfMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=1 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
													inner join Countries c1 on c1.CoId=tf1.TfTeam and tf1.TfEvent IN ('$Item->Event')
													inner join Countries c2 on c2.CoId=tf2.TfTeam and tf2.TfEvent IN ('$Item->Event')
													inner join Grids on tf1.TfMatchNo=GrMatchNo and GrPhase=$Item->Session
													where tf1.TfTournament=$this->TourId";
												}
												$q=safe_r_SQL($SQL);
												if(safe_num_rows($q)==1 or $Item->ElimType==3 or $Item->ElimType==4) {
													$tmp=array();
													if($Item->ElimType==3 or $Item->ElimType==4) {
														$lnk='';
													}
													while($r=safe_fetch($q)) {
														if($Item->ElimType==3) {
															// ElimPool... writes who or a generic sentence
															$opps=array();
															if($r->LeftSide) {
																$opps[]='<span class="confirmed">'.$r->LeftSide.'</span>';
															} elseif(isset($this->PoolMatchWinners[$r->GrMatchNo])) {
																$opps[]='<span class="tempItem">'.$this->PoolMatchWinners[$r->GrMatchNo].'</span>';
															}
															if($r->RightSide) {
																$opps[]='<span class="confirmed">'.$r->RightSide.'</span>';
															} elseif(isset($this->PoolMatchWinners[$r->GrMatchNo+1])) {
																$opps[]='<span class="tempItem">'.$this->PoolMatchWinners[$r->GrMatchNo+1].'</span>';
															}
															$lnk.='<div><b>'.(isset($this->PoolMatches[$r->GrMatchNo]) ? $this->PoolMatches[$r->GrMatchNo] : $Item->Text)
																.': '.$Item->Events.'</b>'
																. ($opps ? '<br>' . implode(' - ', $opps) : '')
																.'</div>';

														} elseif($Item->ElimType==4) {
															// ElimPool... writes who or a generic sentence
															$opps=array();
															if($r->LeftSide) {
																$opps[]='<span class="confirmed">'.$r->LeftSide.'</span>';
															} elseif(isset($this->PoolMatchWinnersWA[$r->GrMatchNo])) {
																$opps[]='<span class="tempItem">'.$this->PoolMatchWinnersWA[$r->GrMatchNo].'</span>';
															}
															if($r->RightSide) {
																$opps[]='<span class="confirmed">'.$r->RightSide.'</span>';
															} elseif(isset($this->PoolMatchWinnersWA[$r->GrMatchNo+1])) {
																$opps[]='<span class="tempItem">'.$this->PoolMatchWinnersWA[$r->GrMatchNo+1].'</span>';
															}
															$tmp[(isset($this->PoolMatchesWA[$r->GrMatchNo]) ? $this->PoolMatchesWA[$r->GrMatchNo] : $Item->Text).($opps ? '' : ': '.$Item->Events) ][]=($opps ? '<b>'.$r->EvCode.':</b> '.implode(' - ', $opps) : '');

															//$lnk.='<div><b>'.(isset($this->PoolMatchesWA[$r->GrMatchNo]) ? $this->PoolMatchesWA[$r->GrMatchNo] : $Item->Text)
															//	.': '.$Item->Events.'</b>'
															//	. ($opps ? '<br>' . implode(' - ', $opps) : '')
															//	.'</div>';

														} elseif(trim($r->LeftSide) and trim($r->RightSide)) {
															$lnk= '<div><b>'.$lnk.'</b><br>' . $r->LeftSide.' - '.$r->RightSide.'</div>';
														}
													}
													if($tmp) {
														$lnk='';
														foreach($tmp as $categories => $opponents) {
															$lnk.='<div><b>'.$categories.'</b>'
																. ($opponents ? '<br>' . implode('<br>', $opponents) : '')
																.'</div>';
														}
													}
													$Class='MatchRow';
												}
											}
											if($Type=='SET') {
												$lnk='<a href="?Activate='.urlencode($key).'">'.strip_tags(str_replace('<br>', ' / ', $lnk), '<div>').'</a>';
											} elseif($Type=='IS') {
												$lnk='<a href="'.$this->ROOT_DIR.'Finals/session.php?Session='.urlencode(($Item->Type=='T' ? 1 : 0)."$Item->Day $Item->Start:00").$TourCode.'">'.$lnk.'</a>';
											}
											$ret[]='<tr name="'.$key.'" class="'.$Class.($ActiveSession ? ' active' : '').'"><td>'
												. $timing . ($Item->Shift && $timing ? ($Type=='IS' ? '<span class="SchDelay">' : '') . '&nbsp;+' . $Item->Shift . ($Type=='IS' ? '</span>' : ''): "")
												.'</td><td class="SchItem">'.$lnk.'</td></tr>';
											$IsTitle=false;
											break;
										case 'R':
											$lnk=$Item->Text.': '.$Item->Events;
											$Class='';
                                            $SQL="select concat_ws(' ', ucase(e1.EnFirstName), e1.EnName) as M1Name, concat_ws(' ', ucase(e2.EnFirstName), e2.EnName) as M2Name
                                                from RoundRobinMatches m1
                                                inner join RoundRobinMatches m2 on m2.RrMatchTournament=m1.RrMatchTournament and m2.RrMatchTeam=m1.RrMatchTeam and m2.RrMatchEvent=m1.RrMatchEvent and m2.RrMatchLevel=m1.RrMatchLevel and m2.RrMatchGroup=m1.RrMatchGroup and m2.RrMatchRound=m1.RrMatchRound and m2.RrMatchMatchNo=m1.RrMatchMatchNo+1
                                                left join Entries e1 on e1.EnId=m1.RrMatchAthlete
                                                left join Entries e2 on e2.EnId=m2.RrMatchAthlete
                                                where m1.RrMatchMatchNo%2=0 and m1.RrMatchTournament=$this->TourId and m1.RrMatchScheduledDate='$Date' and m1.RrMatchScheduledTime='$Time'";
											$NameLink='';
                                            $q=safe_r_sql($SQL);
                                            if(safe_num_rows($q)==1) {
                                                $r=safe_fetch($q);
                                                if($r->M1Name or $r->M2Name) {
                                                    $NameLink= ($r->M1Name? "<b>{$r->M1Name}</b>" :'TBD').' - '.($r->M2Name? "<b>{$r->M2Name}</b>" :'TBD');
                                                }
                                            }
                                            if($Type=='SET') {
												$lnk='<a href="?Activate='.urlencode($key).'">'.strip_tags(str_replace('<br>', ' / ', $lnk), '<div>').' / '.$NameLink.'</a>';
											} elseif($Type=='IS') {
                                                if($NameLink) {
                                                    $NameLink=" ($NameLink)";
                                                }
												$lnk='<a href="'.$this->ROOT_DIR.'Finals/session.php?Session='.urlencode(($Item->Type=='T' ? 1 : 0)."$Item->Day $Item->Start:00").$TourCode.'">'.$lnk.$NameLink.'</a>';
											}
											$ret[]='<tr name="'.$key.'" class="'.$Class.($ActiveSession ? ' active' : '').'"><td>'
												. $timing . ($Item->Shift && $timing ? ($Type=='IS' ? '<span class="SchDelay">' : '') . '&nbsp;+' . $Item->Shift . ($Type=='IS' ? '</span>' : ''): "")
												.'</td><td class="SchItem">'.$lnk.'</td></tr>';
											$IsTitle=false;
											break;
										case 'RA':
											$lnk=$Item->Text.($Item->Events ? ': '.$Item->Events : '');
											if($Item->SubTitle) {
												$lnk.=' - '.$Item->SubTitle;
											}
											$Class='';
											if($Type=='SET') {
												$lnk='<a href="?Activate='.urlencode($key).'">'.strip_tags(str_replace('<br>', ' / ', $lnk), '<div>').'</a>';
											}
											$ret[]='<tr name="'.$key.'" class="'.$Class.($ActiveSession ? ' active' : '').'"><td>'
												. $timing . ($Item->Shift && $timing ? ($Type=='IS' ? '<span class="SchDelay">' : '') . '&nbsp;+' . $Item->Shift . ($Type=='IS' ? '</span>' : ''): "")
												.'</td><td class="SchItem">'.$lnk.'</td></tr>';
											$IsTitle=false;
											break;
										default:
// 											debug_svela($Item);
									}

								} else {
									if($Item->Comments and $Item->Type!='RA') {
										$lnk=$Item->Comments;
									} else {
										switch($Item->Type) {
											case 'I':
											case 'T':
												$lnk=$Item->Text.': '.$Item->Events.' '.'warmup';
												break;
											case 'RA':
												if($Time==$Item->grPos) {
													$lnk=get_text('CallTimeToRoom', 'RunArchery', $Item->Text.' '.$Item->Events);
												} else {
													$lnk=get_text('OfficialPracticeForEvents', 'RunArchery', $Item->Text.' '.$Item->Events);
												}
												break;
											default:
												$lnk = get_text("WarmUp", "Tournament") . ' ' . $lnk;
										}
									}

									if($OldComment==$lnk) continue;

									$OldComment=$lnk;
                                    $OldText=$lnk;

                                    if($Type=='SET') {
										$lnk='<a href="?Activate='.urlencode($key).'">'.strip_tags($lnk).'</a>';
									}
									$ret[]='<tr name="'.$key.'"'.($ActiveSession ? ' class="active"' : '').'><td>'
										. $timing . ($Item->Shift && $timing ? ($Type=='IS' ? '<span class="SchDelay">' : '') . '&nbsp;+' . $Item->Shift . ($Type=='IS' ? '</span>' : ''): "")
										.'</td><td class="SchItem SchWarmup">'.$lnk.'</td></tr>';
									$IsTitle=false;
								}
							}
						}
					}
				}
			}
		}
		if($ret) {
			return '<table width="100%" class="SchTable">'.implode('', $ret).'</table>';
		}
		return '';
	}

	function getScheduleByDay() {
	    $ret=array();
        foreach($this->GetSchedule() as $Date => $Times) {
            $tmpToday=array();
            $OldTitle='';
            $OldSubTitle='';
            $OldComment='';
            $IsTitle=false;
            ksort($Times);
            foreach($Times as $Time => $Sessions) {
                $Singles=array();
                foreach($Sessions as $Session => $Distances) {
                    foreach ($Distances as $Distance => $Items) {
                        foreach ($Items as $k => $Item) {
                            $key = $Item->Day
                                . '|' . $Time
                                . '|' . $Session
                                . '|' . $Distance
                                . '|' . round($Item->Order, 4);
                            if ($Item->Comments) {
                                $SingleKey = "{$Item->Duration}-{$Item->Title}-{$Item->SubTitle}-{$Item->Comments}";
                                if (in_array($SingleKey, $Singles)) continue;
                                $Singles[] = $SingleKey;
                            }
                            $ActiveSession=in_array($key, $this->ActiveSessions);
                            if($Item->Type=='Z') {
                                if($OldTitle!=$Item->Title and $Item->Title) {
                                    if(!$IsTitle) {
                                        $tmpToday[] = array(
                                            'Start'=>$Item->Start,
                                            'End'=>addMinutes($Item->Start, $Item->Duration),
                                            'Duration'=>$Item->Duration,
                                            'Delay'=>$Item->Shift,
                                            'Level'=>0,
                                            'Type'=>'',
                                            'Active'=>$ActiveSession,
                                            'Text'=>strip_tags($Item->Title));
                                    }
                                    $OldTitle=$Item->Title;
                                    $OldSubTitle='';
                                    $IsTitle=true;
                                }
                                if($OldSubTitle!=$Item->SubTitle and $Item->SubTitle) {
                                    $tmpToday[] = array(
                                        'Start'=>$Item->Start,
                                        'End'=>addMinutes($Item->Start, $Item->Duration),
                                        'Duration'=>$Item->Duration,
                                        'Delay'=>$Item->Shift,
                                        'Level'=>1,
                                        'Type'=>'',
                                        'Active'=>$ActiveSession,
                                        'Text'=>strip_tags($Item->SubTitle));
                                    $OldSubTitle=$Item->SubTitle;
                                    $IsTitle=false;
                                }
                                if($Item->Text) {
                                    $tmpToday[] = array(
                                        'Start'=>$Item->Start,
                                        'End'=>addMinutes($Item->Start, $Item->Duration),
                                        'Duration'=>$Item->Duration,
                                        'Delay'=>$Item->Shift,
                                        'Level'=>2,
                                        'Type'=>'',
                                        'Active'=>$ActiveSession,
                                        'Text'=>strip_tags($Item->Text));
                                    $txt=$Item->Text;
                                    $IsTitle=false;
                                }
                            } else {
                                // all other kind of texts have a title and the items
                                if($OldTitle!=$Item->Title) {
                                    if(!$IsTitle) {
                                        $tmpToday[] = array(
                                            'Start' => $Item->Start,
                                            'End' => addMinutes($Item->Start, $Item->Duration),
                                            'Duration' => $Item->Duration,
                                            'Delay' => $Item->Shift,
                                            'Level' => 0,
                                            'Type'=>"",
                                            'Active' => $ActiveSession,
                                            'Text' => strip_tags($Item->Title));
                                    }
                                    $OldTitle=$Item->Title;
                                    $OldSubTitle='';
                                    $IsTitle=true;
                                }
                                if($OldSubTitle!=$Item->SubTitle) {
                                    $tmpToday[] = array(
                                        'Start'=>$Item->Start,
                                        'End'=>addMinutes($Item->Start, $Item->Duration),
                                        'Duration'=>$Item->Duration,
                                        'Delay'=>$Item->Shift,
                                        'Level'=>1,
                                        'Type'=>"",
                                        'Active'=>$ActiveSession,
                                        'Text'=>strip_tags($Item->SubTitle));
                                    $OldSubTitle=$Item->SubTitle;
                                    $IsTitle=false;
                                }

                                $lnk=$Item->Text;
                                if(!$Item->Warmup) {
                                    // not warmup!
                                    $OldComment='';
                                    switch($Item->Type) {
                                        case 'Q':
                                        case 'E':
                                            $lnk='';
                                            if($Item->Comments) {
                                                $txt=$Item->Comments;
                                                $tmpToday[] = array(
                                                    'Start'=>$Item->Start,
                                                    'End'=>addMinutes($Item->Start, $Item->Duration),
                                                    'Duration'=>$Item->Duration,
                                                    'Delay'=>$Item->Shift,
                                                    'Level'=>3,
                                                    'Type'=>$Item->Type,
                                                    'Active'=>$ActiveSession,
                                                    'Text'=>$txt);
                                            }
                                            if(count($this->Groups[$Item->Type][$Session])==1) {
                                                $txt=$Item->Text.$lnk;
                                            } elseif($Item==@end(end(end(end($this->Groups[$Item->Type][$Session]))))) {
                                                $txt=$Item->DistanceName.$lnk;
                                            } else {
                                                $txt=$Item->DistanceName;
                                            }
                                            $tmpToday[] = array(
                                                'Start'=>$Item->Start,
                                                'End'=>addMinutes($Item->Start, $Item->Duration),
                                                'Duration'=>$Item->Duration,
                                                'Delay'=>$Item->Shift,
                                                'Level'=>2,
                                                'Type'=>'Q',
                                                'Active'=>$ActiveSession,
                                                'Text'=>$txt);
                                            $IsTitle=false;
                                            break;
                                        case 'I':
                                        case 'T':
                                            $lnk=$Item->Text.': '.$Item->Events;
                                            $Join=($Item->ElimType>=3 ? 'LEFT' : 'INNER');
                                            if($this->Finalists) { // && $Item->Session<=1) {
                                                // Bronze or Gold Finals
                                                if($Item->Type=='I') {
                                                    $SQL="select distinct concat(upper(e1.EnFirstname), ' ', e1.EnName, ' (', c1.CoCode, ')') LeftSide,
													concat('(', c2.CoCode, ') ', upper(e2.EnFirstname), ' ', e2.EnName) RightSide,
													GrMatchNo
													from Finals tf1
													inner join Finals tf2 on tf1.FinEvent=tf2.FinEvent and tf1.FinTournament=tf2.FinTournament and tf2.FinMatchNo=tf1.FinMatchNo+1 and tf2.FinMatchNo%2=1
													inner join FinSchedule fs1 on tf1.FinTournament=fs1.FsTournament and tf1.FinEvent=fs1.FsEvent and tf1.FinMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=0 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
													inner join FinSchedule fs2 on tf2.FinTournament=fs2.FsTournament and tf2.FinEvent=fs2.FsEvent and tf2.FinMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=0 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
													inner join Grids on tf1.FinMatchNo=GrMatchNo and GrPhase=$Item->Session
													$Join join Entries e1 on e1.EnId=tf1.FinAthlete and tf1.FinEvent IN ('$Item->Event')
													$Join join Entries e2 on e2.EnId=tf2.FinAthlete and tf2.FinEvent IN ('$Item->Event')
													$Join join Countries c1 on e1.EnCountry=c1.CoId and c1.CoTournament=$this->TourId
													$Join join Countries c2 on e2.EnCountry=c2.CoId and c2.CoTournament=$this->TourId
													where tf1.FinTournament=$this->TourId ";
                                                } else {
                                                    $SQL="select distinct concat(c1.CoName, ' (', c1.CoCode, ')') LeftSide,
													concat('(', c2.CoCode, ') ', c2.CoName) RightSide,
													GrMatchNo
													from TeamFinals tf1
													inner join TeamFinals tf2 on tf1.TfEvent=tf2.TfEvent and tf1.TfTournament=tf2.TfTournament and tf2.TfMatchNo=tf1.TfMatchNo+1 and tf2.TfMatchNo%2=1
													inner join FinSchedule fs1 on tf1.TfTournament=fs1.FsTournament and tf1.TfEvent=fs1.FsEvent and tf1.TfMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=1 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
													inner join FinSchedule fs2 on tf2.TfTournament=fs2.FsTournament and tf2.TfEvent=fs2.FsEvent and tf2.TfMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=1 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
													inner join Countries c1 on c1.CoId=tf1.TfTeam and tf1.TfEvent IN ('$Item->Event')
													inner join Countries c2 on c2.CoId=tf2.TfTeam and tf2.TfEvent IN ('$Item->Event')
													inner join Grids on tf1.TfMatchNo=GrMatchNo and GrPhase=$Item->Session
													where tf1.TfTournament=$this->TourId";
                                                }
                                                $q=safe_r_SQL($SQL);
                                                if(safe_num_rows($q)==1 or $Item->ElimType>=3) {
	                                                $tmp=array();
	                                                if($Item->ElimType>=3) {
		                                                $lnk='';
	                                                }
	                                                while($r=safe_fetch($q)) {
	                                                    if($Item->ElimType==3) {
	                                                        // ElimPool... writes who or a generic sentence
	                                                        $opps=array();
	                                                        if($r->LeftSide) {
	                                                            $opps[]=$r->LeftSide;
	                                                        } elseif(isset($this->PoolMatchWinners[$r->GrMatchNo])) {
	                                                            $opps[]=$this->PoolMatchWinners[$r->GrMatchNo];
	                                                        }
	                                                        if($r->RightSide) {
	                                                            $opps[]=$r->RightSide;
	                                                        } elseif(isset($this->PoolMatchWinners[$r->GrMatchNo+1])) {
	                                                            $opps[]=$this->PoolMatchWinners[$r->GrMatchNo+1];
	                                                        }
	                                                        $lnk.=(isset($this->PoolMatches[$r->GrMatchNo]) ? $this->PoolMatches[$r->GrMatchNo] : $Item->Text)
	                                                            . ' ' . $Item->Events
	                                                            . ($opps ? ': '. implode(' - ', $opps) : '');

	                                                    } elseif($Item->ElimType==4) {
	                                                        // ElimPool... writes who or a generic sentence
	                                                        $opps=array();
	                                                        if($r->LeftSide) {
	                                                            $opps[]=$r->LeftSide;
	                                                        } elseif(isset($this->PoolMatchWinnersWA[$r->GrMatchNo])) {
	                                                            $opps[]=$this->PoolMatchWinnersWA[$r->GrMatchNo];
	                                                        }
	                                                        if($r->RightSide) {
	                                                            $opps[]=$r->RightSide;
	                                                        } elseif(isset($this->PoolMatchWinnersWA[$r->GrMatchNo+1])) {
	                                                            $opps[]=$this->PoolMatchWinnersWA[$r->GrMatchNo+1];
	                                                        }

		                                                    //$tmp[(isset($this->PoolMatchesWA[$r->GrMatchNo]) ? $this->PoolMatchesWA[$r->GrMatchNo] : $Item->Text) .': '.$Item->Events][]=($opps ? implode(' - ', $opps) : '');

		                                                    $lnk.=(isset($this->PoolMatchesWA[$r->GrMatchNo]) ? $this->PoolMatchesWA[$r->GrMatchNo] : $Item->Text)
	                                                            .': '.$Item->Events
	                                                            . ($opps ? ' (' . implode(' - ', $opps) . ')' : '');

	                                                    } elseif(trim($r->LeftSide) and trim($r->RightSide)) {
	                                                        $lnk= $lnk. ': ' . $r->LeftSide.' - '.$r->RightSide;
	                                                    }
	                                                }
                                                }
                                            }
                                            $tmpToday[] = array(
                                                'Start'=>$Item->Start,
                                                'End'=>addMinutes($Item->Start, $Item->Duration),
                                                'Duration'=>$Item->Duration,
                                                'Delay'=>$Item->Shift,
                                                'Level'=>2,
                                                'Type'=>$Item->Type,
                                                'Active'=>$ActiveSession,
                                                'Text'=>$lnk);
                                            $IsTitle=false;
                                            break;
                                        default:
                                    }
                                } else {
                                    if($Item->Comments) {
                                        $lnk=$Item->Comments;
                                    } else {
                                        switch($Item->Type) {
                                            case 'I':
                                            case 'T':
                                                $lnk=$Item->Text.': '.$Item->Events.' '.'warmup';
                                                break;
                                            default:
                                                $lnk = get_text("WarmUp", "Tournament") . ' ' . $lnk;
                                        }
                                    }
                                    if($OldComment==$lnk) continue;
                                    $OldComment=$lnk;

                                    $tmpToday[] = array(
                                        'Start'=>$Item->Start,
                                        'End'=>addMinutes($Item->Start, $Item->Duration),
                                        'Duration'=>$Item->Duration,
                                        'Delay'=>$Item->Shift,
                                        'Level'=>3,
                                        'Type'=>"",
                                        'Active'=>$ActiveSession,
                                        'Text'=>$lnk);
                                    $IsTitle=false;
                                }
                            }
                        }
                    }
                }
            }
            $ret[]=array('Day'=>$Date, 'Items'=>$tmpToday);
        }
        return $ret;
    }

	/**
	 * @param string $pdf
	 * If empty creates and returns a pdf, otherwise adds page to an existant pdf
	 * @return tcpdf object
	 *
	 *
	 */
	function getSchedulePDF(&$pdf='') {
		if(empty($pdf)) {
            require_once('Common/pdf/ResultPDF.inc.php');
			$pdf=new ResultPDF(get_text('IntSCHED', 'ODF'));
		}

        $pdf->SetFont($pdf->FontStd,'B',11);
        $pdf->Cell($pdf->getPageWidth() - 2 * IanseoPdf::sideMargin, 8, get_text('IntSCHED', 'ODF'),0,1,'C');


		if($this->SchedVersion) {
		//	$pdf->dy(-4.5*$FontAdjust);
		//	$pdf->Cell(0, 0, $this->SchedVersionText, '', 1, 'R' );
			$pdf->Version=$this->SchedVersion;
			$pdf->setComment($this->SchedVersionText);
		}
		//$pdf->dy(3*$FontAdjust);


		$Start=true;
		$StartX=$pdf->GetX();
		$FontAdjust= 1;
		$DelayWidth=10;
		$TimingWidth=20;
		$DurationWidth=10;
		$CellHeight=5;
		$RepeatTitle='';
		$TimeColumns=$TimingWidth+$DurationWidth+$DelayWidth;
		$descrSize=$pdf->getPageWidth() - 20-$TimeColumns;
		$RepeatTile='';

		$pdf->SetTopMargin(ResultPDF::topMargin);

		//$pdf->ln();
		//$pdf->SetFont($pdf->FontStd, 'B', 20*$FontAdjust);
		//$pdf->Cell(0, 0, $pdf->IsOris ? 'Schedule' : get_text('Schedule', 'Tournament'), '', 1, 'C' );
		$pdf->SetFont($pdf->FontStd, '', 8*$FontAdjust);


		foreach($this->GetSchedule() as $Date => $Times) {
			if(!$Start) {
				if($this->DayByDay or in_array($Date, $this->PageBreaks) or !$pdf->SamePage(5, $CellHeight, '', false)) {
					$pdf->AddPage();
				} else {
					$pdf->dy(2*$FontAdjust);
				}
			}
			$Start=false;


			// DAY
			$pdf->SetFont($pdf->FontStd,'B',8*$FontAdjust);
			$pdf->Cell(0, $CellHeight, formatTextDate($Date, true) ,1,1,'L',1);
            $pdf->SetY($pdf->GetY()+0.1);
			$pdf->SetFont($pdf->FontStd,'');

			$OldTitle='';
			$OldSubTitle='';
			$OldType='';
			$OldStart='';
			$OldEnd='';
			$IsTitle=false;
			$FirstTitle=true;

			$OldComment='';
			ksort($Times);
			foreach($Times as $Time => $Sessions) {
				$Singles=array();
				foreach($Sessions as $Session => $Distances) {
					foreach($Distances as $Distance => $Items) {
						foreach($Items as $k => $Item) {
							if($Item->Comments) {
								$SingleKey="{$Item->Duration}-{$Item->Title}-{$Item->SubTitle}-{$Item->Comments}";
								if(in_array($SingleKey, $Singles)) continue;
								$Singles[]=$SingleKey;
							}

							if(!$pdf->SamePage(1, $CellHeight, '', false)) {
								$pdf->AddPage();
								// Day...
								$pdf->SetFont('', 'B');
								$pdf->Cell(0, $CellHeight, formatTextDate($Date, true) . '    ('.get_text('Continue').')',1,1,'L',1);
								$FirstTitle=true;

								// maybe the session title?
								if($Item->Type!='Z' and $OldTitle==$Item->Title and $RepeatTitle) {
									$pdf->SetX($StartX+$TimeColumns);
									$pdf->Cell($descrSize, $CellHeight, $RepeatTitle . ", " . formatWeekDayLong($Date) . '    ('.get_text('Continue').')',1,1,'L',0);
								}
								$pdf->SetFont('', '');
							}


							$timingDelayed='';
							$timing='';

							if($Item->Type=='Z') {
								// free text
								$timing=$Item->Start.($Item->Duration ? '-'.addMinutes($Item->Start, $Item->Duration) : '');
								if($Item->Shift) {
									$timingDelayed = '+'.$Item->Shift;
								}
								if($OldTitle!=$Item->Title and $Item->Title) {
									if(!$IsTitle) {
										if(!$FirstTitle) $pdf->ln(2);
										$pdf->SetX($StartX+$TimeColumns);
										$pdf->SetFont('', 'BI');
                                        $titleAlign = "L";
                                        $titleCellHeight = $CellHeight;
                                        $fill = 0;
                                        $cellWidth = $descrSize;
                                        $text = strip_tags($Item->Title).(($Item->SubTitle or $Item->Text or !$Item->RowLocation) ? '' : ' ('.$Item->RowLocation.')');
                                        if ($Item->UID == "") {
                                            $pdf->SetFont('', 'B');
                                            $pdf->SetFontSize(10);
                                            $pdf->SetX($StartX + 5);
                                            $cellWidth = $descrSize + $TimeColumns - 10;
                                            $titleAlign = 'C';
                                            $titleCellHeight = 8;
                                            $fill = 1;
                                            $text = strip_tags($Item->Title);
                                        }
										$pdf->Cell($cellWidth, $titleCellHeight, $text, 0, 1, $titleAlign, $fill);
										$pdf->SetFont('', '');
                                        $pdf->SetFontSize(8);
										$RepeatTitle=$Item->Title;
									}
									$OldTitle=$Item->Title;
									$OldSubTitle='';
									$IsTitle=true;
								}
								if($OldSubTitle!=$Item->SubTitle and $Item->SubTitle) {
									if(!$Item->Text) {
										$pdf->SetX($StartX);
										if($Item->Shift and $timing) {
											$pdf->SetX($StartX-$DelayWidth);
											$pdf->Cell($DelayWidth, $CellHeight, $timingDelayed, 0, 0);
											$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
										} else {
											$pdf->SetX($StartX+$DelayWidth);
											$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
										}
										if($timing and $Item->Duration) {
											$pdf->SetFont('', 'I');
											$pdf->setColor('text', 75);
											$pdf->Cell($DurationWidth, $CellHeight, sprintf('%02d:%02d', $Item->Duration/60, $Item->Duration%60), 0, 0, 'R', 0);
											$pdf->SetFont('', '');
											$pdf->setColor('text', 0);
										}
										$timing='';
									}
									$pdf->SetX($StartX+$TimeColumns);
									$pdf->SetFont('', 'BI');
									$pdf->Cell($descrSize, $CellHeight, strip_tags($Item->SubTitle).(($Item->Text or !$Item->RowLocation) ? '' : ' ('.$Item->RowLocation.')'), 0, 1, 'L', 0);
									$pdf->SetFont('', '');
									$OldSubTitle=$Item->SubTitle;
									$IsTitle=false;
								}
								if($Item->Text) {
									$pdf->SetX($StartX);
									if($Item->Shift and $timing) {
										$pdf->Cell($DelayWidth, $CellHeight, $timingDelayed, 0, 0);
										$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
										// $pdf->Line($StartX, $y=$pdf->GetY()+($CellHeight/2), $StartX+$TimingWidth-$FontAdjust, $y);
									} else {
										$pdf->SetX($StartX+$DelayWidth);
										$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
									}
									if($timing and $Item->Duration) {
										$pdf->SetFont('', 'I');
										$pdf->setColor('text', 75);
										$pdf->Cell($DurationWidth, $CellHeight, sprintf('%02d:%02d', $Item->Duration/60, $Item->Duration%60), 0, 0, 'R', 0);
										$pdf->SetFont('', '');
										$pdf->setColor('text', 0);
									}
									$pdf->SetX($StartX+$TimeColumns);
									$pdf->Cell($descrSize, $CellHeight, strip_tags($Item->Text).($Item->RowLocation ? ' ('.$Item->RowLocation.')' : ''), 0, 1, 'L', 0);
									$timing='';
									$IsTitle=false;
								}
								$OldStart=$Item->Start;
								$OldEnd=$Item->Duration;
								$OldComment='';
							} else {
								// all other kind of texts have a title and the items
								if($OldTitle!=$Item->Title) {
									// Title
									if(!$IsTitle) {
										if(!$FirstTitle) $pdf->ln(2);
										$pdf->SetFont('', 'B');

                                        $mainText = $Item->SubTitle;
                                        $pdf->SetFontSize(10);
                                        $pdf->SetX($StartX + 5);
                                        $titleDescrSize = $descrSize + $TimeColumns - 10;;
                                        $titleAlign = 'C';
                                        $titleCellHeight = 8;
                                        $fill = 1;
                                        if (!$FirstTitle) {
                                            $mainText = $Item->Title;
                                            $titleAlign = "L";
                                            $pdf->SetX($StartX+$TimeColumns);
                                            $titleDescrSize = $descrSize;
                                            $titleCellHeight = $CellHeight;
                                            $pdf->setFont('', "BI");
                                            $pdf->SetFontSize(8);
                                            $fill = 0;
                                        }
										$pdf->Cell($titleDescrSize, $titleCellHeight, $mainText.(($Item->SubTitle or $Item->Text or !$Item->RowLocation) ? '' : ' ('.$Item->RowLocation.')'), 0, 1, $titleAlign, $fill);
										$pdf->SetFont('', '');
                                        $pdf->SetFontSize(8);
										$RepeatTitle=$Item->Title;
									}
									$OldTitle=$Item->Title;
									$IsTitle=true;
									$OldSubTitle='';
								}
								//if($Item->Type=='Q' and $Item->Warmup) {
								//	// skip to nex item!
								//	continue;
								//}
								if($OldSubTitle!=$Item->SubTitle && $Item->Title != $mainText) {
									// SubTitle
									$pdf->SetX($StartX+$TimeColumns);
									$pdf->SetFont('', 'BI');
									$pdf->Cell($descrSize, $CellHeight, $Item->Title.(($Item->Text or !$Item->RowLocation) ? '' : ' ('.$Item->RowLocation.')'), 0, 1, 'L', 0);
									$pdf->SetFont('', '');
									$OldSubTitle=$Item->SubTitle;
									$IsTitle=false;
								}

								$timing='';
								if($OldStart != $Item->Start or $OldEnd != $Item->Duration) {
									$timing=$Item->Start.($Item->Duration ? '-'.addMinutes($Item->Start, $Item->Duration) : '');
									$OldStart=$Item->Start;
									$OldEnd=$Item->Duration;
									if($Item->Shift) {
										$timingDelayed = '+'.$Item->Shift;
									}
								}

								$lnk=strip_tags($Item->Text);
								if(!$Item->Warmup) {
									// not warmup!
									$OldComment='';
									switch($Item->Type) {
										case 'Q':
										case 'E':
											//$t=safe_r_SQL("select distinct EcCode, EvTeamEvent
											//	from Entries
											//	INNER JOIN Qualifications on QuId=EnId and QuSession=$Item->Session
											//	INNER JOIN EventClass ON EcClass=EnClass AND EcDivision=EnDivision AND EcTournament=EnTournament and if(EcSubClass='', true, EcSubClass=EnSubClass)
											//	INNER JOIN Events on EvCode=EcCode AND EvTeamEvent=IF(EcTeamEvent!=0, 1,0) AND EvTournament=EcTournament
											//	where EnTournament=$this->TourId
											//	order by EvTeamEvent, EvProgr");
											$lnk='';
											if($Item->Comments) {
												if($Item->Shift and $timing) {
													$pdf->SetX($StartX);
													$pdf->Cell($DelayWidth, $CellHeight, $timingDelayed, 0, 0);
													$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
												} else {
													$pdf->SetX($StartX+$DelayWidth);
													$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
												}
												if($timing and $Item->Duration) {
													$pdf->SetFont('', 'I');
													$pdf->setColor('text', 75);
													$pdf->Cell($DurationWidth, $CellHeight, sprintf('%02d:%02d', $Item->Duration/60, $Item->Duration%60), 0, 0, 'R', 0);
													$pdf->SetFont('', '');
													$pdf->setColor('text', 0);
												}
												$pdf->SetX($StartX+$TimeColumns);
												$pdf->SetFont('', 'I');
												$pdf->Cell($descrSize, $CellHeight, $Item->Comments, 0, 1, 'L', 0);
												$pdf->SetFont('', '');
												$timing='';
											}

											if(count($this->Groups[$Item->Type][$Session])==1) {
												$txt=$Item->Text.$lnk;
											} elseif($Item==@end(end(end(end($this->Groups[$Item->Type][$Session]))))) {
												$txt=$Item->DistanceName.$lnk;
											} else {
												$txt=$Item->DistanceName;
												// more distances defined so format is different...
											}

											if($Item->Shift and $timing) {
												$pdf->SetX($StartX);
												$pdf->Cell($DelayWidth, $CellHeight, $timingDelayed, 0, 0);
												$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
											} else {
												$pdf->SetX($StartX+$DelayWidth);
												$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
											}
											if($timing and $Item->Duration) {
												$pdf->SetFont('', 'I');
												$pdf->setColor('text', 75);
												$pdf->Cell($DurationWidth, $CellHeight, sprintf('%02d:%02d', $Item->Duration/60, $Item->Duration%60), 0, 0, 'R');
												$pdf->SetFont('', '');
												$pdf->setColor('text', 0);
											}
											$pdf->SetX($StartX+$TimeColumns);
											$pdf->Cell($descrSize, $CellHeight, $txt.($Item->RowLocation ? ' ('.$Item->RowLocation.')' : ''), 0, 1, 'L', 0);
											$IsTitle=false;
											break;
										case 'I':
										case 'T':
											$lnk=$Item->Text.': '.$Item->Events;
											if($Item->Shift and $timing) {
												$pdf->SetX($StartX);
												$pdf->Cell($DelayWidth, $CellHeight, $timingDelayed, 0, 0);
												$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
											} else {
												$pdf->SetX($StartX+$DelayWidth);
												$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
											}
											if($timing and $Item->Duration) {
												$pdf->SetFont('', 'I');
												$pdf->setColor('text', 75);
												$pdf->Cell($DurationWidth, $CellHeight, sprintf('%02d:%02d', $Item->Duration/60, $Item->Duration%60), 0, 0, 'R');
												$pdf->SetFont('', '');
												$pdf->setColor('text', 0);
											}
											$pdf->SetX($StartX+$TimeColumns);
											$IsTitle=false;
											if($this->Finalists or $Item->ElimType==3 or $Item->ElimType==4) { // && $Item->Session<=1) {
												$SQL='';
												// Bronze or Gold Finals
												if($Item->Type=='I') {
													if($Item->SO or $Item->ElimType>=3) {
														$Join=(($Item->ElimType==3 or $Item->ElimType==4) ? 'LEFT' : 'INNER');
														// SO are resolved so we can extract the people
														$SQL="select distinct ind1.IndRank LeftRank, ind2.IndRank RightRank, concat(upper(e1.EnFirstname), ' ', e1.EnName, ' (', c1.CoCode, ')') LeftSide,
																concat('(', c2.CoCode, ') ', upper(e2.EnFirstname), ' ', e2.EnName) RightSide,
																GrMatchNo, tf1.FinEvent as EvCode
															from Finals tf1
															inner join Finals tf2 on tf1.FinEvent=tf2.FinEvent and tf1.FinTournament=tf2.FinTournament and tf2.FinMatchNo=tf1.FinMatchNo+1 and tf2.FinMatchNo%2=1
															inner join FinSchedule fs1 on tf1.FinTournament=fs1.FsTournament and tf1.FinEvent=fs1.FsEvent and tf1.FinMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=0 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
															inner join FinSchedule fs2 on tf2.FinTournament=fs2.FsTournament and tf2.FinEvent=fs2.FsEvent and tf2.FinMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=0 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
															inner join Events on EvTournament=tf1.FinTournament and EvTeamEvent=0 and EvCode=tf1.FinEvent
															inner join Grids on tf1.FinMatchNo=GrMatchNo and GrPhase=$Item->Session
															$Join join Entries e1 on e1.EnId=tf1.FinAthlete and tf1.FinEvent IN ('$Item->Event')
															$Join join Entries e2 on e2.EnId=tf2.FinAthlete and tf2.FinEvent IN ('$Item->Event')
															$Join join Individuals ind1 on e1.EnId=ind1.IndId and tf1.FinEvent=ind1.IndEvent
															$Join join Individuals ind2 on e2.EnId=ind2.IndId and tf2.FinEvent=ind2.IndEvent
															$Join join Countries c1 on e1.EnCountry=c1.CoId and c1.CoTournament=$this->TourId
															$Join join Countries c2 on e2.EnCountry=c2.CoId and c2.CoTournament=$this->TourId
															where tf1.FinTournament=$this->TourId
															order by GrMatchNo";
													} elseif($this->Ranking) {
														// we can only catch the supposed positions of the opponents
														$Fld=(useGrPostion2($Item->Distance, $Item->Session) ? 'GrPosition2' : 'GrPosition');
														$SQL="select Gr1.{$Fld} LeftRank, Gr2.{$Fld} RightRank, '' LeftSide, '' RightSide, Gr1.GrMatchNo, tf1.FinEvent as EvCode
															from Finals tf1
															inner join Finals tf2 on tf1.FinEvent=tf2.FinEvent and tf1.FinTournament=tf2.FinTournament and tf2.FinMatchNo=tf1.FinMatchNo+1 and tf2.FinMatchNo%2=1
															inner join FinSchedule fs1 on tf1.FinTournament=fs1.FsTournament and tf1.FinEvent=fs1.FsEvent and tf1.FinMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=0 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
															inner join FinSchedule fs2 on tf2.FinTournament=fs2.FsTournament and tf2.FinEvent=fs2.FsEvent and tf2.FinMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=0 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
															inner join Events on EvTournament=tf1.FinTournament and EvTeamEvent=0 and EvCode=tf1.FinEvent
															inner join Grids Gr1 on tf1.FinMatchNo=Gr1.GrMatchNo and Gr1.GrPhase=$Item->Session
															inner join Grids Gr2 on tf2.FinMatchNo=Gr2.GrMatchNo and Gr2.GrPhase=$Item->Session
															where tf1.FinTournament=$this->TourId";
													}
												} else {
													if($Item->SO) {
														// SO are resolved so we can extract the people
														$SQL="select ind1.TeRank LeftRank, ind2.TeRank RightRank, concat(c1.CoName, ' (', c1.CoCode, ')') LeftSide,
																concat('(', c2.CoCode, ') ', c2.CoName) RightSide,
																GrMatchNo, tf1.TfEvent as EvCode
															from TeamFinals tf1
															inner join TeamFinals tf2 on tf1.TfEvent=tf2.TfEvent and tf1.TfTournament=tf2.TfTournament and tf2.TfMatchNo=tf1.TfMatchNo+1 and tf2.TfMatchNo%2=1
															inner join FinSchedule fs1 on tf1.TfTournament=fs1.FsTournament and tf1.TfEvent=fs1.FsEvent and tf1.TfMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=1 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
															inner join FinSchedule fs2 on tf2.TfTournament=fs2.FsTournament and tf2.TfEvent=fs2.FsEvent and tf2.TfMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=1 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
															inner join Countries c1 on c1.CoId=tf1.TfTeam and tf1.TfEvent IN ('$Item->Event')
															inner join Countries c2 on c2.CoId=tf2.TfTeam and tf2.TfEvent IN ('$Item->Event')
															inner join Teams ind1 on c1.CoId=ind1.TeCoId and tf1.TfEvent=ind1.TeEvent and tf1.TfSubTeam=ind1.TeSubTeam and ind1.TeFinEvent=1
															inner join Teams ind2 on c2.CoId=ind2.TeCoId and tf2.TfEvent=ind2.TeEvent and tf2.TfSubTeam=ind2.TeSubTeam and ind2.TeFinEvent=1
															inner join Grids on tf1.TfMatchNo=GrMatchNo and GrPhase=$Item->Session
															where tf1.TfTournament=$this->TourId";
													} elseif($this->Ranking) {
														// we can only catch the supposed positions of the opponents
														$Fld=(useGrPostion2($Item->Distance, $Item->Session) ? 'GrPosition2' : 'GrPosition');
														$SQL="select Gr1.{$Fld} LeftRank, Gr2.{$Fld} RightRank, '' LeftSide, '' RightSide, Gr1.GrMatchNo, tf1.TfEvent as EvCode
															from TeamFinals tf1
															inner join TeamFinals tf2 on tf1.TfEvent=tf2.TfEvent and tf1.TfTournament=tf2.TfTournament and tf2.TfMatchNo=tf1.TfMatchNo+1 and tf2.TfMatchNo%2=1
															inner join FinSchedule fs1 on tf1.TfTournament=fs1.FsTournament and tf1.TfEvent=fs1.FsEvent and tf1.TfMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=1 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
															inner join FinSchedule fs2 on tf2.TfTournament=fs2.FsTournament and tf2.TfEvent=fs2.FsEvent and tf2.TfMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=1 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
															inner join Grids Gr1 on tf1.TfMatchNo=Gr1.GrMatchNo and Gr1.GrPhase=$Item->Session
															inner join Grids Gr2 on tf2.TfMatchNo=Gr2.GrMatchNo and Gr2.GrPhase=$Item->Session
															where tf1.TfTournament=$this->TourId";
													}
												}
												if($SQL and $q=safe_r_SQL($SQL) and (safe_num_rows($q)==1 or $Item->ElimType==3 or $Item->ElimType==4)) {
													$tmp=array();
													while($r=safe_fetch($q)) {

														$pdf->SetX($StartX+$TimeColumns);
														if($Item->ElimType==3) {
															// ElimPool... writes who or a generic sentence
															$opps=array();
															if($r->LeftSide) {
																$opps[]=($this->Ranking ? '#'.$r->LeftRank.' ' : '') . $r->LeftSide;
															} elseif(isset($this->PoolMatchWinners[$r->GrMatchNo])) {
																$opps[]=($this->Ranking ? '#'.$r->LeftRank.' ' : '') . $this->PoolMatchWinners[$r->GrMatchNo];
															}
															if($r->RightSide) {
																$opps[]=$r->RightSide. ($this->Ranking ? ' #'.$r->RightRank : '');
															} elseif(isset($this->PoolMatchWinners[$r->GrMatchNo+1])) {
																$opps[]=$this->PoolMatchWinners[$r->GrMatchNo+1]. ($this->Ranking ? ' #'.$r->RightRank : '');
															}
															if($opps) {
																$pdf->Cell($descrSize, $CellHeight, (isset($this->PoolMatches[$r->GrMatchNo]) ? $this->PoolMatches[$r->GrMatchNo] : $Item->Text).': '.$Item->Events, 0, 1, 'L', 0);
																$pdf->SetXY($StartX+$TimeColumns, $pdf->getY()-1.5);
																$pdf->Cell($descrSize, $CellHeight, implode(' - ',$opps) , 0, 1, 'L', 0);
															} else {
																if($r->LeftRank or $r->RightRank) $lnk.= ' (#'.$r->LeftRank.' - #'.$r->RightRank.')';
																$pdf->Cell($descrSize, $CellHeight, $lnk, 0, 1, 'L', 0);
															}
														} elseif($Item->ElimType==4) {
															// ElimPool... writes who or a generic sentence
															$opps=array();
															if($r->LeftSide) {
																$opps[]=($this->Ranking ? '#'.$r->LeftRank.' ' : '') . $r->LeftSide;
															} elseif(isset($this->PoolMatchWinnersWA[$r->GrMatchNo])) {
																$opps[]=($this->Ranking ? '#'.$r->LeftRank.' ' : '') . $this->PoolMatchWinnersWA[$r->GrMatchNo];
															}
															if($r->RightSide) {
																$opps[]=$r->RightSide. ($this->Ranking ? ' #'.$r->RightRank : '');
															} elseif(isset($this->PoolMatchWinnersWA[$r->GrMatchNo+1])) {
																$opps[]=$this->PoolMatchWinnersWA[$r->GrMatchNo+1]. ($this->Ranking ? ' #'.$r->RightRank : '');
															}

															$tmp[(isset($this->PoolMatchesWA[$r->GrMatchNo]) ? $this->PoolMatchesWA[$r->GrMatchNo] : $Item->Text).($opps ? '' : ': '.$Item->Events)][]=($opps ? $r->EvCode.': '.implode(' - ', $opps) : '');

															//if($opps) {
															//	$pdf->Cell($descrSize, $CellHeight, (isset($this->PoolMatchesWA[$r->GrMatchNo]) ? $this->PoolMatchesWA[$r->GrMatchNo] : $Item->Text).': '.$Item->Events, 0, 1, 'L', 0);
															//	$pdf->SetXY($StartX+$TimeColumns, $pdf->getY()-1.5);
															//	$pdf->Cell($descrSize, $CellHeight, implode(' - ',$opps) , 0, 1, 'L', 0);
															//} else {
															//	if($r->LeftRank or $r->RightRank) $lnk.= ' (#'.$r->LeftRank.' - #'.$r->RightRank.')';
															//	$pdf->Cell($descrSize, $CellHeight, $lnk, 0, 1, 'L', 0);
															//}
														} else {
															if(trim($r->LeftSide) and trim($r->RightSide)) {
																$pdf->Cell($descrSize, $CellHeight, $lnk, 0, 1, 'L', 0);
																$pdf->SetXY($StartX+$TimeColumns, $pdf->getY()-1.5);
																$pdf->Cell($descrSize, $CellHeight, ($this->Ranking ? '#'.$r->LeftRank.' ' : '') . $r->LeftSide.' - '.$r->RightSide . ($this->Ranking ? ' #'.$r->RightRank : ''), 0, 1, 'L', 0);
															} else {
																$lnk.= ' (#'.$r->LeftRank.' - #'.$r->RightRank.')';
																$pdf->Cell($descrSize, $CellHeight, $lnk, 0, 1, 'L', 0);
															}
														}
													}

													if($tmp) {
														foreach($tmp as $Category => $Opponents) {
															if(!$pdf->SamePage(count($tmp), $CellHeight,'', false)) {
																$pdf->AddPage();
															}
															$pdf->SetX($StartX+$TimeColumns);
															$pdf->Cell($descrSize, $CellHeight, $Category, 0, 1, 'L', 0);
															foreach($Opponents as $Opponent) {
																if(!$Opponent) {
																	continue;
																}
																$pdf->SetXY($StartX+$TimeColumns, $pdf->getY()-1.5);
																$pdf->Cell($descrSize, $CellHeight, $Opponent , 0, 1, 'L', 0);
															}
														}
													}

												} else {
													$pdf->Cell($descrSize, $CellHeight, $lnk, 0, 1, 'L', 0);
												}
											} else {
												$pdf->Cell($descrSize, $CellHeight, $lnk, 0, 1, 'L', 0);
											}
											break;
										case 'R':
											$lnk=$Item->Text.': '.$Item->Events;
											if($Item->Shift and $timing) {
												$pdf->SetX($StartX);
												$pdf->Cell($DelayWidth, $CellHeight, $timingDelayed, 0, 0);
												$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
											} else {
												$pdf->SetX($StartX+$DelayWidth);
												$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
											}
											if($timing and $Item->Duration) {
												$pdf->SetFont('', 'I');
												$pdf->setColor('text', 75);
												$pdf->Cell($DurationWidth, $CellHeight, sprintf('%02d:%02d', $Item->Duration/60, $Item->Duration%60), 0, 0, 'R');
												$pdf->SetFont('', '');
												$pdf->setColor('text', 0);
											}
											$pdf->SetX($StartX+$TimeColumns);
											$IsTitle=false;
											$pdf->Cell($descrSize, $CellHeight, $lnk, 0, 1, 'L', 0);
											break;
										case 'RA':
											$lnk=$Item->Text.($Item->Events ? ': '.$Item->Events : '');
											if($Item->SubTitle) {
												$lnk.=' - '.$Item->SubTitle;
											}
											if($Item->Shift and $timing) {
												$pdf->SetX($StartX);
												$pdf->Cell($DelayWidth, $CellHeight, $timingDelayed, 0, 0);
												$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
											} else {
												$pdf->SetX($StartX+$DelayWidth);
												$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
											}
											if($timing and $Item->Duration) {
												$pdf->SetFont('', 'I');
												$pdf->setColor('text', 75);
												$pdf->Cell($DurationWidth, $CellHeight, sprintf('%02d:%02d', $Item->Duration/60, $Item->Duration%60), 0, 0, 'R');
												$pdf->SetFont('', '');
												$pdf->setColor('text', 0);
											}
											$pdf->SetX($StartX+$TimeColumns);
											$IsTitle=false;
											$pdf->Cell($descrSize, $CellHeight, $lnk, 0, 1, 'L', 0);
											break;
										default:
// 											debug_svela($Item);
									}

								} else {
									if($Item->Comments and $Item->Type!='RA') {
										$lnk=$Item->Comments;
									} else {
										switch($Item->Type) {
											case 'I':
											case 'T':
												$lnk=$Item->Text.': '.$Item->Events.' '.'warmup';
												break;
											case 'RA':
												if($Time==$Item->grPos) {
													$lnk=get_text('CallTimeToRoom', 'RunArchery', $Item->Text.' '.$Item->Events);
												} else {
													$lnk=get_text('OfficialPracticeForEvents', 'RunArchery', $Item->Text.' '.$Item->Events);
												}
												break;
											default:
                                                $lnk = get_text("WarmUp", "Tournament") . ' ' . $lnk;
										}
									}
									if($OldComment==$lnk) continue;
									$OldComment=$lnk;
									if($Item->Shift and $timing) {
										$pdf->SetX($StartX);
										$pdf->Cell($DelayWidth, $CellHeight, $timingDelayed, 0, 0);
										$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
									} else {
										$pdf->SetX($StartX+$DelayWidth);
										$pdf->Cell($TimingWidth, $CellHeight, $timing, 0, 0);
									}
									if($timing and $Item->Duration) {
										$pdf->SetFont('', 'I');
										$pdf->setColor('text', 75);
										$pdf->Cell($DurationWidth, $CellHeight, sprintf('%02d:%02d', $Item->Duration/60, $Item->Duration%60), 0, 0, 'R');
										$pdf->SetFont('', '');
										$pdf->setColor('text', 0);
									}
									$pdf->SetX($StartX+$TimeColumns);
									$pdf->SetFont('', 'I');
									$pdf->Cell($descrSize, $CellHeight, $lnk, 0, 1, 'L', 0);
									$pdf->SetFont('', '');
									$IsTitle=false;
								}
							}
							$FirstTitle=false;
						}
					}
				}
			}
		}
		return $pdf;
	}

	function getScheduleICS($Download=false) {
		// UID could be based on type
		// Z: ToCode+Type+SchUid (generated once)
		// Q: ToCode+Type+Session+Distance
		// E: ToCode+Type+Round+Events
		// RA: ToCode+Type+Phase+Group+Events
		// I: ToCode+Type+Phase+Events+sum(matchno)
		// M: ToCode+Type+Phase+Events+sum(matchno)
		// RR: ToCode+Type+Level+Group+Phase+Events+sum(matchno)
		require_once(__DIR__.'/ics-class.php');
		$q=safe_r_SQL("select ToTimeZone, ToCode, ToName, ToVenue, ToWhere, ToCountry from Tournament where ToId={$this->TourId}");
		if(!($COMP=safe_fetch($q))) {
			return '';
		}
		$Name=$COMP->ToName;
		$Location=$COMP->ToWhere." ({$COMP->ToCountry})";
		if($COMP->ToVenue) {
			$Location=$Location.' - '.$COMP->ToVenue;
		}
		$ICS=new IanseoCalendar($this->TourId, $Name, $Location, $COMP->ToCode.'-Schedule', $COMP->ToTimeZone, $this->SchedVersion??'');
		$ICS->Reset=isset($_REQUEST['reset']);

		foreach($this->GetSchedule() as $Date => $Times) {
			// DAY

			$OldTitle='';
			$OldSubTitle='';
			$OldType='';
			$OldStart='';
			$OldEnd='';
			$IsTitle=false;
			$FirstTitle=true;

			$OldComment='';
			ksort($Times);
			foreach($Times as $Time => $Sessions) {
				$Singles=array();
				foreach($Sessions as $Session => $Distances) {
					foreach($Distances as $Distance => $Items) {
						foreach($Items as $k => $Item) {
							if(!$Item->Duration) {
								continue;
							}
							if($Item->Comments) {
								$SingleKey="{$Item->Duration}-{$Item->Title}-{$Item->SubTitle}-{$Item->Comments}";
								if(in_array($SingleKey, $Singles)) {
									continue;
								}
								$Singles[]=$SingleKey;
							}
							$cal=[
								'start' => $Date.' '.$Time,
								'description' => [$Name],
								'comment' => [],
								'summary' => '',
								'location' => ($Item->Location??$Location),
								'uid' => md5($Item->UID),
							];

							if($Item->Duration) {
								$cal['duration']="PT{$Item->Duration}M";
							}
							if($Item->Shift) {
								$cal['comment'][] = '+'.$Item->Shift;
							}

							if($Item->Type=='Z') {
								// free text
								$timing=$Item->Start.($Item->Duration ? '-'.addMinutes($Item->Start, $Item->Duration) : '');
								if($OldTitle!=$Item->Title and $Item->Title) {
									if(!$IsTitle) {
										$cal['summary']=strip_tags($Item->Title);
										$RepeatTitle=$Item->Title;
									}
									$OldTitle=$Item->Title;
									$OldSubTitle='';
									$IsTitle=true;
								}
								if($OldSubTitle!=$Item->SubTitle and $Item->SubTitle) {
									if(!$Item->Text) {
										$timing='';
									}
									if($cal['summary']) {
										$cal['description'][]=strip_tags($Item->SubTitle);
									} else {
										$cal['summary']=strip_tags($Item->SubTitle);
									}
									$OldSubTitle=$Item->SubTitle;
									$IsTitle=false;
								}
								if($Item->Text) {
									if($cal['summary']) {
										$cal['description'][]=strip_tags($Item->Text);
									} else {
										$cal['summary']=strip_tags($Item->Text);
									}
									$timing='';
									$IsTitle=false;
								}
								$OldStart=$Item->Start;
								$OldEnd=$Item->Duration;
								$OldComment='';
							} else {
								// all other kind of texts have a title and the items
								// subtitle will always be the SUMMARY if present
								if($Item->SubTitle) {
									$cal['summary']=strip_tags($Item->SubTitle);
									$OldSubTitle=$Item->SubTitle;
									$IsTitle=false;
								}

								if(!$IsTitle) {
									if($cal['summary']) {
										$cal['description'][]=strip_tags($Item->Title);
									} else {
										$cal['summary']=strip_tags($Item->Title);
									}
									$RepeatTitle=$Item->Title;
								}
								$OldTitle=$Item->Title;
								$IsTitle=true;
								$OldSubTitle='';

								$timing='';
								if($OldStart != $Item->Start or $OldEnd != $Item->Duration) {
									$timing=$Item->Start.($Item->Duration ? '-'.addMinutes($Item->Start, $Item->Duration) : '');
									$OldStart=$Item->Start;
									$OldEnd=$Item->Duration;
								}

								$lnk=strip_tags($Item->Text);
								if(!$Item->Warmup) {
									// not a warmup!
									$OldComment='';
									switch($Item->Type) {
										case 'Q':
										case 'E':
											$lnk='';
											if($Item->Comments) {
												$cal['comment'][]=$Item->Comments;
												$timing='';
											}

											if(count($this->Groups[$Item->Type][$Session])==1) {
												$txt=$Item->Text.$lnk;
											} elseif($tmp=@end($this->Groups[$Item->Type][$Session]) and $tmp=@end($tmp) and $tmp=@end($tmp) and $Item==@end($tmp)) {
												$txt=$Item->DistanceName.$lnk;
											} else {
												$txt=$Item->DistanceName;
												// more distances defined so format is different...
											}

											if($cal['summary']) {
												$cal['description'][]=strip_tags($txt);
											} else {
												$cal['summary']=strip_tags($txt);
											}
											$IsTitle=false;
											break;
										case 'I':
										case 'T':
											$lnk=$Item->Text.': '.$Item->Events;
											$IsTitle=false;
											if($Item->Type=='I' and $Item->ElimType>=3) { // && $Item->Session<=1) {
												$SQL="select distinct ind1.IndRank LeftRank, ind2.IndRank RightRank, concat(upper(e1.EnFirstname), ' ', e1.EnName, ' (', c1.CoCode, ')') LeftSide,
														concat('(', c2.CoCode, ') ', upper(e2.EnFirstname), ' ', e2.EnName) RightSide,
														GrMatchNo, tf1.FinEvent as EvCode
													from Finals tf1
													inner join Finals tf2 on tf1.FinEvent=tf2.FinEvent and tf1.FinTournament=tf2.FinTournament and tf2.FinMatchNo=tf1.FinMatchNo+1 and tf2.FinMatchNo%2=1
													inner join FinSchedule fs1 on tf1.FinTournament=fs1.FsTournament and tf1.FinEvent=fs1.FsEvent and tf1.FinMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=0 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
													inner join FinSchedule fs2 on tf2.FinTournament=fs2.FsTournament and tf2.FinEvent=fs2.FsEvent and tf2.FinMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=0 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
													inner join Events on EvTournament=tf1.FinTournament and EvTeamEvent=0 and EvCode=tf1.FinEvent
													inner join Grids on tf1.FinMatchNo=GrMatchNo and GrPhase=$Item->Session
													left join Entries e1 on e1.EnId=tf1.FinAthlete and tf1.FinEvent IN ('$Item->Event')
													left join Entries e2 on e2.EnId=tf2.FinAthlete and tf2.FinEvent IN ('$Item->Event')
													left join Individuals ind1 on e1.EnId=ind1.IndId and tf1.FinEvent=ind1.IndEvent
													left join Individuals ind2 on e2.EnId=ind2.IndId and tf2.FinEvent=ind2.IndEvent
													left join Countries c1 on e1.EnCountry=c1.CoId and c1.CoTournament=$this->TourId
													left join Countries c2 on e2.EnCountry=c2.CoId and c2.CoTournament=$this->TourId
													where tf1.FinTournament=$this->TourId
													order by GrMatchNo";
												$q=safe_r_SQL($SQL);
												if(!safe_num_rows($q)) {
													continue 2;
												}
												$tmp=array();
												while($r=safe_fetch($q)) {
													if($Item->ElimType==3) {
														// ElimPool... writes who or a generic sentence
														$opps=array();
														if(isset($this->PoolMatchWinners[$r->GrMatchNo])) {
															$opps[]=($this->Ranking ? '#'.$r->LeftRank.' ' : '') . $this->PoolMatchWinners[$r->GrMatchNo];
														}
														if(isset($this->PoolMatchWinners[$r->GrMatchNo+1])) {
															$opps[]=$this->PoolMatchWinners[$r->GrMatchNo+1]. ($this->Ranking ? ' #'.$r->RightRank : '');
														}

														$tmp[(isset($this->PoolMatches[$r->GrMatchNo]) ? $this->PoolMatches[$r->GrMatchNo] : $Item->Text).($opps ? '' : ': '.$Item->Events)][]=($opps ? $r->EvCode.': '.implode(' - ', $opps) : '');
													} elseif($Item->ElimType==4) {
														// ElimPool... writes who or a generic sentence
														$opps=array();
														if(isset($this->PoolMatchWinnersWA[$r->GrMatchNo])) {
															$opps[]=($this->Ranking ? '#'.$r->LeftRank.' ' : '') . $this->PoolMatchWinnersWA[$r->GrMatchNo];
														}
														if(isset($this->PoolMatchWinnersWA[$r->GrMatchNo+1])) {
															$opps[]=$this->PoolMatchWinnersWA[$r->GrMatchNo+1]. ($this->Ranking ? ' #'.$r->RightRank : '');
														}

														$tmp[(isset($this->PoolMatchesWA[$r->GrMatchNo]) ? $this->PoolMatchesWA[$r->GrMatchNo] : $Item->Text).($opps ? '' : ': '.$Item->Events)][]=($opps ? $r->EvCode.': '.implode(' - ', $opps) : '');
													} else {
														$tmp[$lnk][]= '#'.$r->LeftRank.' - #'.$r->RightRank;
													}
												}

												if($tmp) {
													ksort($tmp);
													$cal['summary']= $Item->Title.' '.implode(' + ', array_keys($tmp));
													foreach($tmp as $Category => $Opponents) {
														foreach($Opponents as $Opponent) {
															if(!$Opponent) {
																continue;
															}
															$cal['description'][]=$Opponent;
														}
													}
												} elseif($Item->ElimType==4) {
													// no tmp and should be, so empty event...
													continue 2;
												}
											} else {
												$cal['summary']=$Item->Title.' - '.strip_tags($lnk);
											}
											break;
										default:
// 											debug_svela($Item);
									}

								} else {
									if($Item->Comments) {
										$lnk=$Item->Comments;
									} else {
										switch($Item->Type) {
											case 'I':
											case 'T':
												$lnk=$Item->Text.': '.$Item->Events.' '.'warmup';
												break;
											default:
                                                $lnk = get_text("WarmUp", "Tournament") . ' ' . $lnk;
										}
									}
									if($OldComment==$lnk) continue;
									$OldComment=$lnk;
									if($cal['summary']) {
										$cal['description'][]=strip_tags($lnk);
									} else {
										$cal['summary']=strip_tags($lnk);
									}
									$IsTitle=false;
								}
							}
							$FirstTitle=false;
							$cal['summary']=$cal['summary'].' - '.$COMP->ToName;
							$ICS->addEvent($cal);
						}
					}
				}
			}
		}
		return $ICS->output($Download);
	}

	function getScheduleBoinx() {
		$nDay=0;
		$ret=array();

		foreach($this->GetSchedule() as $Date => $Times) {
			$nDay++;
			$nGroup=0;
			$n=0;

			$OldTitle='';
			$OldSubTitle='';
			$OldType='';
			$OldStart='';
			$OldEnd='';
			$IsTitle=false;

			$OldComment='';
			ksort($Times);
			foreach($Times as $Time => $Sessions) {
				foreach($Sessions as $Session => $Distances) {
					foreach($Distances as $Distance => $Items) {
						foreach($Items as $k => $Item) {
							$key=$Item->Day
							.'|'.$Time
							.'|'.$Session
							.'|'.$Distance
							.'|'.$Item->Order;
							$ActiveSession=in_array($key, $this->ActiveSessions);


							$LinTim=$Item->Start.($Item->Duration ? '-'.addMinutes($Item->Start, $Item->Duration) : '');
							$LinTit='';
							$LinSub='';
							$LinTxt='';
							if($Item->Type=='Z') {
								// free text
								$OldComment='';
								if($OldTitle!=$Item->Title and $Item->Title) {
									if(!$IsTitle) {
										$LinTit=$Item->Title;
									}
									$OldTitle=$Item->Title;
									$OldSubTitle='';
									$IsTitle=true;
								}
								if($OldSubTitle!=$Item->SubTitle and $Item->SubTitle) {
									$LinSub=$Item->SubTitle;
									$OldSubTitle=$Item->SubTitle;
									$IsTitle=false;
								}
								if($Item->Text) {
									$LinTxt=$Item->Text;
									$IsTitle=false;
								}
								$OldStart=$Item->Start;
								$OldEnd=$Item->Duration;
								$ret[$nDay][]=array($Item->Day, $LinTim, $LinTit, $LinSub, $LinTxt, $ActiveSession, '', '', '', '','');
							} else {
								// all other kind of texts have a title and the items
								if($OldTitle!=$Item->Title) {
									// Title
									if(!$IsTitle) {
										$LinTit=$Item->Title;
									}
									$OldTitle=$Item->Title;
									$OldSubTitle='';
									$IsTitle=true;
								}
								if($OldSubTitle!=$Item->SubTitle) {
									// SubTitle
									$LinSub=$Item->SubTitle;
									$OldSubTitle=$Item->SubTitle;
									$IsTitle=false;
								}

// 								$timing='';
// 								if($OldStart != $Item->Start or $OldEnd != $Item->Duration) {
// 									$timing=$Item->Start.($Item->Duration ? '-'.addMinutes($Item->Start, $Item->Duration) : '');
// 									$OldStart=$Item->Start;
// 									$OldEnd=$Item->Duration;
// 								}

								$lnk=$Item->Text;
								if(!$Item->Warmup) {
									// not warmup!
									$OldComment='';
									switch($Item->Type) {
										case 'Q':
										case 'E':
											$lnk='';
											if($Item->Comments) {
												$ret[$nDay][]=array($Item->Day, $LinTim, $LinTit, $LinSub, $Item->Comments, $ActiveSession, '', '', '', '','');
											}
											if(count($this->Groups[$Item->Type][$Session])==1) {
												$txt=$Item->Text.$lnk;
											} elseif($Item==@end(end(end(end($this->Groups[$Item->Type][$Session]))))) {
												$txt=$Item->DistanceName.$lnk;
											} else {
												$txt=$Item->DistanceName;
												// more distances defined so format is different...
											}
											$ret[$nDay][]=array($Item->Day, $LinTim, $LinTit, $LinSub, $txt, $ActiveSession, '', '', '', '','');

											$IsTitle=false;
											break;
										case 'I':
										case 'T':
											$lnk=$Item->Text.': '.$Item->Events;
											$tmp=array($Item->Day, $LinTim, $LinTit, $LinSub, $lnk, $ActiveSession, '', '', '', '','');
											$IsTitle=false;
											if(true or $this->Finalists) { // && $Item->Session<=1) {
												// Bronze or Gold Finals
												if($Item->Type=='I') {
													$SQL="select tf1.FinMatchNo MatchNo, 0 TeamEvent, tf1.FinEvent Event, concat(upper(e1.EnFirstname), ' ', e1.EnName, ' (', c1.CoCode, ')') Opp1, concat(upper(e2.EnFirstname), ' ', e2.EnName, ' (', c2.CoCode, ')') Opp2
													from Finals tf1
													inner join Finals tf2 on tf1.FinEvent=tf2.FinEvent and tf1.FinTournament=tf2.FinTournament and tf2.FinMatchNo=tf1.FinMatchNo+1 and tf2.FinMatchNo%2=1
													inner join FinSchedule fs1 on tf1.FinTournament=fs1.FsTournament and tf1.FinEvent=fs1.FsEvent and tf1.FinMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=0 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
													inner join FinSchedule fs2 on tf2.FinTournament=fs2.FsTournament and tf2.FinEvent=fs2.FsEvent and tf2.FinMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=0 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
													inner join Entries e1 on e1.EnId=tf1.FinAthlete and tf1.FinEvent IN ('$Item->Event')
													inner join Entries e2 on e2.EnId=tf2.FinAthlete and tf2.FinEvent IN ('$Item->Event')
													inner join Countries c1 on e1.EnCountry=c1.CoId and c1.CoTournament=$this->TourId
													inner join Countries c2 on e2.EnCountry=c2.CoId and c2.CoTournament=$this->TourId
													inner join Grids on tf1.FinMatchNo=GrMatchNo and GrPhase=$Item->Session
													where tf1.FinTournament=$this->TourId ";
												} else {
													$SQL="select tf1.TfMatchNo MatchNo, 1 TeamEvent, tf1.TfEvent Event, concat(c1.CoName, ' (', c1.CoCode, ')') Opp1, concat(c2.CoName, '(', c2.CoCode, ') ') Opp2
													from TeamFinals tf1
													inner join TeamFinals tf2 on tf1.TfEvent=tf2.TfEvent and tf1.TfTournament=tf2.TfTournament and tf2.TfMatchNo=tf1.TfMatchNo+1 and tf2.TfMatchNo%2=1
													inner join FinSchedule fs1 on tf1.TfTournament=fs1.FsTournament and tf1.TfEvent=fs1.FsEvent and tf1.TfMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=1 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
													inner join FinSchedule fs2 on tf2.TfTournament=fs2.FsTournament and tf2.TfEvent=fs2.FsEvent and tf2.TfMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=1 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
													inner join Countries c1 on c1.CoId=tf1.TfTeam and tf1.TfEvent IN ('$Item->Event')
													inner join Countries c2 on c2.CoId=tf2.TfTeam and tf2.TfEvent IN ('$Item->Event')
													inner join Grids on tf1.TfMatchNo=GrMatchNo and GrPhase=$Item->Session
													where tf1.TfTournament=$this->TourId";
												}
												$q=safe_r_SQL($SQL);
												if(safe_num_rows($q)==1 and $r=safe_fetch($q) and trim($r->Opp1) and trim($r->Opp2)) {
													$tmp[6] =$r->MatchNo;
													$tmp[7] =$r->TeamEvent;
													$tmp[8] =$r->Event;
													$tmp[9] =$r->Opp1;
													$tmp[10]=$r->Opp2;
												}
											}
											$ret[$nDay][]=$tmp;

											break;
										default:
// 											debug_svela($Item);
									}

								} else {
									if($Item->Comments) {
										$lnk=$Item->Comments;
									} else {
										switch($Item->Type) {
											case 'I':
											case 'T':
												$lnk=$Item->Text.': '.$Item->Events.' '.'warmup';
												break;
											default:
                                                $lnk = get_text("WarmUp", "Tournament") . ' ' . $lnk;
										}
									}
									if($OldComment==$lnk) continue;
									$OldComment=$lnk;
									$ret[$nDay][]=array($Item->Day, $LinTim, $LinTit, $LinSub, $lnk, $ActiveSession, '','','','','');
									$IsTitle=false;
								}
							}
						}
					}
				}
			}
		}

		$XmlDoc = new DOMDocument('1.0', 'UTF-8');
		$XmlRoot = $XmlDoc->createElement('schedule');
		$XmlDoc->appendChild($XmlRoot);

		foreach($ret as $nDay => $events) {
			$Day = $XmlDoc->createElement('day'.$nDay);
			$XmlRoot->AppendChild($Day);
			$nGroup=0;
			foreach($events as $n=>$Item) {
				if(($n%8)==0) {
					$Group = $XmlDoc->createElement('groupevent'.(++$nGroup));
					$Day->AppendChild($Group);
				}
				$Line = $XmlDoc->createElement('event'.($n%8 + 1));
				$Group->AppendChild($Line);

				$a=$XmlDoc->createElement('day');
				$a->AppendChild($XmlDoc->createCDATASection($Item[0]));
				$Line->AppendChild($a);

				$a=$XmlDoc->createElement('time');
				$a->AppendChild($XmlDoc->createCDATASection($Item[1]));
				$Line->AppendChild($a);

				$a=$XmlDoc->createElement('name');
				$a->AppendChild($XmlDoc->createCDATASection($Item[2]));
				$Line->AppendChild($a);

				$a=$XmlDoc->createElement('sub');
				$a->AppendChild($XmlDoc->createCDATASection($Item[3]));
				$Line->AppendChild($a);

				$a=$XmlDoc->createElement('details');
				$a->AppendChild($XmlDoc->createCDATASection($Item[4]));
				$Line->AppendChild($a);

				$a=$XmlDoc->createElement('on', $Item[5] ? 1: 0 );
				$Line->AppendChild($a);

				$a=$XmlDoc->createElement('matchno');
				$a->AppendChild($XmlDoc->createCDATASection($Item[6]));
				$Line->AppendChild($a);

				$a=$XmlDoc->createElement('team');
				$a->AppendChild($XmlDoc->createCDATASection($Item[7]));
				$Line->AppendChild($a);

				$a=$XmlDoc->createElement('event');
				$a->AppendChild($XmlDoc->createCDATASection($Item[8]));
				$Line->AppendChild($a);

				$a=$XmlDoc->createElement('opp1');
				$a->AppendChild($XmlDoc->createCDATASection($Item[9]));
				$Line->AppendChild($a);

				$a=$XmlDoc->createElement('opp2');
				$a->AppendChild($XmlDoc->createCDATASection($Item[10]));
				$Line->AppendChild($a);
			}
		}
		return $XmlDoc;
	}

	function exportODS($filename='SpreadSheet.ods', $type='a') {
		if(!$filename) $filename=$_SESSION['TourCode'].'.ods';
		require_once('Common/ods/ods.php');
		$this->Ods = new ods();
		$this->Ods->setActiveSheet('Schedule');

		$this->Ods->setStyle('DateCell',
				array('style:text-properties' => array('fo:font-weight' => 'bold', 'fo:font-size' => '18pt')),
				array('style:family'=>'table-cell')
				);
		$this->Ods->setStyle('DateRow',
				array('style:table-row-properties' => array('style:row-height' => '24pt', 'style:use-optimal-row-height' => 'true', 'fo:background-color' => '#dddddd')),
				array('style:family'=>'table-row')
				);
		$this->Ods->setStyle('MainTitle', array('style:text-properties' => array('fo:font-weight' => 'bold', 'fo:font-size' => '25pt')));
		$this->Ods->setStyle('MainTitleRow',
				array('style:table-row-properties' => array('style:row-height' => '36pt', 'style:use-optimal-row-height' => 'true')),
				array('style:family'=>'table-row')
				);
		$this->Ods->setStyle('Title', array('style:text-properties' => array('fo:font-weight' => 'bold', 'fo:font-size' => '14pt')));
		$this->Ods->setStyle('TitleRow',
				array('style:table-row-properties' => array('style:row-height' => '21pt', 'style:use-optimal-row-height' => 'true')),
				array('style:family'=>'table-row')
				);
		$this->Ods->setStyle('SubTitle', array('style:text-properties' => array('fo:font-weight' => 'bold', 'fo:font-style' => 'italic', 'fo:font-size' => '12pt')));
		$this->Ods->setStyle('Comments', array('style:text-properties' => array('fo:font-style' => 'italic')));
		$this->Ods->setStyle('Duration', array('style:text-properties' => array('fo:font-style' => 'italic', 'fo:color' => '#666666')));
		//$TXT=array();

		$this->Ods->setStyle('DateFOPCell',
				array('style:text-properties' => array('fo:font-weight' => 'bold', 'fo:font-size' => '12pt')),
				array('style:family'=>'table-cell')
				);
		$this->Ods->setStyle('DateFOPRow',
				array('style:table-row-properties' => array('style:row-height' => '18pt', 'style:use-optimal-row-height' => 'true', 'fo:background-color' => '#dddddd')),
				array('style:family'=>'table-row')
				);
		$this->Ods->setStyle('TitleFOP', array('style:text-properties' => array('fo:font-weight' => 'bold', 'fo:font-size' => '10pt')));
		$this->Ods->setStyle('TitleFOPRow',
				array('style:table-row-properties' => array('style:row-height' => '15pt', 'style:use-optimal-row-height' => 'true')),
				array('style:family'=>'table-row')
				);
		$this->Ods->setStyle('SubTitleFOP', array('style:text-properties' => array('fo:font-weight' => 'bold', 'fo:font-style' => 'italic', 'fo:font-size' => '10pt')));
		$this->Ods->setStyle('Distance', array('style:paragraph-properties' => array('fo:text-align' => 'center')));
		//$TXT=array();

		$this->Ods->setStyle('TimeCol', array('style:table-column-properties' => array('style:column-width' => '1cm')),
				array('style:family'=>'table-column')
				);
		$this->Ods->setStyle('DescCol', array('style:table-column-properties' => array('style:column-width' => '7.5cm')),
				array('style:family'=>'table-column')
				);
		$this->Ods->setStyle('TgtCol', array('style:table-column-properties' => array('style:column-width' => '0.5cm')),
				array('style:family'=>'table-column')
				);

		$this->Ods->setStyle('ColorWarmup', array('style:table-cell-properties' => array('fo:background-color' => sprintf("#%02X%02X%02X", 198, 198, 198))));

		$row=array('Schedule');

		if($this->SchedVersion) {
			$row[]=null;
			$row[]=null;
			$row[]=$this->SchedVersionText;
		}
		$this->Ods->setRowStyle('MainTitleRow');
		$this->Ods->setCellStyle('MainTitle', null, 0);
		$this->Ods->addRow($row);


// 		$this->Ods->currentRow=-1;
		// seed the schedule
		$this->GetSchedule();

		foreach($this->Schedule as $Date => $Times) {
			$this->Ods->currentRow+=2;
			$this->Ods->currentCell=0;

			$this->Ods->setRowStyle('DateRow');
			$this->Ods->setCellStyle('DateCell');
			$this->Ods->addRow(formatTextDate($Date, true));

			$OldTitle='';
			$OldSubTitle='';
			$OldType='';
			$OldStart='';
			$OldEnd='';
			$IsTitle=false;
			$FirstTitle=true;

			$OldComment='';
			ksort($Times);

			foreach($Times as $Time => $Sessions) {
				$Singles=array();
				foreach($Sessions as $Session => $Distances) {
					foreach($Distances as $Distance => $Items) {
						foreach($Items as $k => $Item) {

							if($Item->Comments) {
								$SingleKey="{$Item->Duration}-{$Item->Title}-{$Item->SubTitle}-{$Item->Comments}";
								if(in_array($SingleKey, $Singles)) continue;
								$Singles[]=$SingleKey;
							}

							$timingDelayed='';
							$timing=array('', '', '', '');


							if($Item->Type=='Z') {
								// free text
								$timing[1]=$Item->Start;
								if($Item->Duration) {
									$timing[2] = addMinutes($Item->Start, $Item->Duration);
									$timing[3] = sprintf('%02d:%02d', $Item->Duration/60, $Item->Duration%60);
								}
								if($Item->Shift) $timing[0] = '+'.$Item->Shift;

								if($OldTitle!=$Item->Title and $Item->Title) {
									if(!$IsTitle) {
										$this->Ods->setRowStyle('TitleRow');
										$this->Ods->setCellStyle('Title', null, 4);
										$this->Ods->addRow(array('', '', '', '', htmlspecialchars(strip_tags($Item->Title))));
									}
									$OldTitle=$Item->Title;
									$OldSubTitle='';
									$IsTitle=true;
								}

								if($OldSubTitle!=$Item->SubTitle and $Item->SubTitle) {
									$row=array('', '', '', '', htmlspecialchars(strip_tags($Item->SubTitle)));
									if(!$Item->Text) {
										if($Item->Shift and $timing[1]) {
											$row[0]=$timing[0];
										}
										$row[1]=$timing[1];
										$row[2]=$timing[2];
										$row[3]=$timing[3];
										$timing[3]='';
										$timing[2]='';
										$timing[1]='';
									}
									$this->Ods->setCellStyle('SubTitle', null, 4);
									$this->Ods->setCellStyle('Duration', null, 3);
									$this->Ods->addRow($row);
									$OldSubTitle=$Item->SubTitle;
									$IsTitle=false;
								}
								if($Item->Text) {
									$row=array('', '', '', '', htmlspecialchars(strip_tags($Item->Text)));
									if($Item->Shift and $timing[1]) {
										$row[0]=$timing[0];
									}
									$row[1]=$timing[1];
									$row[2]=$timing[2];
									$row[3]=$timing[3];
									$timing[3]='';
									$timing[2]='';
									$timing[1]='';
									$this->Ods->setCellStyle('Duration', null, 3);
									$this->Ods->addRow($row);
									$IsTitle=false;
								}
								$OldStart=$Item->Start;
								$OldEnd=$Item->Duration;
								$OldComment='';
							} else {
								// all other kind of texts have a title and the items
								if($OldTitle!=$Item->Title) {
									// Title
									if(!$IsTitle) {
										$this->Ods->setRowStyle('TitleRow');
										$this->Ods->setCellStyle('Title', null, 4);
										$this->Ods->addRow(array('', '', '', '', htmlspecialchars(strip_tags($Item->Title))));
									}
									$OldTitle=$Item->Title;
									$IsTitle=true;
									$OldSubTitle='';
								}
								if($OldSubTitle!=$Item->SubTitle) {
									// SubTitle
									$row=array('', '', '', '', htmlspecialchars(strip_tags($Item->SubTitle)));
									$this->Ods->setCellStyle('SubTitle', null, 4);
									$this->Ods->addRow($row);
									$OldSubTitle=$Item->SubTitle;
									$IsTitle=false;
								}

								$timing=array('', '', '', '');
								if($OldStart != $Item->Start or $OldEnd != $Item->Duration) {
									$timing[1]=$Item->Start;
									if($Item->Duration) {
										$timing[2]=addMinutes($Item->Start, $Item->Duration);
										$timing[3] = sprintf('%02d:%02d', $Item->Duration/60, $Item->Duration%60);
									}
									if($Item->Shift) $timing[0] = '+'.$Item->Shift;
									$OldStart=$Item->Start;
									$OldEnd=$Item->Duration;
								}

								$lnk=strip_tags($Item->Text);
								if(!$Item->Warmup) {
									// not warmup!
									$OldComment='';
									switch($Item->Type) {
										case 'Q':
										case 'E':
											$t=safe_r_SQL("select distinct EcCode, EvTeamEvent from Entries
												INNER JOIN Qualifications on QuId=EnId and QuSession=$Item->Session
												INNER JOIN EventClass ON EcClass=EnClass AND EcDivision=EnDivision AND EcTournament=EnTournament and if(EcSubClass='', true, EcSubClass=EnSubClass)
												INNER JOIN Events on EvCode=EcCode AND EvTeamEvent=IF(EcTeamEvent!=0, 1,0) AND EvTournament=EcTournament
												where EnTournament=$this->TourId
												order by EvTeamEvent, EvProgr");
											$lnk='';
											if($Item->Comments) {
												$row=array('', '', '', '', htmlspecialchars(strip_tags($Item->Comments)));
												if($Item->Shift and $timing[1]) {
													$row[0]=$timing[0];
												}
												$row[1]=$timing[1];
												$row[2]=$timing[2];
												$row[3]=$timing[3];
												$timing[3]='';
												$timing[2]='';
												$timing[1]='';
												$this->Ods->setCellStyle('Duration', null, 3);
												$this->Ods->setCellStyle('Comments', null, 4);
												$this->Ods->addRow($row);
												$IsTitle=false;
											}

											if(count($this->Groups[$Item->Type][$Session])==1) {
												$txt=$Item->Text.$lnk;
											} elseif($Item==@end(end(end(end($this->Groups[$Item->Type][$Session]))))) {
												$txt=$Item->DistanceName.$lnk;
											} else {
												$txt=$Item->DistanceName;
												// more distances defined so format is different...
											}
											$row=array('', '', '', '', htmlspecialchars(strip_tags($txt)));
											if($Item->Shift and $timing[1]) {
												$row[0]=$timing[0];
											}
											$row[1]=$timing[1];
											$row[2]=$timing[2];
											$row[3]=$timing[3];
											$timing[3]='';
											$timing[2]='';
											$timing[1]='';
											$this->Ods->setCellStyle('Duration', null, 3);
											$this->Ods->addRow($row);
											$IsTitle=false;
											break;
										case 'I':
										case 'T':
											$lnk=$Item->Text.': '.$Item->Events;
											$row=array('', '', '', '', htmlspecialchars(strip_tags($lnk)));
											if($Item->Shift and $timing[1]) {
												$row[0]=$timing[0];
											}
											$row[1]=$timing[1];
											$row[2]=$timing[2];
											$row[3]=$timing[3];
											$timing[3]='';
											$timing[2]='';
											$timing[1]='';
											$this->Ods->setCellStyle('Duration', null, 3);
											$this->Ods->addRow($row);
											$IsTitle=false;
											if($this->Finalists) { // && $Item->Session<=1) {
												// Bronze or Gold Finals
												if($Item->Type=='I') {
													$SQL="select ind1.IndRank LeftRank, ind2.IndRank RightRank, concat(upper(e1.EnFirstname), ' ', e1.EnName, ' (', c1.CoCode, ')') LeftSide,
													concat('(', c2.CoCode, ') ', upper(e2.EnFirstname), ' ', e2.EnName) RightSide
													from Finals tf1
													inner join Finals tf2 on tf1.FinEvent=tf2.FinEvent and tf1.FinTournament=tf2.FinTournament and tf2.FinMatchNo=tf1.FinMatchNo+1 and tf2.FinMatchNo%2=1
													inner join FinSchedule fs1 on tf1.FinTournament=fs1.FsTournament and tf1.FinEvent=fs1.FsEvent and tf1.FinMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=0 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
													inner join FinSchedule fs2 on tf2.FinTournament=fs2.FsTournament and tf2.FinEvent=fs2.FsEvent and tf2.FinMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=0 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
													inner join Entries e1 on e1.EnId=tf1.FinAthlete and tf1.FinEvent IN ('$Item->Event')
													inner join Entries e2 on e2.EnId=tf2.FinAthlete and tf2.FinEvent IN ('$Item->Event')
													inner join Individuals ind1 on e1.EnId=ind1.IndId and tf1.FinEvent=ind1.IndEvent
													inner join Individuals ind2 on e2.EnId=ind2.IndId and tf2.FinEvent=ind2.IndEvent
													inner join Countries c1 on e1.EnCountry=c1.CoId and c1.CoTournament=$this->TourId
													inner join Countries c2 on e2.EnCountry=c2.CoId and c2.CoTournament=$this->TourId
													inner join Grids on tf1.FinMatchNo=GrMatchNo and GrPhase=$Item->Session
													where tf1.FinTournament=$this->TourId";
												} else {
													$SQL="select ind1.TeRank LeftRank, ind2.TeRank RightRank, concat(c1.CoName, ' (', c1.CoCode, ')') LeftSide,
													concat('(', c2.CoCode, ') ', c2.CoName) RightSide
													from TeamFinals tf1
													inner join TeamFinals tf2 on tf1.TfEvent=tf2.TfEvent and tf1.TfTournament=tf2.TfTournament and tf2.TfMatchNo=tf1.TfMatchNo+1 and tf2.TfMatchNo%2=1
													inner join FinSchedule fs1 on tf1.TfTournament=fs1.FsTournament and tf1.TfEvent=fs1.FsEvent and tf1.TfMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=1 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
													inner join FinSchedule fs2 on tf2.TfTournament=fs2.FsTournament and tf2.TfEvent=fs2.FsEvent and tf2.TfMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=1 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
													inner join Countries c1 on c1.CoId=tf1.TfTeam and tf1.TfEvent IN ('$Item->Event')
													inner join Countries c2 on c2.CoId=tf2.TfTeam and tf2.TfEvent IN ('$Item->Event')
													inner join Teams ind1 on c1.CoId=ind1.TeCoId and tf1.TfEvent=ind1.TeEvent and tf1.TfSubTeam=ind1.TeSubTeam and ind1.TeFinEvent=1
													inner join Teams ind2 on c2.CoId=ind2.TeCoId and tf2.TfEvent=ind2.TeEvent and tf2.TfSubTeam=ind2.TeSubTeam and ind2.TeFinEvent=1
													inner join Grids on tf1.TfMatchNo=GrMatchNo and GrPhase=$Item->Session
													where tf1.TfTournament=$this->TourId";
												}
												$q=safe_r_SQL($SQL);
												if(safe_num_rows($q)==1 and $r=safe_fetch($q) and trim($r->LeftSide) and trim($r->RightSide)) {
													$this->Ods->addCell(htmlspecialchars(strip_tags(($this->Ranking ? '#'.$r->LeftRank.' ' : '') . $r->LeftSide.' - '.$r->RightSide . ($this->Ranking ? ' #'.$r->RightRank : ''))));
												}
											}
											break;
										default:
// 											debug_svela($Item);
									}

								} else {
									if($Item->Comments) {
										$lnk=$Item->Comments;
									} else {
										switch($Item->Type) {
											case 'I':
											case 'T':
												$lnk=$Item->Text.': '.$Item->Events.' '.'warmup';
												break;
											default:
                                                $lnk = get_text("WarmUp", "Tournament") . ' ' . $lnk;
										}
									}
									if($OldComment==$lnk) continue;
									$OldComment=$lnk;
									$row=array('', '', '', '', htmlspecialchars(strip_tags($lnk)));
									if($Item->Shift and $timing[1]) {
										$row[0]=$timing[0];
									}
									$row[1]=$timing[1];
									$row[2]=$timing[2];
									$row[3]=$timing[3];
									$this->Ods->setCellStyle('Duration', null, 3);
									$this->Ods->setCellStyle('Comments', null, 4);
									$this->Ods->addRow($row);
									$IsTitle=false;
								}
							}
							$FirstTitle=false;
						}
					}
				}
			}
		}

		$terne=array(
				array(0,255,0),
				array(255,153,255),
				array(255,255,204),
				array(153,153,255),
				array(255,153,0),
				array(204,255,204),
				//array(102,0,51),
				array(51,204,204),
		);

		$ColorArray=array();
		foreach($terne as $col) {
			$ColorArray[] = sprintf("#%02X%02X%02X", $col[0], $col[1], $col[2]);
		}
		foreach($terne as $col) {
			$ColorArray[] = sprintf("#%02X%02X%02X", $col[1], $col[2], $col[0]);
		}
		foreach($terne as $col) {
			$ColorArray[] = sprintf("#%02X%02X%02X", $col[2], $col[0], $col[1]);
		}

		$ColorAssignment = array();
		$ColorIndex=0;

		if(!($LocationsToPrint=Get_Tournament_Option('FopLocations'))) {
			$tmp=new stdClass();
			$tmp->Loc='';
			$tmp->Tg1=1;
			$tmp->Tg2=99999;
            $LocationsToPrint = array();
			$LocationsToPrint[]=$tmp;
		}

		$Done=array();

		$OldDate='';
		$OldTime='';

		foreach($this->Schedule as $Date => $Times) {
			$this->Ods->setActiveSheet($Date);
			$this->Ods->currentRow = 0;
			$this->Ods->currentCell= 0;

			$this->Ods->addRow($this->FopVersionText);
			$this->Ods->currentRow ++;

			$this->Ods->setRowStyle('DateFOPRow');
			$this->Ods->setColStyle('TimeCol', 0, 4);
			$this->Ods->setColStyle('DescCol', 4, 1);
			$this->Ods->setColStyle('TgtCol', 5, 250);
			$this->Ods->setCellStyle('DateFOPCell');
			$this->Ods->addRow(formatTextDate($Date, true));

			$OldTitle='';
			$OldSubTitle='';
			$OldType='';
			$OldStart='';
			$OldEnd='';
			$IsTitle=false;

			$OldComment='';
			$OldTime='';
			$RowTime=$this->Ods->currentRow;
			ksort($Times);

			foreach($Times as $Time => $Sessions) {
				$Singles=array();
				$this->Ods->currentRow++;
				foreach($Sessions as $Session => $Distances) {
					foreach($Distances as $Distance => $Items) {
						foreach($Items as $k => $Item) {

							if($Item->Comments) {
								$SingleKey="{$Item->Duration}-{$Item->Title}-{$Item->SubTitle}-{$Item->Comments}";
								if(in_array($SingleKey, $Singles)) continue;
								$Singles[]=$SingleKey;
							}

							$timingDelayed='';
							$timing=array('', '', '', '');


							if($Item->Type=='Z') {
								// free text
								$timing[1]=$Item->Start;
								if($Item->Duration) {
									$timing[2] = addMinutes($Item->Start, $Item->Duration);
									$timing[3] = sprintf('%02d:%02d', $Item->Duration/60, $Item->Duration%60);
								}
								if($Item->Shift) $timing[0] = '+'.$Item->Shift;

								if($OldTitle!=$Item->Title and $Item->Title) {
									if(!$IsTitle) {
										$this->Ods->setRowStyle('TitleFOPRow');
										$this->Ods->setCellStyle('TitleFOP', null, 4);
										$this->Ods->addRow(array('', '', '', '', htmlspecialchars(strip_tags($Item->Title))));
									}
									$OldTitle=$Item->Title;
									$OldSubTitle='';
									$IsTitle=true;
								}

								if($OldSubTitle!=$Item->SubTitle and $Item->SubTitle) {
									$row=array('', '', '', '', htmlspecialchars(strip_tags($Item->SubTitle)));
									if(!$Item->Text) {
										if($Item->Shift and $timing[1]) {
											$row[0]=$timing[0];
										}
										$row[1]=$timing[1];
										$row[2]=$timing[2];
										$row[3]=$timing[3];
										$timing[3]='';
										$timing[2]='';
										$timing[1]='';
									}
									$this->Ods->setCellStyle('SubTitleFOP', null, 4);
									$this->Ods->setCellStyle('Duration', null, 3);
									$this->Ods->addRow($row);
									$OldSubTitle=$Item->SubTitle;
									$IsTitle=false;
								}
								if($Item->Text) {
									$row=array('', '', '', '', htmlspecialchars(strip_tags($Item->Text)));
									if($Item->Shift and $timing[1]) {
										$row[0]=$timing[0];
									}
									$row[1]=$timing[1];
									$row[2]=$timing[2];
									$row[3]=$timing[3];
									$timing[3]='';
									$timing[2]='';
									$timing[1]='';
									$this->Ods->setCellStyle('Duration', null, 3);
									$this->Ods->addRow($row);
									$IsTitle=false;
								}
								$OldStart=$Item->Start;
								$OldEnd=$Item->Duration;
								$OldComment='';

								// is there a target assigment?
								if($Item->Target) {
									$rows=array();
									$MaxTgt=0;
									foreach(explode(',', $Item->Target) as $Block) {
										$tmp= explode('@', $Block);
										$Range=$tmp[0];
										$Dist=$tmp[1];
										if(!empty($tmp[2])) $Event=$tmp[2];
										if(!empty($tmp[3])) $Target=$tmp[3];

										if(empty($ColorAssignment["{$Dist}-{$Event}"])) {
											$ColorAssignment["{$Dist}-{$Event}"]='Color'.$ColorIndex;
											$this->Ods->setStyle('Color'.$ColorIndex, array('style:table-cell-properties' => array('fo:background-color' => $ColorArray[$ColorIndex])));
											$ColorIndex++;
										}

										$tmp=explode('-', $Range);
										if(count($tmp)>1) {
											foreach(range($tmp[0], $tmp[1]) as $tgt) {
												$rows[$tgt]['d']=$Dist;
												$rows[$tgt]['e']=$Event;
												$rows[$tgt]['c']=$ColorAssignment["{$Dist}-{$Event}"];
												$MaxTgt=max($MaxTgt, $tgt);
											}
										} else {
											$rows[$tmp[0]]['d']=$Dist;
											$rows[$tmp[0]]['e']=$Event;
											$rows[$tmp[0]]['c']=$ColorAssignment["{$Dist}-{$Event}"];
											$MaxTgt=max($MaxTgt, $tmp[0]);
										}
									}

									$tgts=array();
									$oldDistance=0;
									$grp=0;
									ksort($rows);

									foreach($rows as $tgt => $def) {
										if($oldDistance!="{$def['d']}-{$def['e']}") $grp++;
										$oldDistance="{$def['d']}-{$def['e']}";
										$tgts[$grp]['distance']=$def['d'];
										$tgts[$grp]['targets'][]=$tgt;
									}

									$this->Ods->currentRow-=2;

									foreach($tgts as $k=>$grp) {
										$this->Ods->currentCell=$grp['targets'][0]+6;
										$this->Ods->setCellStyle('Distance');
										$this->Ods->setCellAttribute('table:number-columns-spanned', 1+end($grp['targets'])-$grp['targets'][0]);
										$this->Ods->Cell($grp['distance'], 'string');
										foreach($grp['targets'] as $tgt) {
											$this->Ods->setCellStyle($rows[$tgt]['c'], $this->Ods->currentRow+1, $tgt+6);
										}
										$this->Ods->Cell($rows[$tgt]['e'], 'string', $this->Ods->currentRow+1, $grp['targets'][0]+6, true);
										// 												$this->Ods->Cell(1+end($grp['targets'])-$grp['targets'][0], 'string', $this->Ods->currentRow+1, end($grp['targets'])+6, true);
									}
									$OldRow=$this->Ods->currentRow+3;
									$this->Ods->currentRow=2;
									$this->Ods->currentCell=7;
									foreach(range(1, $MaxTgt) as $tgt) $this->Ods->Cell($tgt);
									$this->Ods->currentCell=0;
									$this->Ods->currentRow=$OldRow;

								}
							} else {
								// all other kind of texts have a title and the items
								if($OldTitle!=$Item->Title) {
									// Title
									if(!$IsTitle) {
										$this->Ods->setRowStyle('TitleRow');
										$this->Ods->setCellStyle('Title', null, 4);
										$this->Ods->addRow(array('', '', '', '', htmlspecialchars(strip_tags($Item->Title))));
									}
									$OldTitle=$Item->Title;
									$IsTitle=true;
									$OldSubTitle='';
								}
								if($OldSubTitle!=$Item->SubTitle) {
									// SubTitle
									$row=array('', '', '', '', htmlspecialchars(strip_tags($Item->SubTitle)));
									$this->Ods->setCellStyle('SubTitle', null, 4);
									$this->Ods->addRow($row);
									$OldSubTitle=$Item->SubTitle;
									$IsTitle=false;
								}

								$timing=array('', '', '', '');
								if($OldStart != $Item->Start or $OldEnd != $Item->Duration) {
									$timing[1]=$Item->Start;
									if($Item->Duration) {
										$timing[2]=addMinutes($Item->Start, $Item->Duration);
										$timing[3] = sprintf('%02d:%02d', $Item->Duration/60, $Item->Duration%60);
									}
									if($Item->Shift) $timing[0] = '+'.$Item->Shift;
									$OldStart=$Item->Start;
									$OldEnd=$Item->Duration;
								}

								$lnk=strip_tags($Item->Text);
								if(!$Item->Warmup) {
									// not warmup!
									$OldComment='';
									switch($Item->Type) {
										case 'Q':
										case 'E':
											if($OldDate==$Date and $OldTime==$Time) $this->Ods->currentRow--;
											$lnk='';
											if($Item->Comments) {
												$row=array('', '', '', '', htmlspecialchars(strip_tags($Item->Comments)));
												if($Item->Shift and $timing[1]) {
													$row[0]=$timing[0];
												}
												$row[1]=$timing[1];
												$row[2]=$timing[2];
												$row[3]=$timing[3];
												$timing[3]='';
												$timing[2]='';
												$timing[1]='';
												$this->Ods->setCellStyle('Duration', null, 3);
												$this->Ods->setCellStyle('Comments', null, 4);
												$this->Ods->addRow($row);
												$IsTitle=false;
											}

											if(count($this->Groups[$Item->Type][$Session])==1) {
												$txt=$Item->Text.$lnk;
											} elseif($Item==@end(end(end(end($this->Groups[$Item->Type][$Session]))))) {
												$txt=$Item->DistanceName.$lnk;
											} else {
												$txt=$Item->DistanceName;
												// more distances defined so format is different...
											}
											$row=array('', '', '', '', htmlspecialchars(strip_tags($txt)));
											if($Item->Shift and $timing[1]) {
												$row[0]=$timing[0];
											}
											$row[1]=$timing[1];
											$row[2]=$timing[2];
											$row[3]=$timing[3];
											$timing[3]='';
											$timing[2]='';
											$timing[1]='';
											$this->Ods->setCellStyle('Duration', null, 3);
											$this->Ods->addRow($row);
											$IsTitle=false;

											if($Item->Type=='Q' and empty($Done[$Date][$Time][$Item->Type])) {
												$Done[$Date][$Time][$Item->Type]=true;
												if($Item->Target) {
													// USES THIS ONE!!!
													$rows=array();
													$MaxTgt=0;
													foreach(explode(',', $Item->Target) as $Block) {
														$tmp= explode('@', $Block);
														$Range=$tmp[0];
														$Dist=$tmp[1];
														if(!empty($tmp[2])) $Event=$tmp[2];
														if(!empty($tmp[3])) $Target=$tmp[3];

														if(empty($ColorAssignment["{$Dist}-{$Event}"])) {
															$ColorAssignment["{$Dist}-{$Event}"]='Color'.$ColorIndex;
															$this->Ods->setStyle('Color'.$ColorIndex, array('style:table-cell-properties' => array('fo:background-color' => $ColorArray[$ColorIndex])));
															$ColorIndex++;
														}

														$tmp=explode('-', $Range);
														if(count($tmp)>1) {
															foreach(range($tmp[0], $tmp[1]) as $tgt) {
																$rows[$tgt]['d']=$Dist;
																$rows[$tgt]['e']=$Event;
																$rows[$tgt]['c']=$ColorAssignment["{$Dist}-{$Event}"];
																$MaxTgt=max($MaxTgt, $tgt);
															}
														} else {
															$rows[$tmp[0]]['d']=$Dist;
															$rows[$tmp[0]]['e']=$Event;
															$rows[$tmp[0]]['c']=$ColorAssignment["{$Dist}-{$Event}"];
															$MaxTgt=max($MaxTgt, $tmp[0]);
														}
													}

													$tgts=array();
													$oldDistance=0;
													$grp=0;
													ksort($rows);

													foreach($rows as $tgt => $def) {
														if($oldDistance!="{$def['d']}-{$def['e']}") $grp++;
														$oldDistance="{$def['d']}-{$def['e']}";
														$tgts[$grp]['distance']=$def['d'];
														$tgts[$grp]['targets'][]=$tgt;
													}

													$this->Ods->currentRow-=2;

													foreach($tgts as $k=>$grp) {
														$this->Ods->currentCell=$grp['targets'][0]+6;
														$this->Ods->setCellStyle('Distance');
														$this->Ods->setCellAttribute('table:number-columns-spanned', 1+end($grp['targets'])-$grp['targets'][0]);
														$this->Ods->Cell($grp['distance'], 'string');
														foreach($grp['targets'] as $tgt) {
															$this->Ods->setCellStyle($rows[$tgt]['c'], $this->Ods->currentRow+1, $tgt+6);
														}
														$this->Ods->Cell($rows[$tgt]['e'], 'string', $this->Ods->currentRow+1, $grp['targets'][0]+6, true);
														// 												$this->Ods->Cell(1+end($grp['targets'])-$grp['targets'][0], 'string', $this->Ods->currentRow+1, end($grp['targets'])+6, true);
													}
													$OldRow=$this->Ods->currentRow+3;
													$this->Ods->currentRow=2;
													$this->Ods->currentCell=7;
													foreach(range(1, $MaxTgt) as $tgt) $this->Ods->Cell($tgt);
													$this->Ods->currentCell=0;
													$this->Ods->currentRow=$OldRow;

												} else {
													// Get which session and distance is shot at this time...
													$Sql="select * from DistanceInformation where DiTournament={$this->TourId} and DiDay='$Date' and DiStart='$Time'";
													$t=safe_r_sql($Sql);
													if(safe_num_rows(($t))) {
														$this->Ods->currentRow-=($Item->Comments ? 2 : 1);
													}
													$MaxTgt=0;
													while($u=safe_fetch($t)) {
														$Sql="select distinct cast(substr(QuTargetNo,2) as unsigned) TargetNo, IFNULL(Td{$u->DiDistance},'.{$u->DiDistance}.') as Distance, TarDescr, TarDim, DiDay, DiStart, DiWarmStart from
															Entries
															inner join Qualifications on EnId=QuId
															inner join DistanceInformation on QuSession=DiSession and DiTournament={$this->TourId} and DiDistance={$u->DiDistance} and DiDay='$Date' and DiStart='$Time'
															left join TournamentDistances on concat(trim(EnDivision),trim(EnClass)) like TdClasses and EnTournament=TdTournament
															left join (select TfId, TarDescr, TfW{$u->DiDistance} as TarDim, TfTournament from TargetFaces inner join Targets on TfT{$u->DiDistance}=TarId) tf on TfTournament=EnTournament and TfId=EnTargetFace
															where EnTournament={$this->TourId}
															order by TargetNo, Distance desc, TargetNo, TarDescr, TarDim";
														$v=safe_r_sql($Sql);
														$tgts=array();
														$oldDistance=0;
														$grp=0;
														while($w=safe_fetch($v)) {
															$MaxTgt=max($MaxTgt, $w->TargetNo);
															if($oldDistance!=$w->Distance) $grp++;
															$oldDistance=$w->Distance;
															// table:number-columns-spanned
															$tgts[$grp]['distance']=$w->Distance;
															$tgts[$grp]['targets'][]=$w->TargetNo;
														}
														foreach($tgts as $k=>$grp) {
															if(empty($ColorAssignment[$grp['distance']])) {
																$ColorAssignment[$grp['distance']]='Color'.$ColorIndex;
																$this->Ods->setStyle('Color'.$ColorIndex, array('style:table-cell-properties' => array('fo:background-color' => $ColorArray[$ColorIndex])));
																$ColorIndex++;
															}
															$this->Ods->currentCell=$grp['targets'][0]+6;
															$this->Ods->setCellStyle('Distance');
															$this->Ods->setCellAttribute('table:number-columns-spanned', 1+end($grp['targets'])-$grp['targets'][0]);
															$this->Ods->Cell($grp['distance'], 'string');
															foreach($grp['targets'] as $tgt) {
																$this->Ods->setCellStyle($ColorAssignment[$grp['distance']], $this->Ods->currentRow+1, $tgt+6);
															}
															$this->Ods->Cell('1', 'string', $this->Ods->currentRow+1, $grp['targets'][0]+6, true);
															$this->Ods->Cell(1+end($grp['targets'])-$grp['targets'][0], 'string', $this->Ods->currentRow+1, end($grp['targets'])+6, true);
														}
													}
													$OldRow=$this->Ods->currentRow+1;
													$this->Ods->currentRow=2;
													$this->Ods->currentCell=7;
													foreach(range(1, $MaxTgt) as $tgt) $this->Ods->Cell($tgt);
													$this->Ods->currentCell=0;
													$this->Ods->currentRow=$OldRow;

												}
											}
											break;
										case 'I':
										case 'T':
											if($OldDate==$Date and $OldTime==$Time) {
												$this->Ods->currentRow--;
											}
											$lnk=$Item->Text.': '.$Item->Events;
											$row=array('', '', '', '', htmlspecialchars(strip_tags($lnk)));
											if($Item->Shift and $timing[1]) {
												$row[0]=$timing[0];
											}
											$row[1]=$timing[1];
											$row[2]=$timing[2];
											$row[3]=$timing[3];
											$timing[3]='';
											$timing[2]='';
											$timing[1]='';
											$this->Ods->setCellStyle('Duration', null, 3);
											$this->Ods->addRow($row);
											$IsTitle=false;
											if($this->Finalists) { // && $Item->Session<=1) {
												// Bronze or Gold Finals
												if($Item->Type=='I') {
													$SQL="select ind1.IndRank LeftRank, ind2.IndRank RightRank, concat(upper(e1.EnFirstname), ' ', e1.EnName, ' (', c1.CoCode, ')') LeftSide,
														concat('(', c2.CoCode, ') ', upper(e2.EnFirstname), ' ', e2.EnName) RightSide
														from Finals tf1
														inner join Finals tf2 on tf1.FinEvent=tf2.FinEvent and tf1.FinTournament=tf2.FinTournament and tf2.FinMatchNo=tf1.FinMatchNo+1 and tf2.FinMatchNo%2=1
														inner join FinSchedule fs1 on tf1.FinTournament=fs1.FsTournament and tf1.FinEvent=fs1.FsEvent and tf1.FinMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=0 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
														inner join FinSchedule fs2 on tf2.FinTournament=fs2.FsTournament and tf2.FinEvent=fs2.FsEvent and tf2.FinMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=0 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
														inner join Entries e1 on e1.EnId=tf1.FinAthlete and tf1.FinEvent IN ('$Item->Event')
														inner join Entries e2 on e2.EnId=tf2.FinAthlete and tf2.FinEvent IN ('$Item->Event')
														inner join Individuals ind1 on e1.EnId=ind1.IndId and tf1.FinEvent=ind1.IndEvent
														inner join Individuals ind2 on e2.EnId=ind2.IndId and tf2.FinEvent=ind2.IndEvent
														inner join Countries c1 on e1.EnCountry=c1.CoId and c1.CoTournament=$this->TourId
														inner join Countries c2 on e2.EnCountry=c2.CoId and c2.CoTournament=$this->TourId
														inner join Grids on tf1.FinMatchNo=GrMatchNo and GrPhase=$Item->Session
														where tf1.FinTournament=$this->TourId";
												} else {
													$SQL="select ind1.TeRank LeftRank, ind2.TeRank RightRank, concat(c1.CoName, ' (', c1.CoCode, ')') LeftSide,
														concat('(', c2.CoCode, ') ', c2.CoName) RightSide
														from TeamFinals tf1
														inner join TeamFinals tf2 on tf1.TfEvent=tf2.TfEvent and tf1.TfTournament=tf2.TfTournament and tf2.TfMatchNo=tf1.TfMatchNo+1 and tf2.TfMatchNo%2=1
														inner join FinSchedule fs1 on tf1.TfTournament=fs1.FsTournament and tf1.TfEvent=fs1.FsEvent and tf1.TfMatchNo=fs1.FsMatchNo and fs1.FsTeamEvent=1 and fs1.FsScheduledDate='$Date' and fs1.FsScheduledTime='$Time'
														inner join FinSchedule fs2 on tf2.TfTournament=fs2.FsTournament and tf2.TfEvent=fs2.FsEvent and tf2.TfMatchNo=fs2.FsMatchNo and fs2.FsTeamEvent=1 and fs2.FsScheduledDate='$Date' and fs2.FsScheduledTime='$Time'
														inner join Countries c1 on c1.CoId=tf1.TfTeam and tf1.TfEvent IN ('$Item->Event')
														inner join Countries c2 on c2.CoId=tf2.TfTeam and tf2.TfEvent IN ('$Item->Event')
														inner join Teams ind1 on c1.CoId=ind1.TeCoId and tf1.TfEvent=ind1.TeEvent and tf1.TfSubTeam=ind1.TeSubTeam and ind1.TeFinEvent=1
														inner join Teams ind2 on c2.CoId=ind2.TeCoId and tf2.TfEvent=ind2.TeEvent and tf2.TfSubTeam=ind2.TeSubTeam and ind2.TeFinEvent=1
														inner join Grids on tf1.TfMatchNo=GrMatchNo and GrPhase=$Item->Session
														where tf1.TfTournament=$this->TourId";
												}
												$q=safe_r_SQL($SQL);
												if(safe_num_rows($q)==1 and $r=safe_fetch($q) and trim($r->LeftSide) and trim($r->RightSide)) {
													$this->Ods->addCell(htmlspecialchars(strip_tags(($this->Ranking ? '#'.$r->LeftRank.' ' : '') . $r->LeftSide.' - '.$r->RightSide . ($this->Ranking ? ' #'.$r->RightRank : ''))));
												}
											}

											if(empty($Done[$Date][$Time][$Item->Type])) {
												$Done[$Date][$Time][$Item->Type]=true;
												$MaxTgt=0;
												$rows=array();

												// get the warmup targets first (will be overwritten by the real matches...
												$MyQuery = "SELECT FwEvent ,
														FwTargets,
														FwOptions,
														UNIX_TIMESTAMP(FwDay) as SchDate,
														DATE_FORMAT(FwTime,'" . get_text('TimeFmt') . "') as SchTime,
														FwDay,
														FwTime, EvDistance
													FROM FinWarmup
													INNER JOIN Events ON FwEvent=EvCode AND FwTeamEvent=EvTeamEvent AND FwTournament=EvTournament
													WHERE FwTournament={$this->TourId}
														AND date_format(FwDay, '%Y-%m-%d')='$Date' and FwTime='$Time'
														and FwTargets!=''
													ORDER BY FwTargets";
												$t = safe_r_sql($MyQuery);
												while($u=safe_fetch($t)) {
													foreach(explode(',', $u->FwTargets) as $range) {
														$tmp=explode('-', $range);
														if(count($tmp)>1) {
															foreach(range($tmp[0], $tmp[1]) as $tgt) {
																$rows[$tgt]['d']=$u->EvDistance;
																$rows[$tgt]['e']=$u->FwEvent;
																$rows[$tgt]['c']='ColorWarmup';
																$rows[$tgt]['w']='1';
																$MaxTgt=max($MaxTgt, $tgt);
															}
														} else {
															$rows[$tmp[0]]['d']=$u->EvDistance;
															$rows[$tmp[0]]['e']=$u->FwEvent;
															$rows[$tmp[0]]['c']='ColorWarmup';
															$rows[$tmp[0]]['w']='1';
															$MaxTgt=max($MaxTgt, $tmp[0]);
														}
													}
												}

												// Now get the targets with the matches
												$MyQuery = "SELECT '' as Warmup, FSEvent, FSTeamEvent, GrPhase, FsMatchNo, FsTarget, '' as TargetTo, EvMatchArrowsNo, EvMatchMode, EvMixedTeam, EvTeamEvent, UNIX_TIMESTAMP(FSScheduledDate) as SchDate, DATE_FORMAT(FSScheduledTime,'" . get_text('TimeFmt') . "') as SchTime, EvFinalFirstPhase,
														@bit:=if(GrPhase=0, 1, pow(2, ceil(log2(GrPhase))+1)) & EvMatchArrowsNo,
														IF(@bit=0,EvFinEnds,EvElimEnds) AS `ends`,
														IF(@bit=0,EvFinArrows,EvElimArrows) AS `arrows`,
														IF(@bit=0,EvFinSO,EvElimSO) AS `so`,
														EvMaxTeamPerson,
														FSScheduledDate,
														FSScheduledTime, EvDistance
													FROM FinSchedule
													INNER JOIN Grids ON FSMatchNo=GrMatchNo
													INNER JOIN Events ON FSEvent=EvCode AND FSTeamEvent=EvTeamEvent AND FSTournament=EvTournament
													inner join Phases on PhId=EvFinalFirstPhase and (PhIndTeam & pow(2, EvTeamEvent))>0
													WHERE FSTournament=$this->TourId
														AND FSScheduledDate='$Date' and FSScheduledTime='$Time'
														and FsTarget!=''
														AND GrPhase<=greatest(PhId, PhLevel)
													ORDER BY Warmup ASC, FSTarget ASC, FSMatchNo ASC";
												$MaxTgt=0;
												$tgts=array();
												$oldDistance=0;
												$grp=0;
												$t = safe_r_sql($MyQuery);
												while($u=safe_fetch($t)) {
													if(empty($ColorAssignment["{$u->EvDistance}-{$u->FSEvent}"])) {
														$ColorAssignment["{$u->EvDistance}-{$u->FSEvent}"]='Color'.$ColorIndex;
														$this->Ods->setStyle('Color'.$ColorIndex, array('style:table-cell-properties' => array('fo:background-color' => $ColorArray[$ColorIndex])));
														$ColorIndex++;
													}
													$u->FsTarget=intval($u->FsTarget);
													$rows[$u->FsTarget]['d']=$u->EvDistance;
													$rows[$u->FsTarget]['e']=$u->FSEvent;
													$rows[$u->FsTarget]['c']=$ColorAssignment["{$u->EvDistance}-{$u->FSEvent}"];
													$rows[$u->FsTarget]['w']='0';
													$MaxTgt=max($MaxTgt, $u->FsTarget);
												}

												// $rows is now containing all targets
												$tgts=array();
												$oldDistance=0;
												$grp=0;
												ksort($rows);

												foreach($rows as $tgt => $def) {
													if($oldDistance!="{$def['d']}-{$def['e']}-{$def['w']}") $grp++;
													$oldDistance="{$def['d']}-{$def['e']}-{$def['w']}";
													$tgts[$grp]['distance']=$def['d'];
													$tgts[$grp]['targets'][]=$tgt;
												}

												$this->Ods->currentRow-=2;

												foreach($tgts as $k=>$grp) {
													$this->Ods->currentCell=$grp['targets'][0]+6;
													$this->Ods->setCellStyle('Distance');
													$this->Ods->setCellAttribute('table:number-columns-spanned', 1+end($grp['targets'])-$grp['targets'][0]);
													$this->Ods->Cell($grp['distance'], 'string');
													foreach($grp['targets'] as $tgt) {
														$this->Ods->setCellStyle($rows[$tgt]['c'], $this->Ods->currentRow+1, $tgt+6);
													}
													$this->Ods->Cell($rows[$tgt]['e'], 'string', $this->Ods->currentRow+1, $grp['targets'][0]+6, true);
												}
												$OldRow=$this->Ods->currentRow+3;
												$this->Ods->currentRow=2;
												$this->Ods->currentCell=7;
												foreach(range(1, $MaxTgt) as $tgt) $this->Ods->Cell($tgt);
												$this->Ods->currentCell=0;
												$this->Ods->currentRow=$OldRow;

											}
											break;
										default:
// 											debug_svela($Item);
									}

								} else {
									if($Item->Comments) {
										$lnk=$Item->Comments;
									} else {
										switch($Item->Type) {
											case 'I':
											case 'T':
												$lnk=$Item->Text.': '.$Item->Events.' '.'warmup';
												break;
											default:
                                                $lnk = get_text("WarmUp", "Tournament") . ' ' . $lnk;
										}
									}
									if($OldDate==$Date and $OldTime==$Time) $this->Ods->currentRow--;
									if($OldComment==$lnk) continue;
									$OldComment=$lnk;
									$row=array('', '', '', '', htmlspecialchars(strip_tags($lnk)));
									if($Item->Shift and $timing[1]) {
										$row[0]=$timing[0];
									}
									$row[1]=$timing[1];
									$row[2]=$timing[2];
									$row[3]=$timing[3];
									$this->Ods->setCellStyle('Duration', null, 3);
									$this->Ods->setCellStyle('Comments', null, 4);
									$this->Ods->addRow($row);
									$IsTitle=false;

									if(empty($Done[$Date][$Time][$Item->Type])) {
										$Done[$Date][$Time][$Item->Type]=true;
										$MaxTgt=0;
										$rows=array();
										switch($Item->Type) {
											case 'Q':
												break;
											case 'I':
											case 'T':

												// get the warmup targets first (will be overwritten by the real matches...
												$MyQuery = "SELECT FwEvent ,
														FwTargets,
														FwOptions,
														UNIX_TIMESTAMP(FwDay) as SchDate,
														DATE_FORMAT(FwTime,'" . get_text('TimeFmt') . "') as SchTime,
														FwDay,
														FwTime, EvDistance
													FROM FinWarmup
													INNER JOIN Events ON FwEvent=EvCode AND FwTeamEvent=EvTeamEvent AND FwTournament=EvTournament
													WHERE FwTournament=$this->TourId
														AND date_format(FwDay, '%Y-%m-%d')='$Date' and FwTime='$Time'
														and FwTargets!=''
														ORDER BY FwTargets";
												$t = safe_r_sql($MyQuery);
												while($u=safe_fetch($t)) {
													foreach(explode(',', $u->FwTargets) as $range) {
														$tmp=explode('-', $range);
														if(count($tmp)>1) {
															foreach(range($tmp[0], $tmp[1]) as $tgt) {
																$rows[$tgt]['d']=$u->EvDistance;
																$rows[$tgt]['e']=$u->FwEvent;
																$rows[$tgt]['c']='ColorWarmup';
																$MaxTgt=max($MaxTgt, $tgt);
															}
														} else {
															$rows[$tmp[0]]['d']=$u->EvDistance;
															$rows[$tmp[0]]['e']=$u->FwEvent;
															$rows[$tmp[0]]['c']='ColorWarmup';
															$MaxTgt=max($MaxTgt, $tmp[0]);
														}
													}
												}

												break;
										}
										$tgts=array();
										$oldDistance=0;
										$grp=0;
										ksort($rows);

										foreach($rows as $tgt => $def) {
											if($oldDistance!="{$def['d']}-{$def['e']}") $grp++;
											$oldDistance="{$def['d']}-{$def['e']}";
											$tgts[$grp]['distance']=$def['d'];
											$tgts[$grp]['targets'][]=$tgt;
										}

										$this->Ods->currentRow-=2;

										foreach($tgts as $k=>$grp) {
											$this->Ods->currentCell=$grp['targets'][0]+6;
											$this->Ods->setCellStyle('Distance');
											$this->Ods->setCellAttribute('table:number-columns-spanned', 1+end($grp['targets'])-$grp['targets'][0]);
											$this->Ods->Cell($grp['distance'], 'string');
											foreach($grp['targets'] as $tgt) {
												$this->Ods->setCellStyle($rows[$tgt]['c'], $this->Ods->currentRow+1, $tgt+6);
											}
											$this->Ods->Cell($rows[$tgt]['e'], 'string', $this->Ods->currentRow+1, $grp['targets'][0]+6, true);
											// 												$this->Ods->Cell(1+end($grp['targets'])-$grp['targets'][0], 'string', $this->Ods->currentRow+1, end($grp['targets'])+6, true);
										}
										$OldRow=$this->Ods->currentRow+3;
										$this->Ods->currentRow=2;
										$this->Ods->currentCell=7;
										foreach(range(1, $MaxTgt) as $tgt) $this->Ods->Cell($tgt);
										$this->Ods->currentCell=0;
										$this->Ods->currentRow=$OldRow;
									}
								}
							}
							$OldTime=$Time;
							$OldDate=$Date;
						}
					}
				}
			}
		}

		$this->Ods->save($filename, 'a');
		die();
	}

	function FOP($Output=true) {

		$terne=array(
			array(0,255,0),
			array(255,153,255),
			array(255,255,204),
			array(153,153,255),
			array(255,153,0),
			array(204,255,204),
// 			array(204,0,255),
			array(51,204,204),
			//array(255,51,51),
			//array(255,0,51),
			//array(0,255,204),
		);

		// seed a lot of colors (Macolin rules!
		foreach($terne as $col) {
			$ColorArray[] = array($col[0],$col[1],$col[2]);
		}
		foreach($terne as $col) {
			$ColorArray[] = array($col[1],$col[2],$col[0]);
		}
		foreach($terne as $col) {
			$ColorArray[] = array($col[2],$col[0],$col[1]);
		}
		foreach($terne as $col) {
			$ColorArray[] = array($col[0],$col[2],$col[1]);
		}
		foreach($terne as $col) {
			$ColorArray[] = array($col[1],$col[0],$col[2]);
		}
		foreach($terne as $col) {
			$ColorArray[] = array($col[2],$col[1],$col[0]);
		}

		$ColorAssignment = array();
		$MaxColor=count($ColorArray);
		$ColorIndex=0;
		$OldSession = '';
		$OldDist = '';
		$OldTarget = '';
		$TmpColor=array(255,255,255);
		$SecondaryDistance=0;
		$TgText='';
		$TgFirst=0;
		$TgNo=0;
		$TgTop=0;
		$DistanceMin=999;
		$DistanceMax=0;

		$FirstTarget=0;


		// BUILDS AN ARRAY WITH ALL TARGETS DAY BY DAY
		$FOP=array();
		$Done=array();


		foreach($this->GetSchedule() as $Date => $Times) {
			$FOP[$Date]=array('min'=>0, 'max'=>0, 'times'=>array());
			ksort($Times);

			foreach($Times as $Time => $Sessions) {
				foreach($Sessions as $Session => $Distances) {
					foreach($Distances as $Distance => $Items) {
						foreach($Items as $k => $Item) {
							if($Item->Type=='Z') {
								// if no FOP item skip
								if(!$Item->Target) continue;
								if(empty($FOP[$Date]['times'][$Time])) {
									$FOP[$Date]['times'][$Time]=array('time'=>'', 'text'=>array(), 'targets'=>array(), 'min'=>0, 'max'=>0);
								}
								// attach global info
								if(empty($FOP[$Date]['times'][$Time]['time'])) {
									$FOP[$Date]['times'][$Time]['time']=$Item->Start;
									if($Item->Duration) {
										$FOP[$Date]['times'][$Time]['time'] .= '-'.addMinutes($Item->Start, $Item->Duration);
									}
								}
								$tmp=array_merge(explode(' - ', $Item->Title), explode(' - ', $Item->SubTitle), explode(' - ', $Item->Text));
								foreach($tmp as $txt) {
									if($txt and !in_array($txt, $FOP[$Date]['times'][$Time]['text'])) {
										$FOP[$Date]['times'][$Time]['text'][]=strip_tags($txt);
									}
								}

								foreach(explode(',', $Item->Target) as $Block) {

									$tmp= explode('@', $Block);
									$bl=new TargetButt();
									$Range=$tmp[0];


									$bl->Distance=$tmp[1];
									$DistanceMin=min($DistanceMin, $tmp[1]);
									$DistanceMax=max($DistanceMax, $tmp[1]);
									if(!empty($tmp[2])) $bl->Event=$tmp[2];
									if(!empty($tmp[3])) $bl->Target=$tmp[3];

									// we need to rearrange the blocks depending ono the intersections of the selected Locations
									$Ranges=array();
									if(empty($this->LocationsToPrint)) {
										$tmp=explode('-', $Range);
										$Ranges[]=$tmp;
									} else {
										$tmp=explode('-', $Range);

										foreach($this->LocationsToPrint as $i => $k) {
											if(count($tmp)>1) {
												if($k->Tg1 <= $tmp[1] and $k->Tg2 >= $tmp[0]) {
													// portion is inside the printed area
													$Ranges[]=array(max($tmp[0], $k->Tg1), min($tmp[1], $k->Tg2));
												}
											} elseif($tmp[0]>=$k->Tg1 and $tmp[0]<=$k->Tg2) {
												$Ranges[]=$tmp;
											}
										}
									}

									foreach($Ranges as $tmp) {
										if(!$FOP[$Date]['times'][$Time]['min']) {
											$FOP[$Date]['times'][$Time]['min']=$tmp[0];
										}
										if(!$FOP[$Date]['min']) {
											$FOP[$Date]['min']=$tmp[0];
										}
										$FOP[$Date]['times'][$Time]['min']=min($FOP[$Date]['times'][$Time]['min'], $tmp[0]);
										$FOP[$Date]['min']=min($FOP[$Date]['min'], $tmp[0]);
										if(count($tmp)>1) {
											$bl->Range=array($tmp[0], $tmp[1]);
											$FOP[$Date]['times'][$Time]['max']=max($FOP[$Date]['times'][$Time]['max'], $tmp[1]);
											$FOP[$Date]['max']=max($FOP[$Date]['max'], $tmp[1]);
										} else {
											$bl->Range=array($tmp[0],$tmp[0]);
											$FOP[$Date]['times'][$Time]['max']=max($FOP[$Date]['times'][$Time]['max'], $tmp[0]);
											$FOP[$Date]['max']=max($FOP[$Date]['max'], $tmp[0]);
										}

										if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
											$FOP[$Date]['times'][$Time]['targets'][]=$bl;
										}
									}
								}
							} else {
								// No free text, so targets are (should be) assigned
								if(empty($FOP[$Date]['times'][$Time])) {
									$FOP[$Date]['times'][$Time]=array('time'=>'', 'text'=>array(), 'targets'=>array(), 'min'=>0, 'max'=>0);
								}

								if(empty($FOP[$Date]['times'][$Time]['time'])) {
									$FOP[$Date]['times'][$Time]['time']=$Item->Start;
									if($Item->Duration) {
										$FOP[$Date]['times'][$Time]['time'] .= '-'.addMinutes($Item->Start, $Item->Duration);
									}
								}
								$OldComment='';
								if(!$Item->Warmup) {
									// not warmup!
									switch($Item->Type) {
										case 'Q':
										case 'E':
											$tmp=preg_replace('/\([^)]+\)/sim', '', $Item->Title.' - '.$Item->SubTitle.' - '.$Item->Text);
											foreach(preg_split('/( - )|(, )/', $tmp) as $txt) {
												if($txt and !in_array($txt, $FOP[$Date]['times'][$Time]['text'])) {
													$FOP[$Date]['times'][$Time]['text'][]=strip_tags($txt);
												}
											}

// 											if($Item->Comments and !in_array($Item->Comments, $FOP[$Date]['times'][$Time]['text'])) {
// 												$FOP[$Date]['times'][$Time]['text'][]=strip_tags($Item->Comments);
// 											}

											if($Item->Type=='Q' and empty($Done[$Date][$Time][$Item->Type])) {
// 												$Done[$Date][$Time][$Item->Type]=true;
												if($Item->Target) {
													// USES THIS ONE!!!
													foreach(explode(',', $Item->Target) as $Block) {
														$tmp= explode('@', $Block);
														$bl=new TargetButt();
														$Range=$tmp[0];
														$bl->Distance=$tmp[1];
														$DistanceMin=min($DistanceMin, $tmp[1]);
														$DistanceMax=max($DistanceMax, $tmp[1]);
														if(!empty($tmp[2])) $bl->Event=$tmp[2];
														if(!empty($tmp[3])) $bl->Target=$tmp[3];

														if(empty($ColorAssignment["{$bl->Distance}-{$bl->Event}"])) {
															$ColorAssignment["{$bl->Distance}-{$bl->Event}"]=$ColorArray[$ColorIndex];
															$ColorIndex++;
														}
														$bl->Colour=$ColorAssignment["{$bl->Distance}-{$bl->Event}"];

														// we need to rearrange the blocks depending ono the intersections of the selected Locations
														$Ranges=array();
														if(empty($this->LocationsToPrint)) {
															$Ranges[]=explode('-', $Range);
														} else {
															$tmp=explode('-', $Range);

															foreach($this->LocationsToPrint as $i => $k) {
																if(count($tmp)>1) {
																	if($k->Tg1 <= $tmp[1] and $k->Tg2 >= $tmp[0]) {
																		// portion is inside the printed area
																		$Ranges[]=array(max($tmp[0], $k->Tg1), min($tmp[1], $k->Tg2));
																	}
																} elseif($tmp[0]>=$k->Tg1 and $tmp[0]<=$k->Tg2) {
																	$Ranges[]=$tmp;
																}
															}
														}

														foreach($Ranges as $tmp) {
															if(!$FOP[$Date]['times'][$Time]['min']) {
																$FOP[$Date]['times'][$Time]['min']=$tmp[0];
															}
															if(!$FOP[$Date]['min']) {
																$FOP[$Date]['min']=$tmp[0];
															}
															$FOP[$Date]['times'][$Time]['min']=min($FOP[$Date]['times'][$Time]['min'], $tmp[0]);
															$FOP[$Date]['min']=min($FOP[$Date]['min'], $tmp[0]);
															if(count($tmp)>1) {
																$bl->Range=array($tmp[0], $tmp[1]);
																$FOP[$Date]['times'][$Time]['max']=max($FOP[$Date]['times'][$Time]['max'], $tmp[1]);
																$FOP[$Date]['max']=max($FOP[$Date]['max'], $tmp[1]);
															} else {
																$bl->Range=array($tmp[0],$tmp[0]);
																$FOP[$Date]['times'][$Time]['max']=max($FOP[$Date]['times'][$Time]['max'], $tmp[0]);
																$FOP[$Date]['max']=max($FOP[$Date]['max'], $tmp[0]);
															}

															if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
																$FOP[$Date]['times'][$Time]['targets'][]=$bl;
															}
														}
													}
												} else {
													// Get which session and distance is shot at this time...
													$Sql="select * from DistanceInformation where DiTournament={$this->TourId} and DiDay='$Date' and DiStart='$Time'";
													$t=safe_r_sql($Sql);
													while($u=safe_fetch($t)) {
														$Sql="select distinct SesAth4Target, cast(substr(QuTargetNo,2) as unsigned) TargetNo, IFNULL(Td{$u->DiDistance},'.{$u->DiDistance}.') as Distance, TarDescr, TfName, TarDim, DiDay, DiStart, DiWarmStart from
															Entries
															inner join Qualifications on EnId=QuId
															inner join DistanceInformation on QuSession=DiSession and DiTournament={$this->TourId} and DiDistance={$u->DiDistance} and DiDay='$Date' and DiStart='$Time'
															inner join Session on SesOrder=QuSession and SesType='{$Item->Type}' and SesTournament={$this->TourId}
															left join TournamentDistances on concat(trim(EnDivision),trim(EnClass)) like TdClasses and EnTournament=TdTournament
															left join (select TfId, TarDescr, TfW{$u->DiDistance} as TarDim, TfName, TfTournament from TargetFaces inner join Targets on TfT{$u->DiDistance}=TarId) tf on TfTournament=EnTournament and TfId=EnTargetFace
															where EnTournament={$this->TourId}
															".($this->TargetsInvolved ? ' HAVING '.sprintf($this->TargetsInvolved, 'TargetNo') : '')."
															order by TargetNo, Distance desc, TargetNo, TarDescr, TarDim";
														$v=safe_r_sql($Sql);
														$k="";
														$first=true;
														while($w=safe_fetch($v)) {
															if(empty($bl) or $k!="{$w->TarDescr} {$w->TarDim} {$w->Distance}") {
																if($k) {
																	if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
																		$FOP[$Date]['times'][$Time]['targets'][]=$bl;
																	}
																}

																$bl=new TargetButt();
																$bl->Target=$w->TfName;
																$bl->Distance=$w->Distance;
																$DistanceMin=min($DistanceMin, $w->Distance);
																$DistanceMax=max($DistanceMax, $w->Distance);
																$bl->Event=get_text($Item->Type.'-Session', 'Tournament');
																$bl->ArcTarget=$w->SesAth4Target;
																$bl->Range=array($w->TargetNo, $w->TargetNo);
																if(empty($ColorAssignment["{$w->TarDescr} {$w->TarDim}"])) {
																	$ColorAssignment["{$w->TarDescr} {$w->TarDim}"]=$ColorArray[$ColorIndex];
																	$ColorIndex++;
																}
																$bl->Colour=$ColorAssignment["{$w->TarDescr} {$w->TarDim}"];

																if(!$FOP[$Date]['times'][$Time]['min']) $FOP[$Date]['times'][$Time]['min']=$w->TargetNo;
																if(!$FOP[$Date]['min']) $FOP[$Date]['min']=$w->TargetNo;
															} elseif($w->TargetNo == $bl->Range[1]+1) {
																// sequence is OK
																$bl->Range[1]=$w->TargetNo;
															} else {
																// starts another block because there is a "hole" in the target sequence
																if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
																	$FOP[$Date]['times'][$Time]['targets'][]=$bl;
																}
																$bl=new TargetButt();
																$bl->Target=get_text($w->TarDescr)." $w->TarDim cm";
																$bl->Distance=$w->Distance;
																$DistanceMin=min($DistanceMin, $w->Distance);
																$DistanceMax=max($DistanceMax, $w->Distance);
																$bl->Event=get_text($Item->Type.'-Session', 'Tournament');
																$bl->ArcTarget=$w->SesAth4Target;
																$bl->Range=array($w->TargetNo, $w->TargetNo);
																$bl->Colour=$ColorAssignment["{$w->TarDescr} {$w->TarDim}"];
															}
															$FOP[$Date]['times'][$Time]['min']=min($FOP[$Date]['times'][$Time]['min'], $w->TargetNo);
															$FOP[$Date]['min']=min($FOP[$Date]['min'], $w->TargetNo);
															$FOP[$Date]['times'][$Time]['max']=max($FOP[$Date]['times'][$Time]['max'], $w->TargetNo);
															$FOP[$Date]['max']=max($FOP[$Date]['max'], $w->TargetNo);

															$k="{$w->TarDescr} {$w->TarDim} {$w->Distance}";
														}
														if($k) {
															if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
																$FOP[$Date]['times'][$Time]['targets'][]=$bl;
															}
														}
													}
												}
											}
											break;
										case 'I':
										case 'T':
										case 'R':
											if($Item->Title and !in_array($Item->Title, $FOP[$Date]['times'][$Time]['text'])) {
												$FOP[$Date]['times'][$Time]['text'][]=strip_tags($Item->Title);
											}
// 											if($Item->Comments and !in_array($Item->Comments, $FOP[$Date]['times'][$Time]['text'])) {
// 												$FOP[$Date]['times'][$Time]['text'][]=strip_tags($Item->Comments);
// 											}

// 											$FOP[$Date]['times'][$Time]['text'][array_search($Item->Text, $FOP[$Date]['times'][$Time]['text'])].=': '.$Item->Events;

											if(true or empty($Done[$Date][$Time][$Item->Type])) {
												$Done[$Date][$Time][$Item->Type]=true;
												$rows=array();

												// get the warmup targets first (will be overwritten by the real matches)...
												$MyQuery = "SELECT FwEvent ,
														FwTargets,
														FwOptions,
														UNIX_TIMESTAMP(FwDay) as SchDate,
														DATE_FORMAT(FwTime,'" . get_text('TimeFmt') . "') as SchTime,
														FwDay,
														FwTime, EvDistance, TarDescr, EvTargetSize, FsEvent,
														EvMaxTeamPerson, group_concat(distinct if(instr('ABCD', right(FsLetter,1))>0, right(FsLetter,1), '') order by right(FsLetter,1) separator '') as Persons
													FROM FinWarmup
													INNER JOIN Events ON FwEvent=EvCode AND FwTeamEvent=EvTeamEvent AND FwTournament=EvTournament
													left join Targets on EvFinalTargetType=TarId
													left join FinSchedule on FwTeamEvent=FsTeamEvent and FwEvent=FsEvent and FsTournament=FwTournament and FsScheduledDate='$Date' and FsScheduledTime='$Time'
													WHERE FwTournament=" . StrSafe_DB($this->TourId) . "
														AND FwDay='$Date' and FwTime='$Time'
														and FwTargets!=''
													GROUP BY FwEvent
													ORDER BY FwTargets, FwEvent";
												$t = safe_r_sql($MyQuery);
												while($u=safe_fetch($t)) {
													foreach(explode(',', $u->FwTargets) as $range) {
														$Ranges=array();
														if($this->LocationsToPrint) {
															$tmp=explode('-', $range);

															foreach($this->LocationsToPrint as $i => $k) {
																if(count($tmp)>1) {
																	if($k->Tg1 <= $tmp[1] and $k->Tg2 >= $tmp[0]) {
																		// portion is inside the printed area
																		$Ranges[]=array(max($tmp[0], $k->Tg1), min($tmp[1], $k->Tg2));
																	}
																} elseif($tmp[0]>=$k->Tg1 and $tmp[0]<=$k->Tg2) {
																	$Ranges[]=$tmp;
																}
															}
														} else {
															$Ranges[]=explode('-', $range);
														}

														foreach($Ranges as $tmp) {
															if(count($tmp)>1) {
                                                                if(!$u->Persons and (substr($tmp[1],-2)=='AB' or substr($tmp[1],-2)=='CD')) {
                                                                    $u->Persons=substr($tmp[1],-2);
                                                                }
																foreach(range($tmp[0], $tmp[1]) as $tgt) {
																	$DistanceMin=min($DistanceMin, $u->EvDistance);
																	$DistanceMax=max($DistanceMax, $u->EvDistance);

																	$rows[$u->FwEvent][$tgt]['d']=$u->EvDistance;
																	$rows[$u->FwEvent][$tgt]['e']=$u->FwEvent;
																	$rows[$u->FwEvent][$tgt]['w']=1;
																	$rows[$u->FwEvent][$tgt]['ph']=($u->FwOptions ? $u->FwOptions : ($u->FsEvent ? get_text('Bye') : get_text('WarmUp', 'Tournament')));
																	$rows[$u->FwEvent][$tgt]['f']=get_text($u->TarDescr)." $u->EvTargetSize cm";
																	$rows[$u->FwEvent][$tgt]['p']=$u->Persons;
																	$rows[$u->FwEvent][$tgt]['mp']=$u->EvMaxTeamPerson;
																}
															} else {
                                                                if(!$u->Persons and (substr($tmp[0],-2)=='AB' or substr($tmp[0],-2)=='CD')) {
                                                                    $u->Persons=substr($tmp[0],-2);
                                                                }
																$DistanceMin=min($DistanceMin, $u->EvDistance);
																$DistanceMax=max($DistanceMax, $u->EvDistance);

																$rows[$u->FwEvent][$tmp[0]]['d']=$u->EvDistance;
																$rows[$u->FwEvent][$tmp[0]]['e']=$u->FwEvent;
																$rows[$u->FwEvent][$tmp[0]]['w']=1;
																$rows[$u->FwEvent][$tmp[0]]['ph']=($u->FwOptions ? $u->FwOptions : ($u->FsEvent ? get_text('Bye') : get_text('WarmUp', 'Tournament')));
																$rows[$u->FwEvent][$tmp[0]]['f']=get_text($u->TarDescr)." $u->EvTargetSize cm";
																$rows[$u->FwEvent][$tmp[0]]['p']=$u->Persons;
																$rows[$u->FwEvent][$tmp[0]]['mp']=$u->EvMaxTeamPerson;
															}
														}
													}
												}

												// Now get the targets with the matches
												if($Item->Type=='R') {
													$TimeFormat=get_text('TimeFmt');
													$MyQuery = "SELECT '' as Warmup, RrMatchEvent as FSEvent, RrMatchTeam as FSTeamEvent, concat_ws('-', RrMatchLevel, RrMatchGroup, RrMatchRound) as GrPhase, RrMatchMatchNo as FsMatchNo, RrMatchTarget as FsTarget, '' as TargetTo, RrLevArrows as EvMatchArrowsNo, RrLevMatchMode as EvMatchMode, EvMixedTeam, EvTeamEvent, UNIX_TIMESTAMP(RrMatchScheduledDate) as SchDate, DATE_FORMAT(RrMatchScheduledTime,'$TimeFormat') as SchTime, EvFinalFirstPhase,
															RrLevEnds AS `ends`,
															RrLevArrows AS `arrows`,
															RrLevSO AS `so`,
															EvMaxTeamPerson, group_concat(distinct if(instr('ABCD', right(RrMatchTarget,1))>0, right(RrMatchTarget,1), '') order by right(RrMatchTarget,1) separator '') as Persons,
															RrMatchScheduledDate as FSScheduledDate,
															RrMatchScheduledTime as FSScheduledTime, EvDistance, TarDescr, EvTargetSize,
															EvWinnerFinalRank
														FROM RoundRobinMatches
														INNER JOIN RoundRobinLevel ON RrLevTournament=RrMatchTournament AND RrLevTeam=RrMatchTeam and RrLevEvent=RrMatchEvent and RrLevLevel=RrMatchLevel
														INNER JOIN Events ON EvCode=RrMatchEvent AND EvTeamEvent=RrMatchTeam AND EvTournament=RrMatchTournament
														left join Targets on EvFinalTargetType=TarId
														WHERE RrMatchTournament=$this->TourId
															AND RrMatchScheduledDate='$Date' and RrMatchScheduledTime='$Time'
															and RrMatchTarget!=''
															group by RrMatchEvent, RrMatchTarget+0
														".($this->TargetsInvolved ? ' HAVING '.sprintf($this->TargetsInvolved, 'FsTarget+0') : '')."
															ORDER BY Warmup ASC, RrMatchEvent, RrMatchTarget ASC, RrMatchMatchNo ASC";
												} else {
													$MyQuery = "SELECT '' as Warmup, FSEvent, FSTeamEvent, GrPhase, FsMatchNo, FsTarget, '' as TargetTo, EvMatchArrowsNo, EvMatchMode, EvMixedTeam, EvTeamEvent, UNIX_TIMESTAMP(FSScheduledDate) as SchDate, DATE_FORMAT(FSScheduledTime,'" . get_text('TimeFmt') . "') as SchTime, EvFinalFirstPhase,
															@bit:=if(GrPhase=0, 1, pow(2, ceil(log2(GrPhase))+1)) & EvMatchArrowsNo,
															IF(@bit=0,EvFinEnds,EvElimEnds) AS `ends`,
															IF(@bit=0,EvFinArrows,EvElimArrows) AS `arrows`,
															IF(@bit=0,EvFinSO,EvElimSO) AS `so`,
															EvMaxTeamPerson, group_concat(distinct if(instr('ABCD', right(FsLetter,1))>0, right(FsLetter,1), '') order by right(FsLetter,1) separator '') as Persons,
															FSScheduledDate,
															FSScheduledTime, EvDistance, TarDescr, EvTargetSize,
															EvWinnerFinalRank
														FROM FinSchedule
														INNER JOIN Grids ON FSMatchNo=GrMatchNo
														INNER JOIN Events ON FSEvent=EvCode AND FSTeamEvent=EvTeamEvent AND FSTournament=EvTournament
														inner join Phases on EvFinalFirstPhase in (PhId, PhLevel) and (PhIndTeam & pow(2, EvTeamEvent))>0 and PhRuleSets in ('', '{$_SESSION['TourLocRule']}')
														left join Targets on EvFinalTargetType=TarId
														WHERE FSTournament=" . StrSafe_DB($this->TourId) . "
															AND FSScheduledDate='$Date' and FSScheduledTime='$Time'
															and FsTarget!=''
															AND GrPhase<=greatest(ifnull(PhId,0), ifnull(PhLevel,0), EvFinalFirstPhase)
															group by FsEvent, FsTarget, GrPhase
														".($this->TargetsInvolved ? ' HAVING '.sprintf($this->TargetsInvolved, 'FsTarget+0') : '')."
															ORDER BY Warmup ASC, FSTarget ASC, FsEvent, FSMatchNo ASC";
												}
                                               // print_r($MyQuery);
												$t = safe_r_sql($MyQuery);
												while($u=safe_fetch($t)) {
													$EndsArrows=get_text('EventDetailsShort', 'Tournament', array($u->ends, $u->arrows));
													if(!in_array($EndsArrows, $FOP[$Date]['times'][$Time]['text'])) {
														$FOP[$Date]['times'][$Time]['text'][]=$EndsArrows;
													}
													if(empty($ColorAssignment["{$u->EvDistance}-{$u->FSEvent}"])) {
														if(!isset($ColorArray[$ColorIndex])) {
															$ColorArray[$ColorIndex]=$ColorArray[$ColorIndex%$MaxColor];
														}
														$ColorAssignment["{$u->EvDistance}-{$u->FSEvent}"]=$ColorArray[$ColorIndex];
														$ColorIndex++;
													}
/*													if($u->EvFinalFirstPhase==24 or $u->EvFinalFirstPhase==48) {
														if($u->GrPhase==32) $u->GrPhase=24;
														elseif($u->GrPhase==64) $u->GrPhase=48;
													}
*/
													$u->FsTarget=intval($u->FsTarget);
													$DistanceMin=min($DistanceMin, $u->EvDistance);
													$DistanceMax=max($DistanceMax, $u->EvDistance);

													$rows[$u->FSEvent][$u->FsTarget]['d']=$u->EvDistance;
													$rows[$u->FSEvent][$u->FsTarget]['e']=$u->FSEvent;
													$rows[$u->FSEvent][$u->FsTarget]['c']=$ColorAssignment["{$u->EvDistance}-{$u->FSEvent}"];
													$rows[$u->FSEvent][$u->FsTarget]['f']=get_text($u->TarDescr)." $u->EvTargetSize cm";
													$rows[$u->FSEvent][$u->FsTarget]['p']=$u->Persons;
													$rows[$u->FSEvent][$u->FsTarget]['mp']=$u->EvMaxTeamPerson;
													$rows[$u->FSEvent][$u->FsTarget]['w']=0;
													if($Item->Type=='R') {
														$rows[$u->FSEvent][$u->FsTarget]['ph']='';
													} else {
														if($u->GrPhase==0) {
															$rows[$u->FSEvent][$u->FsTarget]['ph']=$u->EvWinnerFinalRank==1 ? get_text('0_Phase') : ($u->EvWinnerFinalRank) . ' vs ' . ($u->EvWinnerFinalRank+1);
														} elseif($u->GrPhase==1) {
															$rows[$u->FSEvent][$u->FsTarget]['ph']=$u->EvWinnerFinalRank==1 ? get_text('1_Phase') : ($u->EvWinnerFinalRank+2) . ' vs ' . ($u->EvWinnerFinalRank+3);
														} else {
															$rows[$u->FSEvent][$u->FsTarget]['ph']=get_text(namePhase($u->EvFinalFirstPhase, $u->GrPhase) . '_Phase');
														}
													}
												}

												$k='';
												foreach($rows as $Events => $tgts) {
													// $rows is now containing all targets
													ksort($tgts);
													foreach($tgts as $tgt => $def) {
														if(empty($bl) or $k!="{$def['d']}-{$def['e']}-{$def['w']}-{$def['ph']}") {
															if($k) {
																if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
																	$FOP[$Date]['times'][$Time]['targets'][]=$bl;
																}
															}

															$bl=new TargetButt();
															$bl->Target=$def['f'];
															$bl->Event=$def['e'];
															$bl->Distance=$def['d'];
															$bl->Line=0;
															$DistanceMin=min($DistanceMin, $def['d']);
															$DistanceMax=max($DistanceMax, $def['d']);

															$bl->Range=array($tgt, $tgt);
															if(!empty($def['c'])) $bl->Colour=$def['c'];
															if(empty($def['p'])) {
																$bl->ArcTarget=$def['mp'];
															} else {
																$bl->ArcTarget=strlen($def['p']);
																if(strlen($def['p'])<4 and strstr($def['p'],'C')) {
																	$bl->Line=1;
																}
															}
															if(!empty($def['ph'])) $bl->Phase=$def['ph'];

															if(!$FOP[$Date]['times'][$Time]['min']) $FOP[$Date]['times'][$Time]['min']=$tgt;
															if(!$FOP[$Date]['min']) $FOP[$Date]['min']=$tgt;
														} elseif($tgt == $bl->Range[1]+1) {
															// sequence is OK
															$bl->Range[1]=$tgt;
														} else {
															// starts another block because there is a "hole" in the target sequence
															if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
																$FOP[$Date]['times'][$Time]['targets'][]=$bl;
															}
															$bl=new TargetButt();
															$bl->Target=$def['f'];
															$bl->Event=$def['e'];
															$bl->Distance=$def['d'];
															$bl->Line=0;
															$DistanceMin=min($DistanceMin, $def['d']);
															$DistanceMax=max($DistanceMax, $def['d']);

															$bl->Range=array($tgt, $tgt);
															if(!empty($def['c'])) $bl->Colour=$def['c'];
															if(empty($def['p'])) {
																$bl->ArcTarget=$def['mp'];
															} else {
																$bl->ArcTarget=strlen($def['p']);
																if(strlen($def['p'])<4 and strstr($def['p'],'C')) {
																	$bl->Line=1;
																}
															}
															if(!empty($def['ph'])) $bl->Phase=$def['ph'];
														}
														$FOP[$Date]['times'][$Time]['min']=min($FOP[$Date]['times'][$Time]['min'], $tgt);
														$FOP[$Date]['min']=min($FOP[$Date]['min'], $tgt);
														$FOP[$Date]['times'][$Time]['max']=max($FOP[$Date]['times'][$Time]['max'], $tgt);
														$FOP[$Date]['max']=max($FOP[$Date]['max'], $tgt);

														$k="{$def['d']}-{$def['e']}-{$def['w']}-{$def['ph']}";
													}
													if($k) {
														if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
															$FOP[$Date]['times'][$Time]['targets'][]=$bl;
														}
													}
												}
											}
											break;
										default:
// 											debug_svela($Item);
									}

								} else {
									if($Item->Comments) {
										$lnk=$Item->Comments;
										if(!in_array($Item->Comments, $FOP[$Date]['times'][$Time]['text'])) {
											$FOP[$Date]['times'][$Time]['text'][]=strip_tags($Item->Comments);
										}
									} else {
										switch($Item->Type) {
											case 'I':
											case 'T':
												$lnk=$Item->Text.': '.$Item->Events.' '.get_text('WarmUp', 'Tournament');
												break;
											default:
												$lnk=' '.get_text('WarmUp', 'Tournament');
										}
										if(!in_array($lnk, $FOP[$Date]['times'][$Time]['text'])) {
											$FOP[$Date]['times'][$Time]['text'][]=strip_tags($lnk);
										}
									}

									$IsTitle=false;

									if(empty($Done[$Date][$Time][$Item->Type])) {
// 										$Done[$Date][$Time][$Item->Type]=true;
										$MaxTgt=0;
										$rows=array();
										switch($Item->Type) {
											case 'Q':
												if($Item->Target) {
													// USES THIS ONE!!!
													foreach(explode(',', $Item->Target) as $Block) {
														$tmp= explode('@', $Block);
														$bl=new TargetButt();
														$Range=$tmp[0];
														$bl->Distance=$tmp[1];
														$DistanceMin=min($DistanceMin, $tmp[1]);
														$DistanceMax=max($DistanceMax, $tmp[1]);

														if(!empty($tmp[2])) $bl->Event=$tmp[2];
														if(!empty($tmp[3])) $bl->Target=$tmp[3];

														// we need to rearrange the blocks depending ono the intersections of the selected Locations
														$Ranges=array();
														if(empty($this->LocationsToPrint)) {
															$tmp=explode('-', $Range);
															$Ranges[]=$tmp;
														} else {
															$tmp=explode('-', $Range);

															foreach($this->LocationsToPrint as $i => $k) {
																if(count($tmp)>1) {
																	if($k->Tg1 <= $tmp[1] and $k->Tg2 >= $tmp[0]) {
																		// portion is inside the printed area
																		$Ranges[]=array(max($tmp[0], $k->Tg1), min($tmp[1], $k->Tg2));
																	}
																} elseif($tmp[0]>=$k->Tg1 and $tmp[0]<=$k->Tg2) {
																	$Ranges[]=$tmp;
																}
															}
														}

														foreach($Ranges as $tmp) {
															if(!$FOP[$Date]['times'][$Time]['min']) $FOP[$Date]['times'][$Time]['min']=$tmp[0];
															if(!$FOP[$Date]['min']) $FOP[$Date]['min']=$tmp[0];
															$FOP[$Date]['times'][$Time]['min']=min($FOP[$Date]['times'][$Time]['min'], $tmp[0]);
															$FOP[$Date]['min']=min($FOP[$Date]['min'], $tmp[0]);
															if(count($tmp)>1) {
																$bl->Range=array($tmp[0], $tmp[1]);
																$FOP[$Date]['times'][$Time]['max']=max($FOP[$Date]['times'][$Time]['max'], $tmp[1]);
																$FOP[$Date]['max']=max($FOP[$Date]['max'], $tmp[1]);
															} else {
																$bl->Range=array($tmp[0],$tmp[0]);
																$FOP[$Date]['times'][$Time]['max']=max($FOP[$Date]['times'][$Time]['max'], $tmp[0]);
																$FOP[$Date]['max']=max($FOP[$Date]['max'], $tmp[0]);
															}

															if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
																$FOP[$Date]['times'][$Time]['targets'][]=$bl;
															}
														}

													}
												} else {
													$Sql="select * from DistanceInformation where DiTournament={$this->TourId} and DiDay='$Date' and DiWarmStart='$Time'";
													$t=safe_r_sql($Sql);
													while($u=safe_fetch($t)) {
														$Sql="select distinct SesAth4Target, cast(substr(QuTargetNo,2) as unsigned) TargetNo, IFNULL(Td{$u->DiDistance},'.{$u->DiDistance}.') as Distance, TarDescr, TarDim, DiDay, DiStart, DiWarmStart from
															Entries
															inner join Qualifications on EnId=QuId
															inner join DistanceInformation on QuSession=DiSession and DiTournament={$this->TourId} and DiDistance={$u->DiDistance} and DiDay='$Date' and DiWarmStart='$Time'
															inner join Session on SesOrder=QuSession and SesType='{$Item->Type}' and SesTournament={$this->TourId}
															left join TournamentDistances on concat(trim(EnDivision),trim(EnClass)) like TdClasses and EnTournament=TdTournament
															left join (select TfId, TarDescr, TfW{$u->DiDistance} as TarDim, TfTournament from TargetFaces inner join Targets on TfT{$u->DiDistance}=TarId) tf on TfTournament=EnTournament and TfId=EnTargetFace
															where EnTournament={$this->TourId}
															order by TargetNo, Distance desc, TargetNo, TarDescr, TarDim";
														$v=safe_r_sql($Sql);
														$k="";
														$first=true;
														while($w=safe_fetch($v)) {
															if(empty($bl) or $k!="{$w->TarDescr} {$w->TarDim} {$w->Distance}") {
																if($k) {
																	if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
																		$FOP[$Date]['times'][$Time]['targets'][]=$bl;
																	}
																}

																$bl=new TargetButt();
																$bl->Target=get_text($w->TarDescr)." $w->TarDim cm";
																$bl->Distance=$w->Distance;
																$DistanceMin=min($DistanceMin, $w->Distance);
																$DistanceMax=max($DistanceMax, $w->Distance);

																$bl->Event=get_text('WarmUp', 'Tournament');
																$bl->ArcTarget=$w->SesAth4Target;
																$bl->Range=array($w->TargetNo, $w->TargetNo);

																if(!$FOP[$Date]['times'][$Time]['min']) $FOP[$Date]['times'][$Time]['min']=$w->TargetNo;
																if(!$FOP[$Date]['min']) $FOP[$Date]['min']=$w->TargetNo;
															} elseif($w->TargetNo == $bl->Range[1]+1) {
																// sequence is OK
																$bl->Range[1]=$w->TargetNo;
															} else {
																// starts another block because there is a "hole" in the target sequence
																if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
																	$FOP[$Date]['times'][$Time]['targets'][]=$bl;
																}
																$bl=new TargetButt();
																$bl->Target=get_text($w->TarDescr)." $w->TarDim cm";
																$bl->Distance=$w->Distance;
																$DistanceMin=min($DistanceMin, $w->Distance);
																$DistanceMax=max($DistanceMax, $w->Distance);

																$bl->Event=get_text('WarmUp', 'Tournament');
																$bl->ArcTarget=$w->SesAth4Target;
																$bl->Range=array($w->TargetNo, $w->TargetNo);
															}
															$FOP[$Date]['times'][$Time]['min']=min($FOP[$Date]['times'][$Time]['min'], $w->TargetNo);
															$FOP[$Date]['min']=min($FOP[$Date]['min'], $w->TargetNo);
															$FOP[$Date]['times'][$Time]['max']=max($FOP[$Date]['times'][$Time]['max'], $w->TargetNo);
															$FOP[$Date]['max']=max($FOP[$Date]['max'], $w->TargetNo);

															$k="{$w->TarDescr} {$w->TarDim} {$w->Distance}";
														}
														if($k) {
															if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
																$FOP[$Date]['times'][$Time]['targets'][]=$bl;
															}
														}
													}
												}
												break;
											case 'I':
											case 'T':

												// get the warmup targets first (will be overwritten by the real matches...
												$MyQuery = "SELECT FwEvent ,
														FwTargets,
														FwOptions,
														UNIX_TIMESTAMP(FwDay) as SchDate,
														DATE_FORMAT(FwTime,'" . get_text('TimeFmt') . "') as SchTime,
														FwDay,
														FwTime, EvDistance, TarDescr, EvTargetSize, EvMaxTeamPerson
													FROM FinWarmup
													INNER JOIN Events ON FwEvent=EvCode AND FwTeamEvent=EvTeamEvent AND FwTournament=EvTournament
													left join Targets on EvFinalTargetType=TarId
													WHERE FwTournament=" . StrSafe_DB($this->TourId) . "
															AND date_format(FwDay, '%Y-%m-%d')='$Date' and FwTime='$Time'
															and FwTargets!=''
															ORDER BY FwTargets, FwEvent";
												$t = safe_r_sql($MyQuery);

												$RowTgts=array();
												while($u=safe_fetch($t)) {
													foreach(explode(',', $u->FwTargets) as $range) {
														$Ranges=array();
														if($this->LocationsToPrint) {
															$tmp=explode('-', $range);

															foreach($this->LocationsToPrint as $i => $k) {
																if(count($tmp)>1) {
																	if($k->Tg1 <= $tmp[1] and $k->Tg2 >= $tmp[0]) {
																		// portion is inside the printed area
																		$Ranges[]=array(max($tmp[0], $k->Tg1), min($tmp[1], $k->Tg2));
																	}
																} elseif($tmp[0]>=$k->Tg1 and $tmp[0]<=$k->Tg2) {
																	$Ranges[]=$tmp;
																}
															}
														} else {
															$Ranges[]=explode('-', $range);
														}

														foreach($Ranges as $tmp) {
															if(count($tmp)>1) {
																foreach(range($tmp[0], $tmp[1]) as $tgt) {
																	$DistanceMin=min($DistanceMin, $u->EvDistance);
																	$DistanceMax=max($DistanceMax, $u->EvDistance);

																	$rows[$u->FwEvent][$tgt]['d']=$u->EvDistance;
																	$rows[$u->FwEvent][$tgt]['e']=$u->FwEvent;
																	$rows[$u->FwEvent][$tgt]['f']=get_text($u->TarDescr)." $u->EvTargetSize cm";
																	$rows[$u->FwEvent][$tgt]['ph']=get_text('WarmUp', 'Tournament');
																	$rows[$u->FwEvent][$tgt]['mp']=$u->EvMaxTeamPerson;
																	if(empty($RowTgts[$tgt])) {
																		$rows[$u->FwEvent][$tgt]['l']=0;
																	} else {
																		$rows[$u->FwEvent][$tgt]['l']=1;
																	}
																	$RowTgts[$tgt]=1;
																}
															} else {
																$DistanceMin=min($DistanceMin, $u->EvDistance);
																$DistanceMax=max($DistanceMax, $u->EvDistance);

																$rows[$u->FwEvent][$tmp[0]]['d']=$u->EvDistance;
																$rows[$u->FwEvent][$tmp[0]]['e']=$u->FwEvent;
																$rows[$u->FwEvent][$tmp[0]]['f']=get_text($u->TarDescr)." $u->EvTargetSize cm";
																$rows[$u->FwEvent][$tmp[0]]['ph']=get_text('WarmUp', 'Tournament');
																$rows[$u->FwEvent][$tmp[0]]['mp']=$u->EvMaxTeamPerson;
																if(empty($RowTgts[$tmp[0]])) {
																	$rows[$u->FwEvent][$tmp[0]]['l']=0;
																} else {
																	$rows[$u->FwEvent][$tmp[0]]['l']=1;
																}
																$RowTgts[$tmp[0]]=1;
															}
														}
													}
												}


												$k='';
												foreach($rows as $Events => $tgts) {
													ksort($tgts);
													foreach($tgts as $tgt => $def) {
														if(empty($bl) or $k!="{$def['d']}-{$def['e']}") {
															if($k) {
																if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
																	$FOP[$Date]['times'][$Time]['targets'][]=$bl;
																}
															}

															$bl=new TargetButt();
															$bl->Target=$def['f'];
															$bl->Event=$def['e'];
															$bl->Distance=$def['d'];
															$DistanceMin=min($DistanceMin, $def['d']);
															$DistanceMax=max($DistanceMax, $def['d']);

															$bl->Range=array($tgt, $tgt);
															if(!empty($def['c'])) $bl->Colour=$def['c'];
															if(!empty($def['ph'])) $bl->Phase=$def['ph'];
															if(!empty($def['l'])) $bl->Line=$def['l'];

															if(!$FOP[$Date]['times'][$Time]['min']) $FOP[$Date]['times'][$Time]['min']=$tgt;
															if(!$FOP[$Date]['min']) $FOP[$Date]['min']=$tgt;
														} elseif($tgt == $bl->Range[1]+1) {
															// sequence is OK
															$bl->Range[1]=$tgt;
														} else {
															// starts another block because there is a "hole" in the target sequence
															if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
																$FOP[$Date]['times'][$Time]['targets'][]=$bl;
															}
															$bl=new TargetButt();
															$bl->Target=$def['f'];
															$bl->Event=$def['e'];
															$bl->Distance=$def['d'];
															$DistanceMin=min($DistanceMin, $def['d']);
															$DistanceMax=max($DistanceMax, $def['d']);

															$bl->Range=array($tgt, $tgt);
															if(!empty($def['c'])) $bl->Colour=$def['c'];
															if(!empty($def['ph'])) $bl->Phase=$def['ph'];
															if(!empty($def['l'])) $bl->Line=$def['l'];
														}
														$FOP[$Date]['times'][$Time]['min']=min($FOP[$Date]['times'][$Time]['min'], $tgt);
														$FOP[$Date]['min']=min($FOP[$Date]['min'], $tgt);
														$FOP[$Date]['times'][$Time]['max']=max($FOP[$Date]['times'][$Time]['max'], $tgt);
														$FOP[$Date]['max']=max($FOP[$Date]['max'], $tgt);

														$k="{$def['d']}-{$def['e']}";
													}
													if($k) {
														if(!in_array($bl, $FOP[$Date]['times'][$Time]['targets'])) {
															$FOP[$Date]['times'][$Time]['targets'][]=$bl;
														}
													}
												}
												break;
										}
									}
								}
							}
							$OldTime=$Time;
							$OldDate=$Date;
						}
					}
				}
			}
		}

		// Starts the real job...
		include_once('Common/pdf/ResultPDF.inc.php');

		//error_reporting(E_ALL);

		$FirstPage=true;
		$DistHeight=4;
		$TgtHeight=3;
		$EventHeight=4;
		$PhaseHeight=4;
		$TgtFaceHeight=3;
		$ArcTgtHeight=2;
		$TimeHeight=6;
		$TimeWidth=20;

        $orientation = isset($_REQUEST["P"]) ? "P" : "L";

		foreach($FOP as $Day => $Blocks) {
			if(!$Blocks['min'] and !$Blocks['max']) continue;
			$TwoColumns=false;
			if($FirstPage) {
                $pdf = new ResultPDF(get_text('FopSetup'), $orientation === "P");
				$pdf->Version=$this->FopVersion;
				$pdf->SetCellPadding(0.1);
				$pdf->SetFillColor(200);
				$pdf->SetTextColor(0);
// 				$pdf->SetAutoPageBreak(false);
			} else {
				$pdf->AddPage($orientation);
			}
			$FirstPage=false;
			$FirstDate=true;

			// Title of the page is ALWAYS the date and the version
			$pdf->SetFont('', 'B', 25);
			$pdf->Cell(0, 0, formatTextDate($Day, true), 'B', 1, 'C');
			$pdf->dy(-5, true);
			$pdf->SetFontSize(7);
			$pdf->Cell(0, 0, $this->FopVersionText, '', 0, 'R');
			$pdf->SetFont('', '', 8);

			// calculates the width of the targets
			$TgtWidthOrg=min(12, ($pdf->getPageWidth()-21-$TimeWidth)/(1+$Blocks['max']-$Blocks['min']));
			$pdf->ln(6);

			$SecondColumn=0;

			if($Blocks['max']-$Blocks['min'] < 4) {
				// the "page" is split in two columns...
				$SecondColumn=20+(($pdf->getPageWidth()-30)/2);
			}

			$CurrentXOffset=0;
			$CurrentYOffset=0;
			$StartY=0;
			$MaxY=0;

			$LastBlock=end($Blocks['times']);

			foreach($Blocks['times'] as $Time => $Block) {
				if(!($CurrentXOffset%2) or !$SecondColumn) {
					if(!$pdf->SamePage(11 + $DistHeight + $TgtHeight + $EventHeight + $PhaseHeight + $TgtFaceHeight + $ArcTgtHeight)) {
						$pdf->AddPage($orientation);
						$FirstDate=true;
						$pdf->SetFont('', 'B', 16);
						$pdf->Cell(0, 0, formatTextDate($Day, true).' ('.get_text('Continue').')', 'B', 1, 'C');
						$pdf->dy(-4, true);
						$pdf->SetFontSize(7);
						$pdf->Cell(0, 0, $this->FopVersionText, '', 0, 'R');
						$pdf->SetFont('', '', 8);
						$pdf->ln(7);
						$MaxY=0;
					}
				}
				if(!$FirstDate and ($Block!=$LastBlock or !$SecondColumn)) {
					$pdf->setY($MaxY, false);
					$pdf->SetLineStyle(array('width'=>0.5, 'color' => array(128)));
					$tmp=$pdf->getMargins();
					$pdf->Line($tmp['left'], $pdf->getY(), $tmp['left'] + $pdf->getPageWidth() - $SecondColumn - 20, $pdf->getY());
					$pdf->SetLineStyle(array('width'=>.1, 'color' => array(0)));
					$pdf->ln(2);
				}
				$FirstDate=false;

				$Y=$pdf->gety();
				if($CurrentXOffset%2 and $SecondColumn) {
					$pdf->SetLeftMargin($SecondColumn);
					$pdf->sety($StartY, true);
					$Y=$pdf->gety();
				} else {
					$pdf->SetLeftMargin(10);
					$pdf->setx(10);
				}

				$CurrentXOffset++;
				$StartY=$Y;

				$pdf->SetFont('', 'B', 10);
				$pdf->Cell($TimeWidth, 0, $Block['time'], 0, 1);
				$pdf->SetFont('', '', 7);
				foreach($Block['text'] as $txt) {
                    $txt=mb_substr($txt, 0, 30, 'UTF-8');
					$pdf->Cell($TimeWidth, 3, $txt, '', 1);
				}
				$pdf->setY($Y);
				$MaxOffset=0;
				$pdf->SetFont('', '', 8);

				$TargetFacesBlocks=array();
				$CurFace='£$';

				$ArcPerTarget=array();
				$CurArcNum=-10;


// 				if((count($Block['targets'])==1 and $Block['targets'][0]->Range[1]-$Block['targets'][0]->Range[0]<3)) {
// 					$TgtWidth=2*$TgtWidthOrg;
// 					$Max=$Block['targets'][0]->Range[1];
// 					$Min=$Block['targets'][0]->Range[0];
// 				} else {
					$TgtWidth=$TgtWidthOrg;
					$Max=$Blocks['max'];
					$Min=$Blocks['min'];
// 				}

				$tmp=$pdf->getMargins();
				$pdf->setX($tmp['left']+1+$TimeWidth);
				$this->PrintTargetLinePdf($pdf, $TgtWidth, $TgtHeight, $Min, $Max);
				$pdf->ln();
				$OrgY=$pdf->GetY();

				foreach($Block['targets'] as $Range) {
					$Y=$OrgY;
					$pdf->SetFillColor($Range->Colour[0], $Range->Colour[1], $Range->Colour[2]);
					$RangeWidth=(1+$Range->Range[1]-$Range->Range[0])*$TgtWidth;
					$RangeStart=$tmp['left']+1 + $TimeWidth + $TgtWidth*($Range->Range[0]-$Blocks['min']);
					//$Offset=min(8, max(0, 14-(intval($Range->Distance)/5)));
					$Offset=min(8, max(0, ((intval($DistanceMax)-intval($DistanceMin))/5) - (intval($Range->Distance)/5)));
					$MaxOffset=max($MaxOffset, $Offset);

					if(!empty($Range->Line)) {
						$Y+=$DistHeight + $Offset + $EventHeight + ($Range->Phase ? $PhaseHeight : 0) + $ArcTgtHeight + 3.5;
					}

					// prints the distance block
					$pdf->setXY($RangeStart, $Y);
					$pdf->Cell($RangeWidth, $DistHeight + $Offset, $Range->Distance, '1', 0, 'C');
					$Y+=$DistHeight + $Offset;

					// Events on each block
					$pdf->SetFont('', 'B');
					$pdf->setXY($RangeStart, $Y);
					$pdf->Cell($RangeWidth, $EventHeight, $Range->Event, 'LTR', 0, 'C', 1);
					$pdf->SetFont('', '');
					$Y+=$EventHeight;
					$pdf->setY($Y);

					// Phase on each block
					if($Range->Phase) {
						$pdf->SetFont('', 'B');
						$pdf->setXY($RangeStart, $Y);
						$pdf->Cell($RangeWidth, $PhaseHeight, $Range->Phase, 'LBR', 0, 'C', 1);
						$pdf->SetFont('', '');
						$Y+=$PhaseHeight;
					}

					if($Range->ArcTarget and $Range->ArcTarget<=4) {
						foreach(range($Range->Range[0], $Range->Range[1]) as $tgt) {
							$colX=$tmp['left']+1 + $TimeWidth + $TgtWidth*($tgt-$Blocks['min']) ;
							$pdf->SetFillColor(255);
							$pdf->Rect($colX, $Y, $TgtWidth, $ArcTgtHeight, "DF");
							$pdf->SetFillColor(127);
                            $targetsPerFace = $Range->ArcTarget != 3 ? $Range->ArcTarget : 2;
                            $larCell=$TgtWidth/($targetsPerFace + 1) - 0.05 * $targetsPerFace;
                            $dividerWidth = ($TgtWidth - $larCell * $targetsPerFace) / ($targetsPerFace + 1);
							if($Range->ArcTarget & 4) {
								$pdf->Rect($colX + 1*$dividerWidth + 0*$larCell, $Y + 0.5, $larCell, 1, "DF");
								$pdf->Rect($colX + 2*$dividerWidth + 1*$larCell, $Y + 0.5, $larCell, 1, "DF");
								$pdf->Rect($colX + 3*$dividerWidth + 2*$larCell, $Y + 0.5, $larCell, 1, "DF");
								$pdf->Rect($colX + 4*$dividerWidth + 3*$larCell, $Y + 0.5, $larCell, 1, "DF");
							} else {
								if($Range->ArcTarget & 1) {
									$pdf->Rect($colX + 1*$dividerWidth + 0*$larCell, $Y + 0.5, $larCell, 1, "DF");
								}
								if($Range->ArcTarget & 2) {
									$pdf->Rect($colX + 1*$dividerWidth + 0*$larCell, $Y + 0.5, $larCell, 1, "DF");
									$pdf->Rect($colX + 2*$dividerWidth + 1*$larCell, $Y + 0.5, $larCell, 1, "DF");
								}
							}
						}
						$Y+=$ArcTgtHeight;
						$GetArcPerTarget=false;
					} else {
						$GetArcPerTarget=true;
					}

					// Target faces used in the block
					if($CurFace!=$Range->Target) {
						$CurFace=$Range->Target;
						$TargetFacesBlocks[$CurFace][]=array($Range->Range[0], $Range->Range[1], $Y);
						$TargetIndex=count($TargetFacesBlocks[$CurFace])-1;
						$CurArcNum=-10;
					}
					if($Range->Range[0]<$TargetFacesBlocks[$CurFace][$TargetIndex][0]) $TargetFacesBlocks[$CurFace][$TargetIndex][0]=$Range->Range[0];
					if($Range->Range[1]>$TargetFacesBlocks[$CurFace][$TargetIndex][1]) $TargetFacesBlocks[$CurFace][$TargetIndex][1]=$Range->Range[1];
					$TargetFacesBlocks[$CurFace][$TargetIndex][2]=max($Y, $TargetFacesBlocks[$CurFace][$TargetIndex][2]);
					if($GetArcPerTarget) {
						if($CurArcNum!=$Range->ArcTarget) {
							$CurArcNum=$Range->ArcTarget;
							$ArcPerTarget[$CurArcNum][]=array($Range->Range[0], $Range->Range[1], $Y);
							$ArcPerTargetIndex=count($ArcPerTarget[$CurArcNum])-1;
						}
						if($Range->Range[0]<$ArcPerTarget[$CurArcNum][$ArcPerTargetIndex][0]) $ArcPerTarget[$CurArcNum][$ArcPerTargetIndex][0]=$Range->Range[0];
						if($Range->Range[1]>$ArcPerTarget[$CurArcNum][$ArcPerTargetIndex][1]) $ArcPerTarget[$CurArcNum][$ArcPerTargetIndex][1]=$Range->Range[1];
					}
				}
				$pdf->SetFontSize(7);
				$Gap=$pdf->getY();
				if(empty($Block['targets'])) $Gap=$pdf->gety()+10;
				foreach($TargetFacesBlocks as $Targetface => $Ranges) {
					if(!$Targetface) continue;
					foreach($Ranges as $Range) {
						$RangeWidth=(1+$Range[1]-$Range[0])*$TgtWidth;
						$RangeStart=$tmp['left'] + 1 + $TimeWidth + $TgtWidth*($Range[0]-$Blocks['min']);
						$pdf->setXY($RangeStart, $Range[2]);
						$pdf->Cell($RangeWidth, $TgtFaceHeight, $Targetface, 'LR', 1, 'C');
						$Gap=max($Gap, $pdf->gety());
					}
				}
				foreach($ArcPerTarget as $Targetface => $Ranges) {
					if(!$Targetface) continue;
					foreach($Ranges as $Range) {
						$RangeWidth=(1+$Range[1]-$Range[0])*$TgtWidth;
						$RangeStart=$tmp['left'] + 1 + $TimeWidth + $TgtWidth*($Range[0]-$Blocks['min']);
						$pdf->setXY($RangeStart, $Range[2] + $TgtFaceHeight);
						$pdf->Cell($RangeWidth, $ArcTgtHeight, $Targetface . ' ' . mb_strtolower(get_text('ArchersPerTarget')), 'LR', 1, 'C');
						$Gap=max($Gap, $pdf->gety());
					}
				}
				$pdf->SetFontSize(8);
				$pdf->SetY($Gap+3, true);
				$MaxY=max($MaxY, $pdf->getY());
// 				$pdf->ln();

			}
		}
		if(empty($pdf)) {
			$pdf = new ResultPDF(get_text('FopSetup'));
		}
		if($Output) {
			$pdf->Output();
		} else {
			return $pdf;
		}
		die();
	}

	function PrintTargetLinePdf(&$pdf, $TgtWidth, $TgtHeight, $Min, $Max) {
		$pdf->SetFont('', '', 6);
		if($this->FopLocations) {
			$OldX=$pdf->getx();
			$OldY=$pdf->gety();
			foreach($this->FopLocations as $field) {
				if($field->Tg1<=$Max and $field->Tg2>=$Min) {
					$start=max($field->Tg1, $Min);
					$end=min($field->Tg2, $Max);
					$pdf->setx($OldX+($start-$Min)*$TgtWidth);
					$pdf->cell($TgtWidth*(1+$end-$start), $TgtHeight, $field->Loc, 'LR', 0, 'C');
				}
			}
			$pdf->setxy($OldX, $OldY+$TgtHeight);
		}
		foreach(range($Min, $Max) as $tgt) {
			$pdf->cell($TgtWidth, $TgtHeight, $tgt, 'LBR', 0, 'C');
		}
		$pdf->SetFont('', '', 8);
	}

	function getSessionFromActiveKey($Active=array()) {
		if(empty($Active)) {
			$Active=$this->ActiveSessions;

			$GetSessions=implode(',', StrSafe_DB($Active));

			/**
			 *
			$key=$Item->Day
			.'|'.$Time
			.'|'.$Session
			.'|'.$Distance
			.'|'.$Item->Order;

			 **/
			$SQL=array();
			// First gets the Texts: titles and description for a given time always go before everything else
			// getting them first to seed the array!
			if(!$this->SesType or strstr($this->SesType, 'Z')) {
				$SQL[]="select distinct
				'' EvShootOff,
				'' grPos,
					SchTargets `BestTarget`,
					'Z' Type,
					SchDay Day,
					'-' Session,
					'-' Distance,
					'' Medal,
					if(SchStart=0, '', date_format(SchStart, '%H:%i')) Start,
					SchDuration Duration,
					'' WarmStart,
					'' WarmDuration,
					SchSubTitle Options,
					SchTitle SesName,
					SchText Events,
					'' Event,
					'' as Locations,
					SchOrder OrderPhase,
					SchShift SchDelay,
					'' TD1, '' TD2, '' TD3, '' TD4, '' TD5, '' TD6, '' TD7, '' TD8
				from Scheduler
				where SchTournament=$this->TourId
					and SchDay>0 and SchStart>0
					and concat_ws('|', SchDay, if(SchStart=0, '', date_format(SchStart, '%H:%i')), '-', '-', SchOrder) in ($GetSessions)
					";
			}

			// Then gets the qualification rounds
			$SQL[]="select distinct
			'' EvShootOff,
			'' grPos,
				DiTargets `BestTarget`,
				DiType Type,
				DiDay Day,
				DiSession Session,
				DiDistance Distance,
				'' Medal,
				if(DiStart=0, '', date_format(DiStart, '%H:%i')) Start,
				DiDuration Duration,
				if(DiWarmStart=0, '', date_format(DiWarmStart, '%H:%i')) WarmStart,
				DiWarmDuration WarmDuration,
				DiOptions Options,
				SesName,
				'' Events,
				'' Event,
				'' as Locations,
				DiSession OrderPhase,
				DiShift SchDelay,
				TD1, TD2, TD3, TD4, TD5, TD6, TD7, TD8
			from DistanceInformation
			INNER join Session on SesTournament=DiTournament and SesOrder=DiSession and SesType=DiType and SesType='Q'
			left join (select * from TournamentDistances where TdTournament=$this->TourId group by TdTournament having count(*)=1) TD on TdTournament=SesTournament
			where DiTournament=$this->TourId
				and DiDay>0 and (DiStart>0 or DiWarmStart>0)
				and concat_ws('|', DiDay, if(DiStart=0, '', date_format(DiStart, '%H:%i')), DiSession, DiDistance, DiSession) in ($GetSessions)
			order by DiDay, DiStart, DiWarmStart, DiSession, DiDistance";

			// Then gets the Elimination rounds
			$SQL[]="select distinct
			'' EvShootOff,
			'' grPos,
				'0' `BestTarget`,
				'E' Type,
				DiDay,
				DiSession,
				DiDistance,
				'',
				if(DiStart=0, '', date_format(DiStart, '%H:%i')) DiStart,
				DiDuration,
				if(DiWarmStart=0, '', date_format(DiWarmStart, '%H:%i')) DiWarmStart,
				DiWarmDuration,
				DiOptions,
				SesName,
				Events,
				'' Event,
				'' as Locations,
				DiSession,
				DiShift SchDelay,
				'' TD1, '' TD2, '' TD3, '' TD4, '' TD5, '' TD6, '' TD7, '' TD8
			from Session
			inner join (select distinct ElSession, ElTournament, ElElimPhase, group_concat(distinct ElEventCode order by ElEventCode separator ', ') Events from Eliminations where ElTournament=$this->TourId group by ElTournament, ElSession, ElElimPhase) Phase on ElSession=SesOrder and ElTournament=SesTournament
			inner join DistanceInformation on SesTournament=DiTournament and SesOrder=DiSession and ElElimPhase=DiDistance and DiType='E'
			where DiTournament=$this->TourId
				and DiDay>0 and (DiStart>0 or DiWarmStart>0)
				and concat_ws('|', DiDay, if(DiStart=0, '', date_format(DiStart, '%H:%i')), DiSession, DiDistance, DiSession) in ($GetSessions)
			order by DiDay, DiStart, DiWarmStart, DiSession, DiDistance";

			// Get all the Free warmups
			$SQL[]="select distinct
			'' EvShootOff,
			'' grPos,
			'0' `BestTarget`,
			if(FwTeamEvent=0, 'I', 'T'),
			FwDay,
			'',
			'',
			'',
			date_format(FwTime, '%H:%i'),
			FwDuration,
			date_format(FwTime, '%H:%i') FwTime,
			FwDuration,
			FwOptions,
			'',
			if(count(*)=2, group_concat(distinct EvEventName order by EvEventName separator ', '), group_concat(distinct FwEvent order by FwEvent separator ', ')) Events,
			group_concat(distinct FwEvent order by FwEvent separator '\',\'') Event,
			'' as Locations,
			'',
			'',
				'' TD1, '' TD2, '' TD3, '' TD4, '' TD5, '' TD6, '' TD7, '' TD8

			from FinWarmup
			inner join Events on FwEvent=EvCode and EvTeamEvent=FwTeamEvent and EvTournament=FwTournament
			where FwTournament=$this->TourId
				and FwMatchTime=0
				and concat_ws('|', FwDay, date_format(FwTime, '%H:%i'), '', '', '') in ($GetSessions)
			group by FwTeamEvent, FwDay, FwTime
			";

			// Get all the matches
			// get all the named sessions
			$SQL[]="select distinct
				'' EvShootOff,
			'' grPos,
				'0' `BestTarget`,
				'Z' Type,
				date_format(SesDtStart, '%Y-%m-%d') Day,
				'-' Session,
				'-' Distance,
				'' EvMedals,
				if(SesDtStart=0, '', date_format(SesDtStart, '%H:%i')) DiStart,
				0 DiDuration,
				'' DiWarmStart,
				0,
				0,
				SesName,
				'',
				'' Event,
				'' as Locations,
				0,
				0 SchDelay,
				'' TD1, '' TD2, '' TD3, '' TD4, '' TD5, '' TD6, '' TD7, '' TD8
			from Session
			where SesTournament=$this->TourId
				and SesName!=''
				and SesDtStart>0
				and concat_ws('|', SesDtStart, if(SesDtStart=0, '', date_format(SesDtStart, '%H:%i')), 0, 0, 0) in ($GetSessions)
			order by SesDtStart";

			$SQL[]="select distinct
					EvShootOff,
					EvFinalFirstPhase=48 or EvFinalFirstPhase = 24 As grPos,
					max(FsTarget*1) as `BestTarget`,
					if(FsTeamEvent=0, 'I', 'T') Type,
					FsScheduledDate Day,
					GrPhase Session,
					EvFinalFirstPhase Distance,
					EvMedals,
					if(FsScheduledTime=0, '', date_format(FsScheduledTime, '%H:%i')) ScheduledTime,
					FsScheduledLen,
					if(FwTime=0, '', date_format(FwTime, '%H:%i')) FwTime,
					FwDuration,
					FwOptions,
					'',
					if(count(*)=2, group_concat(distinct EvEventName order by EvEventName separator ', '), group_concat(distinct FsEvent order by FsEvent separator ', ')) Events,
					group_concat(distinct FsEvent order by FsEvent separator '\',\'') Event,
					'' as Locations,
					1+(1/(1+GrPhase)),
					FsShift SchDelay,
						'' TD1, '' TD2, '' TD3, '' TD4, '' TD5, '' TD6, '' TD7, '' TD8
				from FinSchedule
				inner join Events on FsEvent=EvCode and FsTeamEvent=EvTeamEvent and FsTournament=EvTournament
				inner join Grids on FsMatchNo=GrMatchNo
				left join FinWarmup on FsEvent=FwEvent and FsTeamEvent=FwTeamEvent and FsTournament=FwTournament and FsScheduledDate=FwDay and FsScheduledTime=FwMatchTime
				where FsTournament=$this->TourId
					and FsScheduledDate>0 and (FsScheduledTime>0 or FwTime>0)
					and concat_ws('|', FsScheduledDate, if(FsScheduledTime=0, '', date_format(FsScheduledTime, '%H:%i')), GrPhase, EvFinalFirstPhase, 1+(1/(1+GrPhase))) in ($GetSessions)
				group by FsTeamEvent, FsScheduledDate, FsScheduledTime, Locations, GrPhase, FwTime
				";

			$sql='('.implode(') UNION (', $SQL).') order by Day, if(Start>0, if(WarmStart>0, least(Start, WarmStart), Start), WarmStart), Type!=\'Z\', OrderPhase, Distance, `BestTarget`=0';

			$q=safe_r_SQL($sql);
			$Ret=array();
			while($r=safe_fetch($q)) {
				$ret[]=$r;
			}

			return $ret;

		}
	}
}

function AddMinutes($Time, $Minutes) {
    $origDateTime =  new DateTime($Time);
	if($Minutes!=0) {
        $origDateTime->modify($Minutes . ' minutes');
    }
    return $origDateTime->format('H:i');
}

Class TargetButt {
	var $Target='';
	var $Range=array(0, 0);
	var $Colour=array(200, 200, 200); // Warmup colour
	var $Event='';
	var $Distance='';
	var $ArcTarget=0;
	var $Phase='';
	var $Line=0;
}
