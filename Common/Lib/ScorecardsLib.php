<?php

require_once('Common/pdf/ScorePDF.inc.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Fun_Sessions.inc.php');

/**
 * @param $Session if 0 all sessions
 * @param $FromTgt first target
 * @param $ToTgt last target
 * @param array $Options one or more of the following toggles:<ul>
 *      <li><b>GetArcInfo</b> (bool): adds a line to get DoB and Email from archers</li>
 *      <li><b>noEmpty</b> (bool): do not print the empty scorecards</li>
 *      <li><b>PersonalScore</b> (bool): all selected distances on the same scorecard</li>
 *      <li><b>QRCode</b> (array of APIs): prints the ISK setup QrCodes</li>
 *      <li><b>ScoreBarcode</b> (bool): prints the entry's barcode for results verification</li>
 *      <li><b>ScoreDist</b> (array): distances to print, if no distances prints the first</li>
 *      <li><b>ScoreDraw</b> (string): type of scorecard: <ul>
 *          <li><em>Complete</em>: full standard scorecard</li>
 *          <li><em>CompleteTotals</em>: as <em>Complete</em> with a running grand total column</li>
 *          <li><em>Data</em>: only the data (scorecards have been pre-printed)</li>
 *          <li><em>TargetNo</em>: only the target number (scorecards have been pre-printed)</li>
 *          <li><em>Draw</em>: only the structure, no data</li>
 *          <li><em>FourScoresNFAA</em>: special layout Vegas Style</li>
 *          </ul></li>
 *      <li><b>ScoreFilled</b> (bool): fills in the scorecard with arrow values</li>
 *      <li><b>ScoreFlags</b> (bool): prints the country/club flags</li>
 *      <li><b>ScoreHeader</b> (bool): prints the competition info</li>
 *      <li><b>ScoreLogos</b> (bool): prints the competition images. If QrCode is not empty, no bottom image is printed</li>
 *      <li><b>TourField3D</b> (string): empty (target archery), 'FIELD' or '3D'</li>
 *      </ul>
 * @param $SaveDir Directory to save each scorecard as a file. Ignored if not Personal scorecards.
 * @return ScorePDF
 */
