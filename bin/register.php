<?php

$opt = getopt('u:p:t:h:');

$username = $opt['u'];
$password = $opt['p'];
$serverType = $opt['t'];
$centralIp = $opt['h'];

$credentials = [
    "security" => [
        "credentials" => [
            "login" => $username,
            "password" => $password
        ]
    ]
];
$credentials = json_encode($credentials);

$configAPIV2 = yaml_parse_file(__DIR__ . '/../config/routes/centreon.yaml');
$version = $configAPIV2['centreon']['defaults']['version'];

$centralURL = parse_url($centralIp);
$protocol = $centralURL['scheme'] ?? 'http';
$host = $centralURL['host'] ?? $centralURL['path'];
$port = $centralURL['port'] ?? '';

$ch = curl_init("$protocol://$host:$port/centreon/api/$version/login");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $credentials);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = json_decode(curl_exec($ch), true);
curl_close($ch);

$APIToken = $result['security']['token'];

if (!in_array($serverType, ['0','1','2','3','4'])) {
    echo '-t must be one of those value' . PHP_EOL;
    echo '0 => Central, 1 => Poller, 2 => Remote Server, 3 => Map Server, 4 => MBI Server';
    exit;
}

$curlURL = "$protocol://$host:$port/centreon/api/$version/configuration/register";

