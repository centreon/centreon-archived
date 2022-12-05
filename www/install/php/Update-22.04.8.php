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

require_once __DIR__ . '/../../class/centreonLog.class.php';

$centreonLog = new CentreonLog();

// error specific content
$versionOfTheUpgrade = 'UPGRADE - 22.04.8: ';

try {
    // Transactional queries
    $pearDB->beginTransaction();

    $errorMessage = 'Unable to update illegal characters fields from engine configuration of pollers';
    decodeIllegalCharactersNagios($pearDB);

    $pearDB->commit();
} catch (\Exception $e) {
    if ($pearDB->inTransaction()) {
        $pearDB->rollBack();
    }

    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage
        . ' - Code : ' . (int) $e->getCode()
        . ' - Error : ' . $e->getMessage()
        . ' - Trace : ' . $e->getTraceAsString()
    );

    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int) $e->getCode(), $e);
}

/**
 * Update illegal_object_name_chars + illegal_macro_output_chars fields from cf_nagios table.
 * The aim is to decode entities from them.
 *
 * @param CentreonDB $pearDB
 */
function decodeIllegalCharactersNagios(CentreonDB $pearDB): void
{
    $configs = $pearDB->query(
        <<<'SQL'
            SELECT
                nagios_id,
                illegal_object_name_chars,
                illegal_macro_output_chars
            FROM
                `cfg_nagios`
            SQL
    )->fetchAll(PDO::FETCH_ASSOC);

    $statement = $pearDB->prepare(
        <<<'SQL'
            UPDATE
                `cfg_nagios`
            SET
                illegal_object_name_chars = :illegal_object_name_chars,
                illegal_macro_output_chars = :illegal_macro_output_chars
            WHERE
                nagios_id = :nagios_id
            SQL
    );
    foreach ($configs as $config) {
        $modified = $config;
        $modified['illegal_object_name_chars'] = html_entity_decode($config['illegal_object_name_chars']);
        $modified['illegal_macro_output_chars'] = html_entity_decode($config['illegal_macro_output_chars']);

        if ($config === $modified) {
            // no need to update, we skip a useless query
            continue;
        }

        $statement->bindValue(':illegal_object_name_chars', $modified['illegal_object_name_chars'], \PDO::PARAM_STR);
        $statement->bindValue(':illegal_macro_output_chars', $modified['illegal_macro_output_chars'], \PDO::PARAM_STR);
        $statement->bindValue(':nagios_id', $modified['nagios_id'], \PDO::PARAM_INT);
        $statement->execute();
    }
}
