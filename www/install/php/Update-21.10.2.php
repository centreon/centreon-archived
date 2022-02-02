<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

include_once __DIR__ . "/../../class/centreonLog.class.php";
$centreonLog = new CentreonLog();

$versionOfTheUpgrade = 'UPGRADE - 21.10.2: ';

function loadHosts($pearDB)
{
    $stmt = $pearDB->prepare('SELECT host_id, host_name FROM host');
    $stmt->execute();
    $cache = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    $stmt = $pearDB->prepare('SELECT host_host_id, host_tpl_id FROM host_template_relation ORDER BY `host_host_id`, `order` ASC');
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!isset($cache[$row['host_host_id']]['htpl'])) {
            $cache[$row['host_host_id']]['htpl'] = [];
        }
        array_push($cache[$row['host_host_id']]['htpl'], $row['host_tpl_id']);
    }

    $stmt = $pearDB->prepare('SELECT host_macro_id, host_host_id, host_macro_name, host_macro_value FROM on_demand_macro_host');
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!isset($cache[$row['host_host_id']]['macros'])) {
            $cache[$row['host_host_id']]['macros'] = [];
        }
        $cache[$row['host_host_id']]['macros'][$row['host_macro_name']] = $row;
    }

    return $cache;
}

function cleanDuplicateHostMacros($pearDB, $centreonLog, $cache, $srcHostId)
{
    global $versionOfTheUpgrade;

    if (!isset($cache[$srcHostId]['htpl']) || !isset($cache[$srcHostId]['macros'])) {
        return ;
    }

    $loop = [];
    $stack = [];

    $macros = $cache[$srcHostId]['macros'];
    $stack = $cache[$srcHostId]['htpl'];
    while (($hostId = array_shift($stack))) {
        if (isset($loop[$hostId])) {
            continue;
        }
        $loop[$hostId] = 1;

        foreach ($macros as $name => $value) {
            if (isset($macros[$name]['checked']) && $macros[$name]['checked'] == 1) {
                continue;
            }

            if (isset($cache[$hostId]['macros'][$name])) {
                $macros[$name]['checked'] = 1;
                if ($cache[$hostId]['macros'][$name]['host_macro_value'] === $macros[$name]['host_macro_value']) {
                    $centreonLog->insertLog(
                        4,
                        $versionOfTheUpgrade . "host " . $cache[$hostId]['host_name'] . " delete macro " . $name
                    );
                    $pearDB->query("DELETE FROM on_demand_macro_host WHERE host_macro_id = '" . $value['host_macro_id'] . "'");
                }
            }
        }
        
        if (isset($cache[$hostId]['htpl'])) {
            $stack = array_merge($cache[$hostId]['htpl'], $stack);
        }
    }
}

/**
 * Query with transaction
 */
try {
    $errorMessage = 'Cannot purge host macros';

    $cache = loadHosts($pearDB);

    $pearDB->beginTransaction();

    foreach ($cache as $hostId => $value) {
        cleanDuplicateHostMacros($pearDB, $centreonLog, $cache, $hostId);
    }

    $pearDB->commit();
} catch (\Exception $e) {
    if ($pearDB->inTransaction()) {
        $pearDB->rollBack();
    }
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );
    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int)$e->getCode(), $e);
}
