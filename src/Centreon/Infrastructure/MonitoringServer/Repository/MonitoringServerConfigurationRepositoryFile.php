<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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
declare(strict_types=1);

namespace Centreon\Infrastructure\MonitoringServer\Repository;

use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerConfigurationRepositoryInterface;

/**
 * This class is designed to represent the API repository to manage the generation/move/reload of the monitoring
 * server configuration.
 *
 * @package Centreon\Infrastructure\MonitoringServer\Repository
 */
class MonitoringServerConfigurationRepositoryFile implements MonitoringServerConfigurationRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function generateConfiguration(int $monitoringServerId): void
    {
        $_POST['generate'] = true;
        $_POST['debug'] = true;
        $_POST['poller'] = $monitoringServerId;
        ob_start([$this, 'outputHandler']);
        include(_CENTREON_PATH_ . 'www/include/configuration/configGenerate/xml/generateFiles.php');
        ob_end_flush();
    }

    /**
     * @inheritDoc
     */
    public function moveExportFiles(int $monitoringServerId): void
    {
        $_POST['poller'] = $monitoringServerId;
        ob_start([$this, 'outputHandler']);
        include(_CENTREON_PATH_ . 'www/include/configuration/configGenerate/xml/moveFiles.php');
        ob_end_flush();
    }

     /**
     * @inheritDoc
     */
    public function reloadConfiguration(int $monitoringServerId): void
    {
        $_POST['poller'] = $monitoringServerId;
        $_POST['mode'] = 1;
        ob_start([$this, 'outputHandler']);
        include(_CENTREON_PATH_ . 'www/include/configuration/configGenerate/xml/restartPollers.php');
        ob_end_flush();
    }

    public function outputHandler(string $buffer): string
    {
        $errors = '';
        $values = [];
        $index = [];
        $parser = xml_parser_create();
        xml_parse_into_struct($parser, $buffer, $values, $index);
        if (array_key_exists('ERRORPHP', $index)) {
            foreach ($index['ERRORPHP'] as $valuesIndex) {
                $errors .= $values[$valuesIndex]['value'] . "\n";
            }
        }

        return $errors;
    }
}
