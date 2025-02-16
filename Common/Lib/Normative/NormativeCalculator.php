<?php
require_once "NormativeProvider.php";

const replacements = [
    //костыль для 3д, вырезаем стандартные названия дистанций
    "Круг" => "K",
    "Course" => "K",
    "[а-яА-ЯёË]+" => "",
    "\-" => "_",
    "\ " => "",
];
function calculateClassName($distances)
{
    $className = '';
    //склеиваем все дистанции через символ подчеркивания, и заменяем в их именах дефис на символ подчеркивания
    foreach (array_values($distances) as $distance) {
        if (trim($distance) == '') {
            break;
        }
        $className .= $className != '' ? '_' : '';
        foreach (replacements as $pattern => $replacement) {
            $distance = mb_ereg_replace($pattern, $replacement, $distance);
        }

        $className .= $distance;
    }
    return 'Provider' . $className;
}

function calcNormative($distances, $class, $division, $scoreByDistances)
{
    $className = calculateClassName($distances);
    $fileName = $className . '.inc.php';

    try {
        require_once('providers/' . $fileName);

        $provider = new $className();
        return $provider->calculateNormativeInternal($class, $division, $scoreByDistances)["name"];
    } catch (\Throwable $e) {
        error_log("Provider not found: distances=(" . implode(",", $distances) . "), filename=" . $fileName);

        return Normative::None["name"];
    }
}

?>