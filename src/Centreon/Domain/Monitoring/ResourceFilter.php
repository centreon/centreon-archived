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

namespace Centreon\Domain\Monitoring;

/**
 * Filter model for resource repository
 *
 * @package Centreon\Domain\Monitoring
 */
class ResourceFilter
{
    public const TYPE_SERVICE = 'service';
    public const TYPE_HOST = 'host';
    public const TYPE_META = 'metaservice';

    /**
     * Non-ok status in hard state , not acknowledged & not in downtime
     */
    public const STATE_UNHANDLED_PROBLEMS = 'unhandled_problems';

    /**
     * Non-ok status in hard state
     */
    public const STATE_RESOURCES_PROBLEMS = 'resources_problems';

    /**
     * Resources in downtime
     */
    public const STATE_IN_DOWNTIME = 'in_downtime';

    /**
     * Acknowledged resources
     */
    public const STATE_ACKNOWLEDGED = 'acknowledged';

    /**
     * All status & resources
     */
    public const STATE_ALL = 'all';

    public const STATUS_OK = 'OK';
    public const STATUS_UP = 'UP';
    public const STATUS_WARNING = 'WARNING';
    public const STATUS_DOWN = 'DOWN';
    public const STATUS_CRITICAL = 'CRITICAL';
    public const STATUS_UNREACHABLE = 'UNREACHABLE';
    public const STATUS_UNKNOWN = 'UNKNOWN';
    public const STATUS_PENDING = 'PENDING';

    /**
     * Available state types
     */
    public const HARD_STATUS_TYPE = 'hard';
    public const SOFT_STATUS_TYPE = 'soft';

    public const MAP_STATUS_SERVICE = [
        self::STATUS_OK => 0,
        self::STATUS_WARNING => 1,
        self::STATUS_CRITICAL => 2,
        self::STATUS_UNKNOWN => 3,
        self::STATUS_PENDING => 4,
    ];

    public const MAP_STATUS_HOST = [
        self::STATUS_UP => 0,
        self::STATUS_DOWN => 1,
        self::STATUS_UNREACHABLE => 2,
        self::STATUS_PENDING => 4,
    ];

    public const MAP_STATUS_TYPES = [
        self::HARD_STATUS_TYPE => 1,
        self::SOFT_STATUS_TYPE => 0,
    ];

    /**
     * @var string[]
     */
    private $types = [];

    /**
     * @var string[]
     */
    private $states = [];

    /**
     * @var string[]
     */
    private $statuses = [];

    /**
     * @var string[]
     */
    private $hostgroupNames = [];

    /**
     * @var string[]
     */
    private $servicegroupNames = [];

    /**
     * @var string[]
     */
    private $monitoringServerNames = [];

    /**
     * @var int[]
     */
    private $hostIds = [];

    /**
     * @var int[]
     */
    private $serviceIds = [];

    /**
     * @var int[]
     */
    private $metaServiceIds = [];

    /**
     * @var boolean
     */
    private $onlyWithPerformanceData = false;

    /**
     * @var string[]
     */
    private $statusTypes = [];

