<?php

/*

3D DEFINITIONS (Target Tournaments)

*/
function CreateStandard3DClasses($TourId, $SubRule) {

            CreateClass($TourId, 1, 21, 49, 0, 'M', 'M', 'Men');
            CreateClass($TourId, 2, 21, 49, 1, 'W', 'W', 'Women');
            CreateClass($TourId, 3, 18, 20, 0, 'U21M', 'U21M,M', 'Under 21 Men');
            CreateClass($TourId, 4, 18, 20, 1, 'U21W', 'U21W,W', 'Under 21 Women');
            CreateClass($TourId, 5, 16, 17, 0, 'U18M', 'U18M,U21M,M', 'Under 18 Men');
            CreateClass($TourId, 6, 16, 17, 1, 'U18W', 'U18W,U21W,W', 'Under 18 Women');
            CreateClass($TourId, 7, 15, 15, 0, 'U16M', 'U16M,U18M,U21M,M', 'Under 16 Men');
            CreateClass($TourId, 8, 15, 15, 1, 'U16W', 'U16W,U18W,U21W,W', 'Under 16 Women');
            CreateClass($TourId, 9, 14, 14, 0, 'U15M', 'U15M,U16M,U18M,U21M,M', 'Under 15 Men');
            CreateClass($TourId, 10,14, 14, 1, 'U15W', 'U15W,U16W,U18W,U21W,W', 'Under 15 Women');
            CreateClass($TourId, 11,12, 13, 0, 'U14M', 'U14M,U15,U16M,MU18M,U21M,M', 'Under 14 Men');
            CreateClass($TourId, 12,12, 13, 1, 'U14W', 'U14W,U15W,U16W,U18W,U21W,W', 'Under 14 Women');
            CreateClass($TourId, 13, 1, 11, 0, 'U12M', 'U12M,U14M,U15M,U16M,U18M,U21M,M', 'Under 12 Men');
            CreateClass($TourId, 14, 1, 11, 1, 'U12W', 'U12W,U14W,U15W,U16W,U18W,U21W,W', 'Under 12 Women');
            CreateClass($TourId, 15, 50,100, 0, '50M', '50M,M', '50+ Men');
            CreateClass($TourId, 16, 50,100, 1, '50W', '50W,W', '50+ Women');

}

