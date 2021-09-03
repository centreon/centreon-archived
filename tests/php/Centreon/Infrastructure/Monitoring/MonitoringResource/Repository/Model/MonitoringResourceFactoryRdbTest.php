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

namespace Tests\Centreon\Infrastructure\Monitoring\MonitoringResource\Repository\Model;

use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Infrastructure\Monitoring\MonitoringResource\Repository\Model\MonitoringResourceFactoryRdb;
use PHPUnit\Framework\TestCase;

/**
 * @package Tests\Centreon\Infrastructure\Monitoring\MonitoringResource\Repository\Model
 */
class MonitoringResourceFactoryRdbTest extends TestCase
{
    /**
     * @var array<string, mixed> $realTimeDatabaseData
     */
    private $realTimeDatabaseData;

    protected function setUp(): void
    {
        $this->realTimeDatabaseData = [
            "id" => "116",
            "type" => "service",
            "name" => "proc-sshd",
            "alias" => null,
            "fqdn" => null,
            "host_id" => "27",
            "service_id" => "116",
            "status_code" => "2",
            "status_name" => "CRITICAL",
            "status_severity_code" => "1",
            "icon_name" => "dog.png",
            "icon_url" => "/centreon/img/dog.png",
            "command_line" => "/usr/lib/centreon/plugins/centreon_linux_snmp.pl --plugin=os::linux::snmp::plugin ...",
            "timezone" => null,
            "parent_id" => "27",
            "parent_name" => "Centreon-Central",
            "parent_type" => "host",
            "parent_alias" => "Centreon-Central",
            "parent_fqdn" => "localhost",
            "parent_icon_name" => "",
            "parent_icon_url" => "ppm/applications-monitoring-centreon-central-App-Centreon-64.png",
            "parent_status_code" => "4",
            "parent_status_name" => "PENDING",
            "parent_status_severity_code" => "4",
            "action_url" => "http://action-url.com",
            "notes_url" => "http://notes-url.com",
            "notes_label" => "Notes label",
            "monitoring_server_name" => "Central",/** done */
            "flapping" => "0",
            "percent_state_change" => "0",
            "severity_level" => "1",
            "in_downtime" => "0",
            "acknowledged" => "0",
            "active_checks" => "1",
            "passive_checks" => "0",
            "last_status_change" => "1630327742",
            "last_notification" => "1630327742",
            "notification_number" => "24",
            "tries" => "3/3 (H)",
            "last_check" => "1630334102",
            "next_check" => "1630334402",
            "information" => "CRITICAL: Number of current processes running: 0",
            "performance_data" => "'nbproc'=0;;1:;0;",
            "execution_time" => "0.14019",
            "latency" => "0.353",
            "notification_enabled" => "1",
            "has_graph_data" => "1"
        ];
    }

