<?php

/*

STANDARD DEFINITIONS (Target Tournaments)

*/

/*

FIELD DEFINITIONS (Target Tournaments)

*/

function CreateStandardFieldClasses($TourId, $SubRule) {
	switch($SubRule) {
		case '1':
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

			break;
	}
}

function CreateStandardFieldEvents($TourId, $SubRule) {
	switch($SubRule) {
		case '1':
			$SettingsInd=array(
				'EvFinalFirstPhase' => 8,
				'EvFinalTargetType'=>TGT_FIELD,
				'EvElimEnds'=>6,
				'EvElimArrows'=>3,
				'EvElimSO'=>1,
				'EvFinEnds'=>4,
				'EvFinArrows'=>3,
				'EvFinSO'=>1,
				'EvFinalAthTarget'=>255,
				'EvMatchArrowsNo'=>FINAL_FROM_2,
			);
			$SettingsTeam=array(
				'EvTeamEvent' => 1,
				'EvFinalFirstPhase' => 4,
				'EvFinalTargetType'=>TGT_FIELD,
				'EvElimEnds'=>4,
				'EvElimArrows'=>3,
				'EvElimSO'=>3,
				'EvFinEnds'=>4,
				'EvFinArrows'=>3,
				'EvFinSO'=>3,
				'EvFinalAthTarget'=>255,
				'EvMatchArrowsNo'=>FINAL_FROM_2,
			);
            $SettingsMixedTeam=array(
                'EvTeamEvent' => '1',
                'EvMixedTeam' => '1',
                'EvFinalFirstPhase' => '4',
                'EvFinalTargetType'=>TGT_FIELD,
                'EvElimEnds'=>4,
                'EvElimArrows'=>4,
                'EvElimSO'=>2,
                'EvFinEnds'=>4,
                'EvFinArrows'=>4,
                'EvFinSO'=>2,
                'EvFinalAthTarget'=>15,
                'EvMatchArrowsNo'=>FINAL_FROM_2,
            );
			$i=1;
			CreateEventNew($TourId,'RO', 'Recurve Open', $i++, $SettingsInd);
			CreateEventNew($TourId,'RW', 'Recurve Women', $i++, $SettingsInd);
			CreateEventNew($TourId,'RU21o', 'Recurve Under 21 Open',$i++, $SettingsInd);
			CreateEventNew($TourId,'RU21W', 'Recurve Under 21 Women', $i++, $SettingsInd);
			if($SubRule==1) {
				CreateEventNew($TourId,'RU18O', 'Recurve Under 18 Open', $i++, $SettingsInd);
				CreateEventNew($TourId,'RU18W', 'Recurve Under 18 Women', $i++, $SettingsInd);
				CreateEventNew($TourId,'R50O', 'Recurve 50+ Open', $i++, $SettingsInd);
				CreateEventNew($TourId,'R50W', 'Recurve 50+ Women', $i++, $SettingsInd);
			}
			CreateEventNew($TourId,'CO', 'Compound Open', $i++, $SettingsInd);
			CreateEventNew($TourId,'CW', 'Compound Women', $i++, $SettingsInd);
			CreateEventNew($TourId,'CU21O', 'Compound Under 21 Open', $i++, $SettingsInd);
			CreateEventNew($TourId,'CU21W', 'Compound Under 21 Women',$i++, $SettingsInd);
			if($SubRule==1) {
				CreateEventNew($TourId,'CU18O', 'Compound Under 18 Open', $i++, $SettingsInd);
				CreateEventNew($TourId,'CU18W', 'Compound Under 18 Women', $i++, $SettingsInd);
				CreateEventNew($TourId,'C50O', 'Compound 50+ Open', $i++, $SettingsInd);
				CreateEventNew($TourId,'C50W', 'Compound 50+ Women',$i++, $SettingsInd);
			}
			CreateEventNew($TourId,'BO', 'Barebow Open', $i++, $SettingsInd);
			CreateEventNew($TourId,'BW', 'Barebow Women', $i++, $SettingsInd);
			CreateEventNew($TourId,'BU21O', 'Barebow Under 21 Open', $i++, $SettingsInd);
			CreateEventNew($TourId,'BU21W', 'Barebow Under 21 Women', $i++, $SettingsInd);
			if($SubRule==1) {
				CreateEventNew($TourId,'BU18O', 'Barebow Under 18 Open', $i++, $SettingsInd);
				CreateEventNew($TourId,'BU18W', 'Barebow Under 18 Women', $i++, $SettingsInd);
				CreateEventNew($TourId,'B50O', 'Barebow 50+ Open', $i++, $SettingsInd);
				CreateEventNew($TourId,'B50W', 'Barebow 50+ Women', $i++, $SettingsInd);
			}
			$i=1;
			CreateEventNew($TourId, 'OT', 'Open Team', $i++, $SettingsTeam);
			CreateEventNew($TourId, 'WT', 'Women Team', $i++, $SettingsTeam);
            CreateEventNew($TourId, 'RX', 'Recurve Mixed Team', $i++, $SettingsMixedTeam);
            CreateEventNew($TourId, 'CX', 'Compound Mixed Team', $i++, $SettingsMixedTeam);
            CreateEventNew($TourId, 'BX', 'Barebow Mixed Team', $i++, $SettingsMixedTeam);
			CreateEventNew($TourId, 'OU21T','Open Under 21 Team', $i++, $SettingsTeam);
			CreateEventNew($TourId, 'WU21T','Women Under 21 Team', $i++, $SettingsTeam);
            CreateEventNew($TourId, 'RU21X', 'Recurve Under 21 Mixed Team', $i++, $SettingsMixedTeam);
            CreateEventNew($TourId, 'CU21X', 'Compound Under 21 Mixed Team', $i++, $SettingsMixedTeam);
            CreateEventNew($TourId, 'BU21X', 'Barebow Under 21 Mixed Team', $i++, $SettingsMixedTeam);

        if($SubRule==1) {
                CreateEventNew($TourId, 'OU18T','Open Under 18 Team', $i++, $SettingsTeam);
                CreateEventNew($TourId, 'WU18T','Women Under 18 Team', $i++, $SettingsTeam);
                CreateEventNew($TourId, 'RU18X', 'Recurve Under 18 Mixed Team', $i++, $SettingsMixedTeam);
                CreateEventNew($TourId, 'CU18X', 'Compound Under 18 Mixed Team', $i++, $SettingsMixedTeam);
                CreateEventNew($TourId, 'BU18X', 'Barebow Under 18 Mixed Team', $i++, $SettingsMixedTeam);
				CreateEventNew($TourId, 'O50T','Open 50+ Team', $i++, $SettingsTeam);
				CreateEventNew($TourId, 'W50T','Women 50+ Team', $i++, $SettingsTeam);
                CreateEventNew($TourId, 'R50X', 'Recurve 50+ Mixed Team', $i++, $SettingsMixedTeam);
                CreateEventNew($TourId, 'C50X', 'Compound 50+ Mixed Team', $i++, $SettingsMixedTeam);
                CreateEventNew($TourId, 'B50X', 'Barebow 50+ Mixed Team', $i++, $SettingsMixedTeam);
			}
			break;
	}
}

