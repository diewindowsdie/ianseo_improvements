<?php

/*
IanseoServer: '',

  enableWIFIManagement: false,
  WifiSearch: 60,
  WifiResetCounter: 5,
  WifiDELETE: false,

  WifiSSID: [],
  WifiPWD: [],

  showPictures: false,
*/

require_once(dirname(dirname(__FILE__)).'/config.php');
require_once('Common/Lib/CommonLib.php');
require_once('Common/Lib/Fun_Modules.php');
require_once('../Api/ISK-NG/config_defines.php');

CheckTourSession(true);
checkFullACL(AclAccreditation, 'acAdvanced', AclReadWrite);


$PAGE_TITLE=get_text('MenuLM_QrCodesGates');
$IncludeJquery = true;
$IncludeFA = true;
$JS_SCRIPT=array(
		phpVars2js(array(
			'WifiSSID' => get_text('ISK-WifiSSID','Api'),
			'WifiPWD' => get_text('ISK-WifiPWD','Api'),
            'WifiUse' => get_text('ISK-WifiUse','Api'),
            'WifiTargetRange' => get_text('ISK-WifiTargetRange','Api'),
            'tourCode' => $_SESSION["TourCode"],
		)),
		'<script type="text/javascript" src="./QRcodes.js"></script>',
        '<link href="../Api/ISK-NG/isk.css" rel="stylesheet" type="text/css">',
);

$QrCodeSetting=getParameter('GateNGConnection', '', [
    'type'=>$_SESSION['UseApi']==ISK_NG_LIVE_CODE ? 'socket' : 'http',
    'url'=>'http://'.gethostbyname($_SERVER['HTTP_HOST']).$CFG->ROOT_DIR,
    'socket'=>getModuleParameter('ISK-NG', 'SocketIP', gethostbyname($_SERVER['HTTP_HOST'])),
    'port'=>getModuleParameter('ISK-NG', 'SocketPort', '12346'),
    ], true);

include('Common/Templates/head.php');

echo '<table class="Tabella" style="width:auto;margin:auto;">';
echo '<tr><th class="Title" colspan="3">' . get_text('App-QrCode', 'Tournament') . '</th></tr>';

// Type of connection
echo '<tr>
    <th colspan="2">' . get_text('ConnectionType', 'Api') . '</th>
    <td><select id="type" onchange="changeType(this)">
    <option value="http" '.($QrCodeSetting['type']=='http'?'selected="selected"':'').'>'.get_text('ConnectionHttp', 'Api').'</option>
    <option value="socket"'.($QrCodeSetting['type']=='socket'?'selected="selected"':'').'>'.get_text('ConnectionSocket', 'Api').'</option>
    </select></td>
    </tr>';

// HTTP connection type
echo '<tr class="showHttp d-none">
    <th colspan="2">' . get_text('ISK-ServerUrl','Api') . '</th>
    <td><input type="text" id="serverAddress" value="' . $QrCodeSetting['url'] . '" onchange="updateSettings(this)"></td>
    </tr>';

// Socket connection type
echo '<tr class="showSocket d-none">
    <th colspan="2">' . get_text('ISK-SocketIP','Api') . '</th>
    <td><input type="text" id="socketAddress" value="' . $QrCodeSetting['socket'] . '" onchange="updateSettings(this)"></td>
    </tr>';
echo '<tr class="showSocket d-none">
    <th colspan="2">' . get_text('ISK-SocketPort','Api') . '</th>
    <td><input type="text" id="socketPort" value="' . $QrCodeSetting['port'] . '" onchange="updateSettings(this)"></td>
    </tr>';

echo '<tr class="divider"></tr>';

echo '<tr class="SocketConnection"><th class="Title" colspan="5">' . get_text('ISK-ConnectionStatus','Api') . '</th></tr>'.
    '<tr class="SocketConnection"><td colspan="5" id="ctrConnStatus" class="socketOFF" ondblclick="changeMasterSocket()">DISCONNECTED</td></tr>'.
    '<tr class="SocketConnection Divider"><td colspan="5"></td></tr>';

echo '<tr>
	<th colspan="5">
		<input type="button" class="mx-3" id="print" onclick="print()" value="Print QR Configuration Codes">
		</th>
	</tr>';
echo '</table>';

// gets all the options
echo '<table class="Tabella" style="width:auto;margin:auto;margin-top:1em;">';
// lookup
$lu=GetParameter('GateNG-lookupMode', '', 0);
echo '<tr>
        <th class="Right">' . get_text('GateNG-lookupMode', 'Api') . '</th>
        <td><select onchange="updateGlobal(this)" ref="lookupMode">
            <option value="0" '.($lu==0?'selected="selected"':'').'>'.get_text('GateOnlineOnly','Api').'</option>
            <option value="1" '.($lu==1?'selected="selected"':'').'>'.get_text('GateBackup','Api').'</option>
            <option value="2" '.($lu==2?'selected="selected"':'').'>'.get_text('GateOfflineOnly','Api').'</option>
        </select>
        </tr>';
foreach(['validated', 'checkGateFlow', 'competingOnly', 'showPictures', 'showFlags', 'playSounds','enableHaptics'] as $k) {
    echo '<tr>
        <th class="Right">' . get_text('GateNG-'.$k, 'Api') . '</th>
        <td class="Center"><i onclick="updateGlobal(this)" ref="'.$k.'" class="fa fa-2x '.(getParameter('GateNG-'.$k, '', in_array($k,['showPictures', 'showFlags'])?1:0)?'fa-toggle-on text-success':'fa-toggle-off text-secondary').'"></i></td>
        </tr>';
}
echo '<tr class="Divider"><td class="Divider" colspan="2"></td></tr>';

// get the access zones
$AccessZones=getParameter('GateNG-ZonesEnabled', '', [0], true);
foreach(range(0,6) as $zone) {
    echo '<tr>
        <th class="Right">' . get_text('Area_'.$zone, 'Tournament') . '</th>
        <td class="Center"><i onclick="updateArea(this)" ref="'.$zone.'" class="Zones fa fa-2x '.(in_array($zone, $AccessZones)?'fa-toggle-on text-success':'fa-toggle-off text-secondary').'"></i></td>
        </tr>';
}
echo '</table>';

// Extra addons to activate
$Options=GetParameter('AccessApp', false, array(), true);
echo '<div class="Center">';
foreach($Options as $CompId=>$v) {
    // we only get
    if(getModuleParameter("ExtraAddOns","AddOnsEnable","0", $CompId)) {
        if($AdOns = getModuleParameter("ExtraAddOns", "AddOnsList", array(),$CompId)) {

            $SelectedAddons=GetParameter('GateNG-Addons-'.$CompId, '', [], true);
            echo '<table class="Tabella" style="width:auto;margin:1em 0.5em 0;display: inline-table">';
            $q=safe_r_sql("select ToCode from Tournament where ToId=$CompId");
            $r=safe_fetch($q);
            echo '<tr><th class="Title" colspan="3">'.$r->ToCode.'</th></tr>';
            foreach($AdOns as $key=>$addon) {
                echo '<tr>
                    <th class="Right">' . $addon . '</th>
                    <td class="Center"><i onclick="updateAddon(this)" comp="'.$CompId.'" ref="'.$key.'" class="fa fa-2x '.(in_array($key, $SelectedAddons)?'fa-toggle-on text-success':'fa-toggle-off text-secondary').'"></i></td>
                    </tr>';
            }
            echo '</table>';
        }
    }
}
echo '</div>';

//echo '<div>'.$Code.'</div>';

include('Common/Templates/tail.php');
