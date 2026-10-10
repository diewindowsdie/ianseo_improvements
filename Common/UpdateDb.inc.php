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

if($version<'2026-02-08 16:34:01') {
    safe_w_SQL("Alter table `Entries` add `EnMainInfoUpdate` datetime not null after `EnTimestamp`", false, array(1146, 1054, 1060));
    safe_w_SQL("update `Entries` set `EnMainInfoUpdate`=`EnTimestamp`");
    db_save_version('2026-02-08 16:34:01');
}

if($version<'2026-03-01 08:10:02') {
    safe_w_SQL("INSERT INTO `LookUpPaths` (`LupIocCode`, `LupOrigin`, `LupPath`, `LupPhotoPath`, `LupFlagsPath`, `LupLastUpdate`, `LupRankingPath`, `LupClubNamesPath`, `LupRecordsPath`) 
        VALUES ('NFAA', '', '', '', 'https://extranet.worldarchery.org/Api/GetFlags.php', NOW(), '', '%Modules/LookUpFunctions/LookupFitaClubNames.php', '')", false, array(1062));
    safe_w_SQL("ALTER TABLE `Finals` ADD `FinAverageMatch` DECIMAL(6,3) NOT NULL AFTER `FinWinLose`, ADD `FinAverageTie` DECIMAL(6,3) NOT NULL AFTER `FinAverageMatch`", false, array(1146, 1054, 1060));
    safe_w_SQL("ALTER TABLE `TeamFinals` ADD `TfAverageMatch` DECIMAL(6,3) NOT NULL AFTER `TfWinLose`, ADD `TfAverageTie` DECIMAL(6,3) NOT NULL AFTER `TfAverageMatch`", false, array(1146, 1054, 1060));
    db_save_version('2026-03-01 08:10:02');
}

if($version<'2026-03-03 12:55:02') {
    safe_w_SQL("REPLACE INTO `AccOperationType` (`AOTId`, `AOTDescr`, `AOTOrder`) VALUES (4, 'Goodies', '30'), (5, 'Meal', '40'), (6, 'Other', '50')", false, array(1062));
    db_save_version('2026-03-03 12:55:02');
}

if($version<'2026-03-18 13:25:01') {
    safe_w_sql("drop table if exists `TeamQualificationDistances`");
    safe_w_sql("create table `TeamQualificationDistances` (
        `TqdCoId` int not null,
        `TqdSubTeam` tinyint not null,
        `TqdEvent` varchar(10) not null,
        `TqdTournament` int not null,
        `TqdDistance` tinyint not null,
        `TqdScore` smallint not null,
        `TqdBonus` tinyint not null,
        `TqdTie1` smallint not null,
        `TqdTie2` smallint not null,
        `TqdTie3` smallint not null,
        `TqdTie4` smallint not null,
        `TqdRank` smallint not null,
        `TqdTimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        primary key (`TqdCoId`, `TqdSubTeam`, `TqdEvent`, `TqdTournament`, `TqdDistance`),
        index (`TqdEvent`, `TqdTournament`, `TqdRank`),
        index (`TqdTournament`, `TqdEvent`, `TqdScore`, `TqdTie1`, `TqdTie2`, `TqdTie3`, `TqdTie4`))",false,array(1050));
    db_save_version('2026-03-18 13:25:01');
}

if($version<'2026-04-03 08:00:01') {
    safe_w_sql("REPLACE INTO `InvolvedType` (`ItId`, `ItDescription`, `ItJudge`, `ItDoS`, `ItJury`, `ItOC`) VALUES
        (13, 'TecDelegate', 0, 0, 0, 1),
        (18, 'CompManager', 0, 0, 0, 2),
        (17, 'MedOfficer', 0, 0, 0, 3),
        (23, 'HeadClassifier', 0, 0, 0, 4),
        (24, 'Classifier', 0, 0, 0, 5),
        (19, 'ResVerifier', 0, 0, 0, 6),
        (10, 'ResultResp', 0, 0, 0, 7),
        (9, 'FieldResp', 0, 0, 0, 8),
        (3, 'OrgResponsible', 0, 0, 0, 9),
        (12, 'MediaResp', 0, 0, 0, 10),
        (14, 'SportPres', 0, 0, 0, 11),
        (15, 'Announcer', 0, 0, 0, 12),
        (11, 'LogisticResp', 0, 0, 0, 13),
        (16, 'ADOfficer', 0, 0, 0, 14),
        (5, 'ChairmanJudge', 1, 0, 0, 0),
        (20, 'ChairmanJudgeDeputy', 2, 0, 0, 0),
        (1, 'Judge', 3, 0, 0, 0),
        (25, 'JudgeNTO', 4, 0, 0, 0),
        (21, 'RaceOfficer', 5, 0, 0, 0),
        (22, 'Spotter', 6, 0, 0, 0)",false,array());

    safe_w_sql("ALTER TABLE `IskData` 
        CHANGE `IskDtArrowstring` `IskDtArrowstring` VARCHAR(32) NOT NULL,
        ADD `IskDtTeamArchers` VARCHAR(32) NOT NULL AFTER `IskDtArrowstring`",false,array(1146, 1054, 1060));
    db_save_version('2026-04-03 08:00:01');
}

if($version<'2026-05-13 09:27:00') {
    safe_w_sql("update LookUpPaths set LupFlagsPath='https://extranet.worldarchery.sport/Api/GetFlags.php', LupLastUpdate=now() where LupFlagsPath like '%extranet.worldarchery.org/Api/GetFlags.php'");
	db_save_version('2026-05-13 09:27:00');
}

if($version<'2026-06-17 19:20:01') {
    safe_w_sql("DELETE FROM `IrmTypes` WHERE `IrmId` = 7");
    safe_w_sql("UPDATE `Individuals` set `IndIrmType`=5 where `IndIrmType`=7");
    safe_w_sql("UPDATE `Individuals` set `IndIrmTypeFinal`=5 where `IndIrmTypeFinal`=7");
    db_save_version('2026-06-17 19:20:01');
}

if($version<'2026-08-05 17:30:00') {
    safe_w_sql("ALTER TABLE `RoundRobinGroup` ADD `RrGrFinalRank` INT NOT NULL AFTER `RrGrSoSolved`",false,array(1146, 1054, 1060));
    safe_w_sql("ALTER TABLE `Teams` ADD `TeTbShootingArchers` MEDIUMTEXT NOT NULL AFTER `TeTbDecoded`",false,array(1146, 1054, 1060));
    safe_w_sql("ALTER TABLE `RoundRobinMatches` ADD `RrMatchShootingArchers` MEDIUMTEXT NOT NULL AFTER `RrMatchTieBreaker3`",false,array(1146, 1054, 1060));
    db_save_version('2026-08-05 17:30:00');
}

if($version<'2026-08-09 04:30:00') {
    safe_w_sql("ALTER TABLE `RoundRobinMatches` ADD `RrMatchLJudge` INT NOT NULL AFTER `RrMatchCoach`, ADD `RrMatchTJudge` INT NOT NULL AFTER `RrMatchLJudge`",false,array(1146, 1054, 1060));
    db_save_version('2026-08-09 04:30:00');
}

if($version<'2026-09-14 00:00:04') {
    safe_w_sql("UPDATE `Phases` SET `PhIndTeam` = '3' WHERE `PhId` = 12");
    safe_w_sql("REPLACE INTO `Targets` (`TarId`, `TarDescr`, `TarArray`, `TarStars`, `TarOrder`, `TarFullSize`, `L_size`, `L_color`, `9_size`, `9_color`, `V_size`, `V_color`) 
        VALUES (33, 'TrgFrNature', 'TrgFrNature', 'al', '33', '50', '50', '000000', '30', '000000', '10', 'FFFFFF')");
    safe_w_sql("ALTER TABLE TargetFaces add TfF1 tinyint unsigned not null after TfW1, add TfF2 tinyint unsigned not null after TfW2, add TfF3 tinyint unsigned not null after TfW3, add TfF4 tinyint unsigned not null after TfW4,
        add TfF5 tinyint unsigned not null after TfW5, add TfF6 tinyint unsigned not null after TfW6, add TfF7 tinyint unsigned not null after TfW7, add TfF8 tinyint unsigned not null after TfW8",false,array(1146, 1054, 1060));
    foreach(range(1,8) as $i) {
        safe_w_sql("UPDATE TargetFaces INNER JOIN Tournament ON TfTournament=ToId set TfF{$i}=1 where TfT{$i} in (2,4) and (ToType NOT IN (14,32,36,38,46,47,49) and ToTypeSubRule != 'SetLancClassic') ",false,array());
    }
    db_save_version('2026-09-14 00:00:04');
}

/*

// TEMPLATE
IMPORTANT: InfoSystem related things MUST be changed in the lib.php file!!!
REMEMBER TO CHANGE ALSO Common/Lib/UpdateTournament.inc.php!!!

if($version<'2026-05-13 09:25:00') {
    safe_w_sql("alter table RoundRobinMatches add index (RrMatchTournament, RrMatchTeam, RrMatchEvent)");
	db_save_version('2026-05-13 09:25:00');
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
