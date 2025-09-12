<?php
define('IN_PHP', true);

require_once(dirname(__FILE__, 3) . '/config.php');
require_once('Common/Fun_Number.inc.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Fun_Sessions.inc.php');

CheckTourSession(true);
checkFullACL(AclQualification, '', AclReadWrite);
$EnBib='-';
$archers=array();
$tgtList = array();

// Check the correct separator (as barcode reader may interpret «-» as a «'» !)
//
if(empty($_SESSION['BarCodeSeparator'])) {
	require_once('./GetBarCodeSeparator.php');
	die();
}

$ShowMiss=(!empty($_GET['ShowMiss']));
$D=0;
$T=0;
$Turno='';
$ERROR='';

if($_GET) {
	if(!empty($_GET['BARCODESEPARATOR'])) {
		unset($_SESSION['BarCodeSeparator']);
		CD_redirect($_SERVER['PHP_SELF']);
	}

	if(!empty($_GET['T'])) $Turno='&T='.($T=intval($_GET['T']));

	// try to guess from input field both the distance and the selected archer
	if(!empty($_GET['B'])) {
		$tmpB=explode($_SESSION['BarCodeSeparator'], $_GET['B']);
		if(count($tmpB)==5) {
			$tmpB[0]="{$tmpB[0]}-{$tmpB[1]}";
			$tmpB[1]=$tmpB[2];
			$tmpB[2]=$tmpB[3];
			$tmpB[3]=$tmpB[4];
			unset($tmpB[4]);
		}
		if(!empty($tmpB[3])) {
			$_GET['D']=intval($tmpB[3]);
			$EnBib=$tmpB[0];
		}
	}

	// sets the distance
	if(!empty($_GET['D'])) {
		$D=intval($_GET['D']);
	}

	// sets the autoedit feature
	if(!empty($_GET['AutoEdit']) and empty($_GET['return']) and empty($_GET['C'])) {
		$_GET['C']='EDIT2';
	}
	unset($_GET['return']);

	// we can carry on ONLY if a distance is set (explicitly or through the barcode) -- Changed: No Distaxo, so Total!
	if(!empty($_GET['B'])) {
		// gets all the archers through the input:
		// @STTT (S=Session, T=0-padded target)
		// #Name/Surname
		// _GET['target']
		$archers=getScore($D, $_GET['B'], false, $T);
		if(!is_array($archers)) {
            $ERROR=get_text('BarCodeNotFound', 'Errors', $_GET['B']). '<br>';
            if(!$T) {
                $ERROR.=get_text('BarCodeSession', 'Errors');
            } else {
                $ERROR.=get_text('BarCodeSettings', 'Errors');
            }
        }
        if($EnBib=='-') {
			$EnBib=key($archers);
		}
		// if we have a "C" input (beware of autoedit!) then do the action
		if(!empty($_GET['C'])) {
			$C=$_GET['C'];
			unset($_GET['C']);
			if(!empty($archers[$EnBib]) and !IsBlocked(BIT_BLOCK_QUAL)) {
				$archer=$archers[$EnBib];
				$NeedsRecalc=false;
				switch(strtoupper($C)) {
					case 'EDIT':
						if($D) {
							$GoBack=$_SERVER['SCRIPT_NAME'].go_get();
								// edit the scorecard
							$_REQUEST['Command']='OK';
							$_REQUEST['x_Session']=$archer->QuTargetNo[0];
							$_REQUEST['x_Dist']=$D;
							$_REQUEST['x_Target']=substr($archer->QuTargetNo, 1);
							require_once('Qualification/WriteScoreCard.php');
							die();
						}
						break;
					case 'EDIT2':
						if($D) {
							$GoBack=$_SERVER['SCRIPT_NAME'].go_get().'&return=1';
								// edit the scorecard
							$_REQUEST['Command']='OK';
							$_REQUEST['x_Session']=$archer->QuTargetNo[0];
							$_REQUEST['x_Dist']=$D;
							$_REQUEST['x_From']=substr($archer->QuTargetNo, 1, -1);
							$_REQUEST['x_To']=substr($archer->QuTargetNo, 1, -1);
							if(count($archers)==1) $_REQUEST['x_Target']=$archer->QuTargetNo;
							$_REQUEST['x_Gold']=1;
							require_once('Qualification/index.php');
							die();
						}
						break;
					case 'REM10':
						if($D) {
							$SQL="update Qualifications set QuD{$D}Gold='0',
								QuGold=(QuD1Gold+QuD2Gold+QuD3Gold+QuD4Gold+QuD5Gold+QuD6Gold+QuD7Gold+QuD8Gold)
								where QuId={$archer->EnId}";
							safe_w_sql($SQL);
							updateArcher($archer, $D);
							$NeedsRecalc=true;
						}
						break;
					case 'REMXNINE':
						if($D) {
							$SQL="update Qualifications set QuD{$D}Xnine='0',
								QuXnine=(QuD1Xnine+QuD2Xnine+QuD3Xnine+QuD4Xnine+QuD5Xnine+QuD6Xnine+QuD7Xnine+QuD8Xnine)
								where QuId={$archer->EnId}";
							safe_w_sql($SQL);
							updateArcher($archer, $D);
							$NeedsRecalc=true;
						}
						break;
					case 'REMALL':
						if($D) {
							$SQL="update Qualifications set QuD{$D}Xnine='0', QuD{$D}Gold='0',
								QuXnine=(QuD1Xnine+QuD2Xnine+QuD3Xnine+QuD4Xnine+QuD5Xnine+QuD6Xnine+QuD7Xnine+QuD8Xnine),
								QuGold=(QuD1Gold+QuD2Gold+QuD3Gold+QuD4Gold+QuD5Gold+QuD6Gold+QuD7Gold+QuD8Gold)
								where QuId={$archer->EnId}";
							safe_w_sql($SQL);
							updateArcher($archer, $D);
							$NeedsRecalc=true;
						}
						break;
					case 'RESET':
						if($D) {
							$Select = "SELECT QuD{$D}Arrowstring ArrowString, ToGoldsChars,ToXNineChars
								FROM Qualifications
								inner join Entries on EnId=QuId
								inner join Tournament on EnTournament=ToId
								WHERE ToId={$_SESSION['TourId']} and EnId={$archer->EnId}";

							$Rs=safe_r_sql($Select, false, true);
							if($Rs and $MyRow=safe_fetch($Rs)) {
								require_once('Common/Lib/ArrTargets.inc.php');
								list($CurScore,$CurGold,$CurXNine) = ValutaArrowStringGX($MyRow->ArrowString,$MyRow->ToGoldsChars,$MyRow->ToXNineChars);

								$SQL="update Qualifications set QuD{$D}Xnine='$CurXNine', QuD{$D}Gold='$CurGold',
									QuXnine=(QuD1Xnine+QuD2Xnine+QuD3Xnine+QuD4Xnine+QuD5Xnine+QuD6Xnine+QuD7Xnine+QuD8Xnine),
									QuGold=(QuD1Gold+QuD2Gold+QuD3Gold+QuD4Gold+QuD5Gold+QuD6Gold+QuD7Gold+QuD8Gold)
									where QuId={$archer->EnId}";
								safe_w_sql($SQL);
								updateArcher($archer, $D);
								$NeedsRecalc=true;
							}
						}
						break;
					case strtoupper($_GET['B']):
						foreach($archers as $arc) {
						    updateArcher($arc, $D);
						}
						unset($_GET['C']);
						unset($_GET['B']);
						cd_redirect(basename(__FILE__).go_get());
						break;
					default:
						// reads another barcode
						$_GET['B']=$C;
				}
				if($NeedsRecalc) {
					require_once('Qualification/Fun_Qualification.local.inc.php');
					// needs to recalculate distance and total rank, reset SO etc...
					// reset SOfs
					$SQL=" SELECT DISTINCT EvCode,EvTeamEvent
						FROM Events
						INNER JOIN EventClass ON EvCode=EcCode AND if(EvTeamEvent='0', EcTeamEvent=0, EcTeamEvent>0) AND EcTournament={$_SESSION['TourId']}
						INNER JOIN Entries ON TRIM(EcDivision)=TRIM(EnDivision) AND TRIM(EcClass)=TRIM(EnClass) AND if(EcSubClass='', true, EcSubClass=EnSubClass) AND EnId={$archer->EnId}
					WHERE (EvTeamEvent='0' AND EnIndFEvent='1') OR (EvTeamEvent='1' AND EnTeamFEvent+EnTeamMixEvent>0) AND EvTournament={$_SESSION['TourId']} ";
					$Rs=safe_r_sql($SQL);

					while ($row=safe_fetch($Rs)) {
						ResetShootoff($row->EvCode, $row->EvTeamEvent, 0);
					}

					// recalculate ranks
					$Select = "SELECT QuScore, QuGold, QuXnine FROM Qualifications WHERE QuId={$archer->EnId}";
					$Rs=safe_r_sql($Select);
					if ($MyRow = safe_fetch($Rs)) {
						$Score = $MyRow->QuScore;
						$Gold = $MyRow->QuGold;
						$Xnine = $MyRow->QuXnine;

						// distance Rank
						$Event = '*#*#';

						$Select = "SELECT CONCAT(EnDivision,EnClass) AS MyEvent, EnCountry as MyTeam, EnDivision, EnClass, EnIndClEvent, EnIndFEvent, EnTeamClEvent, EnTeamFEvent+EnTeamMixEvent as AbsTeam,
                            ToElabTeam!=127 as MakeTeams
							FROM Entries
							inner join Tournament on ToId=EnTournament
							WHERE EnId={$archer->EnId} AND EnTournament={$_SESSION['TourId']}";
						$Rs=safe_r_sql($Select);

						if ($rr=safe_fetch($Rs)) {
							$Event = $rr->MyEvent;
							$Category = $rr->MyEvent;
							$Club = $rr->MyTeam;
							$Div = $rr->EnDivision;
							$Cl = $rr->EnClass;

							if($rr->EnIndClEvent) {
                                CalcQualRank($D, $Event);
                                CalcQualRank(0, $Event);
                            }

                            // regular teams
                            if($rr->MakeTeams and $rr->EnTeamClEvent) {
                                MakeTeams($Club, $Category);
                            }

                            // recalc AbsTeams
                            if($rr->MakeTeams and $rr->AbsTeam) {
                                MakeTeamsAbs($Club, $Div, $Cl);
                            }

                            if($rr->EnIndFEvent) {
                                // recalc Individuals
                                $events4abs=array();
                                $Rs=safe_r_sql("select distinct IndEvent from Individuals where IndId={$archer->EnId} AND IndTournament={$_SESSION['TourId']}");
                                while($rr=safe_fetch($Rs)) {
                                    $events4abs[] = $tmp->IndEvent;
                                }
                                if (count($events4abs)) {
                                    Obj_RankFactory::create('Abs', array('events' => $events4abs, 'dist' => $D))->calculate();
                                    Obj_RankFactory::create('Abs', array('events' => $events4abs, 'dist' => 0))->calculate();
                                    foreach ($events4abs as $eventAbs) {
                                        runJack("QRRankUpdate", $_SESSION['TourId'], array("Event" => $eventAbs, "Team" => 0, "TourId" => $_SESSION['TourId']));
                                    }
                                }
                            }
						}
					}
				}
				cd_redirect(basename(__FILE__).go_get());
			} elseif(getScore($D, $C, false, $T)) {
				// reads another barcode
				$_GET['B']=$C;
				cd_redirect(basename(__FILE__).go_get());
			}
		}
	}
}

