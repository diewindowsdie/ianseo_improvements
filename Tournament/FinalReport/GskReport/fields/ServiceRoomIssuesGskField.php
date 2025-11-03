<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class ServiceRoomIssuesGskField extends GskField
{
    public function getParameterName(): string
    {
        return "service_room_issues";
    }

    public function getDefaultValue(): string
    {
        return "Состояние и оснащение служебных помещений соответствует требованиям;";
    }
}