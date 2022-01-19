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

namespace Centreon\Domain\Monitoring\Interfaces;

use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Monitoring\Exception\ResourceException;

interface ResourceServiceInterface
{
    public const TYPE_SERVICE = 'service';
    public const TYPE_HOST = 'host';

    /**
     * List of all types
     */
    public const TYPES = [
        self::TYPE_HOST,
        self::TYPE_SERVICE,
    ];

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

    /**
     * List of all states
     */
    public const STATES = [
        self::STATE_UNHANDLED_PROBLEMS,
        self::STATE_RESOURCES_PROBLEMS,
        self::STATE_IN_DOWNTIME,
        self::STATE_ACKNOWLEDGED,
        self::STATE_ALL,
    ];

    public const STATUS_OK = 'OK';
    public const STATUS_UP = 'UP';
    public const STATUS_WARNING = 'WARNING';
    public const STATUS_DOWN = 'DOWN';
    public const STATUS_CRITICAL = 'CRITICAL';
    public const STATUS_UNREACHABLE = 'UNREACHABLE';
    public const STATUS_UNKNOWN = 'UNKNOWN';
    public const STATUS_PENDING = 'PENDING';

    /**
     * List of all types
     */
    public const STATUSES = [
        self::STATUS_OK,
        self::STATUS_UP,
        self::STATUS_WARNING,
        self::STATUS_DOWN,
        self::STATUS_CRITICAL,
        self::STATUS_UNREACHABLE,
        self::STATUS_UNKNOWN,
        self::STATUS_PENDING,
    ];

    /**
     * Find all resources.
     *
     * @param ResourceFilter $filter
     * @return ResourceEntity[]
     * @throws \Exception
     */
    public function findResources(ResourceFilter $filter): array;

    /**
     * Get list of resources with graph data.
     *
     * @param ResourceEntity[] $resources
     * @return ResourceEntity[]
     */
    public function extractResourcesWithGraphData(array $resources): array;

    /**
     * Enrich resource object with specific host data
     *
     * @param ResourceEntity $resource
     */
    public function enrichHostWithDetails(ResourceEntity $resource): void;

    /**
     * Enrich resource object with specific service data
     *
     * @param ResourceEntity $resource
     * @throws ResourceException
     */
    public function enrichServiceWithDetails(ResourceEntity $resource): void;

    /**
     * Enrich resource object with specific meta service data
     *
     * @param ResourceEntity $resource
     * @throws ResourceException
     */
    public function enrichMetaServiceWithDetails(ResourceEntity $resource): void;

    /**
     * Replace macros in the URL provided by their actual values for a Service Resource
     *
     * @param int $hostId
     * @param int $serviceId
     * @param string $urlType
     * @return string
     * @throws \Exception
     * @throws EntityNotFoundException
     */
    public function replaceMacrosInServiceUrl(int $hostId, int $serviceId, string $urlType): string;

    /**
     * Replace macros in the URL provided by their actual values for a Host Resource
     *
     * @param int $hostId
     * @param string $urlType
     * @return string
     * @throws \Exception
     * @throws EntityNotFoundException
     */
    public function replaceMacrosInHostUrl(int $hostId, string $urlType): string;

    /**
     * Used to filter requests according to a contact.
     * If the filter is defined, all requests will use the ACL of the contact
     * to fetch data.
     *
     * @param mixed $contact Contact to use as a ACL filter
     * @return ResourceServiceInterface
     * @throws \Exception
     */
    public function filterByContact($contact);
}
