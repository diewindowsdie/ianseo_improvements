<?php
define('debug',false);	// settare a true per l'output di debug

require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Common/Lib/Fun_DateTime.inc.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Tournament/Fun_Tournament.local.inc.php');
require_once('Common/Lib/CommonLib.php');
$aclLevel = checkFullACL(AclCompetition, 'cData', AclReadOnly);
$CanEdit=$aclLevel>=AclReadWrite;
CheckTourSession(true);

$TypeOptions = '<option value="0">---</option>';
$RsSel = safe_r_sql("SELECT ItId, ItDescription, if(ItJudge>0,1,0) + if(ItDos>0,2,0) + if(ItJury>0,3,0) + if(ItOc>0,4,0) as TiGroup FROM InvolvedType ORDER BY ItJudge=0, ItJudge, ItDoS=0, ItDoS, ItJury=0, ItJury, ItOc");
$OldType=0;
while ($RowSel = safe_fetch($RsSel)) {
    if($OldType and $OldType!=$RowSel->TiGroup) {
        $TypeOptions .= '<option value="0" disabled>---</option>';
    }
	$TypeOptions.='<option value="'.$RowSel->ItId.'">'.get_text($RowSel->ItDescription,'Tournament').'</option>';
	$OldType=$RowSel->TiGroup;
}

$Genders ='<option value="">---</option>';
$Genders.='<option value="0">'.get_text('GenderShort0').'</option>';
$Genders.='<option value="1">'.get_text('GenderShort1').'</option>';

$JudgeAccreditations = '<option value="">---</option>';
$JudgeAccreditations .= '<option value="10">' . get_text("JudgeAccreditation_10", "Tournament") . '</option>';
$JudgeAccreditations .= '<option value="20">' . get_text("JudgeAccreditation_20", "Tournament") . '</option>';
$JudgeAccreditations .= '<option value="30">' . get_text("JudgeAccreditation_30", "Tournament") . '</option>';
$JudgeAccreditations .= '<option value="40">' . get_text("JudgeAccreditation_40", "Tournament") . '</option>';
$JudgeAccreditations .= '<option value="50">' . get_text("JudgeAccreditation_50", "Tournament") . '</option>';
$JudgeAccreditations .= '<option value="60">' . get_text("JudgeAccreditation_60", "Tournament") . '</option>';


$JS_SCRIPT = array(
    phpVars2js(array(
        'NoEmptyField' => get_text('AllFieldsMandatory','Errors'),
        'AreYouSure' => get_text('MsgAreYouSure'),
        'Gender0' => get_text('GenderShort0'),
        'Gender1' => get_text('GenderShort1'),
        'TitCode' => get_text('Code', 'Tournament'),
        'TitFName' => get_text('FamilyName', 'Tournament'),
        'TitGName' => get_text('GivenName', 'Tournament'),
        'TitLName' => get_text('LastName', 'Tournament'),
        'TitAccred' => get_text('JudgeAccreditation', 'Tournament'),
        'TitGender' => get_text('Sex', 'Tournament'),
        'TitDob' => get_text('DOB','Tournament'),
        'TitCoCode' => get_text('CountryCode'),
        'TitCountry' => get_text('JudgeRegion', 'Tournament'),
        'TitIsSigningProtocols' => get_text('IsSigningProtocols', 'Tournament'),
    )),
    '<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Tournament/Fun_JS.js"></script>',
    '<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Tournament/Fun_AJAX_ManStaffField.js"></script>',
);
$IncludeFA = true;
$IncludeJquery = true;
$PAGE_TITLE=get_text('StaffOnField','Tournament');

