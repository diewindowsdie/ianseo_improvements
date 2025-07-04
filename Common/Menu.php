<?php
require_once('Common/Lib/Fun_Modules.php');
	define ('ClubTeam',false);
	define ('CasTeam',false);
	define('MENU_DIVIDER', '---');

	$Path_DIR = $CFG->DOCUMENT_PATH . 'Common/phplayersmenu/';
	$Path_WWW = $CFG->ROOT_DIR . 'Common/phplayersmenu/';

// definisce globali per JS la variabile di configurazione WebDir per reindirizzare correttamente i files...
// la variabile ha la forma /path/to/ianseo/root/dir/

echo "<script>var WebDir = '$CFG->ROOT_DIR';</script>";

echo '<link rel="icon" href="'.$CFG->ROOT_DIR.'favicon.ico" sizes="16x16 32x32 48x48 64x64" type="image/vnd.microsoft.icon"/>';

require_once $Path_DIR . 'lib/PHPLIB.php';

function get_which_menu($on=false) {
	global $CFG;
	$ret=array();

	// subsites have very specific menu!
	if(!empty($_SESSION['ShortMenu'])) {
		return $_SESSION['ShortMenu'];
	}

	// Start with minimal structure
    $acl = actualACL();
	if($on) {
		if($_SESSION['TourType']==48) {
			// RUN ARCHERY...
			return get_which_run_menu($on);
		}

        /** COMPETITION MENU **/
        $ret['COMP'][] = get_text('MenuLM_Competition') . '';
        $ret['COMP'][] = get_text('MenuLM_Open') . '|' . $CFG->ROOT_DIR . 'index.php';
        $ret['COMP'][] = get_text('MenuLM_Close') . '|' . $CFG->ROOT_DIR . 'Common/TourOff.php';
        if (subFeatureAcl($acl,AclRoot) == AclReadWrite) {
            $ret['COMP'][] = MENU_DIVIDER;
            $ret['COMP'][] = get_text('MenuLM_Delete') . '|' . $CFG->ROOT_DIR . 'Tournament/TourDelete.php';
        }
        $ret['COMP'][] = MENU_DIVIDER;
        $ret['COMP'][] = get_text('MenuLM_View Competition Info') . '|' . $CFG->ROOT_DIR . 'Main.php';
        if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite OR subFeatureAcl($acl,AclCompetition,'cSchedule') == AclReadWrite) {
            $ret['COMP']['INFO'][] = get_text('MenuLM_Edit Competition Info');
        }
        if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite) {
            $ret['COMP']['INFO'][] = get_text('MenuLM_Competition Info') . '|' . $CFG->ROOT_DIR . 'Tournament/index.php';
            $ret['COMP']['INFO'][] = get_text('MenuLM_Images') . '|' . $CFG->ROOT_DIR . 'Tournament/ManLogo.php';
        }
        if(subFeatureAcl($acl,AclCompetition,'cSchedule') == AclReadWrite) {
            $ret['COMP']['INFO'][] = get_text('MenuLM_Session') . '|' . $CFG->ROOT_DIR . 'Tournament/ManSessions_kiss.php';
        }
        if(subFeatureAcl($acl,AclCompetition,'cData') >= AclReadOnly) {
            $ret['COMP']['INFO'][] = get_text('MenuLM_Field Crew') . '|' . $CFG->ROOT_DIR . 'Tournament/ManStaffField.php';
        }
        if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite) {
            $ret['COMP']['INFO'][] = get_text('MenuLM_Divisions and Classes') . '|' . $CFG->ROOT_DIR . 'Tournament/ManDivClass.php';
            $ret['COMP']['INFO'][] = get_text('MenuLM_SubClasses') . '|' . $CFG->ROOT_DIR . 'Tournament/ManSubClass.php';
            $ret['COMP']['INFO'][] = get_text('MenuLM_Distances') . '|' . $CFG->ROOT_DIR . 'Tournament/ManDistances.php';
            $ret['COMP']['INFO'][] = get_text('MenuLM_Targets') . '|' . $CFG->ROOT_DIR . 'Tournament/ManTargets.php';
        }
        if (subFeatureAcl($acl,AclCompetition,'cSchedule') >= AclReadOnly) {
            $ret['COMP'][] = MENU_DIVIDER;
            $ret['COMP'][] = get_text('MenuLM_Scheduling') . '|' . $CFG->ROOT_DIR . 'Scheduler/index.php';
        }
        if (subFeatureAcl($acl,AclCompetition,'cSchedule') == AclReadWrite) {
            $ret['COMP'][] = get_text('MenuLM_Training') . '|' . $CFG->ROOT_DIR . 'Final/ManTraining.php';
        }
        if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite OR subFeatureAcl($acl,AclCompetition,'cSchedule') == AclReadWrite) {
            $ret['COMP'][] = MENU_DIVIDER;
            $ret['COMP']['FINI'][] = get_text('MenuLM_Individual Final Setup') . '';
        }
        if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite) {
            $ret['COMP']['FINI'][] = get_text('MenuLM_Manage Events') . '|' . $CFG->ROOT_DIR . 'Final/Individual/ListEvents.php';
            $ret['COMP']['FINI'][] = get_text('MenuLM_Archers on Targets') . '|' . $CFG->ROOT_DIR . 'Final/PhaseDetails.php';
            $ret['COMP']['FINI'][] = get_text('MenuLM_Arr4Set') . '|' . $CFG->ROOT_DIR . 'Final/PhaseDetails.php?option=ArrowPhase';
        }
        if (subFeatureAcl($acl,AclCompetition,'cSchedule') == AclReadWrite) {
            $ret['COMP']['FINI'][] = get_text("MenuLM_Target's Number") . '|' . $CFG->ROOT_DIR . 'Final/Individual/ManTarget.php';
            $ret['COMP']['FINI'][] = get_text('MenuLM_Scheduling') . '|' . $CFG->ROOT_DIR . 'Final/Individual/ManSchedule.php';
            $ret['COMP']['FINI'][] = get_text('MenuLM_Training') . '|' . $CFG->ROOT_DIR . 'Final/ManTraining.php';
            if (!defined('hideRunning'))
                $ret['COMP']['FINI'][] = get_text('MenuLM_RunningEvents') . '|' . $CFG->ROOT_DIR . 'Final/RunningEvent.php';
        }
        if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite OR subFeatureAcl($acl,AclCompetition,'cSchedule') == AclReadWrite) {
            $ret['COMP']['FINT'][] = get_text('MenuLM_Team Final Setup') . '';
        }
        if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite) {
            $ret['COMP']['FINT'][] = get_text('MenuLM_Manage Events') . '|' . $CFG->ROOT_DIR . 'Final/Team/ListEvents.php';
            $ret['COMP']['FINT'][] = get_text('MenuLM_Arr4Set') . '|' . $CFG->ROOT_DIR . 'Final/PhaseDetails.php?team=1&option=ArrowPhase';
        }
        if(subFeatureAcl($acl,AclCompetition,'cSchedule') == AclReadWrite) {
            $ret['COMP']['FINT'][] = get_text("MenuLM_Target's Number") . '|' . $CFG->ROOT_DIR . 'Final/Team/ManTarget.php';
            $ret['COMP']['FINT'][] = get_text('MenuLM_Scheduling') . '|' . $CFG->ROOT_DIR . 'Final/Team/ManSchedule.php';
            $ret['COMP']['FINT'][] = get_text('MenuLM_Training') . '|' . $CFG->ROOT_DIR . 'Final/ManTraining.php';
            if (!defined('hideRunning'))
                $ret['COMP']['FINT'][] = get_text('MenuLM_RunningEvents') . '|' . $CFG->ROOT_DIR . 'Final/RunningEvent.php';
        }
        if($_SESSION['HasRobin']) {
            if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite OR subFeatureAcl($acl,AclCompetition,'cSchedule') == AclReadWrite) {
                $ret['COMP']['ROBIN'][] = get_text('RoundRobinSetup', 'RoundRobin') . '';
            }
            if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite) {
                $ret['COMP']['ROBIN'][] = get_text('RoundRobinSetup', 'RoundRobin') . '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/Setup.php';
                $ret['COMP']['ROBIN'][] = get_text('GroupAssignment', 'RoundRobin') . '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/Grouping.php';
            }
            if(subFeatureAcl($acl,AclCompetition,'cSchedule') == AclReadWrite) {
                $ret['COMP']['ROBIN'][] = get_text('TargetAssignment', 'RoundRobin') . '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/Targets.php';
            }
        }
        if(subFeatureAcl($acl,AclCompetition,'cSchedule') >= AclReadOnly) {
            $ret['COMP'][] = get_text('MenuLM_Final Field of Play Layout') . '|' . $CFG->ROOT_DIR . 'Final/FopSetup.php|||PrintOut';
            $ret['COMP'][] = get_text('MenuLM_FinalFOPChoose') . '|' . $CFG->ROOT_DIR . 'Final/FopSetup-Choose.php';
        }
        $ret['COMP'][] = MENU_DIVIDER;
        if (subFeatureAcl($acl,AclRoot) == AclReadWrite) {
            $ret['COMP']['BLOCK'][] = get_text('MenuLM_Security') . '|' . $CFG->ROOT_DIR . 'Tournament/BlockTour.php';
            $ret['COMP']['BLOCK'][] = get_text('MenuLM_Lock setup') . '|' . $CFG->ROOT_DIR . 'Tournament/BlockTour.php';
            $ret['COMP']['BLOCK'][] = get_text('MenuLM_Lock manage') . '|' . $CFG->ROOT_DIR . 'Tournament/AccessControlList/index.php';
        }

        if (subFeatureAcl($acl,AclCompetition,'cFinalReport') >= AclReadOnly) {
            $ret['COMP']['REPT'][] = get_text('MenuLM_Final report');
            $ret['COMP']['REPT'][] = get_text('MenuLM_PrintProtocol') . '|' . $CFG->ROOT_DIR . 'Tournament/FinalReport/Protocol.php|||PrintOut';
            if (subFeatureAcl($acl,AclCompetition,'cFinalReport') == AclReadWrite) {
                $ret['COMP']['REPT'][] = get_text('MenuLM_EditFinalReport') . '|' . $CFG->ROOT_DIR . 'Tournament/FinalReport/index.php';
            }
            $ret['COMP']['REPT'][] = get_text('MenuLM_PrintFinalReport') . '|' . $CFG->ROOT_DIR . 'Tournament/FinalReport/PDFReport.php|||PrintOut';
            $ret['COMP']['REPT'][] = get_text('MenuLM_CheckList') . '|' . $CFG->ROOT_DIR . 'Tournament/FinalReport/PDFCheckList.php|||PrintOut';
            $ret['COMP'][] = MENU_DIVIDER;
        }
        if (subFeatureAcl($acl,AclCompetition,'cExport') >= AclReadOnly) {
            $ret['COMP']['EXPT'][] = get_text('MenuLM_Export') . '|' . $CFG->ROOT_DIR . 'Tournament/TournamentExport.php?Complete=1';
            $ret['COMP']['EXPT'][] = get_text('Export2Fitarco', 'Tournament') . '|' . $CFG->ROOT_DIR . 'Tournament/Export2Fitarco.php';
            $ret['COMP']['EXPT'][] = get_text('MenuLM_Export Tournament') . '|' . $CFG->ROOT_DIR . 'Tournament/TournamentExport.php';
            $ret['COMP']['EXPT'][] = get_text('MenuLM_Export Tournament Photo') . '|' . $CFG->ROOT_DIR . 'Tournament/TournamentExport.php?Complete=1';
            if (ProgramRelease == 'HEAD') $ret['COMP']['EXPT'][] = get_text('MenuLM_Export Entries') . '|' . $CFG->ROOT_DIR . 'Partecipants/EntriesExchange.php';
            $ret['COMP'][] = MENU_DIVIDER;
        }
        if (subFeatureAcl($acl,AclInternetPublish) == AclReadWrite) {
            $ret['COMP']['SEND'][] = get_text('MenuLM_Send to ianseo.net');
            if(subFeatureAcl($acl,AclInternetPublish,'ipCredentials') == AclReadWrite) {
                $ret['COMP']['SEND'][] = get_text('MenuLM_Set on-line codes') . '|' . $CFG->ROOT_DIR . 'Tournament/SetCredentials.php';
            }
            if(subFeatureAcl($acl,AclInternetPublish,'ipSend') == AclReadWrite) {
                $ret['COMP']['SEND'][] = get_text('MenuLM_Send') . '|' . $CFG->ROOT_DIR . 'Tournament/UploadResults.php';
            }
            $ret['COMP'][] = MENU_DIVIDER;
        }
        $ret['COMP'][] = get_text('MenuLM_Select Language') . '|' . $CFG->ROOT_DIR . 'Language/index.php';
        if (substr(SelectLanguage(), 0, 2) != 'en') $ret['COMP'][] = 'Select Language|' . $CFG->ROOT_DIR . 'Language/index.php';


        /** PARTICIPANTS MENU **/
        $ret['PART'][] = get_text('MenuLM_Participants');
        if (subFeatureAcl($acl,AclParticipants,'pEntries') == AclReadWrite) {
            $ret['PART'][] = get_text('MenuLM_List (Simple)') . '|' . $CFG->ROOT_DIR . 'Partecipants/index.php';
            $ret['PART'][] = get_text('MenuLM_Partecipant List (Advanced)') . '|' . $CFG->ROOT_DIR . 'Partecipants-exp/index.php';
            $ret['PART'][] = get_text('MenuLM_Athletes Participation to Ind/Team Event') . '|' . $CFG->ROOT_DIR . 'Partecipants/ManEventAccess.php';
        }
        if (subFeatureAcl($acl,AclParticipants,'pAdvancedEntries') == AclReadWrite) {
            $ret['PART'][] = get_text('MenuLM_Athlete Status Management') . '|' . $CFG->ROOT_DIR . 'Partecipants/ManStatus.php';
            $ret['PART'][] = get_text('MenuLM_IrmManagement') . '|' . $CFG->ROOT_DIR . 'Partecipants/ManIrmStatus.php';
        }
        if (subFeatureAcl($acl,AclParticipants,'pTarget') == AclReadWrite) {
            $ret['PART']['TARG'][] = get_text('MenuLM_Target') . '';
            $ret['PART']['TARG'][] = get_text('MenuLM_Manual Assignment') . '|' . $CFG->ROOT_DIR . 'Partecipants/SetTarget_default.php?Ses=*';
            $ret['PART']['TARG'][] = get_text('MenuLM_Draw') . '|' . $CFG->ROOT_DIR . 'Partecipants/SetTarget_auto.php';
            $ret['PART']['TARG'][] = MENU_DIVIDER;
        }
        if (subFeatureAcl($acl,AclParticipants,'pAdvancedTarget') == AclReadWrite) {
            $ret['PART']['TARG'][] = get_text('MenuLM_MoveSession') . '|' . $CFG->ROOT_DIR . 'Partecipants/MoveSession.php';
            $ret['PART']['TARG'][] = get_text('MenuLM_MoveTarget') . '|' . $CFG->ROOT_DIR . 'Partecipants/MoveTarget.php';
            $ret['PART']['TARG'][] = get_text('MenuLM_TargetFromRank') . '|' . $CFG->ROOT_DIR . 'Partecipants/TargetFromRank.php';
            $ret['PART']['TARG'][] = get_text('MenuLM_DeleteTarget') . '|' . $CFG->ROOT_DIR . 'Partecipants/DeleteTarget.php';
            $ret['PART'][] = MENU_DIVIDER;
        }
        if (subFeatureAcl($acl,AclAccreditation,'') >= AclReadOnly) {
            $ret['PART']['ACCR'][] = get_text('MenuLM_Accreditation') . '';
        }
        if(subFeatureAcl($acl,AclAccreditation,'acStandard') == AclReadWrite) {
            $ret['PART']['ACCR'][] = get_text('MenuLM_Accreditation') . '|' . $CFG->ROOT_DIR . 'Accreditation/index.php';
            $ret['PART']['ACCR'][] = get_text('TakePicture', 'Tournament') . '|' . $CFG->ROOT_DIR . 'Accreditation/AccreditationPicture.php';
            $ret['PART']['ACCR'][] = MENU_DIVIDER;
        }
        if(subFeatureAcl($acl,AclAccreditation,'acSetup') == AclReadWrite) {
            $ret['PART']['ACCR'][] = get_text('MenuLM_Fees setup') . '|' . $CFG->ROOT_DIR . 'Accreditation/ManagePrices.php';
            $ret['PART']['ACCR'][] = get_text('MenuLM_Athlets fees status') . '|' . $CFG->ROOT_DIR . 'Accreditation/ManagePays.php';
            $ret['PART']['ACCR'][] = get_text('MenuLM_Accreditation colors management') . '|' . $CFG->ROOT_DIR . 'Accreditation/Colors.php';
            $ret['PART']['ACCR'][] = MENU_DIVIDER;
        }
        if(subFeatureAcl($acl,AclAccreditation,'acStandard') >= AclReadOnly) {
            $ret['PART']['ACCR'][] = get_text('MenuLM_PrintBadges') . '|' . $CFG->ROOT_DIR . 'Accreditation/IdCards.php';
            $ret['PART']['ACCR'][] = get_text('MenuLM_Printout') . '|' . $CFG->ROOT_DIR . 'Accreditation/PrintOut.php';
            $ret['PART'][] = MENU_DIVIDER;
        }
        if(subFeatureAcl($acl,AclParticipants,'pAdvancedEntries') == AclReadWrite) {
            $ret['PART']['SYNC'][] = get_text('MenuLM_Athletes Sync.') . '|' . $CFG->ROOT_DIR . 'Partecipants/LookupTableLoad.php';
            $ret['PART']['SYNC'][] = get_text('MenuLM_Athletes Sync.') . '|' . $CFG->ROOT_DIR . 'Partecipants/LookupTableLoad.php';
            $ret['PART']['SYNC'][] = get_text('MenuLM_ListLoad') . '|' . $CFG->ROOT_DIR . 'Partecipants/ListLoad.php';
            $ret['PART']['SYNC'][] = get_text('MenuLM_AthletesDiscrepancies') . '|' . $CFG->ROOT_DIR . 'Partecipants/Discrepancies.php';
            $ret['PART']['SYNC'][] = MENU_DIVIDER;
            $ret['PART']['SYNC'][] = get_text('MenuLM_ChangeNationsNames') . '|' . $CFG->ROOT_DIR . 'Partecipants/ChangeNationsNames.php';
            $ret['PART']['SYNC'][] = get_text('TourCountries', 'Tournament') . '|' . $CFG->ROOT_DIR . 'Tournament/Countries.php';
            $ret['PART'][] = MENU_DIVIDER;
        }
        if(subFeatureAcl($acl,AclParticipants,'pEntries') >= AclReadOnly) {
            $ret['PART'][] = get_text('MenuLM_Statistics') . '|' . $CFG->ROOT_DIR . 'Partecipants/PrintOutStat.php';
            $ret['PART'][] = MENU_DIVIDER;
            $ret['PART'][] = get_text('MenuLM_Printout') . '|' . $CFG->ROOT_DIR . 'Partecipants/PrintOut.php';
        }


		/** QUALIFICATIONS MENU **/
		$ret['QUAL'][] = get_text('MenuLM_Qualification') .'';
        if (subFeatureAcl($acl,AclQualification) == AclReadWrite) {
            $ret['QUAL']['SCOR'][] = get_text('MenuLM_Input Score');
            $ret['QUAL']['SCOR'][] = get_text('MenuLM_Standard Table') . '|' . $CFG->ROOT_DIR . 'Qualification/index.php';
            $ret['QUAL']['SCOR'][] = get_text('MenuLM_Extended Table') . '|' . $CFG->ROOT_DIR . 'Qualification/index_all.php';
            $ret['QUAL']['SCOR'][] = get_text('MenuLM_Arrow by Arrow (Advanced user)') . '|' . $CFG->ROOT_DIR . 'Qualification/WriteArrows.php';
            $ret['QUAL']['SCOR'][] = get_text('MenuLM_Arrow by Arrow (Scorecards)') . '|' . $CFG->ROOT_DIR . 'Qualification/WriteScoreCard.php';
            $ret['QUAL']['SCOR'][] = MENU_DIVIDER;
            $ret['QUAL']['SCOR'][] = get_text('MenuLM_Check Data Update') . '|' . $CFG->ROOT_DIR . 'Qualification/CheckTargetUpdate.php';
            $ret['QUAL']['SCOR'][] = get_text('SnapshotConf') . '|' . $CFG->ROOT_DIR . 'Qualification/SnapshotConf.php';
            $ret['QUAL'][] = MENU_DIVIDER;
        }
        if (subFeatureAcl($acl,AclQualification) >= AclReadOnly) {
            $ret['QUAL'][] = get_text('MenuLM_Export Text File') . '|' . $CFG->ROOT_DIR . 'Qualification/ExportTSV.php';
            $ret['QUAL'][] = MENU_DIVIDER;
            $ret['QUAL'][] = get_text('MenuLM_Scorecard Printout') . '|' . $CFG->ROOT_DIR . 'Qualification/PrintScore.php';
            $ret['QUAL'][] = get_text('MenuLM_NewBacknumbers') . '|' . $CFG->ROOT_DIR . 'Accreditation/IdCards.php?CardType=Q';
            $ret['QUAL'][] = MENU_DIVIDER;
            $ret['QUAL'][] = get_text('MenuLM_Personal Rank') . '|' . $CFG->ROOT_DIR . 'Qualification/RankPersonal1.php';
            $ret['QUAL'][] = MENU_DIVIDER;
            if($_SESSION['TourType']>=6 AND $_SESSION['TourType']<=8 AND $_SESSION['TourLocRule']=='IT') {
                $ret['QUAL'][] = get_text('SubClassRank','Tournament') . '|' . $CFG->ROOT_DIR . 'Qualification/PrnIndividual.php?SubClassRank=on&SubClassClassRank=on|||PrintOut';
            }
            $ret['QUAL'][] = get_text('MenuLM_Category Result List') . '|' . $CFG->ROOT_DIR . 'Qualification/PrintOut.php';
            $ret['QUAL'][] = get_text('MenuLM_Qualification Round') . '|' . $CFG->ROOT_DIR . 'Qualification/PrintOutAbs.php';
        }

		/** ELIMINATION MENU **/
		if($_SESSION['MenuElimDo']) {
			$ret['ELIM'][] = get_text('MenuLM_Eliminations') .'';
            if (subFeatureAcl($acl,AclEliminations,'') == AclReadWrite) {
                $tmp = get_text('MenuLM_Check shoot-off before eliminations') . '';
                if ($_SESSION['MenuElim1']) {
                    $tmp .= ' <b class="ShootOffMenu">(Round 1: ' . implode('-', $_SESSION['MenuElim1']) . ')</b>';
                }
                if ($_SESSION['MenuElim2']) {
                    $tmp .= ' <b class="ShootOffMenu">(Round 2: ' . implode('-', $_SESSION['MenuElim2']) . ')</b>';
                }
                $tmp .= '|' . $CFG->ROOT_DIR . 'Final/Individual/AbsIndividual.php';
                $ret['ELIM'][] = $tmp;
                $ret['ELIM'][] = MENU_DIVIDER;
            }
            if (subFeatureAcl($acl,AclEliminations,'') >= AclReadOnly) {
                $ret['ELIM'][] = get_text('MenuLM_Scorecard Printout') . '|' . $CFG->ROOT_DIR . 'Elimination/PrintScore.php';
                $ret['ELIM'][] = get_text('MenuLM_NewBacknumbers') . '|' . $CFG->ROOT_DIR . 'Accreditation/IdCards.php?CardType=E';
                $ret['ELIM'][] = MENU_DIVIDER;
            }
            if (subFeatureAcl($acl,AclEliminations,'') == AclReadWrite) {
                $ret['ELIM'][] = get_text('MenuLM_Target Assignment') . '|' . $CFG->ROOT_DIR . 'Elimination/SetTarget.php';
            }
			if($_SESSION['MenuElimOn']) {
                if (subFeatureAcl($acl,AclEliminations,'') == AclReadWrite) {
                    $ret['ELIM']['SCOR'][] = get_text('MenuLM_Input Score');
                    $ret['ELIM']['SCOR'][] = get_text('MenuLM_Standard Table') . '|' . $CFG->ROOT_DIR . 'Elimination/index.php';
                    $ret['ELIM']['SCOR'][] = get_text('MenuLM_Arrow by Arrow (Advanced user)') . '|' . $CFG->ROOT_DIR . 'Elimination/WriteArrows.php';
                    $ret['ELIM']['SCOR'][] = get_text('MenuLM_Arrow by Arrow (Scorecards)') . '|' . $CFG->ROOT_DIR . 'Elimination/WriteScoreCard.php';
                    $ret['ELIM'][] = MENU_DIVIDER;
                }
                if (subFeatureAcl($acl,AclEliminations,'') >= AclReadOnly) {
                    $ret['ELIM'][] = get_text('MenuLM_Printout') . '|' . $CFG->ROOT_DIR . 'Elimination/PrintOut.php';
                }
			}
		}

		if($_SESSION['MenuElimPoolDo']) {
			$ret['ELIMP'][] = get_text('MenuLM_Pools') .'';
            if (subFeatureAcl($acl,AclEliminations,'') == AclReadWrite) {
                $tmp = get_text('MenuLM_Check shoot-off before eliminations') . '';
                if ($_SESSION['MenuElimPool']) {
                    $tmp .= ' <b class="ShootOffMenu">(Pool: ' . implode('-', $_SESSION['MenuElimPool']) . ')</b>';
                }
                $tmp .= '|' . $CFG->ROOT_DIR . 'Final/Individual/AbsIndividual.php';
                $ret['ELIMP'][] = $tmp;
                $ret['ELIMP'][] = MENU_DIVIDER;
            }
            if (subFeatureAcl($acl,AclEliminations,'') >= AclReadOnly) {
                $ret['ELIMP'][] = get_text('MenuLM_Scorecard Printout') . '|' . $CFG->ROOT_DIR . 'Final/Individual/PrintScore.php';
                $ret['ELIMP'][] = get_text('MenuLM_NewBacknumbers') . '|' . $CFG->ROOT_DIR . 'Accreditation/IdCards.php?CardType=I';
                $ret['ELIMP'][] = MENU_DIVIDER;
            }
            if (subFeatureAcl($acl,AclEliminations,'') == AclReadWrite) {
                $ret['ELIMP'][] = get_text('MenuLM_Target Assignment') . '|' . $CFG->ROOT_DIR . 'Elimination/SetTarget.php';
            }
			if($_SESSION['MenuElimOn']) {
                if (subFeatureAcl($acl,AclEliminations,'') == AclReadWrite) {
                    $ret['ELIMP'][] = get_text('MenuLM_Spotting') . '|' . $CFG->ROOT_DIR . 'Final/Spotting.php';
                    $ret['ELIMP'][] = MENU_DIVIDER;
                    $ret['ELIMP']['SCOR'][] = get_text('MenuLM_Input Score');
                    $ret['ELIMP']['SCOR'][] = get_text('MenuLM_Data insert (Table view)') . '|' . $CFG->ROOT_DIR . 'Final/Individual/InsertPoint1.php';
                }
                if (subFeatureAcl($acl,AclEliminations,'') >= AclReadOnly) {
                    $ret['ELIMP'][] = get_text('MenuLM_Printout') . '|' . $CFG->ROOT_DIR . 'Elimination/PrintOut.php';
                }
			}
		}

		/* Round Robin Menu */
		if($_SESSION['HasRobin']) {
			$ret['ROBIN'][] = get_text('R-Session', 'Tournament');
			if (subFeatureAcl($acl,AclRobin,'') == AclReadWrite) {
				$tmp = get_text('DifferentEventSoManagementButton', 'RoundRobin') . '';
				if ($_SESSION['MenuRobin']) {
					$tmp .= ' <b class="ShootOffMenu">(';
					if(isset($_SESSION['MenuRobin'][0])) {
						$tmp.=get_text('Individual').' '.implode('-', $_SESSION['MenuRobin'][0]);
					}
					if(isset($_SESSION['MenuRobin'][1])) {
						$tmp.=(isset($_SESSION['MenuRobin'][0]) ? ' / ' : '').get_text('Team').' '.implode('-', $_SESSION['MenuRobin'][1]);
					}
					$tmp .= ')</b>';
				}
				$tmp .= '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/AbsRobin.php';
				$ret['ROBIN'][] = $tmp;
				$ret['ROBIN'][] = get_text('TargetAssignment', 'RoundRobin') . '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/Targets.php';
				$ret['ROBIN'][] = get_text('GroupAssignment', 'RoundRobin') . '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/Grouping.php';
			}
			if (subFeatureAcl($acl,AclRobin,'') >= AclReadOnly) {
				// $ret['ROBIN'][] = get_text('MenuLM_PrnShootOff') . '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/PrnShootoff.php|||PrintOut';
				$ret['ROBIN'][] = MENU_DIVIDER;
				// $ret['ROBIN'][] = get_text('MenuLM_Export Text File') . '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/ExportTSV.php';
				// $ret['ROBIN'][] = MENU_DIVIDER;
				$ret['ROBIN'][] = get_text('MenuLM_Scorecard Printout') . '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/PrintScore.php';
				// $ret['ROBIN'][] = get_text('MenuLM_NewBacknumbers') . '|' . $CFG->ROOT_DIR . 'Accreditation/IdCards.php?CardType=R';
			}
			if($_SESSION['MenuRobinOn']) {
				if (subFeatureAcl($acl,AclRobin,'') == AclReadWrite) {
					$ret['ROBIN'][] = MENU_DIVIDER;
					// $ret['ROBIN'][] = get_text('MenuLM_Change Components') . '|' . $CFG->ROOT_DIR . 'Final/Team/ChangeComponents.php';
					// $ret['ROBIN'][] = MENU_DIVIDER;
					$ret['ROBIN'][] = get_text('MenuLM_Data insert (Table view)') . '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/InsertPoint.php';
					// $ret['ROBIN'][] = get_text('MenuLM_Arrow by Arrow (Advanced user)') . '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/WriteArrows.php';
					$ret['ROBIN'][] = get_text('MenuLM_Swaps').'|'.$CFG->ROOT_DIR.'Final/SwapMatches.php';
					$ret['ROBIN'][] = get_text('MenuLM_Spotting') . '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/Spotting.php';
				}
				if (subFeatureAcl($acl,AclRobin,'') >= AclReadOnly) {
					$ret['ROBIN'][] = MENU_DIVIDER;
					$ret['ROBIN'][] = get_text('MenuLM_Printout') . '|' . $CFG->ROOT_DIR . 'Modules/RoundRobin/PrintOut.php';
				}
			}
		}

		/** INDIVIDUAL FINAL MENU **/
		if($_SESSION['MenuFinIDo']) {
			$ret['FINI'][] = get_text('MenuLM_Individual Finals') .'';
            if(subFeatureAcl($acl,AclIndividuals,'') == AclReadWrite) {
                $tmp = get_text('MenuLM_Check shoot-off before final phases') . '';
                if ($_SESSION['MenuFinI']) {
                    $tmp .= ' <b class="ShootOffMenu">(' . implode('-', $_SESSION['MenuFinI']) . ')</b>';
                }
                $tmp .= '|' . $CFG->ROOT_DIR . 'Final/Individual/AbsIndividual.php';
                $ret['FINI'][] = $tmp;
            }
            if(subFeatureAcl($acl,AclIndividuals,'') >= AclReadOnly) {
                $ret['FINI'][] = get_text('MenuLM_PrnShootOff') . '|' . $CFG->ROOT_DIR . 'Qualification/PrnShootoff.php|||PrintOut';
                $ret['FINI'][] = MENU_DIVIDER;
                $ret['FINI'][] = get_text('MenuLM_Export Text File') . '|' . $CFG->ROOT_DIR . 'Final/ExportTSV.php';
                $ret['FINI'][] = MENU_DIVIDER;
                $ret['FINI'][] = get_text('MenuLM_Scorecard Printout') . '|' . $CFG->ROOT_DIR . 'Final/Individual/PrintScore.php';
// 			$ret['FINI'][] = get_text('MenuLM_Back Number Printout') .'|'.$CFG->ROOT_DIR.'Final/Individual/PrintBackNo.php';
                $ret['FINI'][] = get_text('MenuLM_NewBacknumbers') . '|' . $CFG->ROOT_DIR . 'Accreditation/IdCards.php?CardType=I';
            }
			if($_SESSION['MenuFinIOn']) {
                if(subFeatureAcl($acl,AclIndividuals,'') == AclReadWrite) {
                    $ret['FINI'][] = MENU_DIVIDER;
                    $ret['FINI'][] = get_text('MenuLM_Data insert (Bracket view)') . '|' . $CFG->ROOT_DIR . 'Final/Individual/InsertPoint_Bra.php';
                    $ret['FINI'][] = get_text('MenuLM_Data insert (Table view)') . '|' . $CFG->ROOT_DIR . 'Final/Individual/InsertPoint1.php';
                    $ret['FINI'][] = get_text('MenuLM_Arrow by Arrow (Advanced user)') . '|' . $CFG->ROOT_DIR . 'Final/WriteArrows.php';
                    $ret['FINI'][] = get_text('MenuLM_Swaps').'|'.$CFG->ROOT_DIR.'Final/SwapMatches.php';
                    $ret['FINI'][] = get_text('MenuLM_Spotting') . '|' . $CFG->ROOT_DIR . 'Final/Spotting.php';
                }
                if(subFeatureAcl($acl,AclIndividuals,'') >= AclReadOnly) {
                    $ret['FINI'][] = MENU_DIVIDER;
                    $ret['FINI'][] = get_text('MenuLM_Printout') . '|' . $CFG->ROOT_DIR . 'Final/PrintOut.php';
                }
			}
		}

		/** TEAM FINAL MENU **/
		if($_SESSION['MenuFinTDo']) {
            $ret['FINT'][] = get_text('MenuLM_Team Finals') . '';
            if (subFeatureAcl($acl,AclTeams,'') == AclReadWrite) {
                $tmp = get_text('MenuLM_Check shoot-off before final phases') . '';
                if ($_SESSION['MenuFinT']) {
                    $tmp .= ' <b class="ShootOffMenu">(' . implode('-', $_SESSION['MenuFinT']) . ')</b>';
                }
                $tmp .= '|' . $CFG->ROOT_DIR . 'Final/Team/AbsTeam.php';
                $ret['FINT'][] = $tmp;
            }
            if (subFeatureAcl($acl,AclTeams,'') >= AclReadOnly) {
                $ret['FINT'][] = get_text('MenuLM_PrnShootOff') . '|' . $CFG->ROOT_DIR . 'Qualification/PrnShootoff.php|||PrintOut';
                $ret['FINT'][] = MENU_DIVIDER;
                $ret['FINT'][] = get_text('MenuLM_Export Text File') . '|' . $CFG->ROOT_DIR . 'Final/ExportTSV.php';
                $ret['FINT'][] = MENU_DIVIDER;
                $ret['FINT'][] = get_text('MenuLM_Scorecard Printout') . '|' . $CFG->ROOT_DIR . 'Final/Team/PrintScore.php';
// 			$ret['FINT'][] = get_text('MenuLM_Back Number Printout') .'|'.$CFG->ROOT_DIR.'Final/Team/PrintBackNo.php';
                $ret['FINT'][] = get_text('MenuLM_NewBacknumbers') . '|' . $CFG->ROOT_DIR . 'Accreditation/IdCards.php?CardType=T';
            }
			if($_SESSION['MenuFinTOn']) {
                if (subFeatureAcl($acl,AclTeams,'') == AclReadWrite) {
                    $ret['FINT'][] = MENU_DIVIDER;
                    $ret['FINT'][] = get_text('MenuLM_Change Components') . '|' . $CFG->ROOT_DIR . 'Final/Team/ChangeComponents.php';
                    $ret['FINT'][] = MENU_DIVIDER;
                    $ret['FINT'][] = get_text('MenuLM_Data insert (Bracket view)') . '|' . $CFG->ROOT_DIR . 'Final/Team/InsertPoint_Bra.php';
                    $ret['FINT'][] = get_text('MenuLM_Data insert (Table view)') . '|' . $CFG->ROOT_DIR . 'Final/Team/InsertPoint1.php';
                    $ret['FINT'][] = get_text('MenuLM_Arrow by Arrow (Advanced user)') . '|' . $CFG->ROOT_DIR . 'Final/WriteArrows.php';
                    $ret['FINT'][] = get_text('MenuLM_Swaps').'|'.$CFG->ROOT_DIR.'Final/SwapMatches.php';
                    $ret['FINT'][] = get_text('MenuLM_Spotting') . '|' . $CFG->ROOT_DIR . 'Final/Spotting.php?Team=1';
                }
                if (subFeatureAcl($acl,AclTeams,'') >= AclReadOnly) {
                    $ret['FINT'][] = MENU_DIVIDER;
                    $ret['FINT'][] = get_text('MenuLM_Printout') . '|' . $CFG->ROOT_DIR . 'Final/PrintOut.php';
                }
			}
		}

		/** PRINTOUT MENU **/
		$ret['PRNT'][] = get_text('MenuLM_Printout') .'';
        if (subFeatureAcl($acl,AclParticipants,'pEntries') >= AclReadOnly) {
            $ret['PRNT'][] = get_text('MenuLM_Participant Lists') . '|' . $CFG->ROOT_DIR . 'Partecipants/PrintOut.php';
            $ret['PRNT'][] = get_text('MenuLM_Statistics') . '|' . $CFG->ROOT_DIR . 'Partecipants/PrintOutStat.php';
            $ret['PRNT'][] = MENU_DIVIDER;
        }
        if (subFeatureAcl($acl,AclQualification) >= AclReadOnly) {
            if($_SESSION['TourType']>=6 AND $_SESSION['TourType']<=8 AND $_SESSION['TourLocRule']=='IT') {
                $ret['PRNT'][] = get_text('SubClassRank','Tournament') . '|' . $CFG->ROOT_DIR . 'Qualification/PrnIndividual.php?SubClassRank=on&SubClassClassRank=on|||PrintOut';
            }
            $ret['PRNT'][] = get_text('MenuLM_Category Result List') . '|' . $CFG->ROOT_DIR . 'Qualification/PrintOut.php';
            $ret['PRNT'][] = get_text('MenuLM_Qualification Round') . '|' . $CFG->ROOT_DIR . 'Qualification/PrintOutAbs.php';
            $ret['PRNT'][] = MENU_DIVIDER;
        }
        if (subFeatureAcl($acl,AclIndividuals,'') >= AclReadOnly OR subFeatureAcl($acl,AclTeams,'') >= AclReadOnly) {
            $ret['PRNT'][] = get_text('MenuLM_Final Rounds') . '|' . $CFG->ROOT_DIR . 'Final/PrintOut.php';
            $ret['PRNT'][] = MENU_DIVIDER;
        }
        if (subFeatureAcl($acl,AclQualification) == AclReadWrite OR subFeatureAcl($acl,AclEliminations,'') == AclReadWrite OR subFeatureAcl($acl,AclIndividuals,'') == AclReadWrite OR subFeatureAcl($acl,AclTeams,'') == AclReadWrite) {
            $ret['PRNT'][] = get_text('MenuLM_Header for Result Printouts') . '|' . $CFG->ROOT_DIR . 'Tournament/PrintoutComments.php';
            $ret['PRNT'][] = MENU_DIVIDER;
        }
        if(subFeatureAcl($acl,AclCompetition,'cAward') == AclReadWrite) {
            $ret['PRNT'][] = get_text('MenuLM_ManAwards') . '|' . $CFG->ROOT_DIR . 'Tournament/ManAwards.php';
        }
        if(subFeatureAcl($acl,AclCompetition,'cAward') >= AclReadOnly) {
            $ret['PRNT'][] = get_text('MenuLM_CheckAwards') . '|' . $CFG->ROOT_DIR . 'Tournament/PDFAward-check.php|||PrintOut';
            $ret['PRNT'][] = get_text('MenuLM_PrintAwardsPositions') . '|' . $CFG->ROOT_DIR . 'Tournament/PDFAward-Positions.php|||PrintOut';
            $ret['PRNT'][] = get_text('MenuLM_PrintAwards') . '|' . $CFG->ROOT_DIR . 'Tournament/PDFAward.php|||PrintOut';
            $ret['PRNT'][] = MENU_DIVIDER;
        }
        if(subFeatureAcl($acl,AclCompetition,'cPrintouts') == AclReadWrite) {
            $ret['PRNT'][] = get_text('MenuLM_Print Sign') . '|' . $CFG->ROOT_DIR . 'Common/Sign';
        }

		/** HHT MENU **/
        if ($_SESSION['MenuHHT'] AND (subFeatureAcl($acl,AclQualification) == AclReadWrite OR subFeatureAcl($acl,AclIndividuals,'') == AclReadWrite OR subFeatureAcl($acl,AclTeams,'') == AclReadWrite)) {
			$ret['HHT'][] = get_text('MenuLM_HTT') .'';
			$ret['HHT'][] = get_text('MenuLM_HTT Communication Setup') .'|'.$CFG->ROOT_DIR.'HHT/Configuration.php';
			$ret['HHT'][] = MENU_DIVIDER;
			$ret['HHT'][] = get_text('MenuLM_HTT Setup') .'|'.$CFG->ROOT_DIR.'HHT/InitHTT.php';
			$ret['HHT'][] = get_text('MenuLM_Athletes Setup') .'|'.$CFG->ROOT_DIR.'HHT/InitAthletes.php';
			$ret['HHT'][] = get_text('MenuLM_Scores Setup') .'|'.$CFG->ROOT_DIR.'HHT/InitScores.php';
			$ret['HHT'][] = get_text('MenuLM_Setup HTT Sequence') .'|'.$CFG->ROOT_DIR.'HHT/Sequence.php';
			$ret['HHT'][] = get_text('MenuLM_Collect Data') .'|'.$CFG->ROOT_DIR.'HHT/Collect.php';
			$ret['HHT'][] = get_text('MenuLM_Get Info') .'|'.$CFG->ROOT_DIR.'HHT/GetInfo.php';
		}

		if(!empty($_SESSION['UseApi'])) {
			$ret['API'][] = get_text('ISKMenuHeader') .'';

			switch($_SESSION['UseApi']) {
                case 11:
                case 12:
                case 13:
                    @include_once('Api/ISK-NG/menu.php');
                    break;
			}
		}

		/** OUTPUT MENU **/
		$ret['MEDI'][] = get_text('MenuLM_Output') .'';
        if (subFeatureAcl($acl, AclOutput, 'outTv') >= AclReadOnly) {
            $ret['MEDI'][] = get_text('MenuLM_TV Output') . '|' . $CFG->ROOT_DIR . 'TV/';
            if (subFeatureAcl($acl, AclOutput, 'outTv') == AclReadWrite) {
                $ret['MEDI'][] = get_text('MenuLM_TV Channels') . '|' . $CFG->ROOT_DIR . 'TV/ChannelSetup.php';
            }
            $ret['MEDI'][] = MENU_DIVIDER;
            $ret['MEDI'][] = get_text('MenuLM_Spotting') . '|' . $CFG->ROOT_DIR . 'Final/Viewer/|||_blank';
            $ret['MEDI'][] = 'ShowTvLiveScore|' . $CFG->ROOT_DIR . 'Final/ShowTVScore.php?TourId=' . $_SESSION['TourCode'] . '|||TV';
        }

	} else {
		// MENU OFF!!!
		$ret['COMP'][] = get_text('MenuLM_Competition');
        if (subFeatureAcl($acl,AclRoot) == AclReadWrite or possibleFeature(AclRoot, AclReadWrite)) {
            $ret['COMP'][] = get_text('MenuLM_New') . '|' . $CFG->ROOT_DIR . 'Tournament/index.php?New=';
        }
		$ret['COMP'][] = get_text('MenuLM_Open') .'|'.$CFG->ROOT_DIR.'index.php';
        if (subFeatureAcl($acl,AclRoot) == AclReadWrite or possibleFeature(AclRoot, AclReadWrite)) {
            $ret['COMP'][] = get_text('MenuLM_Import Tournament') . '|' . $CFG->ROOT_DIR . 'Tournament/TournamentImport.php';
        }
		$ret['COMP'][] = MENU_DIVIDER;
		$ret['COMP'][] = get_text('MenuLM_Select Language') .'|'.$CFG->ROOT_DIR.'Language/index.php';
		if(substr(SelectLanguage(),0,2)!='en') $ret['COMP'][] = 'Select Language|'.$CFG->ROOT_DIR.'Language/index.php';

        if (subFeatureAcl($acl, AclOutput, 'outTv') >= AclReadOnly) {
            $ret['MEDI'][] = get_text('MenuLM_Output') .'';
            $ret['MEDI'][] = get_text('MenuLM_TV Channels') .'|'.$CFG->ROOT_DIR.'tv.php';
        }
	}

	$ret['MODS'][] = get_text('MenuLM_Modules');
	if(empty($_SESSION['AUTH_ENABLE']) OR !empty($_SESSION['AUTH_ROOT']) OR subFeatureAcl($acl??array(), AclRoot, '') == AclReadWrite) {
	    $ret['MODS'][] = ''.get_text('MenuLM_Update') .'|'.$CFG->ROOT_DIR.'Update/';
    }
	//$ret['MODS'][] = get_text('MenuLM_SearchModules') .'|'.$CFG->ROOT_DIR.'Modules/';

	/** Additional Modules Menu **/
	$Modules = glob($CFG->DOCUMENT_PATH . 'Modules/*/menu.php');
	foreach($Modules as $Module) {
		include($Module);
	}

	$Modules = glob($CFG->DOCUMENT_PATH . 'Modules/Sets/*/menu.php');
	foreach($Modules as $Module) {
		include($Module);
	}

	$Modules = glob($CFG->DOCUMENT_PATH . 'Modules/Custom/*/menu.php');
	foreach($Modules as $Module) {
		include($Module);
	}

	if(!empty($ret['IANSEO'])) {
		array_unshift($ret['IANSEO'], get_text('MenuLM_Ianseo'));
	}

    foreach ($ret as $k=>$v) {
        if (count($ret[$k]) == 1) {
            unset($ret[$k]);
        }
    }



    return $ret;
}