    /**
     * Tests that the monitoring resource entity is correctly created.
     * We test all properties.
     *
     * @throws \Assert\AssertionFailedException
     */
    public function testAllPropertiesOnCreate(): void
    {
        $monitoringResource = MonitoringResourceFactoryRdb::create($this->realTimeDatabaseData);
        $this->assertEquals($this->realTimeDatabaseData['id'], $monitoringResource->getId());
        $this->assertEquals($this->realTimeDatabaseData['name'], $monitoringResource->getName());
        $this->assertEquals($this->realTimeDatabaseData['type'], $monitoringResource->getType());
        $this->assertEquals($this->realTimeDatabaseData['alias'], $monitoringResource->getAlias());
        $this->assertEquals($this->realTimeDatabaseData['fqdn'], $monitoringResource->getFqdn());
        $this->assertEquals($this->realTimeDatabaseData['host_id'], $monitoringResource->getHostId());
        $this->assertEquals($this->realTimeDatabaseData['service_id'], $monitoringResource->getServiceId());

        if ($monitoringResource->getIcon() !== null) {
            $this->assertEquals($this->realTimeDatabaseData['icon_name'], $monitoringResource->getIcon()->getName());
            $this->assertEquals($this->realTimeDatabaseData['icon_url'], $monitoringResource->getIcon()->getUrl());
        }

        $this->assertEquals($this->realTimeDatabaseData['command_line'], $monitoringResource->getCommandLine());
        $this->assertEquals(
            $this->realTimeDatabaseData['monitoring_server_name'],
            $monitoringResource->getMonitoringServerName()
        );
        $this->assertEquals($this->realTimeDatabaseData['timezone'], $monitoringResource->getTimezone());

        if ($monitoringResource->getStatus() !== null) {
            $this->assertEquals(
                $this->realTimeDatabaseData['status_code'],
                $monitoringResource->getStatus()->getCode()
            );
            $this->assertEquals(
                $this->realTimeDatabaseData['status_name'],
                $monitoringResource->getStatus()->getName()
            );
            $this->assertEquals(
                $this->realTimeDatabaseData['status_severity_code'],
                $monitoringResource->getStatus()->getSeverityCode()
            );
        }

        $this->assertEquals(
            $this->realTimeDatabaseData['action_url'],
            $monitoringResource->getLinks()->getExternals()->getActionUrl()
        );

        if ($monitoringResource->getLinks()->getExternals()->getNotes() !== null) {
            $this->assertEquals(
                $this->realTimeDatabaseData['notes_url'],
                $monitoringResource->getLinks()->getExternals()->getNotes()->getUrl()
            );
            $this->assertEquals(
                $this->realTimeDatabaseData['notes_label'],
                $monitoringResource->getLinks()->getExternals()->getNotes()->getLabel()
            );
        }

        $this->assertEquals((int) $this->realTimeDatabaseData['flapping'] === 1, $monitoringResource->getFlapping());
        $this->assertEquals(
            $this->realTimeDatabaseData['percent_state_change'],
            $monitoringResource->getPercentStateChange()
        );
        $this->assertEquals($this->realTimeDatabaseData['severity_level'], $monitoringResource->getSeverityLevel());
        $this->assertEquals(
            (int) $this->realTimeDatabaseData['in_downtime'] === 1,
            $monitoringResource->getInDowntime()
        );
        $this->assertEquals(
            (int) $this->realTimeDatabaseData['acknowledged'] === 1,
            $monitoringResource->getAcknowledged()
        );
        $this->assertEquals(
            (int) $this->realTimeDatabaseData['active_checks'] === 1,
            $monitoringResource->getActiveChecks()
        );
        $this->assertEquals(
            (int) $this->realTimeDatabaseData['passive_checks'] === 1,
            $monitoringResource->getPassiveChecks()
        );

        if ($monitoringResource->getLastStatusChange() !== null) {
            $this->assertEquals(
                $this->realTimeDatabaseData['last_status_change'],
                $monitoringResource->getLastStatusChange()->getTimestamp()
            );
        }

        if ($monitoringResource->getLastNotification() !== null) {
            $this->assertEquals(
                $this->realTimeDatabaseData['last_notification'],
                $monitoringResource->getLastNotification()->getTimestamp()
            );
        }
        $this->assertEquals(
            $this->realTimeDatabaseData['notification_number'],
            $monitoringResource->getNotificationNumber()
        );
        $this->assertEquals($this->realTimeDatabaseData['tries'], $monitoringResource->getTries());

        if ($monitoringResource->getLastCheck() !== null) {
            $this->assertEquals(
                $this->realTimeDatabaseData['last_check'],
                $monitoringResource->getLastCheck()->getTimestamp()
            );
        }

        if ($monitoringResource->getNextCheck() !== null) {
            $this->assertEquals(
                $this->realTimeDatabaseData['next_check'],
                $monitoringResource->getNextCheck()->getTimestamp()
            );
        }

        $this->assertEquals($this->realTimeDatabaseData['information'], $monitoringResource->getInformation());
        $this->assertEquals($this->realTimeDatabaseData['performance_data'], $monitoringResource->getPerformanceData());
        $this->assertEquals($this->realTimeDatabaseData['execution_time'], $monitoringResource->getExecutionTime());
        $this->assertEquals($this->realTimeDatabaseData['latency'], $monitoringResource->getLatency());
        $this->assertEquals(
            (int) $this->realTimeDatabaseData['notification_enabled'] === 1,
            $monitoringResource->isNotificationEnabled()
        );
        $this->assertEquals(
            (int) $this->realTimeDatabaseData['has_graph_data'] === 1,
            $monitoringResource->hasGraphData()
        );

        if ($monitoringResource->getParent() !== null) {
            $this->assertEquals($this->realTimeDatabaseData['parent_id'], $monitoringResource->getParent()->getId());
            $this->assertEquals(
                $this->realTimeDatabaseData['parent_name'],
                $monitoringResource->getParent()->getName()
            );
            $this->assertEquals(
                $this->realTimeDatabaseData['parent_type'],
                $monitoringResource->getParent()->getType()
            );
            $this->assertEquals(
                $this->realTimeDatabaseData['parent_alias'],
                $monitoringResource->getParent()->getAlias()
            );
            $this->assertEquals(
                $this->realTimeDatabaseData['parent_fqdn'],
                $monitoringResource->getParent()->getFqdn()
            );

            if ($monitoringResource->getParent()->getIcon() !== null) {
                $this->assertEquals(
                    $this->realTimeDatabaseData['parent_icon_name'],
                    $monitoringResource->getParent()->getIcon()->getName()
                );
                $this->assertEquals(
                    $this->realTimeDatabaseData['parent_icon_url'],
                    $monitoringResource->getParent()->getIcon()->getUrl()
                );
            }

            if ($monitoringResource->getParent()->getStatus() !== null) {
                $this->assertEquals(
                    $this->realTimeDatabaseData['parent_status_code'],
                    $monitoringResource->getParent()->getStatus()->getCode()
                );
                $this->assertEquals(
                    $this->realTimeDatabaseData['parent_status_name'],
                    $monitoringResource->getParent()->getStatus()->getName()
                );
                $this->assertEquals(
                    $this->realTimeDatabaseData['parent_status_severity_code'],
                    $monitoringResource->getParent()->getStatus()->getSeverityCode()
                );
            }
        }
    }
}
