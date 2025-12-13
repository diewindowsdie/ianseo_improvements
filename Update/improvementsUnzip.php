<?php

require_once(dirname(__FILE__, 2) . '/Common/PclZip/pclzip.lib.php');

//список паттернов, которые указывают на файлы, которые не нужно обновлять. Каждый паттерн - это отрицание, потому что pclzip использует паттерн на файлы, которые надо обновлять
$excludePattern = "^" .
    "(?!.*Common\/config\.inc\.php.*$)" . //основной конфиг янсео
    "(?!.*TV\/Photos.*$)" . //Изображения турнира
    ".*";

$zipArchive = new PclZip($fullFilename);
$files = $zipArchive->extract(PCLZIP_OPT_PATH, $targetDir, PCLZIP_OPT_REPLACE_NEWER, PCLZIP_OPT_BY_EREG, $excludePattern);
$failed = array();
foreach ($files as $fileStatus) {
    if (($fileStatus["folder"] === "1" && ($fileStatus["status"] !== "ok" || $fileStatus["status"] !== "already_a_directory")) || (!$fileStatus["folder"] && $fileStatus["status"] !== "ok")) {
        $failed[] = $fileStatus;
    }
}
if (count($failed) === 0) {
    $jsonResponse['error'] = 0;
    $jsonResponse['msg'] = $previousMessages . "<span>Обновление IANSEO успешно завершено.</span><br />";
    JsonOut($jsonResponse);
    unlink($fullFilename);
} else {
    $jsonResponse['msg'] = $previousMessages . "<span style='color: red'>Не удалось обновить следующие файлы IANSEO:</span><br/>";
    foreach($failed as $failedUpdate) {
        $jsonResponse['msg'] .= "<span style='color: red'>" . $failedUpdate["stored_filename"] . "</span><br/>";
    }
    $jsonResponse['msg'] .= "<br/><span>Пожалуйста, обновите IANSEO вручную. Файл с последним релизом расположен здесь: <b>" . $fullFilename . "</b></span><br/>";
    JsonOut($jsonResponse);
}