include('Common/Templates/head.php');
if($CanEdit) {
	echo '<table class="Tabella">
		<tr><th class="Title" colspan="12">' . get_text('StaffOnField', 'Tournament') . '</th></tr>
		<tr class="Divider"><td colspan="12"></td></tr>
		<tr>
            <th class="w-5">' . get_text('Code', 'Tournament') . '</th>
            <th class="w-5">' . get_text('LocalCode', 'Tournament') . '</th>
            <th class="w-15">' . get_text('FamilyName', 'Tournament') . '</th>
            <th class="w-15">' . get_text('GivenName', 'Tournament') . '</th>           
            <th class="w-15">' . get_text('LastName', 'Tournament') . '</th>
            <th class="w-5">' . get_text('JudgeAccreditation', 'Tournament') . '</th>
            <th class="w-5">' . get_text('Sex', 'Tournament') . '</th>
            <th class="w-5">' . get_text('CountryCode') . '</th>
            <th class="w-10">' . get_text('JudgeRegion', 'Tournament') . '</th>
            <th class="w-10">' . get_text('JudgeFunction', 'Tournament') . '</th>
            <th class="w-5">' . get_text('IsSigningProtocols', 'Tournament') . '</th>
            <th class="w-10"></th>
		</tr>
		<tr ref="new">
            <td class="Center"><input type="text" style="width: 95%" id="new_Matr" maxlength="9" onchange="FindFieldStaff(this)"></td>
            <td class="Center"><input type="text" style="width: 95%" id="new_LocalCode" maxlength="32"></td>
            <td class="Center"><input type="text" style="width: 97%" id="new_FamilyName" maxlength="64"></td>
            <td class="Center"><input type="text" style="width: 97%" id="new_GivenName" maxlength="64"></td>
            <td class="Center"><input type="text" style="width: 97%" id="new_LastName" maxlength="64"></td>
            <td class="Center"><select style="width: 97%" id="new_Accreditation">' . $JudgeAccreditations . '</select></td>
            <td class="Center"><select style="width: 95%" id="new_Gender">' . $Genders . '</select></td>
            <td class="Center"><input type="text" style="width: 95%" id="new_CountryCode" maxlength="10" onchange="findCountry(this)"></td>
            <td class="Center"><input type="text" style="width: 95%" id="new_CountryName" maxlength="30"></td>
            <td class="Center"><select style="width: 95%" id="new_Type">' . $TypeOptions . '</select></td>
            <td class="Center"><input type="checkbox" style="width: 95%" id="new_IsSigningProtocols" maxlength="30" /></td>
            <td class="Center NoWrap">
                <div class="Button" onclick="searchFieldStaff()"><i class="fa fa-search"></i></div>
                <div class="Button" onclick="addFieldStaff()">' . get_text('CmdAdd', 'Tournament') . '</div>
                <div class="Button" onclick="resetFieldStaff()">' . get_text('CmdCancel') . '</div>
            </td>
		</tr>
		</table>
		<br/>';
}

echo '<table class="Tabella">
	<tr><th class="Title" colspan="12">'.get_text('PersonList','Tournament').'</th></tr>
	<tr class="Divider"><td colspan="12"></td></tr>
	<tr>
        <th class="w-5">' . get_text('Code', 'Tournament') . '</th>
        <th class="w-5">' . get_text('LocalCode', 'Tournament') . '</th>
        <th class="w-15">' . get_text('FamilyName', 'Tournament') . '</th>
        <th class="w-15">' . get_text('GivenName', 'Tournament') . '</th>
        <th class="w-15">' . get_text('LastName', 'Tournament') . '</th>
        <th class="w-5">' . get_text('JudgeAccreditation', 'Tournament') . '</th>
        <th class="w-5">' . get_text('Sex', 'Tournament') . '</th>
        <th class="w-5">' . get_text('CountryCode') . '</th>
        <th class="w-10">' . get_text('JudgeRegion', 'Tournament') . '</th>
        <th class="w-10">' . get_text('JudgeFunction', 'Tournament') . '</th>
        <th class="w-5">' . get_text('IsSigningProtocols', 'Tournament') . '</th>
        <th class="w-10"></th>
	</tr>
	<tbody id="FieldStaff"></tbody>
	</table>';

?>
	<table class="Tabella">
		<tr><th class="Title" colspan="4"><?php print get_text('PrintList','Tournament'); ?></th></tr>
		<tr class="Divider"><td colspan="2"></td></tr>
		<tr>
			<td style="width:50%;" class="Center">
                    <div style="margin-bottom: 2vh;">
                        <a href="PrnStaffField.php" class="Link" target="PrintOut"><img src="../Common/Images/pdf.gif" alt="<?php print get_text('StaffOnField','Tournament');?>"><br><?php print get_text('StaffOnField','Tournament');?></a>
                    </div>
            </td>
			<td style="width:50%;"  class="Center">
                <div style="margin-bottom: 2vh;">
                    <a href="OrisStaffField.php" class="Link" target="ORISPrintOut"><img src="../Common/Images/pdfOris.gif" alt="<?php print get_text('StaffOnField','Tournament');?>"><br><?php print get_text('StaffOnField','Tournament');?></a>
                </div>

            </td>
		</tr>
	</table>


<br>
<table class="Tabella">
	<tr><td class="Center">
		<a class="Link" href="index.php"><?php echo get_text('Back') ?></a>
	</td></tr>
</table>
<?php
	include('Common/Templates/tail.php');
