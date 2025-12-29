<?php
/*
													- UpdateTargetNo.php -
	La pagina aggiorna il TargetNo del tizio in Qualifications se la sessione è settata
*/

require_once(dirname(__FILE__, 2) . '/config.php');

if (!CheckTourSession()) {
    print get_text('CrackError');
    exit;
}
checkFullACL(AclParticipants, 'pTarget', AclReadWrite, false);

$Errore=0;
$Id='#';
$Msg = get_text('CmdOk');
$PadValue='#';
$Doppi=0;

if (!IsBlocked(BIT_BLOCK_PARTICIPANT)) {
    foreach ($_REQUEST as $Key => $Value) {
        if (substr($Key,0,2)=='d_') {

            $Campo = '';
            $Chiave = '';

            list(,,$Campo,$Chiave) = explode('_',$Key);
            $Id=$Chiave;
                if (preg_match('/^[0-9]{1,' . TargetNoPadding . '}[a-z]{1}$/i',$Value)) {
                    // verifico che in db ci sia settata la sessione != 0
                    $SelectSes = "SELECT QuSession  FROM Qualifications WHERE QuId=" . StrSafe_DB($Chiave);
                    $RsS=safe_r_sql($SelectSes);
                    if (safe_num_rows($RsS)==1 and $RowSes=safe_fetch($RsS)) {
                        $TargetNo = intval(substr($Value,0,-1));
                        $TargetLet = strtoupper(substr($Value,-1)) ;
                        $PadValue = $TargetNo.$TargetLet;
                        $atSql = createAvailableTargetSQL($RowSes->QuSession, $_SESSION['TourId']);
                        $Select = "SELECT * FROM ($atSql) at WHERE FullTgtTarget=" . $TargetNo . " AND FullTgtLetter= " . StrSafe_DB($TargetLet);
                        $RsSel=safe_r_sql($Select);
                        if (safe_num_rows($RsSel)==1){
                            $Update = "UPDATE Qualifications SET QuTarget=".$TargetNo.", QuLetter='".$TargetLet."', QuTimestamp=QuTimestamp WHERE QuId=" . StrSafe_DB($Chiave);
                            $RsUp=safe_w_sql($Update);
                            if(safe_w_affected_rows()) {
                                safe_w_sql("update Entries set EnTimestamp='".date('Y-m-d H:i:s')."' where EnId='{$Chiave}'");
                                safe_w_sql("UPDATE Qualifications SET QuBacknoPrinted=0, QuTimestamp=QuTimestamp WHERE QuId='{$Chiave}'");
                            }
                        } else {
                            $Errore = 1;
                        }
                    } else {
                        $Errore = 1;
                    }
                } else {
                    $Update = "UPDATE Qualifications SET QuTarget=0, QuLetter='', QuBacknoPrinted=0, QuTimestamp=QuTimestamp WHERE QuId=" . StrSafe_DB($Chiave);
                    $RsUp=safe_w_sql($Update);
                    if(safe_w_affected_rows()) {
                        safe_w_sql("update Entries set EnTimestamp='".date('Y-m-d H:i:s')."' where EnId='{$Chiave}'");
                    }
                }
        }
    }
} else {
    $Errore = 1;
}

header('Content-Type: text/xml');

print '<response>';
print '<error>' . $Errore . '</error>';
print '<pad_value>' . $PadValue . '</pad_value>';
print '<id>' . $Id . '</id>';
print '<ses>' . $_REQUEST['Ses'] . '</ses>';
print '</response>';
