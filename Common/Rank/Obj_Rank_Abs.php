<?php
	require_once('Common/Lib/ArrTargets.inc.php');
	require_once('Common/Rank/Obj_Rank_Abs.php');

require_once('Common/Lib/Normative/NormativeCalculator.php');

/**
 * Obj_Rank_Abs
 * Implementa l'algoritmo di default per il calcolo della rank di qualificazione assoluta individuale
 *
 * La tabella in cui vengono scritti i valori è la Individuals.
 *
 * Per questa classe $opts ha la seguente forma:
 *
 * array(
 * 		events	=> array(<ev_1>,<ev_2>...<ev_n>) || string,			[calculate/read]
 * 		dist	=> #												[calculate/read]
 * 		runningDist	=> #											[read]
 * 		tournament => #												[calculate/read]
 * 		cutRank => #												[read]
 * 		session => #												[read,non influisce su calculate]
 * 		comparedTo => #												[read]
 * 		skipExisting => #											[calculate]
 * )
 *
 * con:
 * 	 events: l'array degli eventi assoluti oppure se scalare, una stringa usata in LIKE
 * 	 dist: la distanza con 0 per indicare la rank assoluta totale totale.
 * 	 runningDist: Restituisce la classifica dopo "X" distanze a non della distanza "x" (e rimuove le impostazioni di "dist" se presenti)
 *	 tournament: Se impostato è l'id del torneo su cui operare altrimenti prende quello in sessione.
 *	 session: Se impostato ritorna la classifica di quella sessione, con la rank globale. Chi chiama se vuole ricalcolerà la rank in quella sessione
 *	 skipExisting: Se 1 non sovrascrive posizione e frecce di SO dove sono già valorizzati - Solo per Distanza = 0
 *
 * $data ha la seguente forma
 *
 * array(
 * 		meta 		=> array(
 * 			title 	=> <titolo della classifica localizzato>
 * 			numDist	=> <numero distanze>, inizializzato solo se c'è almeno una sezione
 * 			double	=> <1 se gara doppia 0 altrimenti>, inizializzato solo se c'è almeno una sezione
 * 			lastUpdate => timestamp dell'ultima modifica (il max tra tutte le righe)
 *		),
 * 		sections 	=> array(
 * 			event_1 => array(
 * 				meta => array(
 * 					event => <event_1>, valore uguale alla chiave
 * 					descr => <descrizione evento localizzata>
 * 					qualifiedNo => <numero di persone qualificate per l'evento>
 * 					printHeader => <testa stampa>
 * 					fields(*1) => array(
 *						id 				=> <id della persona>
 *                      bib 			=> <codice della persona>
 *                      session 		=> <sessione>
 *                      target 			=> <piazzola>
 *                      athlete 		=> <cognome e nome>
 *                      familyname 		=> <cognome>
 *						givenname 		=> <nome>
 *                      div				=> <codice divisione>
 *                      cl				=> <codice classe>
 *                      subclass 		=> <categoria>
 *                      countryCode 	=> <codice nazione>
 *                      countryName 	=> <nazione>
 *                      rank 			=> <rank in base alla distanza>
 *                      rankBeforeSO	=> <rank prima degli shootoff (ha senso sulla dist 0)>
 *                      score 			=> <punti in base alla distanza>
 *                      gold 			=> <ori in base alla distanza>
 *                      xnine 			=> <xnine in base alla distanza>
 *                      tiebreak		=> <frecce di tie>					(distanza 0)
 *                      ct				=> <numero di cointoss (gialli)>	(distanza 0)
 *                      so				=> <1 se shootoff (rosso)>			(distanza 0)
 *                      dist_1 			=> <rank|punti|ori|xnine della distanza 1>
 *                      dist_2 			=> <rank|punti|ori|xnine della distanza 2>
 *                      dist_3 			=> <rank|punti|ori|xnine della distanza 3>
 *                      dist_4 			=> <rank|punti|ori|xnine della distanza 4>
 *                      dist_5 			=> <rank|punti|ori|xnine della distanza 5>
 *                      dist_6 			=> <rank|punti|ori|xnine della distanza 6>
 *                      dist_7	 		=> <rank|punti|ori|xnine della distanza 7>
 *                      dist_8 			=> <rank|punti|ori|xnine della distanza 8>
 *                      hits			=> <frecce tirate (tutte se la distanza è zero oppure solo quelle della distanza passata)>
 * 					)
 *				)
 * 				items => array(
 * 					array(id=><valore>,bib=><valore>,...,dist_8=><valore>),
 * 					...
 * 				)
 * 			)
 * 			...
 * 			event_n = ...
 * 		)
 * )
 *
 * Estende Obj_Rank
 */
	class Obj_Rank_Abs extends Obj_Rank
	{
	/**
	 * safeFilter()
	 * Protegge con gli apici gli elementi di $this->opts['events']
	 *
	 * @return mixed: false se non c'è filtro oppure la stringa da inserire nella where delle query
	 */

		protected function safeFilter()
		{
			$ret=array();

			if (array_key_exists('events',$this->opts)) {
				if (is_array($this->opts['events']) && count($this->opts['events'])>0) {
					$f=array();

					foreach ($this->opts['events'] as $e) {
						$f[]=StrSafe_DB($e);
					}

					$ret[]="EvCode IN(" . implode(',',$f) . ")";
				} elseif (gettype($this->opts['events'])=='string' && trim($this->opts['events'])!='') {
					$ret[]="EvCode LIKE '" . $this->opts['events'] . "'";
				}
			}

			if($ret) return " AND " . implode(' AND ', $ret);
			return false;

		}

		public function __construct($opts)
		{
			parent::__construct($opts);
		}

	/**
	 * calculate().
	 * La classifica abs viene calcolata quando si calcola quella di classe e l'evento
	 * prevede la div/cl della persona coinvolta
	 * e quando si fanno gli spareggi per passare alle eliminatorie o alle finali.
	 * Nel primo caso questo è il metodo da chiamare perchè calcolerà l'IndRank o l'IndD[1-8]Rank lavorando su tutto l'evento
	 * (utilizza setRow()) altrimenti occorre usare setRow() direttamente.
	 *
	 * @override
	 *
	 * (non-PHPdoc)
	 * @see ianseo/Common/Rank/Obj_Rank#calculate()
	 */
		public function calculate()
		{
			return true;

		}

	/**
	 * read()
	 *
	 * @override
	 *
	 * (non-PHPdoc)
	 * @see ianseo/Common/Rank/Obj_Rank#read()
	 */
		public function read(){
			$ConfirmStatus=0;
			$dd='';
			if(!empty($this->opts['runningDist']) && $this->opts['runningDist']>0) {
				$this->opts['dist'] = 0;
			}
			if($this->opts['dist']>0) {
				$dd = 'D' . $this->opts['dist'];
				$ConfirmStatus=pow(2,$this->opts['dist']);
			}

			$f=$this->safeFilter();

			$filter="";
			if ($f!==false)
			{
				$filter=$f;
			}

			$EnFilter  = (empty($this->opts['enid']) ? '' : " AND EnId=" . intval($this->opts['enid'])) ;
			$EnFilter .= (empty($this->opts['includeAll']) ? ' and (QuHits!=0 or QuScore!=0 or IndRank!=0 or IndIrmType!=0)' : '') ;
			$EnFilter .= (empty($this->opts['coid']) ? '' : " AND EnCountry=" . intval($this->opts['coid'])) ;
			$EnFilter .= (empty($this->opts['subclass']) ? '' : " AND EnSubclass=" . StrSafe_DB($this->opts['subclass'])) ;
			$EnFilter .= (empty($this->opts['country']) ? '' : " AND CoCode=" . StrSafe_DB($this->opts['country'])) ;
			$EnFilter .= (empty($this->opts['encodeEvents']) ? '' : " AND concat_ws('|', EnCode, EvCode) IN (" . implode(',', StrSafe_DB($this->opts['encodeEvents'])) . ")");
			if (!empty($this->opts['encode'])) {
				if (is_array($this->opts['encode'])) {
					$EnFilter.=" and EnCode IN (" . implode(',',StrSafe_DB($this->opts['encode'])) . ")";
				} else {
					$EnFilter=" and EnCode = " . StrSafe_DB($this->opts['encode']) . " ";
				}
			}


			if (array_key_exists('cutRank',$this->opts)) {
				if(is_numeric($this->opts['cutRank']) && $this->opts['cutRank']>0) {
					$EnFilter.= "AND (Ind{$dd}Rank<={$this->opts['cutRank']} AND Ind{$dd}Rank!=0)";
				} elseif (strtolower($this->opts['cutRank'])=='cut') {
					$EnFilter.= "AND (Ind{$dd}Rank<=EvNumQualified AND Ind{$dd}Rank!=0)";
				}
			}

			$comparedTo=0;
			if(!empty($this->opts["comparedTo"]) && is_numeric($this->opts["comparedTo"]))
				$comparedTo=$this->opts["comparedTo"];

			if(!empty($this->opts['session'])) {
				if(is_array($this->opts['session'])) {
					$EnFilter .= " AND QuSession in (".implode(', ', $this->opts['session']).") ";
				} else {
					if($ses=intval($this->opts['session'])) {
						$EnFilter .= " AND QuSession=$ses ";
					}
				}
			}

			$tmp=null;
			if (empty($this->opts['runningDist']) || $this->opts['runningDist']>0) {
				$tmp=array();
				foreach(range(1,(empty($this->opts['runningDist']) ? 8 : $this->opts['runningDist'])) as $n)
					$tmp[]='QuD'.$n.'Hits';
				$tmp=implode('+', $tmp);
			}
			elseif($this->opts['dist'])	{
				$tmp='QuD'.$this->opts['dist'].'Hits';
			} else {
				$tmp='QuHits';
			}

			$MyRank="Ind{$dd}Rank";
			if($this->Flighted) {
				$MyRank="if(EnSubClass, QuSubClassRank, $MyRank)";
			}

			$only4zero="";
			if ($this->opts['dist']==0 && empty($this->opts['runningDist']))
				$only4zero=", IndTiebreak, IndTbClosest, IndTbDecoded, (IndSO>0) as isSO, IFNULL(sqY.Quanti,1) AS `NumCT`,ABS(IndSO) AS RankBeforeSO ";

			$q="
				SELECT ".($this->Flighted ? "concat(EvCode,EnSubClass)" : "EvCode")." as EventKey,
					EnId, EnCode, ifnull(EdExtra, EnCode) as LocalId, if(EnDob=0, '', EnDob) as BirthDate, EnOdfShortname, EnSex, EnNameOrder, upper(EnIocCode) EnIocCode, EnName AS Name, EnFirstName AS FirstName, upper(EnFirstName) AS FirstNameUpper, EnMiddleName, QuSession as Session, SesName,
					SUBSTRING(QuTargetNo,2) AS TargetNo, FlContAssoc,
					EvProgr, ToNumEnds,ToNumDist,ToMaxDistScore, FdiDetails,
					co.CoId, co.CoCode, co.CoNameComplete, co.CoMaCode, co.CoCaCode, co2.CoNameComplete as CoNameComplete2, co3.CoNameComplete as CoNameComplete3, co2.CoCode as CoCode2, co3.CoCode as CoCode3, EnClass, EnDivision,EnAgeClass,  EnSubClass,  ScDescription,
					IFNULL(Td1,'.1.') as Td1, IFNULL(Td2,'.2.') as Td2, IFNULL(Td3,'.3.') as Td3, IFNULL(Td4,'.4.') as Td4, IFNULL(Td5,'.5.') as Td5, IFNULL(Td6,'.6.') as Td6, IFNULL(Td7,'.7.') as Td7, IFNULL(Td8,'.8.') as Td8,
					QuD1Score, IndD1Rank, QuD2Score, IndD2Rank, QuD3Score, IndD3Rank, QuD4Score, IndD4Rank,
					QuD5Score, IndD5Rank, QuD6Score, IndD6Rank, QuD7Score, IndD7Rank, QuD8Score, IndD8Rank,
					QuD1Gold, QuD2Gold, QuD3Gold, QuD4Gold, QuD5Gold, QuD6Gold, QuD7Gold, QuD8Gold,
					QuD1Xnine, QuD2Xnine, QuD3Xnine, QuD4Xnine, QuD5Xnine, QuD6Xnine, QuD7Xnine, QuD8Xnine,
					QuD1Arrowstring,QuD2Arrowstring,QuD3Arrowstring,QuD4Arrowstring,QuD5Arrowstring,QuD6Arrowstring,QuD7Arrowstring,QuD8Arrowstring,
					QuScore, QuNotes, QuConfirm, QuArrow, IndNotes, (EvShootOff OR EvE1ShootOff OR EvE2ShootOff) as ShootOffSolved,
					IF(EvRunning=1,IFNULL(ROUND(QuScore/QuHits,3),0),0) as RunningScore,
					EvCode,EvEventName,EvRunning, EvFinalFirstPhase, EvElim1, EvElim2, EvIsPara, coalesce(OdfTrOdfCode,'') as OdfUnitCode, EvOdfCode,
					{$tmp} AS Arrows_Shot,
					coalesce(RoundRobinQualified, IF(EvElim1=0 && EvElim2=0, EvNumQualified ,IF(EvElim1=0,EvElim2,EvElim1))) as QualifiedNo, EvFirstQualified, EvQualPrintHead as PrintHeader,
					{$MyRank} AS `Rank`, " . (!empty($comparedTo) ? 'IFNULL(IopRank,0)' : '0') . " as OldRank, Qu{$dd}Score AS Score, Qu{$dd}Gold AS Gold,Qu{$dd}Xnine AS XNine, Qu{$dd}Hits AS Hits, 
					IndIrmType, IrmType, IrmShowRank, IrmHideDetails, ";
			$q.="IndRecordBitmap as RecBitLevel, EvIsPara, co.CoMaCode, co.CoCaCode, "; // records management

			if(!empty($this->opts['runningDist']) && $this->opts['runningDist']>0) {
				$q1='';
				$q2='';
				$q3='';
				for($i=1; $i<=$this->opts['runningDist']; $i++) {
					$q1 .= "QuD" . $i . "Score+";
					$q2 .= "QuD" . $i . "Gold+";
					$q3 .= "QuD" . $i . "XNine+";
				}
				$q .= substr($q1, 0, -1) . " AS OrderScore, ";
				$q .= substr($q2, 0, -1) . " AS OrderGold, ";
				$q .= substr($q3, 0, -1) . " AS OrderXnine, ";
			}
			else {
				$q .= "0 AS OrderScore, 0 AS OrderGold, 0 AS OrderXnine, ";
			}

			$q .= "IndTimestamp, IndRankFinal,
					IF(EvGolds!='',EvGolds,ToGolds) AS GoldLabel, IF(EvXNine!='',EvXNine,ToXNine) AS XNineLabel,
					ToDouble, DiEnds, DiArrows, if(EvGoldsChars='', ToGoldsChars, EvGoldsChars) as QualGoldChars, if(EvXNineChars='', ToXNineChars, EvXNineChars) as QualXNineChars,
					ifnull(concat(DV2.DvMajVersion, '.', DV2.DvMinVersion) ,concat(DV1.DvMajVersion, '.', DV1.DvMinVersion)) as DocVersion,
					date_format(ifnull(DV2.DvPrintDateTime, DV1.DvPrintDateTime), '%e %b %Y %H:%i UTC') as DocVersionDate,
					ifnull(DV2.DvNotes, DV1.DvNotes) as DocNotes, 0 as hasShootOff
					{$only4zero}
				FROM Tournament
				INNER JOIN Entries ON ToId=EnTournament
				INNER JOIN Individuals ON IndTournament=ToId AND IndId=EnId
				INNER JOIN Events ON EvCode=IndEvent AND EvTeamEvent=0 AND EvTournament=ToId
				left JOIN Countries co ON co.CoId=
				    case EvTeamCreationMode 
				        when 0 then EnCountry
				        when 1 then EnCountry2
				        when 2 then EnCountry3
				        else EnCountry
                    end
                    AND EnTournament=co.CoTournament AND EnTournament={$this->tournament}
				left JOIN Countries co2 ON EnCountry2=co2.CoId AND EnTournament=co2.CoTournament AND EnTournament={$this->tournament}
				left JOIN Countries co3 ON EnCountry3=co3.CoId AND EnTournament=co3.CoTournament AND EnTournament={$this->tournament}
				INNER JOIN Qualifications ON EnId=QuId
				INNER JOIN IrmTypes ON IrmId=IndIrmType
				left join Session on SesTournament=ToId and SesOrder=QuSession and SesType='Q'
				left join SubClass on ScTournament=ToId and ScId=EnSubClass
				left join (
				    select max(RrPartSourceRank) as RoundRobinQualified, RrPartTeam, RrPartEvent
				    from RoundRobinParticipants
				    where RrPartSourceLevel=0 and RrPartSourceGroup=0 and RrPartTournament={$this->tournament}
				    group by RrPartEvent, RrPartTeam
				    ) RoundRobinQualified on RrPartTeam=EvTeamEvent and RrPartEvent=EvCode and EvElimType=5
				left join ExtraData on EdId=EnId and EdType='Z'
				LEFT JOIN DocumentVersions DV1 on EvTournament=DV1.DvTournament AND DV1.DvFile = 'QUAL-IND' and DV1.DvEvent=''
				LEFT JOIN DocumentVersions DV2 on EvTournament=DV2.DvTournament AND DV2.DvFile = 'QUAL-IND' and DV2.DvEvent=EvCode
				LEFT JOIN  (SELECT OdfTrOdfCode, OdfTrIanseo 
                    FROM OdfTranslations 
                    WHERE OdfTrTournament={$this->tournament} and OdfTrInternal='QUAL' and OdfTrType='CODE') OdfUnit on OdfTrIanseo='ALL'  
				LEFT JOIN TournamentDistances ON ToType=TdType AND TdTournament=ToId AND CONCAT(TRIM(EnDivision),TRIM(EnClass)) LIKE TdClasses
				left join DistanceInformation on EnTournament=DiTournament and DiSession=1 and DiDistance=1 and DiType='Q' 
				left join (
					select DiSession as FdiSession, group_concat(concat_ws('|', DiDistance, DiEnds, DiArrows, DiScoringEnds, DiScoringOffset) order by DiDistance separator ',') as FdiDetails
					from DistanceInformation
					where DiTournament={$this->tournament} and DiType='Q'
					group by DiSession
					) FullDistanceInfo on FdiSession=QuSession ";
			if(!empty($comparedTo)) {
				$q .= "LEFT JOIN IndOldPositions ON IopId=EnId AND IopEvent=EvCode AND IopTournament=EnTournament AND IopHits=" . ($comparedTo>0 ? $comparedTo :  "(SELECT MAX(IopHits) FROM IndOldPositions WHERE IopId=EnId AND IopEvent=EvCode AND IopTournament=EnTournament AND IopHits!=QuHits) ") . " ";
			}
			$q .= "LEFT JOIN Flags ON FlIocCode='FITA' and FlCode=co.CoCode and FlTournament=ToId

					/* Contatori per CT (gialli)*/
					LEFT JOIN (
						SELECT IndEvent,Count(*) as Quanti, IndSO as sqyRank, IndTournament
						FROM Individuals 
						inner join IrmTypes on IrmId=IndIrmType and IrmShowRank=1
						INNER JOIN Events ON IndEvent=EvCode AND IndTournament=EvTournament AND EvTeamEvent=0
						WHERE IndTournament = {$this->tournament} AND IndSO!=0 {$filter}
						GROUP BY IndSO, IndEvent,IndTournament
						) AS sqY
					ON sqY.sqyRank=IndSO AND sqY.IndEvent=Individuals.IndEvent AND sqY.IndTournament=Individuals.IndTournament

				WHERE
					EnAthlete=1 AND EnIndFEvent=1 AND EnStatus <= 1  
					AND ToId = {$this->tournament}
					{$filter}
					{$EnFilter}
				ORDER BY
					EvProgr, EvCode, if(IrmShowRank=1, 0, IndIrmType), ";
			if($this->Flighted) {
				$q.="ScViewOrder, ";
			}
			if(!empty($this->opts['runningDist']) && $this->opts['runningDist']>0) {
				$q .= "OrderScore DESC, OrderGold DESC, OrderXnine DESC, FirstName, Name ";
			} else {
				$q .= "Ind{$dd}Rank=0, Ind{$dd}Rank ASC, FirstName, Name ";
			}
			$r=safe_r_sql($q);

			$this->data['meta']['title']=get_text('ResultIndAbs','Tournament');
			$this->data['meta']['distance']=$this->opts['dist'];
			$this->data['meta']['numDist']=-1;
			$this->data['meta']['double']=-1;
			$this->data['meta']['lastUpdate']='0000-00-00 00:00:00';
			$this->data['sections']=array();

			if (safe_num_rows($r)>0) {
                $curEvent='';

                $section=null;

                $oldScore=-1;
                $oldGold=-1;
                $oldXnine=-1;
                $myPos=0;
                $myRank=0;

				while ($myRow=safe_fetch($r)) {
					if ($curEvent!=$myRow->EvCode) {
					/*
					 *  se non sono all'inizio, prima di iniziare una sezione devo prendere quella appena fatta
					 *  e accodarla alle altre
					 */
						if ($curEvent!='') {
							foreach($section["meta"]["arrowsShot"] as $k => $v) {
								if($v) $section["meta"]["sesArrows"][$k] = get_text('AfterXArrows', 'Common', $v);
							}
							$this->data['sections'][$curEvent]=$section;
							$section=null;
						}

					// al cambio creo una nuova sezione
						$curEvent=$myRow->EvCode;

					// inizializzo i meta che son comuni a tutta la classifica
						if ($this->data['meta']['numDist']==-1) {
							$this->data['meta']['numDist']=$myRow->ToNumDist;
							$this->data['meta']['double']=$myRow->ToDouble;
						}

					// qui ci sono le descrizioni dei campi
						$distFields=array();
						$distValid=$myRow->ToNumDist;

						// adding the full distance info here
						$FullDistInfo=[];
                        foreach($myRow->FdiDetails != null ? explode(',',$myRow->FdiDetails) : [] as $d) {
							$t=explode('|', $d);
							$FullDistInfo['dist_' . $t[0]]=[
								'ends'=>($t[1]??0),
								'arr'=>($t[2]??0),
								'toShoot'=>($t[3]??0),
								'offset'=>($t[4]??0),
							];
						}

						foreach(range(1,8) as $n) {
							$distFields['dist_' . $n]=$myRow->{'Td' . $n};
							if($distFields['dist_' . $n]=='-') {
								$distValid--;
							}
						}
						if(!$dd) {
							$ConfirmStatus=pow(2, $distValid+1)-2;
						}

						$fields=array(
							'id'  => 'Id',
							'bib' => get_text('Code','Tournament'),
							'session' => get_text('Session'),
							'target' => get_text('Target'),
							'athlete' => get_text('Athlete'),
                            'birthdate' => get_text('DOB', 'Tournament'),
							'familyname' => get_text('FamilyName', 'Tournament'),
							'givenname' => get_text('Name', 'Tournament'),
							'gender' => get_text('Sex', 'Tournament'),
							'div' => get_text('Division'),
							'class' => get_text('Class'),
							'ageclass' => get_text('AgeCl'),
							'subclass' => get_text('SubCl','Tournament'),
							'countryId'  => 'CoId',
							'countryCode' => get_text('CountryCode'),
							'countryName' => get_text('Country'),
							'rank' => get_text('PositionShort'),
							'oldRank' => '',
							'rankBeforeSO' => '',
							'score' => ($myRow->EvRunning==1 ? get_text('ArrowAverage') : get_text('TotalShort','Tournament')),
							'completeScore' => get_text('TotalShort','Tournament'),
							'gold' => $myRow->GoldLabel,
							'xnine' => $myRow->XNineLabel,
							'hits' => get_text('Arrows','Tournament'),
                            'normative' => get_text('Normative'),
                        );

						if ($this->opts['dist']==0 && empty($this->opts['runningDist'])) {
							$fields=$fields+array(
								'tiebreak' => get_text('TieArrows'),
								'tiebreakClosest' => get_text('Close2Center', 'Tournament'),
                                'tiebreakDecoded' => get_text('TieArrows'),
								'ct' => get_text('CoinTossShort','Tournament'),
								'so' => get_text('ShotOffShort','Tournament')
							);
						}

						$fields=$fields+$distFields;

                        $distances = $distFields;

                        $section=array(
							'meta' => array(
								'event' => $curEvent,
                                'odfUnitcode' => $myRow->OdfUnitCode ? $myRow->EvOdfCode.$myRow->OdfUnitCode : '',
								'eventRealCode' => $myRow->EvCode,
								'firstPhase' => $myRow->EvFinalFirstPhase,
								'elimination1' => $myRow->EvElim1,
								'elimination2' => $myRow->EvElim2,
								'descr' => get_text($myRow->EvEventName,'','',true).($this->Flighted && $myRow->ScDescription ? ' '.$myRow->ScDescription : ''),
								'numDist' => $distValid,
								'qualifiedNo' => $myRow->QualifiedNo,
                                'firstQualified' => $myRow->EvFirstQualified,
								'printHeader' => (!empty($this->opts['runningDist']) && $this->opts['runningDist']>0 ? get_text('AfterXDistance','Tournament',$this->opts['runningDist']) : ($this->opts['dist']>0 ? get_text('AtXDistance','Tournament',$this->opts['dist']): $myRow->PrintHeader)),
								'arrowsShot'=> array(),
								'maxPersons' => 1,
								'maxScore' => $myRow->ToMaxDistScore,
								'maxArrows' => ($myRow->DiEnds ? $myRow->DiEnds*$myRow->DiArrows : $myRow->ToNumEnds*3),
								'sesArrows'=> array(),
								'running' => ($myRow->EvRunning==1 ? 1:0),
								'finished' => ($myRow->ShootOffSolved ? 1:0),
								'order' => $myRow->EvProgr,
								'fields' => $fields,
								'version' => $myRow->DocVersion,
								'versionDate' => $myRow->DocVersionDate,
								'versionNotes' => $myRow->DocNotes,
								'lastUpdate' => '0000-00-00 00:00:00',
								'hasShootOff' => 0,
								'distanceInfo'=>$FullDistInfo,
								'shootOffStarted'=>$myRow->hasShootOff,
								'qualGoldChars'=>$myRow->QualGoldChars,
								'qualXNineChars'=>$myRow->QualXNineChars,
							),
							'records' => array(),
						);
						if(!empty($this->opts['records'])) {
							$section['records'] = $this->getRecords($myRow->EvCode);
						}

						$oldScore=-1;
						$oldGold=-1;
						$oldXnine=-1;
						$myPos=0;
						$myRank=0;
					}

                    if(!empty($this->opts['runningDist']) && $this->opts['runningDist']>0) {
                        $myPos++;
                        if (!($oldScore == $myRow->OrderScore && $oldGold == $myRow->OrderGold && $oldXnine == $myRow->OrderXnine)) {
                            $myRank = $myPos;
                        }
                        $oldScore = $myRow->OrderScore;
                        $oldGold = $myRow->OrderGold;
                        $oldXnine = $myRow->OrderXnine;
                    }

				// creo un elemento per la sezione
					if($myRow->IrmShowRank) {
						$tmpRank= (!empty($this->opts['runningDist']) && $this->opts['runningDist']>0 ? $myRank : $myRow->Rank);
					} else {
                        $tmpRank = $myRow->IrmType;
					}

					$Score=$myRow->IrmShowRank ? (!empty($this->opts['runningDist']) && $this->opts['runningDist']>0 ? $myRow->OrderScore : ($myRow->EvRunning==1 ? $myRow->RunningScore: $myRow->Score)) : $myRow->IrmType;

					$item=array(
						'id'  => $myRow->EnId,
						'bib' => $myRow->EnCode,
						'localbib' => $myRow->LocalId,
						'tvname' => $myRow->EnOdfShortname,
						'birthdate' => $myRow->BirthDate ? DateTime::createFromFormat('Y-m-d', $myRow->BirthDate)->format('d.m.Y') : '',
						'session' => $myRow->Session,
						'sessionName' => $myRow->SesName,
						'target' => $myRow->TargetNo,
						'athlete' => $myRow->FirstNameUpper . ' ' . $myRow->Name . ($myRow->EnMiddleName ? " " . $myRow->EnMiddleName : ""),
						'familyname' => $myRow->FirstName,
						'familynameUpper' => $myRow->FirstNameUpper,
						'givenname' => $myRow->Name,
						'nameOrder' => $myRow->EnNameOrder,
						'gender' => $myRow->EnSex,
						'div' => $myRow->EnDivision,
						'class' => $myRow->EnClass,
						'ageclass' => $myRow->EnAgeClass,
						'subclass' => $myRow->EnSubClass,
                        'subclassName' => $myRow->ScDescription,
                        'countryId' => $myRow->CoId,
						'countryCode' => $myRow->CoCode,
                        'countryCode2' => $myRow->CoCode2,
                        'countryCode3' => $myRow->CoCode3,
						'contAssoc' => $myRow->CoCaCode,
						'memberAssoc' => $myRow->CoMaCode,
						'countryIocCode' => $myRow->EnIocCode,
						'countryName' => $myRow->CoNameComplete,
                        'countryName2' => $myRow->CoNameComplete2,
                        'countryName3' => $myRow->CoNameComplete3,
						'rank' => $myRow->IrmShowRank ? $tmpRank : '',
						'oldRank' => $myRow->OldRank,
						'finalRank' => $myRow->IndRankFinal,
						'rankBeforeSO'=>(isset($myRow->RankBeforeSO) ? $myRow->RankBeforeSO:0),
						'score' => $Score,
						'completeScore' => $myRow->Score,
						'scoreConfirmed' => $myRow->QuConfirm==$ConfirmStatus,
						'gold' => $myRow->IrmShowRank ? (!empty($this->opts['runningDist']) && $this->opts['runningDist']>0 ? $myRow->OrderGold : $myRow->Gold) : '',
						'xnine' => $myRow->IrmShowRank ? (!empty($this->opts['runningDist']) && $this->opts['runningDist']>0 ? $myRow->OrderXnine : $myRow->XNine) : '',
						'hits' => $myRow->IrmShowRank ? $myRow->Hits : '',
						'miss' => $myRow->IrmShowRank ? $myRow->Hits-$myRow->QuArrow : '',
						'notes' => trim($myRow->QuNotes. ' ' . $myRow->IndNotes),
						'record' => $this->ManageBitRecord($myRow->RecBitLevel, $myRow->CoCaCode, $myRow->CoMaCode, $myRow->EvIsPara),
						'irm' => $myRow->IndIrmType,
						'irmText' => $myRow->IrmType,
						'recordGap' => ($myRow->Arrows_Shot*10)-$myRow->Score
                    );

					if ($this->opts['dist']==0 AND empty($this->opts['runningDist'])) {
						$item=$item+array(
							'tiebreak' => trim($myRow->IndTiebreak),
                            'tiebreakClosest' => $myRow->IndTbClosest,
							'tiebreakDecoded' => $myRow->IndTbDecoded,
							'ct' => $myRow->NumCT,
							'so' => $myRow->isSO
						);
                        if(trim($myRow->IndTiebreak)) {
                            $section['meta']['hasShootOff']=max($section['meta']['hasShootOff'], strlen(trim($myRow->IndTiebreak)));
                        }
					}

					$distFields=array();
					foreach(range(1,8) as $n) {
						if(!$myRow->IrmShowRank) {
							$distFields['dist_' . $n]='|||';
						} elseif((!empty($this->opts['runningDist']) && $this->opts['runningDist']>0 && $n>$this->opts['runningDist']) || ($this->opts['dist']>0 && $n!=$this->opts['dist'])) {
							$distFields['dist_' . $n]='0|0|0|0';
						} else {
							$distFields['dist_' . $n]=$myRow->{'IndD' . $n . 'Rank'} . '|' . $myRow->{'QuD' . $n . 'Score'} . '|' . $myRow->{'QuD' . $n . 'Gold'} . '|' . $myRow->{'QuD' . $n . 'Xnine'};
						}
						$item["D{$n}Arrowstring"]=$myRow->{"QuD{$n}Arrowstring"};
					}

					$item=$item+$distFields;
                    $item['normative'] = calcNormative($distances, $myRow->EnClass, $myRow->EnDivision, $distFields)["name"];

					//Gestisco il numero di frecce tirate per sessione
					if($myRow->IndIrmType==0 AND (empty($section["meta"]["arrowsShot"][$myRow->Session]) OR $section["meta"]["arrowsShot"][$myRow->Session]<=$myRow->Arrows_Shot)) {
                        $section["meta"]["arrowsShot"][$myRow->Session] = $myRow->Arrows_Shot;
                    }

					$section['items'][]=$item;

					if ($myRow->IndTimestamp>$this->data['meta']['lastUpdate']) {
						$this->data['meta']['lastUpdate']=$myRow->IndTimestamp;
					}
					if ($myRow->IndTimestamp>$section['meta']['lastUpdate']) {
						$section['meta']['lastUpdate']=$myRow->IndTimestamp;
					}
				}

				foreach($section["meta"]["arrowsShot"] as $k => $v) {
					if($v) $section["meta"]["sesArrows"][$k] = get_text('AfterXArrows', 'Common', $v);
				}

			// ultimo giro
				$this->data['sections'][$curEvent]=$section;
			}
		}
	}
