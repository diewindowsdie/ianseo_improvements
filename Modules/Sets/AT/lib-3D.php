<?php

/*

3D DEFINITIONS (Target Tournaments)

*/

/**
 * Modified by helmut.poell@gmx.at
 * 2026-09-07
 */

function CreateStandard3DEvents($TourId, $SubRule) {
    if ($SubRule == 2) {
		$SettingsInd=array(
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
		$SettingsTeam=array(
            'EvTeamEvent' => '1',
            'EvFinalFirstPhase' => '4',
            'EvFinalTargetType'=>8,
            'EvElimEnds'=>4,
            'EvElimArrows'=>4,
            'EvElimSO'=>4,
            'EvFinEnds'=>4,
            'EvFinArrows'=>4,
            'EvFinSO'=>4,
            'EvFinalAthTarget'=>MATCH_NO_SEP,
            'EvMatchArrowsNo'=>FINAL_FROM_2,
            'EvMultiTeam'=>1,
            'EvMultiTeamNo'=>2,
            'EvPartialTeam'=>1
		);
        $SettingsMixedTeam = array(
            'EvTeamEvent' => '1',
            'EvMixedTeam' => '1',
            'EvFinalFirstPhase' => '4',
            'EvFinalTargetType' => TGT_3D,
            'EvElimEnds' => 4,
            'EvElimArrows' => 4,
            'EvElimSO' => 2,
            'EvFinEnds' => 4,
            'EvFinArrows' => 4,
            'EvFinSO' => 2,
            'EvFinalAthTarget' => MATCH_NO_SEP,
            'EvMatchArrowsNo' => FINAL_FROM_2,
            'EvMultiTeam'=>1,
            'EvMultiTeamNo'=>3,
            'EvPartialTeam'=>0
        );

        $i = 1;
        CreateEventNew($TourId,'LW', 'Langbogen Damen', $i++, $SettingsInd);
        CreateEventNew($TourId,'LM', 'Langbogen Herren', $i++, $SettingsInd);
        CreateEventNew($TourId,'TW', 'Traditional Damen', $i++, $SettingsInd);
        CreateEventNew($TourId,'TM', 'Traditional Herren', $i++, $SettingsInd);
        CreateEventNew($TourId,'BW', 'Blankbogen Damen', $i++, $SettingsInd);
        CreateEventNew($TourId,'BM', 'Blankbogen Herren', $i++, $SettingsInd);
        CreateEventNew($TourId,'CW', 'Compound Damen', $i++, $SettingsInd);
        CreateEventNew($TourId,'CM', 'Compound Herren', $i++, $SettingsInd);
        CreateEventNew($TourId,'RW', 'Recurve Damen', $i++, $SettingsInd);
        CreateEventNew($TourId,'RM', 'Recurve Herren', $i++, $SettingsInd);

        $i = 1;
        CreateEventNew($TourId, 'LX', 'Langbogen Mixed Team', $i++, $SettingsMixedTeam);
        CreateEventNew($TourId, 'TX', 'Traditional Mixed Team', $i++, $SettingsMixedTeam);
        CreateEventNew($TourId, 'BX', 'Blankbogen Mixed Team', $i++, $SettingsMixedTeam);
        CreateEventNew($TourId, 'CX', 'Compound Mixed Team', $i++, $SettingsMixedTeam);
        CreateEventNew($TourId, 'RX', 'Recurve Mixed Team', $i++, $SettingsMixedTeam);
        CreateEventNew($TourId, 'WT', 'Damen Team', $i++, $SettingsTeam);
        CreateEventNew($TourId, 'MT', 'Herren Team', $i++, $SettingsTeam);
	}
}

function InsertStandard3DEvents($TourId, $SubRule) {
    if ($SubRule == 2) {
        foreach (array('C'=>'C','B'=>'B','L'=>'L','T'=>'T','R'=>'R') as $kDiv=>$vDiv) {
            $clsTmpArr = array('W','U18W','U21W','50W','65W');
            foreach($clsTmpArr as $kClass=>$vClass) {
                InsertClassEvent($TourId, 0, 1, $vDiv.'W', $kDiv,  $vClass);        // Individuals
                if($kDiv!=='R') {                                                   // Teams all 4 members (C,L,B,T)
                    InsertClassEvent($TourId, ($kDiv == 'C' ? 1 : ($kDiv == 'L' ? 2 : ($kDiv == 'B' ? 3 : 4))), 1, 'WT', $kDiv, $vClass);
                }
                InsertClassEvent($TourId, 1, 1, $vDiv.'X', $kDiv, $vClass);         // Mixed Teams (Part one)
            }
            $clsTmpArr = array('M','U18M','U21M','50M','65M');
            foreach($clsTmpArr as $kClass=>$vClass) {
                InsertClassEvent($TourId, 0, 1, $vDiv.'M', $kDiv,  $vClass);        // Individuals
                if($kDiv!=='R') {                                                   // Teams all 4 members (C,L,B,T)
                    InsertClassEvent($TourId, ($kDiv == 'C' ? 1 : ($kDiv == 'L' ? 2 : ($kDiv == 'B' ? 3 : 4))), 1, 'MT', $kDiv, $vClass);
                } 
                if(substr($vClass,-1,1) != 'U') {
                    InsertClassEvent($TourId, 2, 1, $vDiv . 'X', $kDiv, $vClass);   // Mixed Teams (Part two)
                }
            }
        }
    }
}

function InsertStandard3DEliminations($TourId, $SubRule){
    if($SubRule==2) {
        foreach (array('R', 'C', 'B', 'L', 'T') as $kDiv) {
            foreach (array('M','W') as $kCl) {
                for($n=1; $n<=16; $n++) {
                    safe_w_SQL("INSERT INTO Eliminations set ElId=0, ElElimPhase=0, ElEventCode='{$kDiv}{$kCl}', ElTournament={$TourId}, ElQualRank={$n}");
                }
                for($n=1; $n<=8; $n++) {
                    safe_w_SQL("INSERT INTO Eliminations set ElId=0, ElElimPhase=1, ElEventCode='{$kDiv}{$kCl}', ElTournament={$TourId}, ElQualRank={$n}");
                }
            }
        }
    }
}
