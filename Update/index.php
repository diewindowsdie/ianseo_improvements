<?php

require_once(dirname(__FILE__, 2) . '/config.php');
checkFullACL(AclRoot, '', AclReadWrite);
checkIanseoLicense(true);

if(!empty($_SESSION['AUTH_ENABLE']) AND empty($_SESSION['AUTH_ROOT'])) {
    CD_redirect($CFG->ROOT_DIR.'noAccess.php');
}

// check if the 2018 updateDB has been actually performed
$q=safe_r_SQL("SHOW COLUMNS FROM Finals like 'FinDateTime'");
if($r=safe_fetch($q) and $r->Type!='datetime(3)') {
    $q=safe_r_SQL("select version() as SqlVersion");
    if($r=safe_fetch($q)) {
        $v=explode('.', $r->SqlVersion);
        if(!($v[0]<5 and $v[1]<6)) {
            $q="ALTER TABLE `Finals` change `FinDateTime` FinDateTime DATETIME(3) NOT NULL default '0000-00-00 00:00:00.000'";
            $r=safe_w_sql($q,false,array(1146, 1060));
            $q="ALTER TABLE `TeamFinals` change `TfDateTime` TfDateTime DATETIME(3) NOT NULL default '0000-00-00 00:00:00.000'";
            $r=safe_w_sql($q,false,array(1146, 1060));
        }
    }
}


// check if a major update of Mysql is needed!
$NeedsUpdate='';
$UpdateMessage=UpdateToInnoDb(false);
if($UpdateMessage===true) {
    // DB needs to be updated!
    $NeedsUpdate='<div class="alert alert-danger bold">'.get_text('MysqlInnoDbProcess', 'Errors').'</div>';
    $UpdateMessage= '';
}

require_once('Common/Lib/CommonLib.php');
$IncludeJquery = true;
$JS_SCRIPT=array(
    phpVars2js(array(
        'cmdClose' => get_text('Close'),
        'cmdForceUpdate' => get_text('cmdForceUpdate','Install'),
    )),
    '<link rel="stylesheet" href="index.css">',
    '<script src="./index.js"></script>',
    '<script src="../Common/js/Fun_JS.inc.js"></script>',
);

include('Common/Templates/head.php');

$FileToCheck=__DIR__.'/check';

$f=@fopen($FileToCheck, 'w');
if($f) {
    echo '<div class="Center" style="width:50%;margin:auto;margin-top:2em">
        <div class="alert alert-warning bold">
            <div>'.get_text('BackupTournaments', 'Errors').'</div>';
    if(in_array('zip',get_loaded_extensions())) {
        echo '<div class="Button mt-3" onclick="exportAllCompetitions()">' . get_text('ExportAllComps', 'Install') . '</div>';
    } else {
        echo '<div class="mt-2">'.get_text('MissingZipExtension', 'Errors').'</div>';;
    }

    echo '</div>'
        .$UpdateMessage;
    if($NeedsUpdate) {
        echo '<div id="InnoDbUpdateTable">
            '.$NeedsUpdate.'
            <div><input type="button" value="' . get_text('CmdOk') . '" onclick="doInnoDbUpdate()"></div>
            </div>';
    }

    echo '<div id="UpdateTable" class="text-small '.($NeedsUpdate ? 'd-none' : '').'">
        <div class="alert alert-success">'.get_text('UpdatePrepared', 'Install') .'</div>';

    if(!in_array(ProgramRelease, array('STABLE','FITARCO')) or isset($_GET['testing'])) {
        echo '<div class="alert alert-info mt-2">
            <div><b>'.get_text('SpecialUpdate', 'Install').'<b></b></div>
            <div style="display:flex" class="mt-2"><div class="text-right mx-2">' . get_text('Email','Install') . ':</div><div class="w-100"><input type="text" name="Email" id="Email"  class="w-100"></div></div>
            </div>';
    }

    echo '<div class="alert"><input type="button" value="' . get_text('CmdOk') . '" onclick="doUpdate()"></div>';
	echo '</div>';
	echo '</div>';
	fclose($f);
	unlink($FileToCheck);
} else {
	echo '<div class="Center">';
	echo '<table class="Tabella" style="width:50%">';
	echo '<tr><td colspan="2">'.get_text('NotUpdatable', 'Install').'</td></tr>';
	echo '</table>';
	echo '</div>';
}


include('Common/Templates/tail.php');
