<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class MedicalIssuesGskField extends GskField
{
    public function getParameterName(): string
    {
        return "medical_issues";
    }

    public function getDefaultValue(): string
    {
        return "Травм и заболеваний не было, на соревнованиях присутствовал врач с дежурной бригадой скорой помощи";
    }
}