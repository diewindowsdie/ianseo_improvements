<?php
$previousMessages = "<span>Начинаем процесс обновления...</span><br/><span>Скачиваем файл <b>" . $_REQUEST["url"] . "</b>...</span><br /><span>Файл обновления успешно сохранен во временный каталог.</span><br />";

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

