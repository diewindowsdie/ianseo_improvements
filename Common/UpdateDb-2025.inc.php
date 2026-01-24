<?php
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

if($version<'2025-07-30 11:05:00') {
    safe_w_sql("ALTER TABLE `Session` ADD `SesEvents` TEXT NOT NULL AFTER `SesLocation`",false, array(1054, 1060));
    db_save_version('2025-07-30 11:05:00');
}

if($version<'2025-12-07 11:25:00') {
    safe_w_sql("UPDATE `LookUpPaths` SET `LupFlagsPath` = 'https://extranet.ffta.fr/ianseo/logo.php' WHERE `LupIocCode` = 'FRA'",false, array());
    safe_w_sql("ALTER TABLE `AvailableTarget` CHANGE `AtTargetNo` `AtTargetNo` VARCHAR(9) NOT NULL",false, array(1146, 1054));
    safe_w_sql("ALTER TABLE `Eliminations` CHANGE `ElTargetNo` `ElTargetNo` VARCHAR(9) NOT NULL",false, array(1146, 1054));
    safe_w_sql("ALTER TABLE `HhtData` CHANGE `HdTargetNo` `HdTargetNo` VARCHAR(9) NOT NULL",false, array(1146, 1054));
    safe_w_sql("ALTER TABLE `HTTData` CHANGE `HtdTargetNo` `HtdTargetNo` VARCHAR(9) NOT NULL",false, array(1146, 1054));
    safe_w_sql("ALTER TABLE `IskData` CHANGE `IskDtTargetNo` `IskDtTargetNo` VARCHAR(9) NOT NULL",false, array(1146, 1054));
    safe_w_sql("ALTER TABLE `Qualifications` CHANGE `QuTargetNo` `QuTargetNo` VARCHAR(9) NOT NULL",false, array(1146, 1054));
    db_save_version('2025-12-07 11:25:00');
}

if($version<'2025-12-11 16:50:00') {
    safe_w_sql("ALTER TABLE `IskDevices` CHANGE `IskDvTarget` `IskDvTarget` VARCHAR(4) NOT NULL, CHANGE `IskDvTargetReq` `IskDvTargetReq` VARCHAR(4) NOT NULL",false, array(1146, 1054));
    safe_w_sql("ALTER TABLE `DistanceInformation` CHANGE `DiSession` `DiSession` TINYINT UNSIGNED NOT NULL",false, array(1146, 1054));
    db_save_version('2025-12-11 16:50:00');
}

if($version<'2025-12-12 21:05:00') {
    safe_w_sql("ALTER TABLE `FinSchedule` CHANGE `FSTarget` `FSTarget` VARCHAR(4) NOT NULL",false, array(1146, 1054));
    safe_w_sql("ALTER TABLE `CasScore` CHANGE `CaSTarget` `CaSTarget` VARCHAR(4) NOT NULL",false, array(1146, 1054));
    safe_w_sql("ALTER TABLE `CasTeamTarget` CHANGE `CTTTarget` `CTTTarget` VARCHAR(4) NOT NULL",false, array(1146, 1054));
    safe_w_sql("ALTER TABLE `ClubTeamScore` CHANGE `CTSTarget` `CTSTarget` VARCHAR(4) NOT NULL",false, array(1146, 1054));
    safe_w_sql("ALTER TABLE `HhtData` CHANGE `HdRealTargetNo` `HdRealTargetNo` VARCHAR(4) NOT NULL",false, array(1146, 1054));
    db_save_version('2025-12-12 21:05:00');
}