function CreateSessionScorecard($Session, $FromTgt=1, $ToTgt=999, $Options=array(), $SaveDir='', $File='') {
    global $CFG;
	$Files=array();
	if($Session=='ONLINE') {
		$FromTgt=1;
		$ToTgt=999;
		$Options['ScoreDraw'] = "Complete";
        $Options['ScorePageHeaderFooter'] = "1";
        $Options['ScoreHeader'] = "0";
		$Options['ScoreLogos'] = "0";
		$Options['ScoreFlags'] = "1";
		$Options['ScoreBarcode'] = "1";
		$Options['PersonalScore'] = "1";
		$Options['ScoreFilled'] = "1";
		$Options['ScoreDist'] = range(1,8);
	}
	$DistArray=array();
	if(!empty($Options["ScoreDist"]) and is_array($Options["ScoreDist"])) {
		foreach($Options["ScoreDist"] as $Cards) {
			if(is_numeric($Cards)) {
				$DistArray[]=$Cards;
			}
		}
	} else {
		$DistArray[]=0;
	}

	$ScoreDraw=(empty($Options["ScoreDraw"]) ? '' : $Options["ScoreDraw"]);
	$FillWithArrows = !empty($Options["ScoreFilled"]);

	$pdf = new ScorePDF(true);
	//error_reporting(E_ALL);
    if(!$FillWithArrows and getModuleParameter('ScorecardsAI', 'Active', '') and module_exists('ScorecardsAI')) {
        $pdf->Aruco=1;
        $pdf->ArucoSize=getModuleParameter('ScorecardsAI', 'Size', 10);
        $pdf->ArucoType=970;
        require_once($CFG->DOCUMENT_PATH.'Modules/ScorecardsAI/Lib.php');
        $pdf->ArucoMarker=new ArucoMarkers();
    } else {
        $pdf->Aruco=0;
        $pdf->ArucoSize=0;
        $pdf->ArucoType=0;
        $pdf->ArucoMarker=null;
    }
    if(!empty($Options["QRCode"])) {
        $pdf->QRCode=$Options["QRCode"];
        $pdf->BottomImage=false;
    }
    if($ScoreDraw=="HorScoreAllDist") {
        $pdf->BottomImage=false;
        $pdf->HideLogo();
        $pdf->HideHeader();
    }
    if(!empty($Options["ScoreQrPersonal"])) {
        $pdf->ScoreQrPersonal=true;
        $pdf->BottomImage=false;
    }
	$pdf->FillWithArrows=$FillWithArrows;
	if(empty($Options["ScoreHeader"])) {
		$pdf->HideHeader();
	}
	if(empty($Options["ScoreLogos"])) {
		$pdf->HideLogo();
	}
	if(empty($Options["ScoreFlags"])) {
		$pdf->HideFlags();
	}
    $pdf->FullHeaderShow(!empty($Options["ScorePageHeaderFooter"]) and empty($Options["TourField3D"]));
	if(!empty($Options["ScoreBarcode"])) {
		$pdf->PrintBarcode=true;
	}
	if(!empty($Options["GetArcInfo"])) {
		$pdf->GetArcInfo=true;
	}
	switch($ScoreDraw) {
		case 'Data': $pdf->NoDrawing(); break;
		case 'CompleteTotals': $pdf->PrintTotalColumns(); break;
		case 'FourScoresNFAA':
            if(empty($pdf->prnGolds)) {
                $pdf->NoTensOnlyX();
            }
            break;
	}

	if(!isset($Options["TourField3D"])) {
		$Options["TourField3D"]=''; // target archery
	} elseif($Options["TourField3D"]) {
        $Options["IsRedding"]=($_SESSION['TourLocSubRule']=='NFAA3D-ReddingWestern');
    }

	$Data=QualificationScorecards($Session, $FromTgt, $ToTgt, $IncludeEmpty=(empty($Options["noEmpty"])), $ScoreDraw, $FillWithArrows, false, $PersonalScore=(!empty($Options['PersonalScore'])), $Options);
	if($SaveDir) {
		if($PersonalScore) {
			if(substr($SaveDir, -1)!='/') {
				$SaveDir.='/';
			}
		} else {
			$SaveDir = '';
		}
	}

	$pdf->LastUpdate=$Data->QuTimestamp;

	if($PersonalScore) {
		// this prints all distances of a single archer defined by distances on a same scorecard
		if (count($DistArray) > 1 or $DistArray[0] != 0) {
			$Data->Ath4Target = count($DistArray);
		}
	}

	$defScoreW = ($pdf->GetPageWidth()-$pdf->getSideMargin()*3-($pdf->ArucoSize? $pdf->ArucoSize-8 :0))/2;
	$defScoreH = ($pdf->GetPageHeight() - $pdf->getSideMargin()*3 - ($pdf->NoTensOnlyX ? 7 : 0) - ($pdf->ArucoSize?$pdf->ArucoSize-8:0))/2;

	$defScoreX = $pdf->getSideMargin()+($pdf->ArucoSize?$pdf->ArucoSize-3:0);
	$defScoreX2 = $defScoreX + ($pdf->ArucoSize ? 5 :$pdf->getSideMargin()) + $defScoreW;
	$defScoreY = $pdf->getSideMargin();
	$defScoreY2 = $defScoreY + ($pdf->ArucoSize ? 5 :$pdf->getSideMargin()) + $defScoreH - ($pdf->PrintFullHeader ? 10 + (empty($pdf->QRCode) ? 5 : 0) : 0);

	if(!$Options["TourField3D"]) {
		// target archery
        $ScoreGutter=0;
		if($Data->Ath4Target<=2) {
			$defScoreX = $pdf->getSideMargin()*3;
			$defScoreH = ($pdf->GetPageWidth()-$pdf->getSideMargin()*2 - ($pdf->NoTensOnlyX ? 7 : 0)-$pdf->ArucoSize-($pdf->PrintFullHeader ? 10 : 0));
			$defScoreW = ($pdf->GetPageHeight()-$defScoreX*3)/2;
		} elseif($Data->Ath4Target==3 or $Data->Ath4Target==5 or $Data->Ath4Target==6) {
            if($pdf->ArucoSize) {
                $ScoreGutter=$pdf->getSideMargin()/2;
            } else {
                $ScoreGutter=$pdf->getSideMargin();
            }
			$defScoreH = ($pdf->GetPageWidth()-$pdf->getSideMargin()*2-$pdf->ArucoSize)-($pdf->PrintFullHeader ? 10 : 0);
			$defScoreW = ($pdf->GetPageHeight()-$pdf->getSideMargin()*4-($pdf->ArucoSize?$pdf->ArucoSize-13:0))/3;
		} elseif($ScoreDraw=='HorScoreAllDist' or $ScoreDraw=='HorScore') {
            $ScoreGutter=$pdf->getSideMargin()/2;
            $defScoreH = ($pdf->GetPageWidth()-$pdf->getSideMargin()-34-($pdf->ArucoSize?$pdf->ArucoSize-8:0));
            $defScoreW = ($pdf->GetPageHeight()-$pdf->getSideMargin()*2-$ScoreGutter*3-($pdf->ArucoSize?$pdf->ArucoSize-3:0))/4;
            $defScoreY+=3;
		} elseif($ScoreDraw=='VertScoreAllDist') {
            $ScoreGutter=$pdf->getSideMargin();
            $defScoreH = ($pdf->GetPageHeight()-$pdf->getSideMargin()-83-($pdf->ArucoSize?:0));
            $defScoreW = ($pdf->GetPageWidth()-$pdf->getSideMargin()*3-($pdf->ArucoSize?:0))/2;
        }

		if(($pdf->QRCode or $pdf->ScoreQrPersonal) and $ScoreDraw!='Draw') {
			$QRCodeX=0;
			$QRCodeY=0;
			switch($Data->Ath4Target) {
                case 1:
				case 2:
				case 3:
                case 5:
                case 6:
					// ATTENTION HERE: Landscape page!
					$defScoreH-=25;
					$quanti=count($pdf->QRCode)+($pdf->ScoreQrPersonal ? 1 : 0);
					$QRCodeY=min($pdf->GetPageHeight(),$pdf->GetPageWidth()) - $pdf->getSideMargin() - 25 - ($pdf->PrintFullHeader ? 20 : $pdf->ArucoSize);
					$QRCodeX=(max($pdf->GetPageWidth(), $pdf->GetPageHeight()) + 5 - (25*$quanti))/2;
					break;
				default:
                    if($ScoreDraw=='HorScoreAllDist' or $ScoreDraw=='HorScore') {
                        $defScoreH-=25;
                        $quanti=count($pdf->QRCode)+($pdf->ScoreQrPersonal ? 1 : 0);
                        $QRCodeY=min($pdf->GetPageHeight(),$pdf->GetPageWidth()) - $pdf->getSideMargin() - 25;
                        $QRCodeX=(max($pdf->GetPageWidth(), $pdf->GetPageHeight()) + 5 - (25*$quanti))/2;
                    } else {
                        $defScoreH-=8;
                        $defScoreY2+=8;
                        $QRCodeY=(($pdf->GetPageHeight() - 25)/2)-0.5;
                        if(count($pdf->QRCode)>1 or $pdf->ScoreQrPersonal) {
                            $quanti=count($pdf->QRCode)+($pdf->ScoreQrPersonal ? 1 : 0);
                            $QRCodeX=($pdf->GetPageWidth() + 5 - (30*$quanti))/2;
                        }
                    }
					break;
			}
		}
	} else {
		// field and 3D all scorecards are portrait, but takes the whole width
        // If the competition is of redding style then scorecard is landscape, 3 distance on 1 page, 3rd on another
        if($_SESSION['TourLocSubRule']=='NFAA3D-ReddingWestern') {
            $pdf->IsRedding=true;
            $defScoreW = ($pdf->GetPageHeight()-$pdf->getSideMargin()*3-($pdf->ArucoSize?:0))/2;
            $defScoreH = ($pdf->GetPageWidth()-$pdf->getSideMargin()*2-($pdf->ArucoSize?:0));
            $defScoreX2=$defScoreW+$pdf->getSideMargin()+5;
            if($PersonalScore) {
                $defScoreW = ($pdf->GetPageHeight()-$pdf->getSideMargin()*4)/3;
            } elseif(count($DistArray)==1 and $DistArray[0]!=0) {
                // reprint only one distance so each scorecard has 4 archers
                $defScoreW = ($pdf->GetPageHeight()-$pdf->getSideMargin()*5)/4;
            }
        } else {
            $defScoreW = ($pdf->GetPageWidth()-$pdf->getSideMargin()*2);
        }
		if($pdf->QRCode or $pdf->ScoreQrPersonal) {
			$QRCodeY=0;
			$defScoreH-=12;
			$defScoreY2+=12;
			$quanti=count($pdf->QRCode)+($pdf->ScoreQrPersonal ? 1 : 0);
			$QRCodeX=($pdf->GetPageWidth() + 5 - (30*$quanti))/2;
		}
	}

	if($ScoreDraw=='Draw') {
		foreach($Data->Scores as $Target => $Cards) {
			if($Options["TourField3D"]) {
				foreach($Cards as $Value) {
					$Yscore = $defScoreY2;
					switch(substr($Value["tNo"],-1,1)) {
						case 'A':
						case 'C':
						case 'E':
						case 'G':
						case 'I':
						case 'K':
						case 'M':
						case 'O':
						case 'Q':
						case 'S':
						case 'U':
						case 'W':
						case 'Y':
							$pdf->AddPage();
							$Yscore = $defScoreY;
					}

					$pdf->DrawScoreField($defScoreX, $Yscore, $defScoreW, $defScoreH, 0, $Value, $Data->SesTar4Session, $Data->SesFirstTarget);
					if($Yscore == $defScoreY2 and ($pdf->QRCode or $pdf->ScoreQrPersonal)) {
						foreach($pdf->QRCode as $k => $Api) {
							require_once('Api/'.$Api.'/DrawQRCode.php');
							$Function='DrawQRCode_'.preg_replace('/[^a-z0-9]/sim', '_', $Api);
							$Function($pdf, $QRCodeX + 30*$k, $QRCodeY, $Options['x_Session'], 0, substr($Value["tNo"],0,-1));
						}
                        if($pdf->ScoreQrPersonal) {
                            DrawScoreQrPersonal($pdf, $Value['AtTarget'], $QRCodeX + 30*$k, $QRCodeY);
                        }
					}
				}
			} else {
				// Target Archery
				switch ($Data->Ath4Target) {
					case 1:
						$pdf->AddPage('P');
						$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[0]);
						break;
					case 2:
						$pdf->AddPage('L');
						$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[0]);
						$pdf->DrawScoreNew(2 * $defScoreX + $defScoreW, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[1]);
						break;
					case 3:
						$pdf->AddPage('L');
						$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[0]);
						$pdf->DrawScoreNew(2 * $defScoreX + $defScoreW, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[1]);
						$pdf->DrawScoreNew(3 * $defScoreX + 2 * $defScoreW, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[2]);
						break;
					case 4:
						$pdf->AddPage();
						$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[0]);
						$pdf->DrawScoreNew($defScoreX2, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[1]);
						$pdf->DrawScoreNew($defScoreX, $defScoreY2, $defScoreW, $defScoreH, 0, $Cards[2]);
						$pdf->DrawScoreNew($defScoreX2, $defScoreY2, $defScoreW, $defScoreH, 0, $Cards[3]);
                        break;
                    case 5:
                        $pdf->AddPage('L');
                        $pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[0]);
                        $pdf->DrawScoreNew(2 * $defScoreX + $defScoreW, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[1]);
                        $pdf->DrawScoreNew(3 * $defScoreX + 2 * $defScoreW, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[2]);
                        $pdf->AddPage('L');
                        $pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[3]);
                        $pdf->DrawScoreNew(2 * $defScoreX + $defScoreW, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[4]);
                        break;
                    case 6:
                        $pdf->AddPage('L');
                        $pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[0]);
                        $pdf->DrawScoreNew(2 * $defScoreX + $defScoreW, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[1]);
                        $pdf->DrawScoreNew(3 * $defScoreX + 2 * $defScoreW, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[2]);
                        $pdf->AddPage('L');
                        $pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[3]);
                        $pdf->DrawScoreNew(2 * $defScoreX + $defScoreW, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[4]);
                        $pdf->DrawScoreNew(3 * $defScoreX + 2 * $defScoreW, $defScoreY, $defScoreW, ($pdf->GetPageHeight() - $pdf->getSideMargin() * 2), 0, $Cards[5]);
                        break;
                    case 7:
                        $pdf->AddPage();
                        $pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[0]);
                        $pdf->DrawScoreNew($defScoreX2, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[1]);
                        $pdf->DrawScoreNew($defScoreX, $defScoreY2, $defScoreW, $defScoreH, 0, $Cards[2]);
                        $pdf->DrawScoreNew($defScoreX2, $defScoreY2, $defScoreW, $defScoreH, 0, $Cards[3]);
                        $pdf->AddPage();
                        $pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[4]);
                        $pdf->DrawScoreNew($defScoreX2, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[5]);
                        $pdf->DrawScoreNew($defScoreX, $defScoreY2, $defScoreW, $defScoreH, 0, $Cards[6]);
                        break;
                    case 8:
                        $pdf->AddPage();
                        $pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[0]);
                        $pdf->DrawScoreNew($defScoreX2, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[1]);
                        $pdf->DrawScoreNew($defScoreX, $defScoreY2, $defScoreW, $defScoreH, 0, $Cards[2]);
                        $pdf->DrawScoreNew($defScoreX2, $defScoreY2, $defScoreW, $defScoreH, 0, $Cards[3]);
                        $pdf->AddPage();
                        $pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[4]);
                        $pdf->DrawScoreNew($defScoreX2, $defScoreY, $defScoreW, $defScoreH, 0, $Cards[5]);
                        $pdf->DrawScoreNew($defScoreX, $defScoreY2, $defScoreW, $defScoreH, 0, $Cards[6]);
                        $pdf->DrawScoreNew($defScoreX2, $defScoreY2, $defScoreW, $defScoreH, 0, $Cards[7]);
                        break;
				}
                $k=0;
                if($pdf->ScoreQrPersonal) {
                    DrawScoreQrPersonal($pdf, $Cards[0]['AtTarget'], $QRCodeX + 30*$k, $QRCodeY);
                }
            }
		}
	} elseif($PersonalScore) {
		// this prints all distances of a single archer defined by distances on a same scorecard
		if(count($DistArray)>1 or $DistArray[0]!=0) {
			$Data->Ath4Target=count($DistArray);
		}

		$SecondScorer=false;

		$Origins=array();
		$Origins[]=array($defScoreX, $defScoreY);

		if($Options["TourField3D"]) {
            if($pdf->IsRedding) {
                $Origins[]=array(2*$defScoreX+$defScoreW, $defScoreY);
                $Origins[]=array(3*$defScoreX+2*$defScoreW, $defScoreY);
            }
		} else {
			if($Data->Ath4Target <= 3 OR ($Data->Ath4Target >= 5 and $Data->Ath4Target <= 6)) {
				$pdf->setPageOrientation('L');
			}

			switch($Data->Ath4Target) {
				case 2:
					$Origins[]=array(2*$defScoreX+$defScoreW, $defScoreY);
					break;
				case 3:
					$Origins[]=array(2*$defScoreX+$defScoreW, $defScoreY);
					$Origins[]=array(3*$defScoreX+2*$defScoreW, $defScoreY);
					break;
				case 4:
					$Origins[]=array($defScoreX2, $defScoreY);
					$Origins[]=array($defScoreX, $defScoreY2);
					$Origins[]=array($defScoreX2, $defScoreY2);
					break;
				case 5:
                    $Origins[]=array(2*$defScoreX+$defScoreW, $defScoreY);
                    $Origins[]=array(3*$defScoreX+2*$defScoreW, $defScoreY);
                    $Origins[]=array($defScoreX, $defScoreY);
                    $Origins[]=array(2*$defScoreX+$defScoreW, $defScoreY);
					break;
				case 6:
                    $Origins[]=array(2*$defScoreX+$defScoreW, $defScoreY);
                    $Origins[]=array(3*$defScoreX+2*$defScoreW, $defScoreY);
                    $Origins[]=array($defScoreX, $defScoreY);
                    $Origins[]=array(2*$defScoreX+$defScoreW, $defScoreY);
                    $Origins[]=array(3*$defScoreX+2*$defScoreW, $defScoreY);
                    break;
				case 7:
					$Origins[]=array($defScoreX2, $defScoreY);
					$Origins[]=array($defScoreX, $defScoreY2);
					$Origins[]=array($defScoreX2, $defScoreY2);
					$Origins[]=array($defScoreX, $defScoreY);
					$Origins[]=array($defScoreX2, $defScoreY);
					$Origins[]=array($defScoreX, $defScoreY2);
					break;
				case 8:
					$Origins[]=array($defScoreX2, $defScoreY);
					$Origins[]=array($defScoreX, $defScoreY2);
					$Origins[]=array($defScoreX2, $defScoreY2);
					$Origins[]=array($defScoreX, $defScoreY);
					$Origins[]=array($defScoreX2, $defScoreY);
					$Origins[]=array($defScoreX, $defScoreY2);
					$Origins[]=array($defScoreX2, $defScoreY2);
					break;
			}
		}
		if($pdf->QRCode or $pdf->ScoreQrPersonal) {
			$QRCodeX=0;
            $QRCodeX2=0;
			$QRCodeY=0;
			$quanti=count($pdf->QRCode)+($pdf->ScoreQrPersonal ? 1 : 0);
			switch($Data->Ath4Target) {
                case 1:
				case 2:
					$QRCodeY=$pdf->GetPageHeight() - $pdf->getSideMargin() - 25 - ($pdf->PrintFullHeader ? 20 : $pdf->ArucoSize);
					$QRCodeX=($defScoreW + 5 - (25*$quanti))/2;
					if($Options["TourField3D"]) {
						//$defScoreH-=5;
						//$defScoreY2-=6;
						$QRCodeY=0;
						$QRCodeX=($pdf->getPageWidth()+5-(30*$quanti))/2;
					}
					if(!$FillWithArrows) {
						$QRCodeX=5;
						$SecondScorer=true;
					}
					break;
				case 3:
                case 5:
                case 6:
					$defScoreH-=5;
					$QRCodeY=$pdf->GetPageHeight() - $pdf->getSideMargin() - 25;
					$QRCodeX=($defScoreW + 5 - (25*$quanti))/2;
					if(!$FillWithArrows) {
						$QRCodeX=5;
						$SecondScorer=true;
					}
					break;
				case 4:
                case 7:
                case 8:
					$defScoreH-=6;
					$defScoreY2+=6;
                    $Origins[2][1]+=1;
                    $Origins[3][1]+=1;
                    $QRCodeX +=5;
					$QRCodeX2 = $QRCodeX + ($defScoreW/2);
					if($quanti>1) {
						$QRCodeX = ($defScoreW + 5 - (25*$quanti))/2;
                        $QRCodeX2 = $QRCodeX + ($defScoreW + 5 - (25*$quanti))/4;
					}
					break;
			}
		} elseif($Data->Ath4Target<=3 and !$FillWithArrows and !$pdf->IsRedding) {
			// we still need to have the 2 scorers to sign!
			$defScoreH-=25;
			$SecondScorer=true;
		}

		$FileName='';
		if($SaveDir) {
			$OrgPdf=clone $pdf;
		}
        if($pdf->IsRedding) {
            $pdf->setPageOrientation('L');
        }
        foreach($Data->Scores as $Target => $Cards) {
			set_time_limit(120);
			foreach($Cards as $Card) {
				$Card['SecondScorer']=$SecondScorer;
				if($SaveDir) {
					if($FileName) {
						// a scorecard is ready
						$pdf->Output($SaveDir.$FileName, 'F');
						$Files[]=$FileName;
						$pdf=clone $OrgPdf;
						if($File) {
							file_put_contents($SaveDir.$File, $FileName);
						}
					}
	                $FileName = $_SESSION["TourCode"] . '_' . $Card['Session'] . '_' . $Card['tNo'].'.pdf';
				}
				$pdf->AddPage();
                $ArucoLeft=6;
                $ArucoBottom=$pdf->getPageHeight()-$pdf->ArucoSize-6;
                $ArucoRight=$pdf->getPageWidth()-$pdf->getSideMargin()-$pdf->ArucoSize;
                $Aru=[];
                if($pdf->Aruco) {
                    switch(count($DistArray)) {
                        case 1:
                            $pdf->Aruco=0;
                            break;
                        case 2:
                            $pdf->ArucoType=$pdf->ArucoMarker::Q2H;
                            break;
                        case 3:
                        case 5:
                        case 6:
                            $pdf->ArucoType=$pdf->ArucoMarker::Q3H;
                            break;
                        case 4:
                        case 7:
                        case 8:
                            $pdf->ArucoType=$pdf->ArucoMarker::Q4X;
                            break;
                    }
                }

                $score=1;
				foreach($DistArray as $k => $CurDist) {
					if ($CurDist and (!$Card["NumEnds" . $CurDist] or $Card["D" . $CurDist] == '-')) {
						continue;
					}

					if($Options["TourField3D"]) {
						// Odd Distances on top
						$Yscore = $defScoreY2;
                        $Xscore=$defScoreX;
						if($k%2 == 0) {
							$Yscore = $defScoreY;
							if($k>1 and !$pdf->IsRedding) {
								$pdf->AddPage();
							}
						}
                        if($pdf->IsRedding) {
                            $Card['PersonalScore']=1;
                            $Yscore=$defScoreY;
                            $Xscore=$Origins[max(0,$CurDist-1)][0];
                        }

						$pdf->DrawScoreField($Xscore, $Yscore, $defScoreW, $defScoreH, $CurDist, $Card, $Data->SesTar4Session, $Data->SesFirstTarget);
						if(($Yscore == $defScoreY2 or count($DistArray)==1) and ($pdf->QRCode or $pdf->ScoreQrPersonal)) {
							$j=0;
                            foreach($pdf->QRCode as $j => $Api) {
								require_once('Api/'.$Api.'/DrawQRCode.php');
								$Function='DrawQRCode_'.preg_replace('/[^a-z0-9]/sim', '_', $Api);
								$Function($pdf, $QRCodeX + 30*$j, $QRCodeY, $Card['Session'], 0, substr($Card["tNo"],0,-1), '', 'Q', $PersonalScore);
							}
                            if($pdf->ScoreQrPersonal) {
                                DrawScoreQrPersonal($pdf, $Card['AtTarget'], $QRCodeX + 30*$j, $QRCodeY);
                            }
						}
					} else {
						// target archery
						if(($Data->Ath4Target==5 and $k==3) or ($Data->Ath4Target==6 and $k==3) or ($Data->Ath4Target>6 and $k==4)) {
							$pdf->PrintAruco($Aru);
                            $pdf->AddPage();
                            $Aru['A']='';
                            $score=1;
						}
						$tmp=$pdf->DrawScoreNew($Origins[$k][0], $Origins[$k][1], $defScoreW, $defScoreH, $CurDist, $Card);
                        if(empty($Aru['A'])) {
                            // empty aruco, so we need to set all the codes
                            $Aru['T']=['X'=>$ArucoLeft,'Y'=>$tmp['Y'], 'N'=>$pdf->ArucoType];
                            $Aru['E']=['X'=>$tmp['X'],'Y'=>$ArucoBottom, 'N'=>$tmp['E']];
                            $Aru['O']=['X'=>$tmp['X'],'Y'=>$ArucoBottom,'N'=>$tmp['O']];
                            $Aru['W']=['X'=>$ArucoRight-5*$pdf->ArucoSize,'Y'=>$ArucoBottom,'N'=>$tmp['W']];
                            $Aru['H']=['X'=>$tmp['X']-3*$pdf->ArucoSize,'Y'=>$ArucoBottom,'N'=>$tmp['H']];
                            $Aru['A']=['X'=>$ArucoRight,'Y'=>$ArucoBottom,'N'=>$tmp['A']];
                        }
                        switch($score) {
                            case 2: // score "B"
                                $Aru['O']=['X'=>$tmp['X'],'Y'=>$ArucoBottom,'N'=>$tmp['O']];
                                $Aru['W']=['X'=>$ArucoRight-5*$pdf->ArucoSize,'Y'=>$ArucoBottom,'N'=>$tmp['W']];
                                $Aru['H']=['X'=>$tmp['X']-3*$pdf->ArucoSize,'Y'=>$ArucoBottom,'N'=>$tmp['H']];
                                break;
                            case 3: // score "C"
                                break;
                            case 4: // score "D"
                                break;
                        }
                        $score++;
						if($pdf->QRCode) {
						    $qrOrigin = $Origins[$k][0];
						    if($Data->Ath4Target==4) {
						        switch ($k) {
                                    case 1:
						                $qrOrigin = $Origins[2][0]+$QRCodeX2;
						                break;
                                    case 2:
						                $qrOrigin = $Origins[1][0];
						                break;
                                    case 3:
                                        $qrOrigin += $QRCodeX2;
                                        break;
                                }
                            }
	                        $TmpTarget = substr($Cards[0]['tNo'],0,-1);
                            $kQr=0;
							foreach($pdf->QRCode as $kQr => $Api) {
								require_once('Api/'.$Api.'/DrawQRCode.php');
								$Function='DrawQRCode_'.preg_replace('/[^a-z0-9]/sim', '_', $Api);
								$Function($pdf, $qrOrigin + $QRCodeX + 30*$kQr, $QRCodeY, $Card['Session'], $CurDist, $TmpTarget, '', 'Q', $PersonalScore, $Card["D".$CurDist]);
							}
                            if($pdf->ScoreQrPersonal) {
                                DrawScoreQrPersonal($pdf, $Card['AtTarget'], $QRCodeX + 30*$kQr, $QRCodeY);
                            }
						}
					}
				}
                $pdf->PrintAruco($Aru);
            }
		}
        if($SaveDir) {
            if($FileName) {
                // a scorecard is ready
                $pdf->Output($SaveDir.$FileName, 'F');
                $Files[]=$FileName;
                $pdf=clone $OrgPdf;
	            if($File) {
		            file_put_contents($SaveDir.$File, $FileName);
	            }
            }
        }
	} else {
		foreach($Data->Scores as $Target => $Cards) {
            if($pdf->IsRedding) {
                // scorecard is like regular field BUT distance 1 and 2 on one side and distance 3 on the other
                $n=0;
                if(count($DistArray)==1 and $DistArray[0]!=0) {
                    $pdf->HideLogo();
                    $pdf->HideFlags();
                    foreach($Cards as $k=>$Card) {
                        if(!$Card['EnCode']) {
                            continue;
                        }
                        if($n%4==0) {
                            $n=0;
                            $pdf->AddPage('L');
                            $X=$defScoreX;
                        }
                        $Card['MonoDistance']=1;
                        $pdf->DrawScoreField($X, $defScoreY, $defScoreW, $defScoreH, $DistArray[0], $Card, $Data->SesTar4Session, $Data->SesFirstTarget);
                        $n++;
                        $X+=($defScoreW+$pdf->getSideMargin());
                    }
                } else {
                    for($n=0;$n<count($Cards);$n+=2) {
                        $pdf->AddPage('L');
                        $pdf->DrawScoreField($defScoreX, $defScoreY, $defScoreW, $defScoreH, 1, $Cards[$n], $Data->SesTar4Session, $Data->SesFirstTarget);
                        if($Cards[$n+1] ?? '') {
                            $pdf->DrawScoreField($defScoreX2, $defScoreY, $defScoreW, $defScoreH, 1, $Cards[$n+1], $Data->SesTar4Session, $Data->SesFirstTarget);
                        }
                        if(count($Cards)!=1) {
                            $pdf->AddPage('L');
                            $pdf->DrawScoreField($defScoreX, $defScoreY, $defScoreW, $defScoreH, 3, $Cards[$n], $Data->SesTar4Session, $Data->SesFirstTarget);
                            if($Cards[$n+1] ?? '') {
                                $pdf->DrawScoreField($defScoreX2, $defScoreY, $defScoreW, $defScoreH, 3, $Cards[$n+1], $Data->SesTar4Session, $Data->SesFirstTarget);
                            }
                        } else {
                            $pdf->DrawScoreField($defScoreX2, $defScoreY, $defScoreW, $defScoreH, 3, $Cards[$n], $Data->SesTar4Session, $Data->SesFirstTarget);
                        }

                    }
                }
            } else {
                foreach($DistArray as $CurDist) {
                    if ($CurDist and $Cards[0]["D" . $CurDist] == '-') {
                        continue 2;
                    }

                    if($Options["TourField3D"] and $ScoreDraw!='FourScoresNFAA') {
                        foreach($Cards as $Value) {
                            $Yscore = $defScoreY2;
                            switch(substr($Value["tNo"],-1,1)) {
                                case 'A':
                                case 'C':
                                case 'E':
                                case 'G':
                                case 'I':
                                case 'K':
                                case 'M':
                                case 'O':
                                case 'Q':
                                case 'S':
                                case 'U':
                                case 'W':
                                case 'Y':
                                    $pdf->AddPage($pdf->IsRedding?'L':'P');
                                    $Yscore = $defScoreY;
                            }

                            $pdf->DrawScoreField($defScoreX, $Yscore, $defScoreW, $defScoreH, $CurDist, $Value, $Data->SesTar4Session, $Data->SesFirstTarget);
                            if($Yscore == $defScoreY2 and ($pdf->QRCode or $pdf->ScoreQrPersonal)) {
                                $k=0;
                                foreach($pdf->QRCode as $k => $Api) {
                                    require_once('Api/'.$Api.'/DrawQRCode.php');
                                    $Function='DrawQRCode_'.preg_replace('/[^a-z0-9]/sim', '_', $Api);
                                    $Function($pdf, $QRCodeX + 30*$k, $QRCodeY, $Cards[0]['Session'], $CurDist, substr($Value["tNo"],0,-1), '', 'Q', $PersonalScore);
                                }
                                if($pdf->ScoreQrPersonal) {
                                    DrawScoreQrPersonal($pdf, $Value['AtTarget'], $QRCodeX + 30*$k, $QRCodeY);
                                }
                            }
                        }

                    } else {
                        if($Options["TourField3D"] and $ScoreDraw=='FourScoresNFAA') {
                            // REDDING SCORECARDS
                            while(count($Cards)%4!=0) {
                                $Cards[]=$Data->DefaultScore+array('tNo'=>substr($Cards[0]['tNo'],0,-1).chr(65+count($Cards)));
                            }
                            $pdf->setPageOrientation('L');
                            $defScoreW=($pdf->getPageWidth()-5*$pdf->getSideMargin())/4;
                            $defScoreH=$pdf->getPageHeight()-2*$pdf->getSideMargin()-7;
                            $defScoreX=[$pdf->getSideMargin()];
                            $defScoreX[]=end($defScoreX)+$pdf->getSideMargin()+$defScoreW;
                            $defScoreX[]=end($defScoreX)+$pdf->getSideMargin()+$defScoreW;
                            $defScoreX[]=end($defScoreX)+$pdf->getSideMargin()+$defScoreW;
                            foreach($Cards AS $i => $Card) {
                                if(empty($Card["EnCode"])) {
                                    continue;
                                }
                                $Card['isField']=true;
                                $Card['is3dNFAA']=true;
                                if($i%4 == 0) {
                                    $pdf->AddPage('L');
                                }
                                $pdf->DrawScoreNew($defScoreX[$i%4], $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Card ?? $Data->DefaultScore+array('tNo'=>'A')));
                            }
                        } else {
                            // regular scorecards
                            $Aru=[];
                            if($ScoreDraw=='HorScoreAllDist') {
                                $pdf->AddPage('L');
                                $pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Cards[0] ?? $Data->DefaultScore+array('tNo'=>'A')));
                                $pdf->DrawScoreNew($defScoreX+$ScoreGutter+$defScoreW, $defScoreY, $defScoreW, $defScoreH,$CurDist, ($Cards[1] ?? $Data->DefaultScore+array('tNo'=>'B')));
                                $pdf->DrawScoreNew($defScoreX+2*($defScoreW+$ScoreGutter), $defScoreY, $defScoreW, $defScoreH,$CurDist,(($Cards[2] ?? $Data->DefaultScore+array('tNo'=>'C'))));
                                $pdf->DrawScoreNew($defScoreX+3*($defScoreW+$ScoreGutter), $defScoreY, $defScoreW, $defScoreH,$CurDist,(($Cards[3] ?? $Data->DefaultScore+array('tNo'=>'D'))));

                            } else {
                                $pdf->AddPage((($Data->Ath4Target <= 3 OR $Data->Ath4Target == 5 OR $Data->Ath4Target == 6) or $ScoreDraw=='HorScore') ? 'L' : 'P');
                                $ArucoLeft=6;
                                $ArucoBottom=$pdf->getPageHeight()-16;
                                $ArucoRight=$pdf->getPageWidth()-$pdf->getSideMargin()-$pdf->ArucoSize;
                                switch($Data->Ath4Target) {
                                    case 1:
                                        if(empty($Cards[0]['Ath'])) {
                                            continue 2;
                                        }
                                        $tmp=$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Cards[0] ?? $Data->DefaultScore+array('tNo'=>'A')));
                                        break;
                                    case 2:
                                        $pdf->ArucoType = $pdf->ArucoMarker ? $pdf->ArucoMarker::Q2H : 0;
                                        $tmp=$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Cards[0] ?? $Data->DefaultScore+array('tNo'=>'A')));
                                        $Aru['T']=['X'=>$ArucoLeft,'Y'=>$tmp['Y'], 'N'=>$pdf->ArucoType];
                                        $Aru['E']=['X'=>$tmp['X'],'Y'=>$ArucoBottom, 'N'=>$tmp['E']];
                                        $tmp=$pdf->DrawScoreNew(2*$defScoreX+$defScoreW, $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Cards[1] ?? $Data->DefaultScore+array('tNo'=>'B')));
                                        $Aru['O']=['X'=>$tmp['X'],'Y'=>$ArucoBottom,'N'=>$tmp['O']];
                                        $Aru['W']=['X'=>$ArucoRight-5*$pdf->ArucoSize,'Y'=>$ArucoBottom,'N'=>$tmp['W']];
                                        $Aru['H']=['X'=>$tmp['X']-3*$pdf->ArucoSize,'Y'=>$ArucoBottom,'N'=>$tmp['H']];
                                        $Aru['A']=['X'=>$ArucoRight,'Y'=>$ArucoBottom,'N'=>$tmp['A']];
                                        break;
                                    case 3:
                                        $tmp=$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Cards[0] ?? $Data->DefaultScore+array('tNo'=>'A')));
                                        $Aru['T']=['X'=>$ArucoLeft,'Y'=>$tmp['Y'], 'N'=>$pdf->ArucoType];
                                        $Aru['E']=['X'=>$tmp['X'],'Y'=>$ArucoBottom, 'N'=>$tmp['E']];
                                        $tmp=$pdf->DrawScoreNew($defScoreX+$defScoreW+$ScoreGutter, $defScoreY, $defScoreW, $defScoreH,$CurDist, ($Cards[1] ?? $Data->DefaultScore+array('tNo'=>'B')));
                                        $Aru['O']=['X'=>$tmp['X'],'Y'=>$ArucoBottom,'N'=>$tmp['O']];
                                        $tmp=$pdf->DrawScoreNew($defScoreX+2*($defScoreW+$ScoreGutter), $defScoreY, $defScoreW, $defScoreH,$CurDist,(($Cards[2] ?? $Data->DefaultScore+array('tNo'=>'C'))));
                                        $Aru['W']=['X'=>$tmp['X'],'Y'=>$ArucoBottom,'N'=>$tmp['W']];
                                        $Aru['H']=['X'=>$ArucoRight-3*$pdf->ArucoSize,'Y'=>$ArucoBottom,'N'=>$tmp['H']];
                                        $Aru['A']=['X'=>$ArucoRight,'Y'=>$ArucoBottom,'N'=>$tmp['A']];
                                        break;
                                    case 4:
                                        if($ScoreDraw=='HorScore') {
                                            $pdf->ArucoType = $pdf->ArucoMarker ? $pdf->ArucoMarker::Q4H : 0;
                                            $tmp=$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Cards[0] ?? $Data->DefaultScore+array('tNo'=>'A')));
                                            $Aru['T']=['X'=>$ArucoLeft,'Y'=>$tmp['Y'], 'N'=>$pdf->ArucoType];
                                            $Aru['E']=['X'=>$tmp['X'],'Y'=>$ArucoBottom, 'N'=>$tmp['E']];
                                            $tmp=$pdf->DrawScoreNew($defScoreX+$ScoreGutter+$defScoreW, $defScoreY, $defScoreW, $defScoreH,$CurDist, ($Cards[1] ?? $Data->DefaultScore+array('tNo'=>'B')));
                                            $Aru['O']=['X'=>$tmp['X'],'Y'=>$ArucoBottom,'N'=>$tmp['O']];
                                            $tmp=$pdf->DrawScoreNew($defScoreX+2*($defScoreW+$ScoreGutter), $defScoreY, $defScoreW, $defScoreH,$CurDist,(($Cards[2] ?? $Data->DefaultScore+array('tNo'=>'C'))));
                                            $Aru['W']=['X'=>$tmp['X'],'Y'=>$ArucoBottom,'N'=>$tmp['W']];
                                            $tmp=$pdf->DrawScoreNew($defScoreX+3*($defScoreW+$ScoreGutter), $defScoreY, $defScoreW, $defScoreH,$CurDist,(($Cards[3] ?? $Data->DefaultScore+array('tNo'=>'D'))));
                                            $Aru['H']=['X'=>$tmp['X'],'Y'=>$ArucoBottom,'N'=>$tmp['H']];

                                            $Aru['A']=['X'=>$ArucoRight,'Y'=>$ArucoBottom,'N'=>$tmp['A']];
                                        } else {
                                            $pdf->ArucoType = $pdf->ArucoMarker ? $pdf->ArucoMarker::Q4X : 0;
                                            $tmp=$pdf->DrawScoreNew( $defScoreX,  $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Cards[0] ?? $Data->DefaultScore+array('tNo'=>'A')));
                                            $Aru['T']=['X'=>$ArucoLeft,'Y'=>$tmp['Y'], 'N'=>$pdf->ArucoType];
                                            $tmp=$pdf->DrawScoreNew($defScoreX2,  $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Cards[1] ?? $Data->DefaultScore+array('tNo'=>'B')));
                                            $Aru['O']=['X'=>$tmp['X'],'Y'=>$ArucoBottom, 'N'=>$tmp['O']];
                                            $tmp=$pdf->DrawScoreNew( $defScoreX, $defScoreY2, $defScoreW, $defScoreH, $CurDist, ($Cards[2] ?? $Data->DefaultScore+array('tNo'=>'C')));
                                            $Aru['E']=['X'=>$tmp['X'],'Y'=>$ArucoBottom,'N'=>$tmp['E']];
                                            $tmp=$pdf->DrawScoreNew($defScoreX2, $defScoreY2, $defScoreW, $defScoreH, $CurDist, ($Cards[3] ?? $Data->DefaultScore+array('tNo'=>'D')));
                                            $Aru['W']=['X'=>$ArucoLeft,'Y'=>$tmp['Y'],'N'=>$tmp['W']];

                                            $Aru['H']=['X'=>$ArucoRight-2*$pdf->ArucoSize,'Y'=>$ArucoBottom,'N'=>$tmp['H']];
                                            $Aru['A']=['X'=>$ArucoRight,'Y'=>$ArucoBottom,'N'=>$tmp['A']];
                                        }
                                        break;
                                    case 5:
                                        $tmp=$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Cards[0] ?? $Data->DefaultScore+array('tNo'=>'A')));
                                        $tmp=$pdf->DrawScoreNew(2*$defScoreX+$defScoreW, $defScoreY, $defScoreW, $defScoreH,$CurDist, ($Cards[1] ?? $Data->DefaultScore+array('tNo'=>'B')));
                                        $tmp=$pdf->DrawScoreNew(3*$defScoreX+2*$defScoreW, $defScoreY, $defScoreW, $defScoreH,$CurDist,(($Cards[2] ?? $Data->DefaultScore+array('tNo'=>'C'))));
                                        $pdf->AddPage('L');
                                        $tmp=$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Cards[3] ?? $Data->DefaultScore+array('tNo'=>'D')));
                                        $tmp=$pdf->DrawScoreNew(2*$defScoreX+$defScoreW, $defScoreY, $defScoreW, $defScoreH,$CurDist, ($Cards[4] ?? $Data->DefaultScore+array('tNo'=>'E')));
                                        break;
                                    case 6:
                                        $tmp=$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Cards[0] ?? $Data->DefaultScore+array('tNo'=>'A')));
                                        $tmp=$pdf->DrawScoreNew(2*$defScoreX+$defScoreW, $defScoreY, $defScoreW, $defScoreH,$CurDist, ($Cards[1] ?? $Data->DefaultScore+array('tNo'=>'B')));
                                        $tmp=$pdf->DrawScoreNew(3*$defScoreX+2*$defScoreW, $defScoreY, $defScoreW, $defScoreH,$CurDist,(($Cards[2] ?? $Data->DefaultScore+array('tNo'=>'C'))));
                                        $pdf->AddPage('L');
                                        $tmp=$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, $CurDist, ($Cards[3] ?? $Data->DefaultScore+array('tNo'=>'D')));
                                        $tmp=$pdf->DrawScoreNew(2*$defScoreX+$defScoreW, $defScoreY, $defScoreW, $defScoreH,$CurDist, ($Cards[4] ?? $Data->DefaultScore+array('tNo'=>'E')));
                                        $tmp=$pdf->DrawScoreNew(3*$defScoreX+2*$defScoreW, $defScoreY, $defScoreW, $defScoreH,$CurDist,(($Cards[5] ?? $Data->DefaultScore+array('tNo'=>'F'))));
                                        break;
                                    default:
                                        for($n=0;$n<count($Cards);$n++) {
                                            switch($n%4) {
                                                case 0:
                                                    if($n) {
                                                        $pdf->AddPage(($Data->Ath4Target <= 3 or $ScoreDraw=='HorScore') ? 'L' : 'P');
                                                    }
                                                    $tmp=$pdf->DrawScoreNew($defScoreX, $defScoreY, $defScoreW, $defScoreH, $CurDist,($Cards[$n] ?? $Data->DefaultScore+array('tNo'=>chr(65+$n))));
                                                    break;
                                                case 1:
                                                    $tmp=$pdf->DrawScoreNew($defScoreX2, $defScoreY, $defScoreW, $defScoreH, $CurDist,($Cards[$n] ?? $Data->DefaultScore+array('tNo'=>chr(65+$n))));
                                                    break;
                                                case 2:
                                                    $tmp=$pdf->DrawScoreNew($defScoreX, $defScoreY2, $defScoreW, $defScoreH, $CurDist,($Cards[$n] ?? $Data->DefaultScore+array('tNo'=>chr(65+$n))));
                                                    break;
                                                case 3:
                                                    $tmp=$pdf->DrawScoreNew($defScoreX2, $defScoreY2, $defScoreW, $defScoreH, $CurDist,($Cards[$n] ?? $Data->DefaultScore+array('tNo'=>chr(65+$n))));
                                                    break;
                                            }
                                        }
                                        break;
                                }
                                $pdf->PrintAruco($Aru);
