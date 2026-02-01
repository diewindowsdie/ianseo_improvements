<?php
	define('debug',false);	// settare a true per l'output di debug

	require_once(dirname(dirname(__FILE__)) . '/config.php');
	CheckTourSession(true);
    checkFullACL(AclParticipants, 'pTarget', AclReadWrite);
	require_once('Common/Fun_FormatText.inc.php');
	require_once('Common/Fun_Sessions.inc.php');
    $IncludeJquery = true;

	$JS_SCRIPT=array(
		'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Common/ajax/ObjXMLHttpRequest.js"></script>',
		'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Partecipants/Fun_AJAX_SetTarget_default.js"></script>',
		'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Common/js/Fun_JS.inc.js"></script>',
		'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Partecipants/Fun_JS.js"></script>',
		);

	$PAGE_TITLE=get_text('ManualTargetAssignment','Tournament');

	include('Common/Templates/head.php');
?>
<form name="Frm" method="GET" action="">
<?php
	$QueryString = $_SERVER['QUERY_STRING'];
	$Arr = explode('&',$QueryString);
	foreach ($Arr as $Key => $Value) {
		list($ff,$vv)=explode('=',$Value);
		if($ff!='Event') {
		    print '<input type="hidden" name="' . $ff . '" value="' . $vv . '">';
		}
	}
?>
<table class="Tabella">
<tr><th class="Title" colspan="2"><?php print get_text('ManualTargetAssignment','Tournament');?></th></tr>
<tr class="Divider"><TD  colspan="2"></TD></tr>
<TR><TD width="70%">
<?php
	$MaxSession = 0;
	print get_text('SelectSession','Tournament') . ': ';

	$sessions=GetSessions('Q');

	$MaxSession=count($sessions);

	$ComboSes = array(0 => '--');

	if ($MaxSession>0){
		foreach ($sessions as $s) {
			print '<a class="Link" href="' . $_SERVER['PHP_SELF'] . '?' . (isset($_REQUEST['Event']) ? 'Event=' . $_REQUEST['Event'] . '&amp;' : '') . 'Ses=' . $s->SesOrder . '">' . ($_REQUEST['Ses']==$s->SesOrder ? '[' : '') . $s->SesOrder . ($_REQUEST['Ses']==$s->SesOrder ? ']' : '') . '</a> ';
			$ComboSes[$s->SesOrder]=$s->Descr;
		}

		print '<a class="Link" href="' . $_SERVER['PHP_SELF'] . '?' . (isset($_REQUEST['Event']) ? 'Event=' . $_REQUEST['Event'] . '&amp;' : '') . 'Ses=*">' . ($_REQUEST['Ses']=='*' ? '[' : '')  . get_text('AllsF','Tournament') . ($_REQUEST['Ses']=='*' ? ']' : '') . '</a> ';
	} else {
		exit;
	}
