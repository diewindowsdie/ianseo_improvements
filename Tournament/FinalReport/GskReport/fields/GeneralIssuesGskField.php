<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class GeneralIssuesGskField extends GskField
{
    public function getParameterName(): string
    {
        return "general_issues";
    }

    public function getDefaultValue(): string
    {
        return "Состояние спортивной базы соответствует требованиям техники безопасности и правил проведения соревнований по стрельбе из лука;";
    }
}