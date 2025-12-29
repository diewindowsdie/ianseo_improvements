<?php
require_once('Common/Fun_FormatText.inc.php');

define("HideCols", GetParameter("IntEvent"));

$Filter=[
    "EnTournament = " . $TourId,
];
if(!empty($TVsettings->TVPSession)) {
    $Filter[]="QuSession = " . $TVsettings->TVPSession;
}
$HallField="''";
if(in_array('HALL',$TVsettings->Columns) and $FopLocations=Get_Tournament_Option('FopLocations', [], $TourId)) {
    $HallField='case';
    foreach($FopLocations as $FopLocation) {
        $HallField.=" when QuTarget between {$FopLocation->Tg1} and {$FopLocation->Tg2} then ".StrSafe_DB($FopLocation->Loc);
    }
    $HallField.=" end";
}

$Select = "SELECT EnCode as Bib, EnName AS Name, SesName, DivDescription, ClDescription, upper(EnFirstName) AS FirstName, QuSession AS Session, concat(QuTarget, QuLetter) AS TargetNo, CoCode AS NationCode, CoName AS Nation, EnClass AS ClassCode, EnDivision AS DivCode, EnAgeClass as AgeClass, EnSubClass as SubClass, EnStatus as Status, $HallField as Hall
        FROM Qualifications
        INNER JOIN Entries ON EnId=QuId
        INNER JOIN Countries ON CoId=EnCountry AND CoTournament=EnTournament
        LEFT JOIN Classes ON EnClass=ClId AND EnTournament=ClTournament
        LEFT JOIN Session ON QuSession=SesOrder AND SesType='Q' AND EnTournament=SesTournament
        LEFT JOIN Divisions ON EnDivision=DivId AND EnTournament=DivTournament
        WHERE " . implode(' and ',$Filter) . "
        ORDER BY QuSession, QuTarget, QuLetter, CoCode, Name, CoName, FirstName ";

$Rs=safe_r_sql($Select);

$RowCounter = 0;
$oldTarget='x';
$Class='';
$ViewHalls=(in_array('HALL', $TVsettings->Columns) or in_array('ALL', $TVsettings->Columns));
while($MyRow=safe_fetch($Rs)) {
	if(!isset($ret[$MyRow->Session])) {

		// crea l'header della gara
		$tmp = '';

		$NumCol = 5+$ViewHalls;

		$tmp.= '<tr><th class="Title" colspan="' . ($NumCol) . '">';
		$tmp.= $Arr_Pages[$TVsettings->TVPPage];
		$tmp.= '</th></tr>' . "\n";

		// Titolo della tabella
		$tmp.= '<tr><th class="Title" colspan="' . ($NumCol) . '">';

		$tmp.=  ($MyRow->SesName ? $MyRow->SesName : get_text('Session') . ' ' . $MyRow->Session);
		$tmp.= '</th></tr>' . "\n";

	// Header vero e proprio, incluse le larghezze delle colonne;
		$col=array();
		$tmp.= '<tr>';
		$tmp.= '<th>' . get_text('Target') . '</th>';
		$col[]=9;
        if($ViewHalls) {
            $tmp.= '<th>' . get_text('ShootingHall', 'Tournament') . '</th>';
            $col[]=10;
        }
		$tmp.= '<th>' . get_text('Athlete') . '</th>';
		$col[]=33;
		$tmp.= '<th>' . get_text('Country') . '</th>';
		$col[]=($TVsettings->TVPViewNationName?30:12);
		$tmp.= '<th>' . get_text('Division') . '</th>';
		$col[]=8;
		$tmp.= '<th>' . get_text('Class') . '</th>';
		$col[]=8;
		$tmp.= '</tr>' . "\n";
		$ret[$MyRow->Session]['head']=$tmp;

		$SumCol=array_sum($col);
		$cols='';
		foreach($col as $w) $cols.='<col width="'.round(100*$w/$SumCol, 0).'%"></col>';

		$ret[$MyRow->Session]['cols']=$cols;
		$ret[$MyRow->Session]['fissi']='';
		$ret[$MyRow->Session]['basso']='';
		$ret[$MyRow->Session]['type']='DB';
		$ret[$MyRow->Session]['style']=$ST;
		$ret[$MyRow->Session]['js']=$JS;
		$ret[$MyRow->Session]['js'] .= 'FreshDBContent[%1$s]=\'GetNewContent.php?Quadro=%1$s&Rule='.$RULE->TVRId.'&Tour='.$RULE->TVRTournament.'&Segment='.$TVsettings->TVPId.'&Session='.$MyRow->Session."';\n";
	}
	$RowCounter++;
	$tmp = '';
	//Dati
	if($oldTarget!=intval($MyRow->TargetNo)) {
		if($oldTarget!='x') $tmp.= '<tr style="height:2px;"><th colspan="' . ($NumCol) . '"></th></tr>';
		$oldTarget=intval($MyRow->TargetNo);
		$Class=($Class ? '' : ' class="Next"');
	}

	$tmp.= '<tr' . $Class . '>'
		. '<td class="NumberAlign">' . ltrim($MyRow->TargetNo,'0') . '&nbsp;</td>'
        . ($ViewHalls ? '<td>'.$MyRow->Hall.'</td>' : '')
		. '<td>' . $MyRow->FirstName . ' ' . ($TVsettings->TVPNameComplete==0 ? FirstLetters($MyRow->Name) : $MyRow->Name) . '</td>'
		. '<td>' . $MyRow->NationCode . ' ' . ($TVsettings->TVPViewNationName==1 ? $MyRow->Nation : '') . '</td>'
		. '<td class="Center">' . (HideCols && $MyRow->DivDescription ? $MyRow->DivDescription : $MyRow->DivCode) . '</td>'
		. '<td class="Center">' . (HideCols && $MyRow->ClDescription ? $MyRow->ClDescription : $MyRow->ClassCode) . '</td>'
		. '</tr>' . "\n";

	$ret[$MyRow->Session]['basso'].=$tmp;
}


?>