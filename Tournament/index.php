<?php

require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Lib/Fun_DateTime.inc.php');
require_once('Tournament/Fun_Tournament.local.inc.php');
require_once('Common/Fun_ScriptsOnNewTour.inc.php');
require_once('Common/Fun_Various.inc.php');

if(file_exists($CFG->DOCUMENT_PATH.'Api/ISK-NG/config_defines.php')) {
    include_once($CFG->DOCUMENT_PATH.'Api/ISK-NG/config_defines.php');
}

if(!isset($_REQUEST['New']) and CheckTourSession(true)) {
    checkFullACL(AclCompetition, 'cData', AclReadWrite);
}

$SetTypes=GetExistingTournamentTypes();

$NumErr=0;
if (isset($_REQUEST['Command'])) {
    if($_REQUEST['Command']=='AssignLookupEntry') {
        if (!IsBlocked(BIT_BLOCK_TOURDATA) AND !IsBlocked(BIT_BLOCK_PARTICIPANT) ) {
            $q = safe_w_SQL("UPDATE Entries 
                INNER JOIN Tournament on EnTournament=ToId 
                SET EnIocCode=ToIocCode
                where ToId=".StrSafe_DB($_SESSION['TourId'])." AND ToIocCode!=EnIocCode");
        }
    }
    // DEVE essere stata selezionata una regola localizzata!!!
    if ($_REQUEST['Command']=='SAVE') { // /*and (!isset($_REQUEST['New']) or !empty($_REQUEST['d_Rule']))*/)
        if (!IsBlocked(BIT_BLOCK_TOURDATA)) {
            $ToCode=preg_replace('/[^0-9a-z._-]+/sim', '_', $_REQUEST['d_ToCode']);
            if(isset($_REQUEST['New']) and $_SESSION['TourId']==-1 and $CFG->USERAUTH AND !empty($_SESSION['AUTH_ENABLE']) AND empty($_SESSION['AUTH_ROOT']) and !possibleFeature(AclRoot, AclReadWrite,$ToCode)) {
                CD_redirect($CFG->ROOT_DIR);
                exit;
            }
            $TheString=preg_replace('/ +/', '', $_REQUEST['d_ToTimeZone']);
            $sign=($TheString[0]=='-' ? '-' : '+');
            $tmp=explode(':', $TheString);
            if(empty($tmp[1])) $tmp[1]=0;
            $_REQUEST['d_ToTimeZone']=$sign.sprintf('%02.0f', abs(intval($tmp[0]))).':'.sprintf('%02.0f', abs(intval($tmp[1])));

            $NumErr = VerificaDati($Arr_Values2Check_Index);

        /*
            Se ho l'errore su una data, lo forzo anche nell'altra
        */
            if ($Arr_Values2Check_Index['x_ToWhenFrom']['Error']) {
                $Arr_Values2Check_Index['x_ToWhenTo']['Error']=true;
            } elseif ($Arr_Values2Check_Index['x_ToWhenTo']['Error']) {
                $Arr_Values2Check_Index['x_ToWhenFrom']['Error']=true;
            }

            if ($NumErr==0) {
            /*
                Verifico qui se la data finale è maggiore o uguale a quella iniziale.
                Se non è così forzo l'errore in $Arr_Values2Check_Index['x_ToWhenFrom'] e in $Arr_Values2Check_Index['x_ToWhenTo'].
            */
                if ($_REQUEST['xx_ToWhenToYear'] . $_REQUEST['xx_ToWhenToMonth'] . $_REQUEST['xx_ToWhenToDay'] <
                    $_REQUEST['xx_ToWhenFromYear'] . $_REQUEST['xx_ToWhenFromMonth'] . $_REQUEST['xx_ToWhenFromDay']) {
                    $Arr_Values2Check_Index['x_ToWhenFrom']['Error']=true;
                    $Arr_Values2Check_Index['x_ToWhenTo']['Error']=true;
                } else {
                    $RowId=0;
                    if(empty($_REQUEST['d_ToIocCode'])) $_REQUEST['d_ToIocCode']='';
                    $ToTypeSubRule = (empty($_REQUEST['d_SubRule']) || empty($SetTypes[$_REQUEST['d_Rule']]['rules'][$_REQUEST['d_ToType']][$_REQUEST['d_SubRule']-1]) ? '' : $SetTypes[$_REQUEST['d_Rule']]['rules'][$_REQUEST['d_ToType']][$_REQUEST['d_SubRule']-1]);
                    $DoChanges=isset($_REQUEST['TourReset']);

                    // check rules of the tournament
                    // suppressed after Master World Champs Lausanne 2018
                    //if(!$DoChanges and !isset($_REQUEST['New'])) {
                    //	$t=safe_r_sql("select ToType, ToLocRule, ToTypeSubRule from Tournament where ToId={$_SESSION['TourId']}");
                    //	$u=safe_fetch($t);
                    //	$DoChanges=($u->ToType!=$_REQUEST['d_ToType'] or $u->ToLocRule!=$_REQUEST['d_Rule'] or $u->ToTypeSubRule!=$ToTypeSubRule);
                    //}


                    if(!empty($_SESSION['TourCode']) and $_SESSION['TourCode']!=$ToCode) {
                        // Renaming of the competition... remove all media to force a redraw on subsequent open!
                        require_once('Common/CheckPictures.php');
                        RemoveMedia($_SESSION['TourCode']); // deletes old name things
                        RemoveMedia($ToCode); // delete new name things that might have been left behind at a certains time
                    }
                    $Insert = "INSERT INTO Tournament (
                        " . (!isset($_REQUEST['New']) ? 'ToId,' : '') . "
                        ToType,
                        ToCode,
                        ToName,
                        ToNameShort,
                        ToIocCode,
                        ToCommitee,
                        ToComDescr,
                        ToWhere,
                        ToTimeZone,
                        ToWhenFrom,
                        ToWhenTo,
                        ToCurrency,
                        ToPrintLang,
                        ToPrintChars,
                        ToPrintPaper,
                        ToUseHHT,
                        ToDbVersion,
                        ToTypeSubRule,
                        ToLocRule,
                        ToIsORIS,
                        ToVenue,
                        ToCountry
                        ) 
                        VALUES("
                        . (!isset($_REQUEST['New']) ? StrSafe_DB($_SESSION['TourId']) . "," : '')
                        . StrSafe_DB($_REQUEST['d_ToType']) . ","
                        . StrSafe_DB($ToCode) . ","
                        . StrSafe_DB(stripslashes($_REQUEST['d_ToName'])) . ","
                        . StrSafe_DB(stripslashes($_REQUEST['d_ToNameShort'])) . ","
                        . StrSafe_DB(stripslashes($_REQUEST['d_ToIocCode'])) . ","
                        . StrSafe_DB($_REQUEST['d_ToCommitee']) . ","
                        . StrSafe_DB(stripslashes($_REQUEST['d_ToComDescr'])) . ","
                        . StrSafe_DB(stripslashes($_REQUEST['d_ToWhere'])) . ","
                        . StrSafe_DB(stripslashes($_REQUEST['d_ToTimeZone'])) . ","
                        . StrSafe_DB(sprintf("%04d-%02d-%02d", intval($_REQUEST['xx_ToWhenFromYear']), intval($_REQUEST['xx_ToWhenFromMonth']), intval($_REQUEST['xx_ToWhenFromDay']))) . ","
                        . StrSafe_DB(sprintf("%04d-%02d-%02d", intval($_REQUEST['xx_ToWhenToYear']), intval($_REQUEST['xx_ToWhenToMonth']), intval($_REQUEST['xx_ToWhenToDay']))) . ","
                        . StrSafe_DB($_REQUEST['xx_ToCurrency']) . ","
                        . StrSafe_DB($_REQUEST['xx_ToPrintLang']) . ","
                        . StrSafe_DB($_REQUEST['xx_ToPrintChars']??0) . ","
                        . StrSafe_DB(intval($_REQUEST['xx_ToPaperSize'])) . ","
                        . StrSafe_DB(intval($_REQUEST['xx_ToUseHHT'])) . ","
                        . StrSafe_DB(GetParameter('DBUpdate')) . ","
                        . StrSafe_DB($ToTypeSubRule) . ","
                        . StrSafe_DB($_REQUEST['d_Rule']) . ","
                        . StrSafe_DB(!empty($_REQUEST['d_ORIS'])) . ","
                        . StrSafe_DB(trim($_REQUEST['d_ToVenue'])) . ","
                        . StrSafe_DB(trim($_REQUEST['d_ToCountry'])) . "
                        ) 
                        ON DUPLICATE KEY UPDATE 
                        ToType = " . StrSafe_DB($_REQUEST['d_ToType']) . ",
                        ToCode = " . StrSafe_DB($ToCode) . ",
                        ToName = " . StrSafe_DB(stripslashes($_REQUEST['d_ToName'])) . ",
                        ToNameShort = " . StrSafe_DB(stripslashes($_REQUEST['d_ToNameShort'])) . ",
                        ToIocCode = " . StrSafe_DB(stripslashes($_REQUEST['d_ToIocCode'])) . ",
                        ToCommitee = " . StrSafe_DB($_REQUEST['d_ToCommitee']) . ",
                        ToComDescr = " . StrSafe_DB(stripslashes($_REQUEST['d_ToComDescr'])) . ",
                        ToWhere = " . StrSafe_DB(stripslashes($_REQUEST['d_ToWhere'])) . ",
                        ToTimeZone = " . StrSafe_DB(stripslashes($_REQUEST['d_ToTimeZone'])) . ",
                        ToWhenFrom = " . StrSafe_DB(sprintf("%04d-%02d-%02d", intval($_REQUEST['xx_ToWhenFromYear']), intval($_REQUEST['xx_ToWhenFromMonth']), intval($_REQUEST['xx_ToWhenFromDay']))) . ",
                        ToWhenTo = " . StrSafe_DB(sprintf("%04d-%02d-%02d", intval($_REQUEST['xx_ToWhenToYear']), intval($_REQUEST['xx_ToWhenToMonth']), intval($_REQUEST['xx_ToWhenToDay']))) .  ",
                        ToCurrency = " . StrSafe_DB($_REQUEST['xx_ToCurrency']) .  ",
                        ToPrintLang = " . StrSafe_DB($_REQUEST['xx_ToPrintLang']) .  ",
                        ToPrintChars = " . StrSafe_DB($_REQUEST['xx_ToPrintChars']??0) . ",
                        ToPrintPaper = " . intval($_REQUEST['xx_ToPaperSize']) . ",
                        ToUseHHT = " . intval($_REQUEST['xx_ToUseHHT']) . ", 
                        ToDbVersion = " .  StrSafe_DB(GetParameter('DBUpdate')) . ", 
                        ToTypeSubRule = " .  StrSafe_DB($ToTypeSubRule) . ", 
                        ToLocRule=" . StrSafe_DB($_REQUEST['d_Rule']) . ", 
                        ToIsORIS=" . StrSafe_DB(!empty($_REQUEST['d_ORIS'])) . ", 
                        ToVenue=" . StrSafe_DB(trim($_REQUEST['d_ToVenue'])) . ", 
                        ToCountry=" . StrSafe_DB(trim($_REQUEST['d_ToCountry']));
                    $Rs=safe_w_sql($Insert);
                    $RowId = safe_w_last_id();
                    set_qual_session_flags();
                    $_SESSION['ISORIS']=!empty($_REQUEST['d_ORIS']);
                    // need those in the class creation engine!
                    $_SESSION['TourRealWhenFrom']=sprintf("%04d-%02d-%02d", intval($_REQUEST['xx_ToWhenFromYear']), intval($_REQUEST['xx_ToWhenFromMonth']), intval($_REQUEST['xx_ToWhenFromDay']));
                    $_SESSION['TourRealWhenTo']=sprintf("%04d-%02d-%02d", intval($_REQUEST['xx_ToWhenToYear']), intval($_REQUEST['xx_ToWhenToMonth']), intval($_REQUEST['xx_ToWhenToDay']));


                    //print $Insert;exit;
                    if (isset($_REQUEST['New'])) {
                        // 	Recupero l'ultimo id inserito
                        if($RowId) {
                            $_SESSION['TourId']=$RowId;
                        } else {
                            print get_text('UnexpectedError');
                            exit;
                        }
                    }

                    if(isset($_REQUEST['New']) or $DoChanges) {
                        // Eseguo il/i file(s) di setup della gara
                        GetSetupFile($_SESSION['TourId'], $_REQUEST['d_ToType'], $_REQUEST['d_Rule'], empty($_REQUEST['d_SubRule']) ? '1' : $_REQUEST['d_SubRule'], $ToTypeSubRule);

                        // calcolo il numero massimo di persone in ogni team
                        calcMaxTeamPerson(array(), true, $_SESSION['TourId']);

                        unset($_REQUEST['New']);
                    }

                    // sets any options configured in this page
                    if(!empty($_REQUEST['Options'])) {
                        foreach($_REQUEST['Options'] as $Option => $Value) {
                            switch($Option) {
                            }
                            Set_Tournament_Option($Option, $Value);
                        }
                    }

                    Set_Tournament_Option('TargetsToHHt', !empty($_REQUEST['TargetsToHHt']));

                    if(!empty($_REQUEST['Module'])) {
                        $UseAPI=0;
                        foreach($_REQUEST['Module'] as $Module => $Parameters) {
                            foreach($Parameters as $Parameter => $Value) {
                                switch(true) {
                                    case ($Module=='ISK-NG' and $Parameter=='ServerUrl'):
                                        // both ServerUrl and Mode must be set to save the module
                                        if($Value) {
                                            if(substr($Value,0,4)!='http') {
                                                $Value='http://'.$Value;
                                            }
                                            if($off=@strpos($Value, '/', 9)) {
                                                $Value=substr($Value, 0, $off);
                                            }
                                        }
                                        break;
                                    case ($Module=='ISK-NG' and $Parameter=='ServerUrlPin' and $Value):
                                        if(intval($Value)!=0) {
                                            $Value = str_pad(substr(intval($Value),0,4),4,"0",STR_PAD_LEFT);
                                        } else {
                                            $Value='';
                                        }
                                        break;
                                    case ($Module=='ISK-NG' and $Parameter=='Mode' and $Value):
                                        if($Value=='ng-pro') {
                                            // MUST have a licence to work
                                            if(!isset($_REQUEST['Module']['ISK-NG']['LicenseNumber']) or trim($_REQUEST['Module']['ISK-NG']['LicenseNumber'])=='') {
                                                unset($_REQUEST['Module']['ISK-NG']['LicenseNumber']);
                                                $Value='ng-lite';
                                                delModuleParameter('ISK-NG', 'LicenseNumber');
                                            }
                                        }
                                        $apiType=array('ng-lite'=>11, 'ng-pro'=>12, 'ng-live'=>13);
                                        $UseAPI = $apiType[$Value];
                                        break;
                                    case ($Module=='ISK-NG'):
                                        // check the URL has the $CFG->ROOT_DIR appended
                                        if($Parameter=='ServerUrl') {
                                            if(substr($Value,-1)!='/') {
                                                $Value.='/';
                                            }
                                            if(substr($Value, 0, 4)!='http') {
                                                $Value= "http://".$Value;
                                            }
                                            if(substr($Value, -1*strlen($CFG->ROOT_DIR))!=$CFG->ROOT_DIR) {
                                                $result = parse_url($Value);
                                                $Value= $result['scheme']."://".$result['host'].$CFG->ROOT_DIR;
                                            }
                                        } elseif($Parameter=='LicenseNumber') {
                                            // MUST have a licence to work
                                            if(isset($_REQUEST['Module']['ISK-NG']['Mode']) and $_REQUEST['Module']['ISK-NG']['Mode']=='ng-pro' and !trim($Value)) {
                                                $_REQUEST['Module']['ISK-NG']['Mode']='ng-lite';
                                                setModuleParameter('ISK-NG', 'mode', 'ng-lite');
                                                delModuleParameter('ISK-NG', 'LicenseNumber');
                                                continue 2;
                                            }
                                        }
                                        break;
                                }
                                setModuleParameter($Module, $Parameter, $Value);
                            }
                        }
                        if($UseAPI!=($_SESSION['UseApi']??0)) {
                            // there has been a change in API, so we reset spurious settings
                            switch($UseAPI) {
                                case 11:
                                    resetModuleParameters('ISK-NG', ['Mode','ServerUrl', 'ServerUrlPin', 'Grouping']);
                                    break;
                                case 12:
                                    resetModuleParameters('ISK-NG', ['Mode','ServerUrl', 'ServerUrlPin', 'LicenseNumber', 'Grouping', 'UsePersonalDevices']);
                                    break;
                                case 13:
                                    resetModuleParameters('ISK-NG', ['Mode','SocketIP', 'SocketPort', 'Grouping', 'UsePersonalDevices']);
                                    break;
                            }
                        }
                        Set_Tournament_Option('UseApi', $UseAPI);
                    }

                    header('Location: '.$CFG->ROOT_DIR.'Common/TourOn.php?ToId=' . $_SESSION['TourId'] . '&BackTo='.$CFG->ROOT_DIR.'Tournament/index.php');
                    exit;
                }
            }
        } else {
            $Arr_Values2Check_Index = array (
                'd_ToCode' 			=> array('Func' => 'StrNotEmpty', 'Error' => true),
                'd_ToName' 			=> array('Func' => 'StrNotEmpty', 'Error' => true),
                'd_ToCommitee' 		=> array('Func' => 'StrNotEmpty', 'Error' => true),
                'd_ToComDescr' 		=> array('Func' => 'StrNotEmpty', 'Error' => true),
                'd_ToWhere' 		=> array('Func' => 'StrNotEmpty', 'Error' => true),
                'x_ToWhenFrom' 		=> array('Func' => 'GoodDate', 'Error' => true, 'Value' => (isset($_REQUEST['xx_ToWhenFromYear']) ?  $_REQUEST['xx_ToWhenFromYear'] : '0000') . '-' . (isset($_REQUEST['xx_ToWhenFromMonth']) ? $_REQUEST['xx_ToWhenFromMonth'] : '00') . '-' . (isset($_REQUEST['xx_ToWhenFromDay']) ? $_REQUEST['xx_ToWhenFromDay'] : '00')),
                'x_ToWhenTo'		=> array('Func' => 'GoodDate', 'Error' => true, 'Value' => (isset($_REQUEST['xx_ToWhenToYear']) ?  $_REQUEST['xx_ToWhenToYear'] : '0000') . '-' . (isset($_REQUEST['xx_ToWhenToMonth']) ? $_REQUEST['xx_ToWhenToMonth'] : '00') . '-' . (isset($_REQUEST['xx_ToWhenToDay']) ? $_REQUEST['xx_ToWhenToDay'] : '00')),
                'd_ToTimeZone' 			=> array('Func' => 'StrNotEmpty', 'Error' => true),
                'd_ToType' 			=> array('Func' => 'StrNotEmpty', 'Error' => true),
                'd_ToCountry' 		=> array('Func' => 'StrNotEmpty', 'Error' => true),
                'd_Rule' 			=> array('Func' => 'StrNotEmpty', 'Error' => true)
            );
        }
    }
}

