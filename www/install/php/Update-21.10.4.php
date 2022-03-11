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

//error specific content
$versionOfTheUpgrade = 'UPGRADE - 21.10.4: ';

/**
 * Query with transaction
 */
try {
    $pearDB->beginTransaction();
    $errorMessage = 'Unable to delete logger entry in cb_tag';
    $statement = $pearDB->query("DELETE FROM cb_tag WHERE tagname = 'logger'");
    $errorMessage = 'Unable to update the description in cb_field';
    $statement = $pearDB->query("
        UPDATE cb_field
        SET `description` = 'Time in seconds to wait between each connection attempt. The default value is 30s.'
        WHERE `cb_field_id` = 31
    ");

    $errorMessage  = 'Unable to delete logger entry in cb_tag';
    $statement = $pearDB->query("DELETE FROM cb_tag WHERE tagname = 'logger'");
    $pearDB->commit();

    $errorMessage = 'Cannot purge host macros';
    $cache = loadHosts($pearDB);
    $pearDB->beginTransaction();
    foreach ($cache as $hostId => $value) {
        cleanDuplicateHostMacros($pearDB, $centreonLog, $cache, (int) $hostId);
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

/**
 * @param CentreonDb $db
 * @return array<int, array<string, mixed>>
 */
function loadHosts(CentreonDb $db): array
{
    $stmt = $db->prepare('SELECT host_id, host_name FROM host');
    $stmt->execute();
    $cache = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    $stmt = $db->prepare(
        'SELECT host_host_id, host_tpl_id
         FROM host_template_relation ORDER BY `host_host_id`, `order` ASC'
    );
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cache[$row['host_host_id']]['htpl'][] = $row['host_tpl_id'];
    }

    $stmt = $db->prepare(
        'SELECT host_macro_id, host_host_id, host_macro_name, host_macro_value
        FROM on_demand_macro_host'
    );
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cache[$row['host_host_id']]['macros'][$row['host_macro_name']] = $row;
    }

    return $cache;
}

/**
 * @param CentreonDb $db
 * @param CentreonLog $centreonLog
 * @param array<int, array<string, mixed>> $cache
 * @param int $srcHostId
 */
function cleanDuplicateHostMacros(
    CentreonDb $db,
    CentreonLog $centreonLog,
    array $cache,
    int $srcHostId
): void {
    global $versionOfTheUpgrade;

    if (!isset($cache[$srcHostId]['htpl']) || !isset($cache[$srcHostId]['macros'])) {
        return ;
    }

    $loop = [];
    $stack = [];

    $macros = $cache[$srcHostId]['macros'];
    $stack = $cache[$srcHostId]['htpl'];
    while (($hostId = array_shift($stack)) !== null) {
        if (isset($loop[$hostId])) {
            continue;
        }
        $loop[$hostId] = 1;

        foreach ($macros as $macroName => $macro) {
            if (isset($macro['checked']) && $macro['checked'] === 1) {
                continue;
            }

            if (isset($cache[$hostId]['macros'][$macroName])) {
                $macro['checked'] = 1;
                if ($cache[$hostId]['macros'][$macroName]['host_macro_value'] === $macro['host_macro_value']) {
                    $centreonLog->insertLog(
                        4,
                        $versionOfTheUpgrade . "host " . $cache[$hostId]['host_name'] . " delete macro " . $macroName
                    );
                    $db->query(
                        'DELETE FROM on_demand_macro_host WHERE host_macro_id = ' . (int) $macro['host_macro_id']
                    );
                }
            }
        }

        if (isset($cache[$hostId]['htpl'])) {
            $stack = array_merge($cache[$hostId]['htpl'], $stack);
        }
    }

    // clean empty macros with no macros inherited
    foreach ($macros as $macroName => $macro) {
        if (!isset($macro['checked']) && empty($macro['host_macro_value'])) {
            $centreonLog->insertLog(
                4,
                $versionOfTheUpgrade . "host " . $cache[$srcHostId]['host_name'] . " delete macro " . $macroName
            );
            $db->query(
                'DELETE FROM on_demand_macro_host WHERE host_macro_id = ' . (int) $macro['host_macro_id']
            );
        }
    }
}
