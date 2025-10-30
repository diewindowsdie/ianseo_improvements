<?php
require_once("Tournament/FinalReport/GskReport/GskField.php");

class InformationServicesGskField extends GskField
{
    public function getParameterName(): string
    {
        return "information_services";
    }

    public function getDefaultValue(): string
    {
        return "Информационное обеспечение соревнований – радио-информация, выпуск стартовых протоколов, результатов соревнований, а также обеспечение вычислительной техникой и множительной аппаратурой предоставлялось своевременно;";
    }
}