// nuova procedura

$JS_SCRIPT[] = phpVars2js([
        'TxtSelectLocalRule' => get_text('Setup-Select','Install'),
        'ToTypes' => SubruleEncode($SetTypes),
        'isNew' => isset($_REQUEST['New']),
        'IskResetAlert' => get_text('IskResetAlert','Api'),
    ]);
$JS_SCRIPT[] = '<script type="text/javascript" src="Fun_Index.js"></script>';
$JS_SCRIPT[] = '<style>.TextInput {width:40rem;box-sizing: border-box;}</style>';

$PAGE_TITLE=get_text('TourMainInfo', 'Tournament');
$IncludeJquery = true;

include('Common/Templates/head.php');

$Rs = null;
$MyRow = null;
if (!isset($_REQUEST['New'])) {
    $Select = "SELECT *, 
            DATE_FORMAT(ToWhenFrom,'" . get_text('DateFmtDB') . "') AS DtFrom,
            DATE_FORMAT(ToWhenTo,'" . get_text('DateFmtDB') . "') AS DtTo, 
            DATE_FORMAT(ToWhenFrom,'%d') AS DtFromDay,
            DATE_FORMAT(ToWhenFrom,'%m') AS DtFromMonth,
            DATE_FORMAT(ToWhenFrom,'%Y') AS DtFromYear, 
            DATE_FORMAT(ToWhenTo,'%d') AS DtToDay,
            DATE_FORMAT(ToWhenTo,'%m') AS DtToMonth,
            DATE_FORMAT(ToWhenTo,'%Y') AS DtToYear, 
            ToTypeName AS TtName,
            ToNumDist AS TtNumDist
        FROM Tournament
        WHERE ToId=" . StrSafe_DB($_SESSION['TourId']) . " ";
    $Rs = safe_r_sql($Select);
    if(safe_num_rows($Rs) == 1) {
        $MyRow = safe_fetch($Rs);
        if ($MyRow->ToOptions) {
            $MyRow->ToOptions = unserialize($MyRow->ToOptions);
        }
    }
}
?>
<form name="Frm" id="Frm" method="post" action="">
<input type="hidden" name="Command" id="Command" value="SAVE">
<table class="Tabella">
<tr><th class="Title" colspan="2"><?php print (isset($_REQUEST['New']) ? get_text('NewTour', 'Tournament') : ManageHTML($MyRow->ToName)); ?></th></tr>
<tr class="Divider"><td colspan="2"></td></tr>
<tr><td class="Title" colspan="2"><?php echo get_text('TourMainInfo', 'Tournament') ?></td></tr>
<tr>
<th class="TitleLeft w-15"><?php print get_text('TourCode','Tournament');?></th>
<td>
<input <?php print ($Arr_Values2Check_Index['d_ToCode']['Error'] ? ' class="error"' : '');?> type="text" name="d_ToCode" class="TextInput" maxlength="15" value="<?php print ($Arr_Values2Check_Index['d_ToCode']['Error'] ? (isset($ToCode) ? $ToCode : '') : ($MyRow!=NULL ? $MyRow->ToCode : (isset($ToCode) ? $ToCode : '')));?>">
</td>
</tr>

