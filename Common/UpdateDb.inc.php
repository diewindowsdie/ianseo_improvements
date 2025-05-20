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

if($version<'2025-04-06 19:03:00') {
    safe_w_sql("ALTER TABLE `Events` 
        ADD `EvQualDistances` tinyint NOT NULL DEFAULT 0, 
        ADD `EvLuckyDogDistance` tinyINT NOT NULL DEFAULT 0, 
        ADD `EvSoDistance` tinyINT NOT NULL DEFAULT 0, 
        ADD `EvTiePositionSO` INT NOT NULL DEFAULT 0
        ",false,array(1054, 1060));
    db_save_version('2025-04-06 19:03:00');
}

if($version<'2025-04-07 11:00:05') {
    $q = safe_r_SQL("SELECT `FlTournament`,`FlCode`, `ToIocCode` as refCode, 
       GROUP_CONCAT(CONCAT('#',`FlIocCode`,'#') ORDER BY `FlIocCode`) as allCodes,  sum(if(`FlIocCode`=`ToIocCode`, 1, 0)) as refPresent
        FROM `Flags` INNER JOIN `Tournament` on `FlTournament`=`ToId`
        GROUP BY `FlTournament`,`FlCode` HAVING  count(`FlCode`)>1");
    while($r=safe_fetch($q)) {
        if(!$r->refPresent) {
            $tmp = explode(',', $r->allCodes);
            $r->refCode = str_replace("#","",$tmp[0]);
        }
        safe_w_sql("DELETE FROM `Flags` WHERE `FlTournament`={$r->FlTournament} AND `FlCode`='{$r->FlCode}' AND `FlIocCode`!='{$r->refCode}'");
    }
    safe_w_sql("ALTER TABLE `Flags` DROP PRIMARY KEY, ADD PRIMARY KEY (`FlTournament`, `FlCode`) USING BTREE",false,array(1072, 1062));
    safe_w_sql("ALTER TABLE `Events` ADD `EvQualBestOfDistances` tinyint NOT NULL DEFAULT 0",false,array(1054, 1060));
    safe_w_sql("UPDATE `Tournament` SET `ToTypeSubRule` = 'NFAAIndoor-Nationals' WHERE `ToType` = 32",false,array());
    db_save_version('2025-04-07 11:00:05');
}

if($version<'2025-05-07 09:25:02') {
    safe_w_sql("alter table IdCards add IcPage tinyint not null default 1");
    safe_w_sql("alter table IdCardElements add IceCardPage tinyint not null default 1, drop index IceTournament, drop index IceTournament_2, drop index IceTournament_3, add index (IceTournament, IceCardType, IceCardNumber, IceCardPage, IceOrder)");
    db_save_version('2025-05-07 09:25:02');
}

if($version<'2025-05-20 09:47:02') {
    safe_w_sql("REPLACE INTO `Targets` (`TarId`, `TarDescr`, `TarArray`, `TarStars`, `TarOrder`, `TarFullSize`, `A_size`, `A_color`, `B_size`, `B_color`, `C_size`, `C_color`, `D_size`, `D_color`, `E_size`, `E_color`, `F_size`, `F_color`, `G_size`, `G_color`, `H_size`, `H_color`, `I_size`, `I_color`, `J_size`, `J_color`, `K_size`, `K_color`, `L_size`, `L_color`, `M_size`, `M_color`, `N_size`, `N_color`, `O_size`, `O_color`, `P_size`, `P_color`, `Q_size`, `Q_color`, `R_size`, `R_color`, `S_size`, `S_color`, `T_size`, `T_color`, `U_size`, `U_color`, `V_size`, `V_color`, `W_size`, `W_color`, `X_size`, `X_color`, `Y_size`, `Y_color`, `Z_size`, `Z_color`, `TarDummyLine`, `1_size`, `1_color`, `2_size`, `2_color`, `3_size`, `3_color`, `4_size`, `4_color`, `5_size`, `5_color`, `6_size`, `6_color`, `7_size`, `7_color`, `8_size`, `8_color`, `9_size`, `9_color`, `TarIskDefinition`) 
        VALUES (29, 'TrgLancSix', 'TrgLancSix', 'ag-l', '24', '100', '0', '', '0', 'FFFFFF', '0', 'FFFFFF', '0', '000000', '0', '000000', '0', '00A3D1', '50', '00A3D1', '40', 'ED2939', '30', 'ED2939', '20', 'F9E11E', '0', '', '10', 'F9E11E', '5', 'F9E11E', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', ''),
        (30, 'TrgLancFive', 'TrgLancFive', 'af-l', '24', '100', '0', '', '0', 'FFFFFF', '0', 'FFFFFF', '0', '000000', '0', '000000', '60', '00A3D1', '50', '00A3D1', '40', 'ED2939', '30', 'ED2939', '20', 'F9E11E', '0', '', '10', 'F9E11E', '5', 'F9E11E', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', ''),
        (31, 'TrgLancSixShootUp', 'TrgLancSixShootUp', 'ag-j', '24', '100', '0', '', '0', 'FFFFFF', '0', 'FFFFFF', '0', '000000', '0', '000000', '0', '00A3D1', '50', '00A3D1', '40', 'ED2939', '30', 'ED2939', '20', 'F9E11E', '0', '', '10', 'F9E11E', '5', 'F9E11E', '2', 'FFFFFF', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '')");
    db_save_version('2025-05-20 09:47:00');
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
