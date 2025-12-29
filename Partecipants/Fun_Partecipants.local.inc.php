<?php
	// tolto perché dovrebbe già essere incluso e fa casino ogni tanto
	// require_once(dirname(dirname(__FILE__)) . '/config.php');
	require_once('Common/Lib/Fun_DateTime.inc.php');
	require_once('Partecipants/Fun_Targets.php');

	define('GROUP_TYPE_NOGROUP',0);
	define('GROUP_TYPE_TARGET',1);
	define('GROUP_TYPE_LETTER',2);
	define('GROUP_TYPE_COUNTRY',3);
	define('GROUP_TYPE_CATEGORY',4);	// div-agecl-cl

/**
 * Ritorna un array con le persone.
 *
 * @param int $Id: id della persona se si vuole solo una riga
 * @param string $OrderBy: clausola order by
 * @return mixed[]: array con le persone
 */
function GetRows($Id=null,$OrderBy=null,$AllTargets=false)
{
	$ret=array();

	$DefTargets=getTargets();

	if ($OrderBy===null) {
		$OrderBy= "`Session` ASC, `Target` ASC, `Letter` ASC ";
	}

	$Errore = 0;

	$Select="";
	if (!$AllTargets) {
		$Select
			= "SELECT e.*,IF(EnDob!='0000-00-00',EnDob,'0000-00-00') AS Dob,c.CoCode,c.CoName,c2.CoCode AS CoCode2,c2.CoName AS CoName2,  c3.CoCode AS CoCode3,c3.CoName AS CoName3,"
			. "`q`.`QuSession` AS `Session`, `QuTarget` as `Target`, `QuLetter` as `Letter`, CONCAT(QuTarget, QuLetter) AS TargetNo, ToWhenFrom,TfName, "
			. "eextra.EdEmail, zextra.EdExtra locBib, cextra.EdExtra EnCaption "
			. "FROM Entries AS e LEFT JOIN Countries AS c ON e.EnCountry=c.CoId AND e.EnTournament=c.CoTournament "
			. "LEFT JOIN Countries AS c2 ON e.EnCountry2=c2.CoId AND e.EnTournament=c2.CoTournament "
			. "LEFT JOIN Countries AS c3 ON e.EnCountry3=c3.CoId AND e.EnTournament=c3.CoTournament "
			. "LEFT JOIN TargetFaces ON EnTournament=TfTournament AND EnTargetFace=TfId "
			. "LEFT JOIN ExtraData eextra ON eextra.EdType='E' and eextra.EdId=EnId "
			. "LEFT JOIN ExtraData zextra ON zextra.EdType='Z' and zextra.EdId=EnId "
			. "LEFT JOIN ExtraData cextra ON cextra.EdType='C' and cextra.EdId=EnId "
			. "INNER JOIN Qualifications AS q ON e.EnId=q.QuId "
			. "INNER JOIN Tournament ON EnTournament=ToId "
			. "WHERE e.EnTournament=" . StrSafe_DB($_SESSION['TourId'])
			. ($Id!='' ? " AND EnId=" . StrSafe_DB($Id) : '') . " "
			. "ORDER BY " . $OrderBy . " ";
	} else {
        $atSql = createAvailableTargetSQL(0, $_SESSION['TourId']);
		$Select
			= "(SELECT EnId,EnIocCode,EnTournament,EnDivision,EnClass,EnSubClass,EnAgeClass,"
				. "EnCountry,EnSubTeam,EnCountry2,EnCountry3,EnCtrlCode,Dob,"
				. "EnCode,EnName,EnFirstName,EnBadgePrinted,EnAthlete,"
				. "EnSex,EnWChair,EnSitting,EnIndClEvent,EnTeamClEvent,EnIndFEvent,EnTeamFEvent,EnTeamMixEvent,"
				. "EnDoubleSpace,EnPays,EnStatus,EnTargetFace,EnTimestamp,EnOdfShortname,TfName, "
				. "CoCode,CoName,CoCode2,CoName2,CoCode3,CoName3,"
				. "SUBSTRING(FullTgtSession,1,1) AS `Session`, `FullTgtTarget` as `Target`, `FullTgtLetter` as `Letter`, CONCAT(FullTgtTarget,FullTgtLetter) AS TargetNo, ToWhenFrom, EdEmail, EdExtra locBib, EnCaption "
			. "FROM ($atSql) at "
                ." LEFT JOIN (SELECT "
						. "EnId,EnIocCode,EnTournament,EnDivision,EnClass,EnSubClass,EnAgeClass,eextra.EdEmail,zextra.EdExtra, cextra.EdExtra EnCaption,"
						. "EnCountry,EnSubTeam,EnCountry2,EnCountry3,EnCtrlCode,IF(EnDob!='0000-00-00',EnDob,'0000-00-00') AS Dob,"
						. "EnCode,EnName,EnFirstName,EnBadgePrinted,EnAthlete,"
						. "EnSex,EnWChair,EnSitting,EnIndClEvent,EnTeamClEvent,EnIndFEvent,EnTeamFEvent,EnTeamMixEvent,"
						. "EnDoubleSpace,EnPays,EnStatus,EnTargetFace,EnTimestamp,EnOdfShortname,TfName, "
						. "c.CoCode AS CoCode,c.CoName AS CoName,c2.CoCode AS CoCode2,c2.CoName AS CoName2,c3.CoCode AS CoCode3,c3.CoName AS CoName3, QuSession, QuTarget, QuLetter, ToWhenFrom "
					. "FROM Entries AS e LEFT JOIN Countries AS c ON e.EnCountry=c.CoId AND e.EnTournament=c.CoTournament "
						. "LEFT JOIN Countries AS c2 ON e.EnCountry2=c2.CoId AND e.EnTournament=c2.CoTournament "
						. "LEFT JOIN Countries AS c3 ON e.EnCountry3=c3.CoId AND e.EnTournament=c3.CoTournament "
						. "LEFT JOIN TargetFaces ON EnTournament=TfTournament AND EnTargetFace=TfId "
						. "LEFT JOIN ExtraData eextra ON eextra.EdType='E' and eextra.EdId=EnId "
						. "LEFT JOIN ExtraData zextra ON zextra.EdType='Z' and zextra.EdId=EnId "
						. "LEFT JOIN ExtraData cextra ON cextra.EdType='C' and cextra.EdId=EnId "
						. "INNER JOIN Qualifications AS q ON e.EnId=q.QuId "
						. "INNER JOIN Tournament ON EnTournament=ToId "
						. "WHERE e.EnTournament=" . StrSafe_DB($_SESSION['TourId'])
						. ($Id!='' ? " AND EnId=" . StrSafe_DB($Id) : '') . ") AS sq ON QuSession=FullTgtSession AND QuTarget=FullTgtTarget AND QuLetter=FullTgtLetter) "
			. "UNION ALL "
			. "(SELECT EnId,EnIocCode,EnTournament,EnDivision,EnClass,EnSubClass,EnAgeClass,"
				. "EnCountry,EnSubTeam,EnCountry2,EnCountry3,EnCtrlCode,IF(EnDob!='0000-00-00',EnDob,'0000-00-00') AS Dob,"
				. "EnCode,EnName,EnFirstName,EnBadgePrinted,EnAthlete,"
				. "EnSex,EnWChair,EnSitting,EnIndClEvent,EnTeamClEvent,EnIndFEvent,EnTeamFEvent,EnTeamMixEvent,"
				. "EnDoubleSpace,EnPays,EnStatus,EnTargetFace,EnTimestamp,EnOdfShortname,TfName, "
				. "c.CoCode AS CoCode,c.CoName AS CoName,c2.CoCode AS CoCode2,c2.CoName AS CoName2,c3.CoCode AS CoCode3,c3.CoName AS CoName3,"
				. "`q`.`QuSession` AS `Session`, `q`.`QuTarget` as `Target`, `q`.`QuLetter` as `Letter`, CONCAT(q.QuTarget, q.QuLetter) AS TargetNo,ToWhenFrom,eextra.EdEmail, zextra.EdExtra locBib, cextra.EdExtra EnCaption "
			. "FROM "
				. "Entries LEFT JOIN Countries AS c ON EnCountry=c.CoId AND EnTournament=c.CoTournament "
				. "LEFT JOIN TargetFaces ON EnTournament=TfTournament AND EnTargetFace=TfId "
				. "LEFT JOIN Countries AS c2 ON EnCountry2=c2.CoId AND EnTournament=c2.CoTournament "
				. "LEFT JOIN Countries AS c3 ON EnCountry3=c3.CoId AND EnTournament=c3.CoTournament "
				. "LEFT JOIN ExtraData eextra ON eextra.EdType='E' and eextra.EdId=EnId "
				. "LEFT JOIN ExtraData zextra ON zextra.EdType='Z' and zextra.EdId=EnId "
				. "LEFT JOIN ExtraData cextra ON cextra.EdType='C' and cextra.EdId=EnId "
				. "INNER JOIN Qualifications AS q ON EnId=q.QuId "
				. "INNER JOIN Tournament ON EnTournament=ToId "
				. "WHERE EnTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND QuTarget=0 "
				. ($Id!='' ? " AND EnId=" . StrSafe_DB($Id) : '') . ") "
				. "ORDER BY " . $OrderBy . " ";
	}
	//print $Select;exit;
	$Rs=safe_r_sql($Select);

	if (safe_num_rows($Rs)>0) {
		while ($MyRow=safe_fetch($Rs)) {
			if ($MyRow->EnId!==null) {
				if(empty($DefTargets[$MyRow->EnDivision][$MyRow->EnClass])) {
					// the target is missing for this entry... so sets the EnTargetFace to 0
					safe_w_sql("update Entries set EnTargetFace=0 where EnId=$MyRow->EnId");
					$MyRow->EnTargetFace=0;
				} elseif(empty($DefTargets[$MyRow->EnDivision][$MyRow->EnClass][$MyRow->EnTargetFace])) {
					// the assigned target face doesn't exists so resets to the first one (default)
					reset($DefTargets[$MyRow->EnDivision][$MyRow->EnClass]);
					$TfId = key($DefTargets[$MyRow->EnDivision][$MyRow->EnClass]);
					safe_w_sql("update Entries set EnTargetFace=$TfId where EnId=$MyRow->EnId");
					$MyRow->EnTargetFace=$TfId;
				}
			}

			$ret[]=array(
				'id' => $MyRow->EnId,
				'ioccode' => $MyRow->EnIocCode,
				'code' => $MyRow->EnCode,
				'locCode' => $MyRow->locBib,
				'caption' => $MyRow->EnCaption,
				'status' => $MyRow->EnStatus,
				'session' => $MyRow->Session!=0 ? $MyRow->Session : '',
				'targetno' => $MyRow->TargetNo,
				'firstname' => stripslashes($MyRow->EnFirstName ?? ''),
				'name' => stripslashes($MyRow->EnName ?? ''),
				'tvname' => stripslashes($MyRow->EnOdfShortname ?? ''),
				'email' => stripslashes($MyRow->EdEmail ?? ''),
				'sex_id' => $MyRow->EnSex,
				'sex' =>  $MyRow->EnId!==null ? $MyRow->EnSex==0 ? get_text('ShortMale','Tournament') : get_text('ShortFemale','Tournament') : '',
				'ctrl_code' => $MyRow->EnCtrlCode,
				'dob' => $MyRow->Dob,
				'country_id' => $MyRow->EnCountry,
				'country_code' => $MyRow->CoCode,
				'country_name' => stripslashes($MyRow->CoName ?? ''),
				'sub_team' => $MyRow->EnSubTeam,
				'country_id2' => $MyRow->EnCountry2,
				'country_code2' => $MyRow->CoCode2,
				'country_name2' => stripslashes($MyRow->CoName2 ?? ''),
				'country_id3' => $MyRow->EnCountry3,
				'country_code3' => $MyRow->CoCode3,
				'country_name3' => stripslashes($MyRow->CoName3 ?? ''),
				'division' => $MyRow->EnDivision,
				'class' => $MyRow->EnClass,
				'ageclass' => $MyRow->EnAgeClass,
				'subclass' => $MyRow->EnSubClass,
				'targetface' => $MyRow->EnTargetFace,
				'targetface_name' => $MyRow->TfName,
				'indcl'=>$MyRow->EnIndClEvent,
				'teamcl'=>$MyRow->EnTeamClEvent,
				'indfin'=>$MyRow->EnIndFEvent,
				'teamfin'=>$MyRow->EnTeamFEvent,
				'mixteamfin'=>$MyRow->EnTeamMixEvent,
				'wc'=>$MyRow->EnWChair,
				'double'=>$MyRow->EnDoubleSpace,
			);
		}
	}

	return $ret;
}

