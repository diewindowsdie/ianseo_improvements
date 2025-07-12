<?php
$JSON=array('error' => 1, 'reload' => false);

require_once('./IdCardEdit-config.php');
require_once('Common/Lib/CommonLib.php');
require_once('Common/Lib/Fun_DateTime.inc.php');


if(empty($_REQUEST['act'])) {
    JsonOut($JSON);
}

switch($_REQUEST['act']) {
    case 'update':
        switch($_REQUEST['fld']??'') {
            case 'BadgePages':
                $Pages=max(1, min(2, intval($_REQUEST['val']??1)));
                safe_w_sql("update IdCards set IcPage=$Pages where IcTournament={$_SESSION['TourId']} and IcType='$CardType' and IcNumber=$CardNumber");
                $JSON['error'] = 0;
                $JSON['loadPicture']=$Pages;
                break;
            case 'IdCardsSettings-Width':
            case 'IdCardsSettings-Height':
            case 'IdCardsSettings-PaperWidth':
            case 'IdCardsSettings-PaperHeight':
            case 'IdCardsSettings-OffsetX':
            case 'IdCardsSettings-OffsetY':
            case 'IdCardsSettings-IdBgX':
            case 'IdCardsSettings-IdBgY':
            case 'IdCardsSettings-IdBgH':
            case 'IdCardsSettings-IdBgW':
                $q=safe_r_sql("select IcSettings from IdCards where IcTournament={$_SESSION['TourId']} and IcType='$CardType' and IcNumber=$CardNumber");
                if($r=safe_fetch($q)) {
                    if($r->IcSettings) {
                        $Settings=unserialize($r->IcSettings);
                    } else {
                        $Settings=[];
                    }
                    list(,$Item)=explode('-', $_REQUEST['fld']);
                    $Settings[$Item]=$_REQUEST['val'];
                    $sql="IcTournament={$_SESSION['TourId']}, IcType='$CardType', IcNumber=$CardNumber, IcSettings=".StrSafe_DB(serialize($Settings));
                    safe_w_sql("INSERT INTO IdCards set $sql on duplicate key update $sql");
                    $JSON['error'] = 0;
                    $JSON['reloadPictures'] = 1;
                }
                break;
            default:
                jsonout($JSON);
        }
        break;
    case 'deleteElement':
        $Type=($_REQUEST['type']??'');
        $Order=intval($_REQUEST['order']??0);
        $CardFile="{$CardType}-{$CardNumber}-{$CardPage}-{$Order}";
        safe_w_sql("delete from IdCardElements where $IceFilter and IceOrder={$Order} and IceType=".StrSafe_DB($Type));
        if(file_exists($File=$CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-Image-'.$CardFile.".jpg")) {
            unlink($File);
        }
        if(file_exists($File=$CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-RandomImage-'.$CardFile.".jpg")) {
            unlink($File);
        }
        $JSON['error'] = 0;
        break;
    case 'getElements':
        $JSON['table']='';
        $NewOrder=0;
        $SQL="select * from IdCardElements where {$IceFilter} order by IceOrder";
        $q=safe_r_sql($SQL);
        while($r=safe_fetch($q)) {
            $JSON['table'].= getFieldPos($r);
            $NewOrder=$r->IceOrder;
        }


        $JSON['error']=0;
        break;
    case 'getElementData':
        $r=(object) [
            'IceOrder'=>intval($_REQUEST['order']??999),
            'IceType'=>$_REQUEST['type'],
            'IceOptions'=>'',
            'IceContent'=>'',
//            ''=>'',
        ];
        $JSON['content']= getFieldPos($r, true);
        $JSON['title']= get_text($r->IceType, 'BackNumbers');
        $JSON['error']=0;
        break;
    case 'updateElement':
        $Order=intval($_REQUEST['order']??999);
        if($_REQUEST['fldname']!='Order') {
            $IceFilter.=" and IceType=".StrSafe_DB($_REQUEST['type'])." and IceOrder={$Order}";
        }
        switch($_REQUEST['fldname']) {
            case 'Text':
            case 'Event':
            case 'Ranking':
            case 'FinalRanking':
            case 'SubclassRanking':
            case 'PayoutAwarded':
            case 'WRank':
            case 'ExtraAddOns':
            case 'Category':
            case 'Athlete':
            case 'Club':
            case 'Club2':
            case 'Club3':
            case 'AthQrCode':
            case 'AthBarCode':
            case 'TgtSequence':
                safe_w_sql("update IdCardElements set IceContent=".StrSafe_DB($_REQUEST[$_REQUEST['fldname']]??'')." where $IceFilter");
                break;
            case 'Image':
                if(!isset($_FILES['Image'])) {
                    jsonout($JSON);
                }
                unset($img);
                $file=$_FILES['Image'];
                $CardFile="{$CardType}-{$CardNumber}-{$CardPage}-".intval($_REQUEST['order']);
                switch ($file['type']) {
                    case 'image/png':
                        $img = imagecreatefrompng($file['tmp_name']);
                        break;
                    case 'image/jpeg':
                        $img = imagecreatefromjpeg($file['tmp_name']);
                        break;
                }
                if (!empty($img)) {
                    define('MAX_PHOTO_PIXEL', 3000);
                    $tmpfile = $CFG->DOCUMENT_PATH . 'TV/Photos/' . $_SESSION['TourCodeSafe'] . '-' . $_REQUEST['type'] . '-' . $CardFile . '.jpg';
                    $srcW = imagesx($img);
                    $srcH = imagesy($img);
                    if ($srcW > MAX_PHOTO_PIXEL or $srcH > MAX_PHOTO_PIXEL) {
                        // max dimension is a square of 2000 pixel!
                        $ratio = 1;
                        if ($srcW > MAX_PHOTO_PIXEL) $ratio = MAX_PHOTO_PIXEL / $srcW;
                        if ($srcH > MAX_PHOTO_PIXEL) $ratio = min($ratio, MAX_PHOTO_PIXEL / $srcH);
                        $dstW = intval($srcW * $ratio);
                        $dstH = intval($srcH * $ratio);
                        $im2 = imagecreatetruecolor($dstW, $dstH);
                        imagecopyresampled($im2, $img, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
                        imagejpeg($im2, $tmpfile, 85);
                    } else {
                        imagejpeg($img, $tmpfile, 85);
                    }
                    $Content = file_get_contents($tmpfile);
                    safe_w_sql("update IdCardElements set IceContent=".StrSafe_DB($Content)." where $IceFilter");
                    $JSON['error'] = 0;
                    $JSON['reloadItem'] = 1;
                }
                break;
            case 'Order':
                // changes the order...
                $OldOrder=intval($_REQUEST['order']);
                $NewOrder=intval($_REQUEST['Order']);
                // check if there is already an element with that order
                $q=safe_r_sql("select IceOrder from IdCardElements where $IceFilter and IceOrder=$NewOrder");
                if(safe_num_rows($q)) {
                    // reset the new order flag for this card
                    safe_w_sql("update IdCardElements set IceNewOrder=0 where $IceFilter");
                    safe_w_sql("update IdCardElements set IceNewOrder=$NewOrder where $IceFilter and IceOrder=$OldOrder");
                    if($OldOrder<$NewOrder) {
                        // moves down one place all the elements between old and new
                        safe_w_sql("update IdCardElements set IceOrder=IceOrder-1 where $IceFilter and IceOrder > $OldOrder and IceOrder<=$NewOrder");
                    } else {
                        safe_w_sql("update IdCardElements set IceOrder=IceOrder+1 where $IceFilter and IceOrder >= $NewOrder and IceOrder<$OldOrder");
                    }
                    safe_w_sql("update IdCardElements set IceOrder=IceNewOrder where $IceFilter and IceNewOrder=$NewOrder");
                } else {
                    safe_w_sql("update IdCardElements set IceOrder=$NewOrder where $IceFilter and IceOrder=$OldOrder");
                }
                // delete and rebuilds all the images involved...
                foreach(glob("{$CFG->DOCUMENT_PATH}TV/Photos/{$_SESSION['TourCodeSafe']}-*-{$CardType}-{$CardNumber}-{$CardPage}-*") as $File) {
                    unlink($File);
                }
                $q=safe_r_sql("select IceContent, IceCardPage, IceType, IceOrder, IceCardType, IceCardNumber 
                    from IdCardElements 
                    where IceContent>'' 
                        and IceTournament = {$_SESSION['TourId']} 
                        and IceCardType = '$CardType' 
                        and IceCardNumber = $CardNumber 
                        and IceCardPage = $CardPage 
                        and IceType in ('Image', 'ImageSvg', 'RandomImage', 'WRankImage', 'ExtraAddOnsImage')");
                while($r=safe_fetch($q)) {
                    if($r->IceType=='ImageSvg') {
                        $ImName=$CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-'.$r->IceType.'-'.$r->IceCardType.'-'.$r->IceCardNumber.'-'.$r->IceCardPage.'-'.$r->IceOrder.'.svg';
                        if($im=@gzinflate($r->IceContent)) {
                            file_put_contents($ImName, $im);
                        } else {
                            if(file_exists($ImName)) {
                                @unlink($ImName);
                            }
                        }
                    } else {
                        $ImName=$CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-'.$r->IceType.'-'.$r->IceCardType.'-'.$r->IceCardNumber.'-'.$r->IceCardPage.'-'.$r->IceOrder.'.jpg';
                        if($im=@imagecreatefromstring($r->IceContent)) {
                            imagejpeg($im, $ImName, 90);
                        } else {
                            if(file_exists($ImName)) {
                                @unlink($ImName);
                            }
                        }
                    }
                }

                $JSON['error']=0;
                $JSON['reload']=true;
                break;
            default:
                if(!isset($_REQUEST['Options'])) {
                    jsonout($JSON);
                }
                $q=safe_r_sql("select * from IdCardElements where $IceFilter");
                $r=safe_fetch($q);
                $Options=[];
                if($r->IceOptions) {
                    $Options=unserialize($r->IceOptions);
                }
                foreach($_REQUEST['Options'] as $Key=>$Value) {
                    $Options[$Key]=$Value;
                }
                safe_w_sql("update IdCardElements set IceOptions=".StrSafe_DB(serialize($Options))." where $IceFilter");
                break;
        }
        $JSON['error']=0;
        break;
    case 'saveNewElement':
        $Options=array(
            'X' =>0,
            'Y' =>0,
            'W' =>0,
            'H' =>0,
            'Font'=>'arialbd',
            'Col'=>'#000000',
            'BackCol'=>'',
            'BackCat'=>'',
            'Size'=>12,
            'Just'=>0,
        );
        if($_REQUEST['Type']=='TgtSequence') {
            $Options['LayoutOrientation']=0;
            $Options['FromPhase']=-1;
            $Options['ToPhase']=-1;
        }
        foreach($Options as $k=>$v) {
            if(isset($_REQUEST['Options'][$k])) {
                $Options[$k]=$_REQUEST['Options'][$k];
            }
        }
        $Content=($_REQUEST[$_REQUEST['Type']]??$_REQUEST['Text']??'');
        $MimeType='';
        if(isset($_FILES[$_REQUEST['Type']]) or isset($_FILES['Image'])) {
            unset($img);
            $CardFile="{$CardType}-{$CardNumber}-{$CardPage}-".intval($_REQUEST['Order']);
            $file=$_FILES[$_REQUEST['Type']] ?? $_FILES['Image'];
            if($_REQUEST['Type']=='ImageSvg') {
                $img = file_get_contents($file['tmp_name']);
                if (!empty($img)) {
                    $tmpfile = $CFG->DOCUMENT_PATH . 'TV/Photos/' . $_SESSION['TourCodeSafe'] . '-' . $_REQUEST['Type'] . '-' . $CardFile . '.svg';
                    file_put_contents($tmpfile, $img);
                    $Content= gzdeflate($img);
                }
            } else {
                switch ($file['type']) {
                    case 'image/png':
                        $img = imagecreatefrompng($file['tmp_name']);
                        break;
                    case 'image/jpeg':
                        $img = imagecreatefromjpeg($file['tmp_name']);
                        break;
                }
                if (!empty($img)) {
                    define('MAX_PHOTO_PIXEL', 3000);
                    $tmpfile = $CFG->DOCUMENT_PATH . 'TV/Photos/' . $_SESSION['TourCodeSafe'] . '-' . $_REQUEST['Type'] . '-' . $CardFile . '.jpg';
                    $srcW = imagesx($img);
                    $srcH = imagesy($img);
                    if ($srcW > MAX_PHOTO_PIXEL or $srcH > MAX_PHOTO_PIXEL) {
                        // max dimension is a square of 2000 pixel!
                        $ratio = 1;
                        if ($srcW > MAX_PHOTO_PIXEL) $ratio = MAX_PHOTO_PIXEL / $srcW;
                        if ($srcH > MAX_PHOTO_PIXEL) $ratio = min($ratio, MAX_PHOTO_PIXEL / $srcH);
                        $dstW = intval($srcW * $ratio);
                        $dstH = intval($srcH * $ratio);
                        $im2 = imagecreatetruecolor($dstW, $dstH);
                        imagecopyresampled($im2, $img, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
                        imagejpeg($im2, $tmpfile, 85);
                    } else {
                        imagejpeg($img, $tmpfile, 85);
                    }
                    $Content = file_get_contents($tmpfile);
                    if($_REQUEST['Type']=='RandomImage') {
                        // check previous options and "fix" them
                        $q=safe_r_sql("select IceOptions from IdCardElements where $IceFilter and IceType='RandomImage'");
                        if($r=safe_fetch($q) and $r->IceOptions) {
                            $Options=unserialize($r->IceOptions);
                        }
                    }
                }
            }
        }
        // gets the actual max order
        $q=safe_r_sql("select max(IceOrder)+1 as NewOrder from IdCardElements where $IceFilter");
        $r=safe_fetch($q);
        $MaxOrder=$r->NewOrder;

        $Order=intval($_REQUEST['Order']);
        // check if there is already an element with same order
        $q=safe_r_sql("select IceOrder from IdCardElements where $IceFilter and IceOrder=$Order");
        if(safe_num_rows($q)) {
            $Order=$MaxOrder;
            $MaxOrder++;
        }
        if($_REQUEST['Type']=='ExtraAddOnsImage') {
            // we have extraoptions here!
            if(!empty($_REQUEST['MoreOptions'])) {
                $Options['ExtraAddOns']=$_REQUEST['MoreOptions'];
            }
        }
        $Sql="insert into IdCardElements set 
            IceTournament={$_SESSION['TourId']},
            IceOrder=$Order,
            IceType=".StrSafe_DB($_REQUEST['Type']).",
            IceContent=".StrSafe_DB($Content).",
            IceMimeType='$MimeType',
            IceOptions=".StrSafe_DB(serialize($Options)).",
            IceCardNumber=$CardNumber,
            IceCardType='$CardType',
            IceCardPage=$CardPage";
        safe_w_sql($Sql);
        $JSON['error']=0;
        $q=safe_r_sql("select * from IdCardElements where $IceFilter and IceOrder=$Order");
        $JSON['NewRow']=getFieldPos(safe_fetch($q));
        $JSON['NewOrder']=$MaxOrder;
        break;
    case 'uploadBackground':
        if(empty($_FILES['UploadedBgImage']['size'])) {
            JsonOut($JSON);
        }
        unset($img);
        switch($_FILES['UploadedBgImage']['type']) {
            case 'image/png':
                $img=imagecreatefrompng($_FILES['UploadedBgImage']['tmp_name']);
            case 'image/jpeg':
                if(!isset($img)) $img=imagecreatefromjpeg($_FILES['UploadedBgImage']['tmp_name']);
                break;
        }
        if(!empty($img)) {
            define('MAX_PHOTO_PIXEL', 3000);
            $CardFile="{$CardType}-{$CardNumber}";
            $tmpfile=$CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-'.$CardFile.'-Accreditation.jpg';
            $srcW=imagesx($img);
            $srcH=imagesy($img);
            if($srcW>MAX_PHOTO_PIXEL or $srcH>MAX_PHOTO_PIXEL) {
                // max dimension is a square of 2000 pixel!
                $ratio=1;
                if($srcW>MAX_PHOTO_PIXEL) $ratio=MAX_PHOTO_PIXEL/$srcW;
                if($srcH>MAX_PHOTO_PIXEL) $ratio=min($ratio, MAX_PHOTO_PIXEL/$srcH);
                $dstW=intval($srcW*$ratio);
                $dstH=intval($srcH*$ratio);
                $im2=imagecreatetruecolor($dstW, $dstH);
                imagecopyresampled($im2, $img, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
                imagejpeg($im2, $tmpfile, 85);
            } else {
                imagejpeg($img, $tmpfile, 85);
            }

            $SQL="IcTournament={$_SESSION['TourId']}, IcType='$CardType', IcNumber=$CardNumber, IcBackground=".StrSafe_DB(file_get_contents($tmpfile));
            $q=safe_r_sql("select IcSettings from IdCards where IcTournament={$_SESSION['TourId']} and IcType='$CardType' and IcNumber=$CardNumber");
            if($r=safe_fetch($q) and $r->IcSettings) {
                $r->IcSettings=unserialize($r->IcSettings);
                if(empty($r->IcSettings['IdBgH'])) {
                    $r->IcSettings['IdBgH']=$r->IcSettings['Height'];
                    $r->IcSettings['IdBgW']=$r->IcSettings['Width'];
                    $SQL.=", IcSettings=".StrSafe_DB(serialize($r->IcSettings));
                }
                $JSON['settings']=[
                    'IdBgX'=>$r->IcSettings['IdBgX'],
                    'IdBgY'=>$r->IcSettings['IdBgY'],
                    'IdBgH'=>$r->IcSettings['IdBgH'],
                    'IdBgW'=>$r->IcSettings['IdBgW'],
                ];
            }
            safe_w_sql("INSERT INTO IdCards set $SQL on duplicate key update $SQL");
            $JSON['error']=0;
            $JSON['src']=$CFG->ROOT_DIR.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-'.$CardFile.'-Accreditation.jpg';
        }

        break;
    case 'deleteBackground':
        $CardFile="{$CardType}-{$CardNumber}";
        $SQL="IcBackground=''";
        $q=safe_r_sql("select IcSettings from IdCards where IcTournament={$_SESSION['TourId']} and IcType='$CardType' and IcNumber=$CardNumber");
        if($r=safe_fetch($q) and $r->IcSettings) {
            $r->IcSettings=unserialize($r->IcSettings);
            $r->IcSettings['IdBgH']=0;
            $r->IcSettings['IdBgW']=0;
            $r->IcSettings['IdBgX']=0;
            $r->IcSettings['IdBgY']=0;
            $SQL.=", IcSettings=".StrSafe_DB(serialize($r->IcSettings));
        }

        safe_w_sql("update IdCards set $SQL where IcTournament={$_SESSION['TourId']} and IcType='$CardType' and IcNumber=$CardNumber");
        unlink($CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-'.$CardFile.'-Accreditation.jpg');
        $JSON['error']=0;
        break;
    default:
        jsonout($JSON);
}

jsonout($JSON);


function getFieldPos($r, $new=false) {
    global $CFG, $CardType, $CardNumber, $CardFile, $CardPage;
    static $Fonts='';
    if(empty($Fonts)) {
        foreach(getFonts() as $file => $font) {
            $Fonts.='<option value="'.$file.'">'.$font['name'].'</option>';
        }
    }
    $CardFile="{$CardType}-{$CardNumber}-{$CardPage}";
    $Objects=['th'=>[],'td'=>[]];
    if($r->IceOptions) {
        $Options=unserialize($r->IceOptions);
    } else {
        $Options=array(
            'X' =>0,
            'Y' =>0,
            'W' =>0,
            'H' =>0,
            'Font'=>'arialbd',
            'Col'=>'#000000',
            'BackCol'=>'',
            'BackCat'=>'',
            'Size'=>12,
            'Just'=>0,
        );
    }

    if(!$new) {
        $Objects['th'][]=[
            'cell'=>['<i class="fa fa-lg fa-trash-alt text-danger mx-2" onclick="deleteItem(this)"></i>'],
            'span'=>0,
            'name'=>[''],
        ];
    }
    $Objects['th'][]=[
        'span'=>0,
        'name'=>[get_text('Progr')],
        'cell'=>['<input type="hidden" name="Type" value="'.$r->IceType.'"><input type="number" class="w-7ch" onchange="UpdateRowContent(this)" name="Order" value="'.$r->IceOrder.'">']];
    if(!$new) {
        $Objects['th'][]=[
            'span'=>0,
            'name'=>[get_text('Content', 'BackNumbers')],
            'cell'=>[get_text($r->IceType, 'BackNumbers')]];
    }

    switch($r->IceType) {
        case 'ToLeft':
            $im="TV/Photos/{$_SESSION['TourCodeSafe']}-ToLeft.jpg";
        case 'ToRight':
            if(!isset($im)) $im="TV/Photos/{$_SESSION['TourCodeSafe']}-ToRight.jpg";
        case 'ToBottom':
            if(!isset($im)) $im="TV/Photos/{$_SESSION['TourCodeSafe']}-ToBottom.jpg";
        case 'Picture':
            if(!isset($im)) $im="Common/Images/Photo.gif";
        case 'Flag':
            if(!isset($im)) $im='Common/Images/Flag.jpg';

            $Objects['td'][]=[
                'span'=>2,
                'name'=>[get_text('BadgeOptions','Tournament')],
                'cell'=>['<img src="'.$CFG->ROOT_DIR.$im.'" height="50">']];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('PosX', 'BackNumbers'),get_text('PosY', 'BackNumbers')],
                'cell'=>['<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[X]" value="'.$Options['X'].'">','<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[Y]" value="'.$Options['Y'].'">']];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('Width', 'BackNumbers'),get_text('Heigh', 'BackNumbers'),],
                'cell'=>['<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[W]" value="'.$Options['W'].'">','<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[H]" value="'.$Options['H'].'">',]];
            break;

        case 'ColoredArea':
            $txt='<textarea rows="5" cols="40" onchange="UpdateRowContent(this)" name="Text">'.$r->IceContent.'</textarea>';
        case 'AccessGraphics':
            if(!isset($txt)) $txt= '<img src="'.$CFG->ROOT_DIR.'Common/Images/AccessCodes.png" height="50">';
        case 'CompName':
            if(!isset($txt)) $txt= $_SESSION['TourName'];
        case 'CompDetails':
            if(!isset($txt)) $txt=$_SESSION['TourWhere'].' - '.TournamentDate2StringShort($_SESSION['TourRealWhenFrom'], $_SESSION['TourRealWhenTo']);
        case 'AthCode':
            if(!isset($txt)) $txt='Archer Code';
        case 'TeamComponents':
            if(!isset($txt)) {
                $txt='<select onchange="UpdateRowContent(this)" name="TeamComponents">
					<option value="OneLine"'   .($r->IceContent=='OneLine'   ?' selected':'').'>'.get_text('OneLine',    'BackNumbers').'</option>
					<option value="MultiLine"'  .($r->IceContent=='MultiLine'  ?' selected':'').'>'.get_text('MultiLine',   'BackNumbers').'</option>
					</select>';
            }
        case 'TgtSequence':
            if(!isset($txt)) {
                require_once('Common/Lib/Fun_Phases.inc.php');
                $tmpPhases = getStandardPhases();
                if(!isset($Options['FromPhase'])) $Options['FromPhase']=$tmpPhases[0];
                if(!isset($Options['ToPhase'])) $Options['ToPhase']=0;
                if(!isset($Options['LayoutOrientation'])) $Options['LayoutOrientation']=0;

                $txt='<select onchange="UpdateRowContent(this)" name="TgtSequence">
					<option value="BlackWhite"'  .($r->IceContent=='BlackWhite'  ?' selected':'').'>'.get_text('BlackWhite',   'BackNumbers').'</option>
					<option value="Coloured"'   .($r->IceContent=='Coloured'   ?' selected':'').'>'.get_text('Coloured',    'BackNumbers').'</option>
					</select>';
                if(!isset($imInput)) {
                    $imInput = get_text('PhaseFrom', 'BackNumbers') .
                        '&nbsp;<select onchange="UpdateRowContent(this)" name="Options[FromPhase]">';
                    foreach ($tmpPhases as $vPh) {
                        $imInput .= '<option value="' . $vPh . '"' . ($Options['FromPhase'] == $vPh ? ' selected' : '') . '>' . get_text($vPh . '_Phase') . '</option>';
                    }
                    $imInput .= '</select><br>' . get_text('PhaseTo', 'BackNumbers') . '&nbsp;<select onchange="UpdateRowContent(this)" name="Options[ToPhase]">';
                    $tmpPhases = array_reverse($tmpPhases);
                    foreach ($tmpPhases as $vPh) {
                        $imInput .= '<option value="' . $vPh . '"' . ($Options['ToPhase'] == $vPh ? ' selected' : '') . '>' . get_text($vPh . '_Phase') . '</option>';
                    }
                    $imInput .= '</select><br><br>' . get_text('IdLayout', 'BackNumbers') . '&nbsp;<select onchange="UpdateRowContent(this)" name="Options[LayoutOrientation]">'.
                        '<option value="0"' . ($Options['LayoutOrientation'] == "0" ? ' selected' : '') . '>'.get_text('HLayout', 'BackNumbers').'</option>'.
                        '<option value="1"' . ($Options['LayoutOrientation'] == "1" ? ' selected' : '') . '>'.get_text('VLayout', 'BackNumbers').'</option>'.
                        '</select>';
                }
            }
        case 'Access':
            if(!isset($txt)) $txt='0/9*';
        case 'Session':
            if(!isset($txt)) $txt=get_text('Session');
        case 'Target':
            if(!isset($txt)) $txt=get_text('Target');
        case 'SessionTarget':
            if(!isset($txt)) $txt=get_text('SessionTarget','BackNumbers');
        case 'Event':
            if(!isset($txt)) {
                $txt='<select onchange="UpdateRowContent(this)" name="Event">
					<option value="">--</option>
					<option value="EvCode"'   .($r->IceContent=='EvCode'   ?' selected':'').'>'.get_text('EvCode',    'BackNumbers').'</option>
					<option value="EvCode-EvDescr"'  .($r->IceContent=='EvCode-EvDescr'  ?' selected':'').'>'.get_text('EvCode-EvDescr',   'BackNumbers').'</option>
					<option value="EvDescr"'  .($r->IceContent=='EvDescr'  ?' selected':'').'>'.get_text('EvDescr',   'BackNumbers').'</option>
					</select>';
                if(!$r->IceOptions) $Options['BackCat']=1;
            }
        case 'QRScore':
            if(!isset($txt)) $txt=get_text('Score','Tournament');
        case 'PayoutAwarded':
            if(!isset($txt)) {
                $txt='<select onchange="UpdateRowContent(this)" name="PayoutAwarded">
                    <option value="Numbers"'   .($r->IceContent=='Numbers'   ?' selected':'').'>'.get_text('PayoutNumbers',    'BackNumbers').'</option>
					<option value="Letters"'   .($r->IceContent=='Letters'   ?' selected':'').'>'.get_text('PayoutLetters',    'BackNumbers').'</option>
					<option value="LettersWithDecimal"'   .($r->IceContent=='LettersWithDecimal'   ?' selected':'').'>'.get_text('PayoutLettersWithDecimal',    'BackNumbers').'</option>
                </select>';
            }
        case 'Ranking':
            if(!isset($txt)) {
                $txt='<select onchange="UpdateRowContent(this)" name="Ranking">
                    <option value="Cardinal"'   .($r->IceContent=='Cardinal'   ?' selected':'').'>'.get_text('RnkCardinalEN',    'BackNumbers').'</option>
                    <option value="Ordinal"'   .($r->IceContent=='Ordinal'   ?' selected':'').'>'.get_text('RnkOrdinal',    'BackNumbers').'</option>
					<option value="Roman"'   .($r->IceContent=='Roman'   ?' selected':'').'>'.get_text('RnkRoman',    'BackNumbers').'</option>
                </select>';
            }
        case 'FinalRanking':
            if(!isset($txt)) {
                $txt='<select onchange="UpdateRowContent(this)" name="FinalRanking">
                    <option value="Cardinal"'   .($r->IceContent=='Cardinal'   ?' selected':'').'>'.get_text('RnkCardinalEN',    'BackNumbers').'</option>
                    <option value="Ordinal"'   .($r->IceContent=='Ordinal'   ?' selected':'').'>'.get_text('RnkOrdinal',    'BackNumbers').'</option>
					<option value="Roman"'   .($r->IceContent=='Roman'   ?' selected':'').'>'.get_text('RnkRoman',    'BackNumbers').'</option>
                </select>';
            }
        case 'SubclassRanking':
            if(!isset($txt)) {
                $txt='<select onchange="UpdateRowContent(this)" name="SubclassRanking">
                    <option value="Cardinal"'   .($r->IceContent=='Cardinal'   ?' selected':'').'>'.get_text('RnkCardinalEN',    'BackNumbers').'</option>
                    <option value="Ordinal"'   .($r->IceContent=='Ordinal'   ?' selected':'').'>'.get_text('RnkOrdinal',    'BackNumbers').'</option>
					<option value="Roman"'   .($r->IceContent=='Roman'   ?' selected':'').'>'.get_text('RnkRoman',    'BackNumbers').'</option>
                </select>';
            }
        case 'WRank':
            if(!isset($txt)) {
                $txt=get_text('WRankFields', 'BackNumbers').'<div><input type="number" onchange="UpdateRowContent(this)" name="WRank" value="'.$r->IceContent.'"></div>';
            }
        case 'Category':
            if(!isset($txt)) {
                $txt='<select onchange="UpdateRowContent(this)" name="Category">
					<option value="">--</option>
					<option value="CatCode"'   .($r->IceContent=='CatCode'   ?' selected':'').'>'.get_text('EvCode',    'BackNumbers').'</option>
					<option value="CatCode-EvDescr"'  .($r->IceContent=='CatCode-EvDescr' ? ' selected':'').'>'.get_text('EvCode-EvDescr',   'BackNumbers').'</option>
					<option value="CatDescr"'  .($r->IceContent=='CatDescr'  ?' selected':'').'>'.get_text('EvDescr',   'BackNumbers').'</option>
					<option value="CatDescrUpper"' . ($r->IceContent == 'CatDescrUpper' ? ' selected' : '') . '>' . get_text('EvDescrUpper', 'BackNumbers') . '</option>
					<option value="EvSubCode"'   .($r->IceContent=='EvSubCode'   ?' selected':'').'>'.get_text('EvSubCode',    'BackNumbers').'</option>
					<option value="EvSubCode-EvSubDescr"'  .($r->IceContent=='EvSubCode-EvSubDescr'  ?' selected':'').'>'.get_text('EvSubCode-EvSubDescr',   'BackNumbers').'</option>
					<option value="EvSubDescr"'  .($r->IceContent=='EvSubDescr'  ?' selected':'').'>'.get_text('EvSubDescr',   'BackNumbers').'</option>
					</select>';
                if(!$r->IceOptions) $Options['BackCat']=1;
            }
        case 'ExtraAddOns':
            if(!isset($txt)) {
                $txt='<select onchange="UpdateRowContent(this)" name="ExtraAddOns">';
                $listAddOns = getModuleParameter("ExtraAddOns","AddOnsList", array());
                foreach ($listAddOns  as $kAo => $vAo) {
                    if(!empty($vAo)) {
                        $txt .= '<option value="' . $kAo . '"' . ($r->IceContent == $kAo ? ' selected' : '') . '>' . $vAo . '</option>';
                    }
                }
                $txt .='</select>';
            }
        case 'Athlete':
            if(!isset($txt)) {
                $txt='<select onchange="UpdateRowContent(this)" name="Athlete">
					<option value="">--</option>
					<option value="FamCaps"'   .($r->IceContent=='FamCaps'   ?' selected':'').'>'.get_text('FamCaps',    'BackNumbers').'</option>
					<option value="FamCaps-GAlone"'  .($r->IceContent=='FamCaps-GAlone'  ?' selected':'').'>'.get_text('FamCaps-GAlone',   'BackNumbers').'</option>
					<option value="FamCaps-GivCaps"'.($r->IceContent=='FamCaps-GivCaps'?' selected':'').'>'.get_text('FamCaps-GivCaps', 'BackNumbers').'</option>
					<option value="FamCaps-GivCaps-ClubCaps"'.($r->IceContent=='FamCaps-GivCaps-ClubCaps'?' selected':'').'>'.get_text('FamCaps-GivCaps-ClubCaps', 'BackNumbers').'</option>
					<option value="FamCaps-GivCamel"'.($r->IceContent=='FamCaps-GivCamel'?' selected':'').'>'.get_text('FamCaps-GivCamel', 'BackNumbers').'</option>
					<option value="FamCaps-GivCamel-ClubCamel"'.($r->IceContent=='FamCaps-GivCamel-ClubCamel'?' selected':'').'>'.get_text('FamCaps-GivCamel-ClubCamel', 'BackNumbers').'</option>
					<option value="FamCaps-GivCamel-ClubCaps"'.($r->IceContent=='FamCaps-GivCamel-ClubCaps'?' selected':'').'>'.get_text('FamCaps-GivCamel-ClubCaps', 'BackNumbers').'</option>
					<option value="FamCamel"'   .($r->IceContent=='FamCamel'   ?' selected':'').'>'.get_text('FamCamel',    'BackNumbers').'</option>
					<option value="FamCamel-GAlone"'  .($r->IceContent=='FamCamel-GAlone'  ?' selected':'').'>'.get_text('FamCamel-GAlone',   'BackNumbers').'</option>
					<option value="FamCamel-GivCamel"'.($r->IceContent=='FamCamel-GivCamel'?' selected':'').'>'.get_text('FamCamel-GivCamel', 'BackNumbers').'</option>
					<option value="GivCamel"'   .($r->IceContent=='GivCamel'   ?' selected':'').'>'.get_text('GivCamel',    'BackNumbers').'</option>
					<option value="GivCamel-FamCamel"'.($r->IceContent=='GivCamel-FamCamel'?' selected':'').'>'.get_text('GivCamel-FamCamel', 'BackNumbers').'</option>
					<option value="GivCamel-FamCamel-ClubCamel"'.($r->IceContent=='GivCamel-FamCamel-ClubCamel'?' selected':'').'>'.get_text('GivCamel-FamCamel-ClubCamel', 'BackNumbers').'</option>
					<option value="GivCamel-FamCaps"'.($r->IceContent=='GivCamel-FamCaps'?' selected':'').'>'.get_text('GivCamel-FamCaps', 'BackNumbers').'</option>
                    <option value="GivCamel-FamCaps-ClubCamel"'.($r->IceContent=='GivCamel-FamCaps-ClubCamel'?' selected':'').'>'.get_text('GivCamel-FamCaps-ClubCamel', 'BackNumbers').'</option>
                    <option value="GivCamel-FamCaps-ClubCaps"'.($r->IceContent=='GivCamel-FamCaps-ClubCaps'?' selected':'').'>'.get_text('GivCamel-FamCaps-ClubCaps', 'BackNumbers').'</option>
					<option value="GivCaps"'.($r->IceContent=='GivCaps'?' selected':'').'>'.get_text('GivCaps', 'BackNumbers').'</option>
					<option value="GivCaps-FamCaps"'.($r->IceContent=='GivCaps-FamCaps'?' selected':'').'>'.get_text('GivCaps-FamCaps', 'BackNumbers').'</option>
					<option value="GivCaps-FamCaps-ClubCaps"'.($r->IceContent=='GivCaps-FamCaps-ClubCaps'?' selected':'').'>'.get_text('GivCaps-FamCaps-ClubCaps', 'BackNumbers').'</option>
					<option value="GAlone-FamCaps"'  .($r->IceContent=='GAlone-FamCaps'  ?' selected':'').'>'.get_text('GAlone-FamCaps',   'BackNumbers').'</option>
					<option value="GAlone-FamCamel"'  .($r->IceContent=='GAlone-FamCamel'  ?' selected':'').'>'.get_text('GAlone-FamCamel',   'BackNumbers').'</option>
					</select>';
            }
        case 'Club':
        case 'Club2':
        case 'Club3':
            if(!isset($txt)) {
                $txt='<select onchange="UpdateRowContent(this)" name="'.$r->IceType.'">
					<option value="">--</option>
					<option value="NocCaps-ClubCamel"'.($r->IceContent=='NocCaps-ClubCamel'?' selected':'').'>'.get_text('NocCaps-ClubCamel','BackNumbers').'</option>
					<option value="NocCaps-ClubCaps"'.($r->IceContent=='NocCaps-ClubCaps'?' selected':'').'>'.get_text('NocCaps-ClubCaps','BackNumbers').'</option>
					<option value="NocCaps"'    .($r->IceContent=='NocCaps'    ?' selected':'').'>'.get_text('NocCaps',    'BackNumbers').'</option>
					<option value="ClubCamel"'   .($r->IceContent=='ClubCamel'   ?' selected':'').'>'.get_text('ClubCamel',   'BackNumbers').'</option>
					<option value="ClubCaps"'   .($r->IceContent=='ClubCaps'   ?' selected':'').'>'.get_text('ClubCaps',   'BackNumbers').'</option>
					</select>';
            }

            if(empty($imInput)) {
                $Objects['td'][]=[
                    'span'=>2,
                    'name'=>[get_text('BadgeOptions','Tournament')],
                    'cell'=>[$txt]];
            } else {
                $Objects['td'][]=[
                    'span'=>1,
                    'name'=>[get_text('BadgeOptions','Tournament')],
                    'cell'=>[$txt]];
                $Objects['td'][]=[
                    'span'=>1,
                    'name'=>[get_text('BadgeOptions','Tournament')],
                    'cell'=>[$imInput]];
            }
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('PosX', 'BackNumbers'),get_text('PosY', 'BackNumbers')],
                'cell'=>['<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[X]" value="'.$Options['X'].'">','<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[Y]" value="'.$Options['Y'].'">']];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('Width', 'BackNumbers'),get_text('Heigh', 'BackNumbers'),],
                'cell'=>['<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[W]" value="'.$Options['W'].'">','<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[H]" value="'.$Options['H'].'">',]];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('CharColor', 'BackNumbers'),get_text('BackColor', 'BackNumbers'),],
                'cell'=>['<input size="6" type="text" class="jscolor {hash:true,required:false} jscolor-active" name="Options[Col]" onchange="UpdateRowContent(this)" name="Options[Col]" value="' . $Options['Col'] . '">','<input size="6" type="text" class="jscolor {hash:true,required:false} jscolor-active" name="Options[BackCol]" onchange="UpdateRowContent(this)" name="Options[BackCol]" value="' . $Options['BackCol'] . '">',]];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('BackCat', 'BackNumbers'),],
                'cell'=>['<input type="checkbox" onchange="UpdateRowContent(this)" name="Options[BackCat]"'.(empty($Options['BackCat']) ? '' : ' checked="checked"').'>',]];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('CharType', 'BackNumbers'),],
                'cell'=>['<select onchange="UpdateRowContent(this)" name="Options[Font]">
				    '.preg_replace('/value="'.$Options['Font'].'(.ttf)*"/','$0 selected="selected"', $Fonts).'
					</select>',]];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('CharSize', 'BackNumbers'),],
                'cell'=>['<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[Size]" value="' . $Options['Size'] . '">',]];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('Alignment', 'BackNumbers'),],
                'cell'=>['<select onchange="UpdateRowContent(this)" name="Options[Just]">
					<option value="0"' . ($Options['Just'] == 0 ? ' selected' : '') . '>' . get_text('AlignL', 'BackNumbers') . '</option>
					<option value="1"' . ($Options['Just'] == 1 ? ' selected' : '') . '>' . get_text('AlignC', 'BackNumbers') . '</option>
					<option value="2"' . ($Options['Just'] == 2 ? ' selected' : '') . '>' . get_text('AlignR', 'BackNumbers') . '</option>
					</select>',]];
            break;
        case 'HLine':
            if(!isset($txt)) $txt=get_text('HLine', 'BackNumbers');
            $Objects['td'][]=[
                'span'=>2,
                'name'=>[get_text('BadgeOptions','Tournament')],
                'cell'=>[$txt]];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('PosX', 'BackNumbers'),get_text('PosY', 'BackNumbers')],
                'cell'=>['<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[X]" value="'.$Options['X'].'">','<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[Y]" value="'.$Options['Y'].'">']];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('Width', 'BackNumbers'),get_text('Heigh', 'BackNumbers'),],
                'cell'=>['<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[W]" value="'.$Options['W'].'">','<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[H]" value="'.$Options['H'].'">',]];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('CharColor', 'BackNumbers'),get_text('BackColor', 'BackNumbers'),],
                'cell'=>['<input size="6" type="text" class="jscolor {hash:true,required:false} jscolor-active" name="Options[Col]" onchange="UpdateRowContent(this)" name="Options[Col]" value="' . $Options['Col'] . '">','<input size="6" type="text" class="jscolor {hash:true,required:false} jscolor-active" name="Options[BackCol]" onchange="UpdateRowContent(this)" name="Options[BackCol]" value="' . $Options['BackCol'] . '">',]];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('BackCat', 'BackNumbers'),],
                'cell'=>['<input type="checkbox" onchange="UpdateRowContent(this)" name="Options[BackCat]"'.(empty($Options['BackCat']) ? '' : ' checked="checked"').'>',]];
            break;
        case 'AthBarCode':
            if(!isset($txt)) {
                $txt='<div '.($new?'':'style="float:Right;"').'><input type="text" onchange="UpdateRowContent(this)" name="AthBarCode" value="'.$r->IceContent.'" style="width:100%"><br/>'.get_text('BarCodeFields', 'BackNumbers').'</div>';
            }
            $im='Common/Images/edit-barcode.png';
        case 'AthQrCode':
            if(!isset($im)) $im='Common/Images/qrcode.jpg';
            if(!isset($txt)) {
                $txt='<div '.($new?'':'style="float:Right;"').'><input type="text" onchange="UpdateRowContent(this)" name="AthQrCode" value="'.$r->IceContent.'" style="width:100%"><br/>'.get_text('QrCodeFields', 'BackNumbers').'</div>';
            }
        case 'Accomodation':
            if(!isset($txt)) $txt="";
            if(!isset($im)) $im='Common/Images/Accomodations.png';
        case 'ImageSvg':
            if(!isset($txt)) $txt="";
            if(!isset($im)) {
                $im='';
                $imInput= '<input type="file" name="ImageSvg" onchange="UpdateRowContent(this)">';
                if(file_exists($CFG->DOCUMENT_PATH."TV/Photos/{$_SESSION['TourCodeSafe']}-{$r->IceType}-".$CardFile.'-'.$r->IceOrder.".svg")) {
                    $im="TV/Photos/{$_SESSION['TourCodeSafe']}-{$r->IceType}-".$CardFile.'-'.$r->IceOrder.".svg";
                } elseif($r->IceContent) {
                    if($r->IceContent) {
                        file_put_contents($CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-'.$r->IceType.'-'.$CardFile.'-'.$r->IceOrder.'.svg', gzinflate($r->IceContent));
                        $im="TV/Photos/{$_SESSION['TourCodeSafe']}-{$r->IceType}-".$CardFile.'-'.$r->IceOrder.".svg";
                    }
                }
            }
        case 'Image':
        case 'RandomImage':
        case 'WRankImage':
        case 'ExtraAddOnsImage':
            if(!isset($txt)) $txt="";
            if(!isset($im)) {
                $im='';
                if(file_exists($CFG->DOCUMENT_PATH."TV/Photos/{$_SESSION['TourCodeSafe']}-{$r->IceType}-".$CardFile.'-'.$r->IceOrder.".jpg")) {
                    $im="TV/Photos/{$_SESSION['TourCodeSafe']}-{$r->IceType}-".$CardFile.'-'.$r->IceOrder.".jpg";
                } elseif($r->IceContent) {
                    if($r->IceContent and $img=@imagecreatefromstring($r->IceContent)) {
                        imagejpeg($img, $CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-'.$r->IceType.'-'.$CardFile.'-'.$r->IceOrder.'.jpg', 90);
                        $im="TV/Photos/{$_SESSION['TourCodeSafe']}-{$r->IceType}-".$CardFile.'-'.$r->IceOrder.".jpg";
                    }
                }
            }

            if(empty($imInput)) {
                $imInput='&nbsp;';
                if(empty($txt)) {
                    $imInput = '<input type="file" name="Image" onchange="UpdateRowContent(this)">';
                }
                if($r->IceType == 'WRankImage') {
                    $imInput.='<div>'.get_text('WRankFields', 'BackNumbers').'</div><div><input type="number" onchange="UpdateRowContent(this)" name="Options[WRank]" value="'.($Options['WRank']??0).'"></div>';
                }
                if($r->IceType == 'ExtraAddOnsImage') {
                    $imInput.='<select onchange="UpdateRowContent(this)" name="Options[ExtraAddOns]">';
                    $listAddOns = getModuleParameter("ExtraAddOns","AddOnsList",  array());
                    foreach ($listAddOns  as $kAo => $vAo) {
                        if(!empty($vAo)) {
                            $imInput.='<option value="' . $kAo . '"' . (($Options['ExtraAddOns']??0) == $kAo ? ' selected' : '') . '>' . $vAo . '</option>';
                        }
                    }
                    $imInput.='</select>';
                }
            }
            $Objects['td'][]=[
                'span'=>$imInput?0:2,
                'name'=>[get_text('BadgeOptions','Tournament')],
                'cell'=>[(empty($im) ? '' : '<img src="'.$CFG->ROOT_DIR.$im.'" height="50">').$txt]];
            if($imInput) {
                $Objects['td'][]=[
                    'span'=>0,
                    'name'=>[get_text('BadgeOptions','Tournament')],
                    'cell'=>[$imInput]];
            }
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('PosX', 'BackNumbers'),get_text('PosY', 'BackNumbers')],
                'cell'=>['<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[X]" value="'.$Options['X'].'">','<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[Y]" value="'.$Options['Y'].'">']];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('Width', 'BackNumbers'),get_text('Heigh', 'BackNumbers'),],
                'cell'=>['<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[W]" value="'.$Options['W'].'">','<input size="3" type="text" onchange="UpdateRowContent(this)" name="Options[H]" value="'.$Options['H'].'">',]];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('CharColor', 'BackNumbers'),get_text('BackColor', 'BackNumbers'),],
                'cell'=>['<input size="6" type="text" class="jscolor {hash:true,required:false} jscolor-active" name="Options[Col]" onchange="UpdateRowContent(this)" name="Options[Col]" value="' . $Options['Col'] . '">','<input size="6" type="text" class="jscolor {hash:true,required:false} jscolor-active" name="Options[BackCol]" onchange="UpdateRowContent(this)" name="Options[BackCol]" value="' . $Options['BackCol'] . '">',]];
            $Objects['td'][]=[
                'span'=>0,
                'name'=>[get_text('BackCat', 'BackNumbers'),],
                'cell'=>['<input type="checkbox" onchange="UpdateRowContent(this)" name="Options[BackCat]"'.(empty($Options['BackCat']) ? '' : ' checked="checked"').'>',]];
            break;
        default:
    }

    if($new) {
        $ret='<form id="NewElementDetails"><table class="Tabella">';
        foreach($Objects['th'] as $cell) {
            foreach($cell['name'] as $k=>$v) {
                $ret.='<tr><th class="text-right">'.$v.'</th><td>'.$cell['cell'][$k].'</td></tr>';
            }
        }
        foreach($Objects['td'] as $cell) {
            foreach($cell['name'] as $k=>$v) {
                $ret.='<tr><th class="text-right">'.$v.'</th><td>'.$cell['cell'][$k].'</td></tr>';
            }
        }
        $ret.='</table></form>';
    } else {
        $ret='<tr icetype="'.$r->IceType.'" iceorder="'.$r->IceOrder.'">';
        foreach($Objects['th'] as $cell) {
            $ret.='<th' .($cell['span']>1 ? ' colspan="'.$cell['span'].'"' : ''). '><div>'.implode('</div><div>',$cell['cell']).'</div></th>';
        }
        $i=9;
        foreach($Objects['td'] as $cell) {
            $ret.='<td' .($cell['span']>1 ? ' colspan="'.$cell['span'].'"' : ''). '><div>'.implode('</div><div>',$cell['cell']).'</div></td>';
            $i--;
            if($cell['span']>1) {
                $i--;
            }
        }
        if($i) {
            $ret.=str_repeat('<td></td>', $i);
        }
        $ret. '</tr>';
    }

    return $ret;
}
