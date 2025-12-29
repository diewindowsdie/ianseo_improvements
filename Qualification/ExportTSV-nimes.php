<?php
require_once(dirname(__FILE__, 2) . '/config.php');
require_once('Common/Fun_FormatText.inc.php');

if (!CheckTourSession()) {
    print get_text('CrackError');
    exit;
}
checkFullACL(AclQualification, '', AclReadOnly);

$ToCode = getCodeFromId($_SESSION['TourId']);

$atSql = createAvailableTargetSQL();

$MyQuery = "SELECT EnCode as Bib, EnName AS Name, EnFirstName AS FirstName, EnDob, FullTgtSession AS Session, CONCAT(FullTgtTarget,FullTgtLetter) AS TargetNo,";
$MyQuery.= " CoCode AS NationCode, CoName AS Nation, EnClass, EnDivision AS DivCode, EnAgeClass, EnSubClass as SubClass,  ";
for ($i=1;$i<=8;++$i)
    $MyQuery.= "QuD" . $i . "Score, QuD" . $i . "Rank,QuD" . $i . "Gold,QuD" . $i . "Xnine, ";
$MyQuery.= "QuScore,QuClRank,QuGold,QuXnine ";
$MyQuery.= "FROM ($atSql) at ";
$MyQuery.= "LEFT JOIN ";
$MyQuery.= "(SELECT QuSession, QuTarget, QuLetter, EnCode, EnName, EnFirstName,EnDob, CoCode, CoName, EnClass, EnDivision, EnAgeClass, EnSubClass, EnStatus, ";
for ($i=1;$i<=8;++$i)
    $MyQuery.= "QuD" . $i . "Score, QuD" . $i . "Rank,QuD" . $i . "Gold,QuD" . $i . "Xnine, ";
$MyQuery.= "QuScore,QuClRank,QuGold,QuXnine ";
$MyQuery.= "FROM Qualifications AS q  ";
$MyQuery.= "INNER JOIN Entries AS e ON q.QuId=e.EnId AND e.EnTournament= " . StrSafe_DB($_SESSION['TourId']) . " ";
$MyQuery.= "INNER JOIN Countries AS c ON e.EnCountry=c.CoId AND e.EnTournament=c.CoTournament) as Sq ON QuSession=FullTgtSession AND QuTarget=FullTgtTarget AND QuLetter=FullTgtLetter ";
$MyQuery.= "ORDER BY FullTgtSession, FullTgtTarget, FullTgtLetter, CoCode, Name, CoName, FirstName ";
$Rs=safe_r_sql($MyQuery);

if (safe_num_rows($Rs)>0) {
    header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Content-Disposition: attachment; filename=' . ($ToCode!='' ? $ToCode : 'export') . '.txt');
    header('Content-type: text/tab-separated-values; charset=' . PageEncode);

    $MyHeader
        = get_text('Session') . "\t"
        . get_text('Target') . "\t"
        . get_text('Country') . "\t"
        . get_text('FamilyName','Tournament') . "\t"
        . get_text('Name','Tournament') . "\t"
        . get_text('CtrlCodeShort','Tournament') . "\t"
        . get_text('SubCl','Tournament') . "\t"
        . get_text('Div') . "\t"
        . get_text('AgeCl') . "\t"
        . get_text('Cl') . "\t"
        . get_text('Code','Tournament') . "\t"
        . get_text('Nation') . "\t";
    for ($i=1;$i<=8;++$i)
        $MyHeader
            .=get_text('TotaleScore') . '-' . $i . "\t"
            . ("Rank") . '-' . $i . "\t"
            . ("Golds") . '-' . $i . "\t"
            . ("X-9") . '-' . $i . "\t";


    $MyHeader
        .=get_text('TotaleScore') . "\t"
        . ("Rank") . "\t"
        . ("Golds")  . "\t"
        . ("X-9") . "\t";

    $MyHeader.="\n";

    print $MyHeader;
    $StrData = '';

    while ($MyRow=safe_fetch($Rs)) {
        $StrData
            .=$MyRow->Session . "\t"
            . $MyRow->TargetNo . "\t"
            . (!is_null($MyRow->Bib) ? $MyRow->NationCode : '') . "\t"
            . $MyRow->FirstName . "\t"
            . $MyRow->Name . "\t"
            . $MyRow->EnDob. "\t"
            . $MyRow->SubClass . "\t"
            . $MyRow->DivCode . "\t"
            . $MyRow->EnAgeClass . "\t"
            . $MyRow->EnClass . "\t"
            . $MyRow->Bib . "\t"
            . $MyRow->Nation . "\t";
        for ($i=1;$i<=8;++$i)
            $StrData
                .=$MyRow->{'QuD' . $i . 'Score'} . "\t"
                . $MyRow->{'QuD' . $i . 'Rank'} . "\t"
                . $MyRow->{'QuD' . $i . 'Gold'} . "\t"
                . $MyRow->{'QuD' . $i . 'Xnine'} . "\t";

        $StrData
                .=$MyRow->QuScore . "\t"
                . $MyRow->QuClRank . "\t"
                . $MyRow->QuGold . "\t"
                . $MyRow->QuXnine . "\t";

        $StrData.="\n";
    }

    print $StrData;
}