<tr>
<th class="TitleLeft w-15"><?php print get_text('TourName','Tournament');?></th>
<td>
<textarea <?php print ($Arr_Values2Check_Index['d_ToName']['Error'] ? ' class="error"' : '');?> name="d_ToName" rows="2" class="TextInput" >
<?php
	if ($Arr_Values2Check_Index['d_ToName']['Error']) {
		print (array_key_exists('d_ToName',$_REQUEST) ? $_REQUEST['d_ToName'] : '');
	} else {
		if ($MyRow!=NULL) {
			print $MyRow->ToName;
		} else {
			print (array_key_exists('d_ToName',$_REQUEST) ? $_REQUEST['d_ToName'] : '');
		}
	}
?>
</textarea>
</td>
</tr>
<tr>
<th class="TitleLeft w-15"><?php print get_text('TourShortName','Tournament');?></th>
<td>
<input type="text" name="d_ToNameShort" class="TextInput" maxlength="60" value="<?php print ($MyRow!=NULL ? $MyRow->ToNameShort : (array_key_exists('d_ToNameShort',$_REQUEST) ? $_REQUEST['d_ToNameShort'] : ''));?>">
</td>
</tr>

<tr>
<th class="TitleLeft w-15"><?php print get_text('TourCommitee','Tournament');?></th>
<td>
<input <?php print ($Arr_Values2Check_Index['d_ToCommitee']['Error'] ? ' class="error"' : '');?> type="text" name="d_ToCommitee" class="TextInput" maxlength="10" value="<?php print ($Arr_Values2Check_Index['d_ToCommitee']['Error'] ? (array_key_exists('d_ToCommitee',$_REQUEST) ? $_REQUEST['d_ToCommitee'] : '') : ($MyRow!=NULL ? $MyRow->ToCommitee : (array_key_exists('d_ToCommitee',$_REQUEST) ? $_REQUEST['d_ToCommitee'] : '')));?>"><br><br>
<textarea <?php print ($Arr_Values2Check_Index['d_ToComDescr']['Error'] ? ' class="error"' : '');?> name="d_ToComDescr" rows="2" class="TextInput">
<?php
	if ($Arr_Values2Check_Index['d_ToComDescr']['Error']) {
		print (array_key_exists('d_ToComDescr',$_REQUEST) ? $_REQUEST['d_ToComDescr'] : '');
	} else {
		if (!is_null($MyRow)){
			print $MyRow->ToComDescr;
		} else {
			print (array_key_exists('d_ToComDescr',$_REQUEST) ? $_REQUEST['d_ToComDescr'] : '');
		}
	}