//                                if($Aru and $pdf->Aruco) {
//                                    // print type
//                                    foreach($Aru as $k=>$v) {
//                                        $pdf->ImageSVG('@'.$pdf->ArucoMarker->CreateArucoMarker($v['N']), $v['X'],$v['Y'], $pdf->ArucoSize, $pdf->ArucoSize);
//                                        $pdf->setXY($v['X'],$v['Y']+$pdf->ArucoSize);
//                                        $pdf->Cell($pdf->ArucoSize, 0, $k.' '.$v['N'], 0, 0, 'C');
//                                    }
//                                }
                            }
                            if($pdf->QRCode or $pdf->ScoreQrPersonal) {
                                $TmpTarget = substr($Cards[0]['tNo'],0,-1);
                                $k=-1;
                                foreach($pdf->QRCode as $k => $Api) {
                                    require_once('Api/'.$Api.'/DrawQRCode.php');
                                    $Function='DrawQRCode_'.preg_replace('/[^a-z0-9]/sim', '_', $Api);
                                    $Function($pdf, $QRCodeX + 30*$k, $QRCodeY, $Cards[0]['Session'], $CurDist, $TmpTarget, '', 'Q', $PersonalScore);
                                }
                                if($pdf->ScoreQrPersonal) {
                                    $k++;
                                    DrawScoreQrPersonal($pdf, $Cards[0]['AtTarget'], $QRCodeX + 30*$k, $QRCodeY);
                                }
                            }
                        }
                    }
                }
            }
		}
	}
	if($SaveDir) {
		return($Files);
	}
	return $pdf;
}

