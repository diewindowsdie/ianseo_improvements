<?php

require_once(dirname(__FILE__, 3) . '/config.php');
checkFullACL(AclAccreditation, 'acSetup', AclReadWrite, false);

if(!empty($_REQUEST['SetCamUrl'])) {
	setcookie("CamUrl", $_REQUEST['SetCamUrl'], strtotime("+1 year"));  /* expire in 1 year */
	cd_redirect('Configuration.php');
}

/*

http://localhost:8080/requests/status.xml?command=pl_stop
http://localhost:8080/requests/status.xml?command=pl_pause&id=12

http://localhost:8080/requests/status.xml?command=in_play&input=v4l2%3A%2F%2F%2Fdev%2Fvideo0%20%20%20%20%3Asout%3D%23transcode{ vcodec=mjpg, fps=1, width=640, height=480 }:standard{ access=http, mux=mpjpeg, dst=0.0.0.0:8050/stream.mjpg }



*/

$JS_SCRIPT=array(
	'<script type="text/javascript" src="Fun_AJAX_GetImage.js"></script>',
	'<script type="text/javascript" src="../Fun_JS.js"></script>',
    '<link rel="stylesheet" type="text/css" href="./IdCard.css">',
	);

$ONLOAD=(' onLoad="javascript:reloadPicture()"');

include('Common/Templates/head.php');

?>
<style>
.Para {margin-top:1em;}
pre {border:1px solid black; padding:3px; margin:0;}
</style>
<form>
<table class="Tabella">
<tr>
	<th class="Title"><?php echo get_text('CamUrlSocket', 'Tournament'); ?></th>
	<th class="Title"><?php echo get_text('CamPreview', 'Tournament'); ?></th>
</tr>
<tr  valign="top">
	<td width="50%">
		<div class="Para"><b><?php echo get_text('CamUrlInsert', 'Tournament'); ?></b>
		<br/><input type="text" name="SetCamUrl" style="width:100%" value="<?php echo (empty($_COOKIE["CamUrl"]) ? 'http://localhost:8050/' : $_COOKIE["CamUrl"]) ?>" /></div>
		<div class="Para" align="center"><input type="submit" value="<?php echo get_text('CmdSend', 'Tournament'); ?>" /></div>
		<div class="Para"><?php echo get_text('CamDescriptionVLC', 'Tournament', array("sh " . $CFG->DOCUMENT_PATH . "Scripts", "VLC-Linux.sh", $CFG->DOCUMENT_PATH . "Scripts", "VLC-Windows.bat")); ?></div>
		<div class="Para"><?php echo get_text('CamDescriptionIPCamera', 'Tournament'); ?></div>
		<div class="Para Center" style="margin-top: 1em"><div class="Button"><?php echo get_text('Back'); ?></div></div>
	</td>
	<td>
		<img id="imgGrabbed" src="./grabImage.php" alt="Grab"/>
	</td>
</tr>
</table>
<input type="hidden" id="valueX" value="<?php echo (empty($_COOKIE["getPhotoX"]) ? '168' : $_COOKIE["getPhotoX"]) ?>" />
<input type="hidden" id="valueY" value="<?php echo (empty($_COOKIE["getPhotoY"]) ? '78' : $_COOKIE["getPhotoY"]) ?>" />
<input type="hidden" id="valueW" value="<?php echo (empty($_COOKIE["getPhotoW"]) ? '300' : $_COOKIE["getPhotoW"]) ?>" />
<input type="hidden" id="getSnap" value="0" />
<input type="hidden" id="AthId" value="0" />
</form>
<?php

include('Common/Templates/tail.php');

?>