function get_which_run_menu($on=false) {
	global $CFG;
	$ret=array();

	// subsites have very specific menu!
	if(!empty($_SESSION['ShortMenu'])) {
		return $_SESSION['ShortMenu'];
	}

	// Start with minimal structure
	if($on) {
        $acl = actualACL();

        /** COMPETITION MENU **/
        $ret['COMP'][] = get_text('MenuLM_Competition') . '';
        $ret['COMP'][] = get_text('MenuLM_Open') . '|' . $CFG->ROOT_DIR . 'index.php';
        $ret['COMP'][] = get_text('MenuLM_Close') . '|' . $CFG->ROOT_DIR . 'Common/TourOff.php';
        if (subFeatureAcl($acl,AclRoot,'') == AclReadWrite) {
            $ret['COMP'][] = MENU_DIVIDER;
            $ret['COMP'][] = get_text('MenuLM_Delete') . '|' . $CFG->ROOT_DIR . 'Tournament/TourDelete.php';
        }
        $ret['COMP'][] = MENU_DIVIDER;
        $ret['COMP'][] = get_text('MenuLM_View Competition Info') . '|' . $CFG->ROOT_DIR . 'Main.php';
        if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite OR subFeatureAcl($acl,AclCompetition,'cSchedule') == AclReadWrite) {
            $ret['COMP']['INFO'][] = get_text('MenuLM_Edit Competition Info');
        }
        if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite) {
            $ret['COMP']['INFO'][] = get_text('MenuLM_Competition Info') . '|' . $CFG->ROOT_DIR . 'Tournament/index.php';
            $ret['COMP']['INFO'][] = get_text('MenuLM_Images') . '|' . $CFG->ROOT_DIR . 'Tournament/ManLogo.php';
        }
        if(subFeatureAcl($acl,AclCompetition,'cSchedule') == AclReadWrite) {
            $ret['COMP']['INFO'][] = get_text('MenuLM_Session') . '|' . $CFG->ROOT_DIR . 'Tournament/ManSessions_kiss.php';
        }
        if(subFeatureAcl($acl,AclCompetition,'cData') >= AclReadOnly) {
            $ret['COMP']['INFO'][] = get_text('MenuLM_Field Crew') . '|' . $CFG->ROOT_DIR . 'Tournament/ManStaffField.php';
        }
        if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite) {
            $ret['COMP']['INFO'][] = get_text('MenuLM_Divisions and Classes') . '|' . $CFG->ROOT_DIR . 'Tournament/ManDivClass.php';
            $ret['COMP']['INFO'][] = get_text('MenuLM_Targets') . '|' . $CFG->ROOT_DIR . 'Tournament/ManTargets.php';
        }
        if (subFeatureAcl($acl,AclCompetition,'cSchedule') >= AclReadOnly) {
            $ret['COMP'][] = MENU_DIVIDER;
            $ret['COMP'][] = get_text('MenuLM_Scheduling') . '|' . $CFG->ROOT_DIR . 'Scheduler/index.php';
        }
        if(subFeatureAcl($acl,AclCompetition,'cData') == AclReadWrite) {
            $ret['COMP'][] = MENU_DIVIDER;
            $ret['COMP']['FIN'][] = get_text('MenuLM_RunArcherySetup') . '';
            $ret['COMP']['FIN'][] = get_text('MenuLM_Manage Events') . '|' . $CFG->ROOT_DIR . 'Final/ListEvents.php';
            $ret['COMP']['FIN'][] = get_text('MenuLM_Draw') . '|' . $CFG->ROOT_DIR . 'RunArchery/SetTarget.php';
            $ret['COMP']['FIN'][] = get_text('ExportChips', 'RunArchery') . '|' . $CFG->ROOT_DIR . 'RunArchery/ExportChips.php';
        }
        $ret['COMP'][] = MENU_DIVIDER;
        if (subFeatureAcl($acl,AclRoot) == AclReadWrite) {
            $ret['COMP']['BLOCK'][] = get_text('MenuLM_Security') . '|' . $CFG->ROOT_DIR . 'Tournament/BlockTour.php';
            $ret['COMP']['BLOCK'][] = get_text('MenuLM_Lock setup') . '|' . $CFG->ROOT_DIR . 'Tournament/BlockTour.php';
            $ret['COMP']['BLOCK'][] = get_text('MenuLM_Lock manage') . '|' . $CFG->ROOT_DIR . 'Tournament/AccessControlList/index.php';
        }
        if (subFeatureAcl($acl,AclCompetition,'cExport') >= AclReadOnly) {
            $ret['COMP']['EXPT'][] = get_text('MenuLM_Export') . '|' . $CFG->ROOT_DIR . 'Tournament/TournamentExport.php?Complete=1';
            $ret['COMP']['EXPT'][] = get_text('Export2Fitarco', 'Tournament') . '|' . $CFG->ROOT_DIR . 'Tournament/Export2Fitarco.php';
            $ret['COMP']['EXPT'][] = get_text('MenuLM_Export Tournament') . '|' . $CFG->ROOT_DIR . 'Tournament/TournamentExport.php';
            $ret['COMP']['EXPT'][] = get_text('MenuLM_Export Tournament Photo') . '|' . $CFG->ROOT_DIR . 'Tournament/TournamentExport.php?Complete=1';
            $ret['COMP'][] = MENU_DIVIDER;
        }
        if (subFeatureAcl($acl,AclInternetPublish) == AclReadWrite) {
            $ret['COMP']['SEND'][] = get_text('MenuLM_Send to ianseo.net');
            if(subFeatureAcl($acl,AclInternetPublish,'ipCredentials') == AclReadWrite) {
                $ret['COMP']['SEND'][] = get_text('MenuLM_Set on-line codes') . '|' . $CFG->ROOT_DIR . 'Tournament/SetCredentials.php';
            }
            if(subFeatureAcl($acl,AclInternetPublish,'ipSend') == AclReadWrite) {
                $ret['COMP']['SEND'][] = get_text('MenuLM_Send') . '|' . $CFG->ROOT_DIR . 'Tournament/UploadResults.php';
            }
            $ret['COMP'][] = MENU_DIVIDER;
        }
        $ret['COMP'][] = get_text('MenuLM_Select Language') . '|' . $CFG->ROOT_DIR . 'Language/index.php';
        if (substr(SelectLanguage(), 0, 2) != 'en') $ret['COMP'][] = 'Select Language|' . $CFG->ROOT_DIR . 'Language/index.php';


        /** PARTICIPANTS MENU **/
        $ret['PART'][] = get_text('MenuLM_Participants');
        if (subFeatureAcl($acl,AclParticipants,'pEntries') == AclReadWrite) {
            $ret['PART'][] = get_text('MenuLM_List (Simple)') . '|' . $CFG->ROOT_DIR . 'Partecipants/index.php';
            $ret['PART'][] = get_text('MenuLM_Partecipant List (Advanced)') . '|' . $CFG->ROOT_DIR . 'Partecipants-exp/index.php';
            $ret['PART'][] = get_text('MenuLM_Athletes Participation to Ind/Team Event') . '|' . $CFG->ROOT_DIR . 'Partecipants/ManEventAccess.php';
        }
        if (subFeatureAcl($acl,AclParticipants,'pAdvancedEntries') == AclReadWrite) {
            $ret['PART'][] = get_text('MenuLM_Athlete Status Management') . '|' . $CFG->ROOT_DIR . 'Partecipants/ManStatus.php';
             // $ret['PART'][] = get_text('MenuLM_IrmManagement') . '|' . $CFG->ROOT_DIR . 'Partecipants/ManIrmStatus.php';
            $ret['PART'][] = MENU_DIVIDER;
        }
        if (subFeatureAcl($acl,AclAccreditation,'') >= AclReadOnly) {
            $ret['PART']['ACCR'][] = get_text('MenuLM_Accreditation') . '';
        }
        if(subFeatureAcl($acl,AclAccreditation,'acStandard') == AclReadWrite) {
            $ret['PART']['ACCR'][] = get_text('MenuLM_Accreditation') . '|' . $CFG->ROOT_DIR . 'Accreditation/index.php';
            $ret['PART']['ACCR'][] = get_text('TakePicture', 'Tournament') . '|' . $CFG->ROOT_DIR . 'Accreditation/AccreditationPicture.php';
            $ret['PART']['ACCR'][] = MENU_DIVIDER;
        }
        if(subFeatureAcl($acl,AclAccreditation,'acSetup') == AclReadWrite) {
            $ret['PART']['ACCR'][] = get_text('MenuLM_Fees setup') . '|' . $CFG->ROOT_DIR . 'Accreditation/ManagePrices.php';
            $ret['PART']['ACCR'][] = get_text('MenuLM_Athlets fees status') . '|' . $CFG->ROOT_DIR . 'Accreditation/ManagePays.php';
            $ret['PART']['ACCR'][] = get_text('MenuLM_Accreditation colors management') . '|' . $CFG->ROOT_DIR . 'Accreditation/Colors.php';
            $ret['PART']['ACCR'][] = MENU_DIVIDER;
        }
        if(subFeatureAcl($acl,AclAccreditation,'acStandard') >= AclReadOnly) {
            $ret['PART']['ACCR'][] = get_text('MenuLM_PrintBadges') . '|' . $CFG->ROOT_DIR . 'Accreditation/IdCards.php';
            $ret['PART']['ACCR'][] = get_text('MenuLM_Printout') . '|' . $CFG->ROOT_DIR . 'Accreditation/PrintOut.php';
            $ret['PART'][] = MENU_DIVIDER;
        }
        if(subFeatureAcl($acl,AclParticipants,'pAdvancedEntries') == AclReadWrite) {
            $ret['PART']['SYNC'][] = get_text('MenuLM_Athletes Sync.') . '|' . $CFG->ROOT_DIR . 'Partecipants/LookupTableLoad.php';
            $ret['PART']['SYNC'][] = get_text('MenuLM_Athletes Sync.') . '|' . $CFG->ROOT_DIR . 'Partecipants/LookupTableLoad.php';
            $ret['PART']['SYNC'][] = get_text('MenuLM_ListLoad') . '|' . $CFG->ROOT_DIR . 'Partecipants/ListLoad.php';
            $ret['PART']['SYNC'][] = get_text('MenuLM_AthletesDiscrepancies') . '|' . $CFG->ROOT_DIR . 'Partecipants/Discrepancies.php';
            $ret['PART']['SYNC'][] = MENU_DIVIDER;
            $ret['PART']['SYNC'][] = get_text('MenuLM_ChangeNationsNames') . '|' . $CFG->ROOT_DIR . 'Partecipants/ChangeNationsNames.php';
            $ret['PART']['SYNC'][] = get_text('TourCountries', 'Tournament') . '|' . $CFG->ROOT_DIR . 'Tournament/Countries.php';
            $ret['PART'][] = MENU_DIVIDER;
        }
        if(subFeatureAcl($acl,AclParticipants, 'pEntries')>= AclReadOnly) {
            $ret['PART'][] = get_text('MenuLM_Printout') . '|' . $CFG->ROOT_DIR . 'RunArchery/Participants.php';
        }


		/** Run Archery MENU **/
		$ret['RUN'][] = get_text('MenuLM_RunArchery') .'';
        if (subFeatureAcl($acl,AclQualification) == AclReadWrite) {
            $ret['RUN']['SCOR'][] = get_text('MenuLM_Input Score') . '|' . $CFG->ROOT_DIR . 'RunArchery/index.php';
            $ret['RUN']['SCOR'][] = get_text('MenuLM_Standard Table') . '|' . $CFG->ROOT_DIR . 'RunArchery/index.php';
	        $ret['RUN'][] = MENU_DIVIDER;
        }
        if (subFeatureAcl($acl,AclQualification) >= AclReadOnly) {
            $ret['RUN'][] = get_text('MenuLM_Scorecard Printout') . '|' . $CFG->ROOT_DIR . 'RunArchery/PrintScore.php';
            $ret['RUN'][] = get_text('MenuLM_NewBacknumbers') . '|' . $CFG->ROOT_DIR . 'Accreditation/IdCards.php?CardType=Q';
            $ret['RUN'][] = get_text('Rankings') . '|' . $CFG->ROOT_DIR . 'RunArchery/PrintOut.php';
            $ret['RUN'][] = get_text('MenuLM_OdsExport') . '|' . $CFG->ROOT_DIR . 'RunArchery/Export.php';
        }
		/** PRINTOUT MENU **/
		$ret['PRNT'][] = get_text('MenuLM_Printout') .'';
        if (subFeatureAcl($acl,AclQualification) >= AclReadOnly) {
            $ret['PRNT'][] = get_text('Rankings') . '|' . $CFG->ROOT_DIR . 'RunArchery/PrintOut.php';
            $ret['PRNT'][] = MENU_DIVIDER;
        }
        if(subFeatureAcl($acl,AclCompetition,'cPrintouts') == AclReadWrite) {
            $ret['PRNT'][] = get_text('MenuLM_Print Sign') . '|' . $CFG->ROOT_DIR . 'Common/Sign';
        }
	} else {
		// MENU OFF!!!

		$ret['COMP'][] = get_text('MenuLM_Competition');
		$ret['COMP'][] = get_text('MenuLM_New') .'|'.$CFG->ROOT_DIR.'Tournament/index.php?New=';
		$ret['COMP'][] = get_text('MenuLM_Open') .'|'.$CFG->ROOT_DIR.'index.php';
		$ret['COMP'][] = get_text('MenuLM_Import Tournament') .'|'.$CFG->ROOT_DIR.'Tournament/TournamentImport.php';
		$ret['COMP'][] = MENU_DIVIDER;
		$ret['COMP'][] = get_text('MenuLM_Select Language') .'|'.$CFG->ROOT_DIR.'Language/index.php';
		if(substr(SelectLanguage(),0,2)!='en') $ret['COMP'][] = 'Select Language|'.$CFG->ROOT_DIR.'Language/index.php';

		if(ProgramRelease=='HEAD') {
			$ret['MEDI'][] = get_text('MenuLM_Output') .'';
			$ret['MEDI'][] = get_text('MenuLM_TV Channels') .'|'.$CFG->ROOT_DIR.'tv.php';
		}
	}

	$ret['MODS'][] = get_text('MenuLM_Modules');
	if(ProgramRelease!='HEAD' AND (empty($_SESSION['AUTH_ENABLE']) OR !empty($_SESSION['AUTH_ROOT']))) {
	    $ret['MODS'][] = ''.get_text('MenuLM_Update') .'|'.$CFG->ROOT_DIR.'Update/';
    }
	//$ret['MODS'][] = get_text('MenuLM_SearchModules') .'|'.$CFG->ROOT_DIR.'Modules/';

	/** Additional Modules Menu **/
	$Modules = glob($CFG->DOCUMENT_PATH . 'Modules/*/menu.php');
	foreach($Modules as $Module) {
		switch(basename(dirname($Module))) {
			case 'Barcodes':
			case 'USCAA':
				break;
			default:
				include($Module);
		}
	}

	$Modules = glob($CFG->DOCUMENT_PATH . 'Modules/Sets/*/menu.php');
	foreach($Modules as $Module) {
		include($Module);
	}

	$Modules = glob($CFG->DOCUMENT_PATH . 'Modules/Custom/*/menu.php');
	foreach($Modules as $Module) {
		include($Module);
	}

    foreach ($ret as $k=>$v) {
        if (count($ret[$k]) == 1) {
            unset($ret[$k]);
        }
    }

	if(!empty($ret['IANSEO'])) {
		array_unshift($ret['IANSEO'], get_text('MenuLM_Ianseo'));
	}



    return $ret;
}