function InsertStandardFieldEvents($TourId, $SubRule) {
    InsertClassEvent($TourId, 0, 1, 'RO', 'R', 'O');
    InsertClassEvent($TourId, 0, 1, 'RU21O', 'R', 'U21O');
    InsertClassEvent($TourId, 0, 1, 'RU18O', 'R', 'U18O');
    InsertClassEvent($TourId, 0, 1, 'R50O', 'R', '50O');
    InsertClassEvent($TourId, 0, 1, 'RW', 'R', 'W');
    InsertClassEvent($TourId, 0, 1, 'RU21W', 'R', 'U21W');
    InsertClassEvent($TourId, 0, 1, 'RU18W', 'R', 'U18W');
    InsertClassEvent($TourId, 0, 1, 'R50W', 'R', '50W');
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

    InsertClassEvent($TourId, 1, 1, 'OT', 'R', 'O');
    InsertClassEvent($TourId, 2, 1, 'OT', 'C', 'O');
    InsertClassEvent($TourId, 3, 1, 'OT', 'B', 'O');
    InsertClassEvent($TourId, 1, 1, 'WT', 'R', 'W');
    InsertClassEvent($TourId, 2, 1, 'WT', 'C', 'W');
    InsertClassEvent($TourId, 3, 1, 'WT', 'B', 'W');
    InsertClassEvent($TourId, 1, 1, 'RX', 'R', 'W');
    InsertClassEvent($TourId, 2, 1, 'RX', 'R', 'M');
    InsertClassEvent($TourId, 1, 1, 'CX', 'C', 'W');
    InsertClassEvent($TourId, 2, 1, 'CX', 'C', 'M');
    InsertClassEvent($TourId, 1, 1, 'BX', 'B', 'W');
    InsertClassEvent($TourId, 2, 1, 'BX', 'B', 'M');
    InsertClassEvent($TourId, 1, 1, 'OU21T', 'R', 'U21O');
    InsertClassEvent($TourId, 2, 1, 'OU21T', 'C', 'U21O');
    InsertClassEvent($TourId, 3, 1, 'OU21T', 'B', 'U21O');
    InsertClassEvent($TourId, 1, 1, 'WU21T', 'R', 'U21W');
    InsertClassEvent($TourId, 2, 1, 'WU21T', 'C', 'U21W');
    InsertClassEvent($TourId, 3, 1, 'WU21T', 'B', 'U21W');
    InsertClassEvent($TourId, 1, 1, 'RU21X', 'R', 'U21W');
    InsertClassEvent($TourId, 2, 1, 'RU21X', 'R', 'U21O');
    InsertClassEvent($TourId, 1, 1, 'CU21X', 'C', 'U21W');
    InsertClassEvent($TourId, 2, 1, 'CU21X', 'C', 'U21O');
    InsertClassEvent($TourId, 1, 1, 'BU21X', 'B', 'U21W');
    InsertClassEvent($TourId, 2, 1, 'BU21X', 'B', 'U21O');
    InsertClassEvent($TourId, 1, 1, 'OU18T', 'R', 'U18O');
    InsertClassEvent($TourId, 2, 1, 'OU18T', 'C', 'U18O');
    InsertClassEvent($TourId, 3, 1, 'OU18T', 'B', 'U18O');
    InsertClassEvent($TourId, 1, 1, 'WU18T', 'R', 'U18W');
    InsertClassEvent($TourId, 2, 1, 'WU18T', 'C', 'U18W');
    InsertClassEvent($TourId, 3, 1, 'WU18T', 'B', 'U18W');
    InsertClassEvent($TourId, 1, 1, 'RU18X', 'R', 'U18W');
    InsertClassEvent($TourId, 2, 1, 'RU18X', 'R', 'U18O');
    InsertClassEvent($TourId, 1, 1, 'CU18X', 'C', 'U18W');
    InsertClassEvent($TourId, 2, 1, 'CU18X', 'C', 'U18O');
    InsertClassEvent($TourId, 1, 1, 'BU18X', 'B', 'U18W');
    InsertClassEvent($TourId, 2, 1, 'BU18X', 'B', 'U18O');
    InsertClassEvent($TourId, 1, 1, 'O50T', 'R', '50O');
    InsertClassEvent($TourId, 2, 1, 'O50T', 'C', '50O');
    InsertClassEvent($TourId, 3, 1, 'O50T', 'B', '50O');
    InsertClassEvent($TourId, 1, 1, 'R50X', 'R', '50W');
    InsertClassEvent($TourId, 2, 1, 'R50X', 'R', '50O');
    InsertClassEvent($TourId, 1, 1, 'C50X', 'C', '50W');
    InsertClassEvent($TourId, 2, 1, 'C50X', 'C', '50O');
    InsertClassEvent($TourId, 1, 1, 'B50X', 'B', '50W');
    InsertClassEvent($TourId, 2, 1, 'B50X', 'B', '50O');
    InsertClassEvent($TourId, 1, 1, 'W50T', 'R', '50W');
    InsertClassEvent($TourId, 2, 1, 'W50T', 'C', '50W');
    InsertClassEvent($TourId, 3, 1, 'W50T', 'B', '50W');
}

function InsertStandardFieldEliminations($TourId, $SubRule){
	$cls=array();
	switch($SubRule) {
		case '1':
			$cls=array('O', 'W', 'U21O', 'U21W', 'U18O', 'U18W', '50O', '50W');
			break;
		case '2':
			$cls=array('O', 'W', 'U21O', 'U21W');
			break;
	}
	foreach(array('R', 'C', 'B') as $div) {
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

