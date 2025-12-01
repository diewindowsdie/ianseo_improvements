<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");
require_once("Tournament/FinalReport/GskReport/fields/CompetitionTitleGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/LocalRegionCodeForJudgesGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/IsBasicRegionGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/NumberOfCoachesFromRegion.php");
require_once("Tournament/FinalReport/GskReport/fields/GeneralIssuesGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/ServiceRoomIssuesGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/InformationServicesGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/PressIssuesGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/ViewersAmountGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/GeneralOrganisationGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/MedicalIssuesGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/GuestDelegationsIssuesGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/SecurityNotesGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/OtherNotesGskField.php");

class GskFields
{
    private static $competitionTitle;
    private static $localRegionCodeForJudgesGskField;
    private static $generalIssuesGskField;
    private static $serviceRoomIssuesGskField;
    private static $informationServicesGskField;
    private static $pressIssuesGskField;
    private static $viewersAmountGskField;
    private static $generalOrganisationGskField;
    private static $medicalIssuesGskField;
    private static $guestDelegationsIssuesGskField;
    private static $securityNotesGskField;
    private static $otherNotesGskField;

    public static function getCompetitionTitle(): CompetitionTitleGskField
    {
        if (is_null(self::$competitionTitle)) {
            self::$competitionTitle = new CompetitionTitleGskField();
        }

        return self::$competitionTitle;
    }

    public static function getLocalRegionCodeForJudges(): LocalRegionCodeForJudgesGskField
    {
        if (is_null(self::$localRegionCodeForJudgesGskField)) {
            self::$localRegionCodeForJudgesGskField = new LocalRegionCodeForJudgesGskField();
        }

        return self::$localRegionCodeForJudgesGskField;
    }

    public static function getGeneralIssues(): GeneralIssuesGskField
    {
        if (is_null(self::$generalIssuesGskField)) {
            self::$generalIssuesGskField = new GeneralIssuesGskField();
        }

        return self::$generalIssuesGskField;
    }

    public static function getServiceRoomIssues(): ServiceRoomIssuesGskField
    {
        if (is_null(self::$serviceRoomIssuesGskField)) {
            self::$serviceRoomIssuesGskField = new ServiceRoomIssuesGskField();
        }

        return self::$serviceRoomIssuesGskField;
    }

    public static function getInformationServices(): InformationServicesGskField
    {
        if (is_null(self::$informationServicesGskField)) {
            self::$informationServicesGskField = new InformationServicesGskField();
        }

        return self::$informationServicesGskField;
    }

    public static function getPressIssues(): PressIssuesGskField
    {
        if (is_null(self::$pressIssuesGskField)) {
            self::$pressIssuesGskField = new PressIssuesGskField();
        }

        return self::$pressIssuesGskField;
    }

    public static function getViewersAmount(): ViewersAmountGskField
    {
        if (is_null(self::$viewersAmountGskField)) {
            self::$viewersAmountGskField = new ViewersAmountGskField();
        }

        return self::$viewersAmountGskField;
    }

    public static function getGeneralOrganisation(): GeneralOrganisationGskField
    {
        if (is_null(self::$generalOrganisationGskField)) {
            self::$generalOrganisationGskField = new GeneralOrganisationGskField();
        }

        return self::$generalOrganisationGskField;
    }

    public static function getMedicalIssues(): MedicalIssuesGskField
    {
        if (is_null(self::$medicalIssuesGskField)) {
            self::$medicalIssuesGskField = new MedicalIssuesGskField();
        }

        return self::$medicalIssuesGskField;
    }

    public static function getGuestDelegationIssues(): GuestDelegationsIssuesGskField
    {
        if (is_null(self::$guestDelegationsIssuesGskField)) {
            self::$guestDelegationsIssuesGskField = new GuestDelegationsIssuesGskField();
        }

        return self::$guestDelegationsIssuesGskField;
    }

    public static function getSecurityNotes(): SecurityNotesGskField
    {
        if (is_null(self::$securityNotesGskField)) {
            self::$securityNotesGskField = new SecurityNotesGskField();
        }

        return self::$securityNotesGskField;
    }

    public static function getOtherNotes(): OtherNotesGskField
    {
        if (is_null(self::$otherNotesGskField)) {
            self::$otherNotesGskField = new OtherNotesGskField();
        }

        return self::$otherNotesGskField;
    }

    public static function byParameterName($fieldName): ?GskField
    {
        if ($fieldName === self::getCompetitionTitle()->getParameterName()) {
            return self::$competitionTitle;
        } else if ($fieldName === self::getLocalRegionCodeForJudges()->getParameterName()) {
            return self::$localRegionCodeForJudgesGskField;
        } else if ($fieldName === self::getGeneralIssues()->getParameterName()) {
            return self::$generalIssuesGskField;
        } else if ($fieldName === self::getServiceRoomIssues()->getParameterName()) {
            return self::$serviceRoomIssuesGskField;
        } else if ($fieldName === self::getInformationServices()->getParameterName()) {
            return self::$informationServicesGskField;
        } else if ($fieldName === self::getPressIssues()->getParameterName()) {
            return self::$pressIssuesGskField;
        } else if ($fieldName === self::getViewersAmount()->getParameterName()) {
            return self::$viewersAmountGskField;
        } else if ($fieldName === self::getGeneralOrganisation()->getParameterName()) {
            return self::$generalOrganisationGskField;
        } else if ($fieldName === self::getMedicalIssues()->getParameterName()) {
            return self::$medicalIssuesGskField;
        } else if ($fieldName === self::getGuestDelegationIssues()->getParameterName()) {
            return self::$guestDelegationsIssuesGskField;
        } else if ($fieldName === self::getSecurityNotes()->getParameterName()) {
            return self::$securityNotesGskField;
        } else if ($fieldName === self::getOtherNotes()->getParameterName()) {
            return self::$otherNotesGskField;
        } else {
            $isBasicInstance = new IsBasicRegionGskField($fieldName);
            $coachesFromRegionInstance = new NumberOfCoachesFromRegion($fieldName);
            if ($fieldName === $isBasicInstance->getParameterName()) {
                return $isBasicInstance;
            } else if ($fieldName === $coachesFromRegionInstance->getParameterName()) {
                return $coachesFromRegionInstance;
            }
        }

        return null;
    }
}