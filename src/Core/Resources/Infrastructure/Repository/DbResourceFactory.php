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

namespace Core\Resources\Infrastructure\Repository;

use Core\Domain\RealTime\Model\Icon;
use Centreon\Domain\Monitoring\Notes;
use Centreon\Domain\Monitoring\ResourceStatus;
use Core\Domain\RealTime\ResourceTypeInterface;
use Core\Severity\RealTime\Domain\Model\Severity;
use Centreon\Domain\Monitoring\Icon as LegacyIconModel;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Core\Domain\RealTime\Model\ResourceTypes\HostResourceType;
use Core\Infrastructure\Common\Repository\DbFactoryUtilitiesTrait;
use Core\Domain\RealTime\Model\ResourceTypes\MetaServiceResourceType;

class DbResourceFactory
{
    use DbFactoryUtilitiesTrait;

    /**
     * @param array<string,int|string|null> $record
     * @param ResourceTypeInterface[] $availableResourceTypes
     * @return ResourceEntity
     */
    public static function createFromRecord(array $record, array $availableResourceTypes): ResourceEntity
    {
        $resourceType = self::normalizeType((int) $record['type'], $availableResourceTypes);

        $parent = null;

        $resourceHasParent = self::resourceHasParent((int) $record['type'], $availableResourceTypes);

        if ($resourceHasParent === true) {
            $parentStatus = (new ResourceStatus())
                ->setCode((int) $record['parent_status'])
                ->setName(self::getStatusAsString(HostResourceType::TYPE_NAME, (int) $record['parent_status']))
                ->setSeverityCode(self::normalizeSeverityCode((int) $record['parent_status_ordered']));

            /** @var string|null */
            $name = $record['parent_name'];

            /** @var string|null */
            $alias = $record['parent_alias'];

            /** @var string|null */
            $fqdn = $record['parent_fqdn'];

            $parent = (new ResourceEntity())
                ->setId((int) $record['parent_id'])
                ->setName($name)
                ->setAlias($alias)
                ->setType(HostResourceType::TYPE_NAME)
                ->setFqdn($fqdn)
                ->setStatus($parentStatus);
        }

        $status = (new ResourceStatus())
            ->setCode((int) $record['status'])
            ->setName(self::getStatusAsString($resourceType, (int) $record['status']))
            ->setSeverityCode(self::normalizeSeverityCode((int) $record['status_ordered']));

        /** @var string|null */
        $label = $record['notes'];

        /** @var string|null */
        $url = $record['notes_url'];

        $notes = (new Notes())
            ->setLabel($label)
            ->setUrl($url);

        $statusConfirmedAsString = (int) $record['status_confirmed'] === 1 ? 'H' : 'S';
        $tries = $record['check_attempts']
            . '/' . $record['max_check_attempts'] . ' (' . $statusConfirmedAsString . ')';


        $severity = null;
        if (! empty($record['severity_id'])) {
            $severityIcon = (new Icon())
                ->setId((int) $record['severity_icon_id']);

            $severity = new Severity(
                (int) $record['severity_id'],
                (string) $record['severity_name'],
                (int) $record['severity_level'],
                (int) $record['severity_type'],
                $severityIcon
            );
        }

        /** @var string|null */
        $name = $record['name'];

        /** @var string|null */
        $alias = $record['alias'];

        /** @var string|null */
        $fqdn = $record['address'];

        /** @var string|null */
        $information = $record['output'];

        $resource = (new ResourceEntity())
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
            ->setName($name)
            ->setAlias($alias)
            ->setFqdn($fqdn)
            ->setPassiveChecks((int) $record['passive_checks_enabled'] === 1)
            ->setActiveChecks((int) $record['active_checks_enabled'] === 1)
            ->setNotificationEnabled((int) $record['notifications_enabled'] === 1)
            ->setLastCheck(self::createDateTimeFromTimestamp((int) $record['last_check']))
            ->setInformation($information)
            ->setMonitoringServerName((string) $record['monitoring_server_name'])
            ->setLastStatusChange(self::createDateTimeFromTimestamp((int) $record['last_status_change']))
            ->setHasGraph((int) $record['has_graph'] === 1)
            ->setSeverity($severity)
            ->setInternalId(self::getIntOrNull($record['internal_id']));

        $resource->setId(
            self::resourceHasInternalId((int) $record['type'], $availableResourceTypes) === true
                ? (int) $record['internal_id']
                : (int) $record['id']
        );

        $resource->setServiceId($resourceHasParent === true ? (int) $record['id'] : null);
        $resource->setHostId($resourceHasParent === true ? (int) $record['parent_id'] : (int) $record['id']);

        /** @var string|null */
        $actionUrl = $record['action_url'];

        $resource->getLinks()->getExternals()->setActionUrl($actionUrl);
        $resource->getLinks()->getExternals()->setNotes($notes);

        if (empty($record['icon_id']) === false) {
            $resource->setIcon((new LegacyIconModel())->setId((int) $record['icon_id']));
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
     * @param integer $type
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

    /**
     * Checks if the Resource has a parent to define
     *
     * @param integer $resourceTypeId
     * @param ResourceTypeInterface[] $availableResourceTypes
     * @return boolean
     */
    private static function resourceHasParent(int $resourceTypeId, array $availableResourceTypes): bool
    {
        $hasParent = false;
        foreach ($availableResourceTypes as $resourceType) {
            if ($resourceType->isValidForTypeId($resourceTypeId)) {
                $hasParent = $resourceType->hasParent();
            }
        }

        return $hasParent;
    }

    /**
     * @param integer $resourceTypeId
     * @param ResourceTypeInterface[] $availableResourceTypes
     * @return boolean
     */
    private static function resourceHasInternalId(int $resourceTypeId, array $availableResourceTypes): bool
    {
        $hasInternalId = false;
        foreach ($availableResourceTypes as $resourceType) {
            if ($resourceType->isValidForTypeId($resourceTypeId)) {
                $hasInternalId = $resourceType->hasInternalId();
            }
        }

        return $hasInternalId;
    }
}
