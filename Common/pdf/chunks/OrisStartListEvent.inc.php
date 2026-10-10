<?php

$pdf->SetDataHeader($PdfData->Header, $PdfData->HeaderWidth);
$pdf->setPhase($PdfData->Phase);
$pdf->setDocUpdate($PdfData->LastUpdate);

$OldTarget='';
$First=true;
$OldTeam='#@#@#';
$OldEvent='#@#@#';
$SwitchedToTeam=false;
$IsMixedTeam=false;
foreach($PdfData->Data['Items']??[] as $Group) {
	foreach($Group as $MyRow) {
        // check for old competitions style
        $MyRow->IsTeam=($MyRow->IsTeam??0);
        $MyRow->IsMixedTeam=($MyRow->IsMixedTeam??0);
        if(!$SwitchedToTeam and $MyRow->IsTeam) {
            $SwitchedToTeam=true;
        }
        if(!$IsMixedTeam and $MyRow->IsMixedTeam) {
            // add the header columns
            array_splice($PdfData->Header, 3, 0, ['Gender']);
            array_splice($PdfData->HeaderWidth, 3, 0, [10]);
            $PdfData->HeaderWidth[4]-=10;
            $pdf->SetDataHeader($PdfData->Header, $PdfData->HeaderWidth);
        } else if($IsMixedTeam and !$MyRow->IsMixedTeam) {
            // remove the header columns
            array_splice($PdfData->Header,3,1);
            array_splice($PdfData->HeaderWidth,3,1);
            $PdfData->HeaderWidth[3]+=10;
            $pdf->SetDataHeader($PdfData->Header, $PdfData->HeaderWidth);
        }
        $IsMixedTeam = $MyRow->IsMixedTeam;
		if($OldEvent != $MyRow->IsTeam.$MyRow->EventCode) {
			$pdf->setEvent($MyRow->EventName);
			$pdf->setOrisCode($PdfData->Code, $PdfData->Description);
			$pdf->AddPage();
            if($OldEvent=='#@#@#') {
                $pdf->Bookmark($PdfData->IndexName, 0);
            }
			$OldTeam='#@#@#';
			$OldEvent = $MyRow->IsTeam.$MyRow->EventCode;
		}

		$TgtNo=ltrim(($PdfData->BisTarget && (intval(substr($MyRow->TargetNo,1)) > $PdfData->NumEnd) ? str_pad((substr($MyRow->TargetNo,0,-1)-$PdfData->NumEnd),3,"0",STR_PAD_LEFT) . substr($MyRow->TargetNo,-1,1) . ' bis'  : $MyRow->TargetNo), 'O');

		if($OldTeam != $MyRow->NationCode.$MyRow->NationSubTeam) {
            if($OldTeam!='#@#@#') {
                $pdf->lastY += 3.5;
            }
            $OldTeam = $MyRow->NationCode.$MyRow->NationSubTeam;
			$pdf->SamePage($MyRow->cNumber + ($MyRow->IsTeam ? 2 : 1) , 3.5, $pdf->lastY);

            if($IsMixedTeam) {
                $tmp=array(
                    $MyRow->NationCode,
                    $MyRow->Nation,
                    '#'.ltrim($TgtNo,'0'),
                    $MyRow->EnGender.'§',
                    '',
                    $MyRow->DOB,
                    $MyRow->Athlete
                );
            } else {
                $tmp=array(
                    $MyRow->NationCode,
                    $MyRow->Nation,
                    '#'.ltrim($TgtNo,'0'),
                    ((!$MyRow->IsTeam and $PdfData->IsRanked) ? ($MyRow->Ranking ??  '-').'#' : ''),
                    $MyRow->DOB,
                    $MyRow->Athlete
                );
            }
            if($SwitchedToTeam) {
                if($IsMixedTeam) {
                    $pdf->printDataRow(array(
                        $MyRow->NationCode.'~',
                        $MyRow->Nation . (empty($MyRow->NationSubTeam) ? '' : ' ('.$MyRow->NationSubTeam.')') . '~',
                        '',
                        '',
                        $PdfData->IsRanked ? ($MyRow->TeamRanking ?: '-').'#~' : '',
                        '',
                        ''
                    ));
                } else {
                    $pdf->printDataRow(array(
                        $MyRow->NationCode.'~',
                        $MyRow->Nation . (empty($MyRow->NationSubTeam) ? '' : ' ('.$MyRow->NationSubTeam.')') . '~',
                        '',
                        $PdfData->IsRanked ? ($MyRow->TeamRanking ?: '-').'#~' : '',
                        '',
                        ''
                    ));
                }
                $tmp[0]='';
                $tmp[1]='';
            }
            $pdf->printDataRow($tmp);
		} elseif($IsMixedTeam) {
            $pdf->printDataRow(array(
                '',
                '',
                '#'.ltrim($TgtNo,'0'),
                $MyRow->EnGender.'§',
                '',
                $MyRow->DOB,
                $MyRow->Athlete
            ));
        } else {
			$pdf->printDataRow(array(
                "",
                "",
                '#'.ltrim($TgtNo,'0'),
                ((!$MyRow->IsTeam and $PdfData->IsRanked) ? ($MyRow->Ranking ??  '-').'#' : ''),
                $MyRow->DOB,
                $MyRow->Athlete
			));
		}

		if(!isset($PdfData->HTML)) continue;

        $EvCode=($MyRow->IsTeam?'T: ':'I: ').$MyRow->EventCode;

		$PdfData->HTML['Events'][$EvCode]['Code']=$MyRow->EventCode;
		$PdfData->HTML['Events'][$EvCode]['Description']=$MyRow->EventName;
		// may go for several events...
		if(empty($PdfData->HTML['Events'][$EvCode]['Countries'][$MyRow->NationCode])) {
			$PdfData->HTML['Events'][$EvCode]['Countries'][$MyRow->NationCode]=array();
		}
		$PdfData->HTML['Events'][$EvCode]['Countries'][$MyRow->NationCode][]=array(
				$MyRow->NationCode,
				$MyRow->Nation,
				$TgtNo,
				$MyRow->Ranking,
				$MyRow->DOB,
            ($IsMixedTeam?'('.$MyRow->EnGender.')&nbsp;':'').$MyRow->Athlete,
					$MyRow->SesName ? $MyRow->SesName : $PdfData->Data['Fields']['Session'].' '. $MyRow->Session
				);
	}
}

