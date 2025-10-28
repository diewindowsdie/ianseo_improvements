<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");
require_once("Tournament/FinalReport/GskReport/fields/CompetitionTitleGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/LocalRegionIdForJudgesGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/IsBasicRegionGskField.php");
require_once("Tournament/FinalReport/GskReport/fields/NumberOfCoachesFromRegion.php");

class GskFields
{
    private static $competitionTitle;
    private static $localRegionIdForJudgesGskField;

    public static function getCompetitionTitle(): CompetitionTitleGskField
    {
        if (is_null(self::$competitionTitle)) {
            self::$competitionTitle = new CompetitionTitleGskField();
        }

        return self::$competitionTitle;
    }

    public static function getLocalRegionIdForJudges(): LocalRegionIdForJudgesGskField
    {
        if (is_null(self::$localRegionIdForJudgesGskField)) {
            self::$localRegionIdForJudgesGskField = new LocalRegionIdForJudgesGskField();
        }

        return self::$localRegionIdForJudgesGskField;
    }

    public static function byParameterName($fieldName): ?GskField
    {
        if ($fieldName === self::getCompetitionTitle()->getParameterName()) {
            return self::$competitionTitle;
        } else if ($fieldName === self::getLocalRegionIdForJudges()->getParameterName()) {
            return self::$localRegionIdForJudgesGskField;
        } else {
            $isBasicInstance = new IsBasicRegionGskField($fieldName);
            $coachesFromRegionInstance = new NumberOfCoachesFromRegion($fieldName);
            if ($fieldName === $isBasicInstance->getParameterName()) {
                return $isBasicInstance;
            } else if ($fieldName == $coachesFromRegionInstance->getParameterName()) {
                return $coachesFromRegionInstance;
            }
        }

        return null;
    }
}