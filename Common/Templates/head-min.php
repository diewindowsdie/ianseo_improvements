<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo PageEncode ?>">
<title><?php print ProgramName . ' ' . ProgramVersion . (defined('ProgramBuild') ? ' ('.ProgramBuild.')' : '');;?></title>
<?php

$local_JS = array();
if(empty($NOSTYLE)) {
	$local_JS[]='<link href="'.$CFG->ROOT_DIR.'Common/Styles/colors.css" media="screen" rel="stylesheet" type="text/css">';
	if($_SESSION['debug']) {
		$local_JS[]='<link href="'.$CFG->ROOT_DIR.'Common/Styles/colors_debug.css" media="screen" rel="stylesheet" type="text/css">';
	}
	$local_JS[]='<link href="'.$CFG->ROOT_DIR.'Common/Styles/Blue_screen.css" media="screen" rel="stylesheet" type="text/css">';

	if(SelectLanguage()=='tlh'){
		$local_JS[]= '<link href="'.$CFG->ROOT_DIR.'Common/Styles/klingon.css" rel="stylesheet" type="text/css">';
	}

	if(!empty($IncludeFA)) {
		$local_JS[]= '<link href="'.$CFG->ROOT_DIR.'Common/css/font-awesome.css" rel="stylesheet" type="text/css">';
	}

	if(!empty($IncludeJquery)) {
		$local_JS[]= '<script src="'.$CFG->ROOT_DIR.'Common/js/jquery-3.6.4.min.js"></script>';
		$local_JS[]= '<script src="'.$CFG->ROOT_DIR.'Common/js/jquery-confirm.min.js"></script>';
		$local_JS[]= '<link href="'.$CFG->ROOT_DIR.'Common/css/jquery-confirm.min.css" rel="stylesheet" type="text/css">';
	}
}

if(empty($JS_SCRIPT)) {
	$JS_SCRIPT=array();
}

$JS_SCRIPT = array_merge($local_JS, $JS_SCRIPT);

foreach($JS_SCRIPT as $script) {
    if(preg_match('#(src|href)="(\.){0,1}/(.*?)(\.js|\.css)"#i',$script, $matches)) {
        $file = ($matches[2] == '.' ? getcwd() : $_SERVER["DOCUMENT_ROOT"] ) . '/'. $matches[3] . $matches[4];
        if(file_exists($file)){
            $mtime = filemtime($file);
            $script = str_replace($matches[4],  $matches[4].'?ts=' . $mtime, $script);
        }
    }
    echo "$script\n";
}

?>
<body<?php echo (!empty($ONLOAD)?$ONLOAD:'') ?>>