?>
</textarea>
</td>
</tr>

<tr>
	<th class="TitleLeft w-15"><?php print get_text('TourIsOris','Tournament');?></th>
	<td>
		<input type="checkbox" name="d_ORIS" <?php echo (!empty($_SESSION['ISORIS']) ? ' checked="checked"' : '');?>> <?php echo get_text('TourIsOrisDescr','Tournament'); ?>
	</td>
</tr>

<tr>
	<th class="TitleLeft w-15"><?php print get_text('LocalRule','Tournament');?></th>
	<td>
		<select name="d_Rule" onchange="ChangeTourType(this.value)" id="d_Rule" <?php print ($Arr_Values2Check_Index['d_Rule']['Error'] ? ' class="error"' : '');?>>
			<?php
				if(count($SetTypes)>1)
					echo '<option value="">--> '.get_text('Setup-Select','Install').' <--</option>';
				foreach($SetTypes as $key => $val) {
                    echo '<option value="' . $key . '"' . (((!is_null($MyRow) and $key == $MyRow->ToLocRule) or (is_null($MyRow) and !empty($_REQUEST['d_Rule']) and $key == $_REQUEST['d_Rule'])) ? ' selected' : '') . '>' . $val['descr'] . '</option>';
                }
			?>
		</select>
	</td>
