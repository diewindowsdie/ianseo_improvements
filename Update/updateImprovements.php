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

$root = dirname($_SERVER["SCRIPT_FILENAME"]) . "/../";
if (!is_writable($root)) {
    echo "<span><b>Папка с IANSEO недоступна для записи, обновление невозможно.</b></span>";
}

$opts = array('http' =>
    array(
        'method' => 'GET',
        'header' => "Accept: application/vnd.github+json\r\n".
            "User-Agent: ianseo_improvements\r\n"
    )
);
$context = stream_context_create($opts);
$apiResponse = file_get_contents("https://api.github.com/repos/diewindowsdie/ianseo_improvements/releases", false, $context);
$releases = json_decode($apiResponse, false);

echo "<span id='main'>\n";

$usePreReleases = defined('UsePreReleases') ? UsePreReleases : false;

//найдем самый последний релиз (или пре-релиз для случая live)
$latestReleaseIndex = null;
for ($i = 0; $i < count($releases); ++$i) {
    if ($releases[$i]->prerelease == $usePreReleases) {
        $latestReleaseIndex = $i;
        break;
    }
}

//найдем все релизы от текущего (если он известен) до последнего
if (!defined('CurrentTag') || $latestReleaseIndex->tag_name !== CurrentTag) {
    //сначала попытаемся найти установленный релиз
    $currentReleaseIndex = -1;
    for ($i = 0; $i < count($releases); ++$i) {
        if ($releases[$i]->tag_name === CurrentTag) {
            $currentReleaseIndex = $i;
            break;
        }
    }
    //если мы ничего не нашли - мы не знаем какой релиз стоит сейчас и нужно дать возможность обновления только до последнего релиза (или пре-релиза)
    if ($currentReleaseIndex === -1) {
        $currentReleaseIndex = $latestReleaseIndex + 1;
    }
    for ($i = 0; $i < $currentReleaseIndex; ++$i) {
        //покажем все доступные релизы или пре-релизы
        if ($releases[$i]->prerelease == $usePreReleases) {
            echo "<span>Доступна новая версия: <b>" . $releases[$i]->tag_name . "</b>:<br/>";
            echo nl2br($releases[$i]->body) . "<br/>";
            if (is_writable($root)) {
                echo "<div class='Button' onclick='update_improvements(\"" . $releases[$i]->assets[0]->browser_download_url . "\")'/>Обновить до версии " . $releases[$i]->tag_name . "</div>\n";
                echo "<b>Размер файла для скачивания: " . round($releases[$i]->assets[0]->size / 1000000) . " мб.</b><br/><br/>";
            }
        }
    }
} else {
    echo "<span>У вас последняя версия: <b>" . CurrentTag . "</b>, обновление не требуется.</span>";
}
echo "</span>";

include('Common/Templates/tail.php');