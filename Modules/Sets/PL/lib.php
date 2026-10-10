<?php

// these go here as it is a "global" definition, used or not
$tourCollation = '';
$tourDetIocCode = 'PL';
if(empty($SubRule)) $SubRule='1';

function CreateStandardDivisions($TourId, $Type=1, $SubRule=0) { //Bogaflokkar/Undankeppni. Þessum parti er lokið COMPLETE
    // Ignoring sub-rules for now. Þetta function section bætir við Bogaflokkum í "Division and classes" partinn af Ianseo
    $i=1;
	CreateDivision($TourId, $i++, 'R', 'Łuki Klasyczne');
	CreateDivision($TourId, $i++, 'C', 'Łuki Bloczkowe');
    CreateDivision($TourId, $i++, 'B', 'Łuki Barebow');
}

function CreateStandardClasses($TourId, $SubRule, $Type) { //Aldursflokkar og kyn/Undankeppni. COMPLETE
    //Aldursflokkar. Þetta function section bætir við aldursflokkum og kynjum í "Division and classes" partinn af Ianseo
    $i=1;
	$Phase = 0;
	if ($SubRule==1) { // "Tylko jedna kategoria
		CreateClass($TourId, $i++, 1, 99, 0, 'M', 'M', 'Mężczyźni', 1, '');
		CreateClass($TourId, $i++, 1, 99, 1, 'W', 'W', 'Kobiety', 1, '');
		}

	if ($SubRule==2) { // "AllClasses
		CreateClass($TourId, $i++, 24, 99, 0, 'M', 'M', 'Seniorzy', 1, '');
		CreateClass($TourId, $i++, 24, 99, 1, 'W', 'W', 'Seniorki', 1, '');
		CreateClass($TourId, $i++, 21, 23, 0, 'U24M', 'M,U24M', 'Młodzieżowcy Mężczyźni', 1, '');
        CreateClass($TourId, $i++, 21, 23, 1, 'U24W', 'W,U24W', 'Młodzieżowcy Kobiety', 1, '');
        CreateClass($TourId, $i++, 18, 20, 0, 'U21M', 'M,U24M,U21M', 'Juniorzy', 1, '');
        CreateClass($TourId, $i++, 18, 20, 1, 'U21W', 'W,U24W,U21W', 'Juniorki', 1, '');
        CreateClass($TourId, $i++, 15, 17, 0, 'U18M', 'M,U24M,U21M,U18M', 'Juniorzy Młodsi', 1, '');
        CreateClass($TourId, $i++, 15, 17, 1, 'U18W', 'W,U24W,U21W,U18W', 'Juniorki Młodsze', 1, '');
        CreateClass($TourId, $i++, 12, 14, 0, 'U15M', 'M,U24M,U21M,U18M,U15M', 'Młodzicy', 1, '');
        CreateClass($TourId, $i++, 12, 14, 1, 'U15W', 'W,U24W,U21W,U18W,U15W', 'Młodziczki', 1, '');
		CreateClass($TourId, $i++, 1, 11, 0, 'U12M', 'M,U24M,U21M,U18M,U15M,U12M', 'Dzieci chłopcy', 1, '');
        CreateClass($TourId, $i++, 1, 11, 1, 'U12W', 'W,U24W,U21W,U18W,U15W,U12W', 'Dzieci dziewczynki', 1, '');
		}

    if ($SubRule==3) { // "Youth classes" U24,U21,U18
		CreateClass($TourId, $i++, 21, 23, 0, 'U24M', 'U24M', 'Młodzieżowcy Mężczyźni', 1, '');
        CreateClass($TourId, $i++, 21, 23, 1, 'U24W', 'U24W', 'Młodzieżowcy Kobiety', 1, '');
        CreateClass($TourId, $i++, 18, 20, 0, 'U21M', 'U24M,U21M', 'Juniorzy', 1, '');
        CreateClass($TourId, $i++, 18, 20, 1, 'U21W', 'U24W,U21W', 'Juniorki', 1, '');
        CreateClass($TourId, $i++, 1, 17, 0, 'U18M', 'U24M,U21M,U18M', 'Juniorzy Młodsi', 1, '');
        CreateClass($TourId, $i++, 1, 17, 1, 'U18W', 'U24W,U21W,U18W', 'Juniorki Młodsze', 1, '');
		}
	
	if ($SubRule==4) { // "U15,U12
        CreateClass($TourId, $i++, 12, 14, 0, 'U15M', 'U15M', 'Młodzicy', 1, '');
        CreateClass($TourId, $i++, 12, 14, 1, 'U15W', 'U15W', 'Młodziczki', 1, '');
		CreateClass($TourId, $i++, 1, 11, 0, 'U12M', 'U15M,U12M', 'Dzieci chłopcy', 1, '');
        CreateClass($TourId, $i++, 1, 11, 1, 'U12W', 'U15W,U12W', 'Dzieci dziewczynki', 1, '');
		}

}