</tr>

<tr>
<th class="TitleLeft w-15"><?php print get_text('TourType','Tournament');?></th>
<td>
	<select name="d_ToType" onchange="ChangeLocalSubRule(this.value)" id="d_ToType" <?php print ($Arr_Values2Check_Index['d_ToType']['Error'] ? ' class="error"' : '');?>>
<?php

if(isset($_REQUEST['New'])) {
	echo '<option value="">------</option>';
    if(!empty($_REQUEST['d_Rule']) AND array_key_exists($_REQUEST['d_Rule'], $SetTypes)) {
        foreach($SetTypes[$_REQUEST['d_Rule']]['types'] as $key => $val) {
            echo '<option value="' . $key . '"' . ((!empty($_REQUEST['d_ToType']) and $key == $_REQUEST['d_ToType']) ? ' selected' : '') . '>' . $val . '</option>';
        }
    }
} else {
	foreach($SetTypes[$MyRow->ToLocRule]['types'] as $k=>$v) {
		echo '<option value="'.$k.'"'.($k==$MyRow->ToType?' selected':'').'>'.$v.'</option>';
	}
}

?>
	</select>
</td>
</tr>

<tr id="rowSubRule"<?php echo (isset($_REQUEST['New']) || !empty($SetTypes[$MyRow->ToLocRule]['rules'][$MyRow->ToType]) ? '' : ' style="display:none"'); ?>>
	<th class="TitleLeft w-15"><?php echo get_text('LocalSubRule','Tournament'); ?></th>
	<td><select name="d_SubRule" id="d_SubRule">
<?php

if(isset($_REQUEST['New']) and !empty($_REQUEST['d_Rule']) and !empty($_REQUEST['d_ToType']) and !empty($SetTypes[$_REQUEST['d_Rule']]['rules'][$_REQUEST['d_ToType']])) {
    echo '<option value="">--</option>';
    foreach($SetTypes[$_REQUEST['d_Rule']]['rules'][$_REQUEST['d_ToType']] as $k => $v) {
        echo '<option value="'.($k+1).'"'.(($k+1)==$_REQUEST['d_SubRule'] ? ' selected' : '').'>'.get_text($v,'Install').'</option>';
    }

} else if(!isset($_REQUEST['New']) and !empty($SetTypes[$MyRow->ToLocRule]['rules'][$MyRow->ToType])) {
	echo '<option value="">--</option>';
	foreach($SetTypes[$MyRow->ToLocRule]['rules'][$MyRow->ToType] as $k => $v) {
		echo '<option value="'.($k+1).'"'.($v==$MyRow->ToTypeSubRule?' selected':'').'>'.get_text($v,'Install').'</option>';
	}
}

?>
	</select></td>
</tr>

<tr>
<th class="TitleLeft"><?php echo get_text('TourRuleReset','Tournament'); ?></th>
<td><input type="checkbox" name="TourReset" onclick="this.checked=confirm('<?php echo strip_tags(str_replace(array('<br/>', "'"), array('\n', "\'"), get_text('TourRuleResetDescr','Tournament'))); ?>')">&nbsp;<?php echo get_text('TourRuleResetDescr','Tournament'); ?></td>
</tr>

<?php

if (!isset($_REQUEST['New'])) {
	// the lookup table should NOT appear in new tournaments
    echo '<tr><th class="TitleLeft w-15">'. get_text('LookupTable','Tournament'). '</th><td>';
    echo '<input type="hidden" id="oldToIocCode" value="'.$MyRow->ToIocCode.'">';
	echo '<select name="d_ToIocCode" id="d_ToIocCode" onchange="ChangeLookUpCombo();"><option></option>';
	$q = safe_r_sql("SELECT * FROM LookUpPaths order by LupIocCode='FITA' desc, LupIocCode");
	while($r=safe_fetch($q)) {
		echo '<option value="'.$r->LupIocCode.'"'.($MyRow->ToIocCode==$r->LupIocCode ? ' selected="selected"' : '').'>'.($r->LupIocCode ? get_text('LUE-'.$r->LupIocCode, 'Tournament') : '').'</option>';
	}
	echo '</select>';
	if($MyRow->ToIocCode) {
        $q = safe_r_SQL("select distinct EnIocCode from Entries where EnTournament={$MyRow->ToId} AND EnIocCode != ".StrSafe_DB($MyRow->ToIocCode));
        if(safe_num_rows($q)) {
            echo '<input type="button" id="cmdAssignLookup" value="'.get_text('AssignLookupTable', 'Tournament',  get_text('LUE-'.$MyRow->ToIocCode, 'Tournament')).'" onclick="assignCurrentLookUp()" style="margin-left: 1vh;">';
        }
    }

    echo '</td></tr>';
}

