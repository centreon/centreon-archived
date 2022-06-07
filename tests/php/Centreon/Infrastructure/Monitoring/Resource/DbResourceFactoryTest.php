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

class DbResourceFactoryTest extends TestCase
{
    /**
     * @var array<string, string|null>
     */
    private array $record;

    public function setUp(): void
    {
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
    }

    /**
     * Test that resource is properly created
     */
    public function testResourceCreation(): void
    {
        $resource = DbResourceFactory::createFromRecord($this->record);

        $this->assertEquals((int) $this->record['id'], $resource->getId());
        $this->assertEquals((int) $this->record['parent_id'], $resource->getParent()->getId());
        $this->assertEquals($this->record['name'], $resource->getName());
        $this->assertNull($resource->getAlias());
        $this->assertNull($resource->getFqdn());
        $this->assertEquals($this->record['parent_name'], $resource->getParent()->getName());
        $this->assertEquals((int) $this->record['parent_status'], $resource->getParent()->getStatus()->getCode());
        $this->assertEquals(ResourceStatus::SEVERITY_LOW, $resource->getParent()->getStatus()->getSeverityCode());
        $this->assertEquals($this->record['parent_alias'], $resource->getParent()->getAlias());
        $this->assertEquals((int) $this->record['severity_level'], $resource->getSeverityLevel());
        $this->assertEquals(ResourceEntity::TYPE_SERVICE, $resource->getType());
        $this->assertEquals(ResourceStatus::STATUS_NAME_OK, $resource->getStatus()->getName());
        $this->assertEquals((int) $this->record['status'], $resource->getStatus()->getCode());
        $this->assertEquals(ResourceStatus::SEVERITY_OK, $resource->getStatus()->getSeverityCode());

        $statusConfirmedAsString = (int) $this->record['status_confirmed'] === 1 ? 'H' : 'S';
        $tries = $this->record['check_attempts']
            . '/' . $this->record['max_check_attempts'] . ' (' . $statusConfirmedAsString . ')';

        $this->assertEquals($tries, $resource->getTries());
        $this->assertFalse($resource->getInDowntime());
        $this->assertFalse($resource->getAcknowledged());
        $this->assertFalse($resource->getPassiveChecks());
        $this->assertTrue($resource->getActiveChecks());
        $this->assertTrue($resource->isNotificationEnabled());
        $this->assertEquals((int) $this->record['last_check'], $resource->getLastCheck()->getTimestamp());
        $this->assertEquals(
            (int) $this->record['last_status_change'],
            $resource->getLastStatusChange()->getTimestamp()
        );

        $this->assertEquals($this->record['notes'], $resource->getLinks()->getExternals()->getNotes()->getLabel());
        $this->assertEquals($this->record['notes_url'], $resource->getLinks()->getExternals()->getNotes()->getUrl());
        $this->assertEquals($this->record['action_url'], $resource->getLinks()->getExternals()->getActionUrl());
        $this->assertEquals($this->record['output'], $resource->getInformation());
        $this->assertEquals($this->record['monitoring_server_name'], $resource->getMonitoringServerName());
        $this->assertTrue($resource->hasGraph());
    }
}