function QualificationScorecards($Session, $FromTgt, $ToTgt, $IncludeEmpty=true, $ScoreDraw='', $FillWithArrows=false, $Category = false, $PersonalScore=false, $Options=array()) {
	$Data=new stdClass();
	$Data->QuTimestamp = '';
	$Data->Ath4Target = 4;
	$Data->NumEnds=12;
	$Coalesce=(!empty($Options['x_Coalesce']));

	if(!empty($Options['SessionType']) and $Options['SessionType']=='E') {
		if($Session>0) {
			$ses=GetSessions(null,false, array($Session.'_E'));
			$Data->Ath4Target = $ses[0]->SesAth4Target;
            $Data->SesTar4Session = $ses[0]->SesTar4Session;
            $Data->SesFirstTarget = $ses[0]->SesFirstTarget;
		} else {
	        $ses=GetSessions('E',false);
	        $Data->Ath4Target = $ses[0]->SesAth4Target;
            $Data->SesTar4Session = $ses[0]->SesTar4Session;
            $Data->SesFirstTarget = $ses[0]->SesFirstTarget;
	        foreach ($ses as $tmpSession) {
	            $Data->Ath4Target = max($Data->Ath4Target, $tmpSession->SesAth4Target);
                $Data->SesTar4Session =  max($Data->SesTar4Session, $tmpSession->SesTar4Session);
                $Data->SesFirstTarget =  max($Data->SesFirstTarget, $tmpSession->SesFirstTarget);
	        }
	    }
	} else {
		$MyQuery = "SELECT ToNumEnds AS TtNumEnds FROM Tournament  WHERE ToId=" . StrSafe_DB($_SESSION['TourId']);
		$Rs=safe_r_sql($MyQuery);
		if($r=safe_fetch($Rs)) {
			$Data->NumEnds=$r->TtNumEnds;
		}

		if($Session>0) {
			$ses=GetSessions(null,false, array($Session.'_Q'));
			$Data->Ath4Target = $ses[0]->SesAth4Target;
            $Data->SesTar4Session = $ses[0]->SesTar4Session;
            $Data->SesFirstTarget = $ses[0]->SesFirstTarget;
		} else {
	        $ses=GetSessions('Q',false);
	        $Data->Ath4Target = $ses[0]->SesAth4Target;
            $Data->SesTar4Session = $ses[0]->SesTar4Session;
            $Data->SesFirstTarget = $ses[0]->SesFirstTarget;
	        foreach ($ses as $tmpSession) {
	            $Data->Ath4Target = max($Data->Ath4Target, $tmpSession->SesAth4Target);
                $Data->SesTar4Session =  max($Data->SesTar4Session, $tmpSession->SesTar4Session);
                $Data->SesFirstTarget =  max($Data->SesFirstTarget, $tmpSession->SesFirstTarget);
	        }
	    }
	}

	if($Coalesce) {
	    $Data->Ath4Target*=2;
	}
	$Data->Scores=array();
	$Data->DefaultScore=[];

	if($ScoreDraw=='Draw') {
		$q=safe_r_sql("select ToGolds, ToGoldsChars, ToXNine, ToXNineChars from Tournament where ToId={$_SESSION['TourId']}");
		$r=safe_fetch($q);
		$DrawArray=array("Session"=>$Session, 'Arr0'=>'', 'Golds'=>$r->ToGolds, 'XNine'=>$r->ToXNine,
            'GoldsChars'=>$r->ToGoldsChars, 'XNineChars'=>$r->ToXNineChars);
		// gets the correct distance/session
		if($ses[0]->SesType=='Q') {
			$tmp=getArrowEnds($ses[0]->SesOrder, 1);
			$DrawArray['NumEnds0']=$tmp[1]['ends'];
			$DrawArray['NumArrows0']=$tmp[1]['arrows'];
		}
        $tmpArray = array();
        if(empty($Data->Session[$Session])) {
            for($tgtLetter=0; $tgtLetter<$Data->Ath4Target; $tgtLetter++) {
                $tmpArray = array_merge($tmpArray, array($DrawArray + array('tNo' => chr(ord('A')+$tgtLetter))));
            }

        } else {
            for($tgtLetter=0; $tgtLetter<$Data->Session[$Session]; $tgtLetter++) {
                $tmpArray = array_merge($tmpArray, array($DrawArray + array('tNo' => chr(ord('A')+$tgtLetter))));
            }
        }
        $Data->Scores[] = $tmpArray;
		$Data->DefaultScore=$DrawArray;

		return $Data;
    }

	$FillWithArrows=($FillWithArrows and $ScoreDraw!='TargetNo');

	if(!empty($Options['SessionType']) and $Options['SessionType']=='E') {
		$MyQuery=GetElimScoreBySessionQuery($Session, $Options['x_Phase'], $FromTgt, $ToTgt);
	} else {
		if($Category) {
			$MyQuery=GetScoreByCategoryQuery();
		} else {
			$MyQuery=GetScoreBySessionQuery($Session, $FromTgt, $ToTgt, $IncludeEmpty, $PersonalScore or !empty($Options['IsRedding']), empty($Options['Entry']) ? 0 : intval($Options['Entry']));
		}
	}
	$Rs=safe_r_sql($MyQuery);
	$BothTarget=array('','');
	$OldTarget='';
    while($MyRow=safe_fetch($Rs)) {
	    $Data->QuTimestamp=max($Data->QuTimestamp, $MyRow->QuTimestamp);
        if(!$FillWithArrows) {
            $MyRow->Arr1='';
            $MyRow->Arr2='';
            $MyRow->Arr3='';
            $MyRow->Arr4='';
            $MyRow->Arr5='';
            $MyRow->Arr6='';
            $MyRow->Arr7='';
            $MyRow->Arr8='';
            $MyRow->Tot1='';
            $MyRow->Tot2='';
            $MyRow->Tot3='';
            $MyRow->Tot4='';
            $MyRow->Tot5='';
            $MyRow->Tot6='';
            $MyRow->Tot7='';
            $MyRow->Tot8='';
        }
        if($ScoreDraw=='TargetNo') {
            $MyRow->EnCode='';
            $MyRow->Ath='';
            $MyRow->Noc='';
            $MyRow->CoCode='';
            $MyRow->CoName='';
        }
        $Target=($MyRow->AtTarget ?: '-');
        $Data->Scores[$Target][]=(array) $MyRow;
		if(empty($Data->DefaultScore)) {
            if(!empty($Options['SessionType']) and $Options['SessionType']=='E') {
                $Data->DefaultScore = array("Session" => $MyRow->Session, 'Arr0' => '', 'Golds' => $MyRow->Golds, 'XNine' => $MyRow->XNine,
                    'GoldsChars' => $MyRow->GoldsChars, 'XNineChars' => $MyRow->XNineChars,
                    'NumEnds' . $MyRow->Session => $MyRow->{'NumEnds' . $Options['x_Phase']}, 'NumArrows' . $MyRow->Session => $MyRow->{'NumArrows' . $Options['x_Phase']},
                );
            } else {
                $Data->DefaultScore = array("Session" => $MyRow->Session, 'Arr0' => '', 'Golds' => $MyRow->Golds, 'XNine' => $MyRow->XNine,
                    'GoldsChars' => $MyRow->GoldsChars, 'XNineChars' => $MyRow->XNineChars,
                    'NumEnds' . $MyRow->Session => $MyRow->{'NumEnds' . $MyRow->Session}, 'NumArrows' . $MyRow->Session => $MyRow->{'NumArrows' . $MyRow->Session},
                );
            }
		}
	}
    if($Coalesce) {
    	$Targets=array_keys($Data->Scores);
    	$NewScores=array();
    	while($Targets) {
    		$tgt1=array_shift($Targets);
    		if($tgt2=@array_shift($Targets)) {
			    $Data->Scores["$tgt1"]=array_merge($Data->Scores["$tgt1"], $Data->Scores["$tgt2"]);
			    unset($Data->Scores[$tgt2]);
		    } else {
    			foreach($Data->Scores["$tgt1"] as $tmp) {
    				foreach($tmp as $k=>$v) {
    					if(!($k[0]=='D' and strlen($k)==2)) {
    						$tmp[$k]='';
					    }
				    }
    				//$tmp
				    $Data->Scores["$tgt1"][]=$tmp;
			    }
		    }
	    }
    }
	safe_free_result($Rs);
	return $Data;
}