?>
</TD>
<td><?php print get_text('FilterOnDivCl','Tournament');?>:&nbsp;
<input type="text" name="Event" id="Event" value="<?php print (isset($_REQUEST['Event']) ? $_REQUEST['Event'] : '');?>">&nbsp;
<input type="submit" value="<?php print get_text('CmdOk');?>">
</td>
</TR>
<tr class="Divider"><td colspan="2"></td></tr>
<tr><td class="Bold" colspan="2"><input type="checkbox" name="chk_BlockAutoSave" id="chk_BlockAutoSave" value="1"><?php echo get_text('CmdBlocAutoSave') ?></td></tr>
</table>
</form>
<br>
<?php
    $renderSwapButtons = true;
	if (isset($_REQUEST['Ses']) && ((is_numeric($_REQUEST['Ses']) && $_REQUEST['Ses']>0 && $_REQUEST['Ses']<=$MaxSession) || (!is_numeric($_REQUEST['Ses']) && $_REQUEST['Ses']=='*')))	{

		$OrderBy = "QuSession ASC, QuTarget ASC, QuLetter ASC, EnDivision, EnClass ";

		if (isset($_REQUEST['ordTarget']) && ($_REQUEST['ordTarget']=='ASC' || $_REQUEST['ordTarget']=='DESC')) {
            $OrderBy = "QuSession " . $_REQUEST['ordTarget'] . ", QuTarget " . $_REQUEST['ordTarget'] . ", QuLetter " . $_REQUEST['ordTarget'] . ", EnDivision, EnClass ";
        } elseif (isset($_REQUEST['ordCode']) && ($_REQUEST['ordCode']=='ASC' || $_REQUEST['ordCode']=='DESC')) {
            $OrderBy = "EnCode " . $_REQUEST['ordCode'] . " ";
            $renderSwapButtons = false;
        } elseif (isset($_REQUEST['ordName']) && ($_REQUEST['ordName']=='ASC' || $_REQUEST['ordName']=='DESC')) {
            $OrderBy = "EnFirstName " . $_REQUEST['ordName'] . ",EnName " . $_REQUEST['ordName'] . " ";
            $renderSwapButtons = false;
        } elseif (isset($_REQUEST['ordCountry']) && ($_REQUEST['ordCountry']=='ASC' || $_REQUEST['ordCountry']=='DESC')) {
            $OrderBy = "EnCountry " . $_REQUEST['ordCountry'] . " ";
            $renderSwapButtons = false;
        } elseif (isset($_REQUEST['ordDiv']) && ($_REQUEST['ordDiv']=='ASC' || $_REQUEST['ordDiv']=='DESC')) {
            $OrderBy = "EnDivision " . $_REQUEST['ordDiv'] . " ";
            $renderSwapButtons = false;
        } elseif (isset($_REQUEST['ordCl']) && ($_REQUEST['ordCl']=='ASC' || $_REQUEST['ordCl']=='DESC')) {
            $OrderBy = "EnClass " . $_REQUEST['ordCl'] . " ";
            $renderSwapButtons = false;
        } elseif (isset($_REQUEST['ordScore']) && ($_REQUEST['ordScore']=='ASC' || $_REQUEST['ordScore']=='DESC')) {
            $OrderBy = "QuScore " . $_REQUEST['ordScore'] . ", QuGold " . $_REQUEST['ordScore'] . ", QuXNine " . $_REQUEST['ordScore'];
            $renderSwapButtons = false;
        }


        $athletes = array();
        global $athletesPerTarget;
        $athletesPerTarget = array();
        //если требуется - запросим отдельно количество спортсменов на каждом щите
        if ($renderSwapButtons) {
            $query = "select QuSession, TargetNo, count(TargetNo) Count
                        from (SELECT QuSession,
                            QuTarget AS TargetNo
                            FROM Entries
                                left JOIN Qualifications ON EnId = QuId
                            WHERE EnTournament =" . StrSafe_DB($_SESSION['TourId']) . " AND EnAthlete = 1 " .
                    ($_REQUEST['Ses']!='*' ? "AND QuSession in (0," . intval($_REQUEST['Ses']) . ") " : ' ');
            if(isset($_REQUEST["Event"]) AND preg_match("/^[0-9A-Z%_]+$/i",$_REQUEST["Event"])) {
                $query .= " AND CONCAT(TRIM(EnDivision),TRIM(EnClass)) LIKE " . StrSafe_DB($_REQUEST["Event"]) . " ";
            }
            $query .= ") t
                        group by QuSession, TargetNo order by TargetNo ASC";

            $resultSet = safe_r_SQL($query);
            while ($row = safe_fetch($resultSet)) {
                $athletes[$row->QuSession][$row->TargetNo] = $row->Count;
            }

            //запросим максимальное количество спортсменов на щит в сессии(сессиях)
            $query = "select SesOrder, SesAth4Target from Session where SesType = 'Q' and SesTournament = " . StrSafe_DB($_SESSION['TourId']);
            $resultSet = safe_r_SQL($query);
            while ($row = safe_fetch($resultSet)) {
                $athletesPerTarget[$row->SesOrder] = $row->SesAth4Target;
            }
        }

        //Для всех смен, попадающих под фильтр смены - если есть хоть кто-то с мишенью 0 - значит жеребьевка не полная и кнопки смены мест показывать не нужно
        foreach ($athletes as $session => $sessionData) {
            if ($_REQUEST['Ses']=='*' || $_REQUEST["Ses"] == $session) {
                $renderSwapButtons &= !isset($sessionData["0"]);
            }
        }

        echo '<table class="Tabella">'.
                '<tr>'.
                '<th class="Title w-15"><a class="LinkRevert" href="' . $_SERVER['PHP_SELF'] . '?' . (isset($_REQUEST['Event']) ? 'Event=' . $_REQUEST['Event'] . '&amp;' : '') . 'Ses=' . $_REQUEST['Ses'] . '&amp;ordTarget=' . (isset($_REQUEST['ordTarget']) ? ( $_REQUEST['ordTarget']=='ASC' ? 'DESC' : 'ASC') : 'ASC') . '">' . get_text('Session') . '</a></th>'.
                ($renderSwapButtons ? ('<th class="Title w-5">' . get_text("SwapOnTarget") . '</th>') : "").
                '<th class="Title w-5"><a class="LinkRevert" href="' . $_SERVER['PHP_SELF'] . '?' . (isset($_REQUEST['Event']) ? 'Event=' . $_REQUEST['Event'] . '&amp;' : '') . 'Ses=' . $_REQUEST['Ses'] . '&amp;ordTarget=' . (isset($_REQUEST['ordTarget']) ? ( $_REQUEST['ordTarget']=='ASC' ? 'DESC' : 'ASC') : 'ASC') . '">' . get_text('Target') . '</a></th>'.
                '<th class="Title w-5"><a class="LinkRevert" href="' . $_SERVER['PHP_SELF'] . '?' . (isset($_REQUEST['Event']) ? 'Event=' . $_REQUEST['Event'] . '&amp;' : '') . 'Ses=' . $_REQUEST['Ses'] . '&amp;ordCode=' . (isset($_REQUEST['ordCode']) ? ( $_REQUEST['ordCode']=='ASC' ? 'DESC' : 'ASC') : 'ASC') . '">' . get_text('Code','Tournament') . '</a></th>'.
                '<th class="Title w-20"><a class="LinkRevert" href="' . $_SERVER['PHP_SELF'] . '?' . (isset($_REQUEST['Event']) ? 'Event=' . $_REQUEST['Event'] . '&amp;' : '') . 'Ses=' . $_REQUEST['Ses'] . '&amp;ordName=' . (isset($_REQUEST['ordName']) ? ( $_REQUEST['ordName']=='ASC' ? 'DESC' : 'ASC') : 'ASC') . '">' . get_text('Athlete') . '</a></th>'.
                '<th class="Title w-20"><a class="LinkRevert" href="' . $_SERVER['PHP_SELF'] . '?' . (isset($_REQUEST['Event']) ? 'Event=' . $_REQUEST['Event'] . '&amp;' : '') . 'Ses=' . $_REQUEST['Ses'] . '&amp;ordCountry=' . (isset($_REQUEST['ordCountry']) ? ($_REQUEST['ordCountry']=='ASC' ? 'DESC' : 'ASC') : 'ASC') . '">' . get_text('Country') . '</a></th>'.
                '<th class="Title w-10"><a class="LinkRevert" href="' . $_SERVER['PHP_SELF'] . '?' . (isset($_REQUEST['Event']) ? 'Event=' . $_REQUEST['Event'] . '&amp;' : '') . 'Ses=' . $_REQUEST['Ses'] . '&amp;ordScore=' . (isset($_REQUEST['ordScore']) ? ( $_REQUEST['ordScore']=='ASC' ? 'DESC' : 'ASC') : 'ASC') . '">' . get_text('Score', 'Tournament') . '</a></th>'.
                '<th class="Title w-5">' . get_text('WheelChair', 'Tournament') . '</th>'.
                '<th class="Title w-5"><a class="LinkRevert" href="' . $_SERVER['PHP_SELF'] . '?' . (isset($_REQUEST['Event']) ? 'Event=' . $_REQUEST['Event'] . '&amp;' : '') . 'Ses=' . $_REQUEST['Ses'] . '&amp;ordDiv=' . (isset($_REQUEST['ordDiv']) ? ($_REQUEST['ordDiv']=='ASC' ? 'DESC' : 'ASC') : 'ASC') . '">' . get_text('Division') . '</a></th>'.
                '<th class="Title w-5"><a class="LinkRevert" href="' . $_SERVER['PHP_SELF'] . '?' . (isset($_REQUEST['Event']) ? 'Event=' . $_REQUEST['Event'] . '&amp;' : '') . 'Ses=' . $_REQUEST['Ses'] . '&amp;ordCl=' . (isset($_REQUEST['ordCl']) ? ($_REQUEST['ordCl']=='ASC' ? 'DESC' : 'ASC') : 'ASC') . '">' . get_text('Class') . '</a></th>'.
                '<th class="Title w-10">' . get_text('TargetType') . '</th>'.
                '</tr>';

        function getTargetSwapButtonsHtml($target, $session) {
            global $athletesPerTarget;
            switch($athletesPerTarget[$session]) {
                case 2:
                    return "<input style=\"margin: 1px\" type=\"button\" value=\"A <> B\" onclick=\"swapOnTarget([['A', 'B']], '" . $target . "', '" . $session . "')\" />";
                case 3:
                    return "<input style=\"margin: 1px\" type=\"button\" value=\"A <> C\" onclick=\"swapOnTarget([['A', 'C']], '" . $target . "', '" . $session . "')\" />" .
                            "<br/>" .
                            "<input style=\"margin: 1px\" type=\"button\" value=\"B <> C\" onclick=\"swapOnTarget([['B', 'C']], '" . $target . "', '" . $session . "')\" />";
                case 4:
                    return "<input style=\"margin: 1px\" type=\"button\" value=\"A <> C\" onclick=\"swapOnTarget([['A', 'C']], '" . $target . "', '" . $session . "')\" />" .
                            "<input style=\"margin: 1px\" type=\"button\" value=\"B <> D\" onclick=\"swapOnTarget([['B', 'D']], '" . $target . "', '" . $session . "')\" />" .
                            "<br/>" .
                            "<input style=\"margin: 1px\" type=\"button\" value=\"AB <> CD\" onclick=\"swapOnTarget([['A', 'C'], ['B', 'D']], '" . $target . "', '" . $session . "')\" />";
                case 5:
                    return "<input style=\"margin: 1px\" type=\"button\" value=\"A <> C\" onclick=\"swapOnTarget([['A', 'C']], '" . $target . "', '" . $session . "')\" />" .
                            "<input style=\"margin: 1px\" type=\"button\" value=\"B <> D\" onclick=\"swapOnTarget([['B', 'D']], '" . $target . "', '" . $session . "')\" />" .
                            "<br/>" .
                            "<input style=\"margin: 1px\" type=\"button\" value=\"A <> E\" onclick=\"swapOnTarget([['A', 'E']], '" . $target . "', '" . $session . "')\" />" .
                            "<input style=\"margin: 1px\" type=\"button\" value=\"B <> E\" onclick=\"swapOnTarget([['B', 'E']], '" . $target . "', '" . $session . "')\" />" .
                            "<br/>" .
                            "<input style=\"margin: 1px\" type=\"button\" value=\"C <> E\" onclick=\"swapOnTarget([['C', 'E']], '" . $target . "', '" . $session . "')\" />" .
                            "<input style=\"margin: 1px\" type=\"button\" value=\"D <> E\" onclick=\"swapOnTarget([['D', 'E']], '" . $target . "', '" . $session . "')\" />" .
                            "<br/>" .
                            "<input style=\"margin: 1px\" type=\"button\" value=\"AB <> CD\" onclick=\"swapOnTarget([['A', 'C'], ['B', 'D']], '" . $target . "', '" . $session . "')\" />";
                case 6:
                    return "<input style=\"margin: 1px\" type=\"button\" value=\"AB <> CD\" onclick=\"swapOnTarget([['A', 'C'], ['B', 'D']], '" . $target . "', '" . $session . "')\" />" .
                            "<br/>" .
                            "<input style=\"margin: 1px\" type=\"button\" value=\"AB <> EF\" onclick=\"swapOnTarget([['A', 'E'], ['B', 'F']], '" . $target . "', '" . $session . "')\" />" .
                            "<br/>" .
                            "<input style=\"margin: 1px\" type=\"button\" value=\"CD <> EF\" onclick=\"swapOnTarget([['C', 'E'], ['D', 'F']], '" . $target . "', '" . $session . "')\" />";

                default:
                    return "";
            }
        }

		$Select = "SELECT EnId,EnCode,EnName,EnFirstName,EnSex,EnId,EnTournament,EnDivision,EnClass,EnCountry,EnStatus, EnWChair, " .
			"CoCode,CoName,QuSession, QuTarget, CONCAT(QuTarget, QuLetter) AS TargetNo, TfName, QuScore, QuGold, QuXNine " .
			"FROM Entries INNER JOIN Qualifications ON EnId=QuId ".
			"INNER JOIN Countries ON EnCountry=CoId AND EnTournament=CoTournament " .
            "LEFT JOIN TargetFaces ON EnTournament=TfTournament AND EnTargetFace=TfId " .
			"WHERE EnTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND EnAthlete=1 ".
			($_REQUEST['Ses']!='*' ? "AND QuSession in (0," . intval($_REQUEST['Ses']) . ") " : ' ');
			if(isset($_REQUEST["Event"]) AND preg_match("/^[0-9A-Z%_]+$/i",$_REQUEST["Event"])) {
                $Select .= " AND CONCAT(TRIM(EnDivision),TRIM(EnClass)) LIKE " . StrSafe_DB($_REQUEST["Event"]) . " ";
            }
			$Select.= "ORDER BY " . $OrderBy;
		$Rs=safe_r_sql($Select);

        $previousTarget = '';
        $rowClassSuffix = 1;
        $rowspan = 1;
        $newTarget = false;
		if (safe_num_rows($Rs)>0) {
			while ($MyRow=safe_fetch($Rs)) {
                //если хоть у кого-то есть мишень 0 - значит жеребьевка не полная и кнопки смены мишеней показывать не нужно
                $renderSwapButtons &= $MyRow->TargetNo !== "0";
                $newTarget = $previousTarget !== $MyRow->QuTarget;
                if ($newTarget) {
                    $rowClassSuffix ^= 1;
                    $rowspan = $athletes[$MyRow->QuSession][$MyRow->QuTarget];
                }
                $previousTarget =$MyRow->QuTarget;
				$RowStyle='Background' . $rowClassSuffix;
				switch ($MyRow->EnStatus) {
					case 0:
						$RowStyle = 'Background' . $rowClassSuffix;
						break;
					case 1:
						$RowStyle = 'CanShoot';
						break;
					case 5:
						$RowStyle = 'UnknownShoot';
						break;
					case 8:
						$RowStyle = 'CouldShoot';
						break;
					case 9:
						$RowStyle = 'NoShoot';
						break;
				}
				echo '<tr id="Row_' . $MyRow->EnId . '" class="' . $RowStyle . '">';

				echo '<td class="Center"><select ' . ($MyRow->EnStatus>8 ? ' disabled ' : '') . 'name="d_q_QuSession_' . $MyRow->EnId . '" id="d_q_QuSession_' . $MyRow->EnId . '" onBlur="javascript:UpdateSession(\'d_q_QuSession_' . $MyRow->EnId . '\');	">';
				foreach ($ComboSes as $Key => $Value) {
                    echo '<option value="' . $Key . '"' . ($MyRow->QuSession == $Key ? ' selected' : '') . '>' . $Value . '</option>';
                }
				echo '</select></td>';
                if ($renderSwapButtons) {
                    if ($newTarget) {
                        echo '<td class="Center Background' . $rowClassSuffix . '" rowspan="' . $rowspan . '">' . getTargetSwapButtonsHtml($MyRow->QuTarget, $MyRow->QuSession) . '</td>';
                    } else if (empty($MyRow->TargetNo)) {
                        echo '<td class="Center"></td>';
                    }
                }
				echo '<td class="Center">'.
				    '<input type="text" size="5" maxlength="5" name="d_q_QuTargetNo_' . $MyRow->EnId . '" id="d_q_QuTargetNo_' . $MyRow->EnId . '" value="' . (!empty($MyRow->TargetNo) ? $MyRow->TargetNo : '') . '"' . ($MyRow->QuSession==0 || $MyRow->EnStatus>8 ? ' readonly' : '') . ' onBlur="javascript:UpdateTargetNo(\'d_q_QuTargetNo_' . $MyRow->EnId . '\',\'' . $_REQUEST['Ses'] . '\');">'.
				    '</td>';
				echo '<td class="Center">' . (empty($MyRow->EnCode) ? '&nbsp;' : $MyRow->EnCode) . '</td>';
				echo '<td>' . ((!empty($MyRow->EnFirstName) OR !empty($MyRow->EnName)) ? $MyRow->EnFirstName . ' ' . $MyRow->EnName : '&nbsp;') . '</td>';
				echo '<td class="w-15">' . (empty($MyRow->CoName) ? '&nbsp;' : $MyRow->CoName) . '</td>';
                echo '<td class="Right NoWrap">' . (empty($MyRow->QuScore) ? '&nbsp;' : $MyRow->QuScore . ' / ' . $MyRow->QuGold . ' / ' . $MyRow->QuXNine) . '</td>';
				echo '<td class="Center">' . ($MyRow->EnWChair ? 'X' : '&nbsp;') . '</td>';
                echo '<td class="Center">' . (empty($MyRow->EnDivision) ? '&nbsp;' : $MyRow->EnDivision) . '</td>';
				echo '<td class="Center">' . (empty($MyRow->EnClass) ? '&nbsp;' : $MyRow->EnClass) . '</td>';
                echo '<td class="Center">' . (empty($MyRow->TfName) ? '&nbsp;' : get_text($MyRow->TfName, 'Tournament', '', true)) . '</td>';

                echo '</tr>';
			}
		}
		echo '</table>';
	}
?>
<div id="idOutput"></div>
<script type="text/javascript">FindRedTarget('<?php print $_REQUEST['Ses'];?>');</script>
<?php
	include('Common/Templates/tail.php');
?>
