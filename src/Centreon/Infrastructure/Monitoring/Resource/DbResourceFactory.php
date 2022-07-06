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

namespace Centreon\Infrastructure\Monitoring\Resource;

use Centreon\Domain\Monitoring\Icon;
use Centreon\Domain\Monitoring\Notes;
use Centreon\Domain\Monitoring\ResourceStatus;
use Core\Domain\RealTime\ResourceTypeInterface;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Core\Domain\RealTime\Model\ResourceTypes\HostResourceType;
use Core\Domain\RealTime\Model\ResourceTypes\MetaServiceResourceType;
use Core\Infrastructure\Common\Repository\DbFactoryUtilitiesTrait;

class DbResourceFactory
{
    use DbFactoryUtilitiesTrait;

    /**
     * @param array<string, mixed> $record
     * @param ResourceTypeInterface[] $availableResourceTypes
     * @return ResourceEntity
     */
    public static function createFromRecord(array $record, array $availableResourceTypes): ResourceEntity
    {
        $resourceType = self::normalizeType((int) $record['type'], $availableResourceTypes);

        $parent = null;

        if (
            $resourceType !== MetaServiceResourceType::TYPE_NAME
            && $resourceType !== HostResourceType::TYPE_NAME
        ) {
            $parentStatus = (new ResourceStatus())
                ->setCode((int) $record['parent_status'])
                ->setName(self::getStatusAsString(HostResourceType::TYPE_NAME, (int) $record['parent_status']))
                ->setSeverityCode(self::normalizeSeverityCode((int) $record['parent_status_ordered']));

            $parent = (new ResourceEntity())
                ->setId((int) $record['parent_id'])
                ->setName($record['parent_name'])
                ->setAlias($record['parent_alias'])
                ->setType(HostResourceType::TYPE_NAME)
                ->setStatus($parentStatus);
        }

        $status = (new ResourceStatus())
            ->setCode((int) $record['status'])
            ->setName(self::getStatusAsString($resourceType, (int) $record['status']))
            ->setSeverityCode(self::normalizeSeverityCode((int) $record['status_ordered']));

        $notes = (new Notes())
            ->setLabel($record['notes'])
            ->setUrl($record['notes_url']);

        $statusConfirmedAsString = (int) $record['status_confirmed'] === 1 ? 'H' : 'S';
        $tries = $record['check_attempts']
            . '/' . $record['max_check_attempts'] . ' (' . $statusConfirmedAsString . ')';

        $resource = (new ResourceEntity())
            ->setId((int) $record['id'])
            ->setInternalId(self::getIntOrNull($record['internal_id']))
            ->setType($resourceType)
            ->setParent($parent)
            ->setStatus($status)
            ->setTries($tries)
            ->setServiceId((int) $record['id'])
            ->setHostId((int) $record['parent_id'])
            ->setParent($parent)
            ->setStatus($status)
            ->setInDowntime((int) $record['in_downtime'] === 1)
            ->setAcknowledged((int) $record['acknowledged'] === 1)
            ->setStateType((int) $record['status_confirmed'])
            ->setName($record['name'])
            ->setAlias($record['alias'])
            ->setFqdn($record['address'])
            ->setPassiveChecks((int) $record['passive_checks_enabled'] === 1)
            ->setActiveChecks((int) $record['active_checks_enabled'] === 1)
            ->setNotificationEnabled((int) $record['notifications_enabled'] === 1)
            ->setLastCheck(self::createDateTimeFromTimestamp((int) $record['last_check']))
            ->setInformation($record['output'])
            ->setMonitoringServerName($record['monitoring_server_name'])
            ->setLastStatusChange(self::createDateTimeFromTimestamp((int) $record['last_status_change']))
            ->setHasGraph((int) $record['has_graph'] === 1)
            ->setSeverityLevel((int) $record['severity_level']);

        $resource->getLinks()->getExternals()->setActionUrl($record['action_url']);
        $resource->getLinks()->getExternals()->setNotes($notes);

        if (empty($record['icon_id']) === false) {
            $resource->setIcon((new Icon())->setId((int) $record['icon_id']));
        }

        return $resource;
    }

    /**
     * Returns status as string regarding the resource type
     *
     * @param string $resourceType
     * @param int $statusCode
     * @return string
     */
    private static function getStatusAsString(string $resourceType, int $statusCode): string
    {
        if ($resourceType === ResourceEntity::TYPE_HOST) {
            return match ($statusCode) {
                0 => ResourceStatus::STATUS_NAME_UP,
                1 => ResourceStatus::STATUS_NAME_DOWN,
                2 => ResourceStatus::STATUS_NAME_UNREACHABLE,
                4 => ResourceStatus::STATUS_NAME_PENDING,
                default => ResourceStatus::STATUS_NAME_PENDING
            };
        }

        return match ($statusCode) {
            0 => ResourceStatus::STATUS_NAME_OK,
            1 => ResourceStatus::STATUS_NAME_WARNING,
            2 => ResourceStatus::STATUS_NAME_CRITICAL,
            3 => ResourceStatus::STATUS_NAME_UNKNOWN,
            4 => ResourceStatus::STATUS_NAME_PENDING,
            default => ResourceStatus::STATUS_NAME_PENDING
        };
    }

    /**
     * Normalizes the status severity code
     *
     * @param int $severityCode
     * @return int
     */
    private static function normalizeSeverityCode(int $severityCode): int
    {
        return match ($severityCode) {
            0 => ResourceStatus::SEVERITY_OK,
            1 => ResourceStatus::SEVERITY_PENDING,
            2 => ResourceStatus::SEVERITY_LOW,
            3 => ResourceStatus::SEVERITY_MEDIUM,
            4 => ResourceStatus::SEVERITY_HIGH,
            default => ResourceStatus::SEVERITY_PENDING
        };
    }

    /**
     * Converts the resource type value stored as int into a string
     *
     * @param int $type
     * @param ResourceTypeInterface[] $availableResourceTypes
     * @return string
     */
    private static function normalizeType(int $type, array $availableResourceTypes): string
    {
        $normalizedType = '';
        foreach ($availableResourceTypes as $resourceType) {
            if ($resourceType->isValidForTypeId($type)) {
                $normalizedType =  $resourceType->getName();
            }
        }

        return $normalizedType;
    }
}
