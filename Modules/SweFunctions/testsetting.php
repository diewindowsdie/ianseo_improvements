<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

CheckTourSession(true);

require_once(dirname(__FILE__).'/config.php');
// The transport lives in Functions.php. This file used to carry its own copy, which passed the server's
// body through untouched: a rejected or non-JSON answer reached the page with no 'message' in it and the
// operator was shown the word "undefined". One implementation, one envelope.
require_once(dirname(__FILE__).'/Functions.php');

if (!$swe_configured) {
    echo json_encode(array('status' => 'Failed', 'message' => $swelang_notconfigured));
    exit;
}

$url = $swe_url_test;
// Read post_body for json data
$request_body = file_get_contents('php://input');
$data = json_decode($request_body,true);

$response = sendInformation($url, json_encode($data));

// Testing the settings is the organizer asking "is my connection to the server good?", so it re-asks the
// server which version it speaks rather than trusting the cached answer. That is what makes the day a
// federation upgrades its server a non-event: press Test, and the page updates itself.
swe_forget_api_version($swe_host);
$detected = swe_detect_api_version($swe_host, isset($swe_api_version_force) ? $swe_api_version_force : null);

$decoded = json_decode($response, true);
if (is_array($decoded)) {
    $decoded['apiVersion'] = $detected;
    echo json_encode($decoded, JSON_UNESCAPED_UNICODE);
} else {
    // sendInformation() always returns the envelope, so this is unreachable; kept so a future change
    // to the transport cannot leave the page with nothing at all.
    echo $response;
}
