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

if($version<'2025-01-12 10:46:03') {
    safe_w_sql("CREATE TABLE IF NOT EXISTS `AclTournaments` (
        `AclToPattern` VARCHAR(150) NOT NULL , 
        `AclToNick` VARCHAR(50) NOT NULL , 
        `AclToEnabled` TINYINT NOT NULL , 
        PRIMARY KEY (`AclToPattern`)) ENGINE = InnoDB;",false,array(1050));
    safe_w_sql("ALTER TABLE `AclTournaments` ADD INDEX `AclToEnabled` (`AclToEnabled`, `AclToPattern`) USING BTREE",false,array(1061));
    $tmpValue = getParameter("AuthAllowCompAcl", false, 0);
    if(!empty($tmpValue)) {
        safe_w_sql("INSERT IGNORE INTO `AclTournaments` VALUES ('%','All Competitions - Old Setting',1)",false,array(1062));
    }
    DelParameter("AuthAllowCompAcl");
    db_save_version('2025-01-12 10:46:03');
}

if($version<'2025-02-24 14:17:01') {
    safe_w_sql("ALTER TABLE `Events` ADD `EvLockResults` TINYINT NOT NULL DEFAULT 0 AFTER `EvLoopPenalty`",false,array(1054, 1060));
    db_save_version('2025-02-24 14:17:01');
}

if($version<'2025-02-24 20:03:00') {
    safe_w_sql("ALTER TABLE `Individuals` 
        ADD `IndScore` INT NOT NULL DEFAULT '-1' AFTER `IndIrmTypeFinal`, 
        ADD `IndHits` INT NOT NULL DEFAULT '-1' AFTER `IndScore`, 
        ADD `IndGold` INT NOT NULL DEFAULT '-1' AFTER `IndHits`, 
        ADD `IndXnine` INT NOT NULL DEFAULT '-1' AFTER `IndGold`",false,array(1054, 1060));
    db_save_version('2025-02-24 20:03:00');
}

/*

// TEMPLATE
IMPORTANT: InfoSystem related things MUST be changed in the lib.php file!!!
REMEMBER TO CHANGE ALSO Common/Lib/UpdateTournament.inc.php!!!

if($version<'2024-06-08 15:25:00') {
    safe_w_sql("alter table RoundRobinMatches add index (RrMatchTournament, RrMatchTeam, RrMatchEvent)");
	db_save_version('2024-06-08 15:25:00');
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
