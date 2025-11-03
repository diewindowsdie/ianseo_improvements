<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class OtherNotesGskField extends GskField
{
    public function getParameterName(): string
    {
        return "other_notes";
    }

    public function getDefaultValue(): string
    {
        return "Замечаний нет;";
    }
}