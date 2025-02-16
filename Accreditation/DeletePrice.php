<?php
/*
													- DeletePrice.php -
	Elimina una coppia DivClass da EventClass
*/

	define('debug',false);

	require_once(dirname(dirname(__FILE__)) . '/config.php');

	if (!CheckTourSession() or !isset($_REQUEST['DelDivCl']) or !hasFullACL(AclCompetition, 'acSetup', AclReadWrite)) {
		print get_text('CrackError');
		exit;
	}

	$Errore=0;

	if (!IsBlocked(BIT_BLOCK_ACCREDITATION))
	{
		$Delete
			= "DELETE FROM AccPrice "
			. "WHERE APDivClass=" . StrSafe_DB($_REQUEST['DelDivCl']) . " "
			. "AND APTournament=" . StrSafe_DB($_SESSION['TourId']) . " ";
		$Rs=safe_w_sql($Delete);
		if (debug) print $Delete;

		if (safe_w_affected_rows()!=1)
			$Errore=1;
	}
	else
		$Errore=1;
	if (!debug)
		header('Content-Type: text/xml');

	print '<response>';
	print '<error>' . $Errore . '</error>';
	print '<divcl>' . $_REQUEST['DelDivCl'] . '</divcl>';
	print '</response>';