if($version<'2025-12-18 17:50:00') {
    safe_w_sql("REPLACE INTO `Targets` (`TarId`, `TarDescr`, `TarArray`, `TarStars`, `TarOrder`, `TarFullSize`, 
        `A_size`, `A_color`, `B_size`, `B_color`, `C_size`, `C_color`, `D_size`, `D_color`, `E_size`, `E_color`, `F_size`, `F_color`, `G_size`, `G_color`, `H_size`, `H_color`, `I_size`, `I_color`, `J_size`, `J_color`, 
        `K_size`, `K_color`, `L_size`, `L_color`, `M_size`, `M_color`, `N_size`, `N_color`, `O_size`, `O_color`, `P_size`, `P_color`, `Q_size`, `Q_color`, `R_size`, `R_color`, `S_size`, `S_color`, `T_size`, `T_color`, 
        `U_size`, `U_color`, `V_size`, `V_color`, `W_size`, `W_color`, `X_size`, `X_color`, `Y_size`, `Y_color`, `Z_size`, `Z_color`, `TarDummyLine`, `1_size`, `1_color`, `2_size`, `2_color`, `3_size`, `3_color`, `4_size`, `4_color`, `5_size`, `5_color`, `6_size`, `6_color`, `7_size`, `7_color`, `8_size`, `8_color`, `9_size`, `9_color`, `TarIskDefinition`) 
        VALUES(32, 'TrgIndSmall5', 'TrgIndSmall5', 'af-j', '5', '100', '0', '', '0', '', '0', '', '0', '', '0', '', '60', '00A3D1', '50', '00A3D1', '40', 'ED2939', '30', 'ED2939', '20', 'F9E11E', '5', 'F9E11E', '10', 'F9E11E', 
       '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '')", false,  array(1146, 1054));
    db_save_version('2025-12-18 17:50:00');
}

if($version<'2025-12-31 22:00:01') {
    safe_w_SQL("ALTER TABLE `IskDevices` CHANGE `IskDvUrlDownload` `IskDvExtra` TEXT  NOT NULL",false, array(1146, 1054, 1060));
    safe_w_SQL("UPDATE `LookUpPaths` SET `LupClubNamesPath` = '%Modules/sets/UK/Lookups/clublookup.php' WHERE `LupIocCode` = 'GBR'",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `ElabQualifications` ADD `EqTieBreaker3` INT NOT NULL AFTER `EqXnine`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `Eliminations` CHANGE `ElScore` `ElScore` INT NOT NULL, CHANGE `ElHits` `ElHits` INT NOT NULL, CHANGE `ElGold` `ElGold` INT NOT NULL, CHANGE `ElXnine` `ElXnine` INT NOT NULL",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `Eliminations` ADD `ElTieBreaker3` INT NOT NULL AFTER `ElXnine`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `Events` ADD `EvTieBreaker3` VARCHAR(5) NOT NULL AFTER `EvXNine`, ADD `EvTieBreaker3Chars` VARCHAR(16) NOT NULL AFTER `EvXNineChars`, ADD `EvCheckTieBreaker3` TINYINT NOT NULL AFTER `EvCheckXNines`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `Finals` ADD `FinTieBreaker3` TINYINT NOT NULL AFTER `FinXNines`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `Individuals` ADD `IndTieBreaker3` INT NOT NULL DEFAULT '-1' AFTER `IndXnine`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `Qualifications` ADD `QuTieBreaker3` INT NOT NULL AFTER `QuXnine`,
        ADD `QuD1TieBreaker3` TINYINT NOT NULL AFTER `QuD1Xnine`, ADD `QuD2TieBreaker3` TINYINT NOT NULL AFTER `QuD2Xnine`,
        ADD `QuD3TieBreaker3` TINYINT NOT NULL AFTER `QuD3Xnine`, ADD `QuD4TieBreaker3` TINYINT NOT NULL AFTER `QuD4Xnine`, 
        ADD `QuD5TieBreaker3` TINYINT NOT NULL AFTER `QuD5Xnine`, ADD `QuD6TieBreaker3` TINYINT NOT NULL AFTER `QuD6Xnine`, 
        ADD `QuD7TieBreaker3` TINYINT NOT NULL AFTER `QuD7Xnine`, ADD `QuD8TieBreaker3` TINYINT NOT NULL AFTER `QuD8Xnine`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `RoundRobinLevel` ADD `RrLevCheckTieBreaker3` TINYINT NOT NULL AFTER `RrLevCheckXNines`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `RoundRobinMatches` ADD `RrMatchTieBreaker3` TINYINT NOT NULL AFTER `RrMatchXNines`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `TargetFaces` ADD `TfTieBreaker3` VARCHAR(5) NOT NULL AFTER `TfXNine`, ADD `TfTieBreaker3Chars` VARCHAR(5) NOT NULL AFTER `TfXNineChars`,
        ADD `TfTieBreaker3Chars1` VARCHAR(5) NOT NULL AFTER `TfXNineChars1`, ADD `TfTieBreaker3Chars2` VARCHAR(5) NOT NULL AFTER `TfXNineChars2`, ADD `TfTieBreaker3Chars3` VARCHAR(5) NOT NULL AFTER `TfXNineChars3`, ADD `TfTieBreaker3Chars4` VARCHAR(5) NOT NULL AFTER `TfXNineChars4`, 
        ADD `TfTieBreaker3Chars5` VARCHAR(5) NOT NULL AFTER `TfXNineChars5`, ADD `TfTieBreaker3Chars6` VARCHAR(5) NOT NULL AFTER `TfXNineChars6`, ADD `TfTieBreaker3Chars7` VARCHAR(5) NOT NULL AFTER `TfXNineChars7`, ADD `TfTieBreaker3Chars8` VARCHAR(5) NOT NULL AFTER `TfXNineChars8`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `TeamFinals` ADD `TfTieBreaker3` TINYINT NOT NULL  AFTER `TfXNines`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `TeamFinComponentStats` ADD `TfcStatTieBreaker3` INT NOT NULL DEFAULT '0' AFTER `TfcStatXNines`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `Teams` ADD `TeTieBreaker3` SMALLINT NOT NULL  AFTER `TeXnine`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `Tournament` ADD `ToTieBreaker3` VARCHAR(5) NOT NULL  AFTER `ToXNine`, ADD `ToTieBreaker3Chars` VARCHAR(16) NOT NULL AFTER `ToXNineChars`",false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `Qualifications` DROP INDEX `QuScore`, ADD INDEX `QuScore` (`QuScore`, `QuGold`, `QuXnine`, `QuTieBreaker3`, `QuTieWeight`) USING BTREE",false, array(1146, 1054, 1060, 1072));


    db_save_version('2025-12-31 22:00:01');
}
