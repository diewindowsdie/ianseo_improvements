<?php
	require_once(dirname(dirname(__FILE__)) . '/config.php');
	
	if (!CheckTourSession() || !isset($_REQUEST['row']) || !isset($_REQUEST['cl']) OR !hasFullACL(AclCompetition, 'acSetup', AclReadWrite))	{
		print get_text('CrackError');
		exit;
	}

	
	$Errore=0;
	
	if (!IsBlocked(BIT_BLOCK_ACCREDITATION))
	{
		$delete
			= "DELETE FROM AccColors "
			. "WHERE AcDivClass=" . StrSafe_DB($_REQUEST['cl']) . " AND AcTournament=" . StrSafe_DB($_SESSION['TourId']) . " ";	
		$rs=safe_w_sql($delete);
		
		if (!$rs)
			$Errore=1;
	}
	else
		$Errore=1;
	
	
	header('Content-Type: text/xml');
		
	print '<response>';
	print '<error>' . $Errore . '</error>';
	print '<row>' . $_REQUEST['row'] . '</row>';		
	print '</response>';