function CreateStandard3DEvents($TourId, $SubRule) {
	switch($SubRule) {
        case '1':
        case '2':
            $SettingsInd = array(
                'EvFinalFirstPhase' => 8,
                'EvFinalTargetType' => TGT_3D,
                'EvElimEnds' => 6,
                'EvElimArrows' => 2,
                'EvElimSO' => 1,
                'EvFinEnds' => 4,
                'EvFinArrows' => 2,
                'EvFinSO' => 1,
                'EvFinalAthTarget' => MATCH_NO_SEP,
                'EvMatchArrowsNo' => FINAL_FROM_2
            );
            $SettingsTeam = array(
                'EvTeamEvent' => 1,
                'EvFinalFirstPhase' => 4,
                'EvFinalTargetType' => TGT_3D,
                'EvElimEnds' => 4,
                'EvElimArrows' => 4,
                'EvElimSO' => 4,
                'EvFinEnds' => 4,
                'EvFinArrows' => 4,
                'EvFinSO' => 4,
                'EvFinalAthTarget' => MATCH_NO_SEP,
                'EvMatchArrowsNo' => FINAL_FROM_2,
            );
            $SettingsMixedTeam = array(
                'EvTeamEvent' => 1,
                'EvMixedTeam' => 1,
                'EvFinalFirstPhase' => 4,
                'EvFinalTargetType' => TGT_3D,
                'EvElimEnds' => 4,
                'EvElimArrows' => 4,
                'EvElimSO' => 2,
                'EvFinEnds' => 4,
                'EvFinArrows' => 4,
                'EvFinSO' => 2,
                'EvFinalAthTarget' => MATCH_NO_SEP,
                'EvMatchArrowsNo' => FINAL_FROM_2,
            );

            $i = 1;
            CreateEventNew($TourId, 'CM', 'Compound Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'CW', 'Compound Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU21M', 'Compound Under 21 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU21W', 'Compound Under 21 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU18M', 'Compound Under 18 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU18W', 'Compound Under 18 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU16M', 'Compound Under 16 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU16W', 'Compound Under 16 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU15M', 'Compound Under 15 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU15W', 'Compound Under 15 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU14M', 'Compound Under 14 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU14W', 'Compound Under 14 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU12M', 'Compound Under 12 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU12W', 'Compound Under 12 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'C50M', 'Compound 50+ Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'C50W', 'Compound 50+ Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'BM', 'Barebow Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'BW', 'Barebow Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU21M', 'Barebow Under 21 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU21W', 'Barebow Under 21 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU18M', 'Barebow Under 18 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU18W', 'Barebow Under 18 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU16M', 'Barebow Under 16 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU16W', 'Barebow Under 16 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU15M', 'Barebow Under 15 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU15W', 'Barebow Under 15 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU14M', 'Barebow Under 14 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU14W', 'Barebow Under 14 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU12M', 'Barebow Under 12 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU12W', 'Barebow Under 12 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'B50M', 'Barebow 50+ Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'B50W', 'Barebow 50+ Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'LM', 'Longbow Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'LW', 'Longbow Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU21M', 'Longbow Under 21 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU21W', 'Longbow Under 21 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU18M', 'Longbow Under 18 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU18W', 'Longbow Under 18 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU16M', 'Longbow Under 16 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU16W', 'Longbow Under 16 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU15M', 'Longbow Under 15 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU15W', 'Longbow Under 15 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU14M', 'Longbow Under 14 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU14W', 'Longbow Under 14 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU12M', 'Longbow Under 12 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU12W', 'Longbow Under 12 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'L50M', 'Longbow 50+ Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'L50W', 'Longbow 50+ Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'TM', 'Traditional Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'TW', 'Traditional Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU21M', 'Traditional Under 21 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU21W', 'Traditional Under 21 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU18M', 'Traditional Under 18 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU18W', 'Traditional Under 18 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU16M', 'Traditional Under 16 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU16W', 'Traditional Under 16 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU15M', 'Traditional Under 15 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU15W', 'Traditional Under 15 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU14M', 'Traditional Under 14 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU14W', 'Traditional Under 14 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU12M', 'Traditional Under 12 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU12W', 'Traditional Under 12 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'T50M', 'Traditional 50+ Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'T50W', 'Traditional 50+ Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'RM', 'Recurve Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'RW', 'Recurve Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU21M', 'Recurve Under 21 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU21W', 'Recurve Under 21 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU18M', 'Recurve Under 18 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU18W', 'Recurve Under 18 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU16M', 'Recurve Under 16 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU16W', 'Recurve Under 16 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU15M', 'Recurve Under 15 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU15W', 'Recurve Under 15 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU14M', 'Recurve Under 14 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU14W', 'Recurve Under 14 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU12M', 'Recurve Under 12 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU12W', 'Recurve Under 12 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'R50M', 'Recurve 50+ Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'R50W', 'Recurve 50+ Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'FM', 'Flatbow Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'FW', 'Flatbow Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU21M', 'Flatbow Under 21 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU21W', 'Flatbow Under 21 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU18M', 'Flatbow Under 18 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU18W', 'Flatbow Under 18 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU16M', 'Flatbow Under 16 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU16W', 'Flatbow Under 16 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU15M', 'Flatbow Under 15 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU15W', 'Flatbow Under 15 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU14M', 'Flatbow Under 14 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU14W', 'Flatbow Under 14 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU12M', 'Flatbow Under 12 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU12W', 'Flatbow Under 12 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'F50M', 'Flatbow 50+ Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'F50W', 'Flatbow 50+ Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'AM', 'Asiatic Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'AW', 'Asiatic Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU21M', 'Asiatic Under 21 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU21W', 'Asiatic Under 21 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU18M', 'Asiatic Under 18 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU18W', 'Asiatic Under 18 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU16M', 'Asiatic Under 16 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU16W', 'Asiatic Under 16 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU15M', 'Asiatic Under 15 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU15W', 'Asiatic Under 15 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU14M', 'Asiatic Under 14 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU14W', 'Asiatic Under 14 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU12M', 'Asiatic Under 12 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU12W', 'Asiatic Under 12 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'A50M', 'Asiatic 50+ Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'A50W', 'Asiatic 50+ Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLM', 'Compound Limited Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLW', 'Compound Limited Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU21M', 'Compound Limited Under 21 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU21W', 'Compound Limited Under 21 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU18M', 'Compound Limited Under 18 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU18W', 'Compound Limited Under 18 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU16M', 'Compound Limited Under 16 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU16W', 'Compound Limited Under 16 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU15M', 'Compound Limited Under 15 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU15W', 'Compound Limited Under 15 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU14M', 'Compound Limited Under 14 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU14W', 'Compound Limited Under 14 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU12M', 'Compound Limited Under 12 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU12W', 'Compound Limited Under 12 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CL50M', 'Compound Limited 50+ Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CL50W', 'Compound Limited 50+ Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBM', 'Compound Barebow Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBW', 'Compound Barebow  Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU21M', 'Compound Barebow  Under 21 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU21W', 'Compound Barebow  Under 21 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU18M', 'Compound Barebow  Under 18 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU18W', 'Compound Barebow  Under 18 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU16M', 'Compound Barebow  Under 16 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU16W', 'Compound Barebow  Under 16 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU15M', 'Compound Barebow  Under 15 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU15W', 'Compound Barebow  Under 15 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU14M', 'Compound Barebow  Under 14 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU14W', 'Compound Barebow  Under 14 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU12M', 'Compound Barebow  Under 12 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU12W', 'Compound Barebow  Under 12 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CB50M', 'Compound Barebow  50+ Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CB50W', 'Compound Barebow  50+ Women', $i++, $SettingsInd);
            break;
	}
}

function InsertStandard3DEvents($TourId, $SubRule) {
    InsertClassEvent($TourId, 0, 1, 'CM', 'C', 'M');
    InsertClassEvent($TourId, 0, 1, 'CU21M', 'C', 'U21M');
    InsertClassEvent($TourId, 0, 1, 'CU18M', 'C', 'U18M');
    InsertClassEvent($TourId, 0, 1, 'C50M', 'C', '50M');
    InsertClassEvent($TourId, 0, 1, 'CW', 'C', 'W');
    InsertClassEvent($TourId, 0, 1, 'CU21W', 'C', 'U21W');
    InsertClassEvent($TourId, 0, 1, 'CU18W', 'C', 'U18W');
    InsertClassEvent($TourId, 0, 1, 'C50W', 'C', '50W');
    InsertClassEvent($TourId, 0, 1, 'BM', 'B', 'M');
    InsertClassEvent($TourId, 0, 1, 'BU21M', 'B', 'U21M');
    InsertClassEvent($TourId, 0, 1, 'BU18M', 'B', 'U18M');
    InsertClassEvent($TourId, 0, 1, 'B50M', 'B', '50M');
    InsertClassEvent($TourId, 0, 1, 'BW', 'B', 'W');
    InsertClassEvent($TourId, 0, 1, 'BU21W', 'B', 'U21W');
    InsertClassEvent($TourId, 0, 1, 'BU18W', 'B', 'U18W');
    InsertClassEvent($TourId, 0, 1, 'B50W', 'B', '50W');
    InsertClassEvent($TourId, 0, 1, 'LM', 'L', 'M');
    InsertClassEvent($TourId, 0, 1, 'LU21M', 'L', 'U21M');
    InsertClassEvent($TourId, 0, 1, 'LU18M', 'L', 'U18M');
    InsertClassEvent($TourId, 0, 1, 'L50M', 'L', '50M');
    InsertClassEvent($TourId, 0, 1, 'LW', 'L', 'W');
    InsertClassEvent($TourId, 0, 1, 'LU21W', 'L', 'U21W');
    InsertClassEvent($TourId, 0, 1, 'LU18W', 'L', 'U18W');
    InsertClassEvent($TourId, 0, 1, 'L50W', 'L', '50W');
    InsertClassEvent($TourId, 0, 1, 'TM', 'T', 'M');
    InsertClassEvent($TourId, 0, 1, 'TU21M', 'T', 'U21M');
    InsertClassEvent($TourId, 0, 1, 'TU18M', 'T', 'U18M');
    InsertClassEvent($TourId, 0, 1, 'T50M', 'T', '50M');
    InsertClassEvent($TourId, 0, 1, 'TW', 'T', 'W');
    InsertClassEvent($TourId, 0, 1, 'TU21W', 'T', 'U21W');
    InsertClassEvent($TourId, 0, 1, 'TU18W', 'T', 'U18W');
    InsertClassEvent($TourId, 0, 1, 'T50W', 'T', '50W');
}

function InsertStandard3DEliminations($TourId, $SubRule){
	$cls=array();
	switch($SubRule) {
		case '1':
			$cls=array('M', 'W', 'U21M', 'U21W', 'U18M', 'U18W', '50M', '50W');
			break;
		case '2':
			$cls=array('M', 'W');
			break;
	}
	foreach(array('C', 'B', 'L', 'T') as $div) {
		foreach($cls as $cl) {
			for($n=1; $n<=16; $n++) {
				safe_w_SQL("INSERT INTO Eliminations set ElId=0, ElElimPhase=0, ElEventCode='$div$cl', ElTournament=$TourId, ElQualRank=$n");
			}
			for($n=1; $n<=8; $n++) {
				safe_w_SQL("INSERT INTO Eliminations set ElId=0, ElElimPhase=1, ElEventCode='$div$cl', ElTournament=$TourId, ElQualRank=$n");
			}
		}
	}
}
