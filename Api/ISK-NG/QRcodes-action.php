<?php
require_once(dirname(__FILE__).'/config.php');
require_once('Common/Lib/Fun_Modules.php');
require_once('Common/Lib/CommonLib.php');

$JSON=['error'=>1, 'msg'=>get_text('ErrGenericError', 'Errors')];

if(!CheckTourSession() or !hasFullACL(AclISKServer, 'iskManagement', AclReadWrite)) {
	JsonOut($JSON);
}

$Setup=[];
switch($_REQUEST['action']??'') {
	case 'update':
		switch($_SESSION['UseApi']??'') {
			case ISK_NG_LITE_CODE:
			case ISK_NG_PRO_CODE:
				break;
			case ISK_NG_LIVE_CODE:
				$Setup['action']='setup';
				$Setup['enableWIFIManagement']=intval($_REQUEST['enableWIFIManagement']??0);
				if($Setup['enableWIFIManagement']) {
					$Setup['WifiReconnectTO']=intval($_REQUEST['WifiReconnectTO']??10);
					$Setup['WifiSearch']=intval($_REQUEST['WifiSearch']??60);
					$Setup['WifiResetCounter']=intval($_REQUEST['WifiResetCounter']??5);
					$Setup['WifiDELETE']=intval($_REQUEST['WifiDELETE']??0);
					$Setup['WifiSSID']=[];
					$Setup['WifiPWD']=[];
					$Setup['WifiTgtF']=[];
					$Setup['WifiTgtT']=[];
					foreach($_REQUEST['WifiSSID']??[] as $k=>$v) {
						if($v=trim($v) and $pwd=trim($_REQUEST['WifiPWD'][$k]??'')) {
							$Setup['WifiSSID'][]=$v;
							$Setup['WifiPWD'][]=base64_encode($pwd);
							$Setup['WifiTgtF'][]=intval($_REQUEST['WifiTgtF'][$k]??0);
							$Setup['WifiTgtT'][]=intval($_REQUEST['WifiTgtT'][$k]??0);
						}
					}
				}

				$Setup['enableGPS']=intval($_REQUEST['enableGPS']??0);
				if($Setup['enableGPS']) {
					$Setup['gpsFrequency']=intval($_REQUEST['gpsFrequency']??60);
				}
                $Setup['spottingMode']=intval($_REQUEST['spottingMode']??0);
				$Setup['hideTotals']=intval($_REQUEST['hideTotals']??0);
				$Setup['askTotals']=intval($_REQUEST['askTotals']??0);
                $Setup['enableHaptics']=intval($_REQUEST['enableHaptics']??0);
				$Setup['askSignature']=intval($_REQUEST['askSignature']??0);
                $Setup['regexSignature']=$_REQUEST['regexSignature']??'';
                $Setup['deviceSignatureCode']=$_REQUEST['deviceSignatureCode']??'';
                $Setup['takeScorecardPhoto']=intval($_REQUEST['takeScorecardPhoto']??0);
                if(preg_match("/^[0-9]{4}$/",$_REQUEST['settingsPinCode']??'')) {
                    $Setup['settingsPinCode']=$_REQUEST['settingsPinCode'];
                } else {
                    $Setup['settingsPinCode']='';
                }
				break;
			default:
				JsonOut($JSON);
		}
		setModuleParameter('ISK-NG', 'QRCode-Setup', $Setup);
		$JSON['error']=0;
		break;
    case 'getSignature':
        //  check if there are specific accreditations
        $ExtraSql='';
        $ExtraField='IFNULL(zextra.EdExtra,EnCode) ';
        if($Specific=getModuleParameterLike('Accreditation', 'Matches-A-%', $_SESSION['TourId'])) {
            $f=array();
            foreach($Specific as $SpecType=>$SpecCats) {
                $tmp=explode('-', $SpecType);
                $q=safe_r_SQL("SELECT IceCardNumber FROM `IdCardElements` WHERE `IceTournament` = {$_SESSION['TourId']} AND IceType='AthQrCode' and IceCardType='A' AND IceCardNumber=".end($tmp));
                if(safe_num_rows($q)>0) {
                    $f[]="(IceCardNumber=".end($tmp)." and find_in_set(concat(EnDivision,EnClass), '$SpecCats'))";
                }
            }
            if(count($f)>0) {
                $ExtraSql = "left join IdCardElements on IceTournament=EnTournament and IceType='AthQrCode' and IceCardType='A' and (" . implode(' or ', $f) . ") ";
                $ExtraField = 'coalesce(IceContent, Bibs.EdExtra, EnCode)';
            }
        }
        if($ExtraSql=='') {
            $q=safe_r_SQL("SELECT IceCardNumber, IceOrder, IceCardPage  FROM `IdCardElements` WHERE `IceTournament` = {$_SESSION['TourId']} AND IceType='AthQrCode' and IceCardType='A' ORDER BY IceCardNumber, IceOrder, IceCardPage");
            if($f=safe_fetch($q)) {
                $ExtraSql="left join IdCardElements on IceTournament=EnTournament and IceType='AthQrCode' and IceCardType='A' and IceCardNumber=$f->IceCardNumber AND IceOrder=$f->IceOrder AND IceCardPage=$f->IceCardPage ";
                $ExtraField='coalesce(IceContent, Bibs.EdExtra, EnCode)';
            }
        }

        $q=safe_r_sql("select IFNULL(Bibs.EdExtra,EnCode) as BibCode, CONCAT(EnName, ' ', EnFirstName, ' (', CoCode, ')') as Name, $ExtraField as QrCode, EnCode, CoCode, EnDivision, EnClass, ToCode
            from Qualifications 
            inner join Entries on EnId=QuId
            INNER JOIN `Countries` ON EnCountry=CoId AND EnTournament=CoTournament
            INNER JOIN `Tournament` ON ToId=EnTournament
            left join ExtraData Bibs on Bibs.EdId=EnId and Bibs.EdType='Z' and Bibs.EdExtra!=''
            $ExtraSql
            where EnTournament={$_SESSION['TourId']} and EnAthlete=1 ". (!empty($_REQUEST['code']) ? " and (EnCode=".StrSafe_DB($_REQUEST['code']) ." OR Bibs.EdExtra=".StrSafe_DB($_REQUEST['code']) .") ": "") ."
            order by rand() limit 1");

        if($r=safe_fetch($q)) {
            $replacements = array(
                '{ENCODE}' => $r->BibCode,
                '{COUNTRY}' => $r->CoCode,
                '{DIVISION}' => $r->EnDivision,
                '{CLASS}' => $r->EnClass,
                '{TOURNAMENT}' => $r->ToCode,
            );
            $JSON['data'] = array('BibCode' => $r->BibCode, 'Name' => $r->Name, 'QrCode' => str_replace(array_keys($replacements), array_values($replacements), $r->QrCode), 'DeviceQrCode' => '');
            $Items = getModuleParameter('ISK-NG', 'QRCode-Setup', [], $_SESSION['TourId']);
            if (array_key_exists('deviceSignatureCode', $Items) and !empty($Items["deviceSignatureCode"])) {
                $JSON['data']['DeviceQrCode'] = str_replace(array_keys($replacements), array_values($replacements), $Items["deviceSignatureCode"]);
            } else {
                $JSON['data']['DeviceQrCode'] = $JSON['data']['QrCode'];
            }
        }
        break;
	default:
		JsonOut($JSON);
}
$JSON['error']=0;
JsonOut($JSON);
