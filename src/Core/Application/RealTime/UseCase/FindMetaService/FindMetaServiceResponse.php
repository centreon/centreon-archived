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
declare(strict_types=1);

namespace Core\Application\RealTime\UseCase\FindMetaService;

use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\ServiceStatus;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Application\RealTime\Common\RealTimeResponseTrait;
use Core\Domain\RealTime\Model\ResourceTypes\MetaServiceResourceType;

class FindMetaServiceResponse
{
    use RealTimeResponseTrait;

    /**
     * @var bool
     */
    public $isFlapping;

    /**
     * @var bool
     */
    public $isAcknowledged;

    /**
     * @var bool
     */
    public $isInDowntime;

    /**
     * @var string|null
     */
    public $output;

    /**
     * @var string|null
     */
    public $performanceData;

    /**
     * @var string|null
     */
    public $commandLine;

    /**
     * @var int|null
     */
    public $notificationNumber;

    /**
     * @var \DateTime|null
     */
    public $lastStatusChange;

    /**
     * @var \DateTime|null
     */
    public $lastNotification;

    /**
     * @var float|null
     */
    public $latency;

    /**
     * @var float|null
     */
    public $executionTime;

    /**
     * @var float|null
     */
    public $statusChangePercentage;

    /**
     * @var \DateTime|null
     */
    public $nextCheck;

    /**
     * @var \DateTime|null
     */
    public $lastCheck;

    /**
     * @var bool
     */
    public $hasActiveChecks;

    /**
     * @var bool
     */
    public $hasPassiveChecks;

    /**
     * @var \DateTime|null
     */
    public $lastTimeOk;

    /**
     * @var int|null
     */
    public $checkAttempts;

    /**
     * @var int|null
     */
    public $maxCheckAttempts;

    /**
     * @var array<string, mixed>
     */
    public $status;

    /**
     * @var array<array<string, mixed>>
     */
    public $downtimes;

    /**
     * @var array<string, mixed>
     */
    public $acknowledgement;

    /**
     * @var boolean
     */
    public $hasGraphData;

    /**
     * @var string
     */
    public string $type = MetaServiceResourceType::TYPE_NAME;

    /**
     * @param int $metaId
     * @param int $hostId
     * @param int $serviceId
     * @param string $name
     * @param string $monitoringServerName
     * @param ServiceStatus $status
     * @param Downtime[] $downtimes
     * @param Acknowledgement|null $acknowledgement
     */
    public function __construct(
        public int $metaId,
        public int $hostId,
        public int $serviceId,
        public string $name,
        public string $monitoringServerName,
        ServiceStatus $status,
        public string $calculationType,
        array $downtimes,
        ?Acknowledgement $acknowledgement
    ) {
        $this->status = $this->statusToArray($status);
        $this->downtimes = $this->downtimesToArray($downtimes);
        $this->acknowledgement = $this->acknowledgementToArray($acknowledgement);
    }
}
