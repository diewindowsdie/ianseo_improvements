<?php
require_once "NormativeProvider.php";

const replacements = [
    //костыль для 3д, вырезаем стандартные названия дистанций
    "Круг" => "K",
    "Course" => "K",
    'М' => 'm',
    'м' => 'm',
    //вырезаем всю оставшуюся кириллицу
    "[а-яА-ЯёË]+" => "",
    "\-" => "_",
    "\ " => "",
];
function calculateClassName($distances)
{
    $className = '';
    //склеиваем все дистанции через символ подчеркивания, и заменяем в их именах дефис на символ подчеркивания
    foreach (array_values($distances) as $distance) {
        if (trim($distance) === '' || trim($distance) === '-') {
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

function calcNormative($distances, $class, $division, $scoreByDistances, $tourLocalizationRule)
{
    $className = calculateClassName($distances);
    $fileName = $className . '.inc.php';

    //российские нормативы сейчас лежат в папке FITA
    $tourLocalizationRule = str_replace("default", "FITA", $tourLocalizationRule);

    try {
        require_once('Modules/Sets/' . $tourLocalizationRule . '/Normative/providers/' . $fileName);

        $provider = new $className();
        return $provider->calculateNormativeInternal($class, $division, $scoreByDistances);
    } catch (\Throwable $e) {
        error_log("Provider not found: distances=(" . implode(",", $distances) . "), filename=" . $fileName);

        return Normative::None;
    }
}

?>