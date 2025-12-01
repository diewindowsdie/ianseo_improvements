<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class LocalRegionCodeForJudgesGskField extends GskField
{
    public function getParameterName(): string
    {
        return "localCountryCodeForJudges";
    }

    public function getDefaultValue(): string
    {
        return "";
    }
}