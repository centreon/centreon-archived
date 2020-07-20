<?php

/**
 * Get script params and assign them
 */
$opt = getopt('u:p:t:h:', ["help::","proxy:"]);

if (isset($opt['help'])) {
    echo PHP_EOL;
    echo "Global Options:" . PHP_EOL;
    echo PHP_EOL;
    echo "  -u <mandatory>              username of your centreon-web account" . PHP_EOL;
    echo "  -p <mandatory>              password of your centreon-web account" . PHP_EOL;
    echo "  -t <mandatory>              the server type you want to register:" . PHP_EOL;
    echo "            0: Central" . PHP_EOL;
    echo "            1: Poller" . PHP_EOL;
    echo "            2: Remote Server" . PHP_EOL;
    echo "            3: Map Server" . PHP_EOL;
    echo "            4: MBI Server" . PHP_EOL;
    echo "  -h <mandatory>              URL of your Central platform" . PHP_EOL;
    echo "  --help <optional>           get informations about the parameters available" . PHP_EOL;
    echo "  --proxy <optional>          if using a proxy, provide this parameter" . PHP_EOL;
    exit;
}

try {
    if (isset($opt['u'], $opt['p'], $opt['t'], $opt['h'])) {
        $username = $opt['u'];
        $password = $opt['p'];
        $serverType = (int) $opt['t'];
        $centralIp = $opt['h'];
    } else {
        throw new \InvalidArgumentException(
            'missing parameter: -u -p -t -h are mandatories, use --help for further informations'
        );
    }
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage();
    exit;
}


if (!in_array($serverType, [0,1,2,3,4])) {
    echo '-t must be one of those value' . PHP_EOL;
    echo '0 => Central, 1 => Poller, 2 => Remote Server, 3 => Map Server, 4 => MBI Server';
    exit;
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
 * Parsing url part get from params -h
 */
$centralURL = parse_url($centralIp);
$protocol = $centralURL['scheme'] ?? 'http';
$host = $centralURL['host'] ?? $centralURL['path'];
$port = $centralURL['port'] ?? '';

/**
 * Try Connection to Api
 */
$loginUrl = $protocol . '://' . $host;
if(!empty($port)){
    $loginUrl .= ':' . $port;
}
$loginUrl .= '/centreon/api/' . $version . '/login';

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_PROXY, 'proxy.int.centreon.com');
// curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $credentials);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
var_dump($result);
die();

$result = json_decode($result, true);
/**
 * Save Token or return the error message
 */
if (isset($result['security']['token'])) {
    $APIToken = $result['security']['token'];
} elseif (isset($result['code']) && $result['code'] === 500) {
    echo 'error code: ' . $result['code'] . PHP_EOL;
    echo 'error message: ' . $result['message'] . PHP_EOL;
    exit;
} else {
    echo 'unhandled error' . PHP_EOL;
    exit;
}

/**
 * Prepare Poller Register payload
 */
$host = gethostname();
$payload = [
    "hostName" => $host,
    "serverType" => $serverType,
];


/**
 * POST Request to registration endpoint
 */
$curlURL = "$protocol://$host:$port/centreon/api/$version/configuration/register";
$ch = curl_init($curlURL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "X-AUTH-TOKEN: $APIToken"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = json_decode(curl_exec($ch), true);
curl_close($ch);

/**
 * TODO: Handle Response
 */