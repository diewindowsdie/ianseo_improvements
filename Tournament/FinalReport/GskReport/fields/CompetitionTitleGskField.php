<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class CompetitionTitleGskField extends GskField
{
    public function getParameterName(): string
    {
        return "competitionTitle";
    }

    public function getDefaultValue(): string
    {
        return $_SESSION["TourName"];
    }
}