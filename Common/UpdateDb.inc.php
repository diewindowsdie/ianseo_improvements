<?php
include_once('UpdateFunctions.inc.php');

/*
ogni step viene salvato separatamente al proprio numero di versione...
creato un numero di versione DB apposito...
Se la versione è troppo vecchia include i vecchi file

*/

if($version <= '2011-01-01 00:00:00') require_once('Common/UpdateDb-2010.inc.php');
if($version <= '2012-01-01 00:00:00') require_once('Common/UpdateDb-2011.inc.php');
if($version <= '2013-01-01 00:00:00') require_once('Common/UpdateDb-2012.inc.php');
if($version <= '2014-01-01 00:00:00') require_once('Common/UpdateDb-2013.inc.php');
if($version <= '2015-01-01 00:00:00') require_once('Common/UpdateDb-2014.inc.php');
if($version <= '2016-01-01 00:00:00') require_once('Common/UpdateDb-2015.inc.php');
if($version <= '2017-01-01 00:00:00') require_once('Common/UpdateDb-2016.inc.php');
if($version <= '2018-01-01 00:00:00') require_once('Common/UpdateDb-2017.inc.php');
if($version <= '2019-01-01 00:00:00') require_once('Common/UpdateDb-2018.inc.php');
if($version <= '2020-01-01 00:00:00') require_once('Common/UpdateDb-2019.inc.php');
if($version <= '2021-01-01 00:00:00') require_once('Common/UpdateDb-2020.inc.php');
if($version <= '2022-01-01 00:00:00') require_once('Common/UpdateDb-2021.inc.php');
if($version <= '2023-01-01 00:00:00') require_once('Common/UpdateDb-2022.inc.php');
if($version <= '2024-01-01 00:00:00') require_once('Common/UpdateDb-2023.inc.php');
if($version <= '2025-01-01 00:00:00') require_once('Common/UpdateDb-2024.inc.php');
if($version <= '2026-01-01 00:00:00') require_once('Common/UpdateDb-2025.inc.php');

if($version<'2026-01-03 15:15:01') {
    safe_w_sql("ALTER TABLE `Qualifications` ADD `QuSigned` INT NOT NULL AFTER `QuConfirm`",false,array(1146, 1054, 1060));
    safe_w_sql("ALTER TABLE `Eliminations` ADD `ElSigned` INT NOT NULL AFTER `ElConfirm`",false,array(1146, 1054, 1060));
    safe_w_sql("ALTER TABLE `RoundRobinMatches` ADD `RrMatchSigned` INT NOT NULL AFTER `RrMatchConfirmed`",false,array(1146, 1054, 1060));
    safe_w_sql("ALTER TABLE `Finals` ADD `FinSigned` INT NOT NULL AFTER `FinConfirmed`",false,array(1146, 1054, 1060));
    safe_w_sql("ALTER TABLE `TeamFinals` ADD `TfSigned` INT NOT NULL AFTER `TfConfirmed`",false,array(1146, 1054, 1060));
    safe_w_sql("ALTER TABLE `IskData` ADD `IskDtIsSigned` TINYINT NOT NULL AFTER `IskDtIsClosest`",false,array(1146, 1054, 1060));
    db_save_version('2026-01-03 15:15:01');
}

if($version<'2026-01-05 09:00:00') {
    safe_w_sql("ALTER TABLE `Teams` 
        ADD `TeTieWeight` VARCHAR(50) NOT NULL AFTER `TeIsValidTeam`, 
        ADD `TeTieWeightDrops` TEXT NOT NULL AFTER `TeTieWeight`, 
        ADD `TeTieWeightDecoded` VARCHAR(80) NOT NULL AFTER `TeTieWeightDrops`",false,array(1146, 1054, 1060));
    db_save_version('2026-01-05 09:00:00');
}

if($version<'2026-01-15 09:00:01') {
    safe_w_SQL("UPDATE `LookUpPaths` SET `LupClubNamesPath` = '%Modules/Sets/UK/Lookups/clublookup.php' WHERE `LupIocCode` = 'GBR'", false, array(1146, 1054, 1060));
    db_save_version('2026-01-05 09:00:01');
}

if($version<'2026-01-21 09:00:01') {
    safe_w_SQL("UPDATE `Targets` SET `TarFullSize` = '80', `G_size` = '40', `H_size` = '32', `I_size` = '24', `J_size` = '16', `L_size` = '8', `M_size` = '4', `N_size` = '3' WHERE `TarId` in (24,31)", false, array(1146, 1054, 1060));
    safe_w_SQL("UPDATE `Targets` SET `B_size` = '80', `C_size` = '72', `D_size` = '64', `E_size` = '56', `F_size` = '48' WHERE `TarId` = 24", false, array(1146, 1054, 1060));
    db_save_version('2026-01-21 09:00:01');
}

/*

// TEMPLATE
IMPORTANT: InfoSystem related things MUST be changed in the lib.php file!!!
REMEMBER TO CHANGE ALSO Common/Lib/UpdateTournament.inc.php!!!

if($version<'2025-05-07 09:25:00') {
    safe_w_sql("alter table RoundRobinMatches add index (RrMatchTournament, RrMatchTeam, RrMatchEvent)");
	db_save_version('2025-05-07 09:25:00');
}

*/

db_save_version($newversion);

function db_save_version($newversion) {
	global $CFG;
	//Aggiorno alla versione attuale SOLO le gare che erano alla versione immediatamente precedente
	$oldDbVersion = GetParameter('DBUpdate');
	safe_w_sql("UPDATE Tournament SET ToDbVersion='{$newversion}' WHERE ToDbVersion='{$oldDbVersion}'");

	SetParameter('DBUpdate', $newversion);
	SetParameter('SwUpdate', ProgramVersion);

	foreach(glob($CFG->DOCUMENT_PATH.'TV/Photos/*.ser') as $file) {
		@unlink($file);
		@unlink(substr($file, 0, -3).'check');
	}
}
