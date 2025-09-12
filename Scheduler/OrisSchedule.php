<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Common/pdf/ResultPDF.inc.php');
require_once('Common/Lib/Obj_RankFactory.php');
checkFullACL(AclCompetition, 'cSchedule', AclReadOnly);

define("CellH",8);

CheckTourSession(true);

$PrintNames=isset($_REQUEST['TeamComponents']);

$pdf=new ResultPDF(get_text('FinalScheduleDetailed', 'Tournament'));

$pdf->SetFont($pdf->FontStd,'B',11);
$pdf->Cell($pdf->getPageWidth() - 2 * IanseoPdf::sideMargin, 8, get_text('FinalScheduleDetailed', 'Tournament'),0,1,'C');
$pdf->SetFont($pdf->FontStd, '', 8);

$Filters=['(FsMatchNo%2=0)', 'FSScheduledDate>0'];
if(!empty($_REQUEST['loc'])) {
    $Filters[]="SesLocation like '".StrSafe_DB($_REQUEST['loc'], true)."%'";
}

if(!empty($_REQUEST['ses'])) {
    if(!is_array($_REQUEST['ses'])) {
        $_REQUEST['ses'] = array(intval($_REQUEST['ses']));
    } else {
        array_walk($_REQUEST['ses'], 'intval');
    }
    $Filters[]="SesOrder IN (".implode(',', $_REQUEST['ses']).")";
}

$Sql = "SELECT CONCAT(FsEvent, '|', FsTeamEvent, '|', FsMatchNo) as SesKey,
		coalesce(SesOrder, 0) SesNumber, coalesce(SesName, '') as SesName, coalesce(SesLocation, '') as SesLocation
	FROM FinSchedule
	left join Session on SesType='F' and SesTournament=FsTournament and (SesEvents='' or find_in_set(concat(FsTeamEvent,FsEvent), SesEvents)) and (CONCAT(FsScheduledDate, ' ', FsScheduledTime) >= SesDtStart AND CONCAT(FsScheduledDate, ' ', FsScheduledTime) < SesDtEnd)
	WHERE FsTournament=".$_SESSION['TourId'] . ' and ' . implode(' and ', $Filters) ."
	ORDER BY SesLocation, FsScheduledDate, FsScheduledTime, FsOdfMatchName";

