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
        $query = "select count(1) as Value from Entries e
                    inner join Countries c on e.EnCountry = c.CoId and e.EnTournament = c.CoTournament
                    inner join Divisions d on e.EnDivision = d.DivId and e.EnTournament = d.DivTournament
                    inner join Classes cl on e.EnClass = cl.ClId and e.EnTournament = cl.ClTournament
                         
                where (d.DivAthlete = 0 or cl.ClAthlete = 0) and c.CoCode = '" . $this->regionId . "' and e.EnTournament = " . $_SESSION["TourId"];
        $rs = safe_r_SQL($query);
        return safe_fetch($rs)->Value ?? "0";
    }

    public static function getParameterNameForRegion($regionCode): string {
        return  self::fieldPrefix . $regionCode;
    }
}