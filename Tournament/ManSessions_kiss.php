<?php
require_once(dirname(__FILE__, 2) . '/config.php');
require_once('Common/Lib/Fun_DateTime.inc.php');
require_once('Common/Fun_Sessions.inc.php');
require_once('Common/Fun_Various.inc.php');
require_once('Tournament/Fun_Tournament.local.inc.php');
require_once('Tournament/Fun_ManSessions.inc.php');
checkFullACL(AclCompetition, 'cSchedule', AclReadWrite);
if (!CheckTourSession()) {
    print get_text('CrackError');
    exit;
}

$IncludeFA=true;
$IncludeJquery=true;
$PAGE_TITLE=get_text('ManSession', 'Tournament');

$JS_SCRIPT = array(
    phpVars2js(array(
        "Tar4Session"=> get_text('Tar4Session', 'Tournament'),
        "Ath4Target" => get_text('Ath4Target', 'Tournament'),
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
    '<script type="text/javascript" src="ManSessions_kiss.js"></script>',
    '<script type="text/javascript" src="ManDistancesSessions.js"></script>',
    '<link href="./Mansession.css" rel="stylesheet" type="text/css">',
);

include('Common/Templates/head.php');

echo '<table class="Tabella">'.
    '<tr><th class="Title" colspan="4">'. get_text('ManSession', 'Tournament') . '</th></tr>'.
    '<tr class="Divider"><td colspan="4"></td></tr>'.
    '<tr>'.
        '<th class="TitleLeft w-15">'.get_text('NumSession', 'Tournament').'</th>'.
        '<td class="w-5"><input type="number" min="1" max="255" step="1" id="txtNumSession"></td>'.
        '<td class="w-10 Center"><input type="button" class="mx-2" value="'.get_text('CmdSave').'" onclick="ChangeNumSessions()"><input type="button" class="mx-2" value="'.get_text('CmdCancel').'" onclick="LoadSessions()"></td>'.
        '<td class="w-75">'.(defined('hideSchedulerAndAdvancedSession') ? '' : '<a  class="Link"  href="ManSessions.php">:' . get_text('Advanced') . ':</a>') .'</td>'.
    '</tr>'.
    '<tr class="Divider"><td colspan="4"></td></tr>'.
    '<tr><td colspan="4" id="sessionList"></td></tr>'.
'</table>';

echo '<br>';
echo '<table class="Tabella">'.
    '<tbody id="lstDistanceSession"></tbody>'.
    '<tfoot><tr><th colspan="11" style="padding:0.5em"><div class="Button" onclick="$(\'.advanced\').toggleClass(\'d-none\')">Advanced</div></th></tr></tfoot>'.
    '</table>';

include('Common/Templates/tail.php');

