<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

if(!CheckTourSession()) { //todo check some acl
    die();
}

$moduleName = "GSK-Report";
$fieldPrefix = "field_";
$field = $_REQUEST["fieldName"];

setModuleParameter($moduleName, $fieldPrefix . $field, $_REQUEST["value"], $_SESSION["TourId"]);

?>