?>
<tr>
<th class="TitleLeft w-15"><?php print get_text('TourWhere','Tournament');?></th>
<td>
<textarea <?php print ($Arr_Values2Check_Index['d_ToWhere']['Error'] ? ' class="error"' : '');?> name="d_ToWhere" rows="2" class="TextInput">
<?php
	if ($Arr_Values2Check_Index['d_ToWhere']['Error'] or !$MyRow) {
		print (array_key_exists('d_ToWhere',$_REQUEST) ? $_REQUEST['d_ToWhere'] : '');
	} else {
		print $MyRow->ToWhere;
	}
?>
</textarea>
</td>
</tr>

<tr>
<th class="TitleLeft w-15"><?php print get_text('CompVenue','Tournament');?></th>
<td>
<input type="text" name="d_ToVenue" class="TextInput" value="<?php
if (!$MyRow) {
	print (array_key_exists('d_ToVenue',$_REQUEST) ? $_REQUEST['d_ToVenue'] : '');
} else {
	print $MyRow->ToVenue;
}
?>">

</td>
</tr>

<tr>
    <th class="TitleLeft w-15"><?php echo get_text('Natl-Nation','Tournament'); ?></th>
    <td><select id="d_ToCountry" name="d_ToCountry" <?php print ($Arr_Values2Check_Index['d_ToCountry']['Error'] ? ' class="error"' : '');?>><option value="">--</option>
            <?php
                foreach(get_Countries() as $k => $v) {
                    echo '<option value="'.$k.'"'.(($MyRow and $k==$MyRow->ToCountry) ? ' selected':'').'>'.$k . ' - ' .$v.'</option>';
                }
            ?>
        </select></td>
</tr>

<tr>
<th class="TitleLeft w-15"><?php print get_text('TourTimeZone','Tournament');?></th>
<td>
<input type="text" <?php print ($Arr_Values2Check_Index['d_ToTimeZone']['Error'] ? ' class="error"' : '');?> name="d_ToTimeZone" rows="2" cols="40" value="
<?php
	if ($Arr_Values2Check_Index['d_ToTimeZone']['Error'] or !$MyRow) {
		$Offset=(empty($_COOKIE['offset']) ? '0' : $_COOKIE['offset']);
		print (array_key_exists('d_ToTimeZone',$_REQUEST) ? $_REQUEST['d_ToTimeZone'] : ($Offset[0]=='-' ? '-' : '+').date('H:i', abs($Offset*60)));
	} else {
		print $MyRow->ToTimeZone;
	}
?>"> (±hh:mm)
</td>
</tr>

<tr>
<th class="TitleLeft w-15"><?php print get_text('TourWhen','Tournament');?></th>
<td>
<?php

// search for next Sunday as a default
$NextSunday=strtotime("next Sunday");
$FromDay  = (int) date("j", $NextSunday);
$FromMonth= (int) date("n", $NextSunday);
$FromYear = (int) date("Y", $NextSunday);
$ToDay  = (int) date("j", $NextSunday);
$ToMonth= (int) date("n", $NextSunday);
$ToYear = (int) date("Y", $NextSunday);

// set to defined dates
if($MyRow) {
	$FromDay  = (int) $MyRow->DtFromDay;
	$FromMonth= (int) $MyRow->DtFromMonth;
	$FromYear = (int) $MyRow->DtFromYear;
	$ToDay  = (int) $MyRow->DtToDay;
	$ToMonth= (int) $MyRow->DtToMonth;
	$ToYear = (int) $MyRow->DtToYear;
} else {
	// or set to requested dates
	if(!empty($_REQUEST['xx_ToWhenFromDay']))   $FromDay  = intval($_REQUEST['xx_ToWhenFromDay']);
	if(!empty($_REQUEST['xx_ToWhenFromMonth'])) $FromMonth= intval($_REQUEST['xx_ToWhenFromMonth']);
	if(!empty($_REQUEST['xx_ToWhenFromYear']))  $FromYear = intval($_REQUEST['xx_ToWhenFromYear']);
	if(!empty($_REQUEST['xx_ToWhenToDay']))   $ToDay  = intval($_REQUEST['xx_ToWhenToDay']);
	if(!empty($_REQUEST['xx_ToWhenToMonth'])) $ToMonth= intval($_REQUEST['xx_ToWhenToMonth']);
	if(!empty($_REQUEST['xx_ToWhenToYear']))  $ToYear = intval($_REQUEST['xx_ToWhenToYear']);
}

// Date seting is ALWAYS ISO: yyyy-mm-dd
$tmpFromDay = '<select ' . ($Arr_Values2Check_Index['x_ToWhenFrom']['Error'] ? ' class="error"' : '').' name="xx_ToWhenFromDay">';
for ($i=1;$i<=31;++$i) $tmpFromDay.= '<option value="' . str_pad($i,2,'0',STR_PAD_LEFT) . '"' . ($i==$FromDay ? ' selected' : '') . '>' . sprintf('%0d', $i) . '</option>';
$tmpFromDay.= '</select>';
$tmpToDay = '<select ' . ($Arr_Values2Check_Index['x_ToWhenTo']['Error'] ? ' class="error"' : '') . ' name="xx_ToWhenToDay">';
for ($i=1;$i<=31;++$i) $tmpToDay.= '<option value="' . str_pad($i,2,'0',STR_PAD_LEFT) . '"' . ($i==$ToDay ? ' selected' : '') . '>' . sprintf('%0d', $i) . '</option>';
$tmpToDay.= '</select>';

$tmpFromMonth = '<select ' . ($Arr_Values2Check_Index['x_ToWhenFrom']['Error'] ? ' class="error"' : '') . ' name="xx_ToWhenFromMonth">';
for ($i=1;$i<=12;++$i) $tmpFromMonth.= '<option value="' . str_pad($i,2,'0',STR_PAD_LEFT) . '"' . ($i==$FromMonth ? ' selected' : '') . '>' . sprintf('%0d', $i) . '</option>';
$tmpFromMonth.= '</select>';
$tmpToMonth = '<select ' . ($Arr_Values2Check_Index['x_ToWhenTo']['Error'] ? ' class="error"' : '') . ' name="xx_ToWhenToMonth">';
for ($i=1;$i<=12;++$i) $tmpToMonth.= '<option value="' . str_pad($i,2,'0',STR_PAD_LEFT) . '"' . ($i==$ToMonth ? ' selected' : '') . '>' . sprintf('%0d', $i) . '</option>';
$tmpToMonth.= '</select>';

