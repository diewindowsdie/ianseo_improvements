<?php

/*

STANDARD DEFINITIONS (Target Tournaments)

*/

// these go here as it is a "global" definition, used or not
$tourCollation = '';
$tourDetIocCode = 'SVK';
if(empty($SubRule)) {
    $SubRule='1';
}

function CreateStandardDivisions($TourId, $Type='', $SubRule) {
	$i=1;
	$optionDivs = array(
        'OL'=>array('Olympijský luk','R'),
        'KL'=>array('Kladkový luk','C'),
        'HL'=>array('Holý luk','B'),
        'DL'=>array('Dlhý luk','L'),
        'TL'=>array('Tradičný luk','T')
    );

	if ($Type == 'FIELD_3D') {
        $optionDivs['HU'] = array('Hunter','');
    }
    foreach ($optionDivs as $k => $v){
		CreateDivision($TourId, $i++, $k, $v[0], 1, $v[1], $v[1]);
	}
}

function CreateStandardClasses($TourId, $SubRule, $Type='OUTDOOR') {
    $order=1;
    if($Type != 'FIELD' AND $Type != '3D') {
        CreateClass($TourId, $order++, 21, 49, 0, 'M', 'M', 'Muži');
        CreateClass($TourId, $order++, 21, 49, 1, 'W', 'W', 'Ženy');
        CreateClass($TourId, $order++, 18, 20, 0, 'U21M', 'U21M,M', 'Juniori');
        CreateClass($TourId, $order++, 18, 20, 1, 'U21W', 'U21W,W', 'Juniorky');
        CreateClass($TourId, $order++, 15, 17, 0, 'U18M', 'U18M,U21M,M', 'Kadeti');
        CreateClass($TourId, $order++, 15, 17, 1, 'U18W', 'U18W,U21W,W', 'Kadetky');
        if($Type == '70M') {
            CreateClass($TourId, $order++, 13, 14, 0, 'U15M', 'U15M,U18M,U21M,M', 'Chlapci do 14 rokov','1','KL,HL,DL,TL');
            CreateClass($TourId, $order++, 13, 14, 1, 'U15W', 'U15W,U18M,U21W,W', 'Dievčatá do 14 rokov','1','KL,HL,DL,TL');
            CreateClass($TourId, $order++, 14, 14, 0, 'OLU15M', 'OLU15M,U18M,U21M,M', 'Chlapci do 14 rokov','1','OL');
            CreateClass($TourId, $order++, 14, 14, 1, 'OLU15W', 'OLU15W,U18M,U21W,W', 'Dievčatá do 14 rokov','1','OL');
            CreateClass($TourId, $order++, 13, 13, 0, 'OLU14M', 'OLU14M,OLU15M,U18M,U21M,M', 'Chlapci do 13 rokov','1','OL');
            CreateClass($TourId, $order++, 13, 13, 1, 'OLU14W', 'OLU14W,OLU15W,U18W,U21W,W', 'Dievčatá do 13 rokov','1','OL');
        } else {
            CreateClass($TourId, $order++, 13, 14, 0, 'U15M', 'U15M,U18M,U21M,M', 'Chlapci do 14 rokov');
            CreateClass($TourId, $order++, 13, 14, 1, 'U15W', 'U15W,U18W,U21W,W', 'Dievčatá do 14 rokov');
        }
        CreateClass($TourId, $order++, 11, 12, 0, 'U13M', 'U13M'.($SubRule == 'OUTDOOR' ? ',U14M':'').',U15M,U18M,U21M,M', 'Chlapci do 12 rokov');
        CreateClass($TourId, $order++, 11, 12, 1, 'U13W', 'U13W'.($SubRule == 'OUTDOOR' ? ',U14M':'').',U15W,U18W,U21W,W', 'Dievčatá do 12 rokov');
        CreateClass($TourId, $order++, 6, 10, 0, 'U11M', 'U11M,U13M'.($SubRule == 'OUTDOOR' ? ',U14M':'').',U15M,U18M,U21M,M', 'Chlapci do 10 rokov');
        CreateClass($TourId, $order++, 6, 10, 1, 'U11W', 'U11W,U13W'.($SubRule == 'OUTDOOR' ? ',U14M':'').',U15W,U18W,U21W,W', 'Dievčatá do 10 rokov');
    } else {
        CreateClass($TourId, $order++, 18, 49, 0, 'M', 'M', 'Muži');
        CreateClass($TourId, $order++, 18, 49, 1, 'W', 'W', 'Ženy');
        CreateClass($TourId, $order++, 13, 17, 0, 'U18M', 'U18M,M', 'Kadeti');
        CreateClass($TourId, $order++, 13, 17, 1, 'U18W', 'U18W,W', 'Kadetky');
        CreateClass($TourId, $order++, 6, 12, 0, 'U13M', 'U13M,U18M,M', 'Kadeti');
        CreateClass($TourId, $order++, 6, 12, 1, 'U13W', 'U13W,U18W,W', 'Kadetky');
    }
    CreateClass($TourId, $order++, 50, 59, 0, '50M', '50M,M', 'Veteráni 50+');
    CreateClass($TourId, $order++, 50, 59, 1, '50W', '50W,W', 'Veteránky 50+');
    CreateClass($TourId, $order++, 60, 69, 0, '60M', '60M,50M,M', 'Veteráni 60+');
    CreateClass($TourId, $order++, 60, 69, 1, '60W', '60W,50W,W', 'Veteránky 60+');
    CreateClass($TourId, $order++, 70, 99, 0, '70M', '70M,60M,50M,M', 'Veteráni 70+', '1', ($Type == '3D' ? 'OL,KL,HL':''));
    CreateClass($TourId, $order++, 70, 99, 1, '70W', '70W,60W,50W,W', 'Veteránky 70+', '1', ($Type == '3D' ? 'OL,KL,HL':''));

}

