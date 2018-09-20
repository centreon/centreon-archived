<?php
require_once dirname(__FILE__) . '/../bootstrap.php';
require 'config.php';

$sendStatistics = 0;

$db = $dependencyInjector['configuration_db'];
$result = $db->query("SELECT `value` FROM `options` WHERE `key` = 'send_statistics'");
if ($row = $result->fetch()) {
    $sendStatistics = $row['value'];
}

$result = $db->query("SELECT `value` FROM `informations` WHERE `key` = 'isRemote'");
if ($row = $result->fetch()) {
    (int)$isRemote = $row['value'];
}

if ($sendStatistics && !$isRemote) {
    $files = scandir(STATS_PATH);
    $total_up = 0;
    $total = 0;
    $alive = 0;
    $to_unlink = array();

    /* Here, we parse each file recorded by centreon-retrieve-stats */
    foreach ($files as $f) {
        if (preg_match('/^' . STATS_PREFIX . '([0-9]*)\\.json/', $f, $match)) {
            $to_unlink[] = STATS_PATH . "/$f";
            $content = file_get_contents(STATS_PATH . "/$f");
            $json = json_decode($content, true);
            $timestamp = $match[1];

            if (empty($json['alive'])) {
                error_log($timestamp . "No live information given.");
                unlink($f);
                continue;
            }
            if (isset($json['UUID']) && isset($json['UUID']['CentreonUUID'])) {
                $UUID = $json['UUID']['CentreonUUID'];
            } else {
                error_log($timestamp . "No UUID specified");
                unlink($f);
                continue;
            }

            // Open connection
            $ch = curl_init();
            // Set the url
            curl_setopt($ch, CURLOPT_URL, CENTREON_STATS_URL);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
            if (curl_exec($ch) === false) {
                error_log('ERROR: centreon-send-stats.php --- ' . curl_error($ch));
            }
            curl_close($ch);
            unlink($f);
        }
    }
}