$JS_SCRIPT=array(
        '<script type="text/javascript" src="./barcode.js"></script>',
        '<link href="./barcode.css" media="screen" rel="stylesheet" type="text/css">'
);

$IncludeFA=true;
$IncludeJquery=true;
include('Common/Templates/head.php');

echo '<div id="bcodeContainer" class="bcodeContainer"><div class="bcodeOp"><form id="Frm" method="get" action="">
<table class="Tabella2 w-100">
<tr><th class="Title" colspan="6">'.get_text('CheckScorecards','Tournament').'</th></tr>
<tr class="h-0">
	<th colspan="5" class="w-60">' . get_text('BarcodeSeparator','BackNumbers') . ': <span style="font-size:150%">' . $_SESSION['BarCodeSeparator'] . '</span>' . '</th>
	<th colspan="1" class="w-10"><a href="' . $_SERVER["PHP_SELF"]. '?BARCODESEPARATOR=1">' . get_text('ResetBarcodeSeparator','BackNumbers') . '</a></th>
</tr>
<tr>
    <th class="w-5">'.get_text('Targets','Tournament').'</th>
	<th class="w-5">'.get_text('AutoEdits','Tournament').'</th>
	<th class="w-5">'.get_text('ShowMissing','Tournament').'</th>
	<th class="w-5">'.get_text('Distance','Tournament').'</th>
	<th class="w-20">'.get_text('Barcode','BackNumbers').'</th>
	<th class="w-20">'.get_text('Session').'</th>
