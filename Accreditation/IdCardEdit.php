<?php
/*

=> IDcard can have a background
=> IDcard can have a back with schedule AND-OR a freetext
=> ALL elements are positional (negative x or y position means not printed at all)



*/

// ACL and other checks are made in the config
require_once('./IdCardEdit-config.php');

require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Lib/Fun_DateTime.inc.php');
require_once('Common/Lib/Fun_Modules.php');
require_once('Common/Lib/Fun_Phases.inc.php');
require_once('Common/Lib/CommonLib.php');
require_once('IdCardEmpty.php');


$CardFile="{$CardType}-{$CardNumber}-{$CardPage}";


$RowBn=NULL;
$Select = "SELECT IdCards.*, LENGTH(IcBackground) as ImgSize FROM IdCards WHERE IcTournament={$_SESSION['TourId']} and IcType='$CardType' and IcNumber=$CardNumber";
$Rs=safe_r_sql($Select);

$RowBn=emptyIdCard(safe_fetch($Rs));

if($RowBn->Pages==1 and $CardPage==2) {
    cd_redirect('IdCardEdit.php'.go_get('CardPage', 1));
    die();
}

$IncludeJquery = true;
$JS_SCRIPT=array(
	phpVars2js(array(
        'CardType' => $CardType,
        'CardNumber' => $CardNumber,
        'CardPage' => $CardPage,
        'btnCancel' => get_text('CmdCancel'),
        'btnConfirm' => get_text('Confirm', 'Tournament'),
        'btnSave' => get_text('CmdSave'),
        'msgAreYouSure' => get_text('MsgAreYouSure'),
        'btnEdit' => get_text('',''),
    )),
	'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Common/js/Fun_JS.inc.js"></script>',
	'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Common/js/jscolor.js"></script>',
	'<script type="text/javascript" src="./IdCardEdit.js"></script>',
	'<link href="./IdCardEdit.css" rel="stylesheet" type="text/css"></link>',
	);

$IncludeFA=true;
include('Common/Templates/head.php');

