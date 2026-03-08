<?php
/**
 * Obj_Rank_FinalTeam
 *
 * Implementa l'algoritmo di default per il calcolo della rank finale a squadre.
 *
 * La tabella in cui scrive è Teams e popola la RankFinal "a pezzi". Solo alla fine della gara
 * avremo tutta la colonna valorizzata.
 *
 * A seconda della fase che sto trattando avrò porzioni di colonna da gestire differenti e calcoli differenti.
 *
 * Per questa classe $opts ha la seguente forma:
 *
 * array(
 * 		eventsC => array(<ev_1>@<calcPhase_1>,<ev_2>@<calcPhase_2>,...,<ev_n>@<calcPhase_n>)			[calculate,non influisce su read]
 * 		eventsR => array(<ev_1>,...,<ev_n>)																[read,non influisce su calculate]
 * 		tournament => #																					[calculate/read]
 * )
 */
	class Obj_Rank_FinalTeam_calc extends Obj_Rank_FinalTeam
	{
	/**
	 * writeRow()
	 * Fa l'update in Teams
	 * @param int $id: id della persona
	 * @param string $event: evento
	 * @param int $rank: rank da scrivere
	 * @return boolean: true ok false altrimenti
	 */
		protected function writeRow($id,$subteam,$event,$rank)
		{
			$date=date('Y-m-d H:i:s');
			$q="
				UPDATE
					Teams
				SET
					TeRankFinal={$rank},
					TeTimeStampFinal='{$date}'
				WHERE
					TeTournament={$this->tournament} AND TeEvent='{$event}' AND TeCoId={$id} AND TeSubTeam='{$subteam}' and TeIrmTypeFinal<15
			";
			//print $q.'<br><br>';
			$r=safe_w_sql($q);

			return ($r!==false);
		}


	/*
	 * **************************************************************
	 *
	 * Micro algoritmi da chiamare a seconda del punto di inizio
	 *
	 * **************************************************************
	 */

	/**
	 * calcFromAbs()
	 * Calcola la RankFinal di chi si è fermato agli assoluti.
	 *
	 * @param string $event: evento su cui lavorare
	 * @return bool: true ok false altrimenti
	 */
		protected function calcFromAbs($event) {
			$date=date('Y-m-d H:i:s');

			// check if the event is a round robin...
			$q=safe_r_sql("select max(RrPartSourceRank) as NumQualified from RoundRobinParticipants where RrPartSourceLevel=0 and RrPartTournament={$this->tournament} and RrPartEvent='{$event}' and RrPartTeam=1");
			if($r=safe_fetch($q) and $r->NumQualified) {
				$Field=$r->NumQualified;
			} else {
				$Field='EvNumQualified';
			}
			$q="UPDATE Teams 
    			INNER JOIN Events ON TeEvent=EvCode AND TeTournament=EvTournament AND TeFinEvent=1
				SET TeRankFinal=IF(TeRank > $Field, TeRank, 0), TeTimeStampFinal='{$date}'
				WHERE TeTournament={$this->tournament} AND EvCode='{$event}' AND EvTeamEvent=1";
			//print $q.'<br><br>';
			$r=safe_w_sql($q);

			return ($r!==false);
		}

	/**
	 * calcFromPhase()
	 * Calcola la FinalRank per un evento in una certa fase
	 * @param string $event: evento
	 * @param int $phase: fase
	 * @return boolean: true ok false altrimenti. In un ciclo il primo errore fa terminare il metodo con false!
	 */
		protected function calcFromPhase($event, $realphase, $FirstCycle=true) {
			$date=date('Y-m-d H:i:s');

		// reset delle RankFinal della fase x le persone di quell'evento e quella fase
			$q="
				UPDATE Teams
				INNER JOIN TeamFinals ON TeCoId=TfTeam AND TeSubTeam=TfSubTeam AND TeTournament=TfTournament AND TeEvent=TfEvent AND TeFinEvent=1
			    inner join IrmTypes on IrmId=TeIrmTypeFinal and IrmShowRank=1
				INNER JOIN Grids ON TfMatchNo=GrMatchNo AND GrPhase={$realphase}
				SET
					TeRankFinal=0,
					TeTimeStampFinal='{$date}'
				WHERE
					GrPhase={$realphase} AND TeTournament={$this->tournament} AND TeEvent='{$event}' AND TeFinEvent=1
			";
			//print $q.'<br><br>';
			$r=safe_w_sql($q);
			if (!$r)
				return false;

		/*
		 *  Tiro fuori gli scontri con i perdenti nei non Opp
		 */
			$q="
				SELECT EvWinnerFinalRank, EvCodeParent, SubCodes, EvFinalFirstPhase, least(tf.TfMatchNo,tf2.TfMatchNo) as MatchNo,
                    if((EvMatchArrowsNo & GrBitPhase)=0, EvFinArrows*EvFinEnds, EvElimArrows*EvElimEnds) DiArrows, 
					tf.TfTeam AS TeamId, tf.TfSubTeam AS SubTeam, tf2.TfTeam AS OppTeamId, tf2.TfSubTeam AS OppSubTeam,
					tf.TfIrmType as IrmType, tf2.TfIrmType as OppIrmType, tf.TfWinLose as WinLose, tf2.TfWinLose as OppWinLose,
					tf.TfArrowstring as Arrowstring, tf2.TfArrowstring as OppArrowstring,tf.TfTiebreak as Tiebreak, tf2.TfTiebreak as OppTiebreak,
					tf.TfScore AS Score,tf.TfTie AS Tie, tf2.TfScore AS OppScore, tf2.TfTie as OppTie, tf.TfMatchNo as RealMatchNo, tf2.TfMatchNo as OppRealMatchNo
				FROM
					TeamFinals AS tf
					INNER JOIN TeamFinals AS tf2 ON tf.TfEvent=tf2.TfEvent AND tf.TfMatchNo=IF((tf.TfMatchNo % 2)=0,tf2.TfMatchNo-1,tf2.TfMatchNo+1) AND tf.TfTournament=tf2.TfTournament
					INNER JOIN Grids ON tf.TfMatchNo=GrMatchNo
					INNER JOIN Events ON tf.TfEvent=EvCode AND tf.TfTournament=EvTournament AND EvTeamEvent=1
					left join (select group_concat(DISTINCT concat(EvCode, '@', EvFinalFirstPhase)) SubCodes, EvCodeParent SubMainCode, EvFinalFirstPhase SubFirstPhase from Events where EvCodeParent!='' and EvTeamEvent=1 and EvTournament={$this->tournament} group by EvCodeParent, EvFinalFirstPhase) Secondary on SubMainCode=EvCode and SubFirstPhase=GrPhase/2
				WHERE
					tf.TfTournament={$this->tournament} AND tf.TfEvent='{$event}' AND GrPhase={$realphase}
					AND (tf2.TfWinLose=1 or (tf.TfIrmType>0 and tf.TfIrmType<20 and tf2.TfIrmType>0 and tf2.TfIrmType<20))
				ORDER BY
					least(tf.TfMatchNo,tf2.TfMatchNo)";


			$rs=safe_r_sql($q);

			if ($rs) {
				if (safe_num_rows($rs)>0) {
				/*
				 * Se fase 0 (oro) il perdente ha la rank=2 e il vincente piglia 1,
				 * se fase 1 (bronzo) il perdente ha la rank=4 e il vincete piglia 3
				 * e in entrambi i casi avrò sempre e solo una riga.
				 *
				 * Se fase 2 (semi) non succede nulla.
				 *
				 * Per le altre fasi si cicla nel recordset che ha il numero di righe >=0
				 */

					$myRow=safe_fetch($rs);

					// trasformo la fase
					$phase=namePhase($myRow->EvFinalFirstPhase, $realphase);

					// get the parent chain for this event if any
					$EventToUse=$event;
					$ParentCode=$myRow->EvCodeParent;
					while($ParentCode) {
						$EventToUse=$ParentCode;
						$t=safe_r_sql("select EvCodeParent from Events where EvCode=".StrSafe_DB($ParentCode));
						if($u=safe_fetch($t)) {
							$ParentCode=$u->EvCodeParent;
						} else {
							$ParentCode='';
						}
					}

					if ($phase==0 || $phase==1) {
                        $avg = [
                            round(($myRow->Score / (strlen(trim($myRow->Arrowstring)) ?: ($myRow->DiArrows ?: 1))), 3),
                            round((valutaArrowString($myRow->Tiebreak) / (strlen(trim($myRow->Tiebreak)) ?: 1)), 3),
                            round(($myRow->OppScore / (strlen(trim($myRow->OppArrowstring)) ?: ($myRow->DiArrows ?: 1))), 3),
                            round((valutaArrowString($myRow->OppTiebreak) / (strlen(trim($myRow->OppTiebreak)) ?: 1)), 3)
                        ];
                        safe_w_SQL("UPDATE TeamFinals SET TfAverageMatch='{$avg[0]}', TfAverageTie='{$avg[1]}' WHERE TfTournament='{$this->tournament}' AND TfEvent='$EventToUse' AND TfMatchNo='{$myRow->RealMatchNo}'");
                        safe_w_SQL("UPDATE TeamFinals SET TfAverageMatch='{$avg[2]}', TfAverageTie='{$avg[3]}' WHERE TfTournament='{$this->tournament}' AND TfEvent='$EventToUse' AND TfMatchNo='{$myRow->OppRealMatchNo}'");
                        $toWrite=array();
						if ($phase==0) {
						// vincente
							$toWrite[]=array('event'=>$EventToUse,'id'=>$myRow->OppTeamId,'subteam'=>$myRow->OppSubTeam, 'rank'=>$myRow->EvWinnerFinalRank);
						// perdente
							$toWrite[]=array('event'=>$EventToUse,'id'=>$myRow->TeamId,'subteam'=>$myRow->SubTeam, 'rank'=>$myRow->EvWinnerFinalRank+1);
						} elseif ($phase==1) {
						// vincente
							$toWrite[]=array('event'=>$EventToUse,'id'=>$myRow->OppTeamId,'subteam'=>$myRow->OppSubTeam, 'rank'=>$myRow->EvWinnerFinalRank+2);
						// perdente
							$toWrite[]=array('event'=>$EventToUse,'id'=>$myRow->TeamId,'subteam'=>$myRow->SubTeam, 'rank'=>$myRow->EvWinnerFinalRank+3);
						}
						foreach ($toWrite as $values) {
							$x=$this->writeRow($values['id'],$values['subteam'], $values['event'],$values['rank']);
							if ($x===false)
								return false;
						}
					} elseif ($phase==2 or $myRow->SubCodes) {
                        while ($myRow) {
                            $avg = [
                                round(($myRow->Score / (strlen(trim($myRow->Arrowstring)) ?: ($myRow->DiArrows ?: 1))), 3),
                                round((valutaArrowString($myRow->Tiebreak) / (strlen(trim($myRow->Tiebreak)) ?: 1)), 3),
                                round(($myRow->OppScore / (strlen(trim($myRow->OppArrowstring)) ?: ($myRow->DiArrows ?: 1))), 3),
                                round((valutaArrowString($myRow->OppTiebreak) / (strlen(trim($myRow->OppTiebreak)) ?: 1)), 3)
                            ];
                            safe_w_SQL("UPDATE TeamFinals SET TfAverageMatch='{$avg[0]}', TfAverageTie='{$avg[1]}' WHERE TfTournament='{$this->tournament}' AND TfEvent='$EventToUse' AND TfMatchNo='{$myRow->RealMatchNo}'");
                            safe_w_SQL("UPDATE TeamFinals SET TfAverageMatch='{$avg[2]}', TfAverageTie='{$avg[3]}' WHERE TfTournament='{$this->tournament}' AND TfEvent='$EventToUse' AND TfMatchNo='{$myRow->OppRealMatchNo}'");
                            $myRow = safe_fetch($rs);
                        }
					} else {
                        $lstMatches = array();
                        while ($myRow) {
                            $avg = [
                                round(($myRow->Score / (strlen(trim($myRow->Arrowstring)) ?: ($myRow->DiArrows ?: 1))), 3),
                                round((valutaArrowString($myRow->Tiebreak) / (strlen(trim($myRow->Tiebreak)) ?: 1)), 3),
                                round(($myRow->OppScore / (strlen(trim($myRow->OppArrowstring)) ?: ($myRow->DiArrows ?: 1))), 3),
                                round((valutaArrowString($myRow->OppTiebreak) / (strlen(trim($myRow->OppTiebreak)) ?: 1)), 3)
                            ];
                            $lstMatches[$myRow->MatchNo] = $avg[0]*1000+($avg[1]/100);
                            $toWrite[$myRow->MatchNo]=array('event'=>$EventToUse,'id'=>$myRow->TeamId,'subteam'=>$myRow->SubTeam);
                            safe_w_SQL("UPDATE TeamFinals SET TfAverageMatch='{$avg[0]}', TfAverageTie='{$avg[1]}' WHERE TfTournament='{$this->tournament}' AND TfEvent='$EventToUse' AND TfMatchNo='{$myRow->RealMatchNo}'");
                            safe_w_SQL("UPDATE TeamFinals SET TfAverageMatch='{$avg[2]}', TfAverageTie='{$avg[3]}' WHERE TfTournament='{$this->tournament}' AND TfEvent='$EventToUse' AND TfMatchNo='{$myRow->OppRealMatchNo}'");
                            $myRow=safe_fetch($rs);
                        }
                        arsort($lstMatches);
                        $pos=numMatchesByPhase($phase)+SavedInPhase($phase)+1;
                        $rank=$pos;
                        $oldScore=-1;
                        foreach ($lstMatches as $match=>$score) {
                            if($oldScore!=$score) {
                                $rank=$pos;
                                $oldScore=$score;
                            }
                            $this->writeRow($toWrite[$match]['id'],$toWrite[$match]['subteam'], $toWrite[$match]['event'],$rank);
                            $pos++;
                        }
					}
				}

			} else {
				return false;
			}

			return true;
		}

	/*
	 * **************************************************************
	 *
	 * FINE Micro algoritmi da chiamare a seconda del punto di inizio
	 *
	 * **************************************************************
	 */

	/**
	 * calculate()
	 *
	 * Al primo errore termina con false!
	 *
	 * @Override
	 *
	 * (non-PHPdoc)
	 * @see ianseo/Common/Rank/Obj_Rank#calculate()
	 */
		public function calculate()
		{
			if (count($this->opts['eventsC'])>0)
			{
				foreach ($this->opts['eventsC'] as $c)
				{
					list($event,$phase)=explode('@',$c);

					$x=true;
					switch ($phase)
					{
						case -3:
							$x=$this->calcFromAbs($event);
							break;
						case -2:
							break;
						case -1:
							break;
						default:
						/*
						 * Qui devo ciclare a partire dalla fase passata fino agli ori.
						 * Il primo errore mi fa terminare il metodo con false
						 */
							foreach (getPhasesId() as $p)
							{
							// se sono in una fase > di quella passata ignoro
								if ($p>$phase)
								{
									continue;
								}
								$x=$this->calcFromPhase($event,$p);

								if ($x===false)
								{
									return false;
								}
							}
							break;
					}

					if ($x===false)
						return false;
				}
			}
		}
	}
