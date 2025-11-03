<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class GuestDelegationsIssuesGskField extends GskField
{
    public function getParameterName(): string
    {
        return "guest_delegations_issues";
    }

    public function getDefaultValue(): string
    {
        return "Размещением все делегации были обеспечены, транспортное обслуживание осуществлялось в соответствии с расписанием;";
    }
}