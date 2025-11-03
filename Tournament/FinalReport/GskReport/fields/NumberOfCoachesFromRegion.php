<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class NumberOfCoachesFromRegion extends GskField
{
    private const fieldPrefix = "coaches_from_";
    private string $regionId;

    public function __construct($fieldName) {
        $this->regionId = str_replace(self::fieldPrefix, "", $fieldName);
    }

    public function getParameterName(): string
    {
        return self::getParameterNameForRegion($this->regionId);
    }

    public function getDefaultValue(): string
    {
        return "0";
    }

    public static function getParameterNameForRegion($regionId): string {
        return  self::fieldPrefix . $regionId;
    }
}