$ToFromLen=max(strlen(get_text('From','Tournament')), strlen(get_text('To','Tournament')));
echo get_text('From','Tournament')
	. str_repeat('&nbsp;', 1 + ($ToFromLen - strlen(get_text('From','Tournament')))*2)
	. '<input ' . ($Arr_Values2Check_Index['x_ToWhenFrom']['Error'] ? ' class="error"' : '') . ' type="text" name="xx_ToWhenFromYear" size="4" maxlength="4" value="' . $FromYear . '">'
	. '&nbsp;-&nbsp;'.$tmpFromMonth
	. '&nbsp;-&nbsp;'.$tmpFromDay
	. '&nbsp;(yyyy-mm-dd)';
echo '<br><br>';
echo get_text('To','Tournament')
	. str_repeat('&nbsp;', 1 + ($ToFromLen - strlen(get_text('To','Tournament')))*2)
	. '<input ' . ($Arr_Values2Check_Index['x_ToWhenTo']['Error'] ? ' class="error"' : '') . ' type="text" name="xx_ToWhenToYear" size="4" maxlength="4" value="' . $ToYear . '">'
	. '&nbsp;-&nbsp;'.$tmpToMonth
	. '&nbsp;-&nbsp;'.$tmpToDay
	. '&nbsp;(yyyy-mm-dd)';

?>
</td>
</tr>

<?php // Settaggio carta A4/LETTER ?>
<tr>
<th class="TitleLeft w-15"><?php print get_text('PaperSize','Tournament');?></th>
<td>
<?php
	$ref='';
	if ($MyRow && $NumErr==0)
		$ref=$MyRow->ToPrintPaper;
	elseif(!empty($_REQUEST['xx_ToPaperSize']))
		$ref=$_REQUEST['xx_ToPaperSize'];
?>
<select name="xx_ToPaperSize">
	<option value="0"<?php print ($ref==0 ? ' selected' : ''); ?>>A4</option>
	<option value="1"<?php print ($ref==1 ? ' selected' : ''); ?>>Letter</option>
</select>
</td>
</tr>

<?php // Settaggio Currency ?>
<tr>
<th class="TitleLeft w-15"><?php print get_text('StrCurrency','Tournament');?></th>
<td>
<input type="text" name="xx_ToCurrency" value="<?php echo ($MyRow!=NULL && $NumErr==0 && $MyRow->ToCurrency?$MyRow->ToCurrency: (isset($_REQUEST['xx_ToCurrency']) ? $_REQUEST['xx_ToCurrency'] : '€')); ?>" size="5">
</td>
</tr>

<?php // Settaggio Printouts language
include_once('Common/GlobalsLanguage.inc.php');
?>
<tr>
<th class="TitleLeft w-15"><?php print get_text('PrintLanguage','Tournament');?></th>
<td>
<select name="xx_ToPrintLang">
<option value=""><?php print get_text('SetByUser','Tournament');?></option>
<?php
$ref='';
if ($MyRow!=NULL && $NumErr==0)
	$ref=$MyRow->ToPrintLang;
elseif(!empty($_REQUEST['xx_ToPrintLang']))
	$ref=$_REQUEST['xx_ToPrintLang'];
foreach($Lingue as $key=>$val) {
	echo '<option value="'.$key.'"'.($key==$ref?' selected':'').'>'.$val.'</option>';
}
?>
</select>
</td>
</tr>

<?php // Settaggio PrintoutCharset ?>
<tr>
<th class="TitleLeft w-15"><?php print get_text('PrintCharset','Tournament');?></th>
<td>
<?php
	$options=array(
		0 => get_text('PrintNormal','Tournament'),
		1 => get_text('PrintCyrillic','Tournament'),
		2 => get_text('PrintChinese','Tournament'),
        3 => get_text('PrintJapanese','Tournament')
	);

	$ref='';
	if ($MyRow!=NULL && $NumErr==0)
		$ref=$MyRow->ToPrintChars;
	elseif(!empty($_REQUEST['xx_ToPrintChars']))
		$ref=$_REQUEST['xx_ToPrintChars'];

	foreach ($options as $key => $value)
	{
		print '<input type="radio" name="xx_ToPrintChars" value="' . $key . '" ' . ($ref==$key ? ' checked' : '') . '/>' . $value . '<br/>';
	}
?>
<?php /*?>
<input type="radio" name="xx_ToPrintChars" value="0"<?php echo empty($MyRow->ToPrintChars)?' checked':'' ?>><?php echo get_text('PrintNormal','Tournament'); ?><br/>
<input type="radio" name="xx_ToPrintChars" value="1"<?php echo ($MyRow &&  $MyRow->ToPrintChars == 1?' checked':''); ?>><?php echo get_text('PrintCyrillic','Tournament'); ?><br/>
<input type="radio" name="xx_ToPrintChars" value="2"<?php echo ($MyRow && $MyRow->ToPrintChars == 2 ? ' checked':''); ?>><?php echo get_text('PrintChinese','Tournament'); ?>
<?php*/?>
</td>
</tr>

<?php // Settaggio Uso HHT ?>
<tr>
<th class="TitleLeft w-15"><?php print get_text('EnableHHT','HTT');?></th>
<td>
<?php
if ($MyRow!=NULL && $NumErr==0) {
	$ref=$MyRow->ToUseHHT;
} elseif(!empty($_REQUEST['xx_ToUseHHT'])) {
	$ref=$_REQUEST['xx_ToUseHHT'];
}

echo '<select name="xx_ToUseHHT">
<option value="0"'.($ref==0 ? ' selected="selected"':'').'>'.get_text('No').'</option>
<option value="1"'.($ref==1 ? ' selected="selected"':'').'>'.get_text('Yes').'</option>
</select>
&nbsp;&nbsp;<input type="checkbox" name="TargetsToHHt"'.(empty($_SESSION['TargetsToHHt']) ? '' : ' checked="checked"').'>'.get_text('TargetsToHHt', 'Tournament').'
</td>
</tr>';

require_once('Common/Lib/Fun_Modules.php');
$ISKMode=getModuleParameter('ISK-NG', 'Mode', '');

