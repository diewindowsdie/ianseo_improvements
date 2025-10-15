<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");
require_once("Tournament/FinalReport/GskReport/fields/CompetitionTitleGskField.php");

class GskFields
{
    private static $competitionTitle;

    public static function getCompetitionTitle(): CompetitionTitleGskField
    {
        if (is_null(self::$competitionTitle)) {
            self::$competitionTitle = new CompetitionTitleGskField();
        }

        return self::$competitionTitle;
    }

    public static function byParameterName($fieldName): ?GskField
    {
        if ($fieldName === self::getCompetitionTitle()->getParameterName()) {
            return self::$competitionTitle;
        }

        return null;
    }
}