<?php
require_once('config.php');
global $listACL;

if(isset($_REQUEST['ToCode']) and isset($_REQUEST['Feature']) and ($feature = array_search($_REQUEST['Feature'],$listACL)) !== false and isset($_REQUEST['Level']) and isset($_REQUEST['IP'])) {
    if(isset($_SERVER['HTTP_X_IANSEO_USER']) and isset($_SERVER['HTTP_X_IANSEO_PASSWORD'])) {
        DoLogin($_SERVER['HTTP_X_IANSEO_USER'], $_SERVER['HTTP_X_IANSEO_PASSWORD']);
    }
    $tmp = hasFullACL($feature,'', intval($_REQUEST['Level']) ? AclReadOnly : AclReadWrite, getIdFromCode($_REQUEST['ToCode']), $_REQUEST['IP']);
    $login = false;
    if(AuthModule and intval(getParameter("AuthON", false, 0)) !=0 ) {
        $login = true;
    }
    JSONOut(array('valid'=>$tmp, 'login'=>$login));
} else {
    JSONOut(array('valid'=>false, 'login'=>false));
}