if(file_exists($CFG->DOCUMENT_PATH.'Api/index.php')) {
//	include_once $CFG->DOCUMENT_PATH.'Api/index.php';
	$Apis=AvailableApis();
	$IskType=array();

	// checks all the ISK option types
	foreach($Apis as $Api) {
	    @include($CFG->DOCUMENT_PATH.'Api/'.$Api.'/ConfigOptions.php');
    }
    if($IskType) {

	    echo '<tbody id="ISK-config"><tr>
            <th class="TitleLeft w-15">'.get_text('ISK-EnableScore','Api').'</th>
                <td>
                    <select name="Module[ISK-NG][Mode]" onchange="ChangeIskConfig(this)" id="IskSelect" oldval="'.$ISKMode.'">
                    <option value="">'.get_text('No').'</option>';
	    foreach($IskType as $val => $option) {
	        echo '<option value="'.$val.'"'.($ISKMode==$val ? ' selected="selected"' : '').'>'.$option.'</option>';
        }
	    echo '</select>
                </td>
            </tr>';

?><tr>
<th class="TitleLeft w-15"><?php print get_text('AddSubclasses','Tournament');?></th>
<td>
<input type="checkbox" id="createSubClasses" name="createSubClasses" value="1" onchange="subclassesCheckboxChanged()"/>
    <label for="createSubClasses">Создать коды и названия разрядов</label>
    <select name="subclassesSet" id="subclassesSet" disabled>
        <option id="SportIdent" value="SportIdent" selected="selected">Идентификаторы из SportIdent (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11)</option>
        <option id="LetterCodes" value="LetterCodes">Буквенные идентификаторы (NO, 3J, 2J, 1J, 3, 2, 1, C, M, MM, Z)</option>
    </select>
</td></tr>

<?php

	    echo '<tr><th id="ISK-Messages"></th><td id="IskConfig"></td></tr></tbody>';
    }
}



if (!isset($_REQUEST['New'])) {
    print '<tr>';
    print '<td colspan="2" class="Center">';
    print '<a class="Link" href="ManSessions_kiss.php">' . get_text('ManSession', 'Tournament') . '</a> - ';
    print '<a class="Link" href="ManStaffField.php">' . get_text('ManStaffOnField','Tournament') . '</a> - ';
    print '<a class="Link" href="ManLogo.php">' . get_text('LogoManagement','Tournament') . '</a> - ';
    print '<a class="Link" href="ManDivClass.php">' . get_text('ManDivClass','Tournament') . '</a> - ';
    print '<a class="Link" href="ManSubClass.php">' . get_text('ManSubClasses','Tournament') . '</a> - ';
    print '<a class="Link" href="ManageAdvancedParams.php">' . get_text('AdvancedParams','Tournament') . '</a>';
    print '</td>';
    print '</tr>';
}
?>
<tr><td colspan="2" class="Center">
<input type="submit" value="<?php print get_text('CmdSave');?>" onclick="return CheckIskStatus()">&nbsp;&nbsp;
<input type="reset" value="<?php print get_text('CmdCancel');?>">
<br><br>
<?php
	if (CheckTourSession()) {
		print '<a class="Link" href="../Main.php">' . get_text('Close') . '</a>';
	} else {
		print '<a class="Link" href="../index.php">' . get_text('CmdCancel') . '</a>';
	}
?>
</td></tr>
</table>
</form>
<script>ChangeIskConfig()</script>

<?php
	include('Common/Templates/tail.php');


function isTournamentTypePresent($type) {
	global $CFG;
	// search in the default rules
	if(is_file($CFG->DOCUMENT_PATH . 'Modules/Sets/FITA/Setup_' . $type . '.set')) {
		return true;
	}

	// search in the local rules
	$glob=glob($CFG->DOCUMENT_PATH . 'Modules/Sets/*');
	foreach($glob as $val) {
		if(is_dir($val) and file_exists($val . '/Setup_' . $type . '_' . basename($val) . '.set')) {
			return true;
		}
	}

	return false;
}

function GetExistingTournamentTypes() {
	global $CFG;
	$SetType=array();
	$q=safe_r_SQL("select * from TourTypes order by TtOrderBy");
	$TourTypes=array();
	while($r=safe_fetch($q)) {
	    $TourTypes["$r->TtId"] = get_text($r->TtType, 'Tournament') . ($r->TtDistance ? " - $r->TtDistance " . get_text(($r->TtDistance==1 ? 'Distance':'Distances'),'Tournament') : '');
	}

	// search in the local rules
	$glob=glob($CFG->DOCUMENT_PATH . 'Modules/Sets/*/sets.php');
	foreach($glob as $val) {
		include($val);
	}

    foreach($SetType as $a=>&$b) {
        $b['ord']=($a=='default'?'WA':$a);
    }
    global $UsedLang;
    $UsedLang=strtoupper(SelectLanguage());
    uasort($SetType, function($a, $b) {
        global $UsedLang;
	    if($a['ord']=='WA') return -1;
	    if($b['ord']=='WA') return 1;
	    if($a['ord']=='PAR') return -1;
	    if($b['ord']=='PAR') return 1;
	    if($a['ord']=='WAN') return -1;
	    if($b['ord']=='WAN') return 1;
	    if($a['ord']=='NFAA') return -1;
	    if($b['ord']=='NFAA') return 1;
	    if($a['ord']=='IFAA') return -1;
	    if($b['ord']=='IFAA') return 1;
	    if($a['ord']==$UsedLang) return -1;
	    if($b['ord']==$UsedLang) return 1;

	    return strcmp($a['descr'], $b['descr']);
    });

    return $SetType;
}

function SubruleEncode($SubTypes) {
    $Ret=array();
	foreach($SubTypes as $loc => &$data) {
	    $Ret[$loc]=$data;

	    // Loop thru the types and create objects with an order value.
        // This is needed as javascript will automatically re-sort arrays
        // using the key values which makes them come in the wrong order
        $Ret[$loc]['ordered_types']=array();
        $order = 0;
        foreach($data['types'] as $k => $v) {
            $arr = array('order' => $order++, 'type' => $k, 'name' => $v);
            array_push($Ret[$loc]['ordered_types'], $arr);
        }

		foreach($data['rules'] as $type => &$rules) {
	        $Ret[$loc]['rules'][$type]=array('---');
			foreach($rules as $k => $name) {
				$Ret[$loc]['rules'][$type][$k+1]=get_text($name, 'Install');
			}
		}
	}

	return $Ret;
}