</tr>
<tr class="h-0">
    <td class="Center"><input type="checkbox" onclick="refreshForm()" name="Targets"'.((empty($_GET) or !empty($_GET['Targets'])) ? ' checked="checked"' : '').'></td>
    <td class="Center"><input type="checkbox" onclick="refreshForm()" name="AutoEdit"'.(!empty($_GET['AutoEdit']) ? ' checked="checked"' : '').'></td>
    <td class="Center"><input type="checkbox" onclick="refreshForm()" name="ShowMiss"'.((empty($_GET) or !empty($_GET['ShowMiss'])) ? ' checked="checked"' : '').'></td>
    <td class="Center"><select id="Distance" name="D"  onchange="refreshForm()"><option value="0"></option>';

$q=safe_r_sql("Select ToNumDist, ToGolds, ToXNine from Tournament where ToId={$_SESSION['TourId']}");
$TOUR=safe_fetch($q);
foreach(range(1,$TOUR->ToNumDist) as $d) {
    echo '<option value="'.$d.'"'.(!empty($D) && $D==$d ? ' selected="selected"' : '').'>'.$d.'</option>';
}
echo '</select></td><td class="Center">';
if(!empty($_GET['B'])) {
	echo '<input type="hidden" name="B" value="'.$_GET['B'].'">';
	echo '<input type="text" class="w-95" name="C" id="bib" tabindex="1">';
} else {
	echo '<input type="text" name="B" class="w-95" id="bib" tabindex="1">';
}
echo '</td><td class="Center"><select class="w-95" id="Session" name="T"  onchange="refreshForm(true)"><option value="0"></option>';
$q=safe_r_sql("Select distinct SesOrder, SesName from Session where SesType='Q' and SesTournament={$_SESSION['TourId']} order by SesOrder");
while($r=safe_fetch($q)) {
    echo '<option value="'.$r->SesOrder.'" '.(!empty($_GET['T']) && $_GET['T']==$r->SesOrder ? ' selected="selected"' : '').'>'.($r->SesName ? $r->SesName : $r->SesOrder).'</option>';
}
echo '</select></td>
</tr>
<tr>
    <td class="Center" colspan="2"><input type="submit" value="'. get_text('CmdGo','Tournament').'" id="Vai" onClick="refreshForm();"></td>
    <td class="Center"><input type="button" value="'.get_text('BarcodeMissing','Tournament').'" onClick="window.open(\'./GetScoreBarCodeMissing.php?S=Q&D=\'+document.getElementById(\'Distance\').value+\'&T=\'+document.getElementById(\'Session\').value);"></td>
