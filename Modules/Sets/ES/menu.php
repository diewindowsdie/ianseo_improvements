<?php

if (!empty($on) and !empty($_SESSION['TourLocRule']) and $_SESSION['TourLocRule'] == 'ES') {
	$ret['MODS'][] = 'Enviar a RFETA'.'|' . $CFG->ROOT_DIR . 'Modules/Sets/ES/send/index.php';
}

?>
