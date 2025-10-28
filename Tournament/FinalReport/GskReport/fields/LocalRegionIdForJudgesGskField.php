<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class LocalRegionIdForJudgesGskField extends GskField
{
    public function getParameterName(): string
    {
        return "localCountryIdForJudges";
    }

    public function getDefaultValue(): string
    {
        return "";
    }
}