function CreateStandardEvents($TourId, $SubRule, $Outdoor=true) {
    $i = 1;
    //Olympijský luk
    if ($SubRule == 2 or $SubRule == 4) {
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_5_big10), 5, 3, 1, 5, 3, 1, 'OLU11W', 'Olympijský luk Dievčatá do 10 rokov', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 80), ($Outdoor ? 20 : 10));
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_5_big10), 5, 3, 1, 5, 3, 1, 'OLU11M', 'Olympijský luk Chlapci do 10 rokov', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 80), ($Outdoor ? 20 : 10));
        CreateEvent($TourId, $i++, 0, 0, 8, ($Outdoor ? TGT_OUT_FULL : TGT_IND_5_big10), 5, 3, 1, 5, 3, 1, 'OLU13W', 'Olympijský luk Dievčatá do 12 rokov', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 80), ($Outdoor ? 30 : 18));
        CreateEvent($TourId, $i++, 0, 0, 8, ($Outdoor ? TGT_OUT_FULL : TGT_IND_5_big10), 5, 3, 1, 5, 3, 1, 'OLU13M', 'Olympijský luk Chlapci do 12 rokov', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 80), ($Outdoor ? 30 : 18));
        if ($Outdoor) {
            CreateEvent($TourId, $i++, 0, 0, 8, TGT_OUT_FULL, 5, 3, 1, 5, 3, 1, 'OLU14W', 'Olympijský luk Dievčatá do 13 rokov', 1, 240, 255, 0, 0, '', '', 122, 40);
            CreateEvent($TourId, $i++, 0, 0, 8, TGT_OUT_FULL, 5, 3, 1, 5, 3, 1, 'OLU14M', 'Olympijský luk Chlapci do 13 rokov', 1, 240, 255, 0, 0, '', '', 122, 40);
        }
        CreateEvent($TourId, $i++, 0, 0, 8, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'OLU15W', 'Olympijský luk Dievčatá do 14 rokov', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 60), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, 8, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'OLU15M', 'Olympijský luk Chlapci do 14 rokov', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 60), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, 8, ($Outdoor ? TGT_OUT_FULL : TGT_IND_6_big10), 5, 3, 1, 5, 3, 1, 'OLU18W', 'Olympijský luk Kadetky', 1, 240, ($SubRule == 4 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 60 : 18));
        CreateEvent($TourId, $i++, 0, 0, 8, ($Outdoor ? TGT_OUT_FULL : TGT_IND_6_big10), 5, 3, 1, 5, 3, 1, 'OLU18M', 'Olympijský luk Kadeti', 1, 240, ($SubRule == 4 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 60 : 18));
        CreateEvent($TourId, $i++, 0, 0, 8, ($Outdoor ? TGT_OUT_FULL : TGT_IND_6_big10), 5, 3, 1, 5, 3, 1, 'OLU21W', 'Olympijský luk Juniorky', 1, 240, ($SubRule == 4 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 70 : 18));
        CreateEvent($TourId, $i++, 0, 0, 8, ($Outdoor ? TGT_OUT_FULL : TGT_IND_6_big10), 5, 3, 1, 5, 3, 1, 'OLU21M', 'Olympijský luk Juniori', 1, 240, ($SubRule == 4 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 70 : 18));
    }
    if ($SubRule == 2 or $SubRule == 3) {
        CreateEvent($TourId, $i++, 0, 0, 8, ($Outdoor ? TGT_OUT_FULL : TGT_IND_6_big10), 5, 3, 1, 5, 3, 1, 'OLW', 'Olympijský luk Ženy', 1, 240, ($SubRule == 3 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 70 : 18));
        CreateEvent($TourId, $i++, 0, 0, ($SubRule == 3 ? 16 : 8), ($Outdoor ? TGT_OUT_FULL : TGT_IND_6_big10), 5, 3, 1, 5, 3, 1, 'OLM', 'Olympijský luk Muži', 1, 240, ($SubRule == 3 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 70 : 18));
    }
    if ($SubRule == 2 or (!$Outdoor and $SubRule == 3) or ($Outdoor and $SubRule == 5)) {
        CreateEvent($TourId, $i++, 0, 0, 2, ($Outdoor ? TGT_OUT_FULL : TGT_IND_6_big10), 5, 3, 1, 5, 3, 1, 'OL50W', 'Olympijský luk Veteránky 50+', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 60 : 18));
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_6_big10), 5, 3, 1, 5, 3, 1, 'OL50M', 'Olympijský luk Veteráni 50+', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 60 : 18));
        CreateEvent($TourId, $i++, 0, 0, 2, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'OL60W', 'Olympijský luk Veteránky 60+', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'OL60M', 'Olympijský luk Veteráni 60+', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 50 : 18));
    }
    if ((!$Outdoor and $SubRule == 3) or ($Outdoor and $SubRule == 5)) {
        CreateEvent($TourId, $i++, 0, 0, 2, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'OL70W', 'Olympijský luk Veteránky 70+', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 60), ($Outdoor ? 40 : 18));
        CreateEvent($TourId, $i++, 0, 0, 2, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'OL70M', 'Olympijský luk Veteráni 70+', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 60), ($Outdoor ? 40 : 18));
    }
    //Kladkový luk
    if ($SubRule == 2 or $SubRule == 4) {
        if ($Outdoor) {
            CreateEvent($TourId, $i++, 0, 0, 2, TGT_OUT_5_big10, 5, 3, 1, 5, 3, 1, 'KLU15W', 'Kladkový luk Dievčatá do 14 rokov', 0, 240, 255, 0, 0, '', '', 80, ($Outdoor ? 50 : 18));
            CreateEvent($TourId, $i++, 0, 0, 2, TGT_OUT_5_big10, 5, 3, 1, 5, 3, 1, 'KLU15M', 'Kladkový luk Chlapci do 14 rokov', 0, 240, 255, 0, 0, '', '', 80, ($Outdoor ? 50 : 18));
        }
        CreateEvent($TourId, $i++, 0, 0, ($Outdoor ? ($SubRule==4 ? 8:4) : ($SubRule==4 ? 4:2)), ($Outdoor ? TGT_OUT_5_big10 : TGT_IND_6_small10), 5, 3, 1, 5, 3, 1, 'KLU18W', 'Kladkový luk Kadetky', 0, 240, (($SubRule == 4 AND !$Outdoor) ? 254 : 255), 0, 0, '', '', ($Outdoor ? 80 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, ($Outdoor ? ($SubRule==4 ? 8:4) : ($SubRule==4 ? 4:2)), ($Outdoor ? TGT_OUT_5_big10 : TGT_IND_6_small10), 5, 3, 1, 5, 3, 1, 'KLU18M', 'Kladkový luk Kadeti', 0, 240, ($SubRule == 4 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 80 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, ($Outdoor ? 8 : 4), ($Outdoor ? TGT_OUT_5_big10 : TGT_IND_6_small10), 5, 3, 1, 5, 3, 1, 'KLU21W', 'Kladkový luk Juniorky', 0, 240, (($SubRule == 4 AND !$Outdoor) ? 254 : 255), 0, 0, '', '', ($Outdoor ? 80 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, ($Outdoor ? 8 : 4), ($Outdoor ? TGT_OUT_5_big10 : TGT_IND_6_small10), 5, 3, 1, 5, 3, 1, 'KLU21M', 'Kladkový luk Juniori', 0, 240, ($SubRule == 4 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 80 : 40), ($Outdoor ? 50 : 18));
    }
    if ($SubRule == 2 or $SubRule == 3) {
        CreateEvent($TourId, $i++, 0, 0, 8, ($Outdoor ? TGT_OUT_5_big10 : TGT_IND_6_small10), 5, 3, 1, 5, 3, 1, 'KLW', 'Kladkový luk Ženy', 0, 240, ($SubRule == 3 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 80 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, ($SubRule == 3 ? 16 : 8), ($Outdoor ? TGT_OUT_5_big10 : TGT_IND_6_small10), 5, 3, 1, 5, 3, 1, 'KLM', 'Kladkový luk Muži', 0, 240, ($SubRule == 3 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 80 : 40), ($Outdoor ? 50 : 18));
    }
    if ($SubRule == 2 or (!$Outdoor and $SubRule == 3) or ($Outdoor and $SubRule == 5)) {
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_5_big10 : TGT_IND_6_small10), 5, 3, 1, 5, 3, 1, 'KL50W', 'Kladkový luk Veteránky 50+', 0, 240, 255, 0, 0, '', '', ($Outdoor ? 80 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, ($Outdoor ? 8 : 4), ($Outdoor ? TGT_OUT_5_big10 : TGT_IND_6_small10), 5, 3, 1, 5, 3, 1, 'KL50M', 'Kladkový luk Veteráni 50+', 0, 240, 255, 0, 0, '', '', ($Outdoor ? 80 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, 2, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_small10), 5, 3, 1, 5, 3, 1, 'KL60W', 'Kladkový luk Veteránky 60+', 0, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_small10), 5, 3, 1, 5, 3, 1, 'KL60M', 'Kladkový luk Veteráni 60+', 0, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 50 : 18));
    }
    if ((!$Outdoor and $SubRule == 3)) {
        CreateEvent($TourId, $i++, 0, 0, 2, TGT_IND_1_small10, 5, 3, 1, 5, 3, 1, 'KL70W', 'Kladkový luk Veteránky 70+', 0, 240, 255, 0, 0, '', '', 60, 18);
        CreateEvent($TourId, $i++, 0, 0, 2, TGT_IND_1_small10, 5, 3, 1, 5, 3, 1, 'KL70M', 'Kladkový luk Veteráni 70+', 0, 240, 255, 0, 0, '', '', 60, 18);
    }
    //Holý luk
    if ($SubRule == 2 or $SubRule == 4) {
        if ($Outdoor) {
            CreateEvent($TourId, $i++, 0, 0, 2, TGT_OUT_FULL, 5, 3, 1, 5, 3, 1, 'HLU11W', 'Holý luk Dievčatá do 10 rokov', 1, 240, 255, 0, 0, '', '', 122, 20);
            CreateEvent($TourId, $i++, 0, 0, 2, TGT_OUT_FULL, 5, 3, 1, 5, 3, 1, 'HLU11M', 'Holý luk Chlapci do 10 rokov', 1, 240, 255, 0, 0, '', '', 122, 20);
        }
        CreateEvent($TourId, $i++, 0, 0,  ($Outdoor ? 4 : 2), ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'HLU13W', 'Holý luk Dievčatá do 12 rokov', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 60), ($Outdoor ? 20 : 10));
        CreateEvent($TourId, $i++, 0, 0, ($Outdoor ? 4 : 2), ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'HLU13M', 'Holý luk Chlapci do 12 rokov', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 60), ($Outdoor ? 20 : 10));
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_5_big10), 5, 3, 1, 5, 3, 1, 'HLU15W', 'Holý luk Dievčatá do 14 rokov', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 80), ($Outdoor ? 30 : 18));
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_5_big10), 5, 3, 1, 5, 3, 1, 'HLU15M', 'Holý luk Chlapci do 14 rokov', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 80), ($Outdoor ? 30 : 18));
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'HLU18W', 'Holý luk Kadetky', 1, 240, (($SubRule == 4 AND !$Outdoor) ? 254 : 255), 0, 0, '', '', ($Outdoor ? 122 : 60), ($Outdoor ? 40 : 18));
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'HLU18M', 'Holý luk Kadeti', 1, 240, (($SubRule == 4 AND !$Outdoor) ? 254 : 255), 0, 0, '', '', ($Outdoor ? 122 : 60), ($Outdoor ? 40 : 18));
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'HLU21W', 'Holý luk Juniorky', 1, 240, ($SubRule == 4 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'HLU21M', 'Holý luk Juniori', 1, 240, ($SubRule == 4 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 50 : 18));
    }
    if ($SubRule == 2 or $SubRule == 3) {
        CreateEvent($TourId, $i++, 0, 0, 8, ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'HLW', 'Holý luk Ženy', 1, 240, ($SubRule == 3 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, ($SubRule == 3 ? 16 : 8), ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'HLM', 'Holý luk Muži', 1, 240, ($SubRule == 3 ? 254 : 255), 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 50 : 18));
    }
    if ($SubRule == 2 or (!$Outdoor and $SubRule == 3) or ($Outdoor and $SubRule == 5)) {
        CreateEvent($TourId, $i++, 0, 0, ($Outdoor ? 4 : ($SubRule ==4 ? 8:4)), ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'HL50W', 'Holý luk Veteránky 50+', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, ($Outdoor ? 4 : 2), ($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 5, 3, 1, 5, 3, 1, 'HL50M', 'Holý luk Veteráni 50+', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 0, 0, 2, ($Outdoor ? TGT_OUT_FULL : TGT_IND_5_big10), 5, 3, 1, 5, 3, 1, 'HL60W', 'Holý luk Veteránky 60+', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 80), ($Outdoor ? 40 : 18));
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_5_big10), 5, 3, 1, 5, 3, 1, 'HL60M', 'Holý luk Veteráni 60+', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 80), ($Outdoor ? 40 : 18));
    }
    if ((!$Outdoor and $SubRule == 3) or ($Outdoor and $SubRule == 5)) {
        CreateEvent($TourId, $i++, 0, 0, 2, ($Outdoor ? TGT_OUT_FULL : TGT_IND_5_big10), 5, 3, 1, 5, 3, 1, 'HL70W', 'Holý luk Veteránky 70+', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 80), ($Outdoor ? 30 : 18));
        CreateEvent($TourId, $i++, 0, 0, 4, ($Outdoor ? TGT_OUT_FULL : TGT_IND_5_big10), 5, 3, 1, 5, 3, 1, 'HL70M', 'Holý luk Veteráni 70+', 1, 240, 255, 0, 0, '', '', ($Outdoor ? 122 : 80), ($Outdoor ? 30 : 18));
    }
    //Other
    if(!$Outdoor and $SubRule == 3) {
        CreateEvent($TourId, $i++, 0, 0, 2,TGT_IND_5_big10, 5, 3, 1, 5, 3, 1, 'DLM', 'Dlhý luk Muži', 1, 240, 255, 0, 0, '', '', 80, 18);
        CreateEvent($TourId, $i++, 0, 0, 2,TGT_IND_5_big10, 5, 3, 1, 5, 3, 1, 'TLM', 'Tradičný luk Muži', 1, 240, 255, 0, 0, '', '', 80, 18);
    }

    //TEAM
    if($SubRule == 3) {
        $i = 1;
        CreateEvent($TourId, $i++, 1, 0, 4,($Outdoor ? TGT_OUT_FULL : TGT_IND_6_big10), 4, 6, 3, 4, 6, 3, 'OL', 'Olympijský luk', 1, 240, 0, 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 70 : 18));
        CreateEvent($TourId, $i++, 1, 0, 4,($Outdoor ? TGT_OUT_5_big10 : TGT_IND_6_small10), 4, 6, 3, 4, 6, 3, 'KL', 'Kladkový luk', 0, 240, 0, 0, 0, '', '', ($Outdoor ? 80 : 40), ($Outdoor ? 50 : 18));
        CreateEvent($TourId, $i++, 1, 0, 4,($Outdoor ? TGT_OUT_FULL : TGT_IND_1_big10), 4, 6, 3, 4, 6, 3, 'HL', 'Holý luk', 1, 240, 0, 0, 0, '', '', ($Outdoor ? 122 : 40), ($Outdoor ? 50 : 18));
        if(!$Outdoor) {
            CreateEvent($TourId, $i++, 1, 0, 4,TGT_IND_5_big10, 4, 6, 3, 4, 6, 3, 'DL', 'Dlhý luk', 1, 240, 0, 0, 0, '', '', 80, 18);
        }
    }
}

