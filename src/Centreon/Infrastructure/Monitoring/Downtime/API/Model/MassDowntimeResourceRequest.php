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

namespace Centreon\Infrastructure\Monitoring\Downtime\API\Model;

use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;

class MassDowntimeResourceRequest
{
    /**
     * @var Downtime
     */
    private $downtime;

    /**
     * @var MonitoringResource[]
     */
    private $monitoringResources = [];

    /**
     * @return Downtime
     */
    public function getDowntime(): Downtime
    {
        return $this->downtime;
    }

    /**
     * @param Downtime $downtime
     * @return self
     */
    public function setDowntime(Downtime $downtime): self
    {
        $this->downtime = $downtime;
        return $this;
    }

    /**
     * @return MonitoringResource[]
     */
    public function getMonitoringResources(): array
    {
        return $this->monitoringResources;
    }

    /**
     * @param MonitoringResource[] $monitoringResources
     * @return self
     */
    public function setResources(array $monitoringResources): self
    {
        foreach ($monitoringResources as $monitoringResource) {
            if (!($monitoringResource instanceof MonitoringResource)) {
                throw new \InvalidArgumentException(_('One of the elements provided is not a MonitoringResource type'));
            }
        }
        $this->monitoringResources = $monitoringResources;
        return $this;
    }
}