function CreateStandardSubClasses($TourId) { //Undirflokkar. Þessi partur er ekki notaður COMPLETE
	// Hérna seturðu inn subclasses/undirflokka
}

function CreateStandardEvents($TourId, $TourType, $SubRule, $Outdoor=true) { //Útsláttarkeppni. COMPLETE
    //StandardEvents = Eliminations/Matches Útsláttarkeppni uppsetning, útskýring er fyrir ofan hvern hluta um hvað sá hluti gerir
	// $Phase stillir globally í hvaða útslætti útsláttarkeppni byrjar 0=engin útsláttur "---" 1=semi finals, 2=quarter finals og svo framvegis.
	// Ef þú vilt stilla suma útslætti til að byrja á ákveðnum stað þarftu að finna þann útslátt og bæta við t.d =0 fyrir aftan $Phase í útsláttarlínuni fyrir þann flokk
    $Phase=0; 
    $i=1;
	switch($TourType){
		case 3:
		case 37:
		case 1:
			if ($SubRule==1) { // "All together
				// Recurve
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RM',  'Łuki klasyczne - Mężczyźni', 1, 240, 240, 0, 0, '', '', 122, 70);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RW',  'Łuki klasyczne - Kobiety', 1, 240, 240, 0, 0, '', '', 122, 70);
				// Compound
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_5_big10,  5, 3, 1, 5, 3, 1, 'CM',  'Łuki bloczkowe - Mężczyźni', 0, 240, 240, 0, 0, '', '', 80, 50);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_5_big10,  5, 3, 1, 5, 3, 1, 'CW',  'Łuki bloczkowe - Kobiety', 0, 240, 240, 0, 0, '', '', 80, 50);
				// Berbogi
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'BM',  'Łuki barebow - Mężczyźni', 1, 240, 240, 0, 0, '', '', 122, 50);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'BW',  'Łuki barebow - Kobiety', 1, 240, 240, 0, 0, '', '', 122, 50);
				}

			if ($SubRule==2) { //"All classes
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RM',  'Łuki klasyczne - Mężczyźni', 1, 240, 240, 0, 0, '', '', 122, 70);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RW',  'Łuki klasyczne - Kobiety', 1, 240, 240, 0, 0, '', '', 122, 70);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU24M',  'Łuki klasyczne - Młodzieżowcy mężczyźni', 1, 240, 240, 0, 0, '', '', 122, 70);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU24W',  'Łuki klasyczne - Młodzieżowcy kobiety', 1, 240, 240, 0, 0, '', '', 122, 70);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU21M',  'Łuki klasyczne - Juniorzy', 1, 240, 240, 0, 0, '', '', 122, 70);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU21W',  'Łuki klasyczne - Juniorki', 1, 240, 240, 0, 0, '', '', 122, 70);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU18M',  'Łuki klasyczne - Juniorzy młodsi', 1, 240, 240, 0, 0, '', '', 122, 60);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU18W',  'Łuki klasyczne - Juniorki młodsze', 1, 240, 240, 0, 0, '', '', 122, 60);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU15M',  'Łuki klasyczne - Młodzicy', 1, 240, 240, 0, 0, '', '', 122, 40);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU15W',  'Łuki klasyczne - Młodziczki', 1, 240, 240, 0, 0, '', '', 122, 40);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU12M',  'Łuki klasyczne - Dzieci chłopcy', 1, 240, 240, 0, 0, '', '', 122, 20);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU12W',  'Łuki klasyczne - Dzieci dziewczynki', 1, 240, 240, 0, 0, '', '', 122, 20);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_5_big10,  5, 3, 1, 5, 3, 1, 'CM',  'Łuki bloczkowe - Mężczyźni', 0, 240, 240, 0, 0, '', '', 80, 50);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_5_big10,  5, 3, 1, 5, 3, 1, 'CW',  'Łuki bloczkowe - Kobiety', 0, 240, 240, 0, 0, '', '', 80, 50);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'BM',  'Łuki barebow - Mężczyźni', 1, 240, 240, 0, 0, '', '', 122, 50);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'BW',  'Łuki barebow - Kobiety', 1, 240, 240, 0, 0, '', '', 122, 50);
				}

			if ($SubRule==3) { // U24,U21,U18
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU24M',  'Łuki klasyczne - Młodzieżowcy mężczyźni', 1, 240, 240, 0, 0, '', '', 122, 70);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU24W',  'Łuki klasyczne - Młodzieżowcy kobiety', 1, 240, 240, 0, 0, '', '', 122, 70);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU21M',  'Łuki klasyczne - Juniorzy', 1, 240, 240, 0, 0, '', '', 122, 70);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU21W',  'Łuki klasyczne - Juniorki', 1, 240, 240, 0, 0, '', '', 122, 70);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU18M',  'Łuki klasyczne - Juniorzy młodsi', 1, 240, 240, 0, 0, '', '', 122, 60);
				CreateEvent($TourId, $i++, 0, 0, 48, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU18W',  'Łuki klasyczne - Juniorki młodsze', 1, 240, 240, 0, 0, '', '', 122, 60);
			}
			if ($SubRule==4) { // U15,U12
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU15M',  'Łuki klasyczne - Młodzicy', 1, 240, 240, 0, 0, '', '', 122, 40);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU15W',  'Łuki klasyczne - Młodziczki', 1, 240, 240, 0, 0, '', '', 122, 40);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU12M',  'Łuki klasyczne - Dzieci chłopcy', 1, 240, 240, 0, 0, '', '', 122, 20);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_OUT_FULL,  5, 3, 1, 5, 3, 1, 'RU12W',  'Łuki klasyczne - Dzieci dziewczynki', 1, 240, 240, 0, 0, '', '', 122, 20);
			}
			//Team events
			$i=1;
			if ($SubRule==1) { // "All together
				// Recurve
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RM',  'Łuki klasyczne - mężczyźni zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RW',  'Łuki klasyczne - kobiety zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 12, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'RX',  'Łuki klasyczne - zespoły mieszane', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 3, 0);
				// Compound
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_5_big10,  4, 6, 3, 4, 6, 3, 'CM',  'Łuki bloczkowe - mężczyźni zespoły', 0, 240, 0, 0, 0, '', '', 80, 50, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_5_big10,  4, 6, 3, 4, 6, 3, 'CW',  'Łuki bloczkowe - kobiety zespoły', 0, 240, 0, 0, 0, '', '', 80, 50, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 12, TGT_OUT_5_big10,  4, 4, 2, 4, 4, 2, 'CX',  'Łuki bloczkowe - zespoły mieszane', 0, 240, 0, 0, 0, '', '', 80, 50, '', 1, 0, 1, 0, 3, 0);
				// Barebow
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'BM',  'Łuki barebow - mężczyźni zespoły', 1, 240, 0, 0, 0, '', '', 122, 50, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'BW',  'Łuki barebow - kobiety zespoły', 1, 240, 0, 0, 0, '', '', 122, 50, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 12, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'BX',  'Łuki barebow - zespoły mieszane', 1, 240, 0, 0, 0, '', '', 122, 50, '', 1, 0, 1, 0, 3, 0);
			}

			if ($SubRule==2) { // "All classes
				// Recurve
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RM',  'Łuki klasyczne - seniorzy zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RW',  'Łuki klasyczne - seniorki  zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 12, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'RX',  'Łuki klasyczne - seniorzy zespoły mieszane', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 3, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU24M',  'Łuki klasyczne - zespoły młodzieżowcy mężczyźni ', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU24W',  'Łuki klasyczne - zespoły młodzieżowcy kobiety ', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 12, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'RU24X',  'Łuki klasyczne - zespoły mieszane młodzieżowcy', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 3, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU21M',  'Łuki klasyczne - juniorzy zespoły ', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU21W',  'Łuki klasyczne - juniorki zespoły ', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 12, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'RU21X',  'Łuki klasyczne - juniorzy zespoły mieszane', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 3, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU18M',  'Łuki klasyczne - juniorzy młodsi zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU18W',  'Łuki klasyczne - juniorki młodsze zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 12, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'RU18X',  'Łuki klasyczne - juniorzy młodsi zespoły mieszane', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 3, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU15M',  'Łuki klasyczne - młodzicy zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU15W',  'Łuki klasyczne - młodziczki zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 1, 0, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'RU15X',  'Łuki klasyczne - młodzicy zespoły mieszane', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU12M',  'Łuki klasyczne - dzieci chłopcy zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU12W',  'Łuki klasyczne - dzieci dziewczynki zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 1, 0, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'RU12X',  'Łuki klasyczne - dzieci zespoły mieszane', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 0);
				
				// Compound
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_5_big10,  4, 6, 3, 4, 6, 3, 'CM',  'Łuki bloczkowe - zespoły mężczyźni ', 0, 240, 0, 0, 0, '', '', 80, 50, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_5_big10,  4, 6, 3, 4, 6, 3, 'CW',  'Łuki bloczkowe - zespoły kobiety ', 0, 240, 0, 0, 0, '', '', 80, 50, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 12, TGT_OUT_5_big10,  4, 4, 2, 4, 4, 2, 'CX',  'Łuki bloczkowe - zespoły mieszane', 0, 240, 0, 0, 0, '', '', 80, 50, '', 1, 0, 1, 0, 3, 0);
				// Barebow
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'BM',  'Łuki barebow - zespoły mężczyźni ', 1, 240, 0, 0, 0, '', '', 122, 50, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'BW',  'Łuki barebow - zespoły kobiety ', 1, 240, 0, 0, 0, '', '', 122, 50, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 12, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'BX',  'Łuki barebow - zespoły mieszane', 1, 240, 0, 0, 0, '', '', 122, 50, '', 1, 0, 1, 0, 3, 0);
			}

			if ($SubRule==3) { // "U24,U21,U18
				// Recurve
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU24M',  'Łuki klasyczne - zespoły młodzieżowcy mężczyźni ', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU24W',  'Łuki klasyczne - zespoły młodzieżowcy kobiety ', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 12, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'RU24X',  'Łuki klasyczne - zespoły mieszane młodzieżowcy', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 3, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU21M',  'Łuki klasyczne - juniorzy zespoły ', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU21W',  'Łuki klasyczne - juniorki zespoły ', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 12, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'RU21X',  'Łuki klasyczne - juniorzy zespoły mieszane', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 3, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU18M',  'Łuki klasyczne - juniorzy młodsi zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 12, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU18W',  'Łuki klasyczne - juniorki młodsze zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 12, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'RU18X',  'Łuki klasyczne - juniorzy młodsi zespoły mieszane', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 3, 0);
			}

			if ($SubRule==4) { // "U15, U12
				// Recurve
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU15M',  'Łuki klasyczne - młodzicy zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU15W',  'Łuki klasyczne - młodziczki zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 1, 0, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'RU15X',  'Łuki klasyczne - młodzicy zespoły mieszane', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU12M',  'Łuki klasyczne - dzieci chłopcy zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_OUT_FULL,  4, 6, 3, 4, 6, 3, 'RU12W',  'Łuki klasyczne - dzieci dziewczynki zespoły', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 1, 0, TGT_OUT_FULL,  4, 4, 2, 4, 4, 2, 'RU12X',  'Łuki klasyczne - dzieci zespoły mieszane', 1, 240, 0, 0, 0, '', '', 122, 70, '', 1, 0, 1, 0, 0, 0);
				}
			break;
		case 6:
			if ($SubRule==1) { // "All together
				// Recurve
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RM',  'Łuki klasyczne - Mężczyźni', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RW',  'Łuki klasyczne - Kobiety', 1, 240, 240, 0, 0, '', '', 40, 18);
				// Compound
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_small10,  5, 3, 1, 5, 3, 1, 'CM',  'Łuki bloczkowe - Mężczyźni', 0, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_small10,  5, 3, 1, 5, 3, 1, 'CW',  'Łuki bloczkowe - Kobiety', 0, 240, 240, 0, 0, '', '', 40, 18);
				// Berbogi
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_1_big10,  5, 3, 1, 5, 3, 1, 'BM',  'Łuki barebow - Mężczyźni', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_1_big10,  5, 3, 1, 5, 3, 1, 'BW',  'Łuki barebow - Kobiety', 1, 240, 240, 0, 0, '', '', 40, 18);
				}

			if ($SubRule==2) { //"All classes
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RM',  'Łuki klasyczne - Seniorzy', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RW',  'Łuki klasyczne - Seniorki', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RU24M',  'Łuki klasyczne - Młodzieżowcy mężczyźni', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RU24W',  'Łuki klasyczne - Młodzieżowcy kobiety', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RU21M',  'Łuki klasyczne - Juniorzy', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RU21W',  'Łuki klasyczne - Juniorki', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RU18M',  'Łuki klasyczne - Juniorzy młodsi', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RU18W',  'Łuki klasyczne - Juniorki młodsze', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_IND_1_big10,  5, 3, 1, 5, 3, 1, 'RU15M',  'Łuki klasyczne - Młodzicy', 1, 240, 240, 0, 0, '', '', 60, 18);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_IND_1_big10,  5, 3, 1, 5, 3, 1, 'RU15W',  'Łuki klasyczne - Młodziczki', 1, 240, 240, 0, 0, '', '', 60, 18);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_IND_1_big10,  5, 3, 1, 5, 3, 1, 'RU12M',  'Łuki klasyczne - Dzieci chłopcy', 1, 240, 240, 0, 0, '', '', 80, 15);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_IND_1_big10,  5, 3, 1, 5, 3, 1, 'RU12W',  'Łuki klasyczne - Dzieci dziewczynki', 1, 240, 240, 0, 0, '', '', 80, 15);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_small10,  5, 3, 1, 5, 3, 1, 'CM',  'Łuki bloczkowe - Mężczyźni', 0, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_small10,  5, 3, 1, 5, 3, 1, 'CW',  'Łuki bloczkowe - Kobiety', 0, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_1_big10,  5, 3, 1, 5, 3, 1, 'BM',  'Łuki barebow - Mężczyźni', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_1_big10,  5, 3, 1, 5, 3, 1, 'BW',  'Łuki barebow - Kobiety', 1, 240, 240, 0, 0, '', '', 40, 18);
				}

			if ($SubRule==3) { // U24,U21,U18
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RU24M',  'Łuki klasyczne - Młodzieżowcy mężczyźni', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RU24W',  'Łuki klasyczne - Młodzieżowcy kobiety', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RU21M',  'Łuki klasyczne - Juniorzy', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RU21W',  'Łuki klasyczne - Juniorki', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RU18M',  'Łuki klasyczne - Juniorzy młodsi', 1, 240, 240, 0, 0, '', '', 40, 18);
				CreateEvent($TourId, $i++, 0, 0, 16, TGT_IND_6_big10,  5, 3, 1, 5, 3, 1, 'RU18W',  'Łuki klasyczne - Juniorki młodsze', 1, 240, 240, 0, 0, '', '', 40, 18);
			}
			if ($SubRule==4) { // U15,U12
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_IND_1_big10,  5, 3, 1, 5, 3, 1, 'RU15M',  'Łuki klasyczne - Młodzicy', 1, 240, 240, 0, 0, '', '', 60, 18);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_IND_1_big10,  5, 3, 1, 5, 3, 1, 'RU15W',  'Łuki klasyczne - Młodziczki', 1, 240, 240, 0, 0, '', '', 60, 18);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_IND_1_big10,  5, 3, 1, 5, 3, 1, 'RU12M',  'Łuki klasyczne - Dzieci chłopcy', 1, 240, 240, 0, 0, '', '', 80, 15);
				CreateEvent($TourId, $i++, 0, 0, 0, TGT_IND_1_big10,  5, 3, 1, 5, 3, 1, 'RU12W',  'Łuki klasyczne - Dzieci dziewczynki', 1, 240, 240, 0, 0, '', '', 80, 15);
			}
			//Team events
			$i=1;
			if ($SubRule==1) { // "All together
				// Recurve
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RM',  'Łuki klasyczne - mężczyźni zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RW',  'Łuki klasyczne - kobiety zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 8, TGT_IND_6_big10,  4, 4, 2, 4, 4, 2, 'RX',  'Łuki klasyczne - zespoły mieszane', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 3, 0);
				// Compound
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_small10,  4, 6, 3, 4, 6, 3, 'CM',  'Łuki bloczkowe - mężczyźni zespoły', 0, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_small10,  4, 6, 3, 4, 6, 3, 'CW',  'Łuki bloczkowe - kobiety zespoły', 0, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 8, TGT_IND_6_small10,  4, 4, 2, 4, 4, 2, 'CX',  'Łuki bloczkowe - zespoły mieszane', 0, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 3, 0);
				// Barebow
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_1_big10,  4, 6, 3, 4, 6, 3, 'BM',  'Łuki barebow - mężczyźni zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_1_big10,  4, 6, 3, 4, 6, 3, 'BW',  'Łuki barebow - kobiety zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 8, TGT_IND_1_big10,  4, 4, 2, 4, 4, 2, 'BX',  'Łuki barebow - zespoły mieszane', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 3, 0);
			}

			if ($SubRule==2) { // "All classes
				// Recurve
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RM',  'Łuki klasyczne - seniorzy zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RW',  'Łuki klasyczne - seniorki  zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 8, TGT_IND_6_big10,  4, 4, 2, 4, 4, 2, 'RX',  'Łuki klasyczne - seniorzy zespoły mieszane', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 3, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RU24M',  'Łuki klasyczne - zespoły młodzieżowcy mężczyźni ', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RU24W',  'Łuki klasyczne - zespoły młodzieżowcy kobiety ', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 8, TGT_IND_6_big10,  4, 4, 2, 4, 4, 2, 'RU24X',  'Łuki klasyczne - zespoły mieszane młodzieżowcy', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 3, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RU21M',  'Łuki klasyczne - juniorzy zespoły ', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RU21W',  'Łuki klasyczne - juniorki zespoły ', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 8, TGT_IND_6_big10,  4, 4, 2, 4, 4, 2, 'RU21X',  'Łuki klasyczne - juniorzy zespoły mieszane', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 3, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RU18M',  'Łuki klasyczne - juniorzy młodsi zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RU18W',  'Łuki klasyczne - juniorki młodsze zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 8, TGT_IND_6_big10,  4, 4, 2, 4, 4, 2, 'RU18X',  'Łuki klasyczne - juniorzy młodsi zespoły mieszane', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 3, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_IND_1_big10,  4, 6, 3, 4, 6, 3, 'RU15M',  'Łuki klasyczne - młodzicy zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_IND_1_big10,  4, 6, 3, 4, 6, 3, 'RU15W',  'Łuki klasyczne - młodziczki zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 1, 0, TGT_IND_1_big10,  4, 4, 2, 4, 4, 2, 'RU15X',  'Łuki klasyczne - młodzicy zespoły mieszane', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_IND_1_big10,  4, 6, 3, 4, 6, 3, 'RU12M',  'Łuki klasyczne - dzieci chłopcy zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_IND_1_big10,  4, 6, 3, 4, 6, 3, 'RU12W',  'Łuki klasyczne - dzieci dziewczynki zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 1, 0, TGT_IND_1_big10,  4, 4, 2, 4, 4, 2, 'RU12X',  'Łuki klasyczne - dzieci zespoły mieszane', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 0);
				
				// Compound
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_small10,  4, 6, 3, 4, 6, 3, 'CM',  'Łuki bloczkowe - zespoły mężczyźni ', 0, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_small10,  4, 6, 3, 4, 6, 3, 'CW',  'Łuki bloczkowe - zespoły kobiety ', 0, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 8, TGT_IND_6_small10,  4, 4, 2, 4, 4, 2, 'CX',  'Łuki bloczkowe - zespoły mieszane', 0, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 3, 0);
				// Barebow
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_1_big10,  4, 6, 3, 4, 6, 3, 'BM',  'Łuki barebow - zespoły mężczyźni ', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_1_big10,  4, 6, 3, 4, 6, 3, 'BW',  'Łuki barebow - zespoły kobiety ', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 8, TGT_IND_1_big10,  4, 4, 2, 4, 4, 2, 'BX',  'Łuki barebow - zespoły mieszane', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 3, 0);
			}

			if ($SubRule==3) { // "U24,U21,U18
				// Recurve
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RU24M',  'Łuki klasyczne - zespoły młodzieżowcy mężczyźni ', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RU24W',  'Łuki klasyczne - zespoły młodzieżowcy kobiety ', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 8, TGT_IND_6_big10,  4, 4, 2, 4, 4, 2, 'RU24X',  'Łuki klasyczne - zespoły mieszane młodzieżowcy', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 3, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RU21M',  'Łuki klasyczne - juniorzy zespoły ', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RU21W',  'Łuki klasyczne - juniorki zespoły ', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 8, TGT_IND_6_big10,  4, 4, 2, 4, 4, 2, 'RU21X',  'Łuki klasyczne - juniorzy zespoły mieszane', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 3, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RU18M',  'Łuki klasyczne - juniorzy młodsi zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 0, 8, TGT_IND_6_big10,  4, 6, 3, 4, 6, 3, 'RU18W',  'Łuki klasyczne - juniorki młodsze zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 1);
				CreateEvent($TourId, $i++, 1, 1, 8, TGT_IND_6_big10,  4, 4, 2, 4, 4, 2, 'RU18X',  'Łuki klasyczne - juniorzy młodsi zespoły mieszane', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 3, 0);
			}

			if ($SubRule==4) { // "U15, U12
				// Recurve
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_IND_1_big10,  4, 6, 3, 4, 6, 3, 'RU15M',  'Łuki klasyczne - młodzicy zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_IND_1_big10,  4, 6, 3, 4, 6, 3, 'RU15W',  'Łuki klasyczne - młodziczki zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 1, 0, TGT_IND_1_big10,  4, 4, 2, 4, 4, 2, 'RU15X',  'Łuki klasyczne - młodzicy zespoły mieszane', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 0);
				
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_IND_1_big10,  4, 6, 3, 4, 6, 3, 'RU12M',  'Łuki klasyczne - dzieci chłopcy zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 0, 0, TGT_IND_1_big10,  4, 6, 3, 4, 6, 3, 'RU12W',  'Łuki klasyczne - dzieci dziewczynki zespoły', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 0);
				CreateEvent($TourId, $i++, 1, 1, 0, TGT_IND_1_big10,  4, 4, 2, 4, 4, 2, 'RU12X',  'Łuki klasyczne - dzieci zespoły mieszane', 1, 240, 0, 0, 0, '', '', 40, 18, '', 1, 0, 1, 0, 0, 0);
			}
			break;
			}
	
}

