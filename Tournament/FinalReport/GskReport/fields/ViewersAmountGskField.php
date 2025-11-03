<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class ViewersAmountGskField extends GskField
{
    public function getParameterName(): string
    {
        return "viewers_amount";
    }

    public function getDefaultValue(): string
    {
        return "0";
    }
}