function GetElimScoreBySessionQuery($Session, $Phase, $FromTgt, $ToTgt) {
    $atSql = createAvailableTargetSQL(($Session == -1 ? 0 : $Session),$_SESSION['TourId']);
	$MyQuery = "SELECT ElTargetNo as tNo, ElTargetNo as AtTarget, SUBSTRING(ElTargetNo,0,-1) as AtTarget, ElSession as Session, '' as Dist, EnCode, ElDateTime as QuTimestamp, EnDob as DoB, CoCode, CoName, CONCAT(EnFirstName,' ', EnName) AS Ath, CONCAT(CoCode, ' - ', CoName) as Noc, EnDivision as `Div`, EnClass as Cls, EdEmail as Email, 
            EvCode as Cat,
            '' as D0, '' as D1,
            '' as Arr0, ElArrowstring as Arr1,
            '' as QuD0, ElScore as QuD1,
            '' as Tot0, 0 as Tot1,
            '' as QuGD0, ElGold as QuGD1, 
            '' as QuXD0, ElXnine as QuXD1,
            '' as gxD0, ElGold+ElXnine as gxD1,
            length(trim(ElArrowstring)) as Arrows,
       		EvE".($Phase+1)."Ends as NumEnds0, EvE".($Phase+1)."Arrows as NumArrows0,
       		EvE".($Phase+1)."Ends as NumEnds1, EvE".($Phase+1)."Arrows as NumArrows1,
       		IF(EvGolds='',ToGolds,EvGolds) as Golds, IF(EvXNine='',ToXNine,EvXNine) as XNine,
            IF(EvGoldsChars='',ToGoldsChars,EvGoldsChars) as GoldsChars, IF(EvXNineChars='',ToXNineChars,EvXNineChars) as XNineChars
		FROM Eliminations 
		inner JOIN Events ON EvCode=ElEventCode and EvTeamEvent=0 and EvTournament=ElTournament
		Inner JOIN Tournament ON EvTournament=ToId
		left JOIN Entries AS e ON ElId=e.EnId AND EnAthlete=1 
		left join ExtraData ON EdId=EnId and EdType='E'
		left JOIN Countries AS c ON e.EnCountry=c.CoId AND e.EnTournament=c.CoTournament 
		LEFT JOIN Session ON ElSession=SesOrder AND ElTournament=SesTournament AND SesType='E' 
		WHERE ElTournament = {$_SESSION['TourId']} and ElElimPhase=" . intval($Phase)
		. (!(empty($FromTgt) && empty($ToTgt)) ? " AND ElTargetNo>='" . str_pad($FromTgt,'3','0',STR_PAD_LEFT) . "A' AND ElTargetNo<='" . str_pad($ToTgt,'3','0',STR_PAD_LEFT) . "Z' " : "")
		. (!empty($Session) ? " AND ElSession=". ($Session) : "")
		. ' ORDER BY ElSession, SesOrder, ElElimPhase, ElTargetNo ASC, EnFirstName, EnName, CoCode';

	return $MyQuery;
}