function Params4Recalc($ath)
{
	$indFEvent=$teamFEvent=$country=$div=$cl=$zero=null;

	$q="
		SELECT
			EnIndFEvent, EnTeamFEvent, EnCountry, EnDivision, EnClass, EnSubClass, EnStatus, QuScore
		FROM
			Entries
			INNER JOIN
				Qualifications
			ON EnId=QuId
		WHERE
			EnId={$ath}
	";

	$rs=safe_r_sql($q);

	if ($rs && safe_num_rows($rs)==1)
	{
		$row=safe_fetch($rs);

		$indFEvent=$row->EnIndFEvent;
		$teamFEvent=$row->EnTeamFEvent;
		$country=$row->EnCountry;
		$div=$row->EnDivision;
		$cl=$row->EnClass;
		$subCl=$row->EnSubClass;
		$zero=true;
		if ($row->EnStatus<=1) {
			$zero=($row->QuScore==0);
		}

		return array($indFEvent, $teamFEvent, $country, $div, $cl, $subCl, $zero);
	}
	else
		return false;
}

function RecalculateShootoffAndTeams($indFEvent,$teamFEvent,$country,$div,$cl,$subCl,$zero)
{
	$Errore=0;

	if ($zero)
		return 0;

// scopro se $div e $cl sono per gli atleti
	$q="SELECT (DivAthlete AND ClAthlete) AS isAth
		FROM Divisions
		INNER JOIN Classes
		ON DivTournament=ClTournament
		WHERE
			DivTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND (DivAthlete AND ClAthlete)=1
			AND DivId=" . StrSafe_DB($div) . " AND ClId=" . StrSafe_DB($cl) . "
	";
	//print $q.'<br><br>';
	$rs=safe_r_sql($q);

	if ($rs && safe_num_rows($rs)==1)
	{
		$queries=array();

		$date=date('Y-m-d H:i:s');

	// shootoff degli individuali a zero (e reset della RankFinal)
		if ($indFEvent==1)
		{
			$queries[]="
				UPDATE
					Events
					INNER JOIN
						EventClass
					ON EvCode=EcCode AND EvTeamEvent='0' AND EvTournament=EcTournament AND EcTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND
					EcDivision=" . StrSafe_DB($div) . " AND EcClass=" . StrSafe_DB($cl) . " and if(EcSubClass='', true, EcSubClass='$subCl')
					INNER JOIN
						Individuals
					ON EvCode=IndEvent AND EvTournament=IndTournament AND EvTeamEvent=0 AND EvTournament={$_SESSION['TourId']}
				SET
					EvShootOff='0',
					EvE1ShootOff='0',
					EvE2ShootOff='0',
					IndRankFinal=0,
					IndTimestampFinal='{$date}'
			";
		}
	// shootoff dei team a zero
		if ($teamFEvent==1)
		{
			$queries[]="
				UPDATE
					Events
					INNER JOIN
						EventClass
					ON EvCode=EcCode AND EvTeamEvent='1' AND EvTournament=EcTournament AND EcTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND
					EcDivision=" . StrSafe_DB($div) . " AND EcClass=" . StrSafe_DB($cl) . " and if(EcSubClass='', true, EcSubClass='$subCl')
				SET
					EvShootOff='0',
					EvE1ShootOff='0',
					EvE2ShootOff='0'
			";
		}

		foreach ($queries as $q)
		{
			//print $q.'<br><br>';
			$rs=safe_w_sql($q);
		}
		set_qual_session_flags();

	// teams
		if (MakeTeams($country, $div . $cl))
		{
			$Errore=1;
			//print 'team error';
		}
		else
		{
			if (MakeTeamsAbs($country,$div,$cl))
			{
				$Errore=1;
				//print 'absteam error';
			}
		}

	}

	//exit;
	return $Errore;
}

function getAllDivCl()
{
	$divs=array('--');
	$cls=array('--');
	$agecls=array('--');

	$q="SELECT DivId FROM Divisions WHERE DivTournament={$_SESSION['TourId']} ORDER BY DivViewOrder ASC";
	$r=safe_r_sql($q);
	if ($r)
	{
		while ($row=safe_fetch($r))
		{
			$divs[]=$row->DivId;
		}
	}

	$q="SELECT ClId FROM Classes WHERE ClTournament={$_SESSION['TourId']} ORDER BY ClViewOrder ASC";
	$r=safe_r_sql($q);
	if ($r)
	{
		while ($row=safe_fetch($r))
		{
			$cls[]=$row->ClId;
			$agecls[]=$row->ClId;
		}
	}

	return array($divs,$agecls,$cls);
}

