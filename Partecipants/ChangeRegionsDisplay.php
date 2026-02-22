<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Common/Lib/CommonLib.php');

if (!CheckTourSession()) {
    CD_redirect($CFG->ROOT_DIR);
}

checkACL(array(AclCompetition), AclReadWrite);

if (isset($_REQUEST["set"])) {
    setModuleParameter(REGION_DISPLAY_MODULE_NAME, $_REQUEST["set"], $_REQUEST["value"], $_SESSION['TourId']);
} else {
    $IncludeFA = true;
    $IncludeJquery = true;

    $JS_SCRIPT=array(
        '<script type="text/javascript" src="ChangeRegionDisplay.js"></script>'
    );

    include('Common/Templates/head.php');

    echo '
    <form>
        <table class="Tabella w-40" style="margin: 20px">
            <tr><th colspan="4" style="padding: 10px">Укажите, в каких случаях и данные из каких полей "Регион Х" спортсменов требуется отображать:</th></tr>
            <tr><th></th><th>Регион 1</th><th>Регион 2</th><th>Регион 3</th></tr>
            <tr>
                <td class="bold">Распечатки</td>
                <td class="Center"><input type="checkbox" id="' . REGION_ORIGIN_PRINTOUT . '_1" ' . (getModuleParameter(REGION_DISPLAY_MODULE_NAME, REGION_ORIGIN_PRINTOUT . "_1", "1", $_SESSION['TourId']) ? 'checked="checked"' : '') . ' onchange="setRegionFieldDisplay(this)"/></td>
                <td class="Center"><input type="checkbox" id="' . REGION_ORIGIN_PRINTOUT . '_2" ' . (getModuleParameter(REGION_DISPLAY_MODULE_NAME, REGION_ORIGIN_PRINTOUT . "_2", "1", $_SESSION['TourId']) ? 'checked="checked"' : '') . ' onchange="setRegionFieldDisplay(this)"/></td>
                <td class="Center"><input type="checkbox" id="' . REGION_ORIGIN_PRINTOUT . '_3" ' . (getModuleParameter(REGION_DISPLAY_MODULE_NAME, REGION_ORIGIN_PRINTOUT . "_3", "1", $_SESSION['TourId']) ? 'checked="checked"' : '') . ' onchange="setRegionFieldDisplay(this)"/></td>
            </tr>
            <tr>
                <td class="w-25 bold">Трансляции результатов</td>
                <td class="Center"><input type="checkbox" id="' . REGION_ORIGIN_TRANSLATION . '_1" ' . (getModuleParameter(REGION_DISPLAY_MODULE_NAME, REGION_ORIGIN_TRANSLATION . "_1", "1", $_SESSION['TourId']) ? 'checked="checked"' : '') . ' onchange="setRegionFieldDisplay(this)"/></td>
                <td class="Center"><input type="checkbox" id="' . REGION_ORIGIN_TRANSLATION . '_2" ' . (getModuleParameter(REGION_DISPLAY_MODULE_NAME, REGION_ORIGIN_TRANSLATION . "_2", "1", $_SESSION['TourId']) ? 'checked="checked"' : '') . ' onchange="setRegionFieldDisplay(this)"/></td>
                <td class="Center"><input type="checkbox" id="' . REGION_ORIGIN_TRANSLATION . '_3" ' . (getModuleParameter(REGION_DISPLAY_MODULE_NAME, REGION_ORIGIN_TRANSLATION . "_3", "1", $_SESSION['TourId']) ? 'checked="checked"' : '') . ' onchange="setRegionFieldDisplay(this)"/></td>
            </tr>
            <tr>
                <td class="w-5 bold">Интерфейс</td>
                <td class="Center"><input type="checkbox" id="' . REGION_ORIGIN_INTERFACE . '_1" ' . (getModuleParameter(REGION_DISPLAY_MODULE_NAME, REGION_ORIGIN_INTERFACE . "_1", "1", $_SESSION['TourId']) ? 'checked="checked"' : '') . ' onchange="setRegionFieldDisplay(this)"/></td>
                <td class="Center"><input type="checkbox" id="' . REGION_ORIGIN_INTERFACE . '_2" ' . (getModuleParameter(REGION_DISPLAY_MODULE_NAME, REGION_ORIGIN_INTERFACE . "_2", "1", $_SESSION['TourId']) ? 'checked="checked"' : '') . ' onchange="setRegionFieldDisplay(this)"/></td>
                <td class="Center"><input type="checkbox" id="' . REGION_ORIGIN_INTERFACE . '_3" ' . (getModuleParameter(REGION_DISPLAY_MODULE_NAME, REGION_ORIGIN_INTERFACE . "_3", "1", $_SESSION['TourId']) ? 'checked="checked"' : '') . ' onchange="setRegionFieldDisplay(this)"/></td>
            </tr>
        </table>
        </form>
';
    include('Common/Templates/tail.php');
}
