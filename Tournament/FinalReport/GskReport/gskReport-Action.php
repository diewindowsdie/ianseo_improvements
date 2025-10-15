<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once("Tournament/FinalReport/GskReport/GskFields.php");

if(!CheckTourSession()) { //todo check some acl
    die();
}

$field = GskFields::byParameterName($_REQUEST["fieldName"]);
if ($field) {
    $field->setValue($_REQUEST["value"]);
}

?>