<?php
error_reporting(E_ALL);
$previousMessages = "<span>Начинаем процесс обновления...</span><br/><span>Скачиваем файл <b>" . $_REQUEST["url"] . "</b></span><br /><span>Файл обновления успешно сохранен во временный каталог.</span><br />";

$jsonResponse = array('error' => 1, 'msg' => '');
define('DIRNAME', dirname($_SERVER['SCRIPT_FILENAME']));

require_once(dirname(DIRNAME) . '/config.php');
checkFullACL(AclRoot, '', AclReadWrite);

if (!empty($_SESSION['AUTH_ENABLE']) and empty($_SESSION['AUTH_ROOT'])) {
    $jsonResponse['msg'] = $previousMessages . "<span style='color: red'>Недостаточно прав для обновления IANSEO :(</span><br/>";
    JsonOut($jsonResponse);
}

$tempDir = sys_get_temp_dir();
$filename = basename($_REQUEST['url']);
$fullFilename = $tempDir . "/" . $filename;
if (!is_writable($fullFilename)) {
    $jsonResponse['msg'] = $previousMessages . "<span style='color: red'>Нет доступа к файлу обновления во временном каталоге :(</span><br/>";
    JsonOut($jsonResponse);
}

$targetDir = dirname(__FILE__, 2);
if (!is_writable($targetDir) || !is_writable($targetDir . "/index.php")) {
    $jsonResponse['msg'] = $previousMessages . "<span style='color: red'>Нет доступа к файлам IANSEO, обновление невозможно :(</span><br/>";
    JsonOut($jsonResponse);
}

require_once(dirname(__FILE__, 2) . '/Common/PclZip/pclzip.lib.php');

$zipArchive = new PclZip($fullFilename);
$files = $zipArchive->extract(PCLZIP_OPT_PATH, $targetDir, PCLZIP_OPT_REPLACE_NEWER);
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
