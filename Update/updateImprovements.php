<?php

error_reporting(E_ALL);
require_once(dirname(__FILE__, 2) . '/config.php');
checkFullACL(AclRoot, '', AclReadWrite);

if(!empty($_SESSION['AUTH_ENABLE']) AND empty($_SESSION['AUTH_ROOT'])) {
    CD_redirect($CFG->ROOT_DIR.'noAccess.php');
}

require_once('Common/Lib/CommonLib.php');
$IncludeJquery = true;
$JS_SCRIPT=array(
    '<link rel="stylesheet" href="index.css">',
    '<script src="./index.js"></script>',
    '<script src="../Common/js/Fun_JS.inc.js"></script>',
);

include('Common/Templates/head.php');

$opts = array('http' =>
    array(
        'method' => 'GET',
        'header' => "Accept: application/vnd.github+json\r\n".
            "User-Agent: ianseo_improvements\r\n"
    )
);
$context = stream_context_create($opts);
$apiResponse = file_get_contents("https://api.github.com/repos/diewindowsdie/ianseo_improvements/releases/latest", false, $context);
$release_info = json_decode($apiResponse, false);

echo "<span id='main'>\n";
if (!defined('CurrentTag') || $release_info->tag_name !== CurrentTag) {
    echo "<span>Доступна новая версия: <b>" . $release_info->tag_name . "</b>:<br/><br/>";
    echo nl2br($release_info->body) . "<br/><br/>";
    echo "Размер файла для скачивания: " . round($release_info->assets[0]->size / 1000000). " мб.<br/><br/>";

    $root = dirname($_SERVER["SCRIPT_FILENAME"]) . "/../";
    if (!is_writable($root)) {
        echo "<span><b>Папка с IANSEO недоступна для записи, обновление невозможно.</b></span>";
    } else {
        echo "<div class='Button' onclick='update_improvements(\"" . $release_info->assets[0]->browser_download_url . "\")'/>Обновить</div>\n";
    }
} else {
    echo "<span>У вас последняя версия: <b>" . CurrentTag . "</b>, обновление не требуется.</span>";
}
echo "</span>";

include('Common/Templates/tail.php');