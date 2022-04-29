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

/**
 * Get broker configuration template
 */
class CfgCentreonBrokerLog
{
    private const LOG_IDS = [
        'core' => 1,
        'config' => 2,
        'sql' => 3,
        'processing' => 4,
        'perfdata' => 5,
        'bbdo' => 6,
        'tcp' => 7,
        'tls' => 8,
        'lua' => 9,
        'bam' => 10,
    ];

    private const LOG_LEVELS = [
        'disabled' => 1,
        'critical' => 2,
        'error' => 3,
        'warning' => 4,
        'info' => 5,
        'debug' => 6,
        'trace' => 7,
    ];

    /**
     * Get template configuration
     *
     * @param int $brokerId
     * @return array<string, array<string, array<int, array<string>>>> the configuration template
     */
    public static function getConfiguration(int $brokerId): array
    {
        $loggerConfigurations = [
            self::LOG_IDS['core'] => self::LOG_LEVELS['info'],
            self::LOG_IDS['config'] => self::LOG_LEVELS['error'],
            self::LOG_IDS['sql'] => self::LOG_LEVELS['error'],
            self::LOG_IDS['processing'] => self::LOG_LEVELS['error'],
            self::LOG_IDS['perfdata'] => self::LOG_LEVELS['error'],
            self::LOG_IDS['bbdo'] => self::LOG_LEVELS['error'],
            self::LOG_IDS['tcp'] => self::LOG_LEVELS['error'],
            self::LOG_IDS['tls'] => self::LOG_LEVELS['error'],
            self::LOG_IDS['lua'] => self::LOG_LEVELS['error'],
            self::LOG_IDS['bam'] => self::LOG_LEVELS['error'],
        ];

        $configuration = [];
        foreach ($loggerConfigurations as $loggerId => $loggerLevel) {
            $configuration[] = [
                'id_centreonbroker' => $brokerId,
                'id_log' => $loggerId,
                'id_level' => $loggerLevel,
            ];
        }

        return $configuration;
    }
}
