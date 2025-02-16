<?php

if (subFeatureAcl($acl, AclISKServer, '') >= AclReadOnly) {
	require_once(__DIR__.'/config.php');
	if($_SESSION['UseApi']!=ISK_NG_LITE_CODE) {
        if (subFeatureAcl($acl, AclISKServer, 'iskManagement') == AclReadWrite) {
            $ret['API'][] = get_text('ISK-Configuration') . '|' . $CFG->ROOT_DIR . 'Api/ISK-NG/Devices.php';
        }
        if (subFeatureAcl($acl, AclISKServer, 'iskUser') == AclReadWrite) {
            $ret['API'][] = get_text('ISK-Results') . '|' . $CFG->ROOT_DIR . 'Api/ISK-NG/Results.php';
        }
        if (subFeatureAcl($acl, AclISKServer, 'iskUser') >= AclReadOnly) {
            $ret['API'][] = get_text('FieldMonitor', 'Api') . '|' . $CFG->ROOT_DIR . 'Api/ISK-NG/FieldMonitor.php';
            if ($_SESSION['UseApi'] == ISK_NG_LIVE_CODE and module_exists('ISK-NG_Live')) {
                $ret['API'][] = get_text('MenuLM_Get Info') . '|' . $CFG->ROOT_DIR . 'Api/ISK-NG/DevicesInfo.php';
            }
        }
        if(count($ret['API'])>1) {
            $ret['API'][] = MENU_DIVIDER;
        }
    }
    if (subFeatureAcl($acl, AclISKServer, 'iskManagement') == AclReadWrite) {
        $ret['API'][] = get_text('ISK-AppQrCode', 'Api') . '|' . $CFG->ROOT_DIR . 'Api/ISK-NG/QRcodes.php|||QrCode';
    }
    if(subFeatureAcl($acl, AclISKServer, 'iskUser') == AclReadWrite) {
        if ($_SESSION['UseApi'] == ISK_NG_LIVE_CODE and module_exists('ISK-NG_Live')) {
            $ret['API'][] = get_text('ISK-GetQRData') . '|' . $CFG->ROOT_DIR . 'Api/ISK-NG/ManualDataDownload.php';
        }
        if ($_SESSION['UseApi'] != ISK_NG_LITE_CODE and getModuleParameter('ISK-NG', 'UsePersonalDevices')) {
            $ret['API'][] = get_text('TargetRequests-Printout', 'Api') . '|' . $CFG->ROOT_DIR . 'Api/ISK-NG/TargetRequests.php';
        } else if ($_SESSION['UseApi'] == ISK_NG_LITE_CODE) {
            $ret['API'][] = get_text('TargetScoring-Printout', 'Api') . '|' . $CFG->ROOT_DIR . 'Api/ISK-NG/TargetDistanceRequests.php';
        }
        $ret['API'][] = MENU_DIVIDER;
    }
    if(subFeatureAcl($acl, AclISKServer, 'iskManagement') == AclReadWrite) {
        if ($_SESSION['UseApi'] == ISK_NG_LITE_CODE) {
            $ret['API'][] = get_text('ManageLockedSessions', 'Api') . '|' . $CFG->ROOT_DIR . 'Api/ISK-NG/Sessions.php';
        }
        $ret['API'][] = get_text('RankCalcSettings', 'Api') . '|' . $CFG->ROOT_DIR . 'Api/ISK-NG/RankCalcSettings.php';
        $ret['API'][] = get_text('API-DeviceGrouping', 'Api') . '|' . $CFG->ROOT_DIR . 'Api/ISK-NG/DeviceGrouping.php';
    }
}