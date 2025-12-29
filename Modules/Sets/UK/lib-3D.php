<?php

/*

3D DEFINITIONS (Target Tournaments)

*/
function CreateStandard3DClasses($TourId, $SubRule) {

           	CreateClass($TourId, 1, 21, 49, -1, 'O', 'O', 'Open');
			CreateClass($TourId, 2, 21, 49, 1, 'W', 'W', 'Women');
			CreateClass($TourId, 3, 18, 20, -1, 'U21O', 'U21O,O', 'Under 21 Open');
			CreateClass($TourId, 4, 18, 20, 1, 'U21W', 'U21W,W', 'Under 21 Women');
            CreateClass($TourId, 5, 16, 17, -1, 'U18O', 'U18O,U21O,O', 'Under 18 Open');
            CreateClass($TourId, 6, 16, 17, 1, 'U18W', 'U18W,U21W,W', 'Under 18 Women');
            CreateClass($TourId, 7, 15, 15, -1, 'U16O', 'U16O,U18O,U21O,O', 'Under 16 Open');
            CreateClass($TourId, 8, 15, 15, 1, 'U16W', 'U16W,U18W,U21W,W', 'Under 16 Women');
            CreateClass($TourId, 9, 14, 14, -1, 'U15O', 'U15O,U16O,U18O,U21O,O', 'Under 15 Open');
            CreateClass($TourId, 10,14, 14, 1, 'U15W', 'U15W,U16W,U18W,U21W,W', 'Under 15 Women');
            CreateClass($TourId, 11,12, 13, -1, 'U14O', 'U14O,U15O,U16O,MU18O,U21O,O', 'Under 14 Open');
            CreateClass($TourId, 12,12, 13, 1, 'U14W', 'U14W,U15W,U16W,U18W,U21W,W', 'Under 14 Women');
            CreateClass($TourId, 13, 1, 11, -1, 'U12O', 'U12O,U14O,U15O,U16O,U18O,U21O,O', 'Under 12 Open');
            CreateClass($TourId, 14, 1, 11, 1, 'U12W', 'U12W,U14W,U15W,U16W,U18W,U21W,W', 'Under 12 Women');
            CreateClass($TourId, 15, 50,100, -1, '50O', '50O,O', '50+ Open');
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
            CreateEventNew($TourId, 'CO', 'Compound Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'CW', 'Compound Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU21O', 'Compound Under 21 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU21W', 'Compound Under 21 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU18O', 'Compound Under 18 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU18W', 'Compound Under 18 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU16O', 'Compound Under 16 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU16W', 'Compound Under 16 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU15O', 'Compound Under 15 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU15W', 'Compound Under 15 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU14O', 'Compound Under 14 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU14W', 'Compound Under 14 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU12O', 'Compound Under 12 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'CU12W', 'Compound Under 12 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'C50O', 'Compound 50+ Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'C50W', 'Compound 50+ Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'BO', 'Barebow Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'BW', 'Barebow Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU21O', 'Barebow Under 21 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU21W', 'Barebow Under 21 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU18O', 'Barebow Under 18 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU18W', 'Barebow Under 18 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU16O', 'Barebow Under 16 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU16W', 'Barebow Under 16 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU15O', 'Barebow Under 15 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU15W', 'Barebow Under 15 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU14O', 'Barebow Under 14 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU14W', 'Barebow Under 14 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU12O', 'Barebow Under 12 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'BU12W', 'Barebow Under 12 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'B50O', 'Barebow 50+ Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'B50W', 'Barebow 50+ Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'LO', 'Longbow Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'LW', 'Longbow Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU21O', 'Longbow Under 21 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU21W', 'Longbow Under 21 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU18O', 'Longbow Under 18 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU18W', 'Longbow Under 18 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU1O', 'Longbow Under 16 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU16W', 'Longbow Under 16 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU15O', 'Longbow Under 15 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU15W', 'Longbow Under 15 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU14O', 'Longbow Under 14 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU14W', 'Longbow Under 14 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU12O', 'Longbow Under 12 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'LU12W', 'Longbow Under 12 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'L50O', 'Longbow 50+ Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'L50W', 'Longbow 50+ Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'TO', 'Traditional Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'TW', 'Traditional Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU21O', 'Traditional Under 21 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU21W', 'Traditional Under 21 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU18O', 'Traditional Under 18 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU18W', 'Traditional Under 18 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU16O', 'Traditional Under 16 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU16W', 'Traditional Under 16 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU15O', 'Traditional Under 15 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU15W', 'Traditional Under 15 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU14O', 'Traditional Under 14 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU14W', 'Traditional Under 14 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU12O', 'Traditional Under 12 Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'TU12W', 'Traditional Under 12 Women', $i++, $SettingsInd);
                CreateEventNew($TourId, 'T50O', 'Traditional 50+ Men', $i++, $SettingsInd);
                CreateEventNew($TourId, 'T50W', 'Traditional 50+ Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'RO', 'Recurve Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'RW', 'Recurve Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU21O', 'Recurve Under 21 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU21W', 'Recurve Under 21 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU18O', 'Recurve Under 18 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU18W', 'Recurve Under 18 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU16O', 'Recurve Under 16 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU16W', 'Recurve Under 16 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU15O', 'Recurve Under 15 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU15W', 'Recurve Under 15 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU14O', 'Recurve Under 14 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU14W', 'Recurve Under 14 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU12O', 'Recurve Under 12 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'RU12W', 'Recurve Under 12 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'R50O', 'Recurve 50+ Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'R50W', 'Recurve 50+ Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'FO', 'Flatbow Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'FW', 'Flatbow Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU21O', 'Flatbow Under 21 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU21W', 'Flatbow Under 21 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU18O', 'Flatbow Under 18 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU18W', 'Flatbow Under 18 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU16O', 'Flatbow Under 16 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU16W', 'Flatbow Under 16 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU15O', 'Flatbow Under 15 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU15W', 'Flatbow Under 15 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU14O', 'Flatbow Under 14 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU14W', 'Flatbow Under 14 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU12O', 'Flatbow Under 12 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'FU12W', 'Flatbow Under 12 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'F50O', 'Flatbow 50+ Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'F50W', 'Flatbow 50+ Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'AO', 'Asiatic Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'AW', 'Asiatic Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU21O', 'Asiatic Under 21 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU21W', 'Asiatic Under 21 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU18O', 'Asiatic Under 18 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU18W', 'Asiatic Under 18 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU16O', 'Asiatic Under 16 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU16W', 'Asiatic Under 16 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU15O', 'Asiatic Under 15 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU15W', 'Asiatic Under 15 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU14O', 'Asiatic Under 14 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU14W', 'Asiatic Under 14 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU12O', 'Asiatic Under 12 Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'AU12W', 'Asiatic Under 12 Women', $i++, $SettingsInd);
            CreateEventNew($TourId, 'A50O', 'Asiatic 50+ Men', $i++, $SettingsInd);
            CreateEventNew($TourId, 'A50W', 'Asiatic 50+ Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLO', 'Compound Limited Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLW', 'Compound Limited Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU21O', 'Compound Limited Under 21 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU21W', 'Compound Limited Under 21 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU18O', 'Compound Limited Under 18 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU18W', 'Compound Limited Under 18 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU16O', 'Compound Limited Under 16 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU16W', 'Compound Limited Under 16 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU15O', 'Compound Limited Under 15 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU15W', 'Compound Limited Under 15 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU14O', 'Compound Limited Under 14 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU14W', 'Compound Limited Under 14 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU12O', 'Compound Limited Under 12 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CLU12W', 'Compound Limited Under 12 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CL50O', 'Compound Limited 50+ Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CL50W', 'Compound Limited 50+ Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBO', 'Compound Barebow Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBW', 'Compound Barebow  Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU21O', 'Compound Barebow  Under 21 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU21W', 'Compound Barebow  Under 21 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU18O', 'Compound Barebow  Under 18 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU18W', 'Compound Barebow  Under 18 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU16O', 'Compound Barebow  Under 16 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU16W', 'Compound Barebow  Under 16 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU15O', 'Compound Barebow  Under 15 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU15W', 'Compound Barebow  Under 15 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU14O', 'Compound Barebow  Under 14 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU14W', 'Compound Barebow  Under 14 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU12O', 'Compound Barebow  Under 12 Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CBU12W', 'Compound Barebow  Under 12 Women', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CB50O', 'Compound Barebow  50+ Men', $i++, $SettingsInd);
        CreateEventNew($TourId, 'CB50W', 'Compound Barebow  50+ Women', $i++, $SettingsInd);
            break;
	}
}

function InsertStandard3DEvents($TourId, $SubRule) {
    InsertClassEvent($TourId, 0, 1, 'CO', 'C', 'O');
    InsertClassEvent($TourId, 0, 1, 'CU21O', 'C', 'U21O');
    InsertClassEvent($TourId, 0, 1, 'CU18O', 'C', 'U18O');
    InsertClassEvent($TourId, 0, 1, 'C50O', 'C', '50O');
    InsertClassEvent($TourId, 0, 1, 'CW', 'C', 'W');
    InsertClassEvent($TourId, 0, 1, 'CU21W', 'C', 'U21W');
    InsertClassEvent($TourId, 0, 1, 'CU18W', 'C', 'U18W');
    InsertClassEvent($TourId, 0, 1, 'C50W', 'C', '50W');
    InsertClassEvent($TourId, 0, 1, 'BO', 'B', 'O');
    InsertClassEvent($TourId, 0, 1, 'BU21O', 'B', 'U21O');
    InsertClassEvent($TourId, 0, 1, 'BU18O', 'B', 'U18O');
    InsertClassEvent($TourId, 0, 1, 'B50O', 'B', '50O');
    InsertClassEvent($TourId, 0, 1, 'BW', 'B', 'W');
    InsertClassEvent($TourId, 0, 1, 'BU21W', 'B', 'U21W');
    InsertClassEvent($TourId, 0, 1, 'BU18W', 'B', 'U18W');
    InsertClassEvent($TourId, 0, 1, 'B50W', 'B', '50W');
    InsertClassEvent($TourId, 0, 1, 'LO', 'L', 'O');
    InsertClassEvent($TourId, 0, 1, 'LU21O', 'L', 'U21O');
    InsertClassEvent($TourId, 0, 1, 'LU18O', 'L', 'U18O');
    InsertClassEvent($TourId, 0, 1, 'L50O', 'L', '50O');
    InsertClassEvent($TourId, 0, 1, 'LW', 'L', 'W');
    InsertClassEvent($TourId, 0, 1, 'LU21W', 'L', 'U21W');
    InsertClassEvent($TourId, 0, 1, 'LU18W', 'L', 'U18W');
    InsertClassEvent($TourId, 0, 1, 'L50W', 'L', '50W');
    InsertClassEvent($TourId, 0, 1, 'TO', 'T', 'M');
    InsertClassEvent($TourId, 0, 1, 'TU21O', 'T', 'U21O');
    InsertClassEvent($TourId, 0, 1, 'TU18O', 'T', 'U18O');
    InsertClassEvent($TourId, 0, 1, 'T50O', 'T', '50O');
    InsertClassEvent($TourId, 0, 1, 'TW', 'T', 'W');
    InsertClassEvent($TourId, 0, 1, 'TU21W', 'T', 'U21W');
    InsertClassEvent($TourId, 0, 1, 'TU18W', 'T', 'U18W');
    InsertClassEvent($TourId, 0, 1, 'T50W', 'T', '50W');
}

function InsertStandard3DEliminations($TourId, $SubRule){
	$cls=array();
	switch($SubRule) {
		case '1':
			$cls=array('O', 'W', 'U21O', 'U21O', 'U18O', 'U18W', '50O', '50W');
			break;
		case '2':
			$cls=array('O', 'W');
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
