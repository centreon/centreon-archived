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
const TYPE_CENTRAL = 'central';
const TYPE_POLLER = 'poller';
const TYPE_REMOTE = 'remote';
const TYPE_MAP = 'map';
const TYPE_MBI = 'mbi';
const SERVER_TYPES = [TYPE_CENTRAL, TYPE_POLLER, TYPE_REMOTE, TYPE_MAP, TYPE_MBI];

$opt = getopt('u:t:h:n:', ["help::", "root:", "node-address:", "insecure::", "template:"]);
/**
 * Format the --help message
 */
$helpMessage = <<<'EOD'
This script will register a platform (CURRENT NODE) on another (TARGET NODE).
If you register a CURRENT NODE on a TARGET NODE that is already linked to a Central,
your informations will automatically be forwarded to the Central.
If you register a Remote Server, this script will automatically convert your CURRENT NODE in Remote Server.
After executing the script, please use the wizard on your Central to complete your installation.

Global Options:
  -u <mandatory>              username of your centreon-web account on the TARGET NODE.
  -h <mandatory>              URL of the TARGET NODE
  -t <mandatory>              the server type you want to register (CURRENT NODE):
            - Poller
            - Remote
            - MAP
            - MBI
  -n <mandatory>              name of the CURRENT NODE that will be displayed on the TARGET NODE

  --help <optional>           get information about the parameters available
  --root <optional>           your Centreon root path on TARGET NODE (by default "centreon")
  --node-address <optional>   provide your FQDN or IP of the CURRENT NODE. FQDN must be resolvable on the TARGET NODE
  --insecure <optional>       allow self-signed certificate
  --template <optional>       provide the path of a register topology configuration file to automate the script
             - API_USERNAME             <mandatory> string
             - API_PASSWORD             <mandatory> string
             - CURRENT_NODE_TYPE        <mandatory> string
             - TARGET_NODE_ADDRESS      <mandatory> string (PARENT NODE ADDRESS)
             - CURRENT_NODE_NAME        <mandatory> string (CURRENT NODE NAME)
             - PROXY_USAGE              <mandatory> boolean
             - ROOT_CENTREON_FOLDER     <optional> string (CENTRAL ROOT CENTREON FOLDER)
             - CURRENT_NODE_ADDRESS     <optional> string (CURRENT NODE IP OR FQDN)
             - INSECURE                 <optional> boolean
             - PROXY_HOST               <optional> string
             - PROXY_PORT               <optional> integer
             - PROXY_USERNAME           <optional> string
             - PROXY_PASSWORD           <optional> string

EOD;

/**
 * Display --help message
 */
if (isset($opt['help'])) {
    echo $helpMessage;
    exit(0);
}

/**
 * Assign options to variables
 */
