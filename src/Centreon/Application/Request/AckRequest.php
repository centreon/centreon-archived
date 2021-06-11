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

namespace Centreon\Application\Request;

use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;

class AckRequest
{
    /**
     * @var Acknowledgement
     */
    private $acknowledgement;

    /**
     * @var MonitoringResource[]
     */
    private $monitoringResources = [];

    /**
     * @return Acknowledgement
     */
    public function getAcknowledgement(): Acknowledgement
    {
        return $this->acknowledgement;
    }

    /**
     * @param Acknowledgement $acknowledgement
     * @return AckRequest
     */
    public function setAcknowledgement(Acknowledgement $acknowledgement): AckRequest
    {
        $this->acknowledgement = $acknowledgement;
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
     * @return AckRequest
     */
    public function setMonitoringResources(array $monitoringResources): AckRequest
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
