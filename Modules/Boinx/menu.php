<?php
$version='2011-05-13 08:13:00';

if (!empty($on) AND subFeatureAcl($acl, AclOutput, 'outCaspar') == AclReadWrite and !empty($ret['MEDI'])) {
	if(count($ret['MEDI'])>1 and end($ret['MEDI']) != MENU_DIVIDER) {
		$ret['MEDI'][] = MENU_DIVIDER;
	}
    $ret['MEDI'][] = get_text('MenuLM_ManageTvFlags').'|' . $CFG->ROOT_DIR . 'Modules/Boinx/ManageTVFlags.php';
    $ret['MEDI'][] = get_text('MenuLM_ShowTvFlags').'|' . $CFG->ROOT_DIR . 'Modules/Boinx/ShowTVFlags.php?Tour=' . $_SESSION['TourCode'] . '|||TV';
}
