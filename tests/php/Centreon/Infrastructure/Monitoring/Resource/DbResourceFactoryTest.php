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

namespace Tests\Centreon\Infrastructure\Monitoring\Resource;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Infrastructure\Monitoring\Resource\DbResourceFactory;
use Core\Domain\RealTime\Model\ResourceTypes\ServiceResourceType;

beforeEach(function () {
    $this->record = [
        'resource_id' => '9',
        'name' => 'Ping',
        'alias' => null,
        'address' => null,
        'id' => '104',
        'internal_id' => null,
        'parent_id' => '14',
        'parent_name' => 'Database',
        'parent_status' => '2',
        'parent_alias' => 'Database',
        'parent_status_ordered' => '2',
        'severity_level' => '10',
        'type' => '0',
        'status' => '0',
        'status_ordered' => '0',
        'status_confirmed' => '1',
        'in_downtime' => '0',
        'acknowledged' => '0',
        'passive_checks_enabled' => '0',
        'active_checks_enabled' => '1',
        'notifications_enabled' => '1',
        'last_check' => '1651218259',
        'last_status_change' => '1651211193',
        'check_attempts' => '1',
        'max_check_attempts' => '3',
        'notes' => 'Notes label',
        'notes_url' => 'https://www.centreon.com',
        'action_url' => 'https://www.centreon.com',
        'output' => 'OK - localhost rta 0.257ms lost 0%',
        'poller_id' => '1',
        'has_graph' => '1',
        'monitoring_server_name' => 'Central',
        'enabled' => '1',
        'icon_id' => '0'
    ];

    $this->serviceResourceType = $this->createMock(ServiceResourceType::class);
});


it('should create a ResourceEntity', function () {
    $this->serviceResourceType
            ->expects($this->once())
            ->method('isValidForTypeId')
            ->with($this->record['type'])
            ->willReturn(true);

    $this->serviceResourceType
        ->expects($this->once())
        ->method('getName')
        ->willReturn(ServiceResourceType::TYPE_NAME);

    $resource = DbResourceFactory::createFromRecord($this->record, [$this->serviceResourceType]);

    expect((int) $this->record['id'], $resource->getId());
    expect((int) $this->record['parent_id'], $resource->getParent()?->getId());
    expect($this->record['name'], $resource->getName());
    expect($resource->getAlias())->toBeNull();
    expect($resource->getFqdn())->toBeNull();
    expect($this->record['parent_name'], $resource->getParent()?->getName());
    expect((int) $this->record['parent_status'], $resource->getParent()?->getStatus()?->getCode());
    expect(ResourceStatus::SEVERITY_LOW, $resource->getParent()?->getStatus()?->getSeverityCode());
    expect($this->record['parent_alias'], $resource->getParent()?->getAlias());
    expect((int) $this->record['severity_level'], $resource->getSeverityLevel());
    expect(ResourceEntity::TYPE_SERVICE, $resource->getType());
    expect(ResourceStatus::STATUS_NAME_OK, $resource->getStatus()?->getName());
    expect((int) $this->record['status'], $resource->getStatus()?->getCode());
    expect(ResourceStatus::SEVERITY_OK, $resource->getStatus()?->getSeverityCode());

    $statusConfirmedAsString = (int) $this->record['status_confirmed'] === 1 ? 'H' : 'S';

    $tries = $this->record['check_attempts']
        . '/' . $this->record['max_check_attempts'] . ' (' . $statusConfirmedAsString . ')';

    expect($tries)->toBe($resource->getTries());
    expect($resource->getInDowntime())->toBeFalse();
    expect($resource->getAcknowledged())->toBeFalse();
    expect($resource->getPassiveChecks())->toBeFalse();
    expect($resource->getActiveChecks())->toBeTrue();
    expect($resource->isNotificationEnabled())->toBeTrue();
    expect((int) $this->record['last_check'], $resource->getLastCheck()?->getTimestamp());
    expect((int) $this->record['last_status_change'],$resource->getLastStatusChange()?->getTimestamp());
    expect($this->record['notes'])->toBe($resource->getLinks()->getExternals()->getNotes()?->getLabel());
    expect($this->record['notes_url'])->toBe($resource->getLinks()->getExternals()->getNotes()?->getUrl());
    expect($this->record['action_url'])->toBe($resource->getLinks()->getExternals()->getActionUrl());
    expect($this->record['output'])->toBe($resource->getInformation());
    expect($this->record['monitoring_server_name'])->toBe($resource->getMonitoringServerName());
    expect($resource->hasGraph())->toBeTrue();
});
