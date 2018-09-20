<?php
require_once dirname(__FILE__) . '/../bootstrap.php';
require 'config.php';

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
    $time = time();
    // If authentication API if alive, add the information
    if ($httpCode == 200) {
        $alive = 1;
    } // Otherwise printing that instance is not alive in the file, then stop
    else {
        $data = array(
            'alive' => 0
        );
        writeOnFile($data, $time);
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
        'alive' => $alive,
        'timestamp' => $time,
        'UUID' => $UUID,
        'versions' => $versions,
        'infos' => $infos,
        'timezone' => $timez
    );
    writeOnFile($data, $time);
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

/**
 *    Write the data on a json file.
 */
function writeOnFile(&$data, $time)
{
    $filePath = STATS_PATH . '/' . STATS_PREFIX . $time . ".json";
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}
