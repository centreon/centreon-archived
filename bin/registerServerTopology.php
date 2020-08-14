<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
$opt = getopt('u:t:h:n:', ["help::", "dns:", "insecure::"]);
const SERVER_TYPES = ["poller", "remote", "map", "mbi"];

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
            - host <mandatory>
            - port <mandatory>
            - username <optional>
            - password <optional>
  --dns <optional>            provide your DNS instead of automaticaly get server IP
  --insecure <optional>       allow self-signed certificate


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
    $serverType = in_array(strtolower($opt['t']), SERVER_TYPES)
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
$proxy =  strtolower(askQuestion("Are you using a proxy ? (y/n)"));
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
if ($proxy === 'y') {
    $proxyInfo['host'] = askQuestion('proxy host: ');
    $proxyInfo['port'] = (int) askQuestion('proxy port: ');
    $proxyInfo['username'] = askQuestion('proxy username (press enter if no username/password required): ');
    if (!empty($proxyInfo['username'])) {
        $proxyInfo['password'] = askQuestion('proxy password: ', true);
    }
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
    "address" => $dns ?? $serverIp,
    "parent_address" => $host
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
parent server address: $host


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
$loginUrl .= '/api/latest/login';

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginCredentials);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if (isset($opt['insecure'])) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
}

if (isset($proxyInfo)) {
    curl_setopt($ch, CURLOPT_PROXY, $proxyInfo['host']);
    curl_setopt($ch, CURLOPT_PROXYPORT, $proxyInfo['port']);
    if (!empty($proxyInfo['username'])) {
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyInfo['username'] . ':' . $proxyInfo['password']);
    }
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
$registerUrl .= "/api/latest/platform/topology";

$ch = curl_init($registerUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "X-AUTH-TOKEN: $APIToken"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $registerPayload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if (isset($opt['insecure'])) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
}

if (isset($proxyInfo)) {
    curl_setopt($ch, CURLOPT_PROXY, $proxyInfo['host']);
    curl_setopt($ch, CURLOPT_PROXYPORT, $proxyInfo['port']);
    if (!empty($proxyInfo['username'])) {
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyInfo['username'] . ':' . $proxyInfo['password']);
    }
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