    /**
     * Transform result by map
     *
     * @param array<mixed, mixed> $list
     * @param array<mixed, mixed> $map
     * @return array<int, mixed>
     */
    public static function map(array $list, array $map): array
    {
        $result = [];

        foreach ($list as $value) {
            if (!array_key_exists($value, $map)) {
                continue;
            }

            $result[] = $map[$value];
        }

        return $result;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasType(string $type): bool
    {
        return in_array($type, $this->types);
    }

    /**
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param string[] $types
     * @return \Centreon\Domain\Monitoring\ResourceFilter
     */
    public function setTypes(array $types): self
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @param string $state
     * @return bool
     */
    public function hasState(string $state): bool
    {
        return in_array($state, $this->states);
    }

    /**
     * @return string[]
     */
    public function getStates(): array
    {
        return $this->states;
    }

    /**
     * @param string[] $states
     * @return \Centreon\Domain\Monitoring\ResourceFilter
     */
    public function setStates(array $states): self
    {
        $this->states = $states;

        return $this;
    }

    /**
     * @param string $status
     * @return bool
     */
    public function hasStatus(string $status): bool
    {
        return in_array($status, $this->statuses);
    }

    /**
     * @return string[]
     */
    public function getStatuses(): array
    {
        return $this->statuses;
    }

    /**
     * @param string[] $statuses
     * @return \Centreon\Domain\Monitoring\ResourceFilter
     */
    public function setStatuses(array $statuses): self
    {
        $this->statuses = $statuses;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getHostgroupNames(): array
    {
        return $this->hostgroupNames;
    }

    /**
     * @param string[] $hostgroupNames
     * @return \Centreon\Domain\Monitoring\ResourceFilter
     */
    public function setHostgroupNames(array $hostgroupNames): self
    {
        $this->hostgroupNames = $hostgroupNames;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getMonitoringServerNames(): array
    {
        return $this->monitoringServerNames;
    }

    /**
     * @param string[] $monitoringServerNames
     * @return \Centreon\Domain\Monitoring\ResourceFilter
     */
    public function setMonitoringServerNames(array $monitoringServerNames): self
    {
        $this->monitoringServerNames = $monitoringServerNames;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getServicegroupNames(): array
    {
        return $this->servicegroupNames;
    }

    /**
     * @param string[] $servicegroupNames
     * @return \Centreon\Domain\Monitoring\ResourceFilter
     */
    public function setServicegroupNames(array $servicegroupNames): self
    {
        $this->servicegroupNames = $servicegroupNames;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getHostIds(): array
    {
        return $this->hostIds;
    }

    /**
     * @param int[] $hostIds
     * @return \Centreon\Domain\Monitoring\ResourceFilter
     */
    public function setHostIds(array $hostIds): self
    {
        foreach ($hostIds as $hostId) {
            if (!is_int($hostId)) {
                throw new \InvalidArgumentException('Host ids must be an array of integers');
            }
        }

        $this->hostIds = $hostIds;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getServiceIds(): array
    {
        return $this->serviceIds;
    }

    /**
     * @param int[] $serviceIds
     * @return \Centreon\Domain\Monitoring\ResourceFilter
     */
    public function setServiceIds(array $serviceIds): self
    {
        foreach ($serviceIds as $serviceId) {
            if (!is_int($serviceId)) {
                throw new \InvalidArgumentException('Service ids must be an array of integers');
            }
        }

        $this->serviceIds = $serviceIds;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getMetaServiceIds(): array
    {
        return $this->metaServiceIds;
    }

    /**
     * @param int[] $metaServiceIds
     * @return \Centreon\Domain\Monitoring\ResourceFilter
     */
    public function setMetaServiceIds(array $metaServiceIds): self
    {
        foreach ($metaServiceIds as $metaServiceId) {
            if (!is_int($metaServiceId)) {
                throw new \InvalidArgumentException('Meta Service ids must be an array of integers');
            }
        }

        $this->metaServiceIds = $metaServiceIds;

        return $this;
    }

    /**
     * @param boolean $onlyWithPerformanceData
     * @return \Centreon\Domain\Monitoring\ResourceFilter
     */
    public function setOnlyWithPerformanceData(bool $onlyWithPerformanceData): self
    {
        $this->onlyWithPerformanceData = $onlyWithPerformanceData;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getOnlyWithPerformanceData(): bool
    {
        return $this->onlyWithPerformanceData;
    }

    /**
     * @return string[]
     */
    public function getStatusTypes(): array
    {
        return $this->statusTypes;
    }

    /**
     * @param string[] $statusTypes
     * @return self
     */
    public function setStatusTypes(array $statusTypes): self
    {
        $this->statusTypes = $statusTypes;
        return $this;
    }
}