</tr>
<tr>
    <td colspan="6">'.get_text('ScoreBarCodeShortcuts', 'Help').'</td>
</tr>';
if(!$archers){
    if($ERROR) {
        echo '<tr><td colspan="6"><div class="red p-2 text-white LetteraGrande">'.$ERROR.'</div></td></tr>';
    }
} else  {
    echo '<tr><td colspan="6"><br><table class="Tabella TabellaScore">';
    echo '<tr><th class="Title" colspan="16">'.get_text('Archer').'</th></tr>';
    echo '<tr>';
    echo '<th>'.get_text('TargetShort', 'Tournament').'</th>';
    echo '<th>'.get_text('DistanceShort','Tournament').'</th>';
    echo '<th colspan="2">'.get_text('Name','Tournament').'</th>';
    echo '<th>'.get_text('ClassDiv', 'InfoSystem').'</th>';
    if($_SESSION['TourLocSubRule']=='NFAA3D-ReddingWestern') {
        echo '<th>'.get_text('Total').'</th>';
        echo '<th>'.get_text('DistanceNum', 'Api', 1).'</th>';
        if($D>1) {
            echo '<th>'.get_text('DistanceNum', 'Api', 2).'</th>';
        }
        if($D>2) {
            echo '<th>'.get_text('DistanceNum', 'Api', 3).'</th>';
        }
    } else {
        echo '<th>'.get_text('Total').'</th>';
        echo '<th>'.$TOUR->ToGolds.'</th>';
        echo '<th>'.$TOUR->ToXNine.'</th>';
        echo '<th>'.get_text('Total').'</th>';
        echo '<th>'.$TOUR->ToGolds.'</th>';
        echo '<th>'.$TOUR->ToXNine.'</th>';
    }
    echo '<th>'.get_text('Arrows','Tournament').'</th>';
    echo '<th colspan="4"></th>';
    echo '</tr>';
    foreach($archers as $archer) {
        $tgtList[] = $archer->QuTargetNo;
        $T=$archer->QuTargetNo[0];
        echo '<tr'.($archer->EnBib==$EnBib ? ' class="selected"' : '').'>';
        echo '<td class="Score">'.ltrim(substr($archer->QuTargetNo, 1), '0').'</td>';
        echo '<td class="Score">'.intval($D).'</td>';
        echo '<td>'.$archer->Firstname.'</td>';
        echo '<td>'.$archer->EnName.'</td>';
        echo '<td class="Center">'.$archer->EnDivision.' '.$archer->EnClass.'</td>';
        if($_SESSION['TourLocSubRule']=='NFAA3D-ReddingWestern') {
            $Tot=$archer->Score1;
            $Col='<td class="Right">'.$archer->Score1.'</td>';
            if($D>1) {
                $Tot+=$archer->Score2;
                $Col.='<td class="Right">'.$archer->Score2.'</td>';
            }
            if($D>2) {
                $Tot+=$archer->Score3;
                $Col.='<td class="Right">'.$archer->Score3.'</td>';
            }
            echo '<td class="Right"><b>'.$Tot.'</b></td>';
            echo $Col;
        } else {
            echo '<td class="Score ScoreBig">'.$archer->Score.'</td>';
            echo '<td class="Score ScoreBig">'.$archer->Gold.'</td>';
            echo '<td class="Score ScoreBig">'.$archer->Xnine.'</td>';
            echo '<td class="Score">'.$archer->tScore.'</td>';
            echo '<td class="Score">'.$archer->tGold.'</td>';
            echo '<td class="Score">'.$archer->tXnine.'</td>';
        }
        echo '<td class="Score '.((($archer->Hits OR $archer->expectedArrows) AND $archer->Hits != $archer->expectedArrows) ? 'ArrError': '').'">'.$archer->Hits.'</td>';
        echo '<td class="Command Bold"><a href="'.go_get(array('B'=>$archer->EnBib.$_SESSION['BarCodeSeparator'].$archer->EnDivision.$_SESSION['BarCodeSeparator'].$archer->EnClass, 'C' => $archer->EnBib.$_SESSION['BarCodeSeparator'].$archer->EnDivision.$_SESSION['BarCodeSeparator'].$archer->EnClass)).'">CONFIRM</a></td>';
        if($D) {
            echo '<td class="Command"><a href="'.go_get(array('B'=>$archer->EnBib.$_SESSION['BarCodeSeparator'].$archer->EnDivision.$_SESSION['BarCodeSeparator'].$archer->EnClass, 'C'=> 'EDIT')).'">Edit arrows</a>
					<br/><a href="'.go_get(array('B'=>$archer->EnBib.$_SESSION['BarCodeSeparator'].$archer->EnDivision.$_SESSION['BarCodeSeparator'].$archer->EnClass, 'C' => 'EDIT2')).'">Edit totals</a>
					</td>';
            echo '<td class="Command"><a href="'.go_get(array('B'=>$archer->EnBib.$_SESSION['BarCodeSeparator'].$archer->EnDivision.$_SESSION['BarCodeSeparator'].$archer->EnClass, 'C'=> 'REM10')).'">Remove 10</a>
					<br/><a href="'.go_get(array('B'=>$archer->EnBib.$_SESSION['BarCodeSeparator'].$archer->EnDivision.$_SESSION['BarCodeSeparator'].$archer->EnClass, 'C'=> 'REMXNINE')).'">Remove X/Nine</a>
					<br/><a href="'.go_get(array('B'=>$archer->EnBib.$_SESSION['BarCodeSeparator'].$archer->EnDivision.$_SESSION['BarCodeSeparator'].$archer->EnClass, 'C'=> 'REMALL')).'">Remove both</a>
					</td>';
            echo '<td class="Command"><a href="'.go_get(array('B'=>$archer->EnBib.$_SESSION['BarCodeSeparator'].$archer->EnDivision.$_SESSION['BarCodeSeparator'].$archer->EnClass, 'C'=> 'RESET')).'">Reset both</a>
					</td>';
        } else {
            echo '<td colspan="3">&nbsp;</td>';
        }
        echo '</tr>';
    }
    echo '</table></td></tr>';
}
echo '<tr class="divider"><td colspan="6"></td></tr>
    <tr><th colspan="6"><img class="p-2" src="beiter.png" alt="Beiter Logo" /><br>' . get_text('Credits-BeiterCredits', 'Install') . '</th></tr>';
