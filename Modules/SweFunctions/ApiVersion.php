<?php
/**
 * SweFunctions - which API version the federation's result server speaks.
 *
 * The three federations share this module but upgrade their servers independently, so the version used
 * to be a constant per federation in config.php. That meant every organizer had to install a new module
 * on the day their federation's server gained v2 — for a change that happened on the server.
 *
 * The module now asks the server instead. v1 remains the answer whenever v2 cannot be confirmed, so a
 * server that has not been upgraded, or cannot be reached, keeps working exactly as before.
 *
 * HOW THE PROBE WORKS
 * A GET to the v2 results URL, which is POST-only:
 *   405 Method Not Allowed  the route exists  -> the server speaks v2
 *   404 Not Found           no such route     -> v1 only
 * Nothing is sent, nothing is imported, and no competition password is used — unlike probing with a
 * POST, which would have to carry credentials and would look like a failed login in the server's log.
 *
 * ANY other answer means v1. A 500, a proxy error page, a captive portal, a timeout: none of them is
 * evidence that v2 exists, and guessing wrong upwards breaks the send outright, while guessing wrong
 * downwards only loses the team and run-archery blocks. That asymmetry is why the default is v1 and not
 * "whatever we saw last".
 */

require_once(dirname(__FILE__).'/Federation.php');

/** Detected v2 is trusted for this long: a transient blip must not silently downgrade a working send. */
define('SWE_APIVER_TTL_V2', 12 * 3600);
/** A v1 answer is re-checked sooner, so the day a federation upgrades its server nobody has to be told. */
define('SWE_APIVER_TTL_V1', 900);

/**
 * @param string $host        Result server base URL, e.g. https://resultat.bueskyting.no
 * @param int|null $force     Pin a version and skip the probe entirely (config: $swe_api_version_force)
 * @return int 1 or 2
 */
function swe_detect_api_version($host, $force = null) {
    if ($force !== null && (int)$force > 0) {
        return (int)$force;
    }
    if (!is_string($host) || $host === '' || !function_exists('curl_init')) {
        return 1;
    }

    $key = md5($host);
    if (isset($_SESSION['swe_apiver'][$key])) {
        $hit = $_SESSION['swe_apiver'][$key];
        $ttl = ((int)$hit['v'] >= 2) ? SWE_APIVER_TTL_V2 : SWE_APIVER_TTL_V1;
        if (isset($hit['t']) && (time() - (int)$hit['t']) < $ttl) {
            return (int)$hit['v'];
        }
    }

    $version = swe_probe_api_version($host);

    if (!isset($_SESSION['swe_apiver']) || !is_array($_SESSION['swe_apiver'])) {
        $_SESSION['swe_apiver'] = array();
    }
    $_SESSION['swe_apiver'][$key] = array('v' => $version, 't' => time());
    return $version;
}

/**
 * One HTTP round trip, no body, short timeouts — this runs while the organizer waits for a page.
 *
 * @return int 1 or 2
 */
function swe_probe_api_version($host) {
    $url  = rtrim($host, '/') . '/api/v2/CompetitionResults';
    $hand = curl_init($url);
    curl_setopt($hand, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($hand, CURLOPT_NOBODY, true);
    curl_setopt($hand, CURLOPT_RETURNTRANSFER, true);
    // No redirect following: a login page or a captive portal answering 200 for everything must not be
    // read as "v2 exists". Only the method-not-allowed answer from the real route counts.
    curl_setopt($hand, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($hand, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($hand, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($hand, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($hand, CURLOPT_TIMEOUT, 5);
    curl_exec($hand);
    $errno = curl_errno($hand);
    $http  = (int)curl_getinfo($hand, CURLINFO_HTTP_CODE);
    curl_close($hand);

    if ($errno !== 0) {
        error_log("SweFunctions: API version probe failed for $url (cURL $errno) — using v1");
        return 1;
    }
    // 405 is the whole signal: the route is there and it only takes POST.
    return ($http === 405) ? 2 : 1;
}

/**
 * Forget the cached answer for a host. Called after a successful settings test, so an organizer who has
 * just been told their server upgraded does not have to wait out the TTL.
 */
function swe_forget_api_version($host) {
    if (is_string($host) && $host !== '') {
        unset($_SESSION['swe_apiver'][md5($host)]);
    }
}
