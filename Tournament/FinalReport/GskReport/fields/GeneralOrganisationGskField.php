<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class GeneralOrganisationGskField extends GskField
{
    public function getParameterName(): string
    {
        return "general_organisation";
    }

    public function getDefaultValue(): string
    {
        return "Объективность судейства и точность расписания соблюдались на протяжении всех дней соревнований;";
    }
}