function GetScoreBySessionQuery($Session, $FromTgt, $ToTgt, $IncludeEmpty=true, $PersonalScore=false, $EnId=0) {
	$NoEmpty='';
    $atSql = createAvailableTargetSQL(($Session == -1 ? 0 : $Session),$_SESSION['TourId']);
    if(!$IncludeEmpty) {
        $NoEmpty = "INNER JOIN
			(SELECT DISTINCT QuTarget as TgtNo, QuSession as TgtSession
			FROM Qualifications
			INNER JOIN Entries On QuId=EnId
			WHERE EnTournament = {$_SESSION['TourId']} AND EnAthlete=1 " .
            ($Session!=-1 ? "AND QuSession=$Session ". ((!empty($FromTgt) and is_numeric($FromTgt)) ? " and QuTarget between $FromTgt and $ToTgt " :  "")  : "") .
			") as Tgt ON TgtNo=FullTgtTarget and TgtSession=FullTgtSession";
	}
	$EndSql='d1.DiEnds as NumEnds0, d1.DiArrows as NumArrows0, d1.DiScoringEnds as ScoringEnds0, d1.DiScoringOffset as ScoringOffset0';
	for($i=1;$i<=8;$i++) {
		$EndSql.=", d{$i}.DiEnds as NumEnds{$i}, d{$i}.DiArrows as NumArrows{$i}, d{$i}.DiScoringEnds as ScoringEnds{$i}, d{$i}.DiScoringOffset as ScoringOffset{$i}";
	}
	$MyQuery = "SELECT concat(FullTgtTarget,FullTgtLetter) as tNo, FullTgtTarget as AtTarget, FullTgtSession as Session, '' as Dist, EnCode, QuTimestamp, EnDob as DoB, CoCode, CoName, CONCAT(EnFirstName,' ', EnName) AS Ath, CONCAT(CoCode, ' - ', CoName) as Noc, EnDivision as `Div`, EnClass as Cls, EdEmail as Email, 
            concat(EnDivision, ' ', EnClass) as Cat, SesName, 
            IF(TfGolds='',ToGolds,TfGolds) as Golds, IF(TfXNine='',ToXNine,TfXNine) as XNine,
            IF(TfGoldsChars='',ToGoldsChars,TfGoldsChars) as GoldsChars, IF(TfXNineChars='',ToXNineChars,TfXNineChars) as XNineChars,
            '' as D0, Td1 as D1, Td2 as D2, Td3 as D3, Td4 as D4, Td5 as D5, Td6 as D6, Td7 as D7, Td8 as D8,
            '' as Arr0, QuD1Arrowstring as Arr1, QuD2Arrowstring as Arr2, QuD3Arrowstring as Arr3, QuD4Arrowstring as Arr4, QuD5Arrowstring as Arr5, QuD6Arrowstring as Arr6, QuD7Arrowstring as Arr7, QuD8Arrowstring as Arr8,
            '' as QuD0, QuD1Score as QuD1, QuD2Score as QuD2, QuD3Score as QuD3, QuD4Score as QuD4, QuD5Score as QuD5, QuD6Score as QuD6, QuD7Score as QuD7, QuD8Score as QuD8,
            '' as Tot0, 0 as Tot1, QuD1Score as Tot2, QuD1Score+QuD2Score as Tot3, QuD1Score+QuD2Score+QuD3Score as Tot4, QuD1Score+QuD2Score+QuD3Score+QuD4Score as Tot5, QuD1Score+QuD2Score+QuD3Score+QuD4Score+QuD5Score as Tot6, QuD1Score+QuD2Score+QuD3Score+QuD4Score+QuD5Score+QuD6Score as Tot7, QuD1Score+QuD2Score+QuD3Score+QuD4Score+QuD5Score+QuD6Score+QuD7Score as Tot8,
            '' as QuGD0, QuD1Gold as QuGD1, QuD2Gold as QuGD2, QuD3Gold as QuGD3, QuD4Gold as QuGD4, QuD5Gold as QuGD5, QuD6Gold as QuGD6, QuD7Gold as QuGD7, QuD8Gold as QuGD8, 
            '' as QuXD0, QuD1XNine as QuXD1, QuD2XNine as QuXD2, QuD3XNine as QuXD3, QuD4XNine as QuXD4, QuD5XNine as QuXD5, QuD6XNine as QuXD6, QuD7XNine as QuXD7, QuD8XNine as QuXD8,
            '' as gxD0, QuD1Gold+QuD1XNine as gxD1, QuD2Gold+QuD2XNine as gxD2, QuD3Gold+QuD3XNine as gxD3, QuD4Gold+QuD4XNine as gxD4, QuD5Gold+QuD5XNine as gxD5, QuD6Gold+QuD6XNine as gxD6, QuD7Gold+QuD7XNine as gxD7, QuD8Gold+QuD8XNine as gxD8,
            length(trim(concat(QuD1Arrowstring, QuD2Arrowstring, QuD3Arrowstring, QuD4Arrowstring, QuD5Arrowstring, QuD6Arrowstring, QuD7Arrowstring, QuD8Arrowstring))) as Arrows,
       		$EndSql
		FROM ($atSql) as at " .
        ((!$IncludeEmpty AND $PersonalScore) ? "INNER" : "LEFT") . " join (select * from Qualifications 
			inner join Entries on EnId=QuId and EnTournament={$_SESSION['TourId']}
			inner join Countries on CoId=EnCountry and CoTournament=EnTournament
			inner join Tournament on ToId=EnTournament
			inner join Session on SesTournament=EnTournament AND SesOrder=QuSession AND SesType='Q'
			left join TargetFaces ON TfTournament=EnTournament and EnTargetFace=TfId
			left join ExtraData ON EdId=EnId and EdType='E'
			left join TournamentDistances ON ToType=TdType and TdTournament=ToId AND CONCAT(TRIM(EnDivision),TRIM(EnClass)) LIKE TdClasses) sqy ON QuSession=FullTgtSession AND QuTarget=FullTgtTarget AND QuLetter=FullTgtLetter
			left join DistanceInformation d1 on d1.DiTournament={$_SESSION['TourId']} and d1.DiType='Q' and d1.DiSession=FullTgtSession and d1.DiDistance=1
			left join DistanceInformation d2 on d2.DiTournament={$_SESSION['TourId']} and d2.DiType='Q' and d2.DiSession=FullTgtSession and d2.DiDistance=2
			left join DistanceInformation d3 on d3.DiTournament={$_SESSION['TourId']} and d3.DiType='Q' and d3.DiSession=FullTgtSession and d3.DiDistance=3
			left join DistanceInformation d4 on d4.DiTournament={$_SESSION['TourId']} and d4.DiType='Q' and d4.DiSession=FullTgtSession and d4.DiDistance=4
			left join DistanceInformation d5 on d5.DiTournament={$_SESSION['TourId']} and d5.DiType='Q' and d5.DiSession=FullTgtSession and d5.DiDistance=5
			left join DistanceInformation d6 on d6.DiTournament={$_SESSION['TourId']} and d6.DiType='Q' and d6.DiSession=FullTgtSession and d6.DiDistance=6
			left join DistanceInformation d7 on d7.DiTournament={$_SESSION['TourId']} and d7.DiType='Q' and d7.DiSession=FullTgtSession and d7.DiDistance=7
			left join DistanceInformation d8 on d8.DiTournament={$_SESSION['TourId']} and d8.DiType='Q' and d8.DiSession=FullTgtSession and d8.DiDistance=8
		$NoEmpty ".
        ($Session!=-1 ? " WHERE FullTgtSession=$Session " . ((!empty($FromTgt) AND is_numeric($FromTgt)) ? " and FullTgtTarget between $FromTgt and $ToTgt " : "") : "") .
		($EnId ? " and EnId=$EnId " : "").
		"ORDER BY FullTgtSession ASC, FullTgtTarget ASC, FullTgtLetter ASC, Ath, Noc";
	return $MyQuery;
}

