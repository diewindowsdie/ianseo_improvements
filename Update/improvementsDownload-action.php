<?php
$previousMessages = "<span>Начинаем процесс обновления...</span><br/><span>Скачиваем файл <b>" . $_REQUEST["url"] . "</b>...</span><br />";

$jsonResponse=array('error'=>1, 'msg'=> '');
define('DIRNAME', dirname($_SERVER['SCRIPT_FILENAME']));
require_once(dirname(DIRNAME) . '/config.php');
checkFullACL(AclRoot, '', AclReadWrite);

if(!empty($_SESSION['AUTH_ENABLE']) AND empty($_SESSION['AUTH_ROOT'])) {
    $jsonResponse['msg'] = $previousMessages . "<span style='color: red'>Недостаточно прав для обновления IANSEO :(</span><br/>";
    JsonOut($jsonResponse);
}

$tempDir = sys_get_temp_dir();
$filename = basename($_REQUEST['url']);
$fullFilename = $tempDir . "/" . $filename;
if (!is_writable($tempDir)) {
    $jsonResponse['msg'] = $previousMessages . "<span style='color: red'>Временный каталог сервера недоступен для записи, невозможно скачать обновление :(</span><br/>";
    JsonOut($jsonResponse);
}

$opts = array('http' =>
    array(
        'method' => 'GET',
        'header' => 'User-Agent: curl/8.17.0\r\n'
    )
);
$context = stream_context_create($opts);
$file = fopen($_REQUEST["url"], 'rb', false, $context);
if (!$file) {
    $jsonResponse['msg'] = $previousMessages . "<span style='color: red'>Произошла ошибка при сохранении файла обновления во временный каталог: <b>" . ($http_response_header[0] ? $http_response_header[0] : "Неизвестная ошибка") . "</b></span><br/>";
    JsonOut($jsonResponse);
}
$bytesWritten = file_put_contents($fullFilename, $file);
if ($bytesWritten === false) {
    $jsonResponse['msg'] = $previousMessages . "<span style='color: red'>Произошла ошибка при сохранении файла обновления во временный каталог :(</span><br/>";
    JsonOut($jsonResponse);
}

$jsonResponse['error'] = 0;
$jsonResponse['msg'] = $previousMessages . "<span>Файл обновления успешно сохранен во временный каталог.</span><br />";
JsonOut($jsonResponse);