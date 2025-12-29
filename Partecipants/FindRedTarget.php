<?php
/*
													- FindRedTarget.php -
	Cerca i targetno doppi e ritorna l'elenco.
	La funzione ajax si preoccuperà di colorare i doppioni
*/

require_once(dirname(__FILE__, 2) . '/config.php');

if (!CheckTourSession()) {
    print get_text('CrackError');
    exit;
}
checkFullACL(AclParticipants, 'pEntries', AclReadOnly, false);

$xml = '';
$Errore=0;

$MaxSession = 0;
$Select
    = "SELECT ToNumSession "
    . "FROM Tournament "
    . "WHERE ToId=" . StrSafe_DB($_SESSION['TourId']) . " ";
$Rs=safe_r_sql($Select);

if (safe_num_rows($Rs)==1) {
    $MyRow=safe_fetch($Rs);
    $MaxSession=$MyRow->ToNumSession;
}

if (isset($_REQUEST['Ses']) AND ((is_numeric($_REQUEST['Ses']) AND $_REQUEST['Ses']>0 AND $_REQUEST['Ses']<=$MaxSession) || (!is_numeric($_REQUEST['Ses']) AND $_REQUEST['Ses']=='*'))) {
    $atSql = createAvailableTargetSQL(0, $_SESSION['TourId']);
    $Select = "SELECT QuId, sq.Quanti, (FullTgtSession IS NOT NULL) AS ValidTarget "
        . "FROM Qualifications AS q "
        . "INNER JOIN (SELECT QuSession, QuTarget, QuLetter, COUNT(*) AS Quanti FROM Entries INNER JOIN Qualifications ON EnId = QuId AND EnTournament = " . StrSafe_DB($_SESSION['TourId']) . " WHERE QuTarget!=0 GROUP BY QuSession, QuTarget, QuLetter) AS sq ON q.QuSession = sq.QuSession AND q.QuTarget = sq.QuTarget AND q.QuLetter = sq.QuLetter "
        . "INNER JOIN Entries ON QuId=EnId AND EnTournament=" . StrSafe_DB($_SESSION['TourId']) . " "
        . "LEFT JOIN ($atSql) at ON q.QuSession=FullTgtSession AND q.QuTarget=FullTgtTarget AND q.QuLetter=FullTgtLetter "
        . "ORDER BY q.QuSession ASC , sq.QuSession ASC, sq.QuTarget ASC, sq.QuLetter ASC ";

    $Rs=safe_r_sql($Select);

    if (safe_num_rows($Rs)>0) {
        while ($MyRow=safe_fetch($Rs)) {
            $xml
                .= '<target>'
                 . '<id>' . $MyRow->QuId . '</id>'
                 . '<num>' . (!$MyRow->ValidTarget ? '0' : $MyRow->Quanti) . '</num>'
                 . '</target>';
        }
    }
}

header('Content-Type: text/xml');

print '<response>';
print '<error>' . $Errore . '</error>';
print $xml;
print '</response>';
