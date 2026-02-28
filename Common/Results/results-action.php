<?php
require_once dirname(__FILE__, 3) . '/config.php';
require_once('Common/Lib/CommonLib.php');
require_once "lib.php";

global $CFG;

if (!CheckTourSession()) {
    CD_redirect($CFG->ROOT_DIR);
}

checkACL(array(AclCompetition), AclReadWrite);

if (!isset($_REQUEST["action"])) {
    exit();
}

switch ($_REQUEST["action"]) {
    case "set-basic":
        $value = $_REQUEST["value"];
        if ($_REQUEST["invert"] === "true") {
            $value = $value === "0" ? "1" : "0";
        }

        setModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, $_REQUEST["param"], $value, $_SESSION["TourId"]);
        break;
    case "set-protocol":
        setModuleParameter(PROTOCOL_MODULE, $_REQUEST["param"], $_REQUEST["value"], $_SESSION["TourId"]);
        break;
    case "set-displayed-session":
        $currentValue = getModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, SHOW_FINAL_SESSIONS_IN_SCHEDULE_PARAM_NAME, array(), $_SESSION["TourId"]);

        $split = explode("_", $_REQUEST["param"]);
        $sessionId = array_pop($split);
        if ($_REQUEST["value"] === "0") {
            $currentValue = array_diff($currentValue, [$sessionId]);
        } else {
            $currentValue[] = $sessionId;
        }

        setModuleParameter(RESULTS_PUBLICATION_MODULE_NAME, SHOW_FINAL_SESSIONS_IN_SCHEDULE_PARAM_NAME, $currentValue, $_SESSION["TourId"]);
        break;
}