function InsertStandardEvents($TourId, $TourType, $SubRule, $Outdoor=true) { //Define which categories and classes go to which events
	if ($SubRule==1) { // "All together
		// Recurve
		InsertClassEvent($TourId, 0, 1, 'RM',  'R', 'M');
		InsertClassEvent($TourId, 0, 1, 'RW',  'R', 'W');
		// Compound
		InsertClassEvent($TourId, 0, 1, 'CM',  'C', 'M');
		InsertClassEvent($TourId, 0, 1, 'CW',  'C', 'W');
		// Barebow
		InsertClassEvent($TourId, 0, 1, 'BM',  'B', 'M');
		InsertClassEvent($TourId, 0, 1, 'BW',  'B', 'W');
	}
	if ($SubRule==2) { // "All classes
		// Recurve 
		InsertClassEvent($TourId, 0, 1, 'RM',  'R', 'M');
		InsertClassEvent($TourId, 0, 1, 'RW',  'R', 'W');
		InsertClassEvent($TourId, 0, 1, 'RU24M',  'R', 'U24M');
		InsertClassEvent($TourId, 0, 1, 'RU24W',  'R', 'U24W');
		InsertClassEvent($TourId, 0, 1, 'RU21M',  'R', 'U21M');
		InsertClassEvent($TourId, 0, 1, 'RU21W',  'R', 'U21W');
		InsertClassEvent($TourId, 0, 1, 'RU18M',  'R', 'U18M');
		InsertClassEvent($TourId, 0, 1, 'RU18W',  'R', 'U18W');
		InsertClassEvent($TourId, 0, 1, 'RU15M',  'R', 'U15M');
		InsertClassEvent($TourId, 0, 1, 'RU15W',  'R', 'U15W');
		InsertClassEvent($TourId, 0, 1, 'RU12M',  'R', 'U12M');
		InsertClassEvent($TourId, 0, 1, 'RU12W',  'R', 'U12W');
		// Compound 
		InsertClassEvent($TourId, 0, 1, 'CM',  'C', 'M');
		InsertClassEvent($TourId, 0, 1, 'CW',  'C', 'W');
		// Barebow 
		InsertClassEvent($TourId, 0, 1, 'BM',  'B', 'M');
		InsertClassEvent($TourId, 0, 1, 'BW',  'B', 'W');
	}
	
    if ($SubRule==3) {	// "U24,U21,U18
		InsertClassEvent($TourId, 0, 1, 'RU24M',  'R', 'U24M');
		InsertClassEvent($TourId, 0, 1, 'RU24W',  'R', 'U24W');
		InsertClassEvent($TourId, 0, 1, 'RU21M',  'R', 'U21M');
		InsertClassEvent($TourId, 0, 1, 'RU21W',  'R', 'U21W');
		InsertClassEvent($TourId, 0, 1, 'RU18M',  'R', 'U18M');
		InsertClassEvent($TourId, 0, 1, 'RU18W',  'R', 'U18W');
	}
		
	if ($SubRule==4) {	// "U15,U12
		InsertClassEvent($TourId, 0, 1, 'RU15M',  'R', 'U15M');
		InsertClassEvent($TourId, 0, 1, 'RU15W',  'R', 'U15W');
		InsertClassEvent($TourId, 0, 1, 'RU12M',  'R', 'U12M');
		InsertClassEvent($TourId, 0, 1, 'RU12W',  'R', 'U12W');
	}
		
	  
	//Teams
	if ($SubRule==1) { // "All together
			InsertClassEvent($TourId, 1, 4, 'RM', 'R', 'M');
			InsertClassEvent($TourId, 1, 4, 'RW', 'R', 'W');
			InsertClassEvent($TourId, 1, 1, 'RX', 'R', 'M');
			InsertClassEvent($TourId, 2, 1, 'RX', 'R', 'W');

			//Compound Team 
			InsertClassEvent($TourId, 1, 4, 'CM', 'C', 'M');
			InsertClassEvent($TourId, 1, 4, 'CW', 'C', 'W');
			InsertClassEvent($TourId, 1, 1, 'CX', 'C', 'M');
			InsertClassEvent($TourId, 2, 1, 'CX', 'C', 'W');
		
			//Barebow Team 
			InsertClassEvent($TourId, 1, 4, 'BM', 'B', 'M');
			InsertClassEvent($TourId, 1, 4, 'BW', 'B', 'W');
			InsertClassEvent($TourId, 1, 1, 'BX', 'B', 'M');
			InsertClassEvent($TourId, 2, 1, 'BX', 'B', 'W');	

		}

		if ($SubRule==2) { // "All classes
			InsertClassEvent($TourId, 1, 4, 'RM', 'R', 'M');
			InsertClassEvent($TourId, 1, 4, 'RW', 'R', 'W');
			InsertClassEvent($TourId, 1, 1, 'RX', 'R', 'M');
			InsertClassEvent($TourId, 2, 1, 'RX', 'R', 'W');

			InsertClassEvent($TourId, 1, 4, 'RU24M', 'R', 'U24M');
			InsertClassEvent($TourId, 1, 4, 'RU24W', 'R', 'U24W');
			InsertClassEvent($TourId, 1, 1, 'RU24X', 'R', 'U24M');
			InsertClassEvent($TourId, 2, 1, 'RU24X', 'R', 'U24W');

			InsertClassEvent($TourId, 1, 4, 'RU21M', 'R', 'U21M');
			InsertClassEvent($TourId, 1, 4, 'RU21W', 'R', 'U21W');
			InsertClassEvent($TourId, 1, 1, 'RU21X', 'R', 'U21M');
			InsertClassEvent($TourId, 2, 1, 'RU21X', 'R', 'U21W');

			InsertClassEvent($TourId, 1, 4, 'RU18M', 'R', 'U18M');
			InsertClassEvent($TourId, 1, 4, 'RU18W', 'R', 'U18W');
			InsertClassEvent($TourId, 1, 1, 'RU18X', 'R', 'U18M');
			InsertClassEvent($TourId, 2, 1, 'RU18X', 'R', 'U18W');

			InsertClassEvent($TourId, 1, 3, 'RU15M', 'R', 'U15M');
			InsertClassEvent($TourId, 1, 3, 'RU15W', 'R', 'U15W');
			InsertClassEvent($TourId, 1, 1, 'RU15X', 'R', 'U15M');
			InsertClassEvent($TourId, 2, 1, 'RU15X', 'R', 'U15W');

			InsertClassEvent($TourId, 1, 3, 'RU12M', 'R', 'U12M');
			InsertClassEvent($TourId, 1, 3, 'RU12W', 'R', 'U12W');
			InsertClassEvent($TourId, 1, 1, 'RU12X', 'R', 'U12M');
			InsertClassEvent($TourId, 2, 1, 'RU12X', 'R', 'U12W');

			//Compound Team 
			InsertClassEvent($TourId, 1, 4, 'CM', 'C', 'M');
			InsertClassEvent($TourId, 1, 4, 'CW', 'C', 'W');
			InsertClassEvent($TourId, 1, 1, 'CX', 'C', 'M');
			InsertClassEvent($TourId, 2, 1, 'CX', 'C', 'W');
		
			//Barebow Team 
			InsertClassEvent($TourId, 1, 4, 'BM', 'B', 'M');
			InsertClassEvent($TourId, 1, 4, 'BW', 'B', 'W');
			InsertClassEvent($TourId, 1, 1, 'BX', 'B', 'M');
			InsertClassEvent($TourId, 2, 1, 'BX', 'B', 'W');	
		}

		if ($SubRule==3) { // "U24, U21, U18
			InsertClassEvent($TourId, 1, 4, 'RU24M', 'R', 'U24M');
			InsertClassEvent($TourId, 1, 4, 'RU24W', 'R', 'U24W');
			InsertClassEvent($TourId, 1, 1, 'RU24X', 'R', 'U24M');
			InsertClassEvent($TourId, 2, 1, 'RU24X', 'R', 'U24W');

			InsertClassEvent($TourId, 1, 4, 'RU21M', 'R', 'U21M');
			InsertClassEvent($TourId, 1, 4, 'RU21W', 'R', 'U21W');
			InsertClassEvent($TourId, 1, 1, 'RU21X', 'R', 'U21M');
			InsertClassEvent($TourId, 2, 1, 'RU21X', 'R', 'U21W');

			InsertClassEvent($TourId, 1, 4, 'RU18M', 'R', 'U18M');
			InsertClassEvent($TourId, 1, 4, 'RU18W', 'R', 'U18W');
			InsertClassEvent($TourId, 1, 1, 'RU18X', 'R', 'U18M');
			InsertClassEvent($TourId, 2, 1, 'RU18X', 'R', 'U18W');
		}

		if ($SubRule==4) { // "All classes
			InsertClassEvent($TourId, 1, 3, 'RU15M', 'R', 'U15M');
			InsertClassEvent($TourId, 1, 3, 'RU15W', 'R', 'U15W');
			InsertClassEvent($TourId, 1, 1, 'RU15X', 'R', 'U15M');
			InsertClassEvent($TourId, 2, 1, 'RU15X', 'R', 'U15W');

			InsertClassEvent($TourId, 1, 3, 'RU12M', 'R', 'U12M');
			InsertClassEvent($TourId, 1, 3, 'RU12W', 'R', 'U12W');
			InsertClassEvent($TourId, 1, 1, 'RU12X', 'R', 'U12M');
			InsertClassEvent($TourId, 2, 1, 'RU12X', 'R', 'U12W');
		}
	

		
		
//}
		
	
}