echo '</table></div>
    <div id="bcodeMissingContainer">';

if($ShowMiss) {
    $cnt = 0;
    $tgt = 0;
    $tmpRow = '';
    $MyQuery = "SELECT EnCode as Bib
			, EnName AS Name
			, upper(EnFirstName) AS FirstName
			, QuSession AS Session
			, SUBSTRING(QuTargetNo,2) AS TargetNo
			, CoCode AS NationCode, CoName AS Nation
			, EnClass AS ClassCode, ClDescription
			, EnDivision AS DivCode, DivDescription
		FROM Entries
		inner JOIN Countries ON EnCountry=CoId AND EnTournament=CoTournament
		inner JOIN Qualifications ON EnId=QuId " . ($T ? "and QuSession=$T " : " ") . "
		inner JOIN Divisions ON EnTournament=DivTournament AND EnDivision=DivId
		inner JOIN Classes ON EnTournament=ClTournament AND EnClass=ClId
		WHERE EnAthlete=1
			AND EnTournament = {$_SESSION['TourId']} AND EnStatus<=1
			AND QuConfirm & ".pow(2, $D)." = 0
		ORDER BY QuTargetNo ";
    $Q=safe_r_sql($MyQuery);

    while($r=safe_fetch($Q)) {
        $tgtClass = '';
        if(empty($_GET['Targets']) or $tgt!=intval($r->TargetNo)) {
            $tgtClass = ($tgt!=intval($r->TargetNo) ? 'newTgt' : '');
            $tgt=intval($r->TargetNo);
            $cnt++;
        }
        $tmpRow .= '<tr class="'.$tgtClass.'" '.
            (in_array($r->Session.$r->TargetNo, $tgtList) ? '' : 'onclick="sendTarget(\''.(empty($_GET['Targets']) ? $r->TargetNo : $tgt).'\')"').
            '><td>'.$r->TargetNo.'</td><td>'.$r->DivCode.$r->ClassCode.'</td><td>'.$r->FirstName.' '.$r->Name.'</td></tr>';
    }
    echo '<div class="fixedHead">' . get_text('TotalMissingScorecars','Tournament',$cnt) . '</div>';
    echo '<div id="bcodeMissing"><table id="bcodeMissingTable">';
    echo '<colgroup><col class="w-5"><col class="w-10"><col class="w-85 nowrap"></colgroup>';
    echo '<tbody class="scrollBody">'.$tmpRow.'</tbody>';
    echo '</table></div>';
}
echo '</div></div>';
include('Common/Templates/tail.php');


