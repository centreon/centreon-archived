<?php

/*
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

function askQuestion($question, $hidden = false)
{
    if ($hidden) {
        system("stty -echo");
    }
    printf("%s", $question);
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);
    if ($hidden) {
        system("stty echo");
    }
    printf("\n");
    return $response;
}

/**
 * Get script params
 */
$opt = getopt('u:t:h:', ["help:","proxy::"]);
/**
 * Gestion de  mdp CHEKC /tools/update_centreon_storage_logs
 */
/**
 * Format the --help message
 */
$helpMessage = <<<'EOD'
Global Options:
  -u <mandatory>              username of your centreon-web account
  -t <mandatory>              the server type you want to register:
            0: Central
            1: Poller
            2: Remote Server
            3: MAP Server
            4: MBI Server
  -h <mandatory>              URL of the Central / Remote Server target platform
  --help <optional>           get informations about the parameters available
  --proxy <optional>          provide the differents asked informations

EOD;

/**
 * Display --help message
 */
if (isset($opt['help'])) {
    echo $helpMessage;
    exit;
}

/**
 * Assign options to variables
 */
try {
    if (!isset($opt['u'], $opt['t'], $opt['h'])) {
        throw new \InvalidArgumentException(
            'missing parameter: -u -t -h are mandatories:' . PHP_EOL . $helpMessage
        );
    }

    $username = $opt['u'];
    $serverType =
        filter_var($opt['t'], FILTER_VALIDATE_INT)
        && in_array($opt['t'],[0, 1, 2, 3, 4])
        ? $opt['t']
        : false;

    if (!$serverType) {
        throw new \InvalidArgumentException(
            "-t must be one of those value"
            . PHP_EOL . "0 => Central, 1 => Poller, 2 => Remote Server, 3 => Map Server, 4 => MBI Server" . PHP_EOL
        );
    }

    $targetHost = $opt['h'];
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage();
    exit;
}
$password = askQuestion($targetHost . ': enter your password ', true);

/**
 * Parsing url part from params -h
 */
$targetURL = parse_url($targetHost);
$protocol = $targetURL['scheme'] ?? 'http';
$host = $targetURL['host'] ?? $targetURL['path'];
$port = $targetURL['port'] ?? '';

/**
 * Proxy informations
 */
if (isset($opt['proxy'])) {
    $proxyInfo['username'] = askQuestion('proxy username: ');
    $proxyInfo['password'] = askQuestion('proxy password: ', true);
    $proxyInfo['host'] = askQuestion('proxy host: ');
    $proxyInfo['port'] = (int) askQuestion('proxy port: ');
    var_dump($proxyInfo);
}

/**
 * prepare payload for login to API
 */
$credentials = [
    "security" => [
        "credentials" => [
            "login" => $username,
            "password" => $password
        ]
    ]
];
$credentials = json_encode($credentials);
$version = "beta";

/**
 * Connection to Api
 */
$loginUrl = $protocol . '://' . $host;
if(!empty($port)){
    $loginUrl .= ':' . $port;
}
$loginUrl .= '/centreon/api/' . $version . '/login';

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $credentials);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if (isset($proxyInfo)) {
    curl_setopt($ch, CURLOPT_PROXY, $proxyInfo['host']);
    curl_setopt($ch, CURLOPT_PROXYPORT, $proxyInfo['port']);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyInfo['username'] . ':' . $proxyInfo['password']);
}

$result = curl_exec($ch);

if(!$result){
    echo curl_error($ch);
    exit;
}

curl_close($ch);
$result = json_decode($result, true);

/**
 * Save Token or return the error message
 */
if (isset($result['security']['token'])) {
    $APIToken = $result['security']['token'];
} elseif (isset($result['code'])) {
    echo 'error code: ' . $result['code'] . PHP_EOL;
    echo 'error message: ' . $result['message'] . PHP_EOL;
    exit;
} else {
    echo 'unhandled error' . PHP_EOL;
    exit;
}

/**
 * Prepare Server Register payload
 */
$host = gethostname();

$serverIp = shell_exec("hostname -I | awk ' {print $1}'");

$payload = [
    "server_name" => $host,
    "server_type" => $serverType,
    "ip_address" => trim($serverIp)
];
var_dump($APIToken,$payload);
die;
/**
 * POST Request to server registration endpoint
 */
$curlURL = $protocol . '://' . $host;
if(!empty($port)){
    $curlURL .= ':' . $port;
}
$curlURL .= "/centreon/api/$version/configuration/register";

$ch = curl_init($curlURL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "X-AUTH-TOKEN: $APIToken"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if (isset($proxyInfo)) {
    curl_setopt($ch, CURLOPT_PROXY, $proxyInfo['host']);
    curl_setopt($ch, CURLOPT_PROXYPORT, $proxyInfo['port']);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyInfo['username'] . ':' . $proxyInfo['password']);
}

$result = curl_exec($ch);

if(!$result){
    echo curl_error($ch);
    exit;
}

curl_close($ch);
/**
 * TODO: Handle Response
 */