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

use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Core\Resources\Infrastructure\Repository\DbResourceFactory;
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
        'parent_fqdn' => '127.0.0.1',
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
        'icon_id' => '0',
        'severity_id' => '10',
        'severity_name' => 'High',
        'severity_level' => '50',
        'severity_icon_id' => '1',
        'severity_type' => '0',
    ];

    $this->serviceResourceType = $this->createMock(ServiceResourceType::class);
});

it('should create a resource model from record', function () {
    $this->serviceResourceType
        ->expects($this->any())
        ->method('isValidForTypeId')
        ->with($this->record['type'])
        ->willReturn(true);

    $this->serviceResourceType
        ->expects($this->once())
        ->method('getName')
        ->willReturn(ServiceResourceType::TYPE_NAME);

    $this->serviceResourceType
        ->expects($this->any())
        ->method('hasParent')
        ->willReturn(true);

    $this->serviceResourceType
        ->expects($this->once())
        ->method('hasInternalId')
        ->willReturn(false);

    $resource = DbResourceFactory::createFromRecord($this->record, [$this->serviceResourceType]);

    expect($resource->getId())->toBe((int) $this->record['id']);
    expect($resource->getParent()?->getId())->toBe((int) $this->record['parent_id']);
    expect($resource->getName())->toBe($this->record['name']);
    expect($resource->getAlias())->toBeNull();
    expect($resource->getFqdn())->toBeNull();
    expect($resource->getParent()?->getName())->toBe($this->record['parent_name']);
    expect($resource->getParent()?->getStatus()?->getCode())->toBe((int) $this->record['parent_status']);
    expect(ResourceStatus::SEVERITY_LOW)->toBe($resource->getParent()?->getStatus()?->getSeverityCode());
    expect($this->record['parent_alias'])->toBe($resource->getParent()?->getAlias());
    expect(ResourceEntity::TYPE_SERVICE)->toBe($resource->getType());
    expect(ResourceStatus::STATUS_NAME_OK)->toBe($resource->getStatus()?->getName());
    expect((int) $this->record['status'])->toBe($resource->getStatus()?->getCode());
    expect(ResourceStatus::SEVERITY_OK)->toBe($resource->getStatus()?->getSeverityCode());

    $statusConfirmedAsString = (int) $this->record['status_confirmed'] === 1 ? 'H' : 'S';
    $tries = $this->record['check_attempts']
        . '/' . $this->record['max_check_attempts'] . ' (' . $statusConfirmedAsString . ')';

    expect($resource->getTries())->toBe($tries);
    expect($resource->getInDowntime())->toBeFalse();
    expect($resource->getAcknowledged())->toBeFalse();
    expect($resource->getPassiveChecks())->toBeFalse();
    expect($resource->getActiveChecks())->toBeTrue();
    expect($resource->isNotificationEnabled())->toBeTrue();
    expect($resource->getLastCheck()?->getTimestamp())->toBe((int) $this->record['last_check']);
    expect($resource->getLastStatusChange()?->getTimestamp())->toBe((int) $this->record['last_status_change']);
    expect($resource->getLinks()->getExternals()->getNotes()?->getLabel())->toBe($this->record['notes']);
    expect($resource->getLinks()->getExternals()->getNotes()?->getUrl())->toBe($this->record['notes_url']);
    expect($resource->getLinks()->getExternals()->getActionUrl())->toBe($this->record['action_url']);
    expect($resource->getInformation())->toBe($this->record['output']);
    expect($resource->getMonitoringServerName())->toBe($this->record['monitoring_server_name']);
    expect($resource->hasGraph())->toBeTrue();
    expect($resource->getSeverity()?->getName())->toBe($this->record['severity_name']);
    expect($resource->getSeverity()?->getId())->toBe((int) $this->record['severity_id']);
    expect($resource->getSeverity()?->getLevel())->toBe((int) $this->record['severity_level']);
    expect($resource->getSeverity()?->getType())->toBe((int) $this->record['severity_type']);
    expect($resource->getSeverity()?->getIcon()->getId())->toBe((int) $this->record['severity_icon_id']);
});
