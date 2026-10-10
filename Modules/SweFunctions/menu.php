<?php
$version='2026-10-07';

// Only a competition created under the federation's own rule set reports to it: see Federation.php.
require_once(dirname(__FILE__).'/Federation.php');
$SweFederation = swe_federation_code();

if(!empty($on) && $SweFederation == 'SE') {
	$ret['COMP']['EXPT'][] = '* Exportera svenska resultat|'.$CFG->ROOT_DIR.'Modules/SweFunctions/';
}
if(!empty($on) && $SweFederation == 'NO') {
	$ret['COMP']['EXPT'][] = '* Exportere norske resultat|'.$CFG->ROOT_DIR.'Modules/SweFunctions/';
}
if(!empty($on) && $SweFederation == 'IS') {
	$ret['COMP']['EXPT'][] = '* Senda íslensk úrslit|'.$CFG->ROOT_DIR.'Modules/SweFunctions/';
}
?>
