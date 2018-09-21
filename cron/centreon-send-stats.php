<?php
require_once dirname(__FILE__) . '/../bootstrap.php';
require '../config/config-statistics.php';

$sendStatistics = 0;
$isRemote = 0;

$db = $dependencyInjector['configuration_db'];
$result = $db->query("SELECT `value` FROM `options` WHERE `key` = 'send_statistics'");
if ($row = $result->fetch()) {
    (int)$sendStatistics = $row['value'];
}

$result = $db->query("SELECT `value` FROM `informations` WHERE `key` = 'isRemote'");
if ($row = $result->fetch()) {
    (int)$isRemote = $row['value'];
}

if ($sendStatistics && !$isRemote) {

    // Retrieve token and httpcode from authentication API
    retrieveAuthenticationToken($token, $httpCode);
    $timestamp = time();

    // If authentication API if alive, add the information
    if ($httpCode != 200) {
        echo $timestamp . " : No live information given.";
        return;
    }

    $ch = curl_init();
    $auth_header[] = 'Content-type: application/json';
    $auth_header[] = 'centreon-auth-token: ' . $token;

    // Settings parameters for curl queries
    curl_setopt($ch, CURLOPT_HTTPHEADER, $auth_header);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    // Retrieve UUID
    curl_setopt($ch, CURLOPT_URL, WS_ROUTE . UUID_RESOURCE);
    $UUID = curl_exec($ch);
    $UUID = json_decode($UUID, true);

    if (empty($UUID)) {
        \error_log($timestamp . " : No UUID specified");
        return;
    }

    // Retrieve versionning
    curl_setopt($ch, CURLOPT_URL, WS_ROUTE . VERSIONNING_RESOURCE);
    $versions = curl_exec($ch);
    $versions = json_decode($versions, true);

    // Retrieve informations
    curl_setopt($ch, CURLOPT_URL, WS_ROUTE . INFOS_RESOURCE);
    $infos = curl_exec($ch);
    $infos = json_decode($infos, true);

    // Retrieve zone
    curl_setopt($ch, CURLOPT_URL, WS_ROUTE . INFO_TIMEZONE);
    $timez = curl_exec($ch);
    $timez = json_decode($timez, true);

    // Construct the object gathering datas
    $data = array(
        'timestamp' => $timestamp,
        'UUID' => $UUID,
        'versions' => $versions,
        'infos' => $infos,
        'timezone' => $timez
    );

    $data = json_encode($data, JSON_PRETTY_PRINT);

    // Open connection
    $ch = curl_init();
    // Set the url
    curl_setopt($ch, CURLOPT_URL, CENTREON_STATS_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    if (curl_exec($ch) === false) {
        \error_log($timestamp .' : centreon-send-stats.php --- ' . curl_error($ch));
    }
    curl_close($ch);
}

/**
 *    Set the token and the httpCode by quering authentication API.
 */
function retrieveAuthenticationToken(&$token, &$httpCode)
{
    $tokenFieldLabel = 'authToken';

    /* Add content type to headers */
    $headers[] = 'Content-type: application/x-www-form-urlencoded';
    $headers[] = 'Connection: close';

    /* Setting curl options */
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, AUTH_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'username=' . USERNAME . '&password=' . PASSWORD);

    /* Execute curl command on authentication API */
    $authenticationResult = curl_exec($ch);

    /* Http code retrieval */
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    /* Token retrieval, then use token to request */
    $authenticationResult = json_decode($authenticationResult, true);
    $token = $authenticationResult[$tokenFieldLabel];
}
