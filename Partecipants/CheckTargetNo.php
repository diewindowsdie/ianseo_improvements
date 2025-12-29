<?php
/*
													- CheckTargetNo.php -
	Cerifica il targetno in Partecipants.php
*/

	require_once(dirname(dirname(__FILE__)) . '/config.php');

	if (!CheckTourSession() || !isset($_REQUEST['d_q_QuSession']) || !isset($_REQUEST['d_q_QuTargetNo'])) {
		print get_text('CrackError');
		exit;
	}
    checkFullACL(AclParticipants, 'pEntries', AclReadOnly, false);

	$Errore=0;

	$TargetNo = '';


	if (trim($_REQUEST['d_q_QuTargetNo'])!='') {
		if (!preg_match('/^[0-9]{1,' . TargetNoPadding . '}[a-z]{1}$/i',$_REQUEST['d_q_QuTargetNo'])) {
			$Errore=1;
		} else {
			$TargetNo = intval(substr($_REQUEST['d_q_QuTargetNo'],0,-1));
            $TargetLet = strtoupper(substr($_REQUEST['d_q_QuTargetNo'],-1)) ;
            $atSql = createAvailableTargetSQL($_REQUEST['d_q_QuSession'],$_SESSION['TourId']);
			$Select = "SELECT * FROM ($atSql) at WHERE FullTgtTarget=" . $TargetNo . " AND FullTgtLetter= " . StrSafe_DB($TargetLet);
			$Rs=safe_r_sql($Select);
			if (!$Rs || safe_num_rows($Rs)!=1) {
				$TargetNo = $_REQUEST['d_q_QuTargetNo'];
				$Errore=1;
			}
		}
	}

    header('Content-Type: text/xml');
	print '<response>';
	print '<error>' . $Errore . '</error>';
	print '<targetno><![CDATA[' . $TargetNo.$TargetLet . ']]></targetno>';
	print '</response>';
