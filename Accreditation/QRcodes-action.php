<?php


require_once(dirname(dirname(__FILE__)).'/config.php');
require_once('Common/Lib/CommonLib.php');
require_once('Common/Lib/Fun_Modules.php');
require_once('../Api/ISK-NG/config_defines.php');

$JSON=['error'=>1, 'msg'=>''];

if(!CheckTourSession() or !hasFullACL(AclAccreditation, 'acAdvanced', AclReadWrite)) {
    JsonOut($JSON);
}

switch($_REQUEST['act']??'') {
    case 'changeType':
        switch($_REQUEST['type']??'') {
            case 'http':
            case 'socket':
                $QrCodeSetting=getModuleParameter('GateNG', 'Settings', [
                    'type'=>'http',
                    'url'=>'http://'.gethostbyname($_SERVER['HTTP_HOST']).$CFG->ROOT_DIR,
                    'socket'=>getModuleParameter('ISK-NG', 'SocketIP', gethostbyname($_SERVER['HTTP_HOST'])),
                    'port'=>getModuleParameter('ISK-NG', 'SocketPort', '12346'),
                ]);
            $QrCodeSetting['type']=$_REQUEST['type'];
            setModuleParameter('GateNG', 'Settings', $QrCodeSetting);
            $JSON['error']=0;
            break;
        }
        break;
    case 'setSetting':
        $QrCodeSetting=getParameter('GateNGConnection', '', [
            'type'=>$_SESSION['UseApi']==ISK_NG_LIVE_CODE ? 'socket' : 'http',
            'url'=>'http://'.gethostbyname($_SERVER['HTTP_HOST']).$CFG->ROOT_DIR,
            'socket'=>getModuleParameter('ISK-NG', 'SocketIP', gethostbyname($_SERVER['HTTP_HOST'])),
            'port'=>getModuleParameter('ISK-NG', 'SocketPort', '12346'),
            ], true);

        switch($_REQUEST['id']??'') {
            case 'serverAddress':
                $QrCodeSetting['url']=($_REQUEST['val']??'');
                break;
            case 'socketAddress':
                $QrCodeSetting['socket']=($_REQUEST['val']??'');
                break;
            case 'socketPort':
                $QrCodeSetting['port']=($_REQUEST['val']??'');
                break;
        }
        setParameter('GateNGConnection', $QrCodeSetting, true);
        $JSON['error']=0;

        break;
    case 'updateGlobal':
        $fld=($_REQUEST['fld']??'');
        $val=intval($_REQUEST['value']??0);
        if(in_array($fld, ['lookupMode','validated', 'checkGateFlow', 'competingOnly', 'showPictures', 'showFlags', 'playSounds','enableHaptics'])) {
            SetParameter('GateNG-'.$fld, $val);
            $JSON['error']=0;
        }
        break;
    case 'updateArea':
        $fld=intval($_REQUEST['fld']??0);
        $val=intval($_REQUEST['value']??0);
        if(in_array($fld, range(0,6))) {
            $AccessZones=getParameter('GateNG-ZonesEnabled', '', [0], true);
            if($val) {
                if(!in_array($fld, $AccessZones)) {
                    $AccessZones[]=$fld;
                }
                if($fld) {
                    if(in_array(0, $AccessZones)) {
                        unset($AccessZones[array_search(0, $AccessZones)]);
                    }
                } else {
                    $AccessZones=[0];
                }
            } elseif(in_array($fld, $AccessZones)) {
                unset($AccessZones[array_search($fld, $AccessZones)]);
            }
            $AccessZones=array_values($AccessZones);
            SetParameter('GateNG-ZonesEnabled', $AccessZones,true);
            $JSON['error']=0;
            $JSON['values']=$AccessZones;
        }
        break;
    case 'updateAddon':
        $CompId=intval($_REQUEST['comp']??0);
        $val=intval($_REQUEST['value']??0);
        $fld=intval($_REQUEST['fld']??0);
        if(getModuleParameter("ExtraAddOns","AddOnsEnable","0", $CompId)) {
            if($AdOns = getModuleParameter("ExtraAddOns", "AddOnsList", array(),$CompId)) {
                $SelectedAddons = GetParameter('GateNG-Addons-' . $CompId, '', [], true);
                if($val) {
                    if(!in_array($fld, $SelectedAddons)) {
                        $SelectedAddons[]=$fld;
                    }
                } elseif(in_array($fld, $SelectedAddons)) {
                    unset($SelectedAddons[array_search($fld, $SelectedAddons)]);
                }
                $SelectedAddons=array_values($SelectedAddons);
                SetParameter('GateNG-Addons-' . $CompId, $SelectedAddons,true);
                $JSON['error']=0;
            }
        }
        break;
}
JsonOut($JSON);


