<?php
require_once "NormativeProvider.php";

function calculateClassName($distances)
{
    $className = '';
    //склеиваем все дистанции через символ подчеркивания, и заменяем в их именах дефис на символ подчеркивания
    foreach (array_values($distances) as $distance) {
        if (trim($distance) == '') {
            break;
        }
        $className .= $className != '' ? '_' : '';
        $className .= str_replace('-', '_', $distance);
    }
    return 'Provider' . $className;
}

function calcNormative($distances, $class, $division, $scoreByDistances)
{
    $className = calculateClassName($distances);
    $fileName = $className . '.inc.php';

    require_once('providers/' . $fileName);

    $provider = new $className();
    return $provider->calculateNormativeInternal($class, $division, $scoreByDistances)["name"];
}

?>