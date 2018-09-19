<?php
require 'config.php';
require './../bootstrap.php';

$sendStatistics = 0;

$db = $dependencyInjector['configuration_db'];
$result = $db->query("SELECT `send_statistics` FROM `options`");
if ($row = $result->fetch()) {
    $sendStatistics = $row['send_statistics'];
}

// Our goal is to fill a json structure like the following:
// Then we send it to the CENTREON_STATS_URL.
//{
//  metrics: [
//    {
//      host: dfs1
//      what: diskspace
//      unit: ""
//      mtype: gauge
//    }
//  ]
//  meta: {
//    agent: diamond,
//    processed_by: statsd2
//  }
//}

if ($sendStatistics) {

    $files = scandir(STATS_PATH);
    $aggregation = array(
        'nb_hosts' => 0,
        'nb_hg' => 0,
        'nb_services' => 0,
        'nb_sg' => 0,
        'nb_remotes' => 0,
        'nb_pollers' => 0,
        'nb_central' => 0,
    );
    $types = array_keys($aggregation);

    $total_up = 0;
    $total = 0;
    $alive = 0;
    $timestamp = 0;
    $to_unlink = array();

    $retval = array(
        'metrics' => array(),
        'meta' => array()
    );

    /* Here, we parse each file recorded by centreon-retrieve-stats */
    foreach ($files as $f) {
        if (preg_match('/^' . STATS_PREFIX . '([0-9]*)\\.json/', $f, $match)) {
            $to_unlink[] = STATS_PATH . "/$f";
            $total++;
            $ts = $match[1];
            if ($ts > $timestamp) {
                $timestamp = $ts;
            }

            $content = file_get_contents(STATS_PATH . "/$f");
            $json = json_decode($content, true);

            if (isset($json['alive'])) {
                if ($json['alive'] == 0) {
                    continue;
                } else {
                    $alive++;
                }
            } else {
                error_log("No live information given.");
                continue;
            }

            if (isset($json['infos'])) {
                $info = $json['infos'];
                foreach ($types as $type) {
                    if (isset($info[$type])) {
                        $aggregation[$type] += $info[$type];
                    }
                }
            }

            if (isset($json['versions'])) {
                $versionning = $json['versions'];
                foreach ($versionning as $k => $v) {
                    $meta = &$retval['meta'];
                    foreach ($v as $k1 => $value) {
                        $meta["$k:$k1"] = $value;
                    }
                }
            }

            if (isset($json['UUID']) && isset($json['UUID']['CentreonUUID'])) {
                $UUID = $json['UUID']['CentreonUUID'];
            } else {
                error_log("No UUID specified");
            }
            $total_up++;
        }
    }

    if ($total != 0) {
        if ($total_up != 0) {
            foreach ($types as $type) {
                $aggregation[$type] /= $total_up;
                $retval['jwtToken'] = $UUID;
                $retval['metrics'][] = array(
                    'host' => $UUID,
                    'what' => $type,
                    'unit' => '',
                    'result' => $aggregation[$type],
                    'mtype' => 'gauge',
                    'timestamp' => $timestamp
                );
            }
        }

        $retval['metrics'][] = array(
            'host' => $UUID,
            'what' => 'alive',
            'unit' => '%',
            'result' => $alive * 100 / $total,
            'mtype' => 'gauge',
            'timestamp' => $timestamp
        );

        // Open connection
        $ch = curl_init();

        $stats_url = CENTREON_STATS_URL . '/instances/' . $UUID . '/metrics';
        // Set the url
        curl_setopt($ch, CURLOPT_URL, $stats_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($retval));

        if (curl_exec($ch) === false) {
            die('ERROR: centreon-send-stats.php --- ' . curl_error($ch));
        }

        curl_close($ch);

        /* We just have to remove parsed files */
        foreach ($to_unlink as $f) {
            unlink($f);
        }
    }
}