echo '<div id="PrnParameters">';
if($CardPage==1) {
    // print the matching of the divclass
    $CategoryList='';
    switch($CardType) {
        case 'A':
        case 'Q':
        case 'E':
            $CategoryList='<table class="Tabella">';
            $IsAthlete=($CardType!='A' ? '1' : '');
            $Classes=array();
            $q=safe_r_sql("select * from Classes where ClTournament={$_SESSION['TourId']} ".($IsAthlete ? 'and ClAthlete=1' : '')." order by ClViewOrder");
            while($r=safe_fetch($q)) {
                $Classes[$r->ClId]=$r->ClDescription;
            }
            $Divisions=array();
            $q=safe_r_sql("select * from Divisions where DivTournament={$_SESSION['TourId']} ".($IsAthlete ? 'and DivAthlete=1' : '')." order by DivViewOrder");
            while($r=safe_fetch($q)) {
                $Divisions[$r->DivId]=$r->DivDescription;
            }

            $Categories=array();
            $q=safe_r_sql("select * from Classes inner join Divisions on DivTournament=ClTournament and (ClDivisionsAllowed='' or find_in_set(DivId, ClDivisionsAllowed)) and ClAthlete=DivAthlete where DivTournament='{$_SESSION['TourId']}' ".($IsAthlete ? 'and DivAthlete=1' : '')." order by DivViewOrder, ClViewOrder");
            while($r=safe_fetch($q)) {
                $Categories[$r->DivId][$r->ClId]=$r->DivId.$r->ClId;
            }

            $Matches=getModuleParameter('Accreditation', 'Matches-'.$CardType.'-'.$CardNumber, '');
            if($Matches) {
                $Matches=explode(',', $Matches);
            } else {
                $Matches=array();
            }

            $CategoryList.= '<tr><th colspan="'.(1+count($Classes)).'" class="Title">'.get_text('SetAccreditationMatches', 'BackNumbers').'</th></tr>';
            $CategoryList.= '<tr><th></th>';
            foreach($Classes as $key => $desc) {
                $CategoryList.= '<th onclick="toggleClass(\''.$key.'\')">'.$desc.'</th>';
            }
            $CategoryList.= '</tr>';

            foreach($Divisions as $Div => $desc) {
                $CategoryList.= '<tr>';
                $CategoryList.= '<th onclick="toggleDiv(\''.$Div.'\')">'.$desc.'</th>';
                foreach($Classes as $Cl => $desc) {
                    if(isset($Categories[$Div][$Cl])) {
                        $CategoryList.= '<td><input type="checkbox" onclick="toggleCategory()" class="CategorySelects ClSelect'.$Cl.' DivSelect'.$Div.'" value="'.$Categories[$Div][$Cl].'"'.(in_array($Categories[$Div][$Cl], $Matches) ? ' checked="checked"' : '').'></td>';
                    } else {
                        $CategoryList.= '<td></td>';
                    }
                }
                $CategoryList.= '</tr>';
            }
            $CategoryList.='</table>';
            echo $CategoryList;
            break;
        case 'I':
        case 'T':
        case 'Y':
        case 'Z':
            $Events=array();
            $q=safe_r_sql("select * from Events where EvTeamEvent=".(($CardType=='I' or $CardType=='Y') ? 0 : 1)." and EvTournament='{$_SESSION['TourId']}' order by EvProgr");
            while($r=safe_fetch($q)) $Events[$r->EvCode]=$r->EvCode;

            $Matches=getModuleParameter('Accreditation', 'Matches-'.$CardType.'-'.$CardNumber, '');
            if($Matches) {
                $Matches=explode(',', $Matches);
            } else {
                $Matches=array();
            }

            echo '<div class="Title mt-3">'.get_text('SetAccreditationMatches', 'BackNumbers').'</div>';
            echo '<div class="d-flex" id="CategoryList">';
            foreach($Events as $EvCode => $desc) {
                echo '<div><input type="checkbox" onclick="toggleCategory()" class="CategorySelects" value="'.$EvCode.'"'.(in_array($EvCode, $Matches) ? ' checked="checked"' : '').'>'.$desc.'</div>';
            }
            echo '</div>';
            break;
    }


    echo '<table class="Tabella mt-3">';
    echo '<tr>';
    echo '<th class="Title" width="50%" colspan="3">' . get_text('BadgeDimention', 'BackNumbers')  . '</th>';
    echo '<th class="Title" width="50%">' . get_text('BadgePreview', 'BackNumbers')  . '</th>';
    echo '</tr>';

    echo '<tr>';
    //Dimensione Accredito
    echo '<th rowspan="2">' . get_text('BadgeDimention', 'BackNumbers')  . '</th>
        <th>'.get_text('Width', 'BackNumbers') . '</th>
        <th>'.get_text('Heigh', 'BackNumbers') . '</th>';
    //Esempio...
    echo '<td rowspan="10" class="Center">
        <div id="DetachableImage"><img id="IdCardImage" width="'.($RowBn->Settings["Width"]*2).'" height="'.($RowBn->Settings["Height"]*2).'" src="ImgIdCard.php?CardType='.$CardType.'&CardNumber='.$CardNumber.'&CardPage='.$CardPage.'">
        <div class="Center mt-3"><div class="Button NoWrap mx-3" onclick="$(\'#DetachableImage\').toggleClass(\'detached\')">Detach/Fix Image</div>';

    if($RowBn->Pages==2) {
        echo '<div class="Button NoWrap mx-3" onclick="location.href=\'IdCardEdit.php'.go_get('CardPage', 3-$CardPage).'\'">' . get_text('BadgeEditPage'.(3-$CardPage), 'BackNumbers') . '</div>';
    }
    echo '</div></div></td>';
    echo '</tr>
        <tr align="center">
        <td><input type="text" onchange="UpdateCardSettings(this)" id="IdCardsSettings-Width" size="10" value="' . $RowBn->Settings["Width"] . '"></td>
        <td><input type="text" onchange="UpdateCardSettings(this)" id="IdCardsSettings-Height" size="10" value="' . $RowBn->Settings["Height"] . '"></td>
        </tr>
        <tr align="center">
            <th>' . get_text('PaperSize', 'Tournament')  . '</th>
            <td><input type="text" onchange="UpdateCardSettings(this)" id="IdCardsSettings-PaperWidth" size="10" value="' . $RowBn->Settings["PaperWidth"] . '"></td>
            <td><input type="text" onchange="UpdateCardSettings(this)" id="IdCardsSettings-PaperHeight" size="10" value="' . $RowBn->Settings["PaperHeight"] . '"></td>
        </tr>
        <tr align="center">
            <th>'.get_text('IdCardOffsets', 'BackNumbers') . '</th>
            <td><input type="text" onchange="UpdateCardSettings(this)" id="IdCardsSettings-OffsetX" size="10" value="' . $RowBn->Settings["OffsetX"] . '"></td>
            <td><input type="text" onchange="UpdateCardSettings(this)" id="IdCardsSettings-OffsetY" size="10" value="' . $RowBn->Settings["OffsetY"] . '"></td>
        </tr>';

    // Background Image
    $ClImg=($RowBn->ImgSize ? '' : 'd-none');
    $ClLoader=($RowBn->ImgSize ? 'd-none' : '');
    $ImgSrc='';
    if(is_file($CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-'.$CardType.'-'.$CardNumber.'-Accreditation.jpg')) {
        $ImgSrc='src="'.$CFG->ROOT_DIR.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-'.$CardType.'-'.$CardNumber.'-Accreditation.jpg"';
    }
    $BgImage= '<img '.$ImgSrc.' id="IdCardBackground">
        <div class="mt-3 Center"><div class="Button" name="DeleteBgImage" onclick="deleteBackground(this)">' . get_text('CmdDelete','Tournament') . '</div></div>
        ';

    echo '<tr><td colspan="3">
            <div id="BgImageLoaderDiv" class="Center my-3 '.$ClLoader.'"><span><input name="UploadedBgImage" id="UploadedBgImage" type="file" size="20" /></span><div class="Button mx-3" onclick="uploadBackground()">'.get_text('CmdUpload', 'Tournament').'</div></div>
            <table class="Tabella mt-3 '.$ClImg.'" id="BgDetails">
            <tr><th class="Title" colspan="4">' . get_text('BgImage', 'BackNumbers')  . '</th></tr>
            <tr>
            <td colspan="4" class="Center">'.$BgImage.'</td>
            </tr>
            <tr>
            <th>'.get_text('PosX', 'BackNumbers').'</th>
            <th>'.get_text('PosY', 'BackNumbers').'</th>
            <th>'.get_text('Width', 'BackNumbers').'</th>
            <th>'.get_text('Heigh', 'BackNumbers').'</th>
            </tr>
            <tr>
            <td><input type="text" onchange="UpdateCardSettings(this)" id="IdCardsSettings-IdBgX" size="7" maxlength="5" value="' . $RowBn->Settings["IdBgX"] . '" /></td>
            <td><input type="text" onchange="UpdateCardSettings(this)" id="IdCardsSettings-IdBgY" size="7" maxlength="5" value="' . $RowBn->Settings["IdBgY"] . '" /></td>
            <td><input type="text" onchange="UpdateCardSettings(this)" id="IdCardsSettings-IdBgW" size="7" maxlength="5" value="' . $RowBn->Settings["IdBgW"] . '" /></td>
            <td><input type="text" onchange="UpdateCardSettings(this)" id="IdCardsSettings-IdBgH" size="7" maxlength="5" value="' . $RowBn->Settings["IdBgH"] . '" /></td>
            </tr>
            </table>
        </td></tr>
        
        ';


//Sfondo
    echo '</tr>';
} else {
    echo '<table class="Tabella">';
    echo '<tr><td width="100%" align="center">
        <div id="DetachableImage"><img id="IdCardImage" width="'.($RowBn->Settings["Width"]*2).'" height="'.($RowBn->Settings["Height"]*2).'" src="ImgIdCard.php?CardType='.$CardType.'&CardNumber='.$CardNumber.'&CardPage='.$CardPage.'"></div>
        <div class="Center mt-3">
        <div class="Button NoWrap" onclick="$(\'#DetachableImage\').toggleClass(\'detached\')">Detach/Fix Image</div>';
    if($RowBn->Pages==2) {
        echo '<div class="Button NoWrap mx-3" onclick="location.href=\'IdCardEdit.php'.go_get('CardPage', 3-$CardPage).'\'">' . get_text('BadgeEditPage'.(3-$CardPage), 'BackNumbers') . '</div>';
    }
    echo '</div></td></tr>';
}
echo '</table>';

//Parametri
echo '<table class="Tabella">';
echo '<tr><th>&nbsp;</th><th>' . get_text('Progr')  . '</th>
	<th colspan="3">' . get_text('Content', 'BackNumbers')  . '</th>
	<th nowrap="nowrap">' . get_text('PosX', 'BackNumbers') . '
		<br/>' . get_text('PosY', 'BackNumbers') . '</th>
	<th>' . get_text('Width', 'BackNumbers') . '
		<br/>' . get_text('Heigh', 'BackNumbers') . '</th>
	<th>' . get_text('CharColor', 'BackNumbers') . '
		<br/>' . get_text('BackColor', 'BackNumbers') . '</th>
	<th>' . get_text('BackCat', 'BackNumbers') . '</th>
	<th>' . get_text('CharType', 'BackNumbers') . '</th>
	<th>' . get_text('CharSize', 'BackNumbers') . '</th>
	<th>' . get_text('Alignment', 'BackNumbers') . '</th>
	</tr>';

echo '<tbody id="IceElements"></tbody>';

// Inserts a new block
$Select='<option value=""></option>';
// Comp Logo Left
if(file_exists($CFG->DOCUMENT_PATH.($im="TV/Photos/{$_SESSION['TourCodeSafe']}-ToLeft.jpg"))) {
    $Select.='<option value="ToLeft">'.get_text('ToLeft', 'BackNumbers').'</option>';
}
// Comp Logo Right
if(file_exists($CFG->DOCUMENT_PATH.($im="TV/Photos/{$_SESSION['TourCodeSafe']}-ToRight.jpg"))) {
    $Select.='<option value="ToRight">'.get_text('ToRight', 'BackNumbers').'</option>';
}
// Comp Logo Bottom
if(file_exists($CFG->DOCUMENT_PATH.($im="TV/Photos/{$_SESSION['TourCodeSafe']}-ToBottom.jpg"))) {
    $Select.='<option value="ToBottom">'.get_text('ToBottom', 'BackNumbers').'</option>';
}
// Colored area
$Select.='<option value="ColoredArea">'.get_text('ColoredArea', 'BackNumbers').'</option>';
// Comp name
$Select.='<option value="CompName">'.get_text('CompName', 'BackNumbers').'</option>';
// Comp Details
$Select.='<option value="CompDetails">'.get_text('CompDetails', 'BackNumbers').'</option>';
if($CardType=='T') {
    // Team components
    $Select.='<option value="TeamComponents">'.get_text('TeamComponents').'</option>';
} else {
    // numero tessera
    $Select.='<option value="AthCode">'.get_text('AthCode', 'BackNumbers').'</option>';
    // numero tessera in barcode/Qrcode
    $Select.='<option value="AthBarCode">'.get_text('AthBarCode', 'BackNumbers').'</option>';
    // numero tessera in barcode/Qrcode
    $Select.='<option value="AthQrCode">'.get_text('AthQrCode', 'BackNumbers').'</option>';
    // Athlete
    $Select.='<option value="Athlete">'.get_text('Athlete', 'BackNumbers').'</option>';
}
// Picture
if($CardType=='A') {
    $Select.='<option value="Picture">'.get_text('Picture', 'BackNumbers').'</option>';
}
// Category
$Select.='<option value="Category">'.get_text('Category', 'BackNumbers').'</option>';
// Event
if(strstr('EITYZ', $CardType)) {
    $Select.='<option value="Event">'.get_text('Event', 'BackNumbers').'</option>';
    $Select.='<option value="Ranking">'.get_text('Ranking', 'BackNumbers').'</option>';
    $Select.='<option value="QRScore">'.get_text('QRScore', 'BackNumbers').'</option>';
}
// Diploma based on Ranking
if(strstr('YZ', $CardType)) {
    $Select.='<option value="FinalRanking">'.get_text('FinalRanking', 'BackNumbers').'</option>';
    $Select.='<option value="SubclassRanking">'.get_text('SubclassRanking', 'BackNumbers').'</option>';
    $Select.='<option value="PayoutAwarded">'.get_text('PayoutAwarded', 'BackNumbers').'</option>';
}
// Session
$Select.='<option value="Session">'.get_text('Session', 'BackNumbers').'</option>';
// Target
$Select.='<option value="Target">'.get_text('Target').'</option>';
// SessionTarget
$Select.='<option value="SessionTarget">'.get_text('SessionTarget', 'BackNumbers').'</option>';
// Club
$Select.='<option value="Club">'.get_text('Club', 'BackNumbers').'</option>';
$Select.='<option value="Club2">'.get_text('Club2', 'BackNumbers').'</option>';
$Select.='<option value="Club3">'.get_text('Club3', 'BackNumbers').'</option>';
// Flag
$Select.='<option value="Flag">'.get_text('Flag', 'BackNumbers').'</option>';
// Image
$Select.='<option value="Image">'.get_text('Image', 'BackNumbers').'</option>';
// ImageSvg
$Select.='<option value="ImageSvg">'.get_text('ImageSvg', 'BackNumbers').'</option>';
// RandomImage
$Select.='<option value="RandomImage">'.get_text('RandomImage', 'BackNumbers').'</option>';
// Line
$Select.='<option value="HLine">'.get_text('HLine', 'BackNumbers').'</option>';
// Target sequence
if($CardType=='I' or $CardType=='T') {
    $Select.='<option value="TgtSequence">'.get_text('TgtSequence', 'BackNumbers').'</option>';
}
// World Rankings
$Select.='<option value="WRank">'.get_text('WRank', 'BackNumbers').'</option>';
// WRankImage
$Select.='<option value="WRankImage">'.get_text('WRankImage', 'BackNumbers').'</option>';
//Extras
if(module_exists('ExtraAddOns') AND getModuleParameter("ExtraAddOns","AddOnsEnable","0") != "0") {
    $Select.='<option value="ExtraAddOns">'.get_text('ExtraAddOns', 'BackNumbers').'</option>';
    $Select.='<option value="ExtraAddOnsImage">'.get_text('ExtraAddOnsImage', 'BackNumbers').'</option>';
}
// Diritti di accesso
if($CardType=='A') {
    $Select.='<option value="Access">'.get_text('Access', 'BackNumbers').'</option>';
    $Select .= '<option value="AccessGraphics">' . get_text('AccessGraphics', 'BackNumbers') . '</option>';

    // Diritti di pappa/transport/hotel
    $Select.='<option value="Accomodation">'.get_text('Accomodation', 'BackNumbers').'</option>';
}

// Schedule
// $Select.='<option value="Schedule">'.get_text('Schedule', 'BackNumbers').'</option>';
// regole di partecipazioni

$q=safe_r_sql("select max(IceOrder)+1 as NewOrder from IdCardElements where IceTournament={$_SESSION['TourId']} and IceCardType='{$CardType}' and IceCardNumber={$CardNumber} and IceCardPage={$CardPage}");
$r=safe_fetch($q);

echo '<tr icetype="" iceorder="'.$r->NewOrder.'"><th>&nbsp;</th><th><input type="number" class="w-7ch" name="NewOrder" value="'.$r->NewOrder.'"></th>
            <th><select name="NewContent" onchange="getElementData(this)">' . $Select  . '</select></th>
            <td colspan="9"></td>
            </tr>';

// All the already inserted elements
echo '</table>';
echo '</div>';

include('Common/Templates/tail.php');



