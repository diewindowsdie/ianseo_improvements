<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class PressIssuesGskField extends GskField
{
    public function getParameterName(): string
    {
        return "press_issues";
    }

    public function getDefaultValue(): string
    {
        return "Сотрудникам СМИ были предоставлены места на трибунах, а также помещения для расположения пресс-центра. Ход соревнований освещался в местных СМИ;";
    }
}