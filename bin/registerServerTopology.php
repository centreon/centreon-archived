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

require_once('registerServerTopology-Func.php');

/************************ */
/*     DATA RESOLVING
/************************ */

/**
 * Get script params
 */
const TYPE_POLLER = 'poller';
const TYPE_REMOTE = 'remote';
const TYPE_MAP = 'map';
const TYPE_MBI = 'mbi';
const SERVER_TYPES = [TYPE_POLLER, TYPE_REMOTE, TYPE_MAP, TYPE_MBI];

$opt = getopt('u:t:h:n:', ["help::", "root:", "dns:", "insecure::", "template:"]);
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
  --root <optional>           your root Centreon folder (by default "centreon")
  --dns <optional>            provide your server DNS instead of IP. The DNS must be resolvable on the Central.
  --insecure <optional>       allow self-signed certificate
  --template <optional>       give the path of a register topology configuration to automate the script
             - API_USERNAME             <mandatory> string
             - API_PASSWORD             <mandatory> string
             - SERVER_TYPE              <mandatory> string
             - HOST_ADDRESS             <mandatory> string
             - SERVER_NAME              <mandatory> string
             - ROOT_CENTREON_FOLDER     <optional> string
             - DNS                      <optional> string
             - INSECURE                 <optional> boolean
             - PROXY_USAGE              <optional> boolean
             - PROXY_HOST               <optional> string
             - PROXY_PORT               <optional> integer
             - PROXY_USERNAME           <optional> string
             - PROXY_PASSWORD           <optional> string

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
$configOptions = [];
if (isset($opt['template'])) {
    $configTemplate = parseTemplateFile($opt['template']);
    try {
        $configOptions = setConfigOptionsFromTemplate($configTemplate, $helpMessage);
    } catch (\InvalidArgumentException $e) {
        exit($e->getMessage());
    }
} else {
    try {
        if (!isset($opt['u'], $opt['t'], $opt['h'], $opt['n'])) {
            throw new \InvalidArgumentException(
                PHP_EOL . 'missing parameter: -u -t -h -n are mandatories:' . PHP_EOL . $helpMessage
            );
        }

        $configOptions['API_USERNAME'] = $opt['u'];
        $configOptions['SERVER_TYPE'] = in_array(strtolower($opt['t']), SERVER_TYPES)
            ? strtolower($opt['t'])
            : false;

        if (!$configOptions['SERVER_TYPE']) {
            throw new \InvalidArgumentException(
                "-t must be one of those value"
                    . PHP_EOL . "Poller, Remote, MAP, MBI" . PHP_EOL
            );
        }

        $configOptions['ROOT_CENTREON_FOLDER'] = $opt['root'] ?? 'centreon';
        $configOptions['HOST_ADDRESS'] = $opt['h'];
        $configOptions['SERVER_NAME'] = $opt['n'];

        if (isset($opt['dns'])) {
            $configOptions['DNS'] = filter_var($opt['dns'], FILTER_VALIDATE_DOMAIN);
            if (!$configOptions['DNS']) {
                throw new \InvalidArgumentException(
                    PHP_EOL . "Bad DNS Format" . PHP_EOL
                );
            }
        }
    } catch (\InvalidArgumentException $e) {
        exit($e->getMessage());
    }
    $configOptions['API_PASSWORD'] = askQuestion(
        $configOptions['HOST_ADDRESS'] . ': please enter your password ',
        true
    );
    $configOptions['PROXY_USAGE'] =  strtolower(askQuestion("Are you using a proxy ? (y/n)"));

    if (isset($opt['insecure'])) {
        $configOptions['INSECURE'] = true;
    }

    /**
     * Proxy informations
     */
    if ($configOptions['PROXY_USAGE'] === 'y') {
        $configOptions['PROXY_USAGE'] = true;
        $configOptions["PROXY_HOST"] = askQuestion('proxy host: ');
        $configOptions["PROXY_PORT"] = (int) askQuestion('proxy port: ');
        $configOptions["PROXY_USERNAME"] = askQuestion(
            'proxy username (press enter if no username/password are required): '
        );
        if (!empty($configOptions["PROXY_USERNAME"])) {
            $configOptions['PROXY_PASSWORD'] = askQuestion('please enter the proxy password: ', true);
        }
    }
}

