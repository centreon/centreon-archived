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

require_once('registerServerTopology-func.php');

/************************ */
/*     DATA RESOLVING
/************************ */

/**
 * Get script params
 */
$opt = getopt('u:t:h:n:', ["help::", "proxy::", "dns:", "autosigned::"]);
const SERVER_TYPE = ["poller", "remote", "map", "mbi"];

/**
 * Format the --help message
 */
$helpMessage = <<<'EOD'

Global Options:
  -u <mandatory>              username of your centreon-web account
  -t <mandatory>              the server type you want to register:
            - Poller
            - Remote
            - MAP
            - MBI
  -h <mandatory>              URL of the Central / Remote Server target platform
  -n <mandatory>              name of your registered server
  --help <optional>           get informations about the parameters available
  --proxy <optional>          provide the differents asked informations
  --dns <optional>            provide your DNS instead of automaticaly get server IP
  --autosigned <optional>     allow autosigned certificate


EOD;

/**
 * Display --help message
 */
if (isset($opt['help'])) {
    exit($helpMessage);
}

/**
 * Assign options to variables
 */
try {
    if (!isset($opt['u'], $opt['t'], $opt['h'], $opt['n'])) {
        throw new \InvalidArgumentException(
            PHP_EOL . 'missing parameter: -u -t -h -n are mandatories:' . PHP_EOL . $helpMessage
        );
    }

    $username = $opt['u'];
    $serverType = in_array(strtolower($opt['t']), SERVER_TYPE)
        ? strtolower($opt['t'])
        : false;

    if (!$serverType) {
        throw new \InvalidArgumentException(
            "-t must be one of those value"
            . PHP_EOL . "Poller, Remote, MAP, MBI" . PHP_EOL
        );
    }

    if (isset($opt['dns'])) {
        $dns = filter_var($opt['dns'], FILTER_VALIDATE_DOMAIN);
        if (!$dns) {
            throw new \InvalidArgumentException(
                PHP_EOL . "Bad DNS Format" . PHP_EOL
            );
        }
    }

    $targetHost = $opt['h'];
    $serverHostName = $opt['n'];
} catch (\InvalidArgumentException $e) {
    exit($e->getMessage());
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
}

/**
 * prepare Login payload
 */
$loginCredentials = [
    "security" => [
        "credentials" => [
            "login" => $username,
            "password" => $password
        ]
    ]
];

/**
 * Prepare Server Register payload
 */

$serverIp = trim(shell_exec("hostname -I | awk ' {print $1}'"));
$registerPayload = [
    "name" => $serverHostName,
    "type" => $serverType,
    "address" => $dns ?? $serverIp
];

/**
 * Display Summary of action
 */
$address = $registerPayload["address"];
$summary = <<<EOD

Summary of the informations that will be send:

Api Connection:
username: $username
password: $password
target server: $host

Pending Registration Server:
name: $serverHostName
type: $serverType
address: $address


EOD;

/**
 * Convert payloads to JSON
 */
$loginCredentials = json_encode($loginCredentials);
$registerPayload = json_encode($registerPayload);

echo $summary;

$proceed = askQuestion('Do you want to register this server with those informations ? (y/n)');
$proceed = strtolower($proceed);
if ($proceed !== "y") {
    exit();
}

/************************ */
/*     API REQUEST
/************************ */

/**
 * Connection to Api
 */
$loginUrl = $protocol . '://' . $host;
if (!empty($port)) {
    $loginUrl .= ':' . $port;
}
$loginUrl .= '/centreon/api/latest/login';

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginCredentials);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if(isset($opt['autosigned'])) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER	, false);
}

if (isset($proxyInfo)) {
    curl_setopt($ch, CURLOPT_PROXY, $proxyInfo['host']);
    curl_setopt($ch, CURLOPT_PROXYPORT, $proxyInfo['port']);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyInfo['username'] . ':' . $proxyInfo['password']);
}

$result = curl_exec($ch);

if (!$result) {
    exit(curl_error($ch));
}

curl_close($ch);
$result = json_decode($result, true);

/**
 * Save Token or return the error message
 */
if (isset($result['security']['token'])) {
    $APIToken = $result['security']['token'];
} elseif (isset($result['code'])) {
    exit(formatResponseMessage($result['code'], $result['message'], 'error'));
} else {
    exit(formatResponseMessage(400, 'Can\'t connect to the api', 'error'));
}

/**
 * POST Request to server registration endpoint
 */
$registerUrl = $protocol . '://' . $host;
if (!empty($port)) {
    $registerUrl .= ':' . $port;
}
$registerUrl .= "/centreon/api/latest/platform_topology";

$ch = curl_init($registerUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "X-AUTH-TOKEN: $APIToken"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $registerPayload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if(isset($opt['autosigned'])) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER	, false);
}

if (isset($proxyInfo)) {
    curl_setopt($ch, CURLOPT_PROXY, $proxyInfo['host']);
    curl_setopt($ch, CURLOPT_PROXYPORT, $proxyInfo['port']);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyInfo['username'] . ':' . $proxyInfo['password']);
}

$result = curl_exec($ch);

$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($result === false) {
    exit(curl_error($ch));
}

curl_close($ch);
$result = json_decode($result, true);

/**
 * Display response of API
 */
if ($responseCode === 201) {
    $responseMessage = "The '$serverType' Platform: '$serverHostName@$address' linked to '$host' has been added";
    exit(formatResponseMessage($responseCode, $responseMessage, 'success'));
} elseif (isset($result['code'], $result['message'])) {
    exit(formatResponseMessage($result['code'], $result['message'], 'success'));
} else {
    exit(formatResponseMessage(500, 'An error occured while contacting the API', 'error'));
}
