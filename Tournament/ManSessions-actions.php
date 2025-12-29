<?php

require_once(dirname(dirname(__FILE__)) . '/config.php');
$JSON=['error'=>1, 'msg'=>get_text('ErrGenericError', 'Errors')];

if (defined('hideSchedulerAndAdvancedSession')) {
    die('');
}

if(!CheckTourSession() or !hasFullACL(AclCompetition, 'cSchedule', AclReadWrite)) {
    jsonout($JSON);
}

require_once('Common/Fun_Sessions.inc.php');

switch($_REQUEST['act']??'') {
    case 'getSesLocations':
        $SesLocations=getSesLocations();
        $JSON['error']=0;
        $JSON['locations']=orderSesLocations($SesLocations);
        break;
    case 'updateSesLocations':
        if(!isset($_REQUEST['ref']) or !isset($_REQUEST['val'])) {
            jsonout($JSON);
        }
        $SesLocations=getSesLocations();
        if(!isset($SesLocations[$_REQUEST['ref']])) {
            jsonout($JSON);
        }
        $SesLocations[$_REQUEST['ref']]['order']=max(0, intval($_REQUEST['val']));
        $JSON['error']=0;
        $JSON['locations']=orderSesLocations($SesLocations);
        $SesToSave=[];
        foreach($JSON['locations'] as $location) {
            $SesToSave[$location['location']]=$location['order'];
        }
        setModuleParameter('SesLocations', 'PrintOrder', $SesToSave);
        break;
}

jsonout($JSON);