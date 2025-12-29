<?php
global $CFG;
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Common/Lib/Fun_FormatText.inc.php');
require_once('Common/Lib/CommonLib.php');
checkFullACL(AclCompetition, 'cData', AclReadWrite);
CheckTourSession(true); // will print the crack error string if not inside a tournament!

$rs=safe_r_sql("SELECT ToType,ToNumDist AS TtNumDist
	FROM Tournament
	WHERE ToId={$_SESSION['TourId']}");
if(!safe_num_rows($rs)) {
	CD_redirect($CFG->ROOT_DIR);
}

$r=safe_fetch($rs);
$tourType=$r->ToType;
$numDist=$r->TtNumDist;

$rsDist=null;

$IncludeFA=true;
$IncludeJquery=true;
$PAGE_TITLE=get_text('ManDistances','Tournament');

$JS_SCRIPT = array(
    '<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Common/js/Fun_JS.inc.js"></script>',
    '<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Tournament/ManDistances.js"></script>',
    '<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Tournament/ManDistancesSessions.js"></script>',
	phpVars2js(array(
		'StrConfirm'=>get_text('MsgAreYouSure'),
		'NumDist'=>$numDist,
		'TourType'=>$tourType,
        "Session" => get_text('Session'),
        "headerDistanceSession" => '<tr>
            <th>'.get_text('Distance', 'Tournament').'</th>
            <th>'.get_text('Ends', 'Tournament').'</th>
            <th>'.get_text('ArrowsPerEnd', 'Tournament').'</th>
            <th class="d-none advanced">'.get_text('EndsToShoot', 'Tournament').'</th>
            <th class="d-none advanced">'.get_text('EndsOffset', 'Tournament').'</th>
            <th>'.get_text('Date', 'Tournament').'</th>
            <th>'.get_text('WarmUp', 'Tournament').'</th>
            <th>'.get_text('Length', 'Tournament').'</th>
            <th>'.get_text('Time', 'Tournament').'</th>
            <th>'.get_text('Length', 'Tournament').'</th>
            <th>'.get_text('ScheduleNotes', 'Tournament').'</th>
            </tr>'
		)),
    );

include('Common/Templates/head.php');

echo '<div style="margin:auto">';
echo '<table class="Tabella freeWidth mb-5">
	<tr><th class="Title" colspan="'.($numDist+3).'">'.(get_text('ManDistances','Tournament')).'</th></tr>
	<tr>
	    <th>'.(get_text('AvailableValues','Tournament')).'</th>
	    <th>'.(get_text('FilterOnDivCl','Tournament')).'</th>';
for ($i=1;$i<=$numDist;++$i) {
    echo '<th>.'.($i).'.</th>';
}
echo '<th></th>
    </tr>';

echo '<tr id="edit">
	<td id="categories"></td>
	<td class="Center"><input type="text" class="CheckDisabled" name="cl" size="12" maxlength="10" value=""></td>';
for ($i=1;$i<=$numDist;++$i) {
    echo '<td class="Center"><input type="text" class="CheckDisabled" name="td-'.($i).'" size="12" maxlength="10" value="" dist="'.$i.'"></td>';
}
echo '<td class="Center">
    <input type="button" name="command" class="CheckDisabled" value="'.(get_text('CmdOk')).'" onclick="save(this);">&nbsp;&nbsp;
    <input type="button" name="command" class="CheckDisabled" value="'.(get_text('CmdCancel')).'" onclick="resetInput(this)">
    </td>
    </tr>
    <tr class="Spacer"><td colspan="'.($numDist+3).'"></td></tr>
    <tbody id="tbody"></tbody>
    </table>';

// DISTANCE INFORMATION MANAGEMENT
echo '<table class="Tabella">'.
    '<tbody id="lstDistanceSession"></tbody>'.
    '<tfoot><tr><th colspan="11" style="padding:0.5em"><div class="Button" onclick="$(\'.advanced\').toggleClass(\'d-none\')">Advanced</div></th></tr></tfoot>'.
    '</table>';

echo '</div>
    <div id="idOutput"></div>';

include('Common/Templates/tail.php');

