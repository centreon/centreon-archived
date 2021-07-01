<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Engine\Interfaces;

use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\HostConfiguration\Host;

interface EngineConfigurationRepositoryInterface
{
    /**
     * Find the Engine configuration associated to a host.
     *
     * @param Host $host Host for which we want to find the Engine configuration
     * @return EngineConfiguration|null
     */
    public function findEngineConfigurationByHost(Host $host): ?EngineConfiguration;

    /**
     * Find the Engine configuration based on the monitoring server id.
     *
     * @param int $monitoringServerId
     * @return EngineConfiguration|null
     * @throws \Throwable
     */
    public function findEngineConfigurationByMonitoringServerId(int $monitoringServerId): ?EngineConfiguration;

    /**
     * Find the Engine configuration by its name.
     *
     * @param string $engineName Name of Engine configuration
     * @return EngineConfiguration|null
     */
    public function findEngineConfigurationByName(string $engineName): ?EngineConfiguration;
}
