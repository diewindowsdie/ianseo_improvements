<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

CheckTourSession(true);

require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Lib/Obj_RankFactory.php');
// DecodeFromLetter() and the $LetterPoint table it reads. Obj_Rank_FinalInd pulls this in for its
// own arrowstrings, so the v2 qualification builder used to work only because the finals were read
// first — required here so the order of the builders cannot decide whether arrow data survives.
require_once('Common/Lib/ArrTargets.inc.php');

require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/Functions.php');

if (!$swe_configured) {
    echo json_encode(array('status' => 'Failed', 'message' => $swelang_notconfigured));
    exit;
}

// Read post_body for json data
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);
if (!is_array($data)) {
    $data = array();
}

$auth = isset($data['auth']) && is_array($data['auth']) ? $data['auth'] : array();

/*
 * The operator does not pick what to send: IANSEO knows which events the competition has, so both
 * versions always post everything they can produce. For v2 that also means ARS can replace the
 * competition's whole result set, instead of keeping rows from a competition imported earlier.
 */
if ((int)$swe_api_version >= 2) {
    $payload = buildPayloadV2($auth, $swe_module_version);
    echo sendInformation($swe_url_result_v2, json_encode($payload, JSON_UNESCAPED_UNICODE));
    exit;
}

/* ---------- v1: legacy payload shape ---------- */
/*
 * No finals, no 'final' key. That is exactly what the v1 endpoint received when the operator left the
 * Final checkbox unticked, before the checkboxes were removed, so it is the one shape every v1 server
 * already accepts. Norway and Iceland run most competitions without finals, and both other choices broke
 * the whole send: "" (what the single Send button first sent) cannot bind to a list, and [] is refused by
 * the v1 server as "Import data could not be parsed". Either way nothing was imported, the qualification
 * included.
 *
 * v1 only. The v2 endpoint must keep getting "final": [], because it clears old individual results only
 * when a payload speaks about BOTH qualification and final (CompetitionResultsV2Controller).
 *
 * !empty() rather than count(): a rank object that returned nothing at all makes count() a fatal on
 * PHP 8, which would take the JSON answer down with it.
 */
$rankData = getIndFinals();
$data = array(
    'auth'          => $auth,
    'qualification' => prepareIndQualification(getIndQualification()),
);
if (!empty($rankData['sections'])) {
    $data['final'] = prepareIndFinals($rankData);
}
echo sendInformation($swe_url_result, json_encode($data));
?>