/**
 * Parsing url part from params -h
 */
$targetURL = parse_url($configOptions['HOST_ADDRESS']);
$protocol = $targetURL['scheme'] ?? 'http';
$host = $targetURL['host'] ?? $targetURL['path'];
$port = $targetURL['port'] ?? '';


/**
 * prepare Login payload
 */
$loginCredentials = [
    "security" => [
        "credentials" => [
            "login" => $configOptions['API_USERNAME'],
            "password" => $configOptions['API_PASSWORD']
        ]
    ]
];

/**
 * Prepare Server Register payload
 */
$serverIp = trim(shell_exec("hostname -I | awk ' {print $1}'"));
$registerPayload = [
    "name" => $configOptions['SERVER_NAME'],
    "type" => $configOptions['SERVER_TYPE'],
    "address" => $configOptions['DNS'] ?? $serverIp,
    "parent_address" => $host
];

/**
 * Display Summary of action
 */
$address = $registerPayload["address"];
$username = $configOptions['API_USERNAME'];
$serverHostName = $registerPayload['name'];
$serverType = $registerPayload["type"];
$summary = <<<EOD

Summary of the informations that will be send:

Api Connection:
username: $username
password: ******
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
$loginUrl = $protocol . '://' . $host . '/' . $root;
if (!empty($port)) {
    $loginUrl .= ':' . $port;
}
$loginUrl .= '/api/latest/login';

try {
    $ch = curl_init($loginUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $loginCredentials);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if (isset($configOptions["INSECURE"])) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    if ($configOptions['PROXY_USAGE']) {
        curl_setopt($ch, CURLOPT_PROXY, $configOptions["PROXY_HOST"]);
        curl_setopt($ch, CURLOPT_PROXYPORT, $configOptions["PROXY_PORT"]);
        if (!empty($configOptions["PROXY_USERNAME"])) {
            curl_setopt(
                $ch,
                CURLOPT_PROXYUSERPWD,
                $configOptions["PROXY_USERNAME"] . ':' . $configOptions['PROXY_PASSWORD']
            );
        }
    }

    $result = curl_exec($ch);

    if ($result == false) {
        throw new Exception(curl_error($ch) . PHP_EOL);
    }
} catch (\Exception $e) {
    exit($e->getMessage());
} finally {
    curl_close($ch);
}

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
$registerUrl = $protocol . '://' . $host . '/' . $root;
if (!empty($port)) {
    $registerUrl .= ':' . $port;
}
$registerUrl .= "/api/latest/platform/topology";

try {
    $ch = curl_init($registerUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "X-AUTH-TOKEN: $APIToken"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $registerPayload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if (isset($configOptions["INSECURE"])) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    if (isset($configOptions['PROXY_USAGE'])) {
        curl_setopt($ch, CURLOPT_PROXY, $configOptions["PROXY_HOST"]);
        curl_setopt($ch, CURLOPT_PROXYPORT, $configOptions["PROXY_PORT"]);
        if (!empty($configOptions["PROXY_USERNAME"])) {
            curl_setopt(
                $ch,
                CURLOPT_PROXYUSERPWD,
                $configOptions["PROXY_USERNAME"] . ':' . $configOptions['PROXY_PASSWORD']
            );
        }
    }

    $result = curl_exec($ch);

    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($result === false) {
        throw new Exception(curl_error($ch) . PHP_EOL);
    }
} catch (Exception $e) {
    exit($e->getMessage());
} finally {
    curl_close($ch);
}

$result = json_decode($result, true);

/**
 * Display response of API
 */
if ($responseCode === 201) {
    $responseMessage = "The '$serverType' Platform: '$serverHostName@$address' linked to '$host' has been added";
    exit(formatResponseMessage($responseCode, $responseMessage, 'success'));
} elseif (isset($result['code'], $result['message'])) {
    exit(formatResponseMessage($result['code'], $result['message'], 'error'));
} else {
    exit(formatResponseMessage(500, 'An error occurred while contacting the API', 'error'));
}
