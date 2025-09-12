<?php

require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Common/Lib/CommonLib.php');
CheckTourSession(true);
checkFullACL(AclCompetition, 'cSchedule', AclReadOnly);

$PAGE_TITLE=get_text('FopSetup');
$JS_SCRIPT=array(
	'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Common/ajax/ObjXMLHttpRequest.js"></script>',
	'<script type="text/javascript" src="./Fun_AJAX_FopSetup.js"></script>',
	'<script type="text/javascript" >var StrConfirm="' . get_text('MsgAreYouSure') . '";</script>'
	);

if(!$FopLocations=Get_Tournament_Option('FopLocations')) {
	$FopLocations=array();
	Set_Tournament_Option('FopLocations', $FopLocations);
}

include('Common/Templates/head.php');

echo '<form name="FrmParam" method="GET" action="'.$CFG->ROOT_DIR.'Scheduler/" target="FOP">
    <input type="hidden" name="fop" value="1">
	<table class="Tabella2" id="FopTable">
		<tr><th class="Title" colspan="4">'.get_text('FopSetup').'</th></tr>
		<tr class="Divider"><td colspan="4"></td></tr>
		<tbody id="options">';
echo '<tr>';
echo '<th>'.get_text('Days', 'Tournament').'</th>';
echo '<td colspan="3">';
$Dates=[];
$q=safe_r_sql("select distinct date_format(SesDtStart, '%Y-%m-%d') as SesDate from Session where SesTournament={$_SESSION['TourId']} and SesDtStart>0
    union
    select distinct FSScheduledDate as SesDate from FinSchedule where FSTournament={$_SESSION['TourId']} and FSScheduledDate>0
    union
    select distinct SchDay as SesDate from Scheduler where SchTournament={$_SESSION['TourId']} and SchDay>0
    order by SesDate");
while($r=safe_fetch($q)) {
    echo '<input type="checkbox" name="Days['.$r->SesDate.']" value="'.$r->SesDate.'" '.(empty($_REQUEST['Day']) || isset($_REQUEST['Day'][$r->SesDate]) ? 'checked="checked"' : '').'>'.$r->SesDate.'<br/>';
}
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<th>'.get_text('Print', 'Tournament').'</th>';
echo '<th>'.get_text('Location', 'Tournament').'</th>';
echo '<th>'.get_text('FirstTarget', 'Tournament').'</th>';
echo '<th>'.get_text('LastTarget', 'Tournament').'</th>';
echo '</tr>';
$n=0;
foreach($FopLocations as $v) {
	echo '<tr id="Row'.$n.'">';
	echo '<td><input type="checkbox" name="Locations['.$n.']" '.(empty($_REQUEST['Print']) || isset($_REQUEST['Print'][$n]) ? 'checked="checked"' : '').'></td>';
	echo '<td><input type="text" value="'.$v->Loc.'" id="Location['.$n.']" onchange="UpdateField(this)"></td>';
	echo '<td><input type="text" value="'.$v->Tg1.'" id="Start['.$n.']" onchange="UpdateField(this)"></td>';
	echo '<td><input type="text" value="'.$v->Tg2.'" id="End['.$n.']" onchange="UpdateField(this)"></td>';
	echo '<td><img src="../Common/Images/drop.png" onclick="DeleteLocation(\'Row'.$n.'\')"></td>';
	echo '</tr>';
	$n++;
}
echo '<tr>';
echo '<td><input type="button" value="'.get_text('CmdAdd', 'Tournament').'" name="Add" onclick="AddLocation()"></td>';
echo '<td><input type="text" value="" id="LocLocation"></td>';
echo '<td><input type="text" value="" id="LocStart"></td>';
echo '<td><input type="text" value="" id="LocEnd"></td>';
echo '</tr>';

echo '<tr class="Divider"><td colspan="4"></td></tr>';

echo '<tr><td colspan="4"><input type="submit" value="'.get_text('PrintFOP', 'Tournament').'"></td></tr>';

?>



		</tbody>
	</table>
</form>
<?php


include('Common/Templates/tail' . (isset($_REQUEST["hideMenu"]) ? '-min' : '') . '.php');