$q=safe_r_SQL($Sql);
$SessionMatches = array();
while($r=safe_fetch($q)) {
	$SessionMatches[$r->SesNumber][] = $r;
}
$lastSes=0;
$evInSession=0;
$runningDay='';
$sesInDay=0;
$sesCnt=-1;
$pdf->SetFont('','');
$FirstPage=true;
$OldLocation='';
foreach($SessionMatches as $vSes => $items) {
	$NumItems=count($items);
	foreach($items as $i => $r) {
		list($eventCode,$isTeam,$matchNo) = explode('|',$r->SesKey);
		$opts=array('matchno'=>$matchNo, 'events'=>$eventCode);
		$rank=Obj_RankFactory::create(($isTeam ? 'GridTeam':'GridInd'), $opts);
		$rank->read();
		$rankData=$rank->getData();

		$ChangePage=false;
		$Continue='';

		$item=$rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0];
		if($item['tie']!=2 AND $item['oppTie']!=2) {

            $ExtraLineHeight = 0;
            $AthlBorder = 1;
            if ($isTeam and $PrintNames) {
                $ExtraLineHeight = 3 * $rankData["sections"][$eventCode]['meta']['maxTeamPerson'];
                $AthlBorder = 'LTR';
            }

            if (!$i) {
                if (!$pdf->samePage(3, CellH, '', false)
                    or (!$pdf->samePage($NumItems, CellH, '', false))) {
                    // first item in a block... needs at least 3 rows to print the sessions data
                    // not able to split in 3+3
                    $ChangePage = true;
                    if ($runningDay == $item["scheduledDate"]) $Continue = ' (Cont.)';
                }
            } elseif (($NumItems - $i == 4 and !$pdf->samePage(3, CellH, '', false))
                or !$pdf->samePage(($isTeam and $PrintNames) ? $rankData["sections"][$eventCode]['meta']['maxTeamPerson'] : 1, CellH, '', false)) {
                // needs to have room for printing the last 3 rows
                $ChangePage = true;
                $Continue = ' (Cont.)';
            }

            if ($runningDay != $item["scheduledDate"]
                or $ChangePage
                or $OldLocation!=$r->SesLocation) {
                // close the cell...
//                if (!$FirstPage) $pdf->Line(IanseoPdf::sideMargin, $y1 = $pdf->GetY(), IanseoPdf::sideMargin + 25, $y1);

//                $pdf->AddPage();

//                $pdf->SetXY(IanseoPdf::sideMargin, IanseoPdf::topMargin);
                $pdf->SetFont('', 'B');
                $pdf->dy(2);
                $pdf->Cell(30, CellH, get_text("Date", "Tournament") . "/" . get_text("Session"), 1, 0, 'L', 0);
                $pdf->Cell(11, CellH / 2, get_text("Start", "ODF"), 'TLR', 0, 'C', 0, 0, true, false, 'T', 'B');
                $pdf->SetXY($pdf->GetX() - 11, $pdf->GetY() + CellH / 2);
                $pdf->Cell(11, CellH / 2, get_text("Time", "ODF"), 'BLR', 0, 'C', 0, 0, true, false, 'T', 'T');;
                $pdf->SetXY($pdf->GetX(), $pdf->GetY() - CellH / 2);
                $pdf->Cell(39, CellH, get_text("EventFinals"), 1, 0, 'L', 0);

                $pdf->Cell(10, CellH / 2, get_text("RankScoreShort"), 'TLR', 0, 'C', 0, 0, true, false, 'T', 'B');
                $pdf->SetXY($pdf->GetX() - 10, $pdf->GetY() + CellH / 2);
                $pdf->Cell(10, CellH / 2, mb_lcfirst(get_text("Rank")), 'BLR', 0, 'C', 0, 0, true, false, 'T', 'T');
                $pdf->SetXY($pdf->GetX(), $pdf->GetY() - CellH / 2);
                $pdf->Cell(45, CellH, get_text("ParticipantSchedule", "Tournament", "1"), 1, 0, 'L', 0);

                $pdf->Cell(10, CellH / 2, get_text("RankScoreShort"), 'TLR', 0, 'C', 0, 0, true, false, 'T', 'B');
                $pdf->SetXY($pdf->GetX() - 10, $pdf->GetY() + CellH / 2);
                $pdf->Cell(10, CellH / 2, mb_lcfirst(get_text("Rank")), 'BLR', 0, 'C', 0, 0, true, false, 'T', 'T');
                $pdf->SetXY($pdf->GetX(), $pdf->GetY() - CellH / 2);
                $pdf->Cell(45, CellH, get_text("ParticipantSchedule", "Tournament", "2"), 1, 1, 'L', 0);
                $pdf->SetFont('', '');
                if ($runningDay != $item["scheduledDate"]) {
                    $sesInDay = 0;
                } else {
                    $evInSession = -1;
                }

                $runningDay = $item["scheduledDate"];
                $OldLocation=$r->SesLocation;
            }
            $FirstPage = false;
            if ($lastSes != $vSes) {
                $evInSession = 0;
                $sesInDay++;
                $sesCnt++;
                $pdf->Line(IanseoPdf::sideMargin, $y1 = $pdf->GetY(), IanseoPdf::sideMargin + 30, $y1);

            } else {
                $evInSession++;
            }

            $OrgY = $pdf->getY();

            $SessionText = '<b>'. mb_ucfirst(IntlDateFormatter::create(SelectLanguage(), IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, "EEEE, dd MMMM")->format(new DateTime($runningDay))) .'</b>' . $Continue . "<br><br>".
                "<b>" . get_text("Session") . " " . $sesInDay . ":</b><br>".
                ($r->SesLocation ? "<i>{$r->SesLocation}</i><br>" : '').
                $r->SesName;
            if($evInSession == 0) {
                $pdf->MultiCell(30, CellH + $ExtraLineHeight, $SessionText, 'TLR', 'L', 0, 0, '', '', true, 0, true, true, 0);
            } else {
                $pdf->Cell(30, CellH + $ExtraLineHeight,'','LR' . ($evInSession == 0 ? 'T' : ''), 0, 'L', 0);
            }
            $pdf->Cell(11, CellH + $ExtraLineHeight, (new DateTime($item["scheduledTime"]))->format('H:i'), 1, 0, 'C', 0);
            $pdf->Cell(30, CellH + $ExtraLineHeight, $rankData["sections"][$eventCode]["meta"]["eventName"], 1, 0, 'L', 0);
            $pdf->Cell(9, CellH + $ExtraLineHeight, $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["meta"][((($rankData["sections"][$eventCode]["meta"]["elimType"]??0) >=3 AND key($rankData["sections"][$eventCode]["phases"])>$rankData["sections"][$eventCode]["meta"]["firstPhase"]) ? "matchName" : "phaseName")], 1, 0, 'L', 0);

            $Name = (empty($item['odfPath']) or $item[$isTeam ? "countryName" : "athlete"]) ? $item[$isTeam ? "countryName" : "athlete"] : $item['odfPath'];
            $pdf->Cell(10, CellH + $ExtraLineHeight, ($item["qualRank"] ?? ''), 1, 0, 'R', 0);
            $pdf->Cell(37, CellH, $Name, $AthlBorder, 0, 'L', 0);
            $pdf->Cell(8, CellH + $ExtraLineHeight, ($item["countryCode"] ?? ''), 1, 0, 'L', 0);

            $Name = (empty($item['oppOdfPath']) or $item[$isTeam ? "oppCountryName" : "oppAthlete"]) ? $item[$isTeam ? "oppCountryName" : "oppAthlete"] : $item['oppOdfPath'];
            $pdf->Cell(10, CellH + $ExtraLineHeight, ($item["oppQualRank"] ?? ''), 1, 0, 'R', 0);
            $pdf->Cell(37, CellH, $Name, $AthlBorder, 0, 'L', 0);
            $pdf->Cell(8, CellH + $ExtraLineHeight, ($item["oppCountryCode"] ?? ''), 1, 1, 'L', 0);

            if ($isTeam and $PrintNames) {
                $OrgX = $pdf->getX() + 93;
                $Font = $pdf->getFontSizePt();
                $pdf->SetFontSize(8);
                if (!empty($rankData["sections"][$eventCode]['athletes'][$item['teamId']][$item['subTeam']])) {
                    foreach ($rankData["sections"][$eventCode]['athletes'][$item['teamId']][$item['subTeam']] as $k => $Component) {
                        $pdf->setxy($OrgX, 3 * $k + $OrgY + 6);
                        $pdf->Cell(34, 3, $Component['athlete'], '', 0, 'L', 0);
                    }
                }
                $pdf->Line($OrgX - 3, $OrgY + CellH + $ExtraLineHeight, $OrgX + 34, $OrgY + CellH + $ExtraLineHeight);
                $OrgX += 55;
                if (!empty($rankData["sections"][$eventCode]['athletes'][$item['oppTeamId']][$item['oppSubTeam']])) {
                    foreach ($rankData["sections"][$eventCode]['athletes'][$item['oppTeamId']][$item['oppSubTeam']] as $k => $Component) {
                        $pdf->setxy($OrgX, 3 * $k + $OrgY + 6);
                        $pdf->Cell(34, 3, $Component['athlete'], '', 0, 'L', 0);
                    }
                }
                $pdf->Line($OrgX - 3, $OrgY + CellH + $ExtraLineHeight, $OrgX + 34, $OrgY + CellH + $ExtraLineHeight);
                $pdf->SetY($OrgY + CellH + $ExtraLineHeight);
                $pdf->SetFontSize($Font);
            }
            $lastSes = $vSes;
        }
	}
    $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX()+30, $pdf->GetY());
}

$pdf->Output();
