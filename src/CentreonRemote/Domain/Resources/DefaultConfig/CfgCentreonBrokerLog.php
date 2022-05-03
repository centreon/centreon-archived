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

namespace CentreonRemote\Domain\Resources\DefaultConfig;

use CentreonDB;

/**
 * Get broker configuration template
 */
class CfgCentreonBrokerLog
{
    /**
     * Get template configuration
     *
     * @param CentreonDB $db
     * @param int $brokerId
     * @return \Generator<array<string,string|int>> the configuration template
     */
    public static function getConfiguration(CentreonDB $db, int $brokerId): \Generator
    {
        $loggerIds = self::getLoggerIds($db);
        $loggerLevelIds = self::getLoggerLevelIds($db);

        $loggerConfigurations = [
            $loggerIds['core'] => $loggerLevelIds['info'],
            $loggerIds['config'] => $loggerLevelIds['error'],
            $loggerIds['sql'] => $loggerLevelIds['error'],
            $loggerIds['processing'] => $loggerLevelIds['error'],
            $loggerIds['perfdata'] => $loggerLevelIds['error'],
            $loggerIds['bbdo'] => $loggerLevelIds['error'],
            $loggerIds['tcp'] => $loggerLevelIds['error'],
            $loggerIds['tls'] => $loggerLevelIds['error'],
            $loggerIds['lua'] => $loggerLevelIds['error'],
            $loggerIds['bam'] => $loggerLevelIds['error'],
        ];

        foreach ($loggerConfigurations as $loggerId => $loggerLevel) {
            yield [
                'id_centreonbroker' => $brokerId,
                'id_log' => $loggerId,
                'id_level' => $loggerLevel,
            ];
        }
    }

    /**
     * Get logger ids
     *
     * @param CentreonDB $db
     * @return array<string,int>
     */
    private static function getLoggerIds(CentreonDB $db): array
    {
        $result = $db->query(
            "SELECT name, id FROM cb_log"
        );

        return $result->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * Get logger level ids
     *
     * @param CentreonDB $db
     * @return array<string,int>
     */
    private static function getLoggerLevelIds(CentreonDB $db): array
    {
        $result = $db->query(
            "SELECT name, id FROM cb_log_level"
        );

        return $result->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
