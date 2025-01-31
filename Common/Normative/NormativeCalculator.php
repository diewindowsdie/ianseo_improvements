<?php

interface NormativeProvider
{
    public function calculateNormativeInternal($class, $division, $score);
}

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

function calcNormative($distances, $class, $division, $score)
{
    $className = calculateClassName($distances);
    $fileName = $className . '.inc.php';

    require_once('conf/' . $fileName);

    $provider = new $className();
    return $provider->calculateNormativeInternal($class, $division, $score);
}
?>