function getScore($dist, $barcode, $strict=false, $Session=0) {
	global $EnBib;
	$ret=array();
	$div='';
	$cls='';
	if($barcode[0]=='@') {
		$barcode=substr($barcode,1);
        $letter='%';
        if(!is_numeric($barcode)) {
            $letter=substr($barcode,-1);
            $barcode=substr($barcode,0, -1);
        }
		// left-pad with 0 and insert jolly session if session not defined or not set
		if(strlen($barcode)<4) {
            $barcode=($Session ?: '_').str_pad($barcode, 3, '0', STR_PAD_LEFT);
        }

		$filter=" QuTargetNo like '".$barcode.$letter."'";
	} elseif($barcode[0]=='#') {
		$filter=" (EnFirstname like ".StrSafe_DB(substr($barcode,1).'%')." or EnName like ".StrSafe_DB(substr($barcode,1).'%').")";
	} else {
		$tmp=@explode($_SESSION['BarCodeSeparator'], $barcode);
		if(count($tmp)>4) {
			$bib=$tmp[0].'-'.$tmp[1];
			$div=$tmp[2];
			$cls=$tmp[3];
		} else {
			//$bib=ltrim($tmp[0], '0'); // why??? Breaks all the regular bibs that start with 0!
			$bib=$tmp[0]??'';
			$div=$tmp[1]??'';
			$cls=$tmp[2]??'';
		}
		if(substr($bib, 0, 2)=='UU') $bib='_'.substr($bib, 2);
		$filter="EnCode='$bib' and EnDivision='$div' and EnClass='$cls'";
        $filter2="EnCode='$bib'";
        if($Session) {
            $filter.=" and QuSession=$Session";
            $filter2.=" and QuSession=$Session";
        }

        $EnBib=$bib;

		if(!$strict and !empty($_GET['Targets'])) {
			$filter="left(QuTargetNo,4)=(select left(QuTargetNo,4) from Qualifications inner join Entries on EnId=QuId and EnTournament={$_SESSION['TourId']} where $filter)";
		}
		if(empty($bib) or empty($div) or empty($cls)) return;
	}
	$SQL="select QuTargetNo, EnCode EnBib, EnId, EnName, upper(EnFirstname) Firstname, EnDivision, EnClass, QuScore tScore, QuGold tGold, QuXnine tXnine, IFNULL(if(DiScoringEnds=0, DiEnds, DiScoringEnds)*DiArrows,0) as expectedArrows, 
	    " . ($_SESSION['TourLocSubRule']=='NFAA3D-ReddingWestern' ? "QuD1Score Score1, QuD2Score Score2, QuD3Score Score3, QuD1Hits Hits1, QuD2Hits Hits2, QuD3Hits Hits3," : "") . "
	    " . ($dist ? "QuD{$dist}Score Score, QuD{$dist}Gold Gold, QuD{$dist}Xnine Xnine, QuD{$dist}Hits Hits" : "QuScore Score, QuGold Gold, QuXnine Xnine, QuHits Hits") . "
		from Qualifications 
		inner join Entries on EnId=QuId and EnTournament={$_SESSION['TourId']}
		inner join Session on SesTournament=EnTournament and SesOrder=QuSession and SesType='Q'
		left join DistanceInformation on DiTournament=EnTournament AND DiSession=QuSession AND DiDistance={$dist} AND DiType='Q'
		where $filter
		order by QuTargetNo, EnDivision='$div' desc, EnClass='$cls' desc ";
    $q=@safe_r_sql($SQL, false, true);
    if(!$q) {
        return false;
    }
	while($r=safe_fetch($q)) {
		$ret["$r->EnBib"]=$r;
	}
	if(!$ret) {
		$SQL="select QuTargetNo, EnCode EnBib, EnId, EnName, upper(EnFirstname) Firstname, EnDivision, EnClass, QuScore tScore, QuGold tGold, QuXnine tXnine, IFNULL(if(DiScoringEnds=0, DiEnds, DiScoringEnds)*DiArrows,0) as expectedArrows, " .
				($dist ? "QuD{$dist}Score Score, QuD{$dist}Gold Gold, QuD{$dist}Xnine Xnine, QuD{$dist}Hits Hits" : "QuScore Score, QuGold Gold, QuXnine Xnine, QuHits Hits") . "
				from Qualifications 
				inner join Entries on EnId=QuId and EnTournament={$_SESSION['TourId']} 
				left join DistanceInformation on DiTournament=EnTournament AND DiSession=QuSession AND DiDistance={$dist} AND DiType='Q'
				where $filter2
				order by QuTargetNo, EnDivision='$div' desc, EnClass='$cls' desc ";
		$q=safe_r_sql($SQL, false, true);
		while($r=safe_fetch($q)) {
			$ret["$r->EnBib"]=$r;
		}
		if(count($ret)>1) {
			$ret=array();
		}
	}
	if(!$ret) {
		$filter="EdExtra='$bib' and EnDivision='$div' and EnClass='$cls'";
		$filter2="EdExtra='$bib'";
        if($Session) {
            $filter.=" and QuSession=$Session";
            $filter2.=" and QuSession=$Session";
        }
		$EnBib=$bib;

		if(!$strict and !empty($_GET['Targets'])) {
			$filter="left(QuTargetNo,4)=(select left(QuTargetNo,4) from Qualifications inner join Entries on EnId=QuId and EnTournament={$_SESSION['TourId']} inner JOIN ExtraData ON EdType='Z' and EdId=EnId where $filter)";
		}
		if(empty($bib) or empty($div) or empty($cls)) return;

		$SQL="select QuTargetNo, EdExtra EnBib, EnId, EnName, upper(EnFirstname) Firstname, EnDivision, EnClass, QuScore tScore, QuGold tGold, QuXnine tXnine, IFNULL(if(DiScoringEnds=0, DiEnds, DiScoringEnds)*DiArrows,0) as expectedArrows, " .
			($dist ? "QuD{$dist}Score Score, QuD{$dist}Gold Gold, QuD{$dist}Xnine Xnine, QuD{$dist}Hits Hits" : "QuScore Score, QuGold Gold, QuXnine Xnine, QuHits Hits") . "
            from Qualifications 
            inner join Entries on EnId=QuId and EnTournament={$_SESSION['TourId']} 
            inner JOIN ExtraData ON EdType='Z' and EdId=EnId
            left join DistanceInformation on DiTournament=EnTournament AND DiSession=QuSession AND DiDistance={$dist} AND DiType='Q'
            where $filter
            order by QuTargetNo, EnDivision='$div' desc, EnClass='$cls' desc ";
		$q=safe_r_sql($SQL, false, true);
		while($r=safe_fetch($q)) $ret["$r->EnBib"]=$r;
		if(!$ret) {
			$SQL="select QuTargetNo, EdExtra EnBib, EnId, EnName, upper(EnFirstname) Firstname, EnDivision, EnClass, QuScore tScore, QuGold tGold, QuXnine tXnine, IFNULL(if(DiScoringEnds=0, DiEnds, DiScoringEnds)*DiArrows,0) as expectedArrows, " .
				($dist ? "QuD{$dist}Score Score, QuD{$dist}Gold Gold, QuD{$dist}Xnine Xnine, QuD{$dist}Hits Hits" : "QuScore Score, QuGold Gold, QuXnine Xnine, QuHits Hits") . "
				from Qualifications 
				inner join Entries on EnId=QuId and EnTournament={$_SESSION['TourId']} 
                inner JOIN ExtraData ON EdType='Z' and EdId=EnId
                left join DistanceInformation on DiTournament=EnTournament AND DiSession=QuSession AND DiDistance={$dist} AND DiType='Q'
				where $filter2
				order by QuTargetNo, EnDivision='$div' desc, EnClass='$cls' desc ";
			$q=safe_r_sql($SQL, false, true);
			while($r=safe_fetch($q)) $ret["$r->EnBib"]=$r;
			if(count($ret)>1) {
				$ret=array();
			}
		}
	}
	return $ret;
}

function updateArcher($archer, $D) {
	$SQL= "update Qualifications
	    set QuConfirm = QuConfirm | ".pow(2, $D) ."
	    where QuId=$archer->EnId";
	safe_w_sql($SQL);
}