function GetScoreByCategoryQuery($Category='') {
	$MyQuery = "SELECT EnCode, EnDob as DoB, CoCode, CoName, QuSession as Session, QuTimestamp, QuTarget as AtTarget, '' as Dist, CONCAT(QuTarget, QuLetter) as tNo, CONCAT(EnFirstName,' ', EnName) AS Ath, CONCAT(CoCode, ' - ', CoName) as Noc, EnDivision as `Div`, EnClass as Cls, EdEmail as Email, 
           concat(EnDivision, ' ', EnClass) as Cat, SesName,
            IF(TfGolds='',ToGolds,TfGolds) as Golds, IF(TfXNine='',ToXNine,TfXNine) as XNine,
            IF(TfGoldsChars='',ToGoldsChars,TfGoldsChars) as GoldsChars, IF(TfXNineChars='',ToXNineChars,TfXNineChars) as XNineChars,
            '' as D0, Td1 as D1, Td2 as D2, Td3 as D3, Td4 as D4, Td5 as D5, Td6 as D6, Td7 as D7, Td8 as D8,
            '' as Arr0, QuD1Arrowstring as Arr1, QuD2Arrowstring as Arr2, QuD3Arrowstring as Arr3, QuD4Arrowstring as Arr4, QuD5Arrowstring as Arr5, QuD6Arrowstring as Arr6, QuD7Arrowstring as Arr7, QuD8Arrowstring as Arr8,
            '' as QuD0, QuD1Score as QuD1, QuD2Score as QuD2, QuD3Score as QuD3, QuD4Score as QuD4, QuD5Score as QuD5, QuD6Score as QuD6, QuD7Score as QuD7, QuD8Score as QuD8,
            '' as Tot0, 0 as Tot1, QuD1Score as Tot2, QuD1Score+QuD2Score as Tot3, QuD1Score+QuD2Score+QuD3Score as Tot4, QuD1Score+QuD2Score+QuD3Score+QuD4Score as Tot5, QuD1Score+QuD2Score+QuD3Score+QuD4Score+QuD5Score as Tot6, QuD1Score+QuD2Score+QuD3Score+QuD4Score+QuD5Score+QuD6Score as Tot7, QuD1Score+QuD2Score+QuD3Score+QuD4Score+QuD5Score+QuD6Score+QuD7Score as Tot8,
            '' as QuGD0, QuD1Gold as QuGD1, QuD2Gold as QuGD2, QuD3Gold as QuGD3, QuD4Gold as QuGD4, QuD5Gold as QuGD5, QuD6Gold as QuGD6, QuD7Gold as QuGD7, QuD8Gold as QuGD8, 
            '' as QuXD0, QuD1XNine as QuXD1, QuD2XNine as QuXD2, QuD3XNine as QuXD3, QuD4XNine as QuXD4, QuD5XNine as QuXD5, QuD6XNine as QuXD6, QuD7XNine as QuXD7, QuD8XNine as QuXD8,
            '' as gxD0, QuD1Gold+QuD1XNine as gxD1, QuD2Gold+QuD2XNine as gxD2, QuD3Gold+QuD3XNine as gxD3, QuD4Gold+QuD4XNine as gxD4, QuD5Gold+QuD5XNine as gxD5, QuD6Gold+QuD6XNine as gxD6, QuD7Gold+QuD7XNine as gxD7, QuD8Gold+QuD8XNine as gxD8,
            length(trim(concat(QuD1Arrowstring, QuD2Arrowstring, QuD3Arrowstring, QuD4Arrowstring, QuD5Arrowstring, QuD6Arrowstring, QuD7Arrowstring, QuD8Arrowstring))) as Arrows,
       		d1.DiEnds as NumEnds0, d1.DiArrows as NumArrows0,
       		d1.DiEnds as NumEnds1, d1.DiArrows as NumArrows1, d2.DiEnds as NumEnds2, d2.DiArrows as NumArrows2, d3.DiEnds as NumEnds3, d3.DiArrows as NumArrows3, d4.DiEnds as NumEnds4, d4.DiArrows as NumArrows4,
       		d5.DiEnds as NumEnds5, d5.DiArrows as NumArrows5, d6.DiEnds as NumEnds6, d6.DiArrows as NumArrows6, d7.DiEnds as NumEnds7, d7.DiArrows as NumArrows7, d8.DiEnds as NumEnds8, d8.DiArrows as NumArrows8
        FROM Entries
        INNER JOIN Qualifications ON EnId = QuId
        INNER JOIN Countries ON EnCountry=CoId AND EnTournament=CoTournament
        INNER JOIN Tournament ON EnTournament=ToId
        INNER JOIN Session on SesTournament=EnTournament AND SesOrder=QuSession AND SesType='Q'
		left join TargetFaces ON TfTournament=EnTournament and EnTargetFace=TfId
        LEFT JOIN ExtraData ON EdId=EnId and EdType='E'
        LEFT JOIN TournamentDistances ON ToType=TdType and TdTournament=ToId AND CONCAT(TRIM(EnDivision),TRIM(EnClass)) LIKE TdClasses
		left join DistanceInformation d1 on d1.DiTournament=EnTournament and d1.DiType and d1.DiSession=QuSession and d1.DiDistance=1
		left join DistanceInformation d2 on d2.DiTournament=EnTournament and d2.DiType and d2.DiSession=QuSession and d2.DiDistance=2
		left join DistanceInformation d3 on d3.DiTournament=EnTournament and d3.DiType and d3.DiSession=QuSession and d3.DiDistance=3
		left join DistanceInformation d4 on d4.DiTournament=EnTournament and d4.DiType and d4.DiSession=QuSession and d4.DiDistance=4
		left join DistanceInformation d5 on d5.DiTournament=EnTournament and d5.DiType and d5.DiSession=QuSession and d5.DiDistance=5
		left join DistanceInformation d6 on d6.DiTournament=EnTournament and d6.DiType and d6.DiSession=QuSession and d6.DiDistance=6
		left join DistanceInformation d7 on d7.DiTournament=EnTournament and d7.DiType and d7.DiSession=QuSession and d7.DiDistance=7
		left join DistanceInformation d8 on d8.DiTournament=EnTournament and d8.DiType and d8.DiSession=QuSession and d8.DiDistance=8
        WHERE EnTournament = {$_SESSION['TourId']} ".($Category ? "AND concat(EnDivision,EnClass)='$Category'" : '')."
        ORDER BY EnFirstName, EnName, CoCode";
	return $MyQuery;
}

function DrawScoreQrPersonal(&$pdf, $Target, $X, $Y, $Group=null, $Width=25, $Border=true) {
    $Oldx=$pdf->getX();
    $Oldy=$pdf->getY();

    $Offset=($Border ? min(5, max(2,$Width/25)) : 0);
    $QrWidth=$Width - 2*$Offset;

    $Data=[
        'scanTrigger' => 'TargetRequest',
        'Target' => $Target,
        'ToCode' => $_SESSION['TourCode'],
    ];

    if(!is_null($Group)) {
        $Data['Group']=$Group;
    }

    // draws a white background square
    if($Border) {
        $pdf->Rect($X, $Y, $Width, $Width, '', array('all'=>array('color'=>100)), array(255));
    }
    $pdf->write2DBarcode(json_encode(['payload' => json_encode($Data)]), 'QRCODE,L', $X+$Offset, $Y+$Offset, $QrWidth, $QrWidth);

    $pdf->setXY($Oldx, $Oldy);
}