$configOptions = [];
if (isset($opt['template'])) {
    try {
        $configTemplate = parseTemplateFile($opt['template']);
        $configOptions = setConfigOptionsFromTemplate($configTemplate, $helpMessage);
    } catch (\InvalidArgumentException $e) {
        echo $e->getMessage();
        exit(1);
    }
} else {
    try {
        if (!isset($opt['u'], $opt['t'], $opt['h'], $opt['n'])) {
            throw new \InvalidArgumentException(
                PHP_EOL . 'missing parameter: -u -t -h -n are mandatories:' . PHP_EOL . $helpMessage
            );
        }

        $configOptions['API_USERNAME'] = $opt['u'];
        $configOptions['CURRENT_NODE_TYPE'] = in_array(strtolower($opt['t']), SERVER_TYPES)
            ? strtolower($opt['t'])
            : null;

        if (!$configOptions['CURRENT_NODE_TYPE']) {
            throw new \InvalidArgumentException(
                "-t must be one of those value"
                    . PHP_EOL . "Poller, Remote, MAP, MBI" . PHP_EOL
            );
        }

        $configOptions['ROOT_CENTREON_FOLDER'] = $opt['root'] ?? 'centreon';
        $configOptions['TARGET_NODE_ADDRESS'] = $opt['h'];
        $configOptions['CURRENT_NODE_NAME'] = $opt['n'];

        if (isset($opt['node-address'])) {
            $configOptions['CURRENT_NODE_ADDRESS'] = filter_var($opt['node-address'], FILTER_VALIDATE_DOMAIN);
            if (!$configOptions['CURRENT_NODE_ADDRESS']) {
                throw new \InvalidArgumentException(
                    PHP_EOL . "Bad node-address Format" . PHP_EOL
                );
            }
        }
    } catch (\InvalidArgumentException $e) {
        echo $e->getMessage();
        exit(1);
    }
    $configOptions['API_PASSWORD'] = askQuestion(
        $configOptions['TARGET_NODE_ADDRESS'] . ': please enter your password ',
        true
    );
    $configOptions['PROXY_USAGE'] =  strtolower(askQuestion("Are you using a proxy? (y/n) "));

    if (isset($opt['insecure'])) {
        $configOptions['INSECURE'] = true;
    } else {
        $configOptions['INSECURE'] = false;
    }
    /**
     * Proxy information
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
    } else {
        $configOptions['PROXY_USAGE'] = false;
    }
}

/**
 * Parsing url part from params -h
 */
$targetURL = parse_url($configOptions['TARGET_NODE_ADDRESS']);
$host = $targetURL['host'] ?? $targetURL['path'];
$protocol = $targetURL['scheme'] ?? 'http';
$defaultPort = ('https' === $protocol) ? 443 : 80;
$port = $targetURL['port'] ?? $defaultPort;

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
$foundIps = explode(" ", trim(shell_exec("hostname -I")));
$foundIps = array_combine(range(1, count($foundIps)), array_values($foundIps));

$goodIp = false;

$ipSelection = 'Found IP on CURRENT NODE:' . PHP_EOL;
foreach ($foundIps as $key => $ip) {
        $ipSelection .= "   [$key]: $ip" . PHP_EOL;
}

while (!$goodIp) {
    echo $ipSelection;
    $ipChoice = askQuestion('Which IP do you want to use as CURRENT NODE IP? ');

    if (!array_key_exists($ipChoice, $foundIps)) {
        echo 'Bad IP Choice' . PHP_EOL;
    } else {
        $goodIp = true;
    }
}
$serverIp = $foundIps[$ipChoice];

$payload = [
    "name" => $configOptions['CURRENT_NODE_NAME'],
    "hostname" => gethostname(),
    "type" => $configOptions['CURRENT_NODE_TYPE'],
    "address" => $configOptions['CURRENT_NODE_ADDRESS'] ?? $serverIp,
];

if ($configOptions['CURRENT_NODE_TYPE'] !== TYPE_CENTRAL) {
    $payload["parent_address"] = $host;
}

/**
 * Display Summary of action
 */
$address = $payload["address"];
$username = $configOptions['API_USERNAME'];
$serverHostName = $payload['name'];
$serverType = $payload["type"];
$summary = <<<EOD

Summary of the information that will be send:

Api Connection:
username: $username
password: ******
target server: $host

Pending Registration Server:
name: $serverHostName
type: $serverType
address: $address


EOD;


echo $summary;

$proceed = askQuestion('Do you want to register this server with those information? (y/n) ');
$proceed = strtolower($proceed);
if ($proceed !== "y") {
    exit(0);
}

/**
 * Master-to-Remote transition
 */
if (isRemote($serverType)) {
    require_once(realpath(__DIR__ . '/../config/centreon.config.php'));
    require_once _CENTREON_PATH_ . '/www/class/centreonDB.class.php';

    require_once _CENTREON_PATH_ . '/src/Security/Interfaces/EncryptionInterface.php';
    require_once _CENTREON_PATH_ . '/src/Security/Encryption.php';

    require_once _CENTREON_PATH_ . '/src/Centreon/Infrastructure/CentreonLegacyDB/Mapping/ClassMetadata.php';
    require_once _CENTREON_PATH_ . '/src/Centreon/Infrastructure/CentreonLegacyDB/ServiceEntityRepository.php';
    require_once _CENTREON_PATH_ . '/src/Centreon/Domain/Repository/InformationsRepository.php';
    require_once _CENTREON_PATH_ . '/src/Centreon/Domain/Repository/TopologyRepository.php';

    define("SECOND_KEY", base64_encode('api_remote_credentials'));
    /*
     * Set encryption parameters
     */
    $localEnv = '';
    if (file_exists(_CENTREON_PATH_ . '/.env.local.php')) {
        $localEnv = @include _CENTREON_PATH_ . '/.env.local.php';
    }

    //check if the remote is register on server
    if (hasRemoteChild()) {
        echo formatResponseMessage('Central cannot be converted to Remote', 'error');
        exit(1);
    }

    //prepare db credential
    $loginCredentialsDb = [
        "apiUsername" => $configOptions['API_USERNAME']
    ];
    $centreonEncryption = new \Security\Encryption();
    try {
        $centreonEncryption->setFirstKey($localEnv['APP_SECRET'])->setSecondKey(SECOND_KEY);
        $loginCredentialsDb['apiCredentials'] = $centreonEncryption->crypt($configOptions['API_PASSWORD']);
    } catch (\InvalidArgumentException $e) {
        echo $e->getMessage();
        exit(1);
    }
    $loginCredentialsDb['apiPath'] = $configOptions['ROOT_CENTREON_FOLDER'] ?? 'centreon';
    $loginCredentialsDb['apiPeerValidation'] = $configOptions['INSECURE'] === true ? 'no' : 'yes';
    $loginCredentialsDb['apiScheme'] = $protocol;
    $loginCredentialsDb['apiPort'] = $port;

    if ($configOptions['PROXY_USAGE'] === true) {
        $loginCredentialsDb['proxy_informations']['proxy_url'] = $configOptions["PROXY_HOST"];
        $loginCredentialsDb['proxy_informations']['proxy_port'] = $configOptions["PROXY_PORT"];
        $loginCredentialsDb['proxy_informations']['proxy_user'] = !empty($configOptions["PROXY_USERNAME"])
            ? $configOptions["PROXY_USERNAME"]
            : null;
        $loginCredentialsDb['proxy_informations']['proxy_password'] = $configOptions['PROXY_PASSWORD'] ?? null;
    }
    try {
        $registerPayloads = registerRemote($host, $loginCredentialsDb);
    } catch (\PDOException $e) {
        echo $e->getMessage();
        exit(1);
    }
} else {
    $registerPayloads = [];
}

$registerPayloads[] = $payload;

/**
* Convert payloads to JSON
*/
$loginCredentials = json_encode($loginCredentials);

/************************ */
/*     API REQUEST        */
/************************ */

/**
 * Connection to Api
 */
$loginUrl = $protocol . '://' . $host;
if (!empty($port)) {
    $loginUrl .= ':' . $port;
}
$loginUrl .= '/' . $configOptions['ROOT_CENTREON_FOLDER'] . '/api/latest/login';
try {
    $ch = curl_init($loginUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $loginCredentials);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($configOptions["INSECURE"] === true) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    if ($configOptions['PROXY_USAGE'] === true) {
        curl_setopt($ch, CURLOPT_PROXY, $configOptions["PROXY_HOST"]);
        curl_setopt($ch, CURLOPT_PROXYPORT, $configOptions["PROXY_PORT"]);
        if (!empty($configOptions["PROXY_USERNAME"])) {
            curl_setopt(
                $ch,
                CURLOPT_PROXYUSERPWD,
                $configOptions["PROXY_USERNAME"] . ':' . $configOptions['PROXY_PASSWORD']
            );
        }
    } else {
        curl_setopt($ch, CURLOPT_PROXY, '');
    }

    $result = curl_exec($ch);

    if ($result == false) {
        throw new Exception(curl_error($ch) . PHP_EOL);
    }
} catch (\Exception $e) {
    echo $e->getMessage();
    exit(1);
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
    echo formatResponseMessage($result['message'], 'error');
    exit(1);
} else {
    echo formatResponseMessage('Can\'t connect to the API using: ' . $loginUrl, 'error');
    exit(1);
}

/**
 * POST Request to server registration endpoint
 */
$registerUrl = $protocol . '://' . $host;
if (!empty($port)) {
    $registerUrl .= ':' . $port;
}
$registerUrl .= '/' . $configOptions['ROOT_CENTREON_FOLDER'] . '/api/latest/platform/topology';

foreach ($registerPayloads as $postData) {
    $registerPayload = json_encode($postData);
    try {
        $ch = curl_init($registerUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "X-AUTH-TOKEN: $APIToken"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $registerPayload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($configOptions["INSECURE"] === true) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        if ($configOptions['PROXY_USAGE'] === true) {
            curl_setopt($ch, CURLOPT_PROXY, $configOptions["PROXY_HOST"]);
            curl_setopt($ch, CURLOPT_PROXYPORT, $configOptions["PROXY_PORT"]);
            if (!empty($configOptions["PROXY_USERNAME"])) {
                curl_setopt(
                    $ch,
                    CURLOPT_PROXYUSERPWD,
                    $configOptions["PROXY_USERNAME"] . ':' . $configOptions['PROXY_PASSWORD']
                );
            }
        } else {
            curl_setopt($ch, CURLOPT_PROXY, '');
        }

        $result = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($result === false) {
            throw new Exception(curl_error($ch) . PHP_EOL);
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        exit(1);
    } finally {
        curl_close($ch);
    }

    $result = json_decode($result, true);

    /**
     * Display response of API
     */
    if ($responseCode === 201) {
        $responseMessage = "The CURRENT NODE '" . $postData['type'] . "': '" . $postData['name'] . "@" .
            $postData['address'] . "' linked to TARGET NODE: '" . $postData['parent_address'] . "' has been added";
        echo formatResponseMessage($responseMessage, 'success');
    } elseif (isset($result['message'])) {
        echo formatResponseMessage($result['message'], 'error');
        exit(1);
    } else {
        echo formatResponseMessage('An error occurred while contacting the API using:' . $registerUrl, 'error');
        exit(1);
    }
}
exit(0);
