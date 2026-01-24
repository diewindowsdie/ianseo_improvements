<?php
/*
													- UpdateQuals.php -
	Aggiorna la tabella Qualifications
*/

$JSON=[
	'error' => 1 ,
	'id' => 'null' ,
	'score' => 'null' ,
	'gold' => 'null' ,
	'xnine' =>  'null' ,
	'which' =>  '' ,
];

require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Qualification/Fun_Qualification.local.inc.php');
require_once('Common/Fun_Sessions.inc.php');
require_once('Common/Lib/ArrTargets.inc.php');
require_once('Common/Lib/Obj_RankFactory.php');	// nuovo by simo

if (!CheckTourSession() or !hasFullACL(AclQualification, '', AclReadWrite) or IsBlocked(BIT_BLOCK_QUAL)) {
    JsonOut($JSON);
}

$Errore=0;
$Cosa = "";
$Atleta = "";
$Societa = "";
$Category = "";

$OldValue=null;

$Evento = '*#*#';
$Div="";
$Cl="";

$MyRow=NULL;

foreach ($_REQUEST as $Key => $Value) {
	if (substr($Key,0,2)!='d_') {
        continue;
    }
    list(,$Cosa,$Atleta)=explode('_',$Key);
    $Atleta=intval($Atleta);

    /*
        Fetch the old data to establish a SO reset
    */
    $Dist=substr($Cosa,3,1);
    $OldValue='';
    $Sel = "SELECT *
        FROM Qualifications
        WHERE QuId={$Atleta}";
    $RsSel =safe_r_sql($Sel);
    if ($rr=safe_fetch($RsSel)) {
        $OldValue=$rr->{$Cosa};
    }

    // Check common errors
    if (strpos($Key,'Score')!==false) {
        $MaxScore=0;
        $Select = "SELECT ToMaxDistScore AS TtMaxDistScore
            FROM Tournament
            WHERE ToId={$_SESSION['TourId']}";

        $RsMax=safe_r_sql($Select);

        if (safe_num_rows($RsMax)!=1) {
            jsonOut($JSON);
        }
        $rr=safe_fetch($RsMax);
        if ($Value > $rr->TtMaxDistScore) {
            jsonOut($JSON);
        }
    } elseif ($Cosa=='QuArrow') {
        $Dist=0;
        $q=safe_r_sql("select sum(DiArrows*DiEnds) TotArrows 
            from Entries
            inner join Qualifications on QuId=EnId
            inner join DistanceInformation on DiSession=QuSession and DiTournament=EnTournament
            where EnId={$Atleta} and EnTournament={$_SESSION['TourId']}
            group by EnId");
        $r=safe_fetch($q);
        $TotArrows=intval($r->TotArrows??0);
        if($Value>$TotArrows or $Value<0) {
            jsonOut($JSON);
        }
    } else {
        if ($Value != 0) {
            $Value = intval($Value);
            // distanza trattata
            $Select = "SELECT QuD" . $Dist . "Score AS Score,QuD" . $Dist . "Gold AS Gold,QuD" . $Dist . "Xnine AS Xnine, TfT".$Dist." as Tgt, 
                    IF(TfGoldsChars" . $Dist . "='',IF(TfGoldsChars='',ToGoldsChars,TfGoldsChars),TfGoldsChars" . $Dist . ") AS TtGolds, IF(TfXNineChars" . $Dist . "='',IF(TfXNineChars='',ToXNineChars,TfXNineChars),TfXNineChars" . $Dist . ") AS TtXNine
                FROM Qualifications
                INNER JOIN Entries ON QuId=EnId
                INNER JOIN Tournament ON EnTournament=ToId
                LEFT JOIN TargetFaces ON EnTournament=TfTournament and EnTargetFace=TfId
                WHERE QuId={$Atleta} AND ToId={$_SESSION['TourId']}";

            $RsGX = safe_r_sql($Select);

            if (safe_num_rows($RsGX) != 1) {
                jsonout($JSON);
            }
            $Row = safe_fetch($RsGX);
            $Tgt = GetGoodLettersFromTgtId(($Row->Tgt??1));


            $arrG = array_intersect(str_split($Row->TtGolds),$Tgt);
            $arrX = array_intersect(str_split($Row->TtXNine),$Tgt);

            //Check if arrays are completely included
            $validValue=true;
            if(substr($Cosa,-5)=='Xnine'){
                $validValue = ($validValue AND ($Value*GetMinTargetValue($arrX)<=$Row->Score));
                if($Row->Gold>0 AND count(array_intersect($arrX,$arrG))==count($arrX)) {
                    $validValue = ($validValue AND $Value<=$Row->Gold);
                } else if(count(array_intersect($arrX,$arrG))!=0 and count(array_intersect($arrX,$arrG))!=count($arrX)) {
                    if($Value>=$Row->Gold) {
                        $validValue = ($validValue and floor((intval($Row->Score) - ($Row->Gold * GetMinTargetValue($arrG))) / GetMinTargetValue($arrX)) - ($Value - $Row->Gold) >= 0);
                    } else {
                        $validValue = ($validValue AND $Value<=$Row->Gold);
                    }
                } else if(count(array_intersect($arrX,$arrG))==0) {
                    $validValue = ($validValue AND intval($Row->Score)-($Row->Gold*GetMinTargetValue($arrG))-($Value*GetMinTargetValue($arrX))>=0);
                }
            } else if(substr($Cosa,-4)=='Gold') {
                $validValue = ($validValue AND ($Value*GetMinTargetValue($arrG)<=$Row->Score));
                if($Row->Xnine>0 AND count(array_intersect($arrG,$arrX))==count($arrG)) {
                    $validValue = ($validValue AND $Value<=$Row->Xnine);
                } else if(count(array_intersect($arrG,$arrX))!=0 and count(array_intersect($arrG,$arrX))!=count($arrG)) {
                    if($Value>=$Row->Xnine) {
                        $validValue = ($validValue and floor((intval($Row->Score) - ($Row->Xnine * GetMinTargetValue($arrX))) / GetMinTargetValue($arrG)) - ($Value - $Row->Xnine) >= 0);
                    } else {
                        $validValue = ($validValue AND $Value<=$Row->Xnine);
                    }
                } else if(count(array_intersect($arrG,$arrX))==0) {
                    $validValue = ($validValue AND intval($Row->Score)-($Row->Xnine*GetMinTargetValue($arrX))-($Value*GetMinTargetValue($arrG))>=0);
                }
            }
            if(!$validValue) {
                jsonOut($JSON);
            }
        }
    }

    if($OldValue!=$Value) {
        // scrivo il dato e aggiorno i totali
        $Update = "UPDATE Qualifications SET "
            . $Cosa . "=" . StrSafe_DB($Value) . ", "
            . "QuConfirm = QuConfirm & (255-" . pow(2, intval($Dist)) . "), "
            . "QuSigned = QuSigned & (255-" . pow(2, intval($Dist)) . "), "
            . "QuScore=QuD1Score+QuD2Score+QuD3Score+QuD4Score+QuD5Score+QuD6Score+QuD7Score+QuD8Score,"
            . "QuGold=QuD1Gold+QuD2Gold+QuD3Gold+QuD4Gold+QuD5Gold+QuD6Gold+QuD7Gold+QuD8Gold,"
            . "QuXnine=QuD1Xnine+QuD2Xnine+QuD3Xnine+QuD4Xnine+QuD5Xnine+QuD6Xnine+QuD7Xnine+QuD8Xnine, "
            . "QuHits=QuD1Hits+QuD2Hits+QuD3Hits+QuD4Hits+QuD5Hits+QuD6Hits+QuD7Hits+QuD8Hits, "
            . "QuTimestamp=" . StrSafe_DB(date('Y-m-d H:i:s')) . " "
            . "WHERE QuId=" . StrSafe_DB($Atleta);
        $RsUp = safe_w_sql($Update);
        if (safe_w_affected_rows() == 1 AND $OldValue != $Value) {
            // distruggo e ricreo le eliminatorie
            // scopro in che evento elim si trova la divcl del tipo
            $q = "SELECT EvCode
                FROM Individuals 
                INNER JOIN Events on EvCode=IndEvent and EvTournament=IndTournament and EvTeamEvent=0 AND (EvElim1+EvElim2)>0
                WHERE IndId={$Atleta}";
            $r = safe_r_sql($q);
            //print $q;exit;
            if ($r && safe_num_rows($r) > 0) {
                while ($row = safe_fetch($r)) {
                    $ev = $row->EvCode;
                    for ($j = 1; $j <= 2; ++$j) {
                        ResetElimRows($ev, $j);
                    }
                }
            }

            // azzero gli shootoff
            $q = " SELECT DISTINCT EvCode,EvTeamEvent
                FROM Events
                INNER JOIN EventClass ON EvCode=EcCode AND if(EvTeamEvent=0, EcTeamEvent=0, EcTeamEvent>0) AND EcTournament={$_SESSION['TourId']}
                INNER JOIN Entries ON EcDivision=EnDivision AND EcClass=EnClass and if(EcSubClass='', true, EcSubClass=EnSubClass) AND EnId={$Atleta}
                WHERE (EvTeamEvent='0' AND EnIndFEvent='1') OR (EvTeamEvent='1' AND EnTeamFEvent+EnTeamMixEvent>0) AND EvTournament={$_SESSION['TourId']}";
            //print $q;
            $Rs = safe_r_sql($q);
            if ($Rs && safe_num_rows($Rs) > 0) {
                while ($row = safe_fetch($Rs)) {
                    ResetShootoff($row->EvCode, $row->EvTeamEvent, 0);
                }
            }
        }
    }

    // estraggo i totali
    $Select = "SELECT QuId,QuScore,QuGold,QuXnine, {$Cosa} 
        FROM Qualifications WHERE QuId={$Atleta}";
    $Rs=safe_r_sql($Select);
    $MyRow=safe_fetch($Rs);
    if ($Value!=$MyRow->{$Cosa}) {
        jsonout($JSON);
    }

    if ($OldValue!=$Value AND !isset($_REQUEST["NoRecalc"])) {
        $Select = "SELECT CONCAT(EnDivision,EnClass) AS MyEvent, EnCountry as MyTeam, EnDivision, EnClass
                FROM Entries
                WHERE EnId={$Atleta} AND EnTournament={$_SESSION['TourId']}";

        $Rs = safe_r_sql($Select);
        $rr = safe_fetch($Rs);
        $Evento = $rr->MyEvent;
        $Category = $rr->MyEvent;
        $Societa = $rr->MyTeam;
        $Div = $rr->EnDivision;
        $Cl = $rr->EnClass;

        if ($Dist) {
            if (CalcQualRank($Dist, $Evento)) {
                jsonout($JSON);
            }
        }
        if (CalcQualRank(0, $Evento)) {
            jsonout($JSON);
        }

        // eventi di cui calcolare le rank assolute
        $events4abs = array();
        $q = "SELECT distinct IndEvent from Individuals where IndId={$Atleta} and IndTournament={$_SESSION['TourId']}";
        $r = safe_r_sql($q);
        while ($tmp = safe_fetch($r)) {
            $events4abs[] = $tmp->IndEvent;
        }

        // rank abs di distanza
        if (count($events4abs) > 0) {
            if ($Dist and !Obj_RankFactory::create('Abs', array('events' => $events4abs, 'dist' => $Dist))->calculate()) {
                jsonout($JSON);
            }

            if (!Obj_RankFactory::create('Abs', array('events' => $events4abs, 'dist' => 0))->calculate()) {
                jsonout($JSON);
            } else {
                foreach ($events4abs as $eventAbs) {
                    runJack("QRRankUpdate", $_SESSION['TourId'], array("Event" => $eventAbs, "Team" => 0, "TourId" => $_SESSION['TourId']));
                }
            }
        }

        if (MakeTeams($Societa, $Evento)) {
            jsonout($JSON);
        }

        if (MakeTeamsAbs($Societa, $Div, $Cl)) {
            jsonout($JSON);
        }
    }

    $JSON['error']=0;
    $JSON['which']=$Key;
    if($MyRow) {
		$JSON['id'] = $MyRow->QuId;
		$JSON['score'] = $MyRow->QuScore;
		$JSON['gold'] = $MyRow->QuGold;
		$JSON['xnine'] = $MyRow->QuXnine;
    }

    JsonOut($JSON);
}

JsonOut($JSON);