function InsertStandardEvents($TourId, $SubRule, $Outdoor=true) {
    foreach (array('OL','KL','HL','DL','TL') as $vDiv) {
        $clsTmpArr = array('W','U21W','U18W','U15W','U14W','U13W','U11W','50W','60W','70W','M','U21M','U18M','U15M','U14M','U13M','U11M','50M','60M','70M');
        foreach($clsTmpArr as $vClass) {
            InsertClassEvent($TourId, 0, 1, $vDiv.$vClass, $vDiv, (($Outdoor AND $vDiv=='OL' AND ($vClass=='U15W' OR $vClass=='U15M' OR $vClass=='U14W' OR $vClass=='U14M')) ? 'OL' : '') .  $vClass);
        }
    }
    if($SubRule == 3) {
        $divTmpArr = array('OL','KL','HL');
        if(!$Outdoor) {
            $divTmpArr[] = 'DL';
        }
        foreach ($divTmpArr as $vDiv) {
            $clsTmpArr = array('W','M');
            if(!$Outdoor) {
                $clsTmpArr = array('W','M','50W','50M','60W','60M');
            }
            foreach($clsTmpArr as $vClass) {
                InsertClassEvent($TourId, 1, 3, $vDiv, $vDiv, $vClass);
            }
        }
    }
}