function build_menu($menu, $dots='.|') {
	$ret='';
	foreach($menu as $val) {
		if(is_array($val)) {
			$tmp=array_shift($val);
			if($tmp)
				$ret .= $dots . $tmp;
			$ret .= build_menu($val, '.' . $dots);
		} else {
			$ret .= $dots . $val;
		}
		$ret;
	}
	return trim($ret);
}

function DoSubMenu($List, $lvl=0) {
	$ret='';
	if(is_array($List)) {
		// first item is the title!
		$tit=array_shift($List);
		$li=getSubMenuItem($tit, $lvl);
		$ret.=$li;
		$first=true;
		foreach($List as $t => $sl) {
			$ret.=DoSubMenu($sl, $lvl+1);
			$first=false;
		}
		$ret.= "</ul></li>";
	} elseif($List==MENU_DIVIDER  ) {
		$ret.= "<hr>";
	} else {
		$li=getSubMenuItem($List);
		$ret.=$li;
	}
	return $ret;
}

function getSubMenuItem($tit, $lvl=-1) {
    $lnk='';
    $tgt='';
    if(strpos($tit,'|')) {
        $tmp = explode('|', $tit);
        $tit=$tmp[0];
        $lnk=$tmp[1];
        if(count($tmp)>2){
            $tgt=$tmp[4];
        }
    }
    $link ='<a href="'.($lnk!='' ? $lnk : '#url').'"';
    if($tgt!='') $link.=' target="'.$tgt.'"';
    $link.=($lvl>0 ? ' class="submenu"' : '').'>'.$tit.'</a>';
    return '<li'.($lvl!=0 ? '' : ' class="MenuTitle"').'>'.$link.($lvl==-1 ? '</li>' : '<ul>');
}

function PrintMenu() {
	$Menu=get_which_menu(CheckTourSession());
	echo '<ul>';
	foreach($Menu as $Top => $List) {
		echo DoSubMenu($List);
	}
	echo '</ul>';
}

