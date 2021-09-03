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

namespace Tests\Centreon\Domain\Monitoring\MetaServiceConfiguration\UseCase\v2110\FindMonitoringResources;

use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\FindMonitoringResources\FindMonitoringResourcesResponse;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResourceTest;

/**
 * @package Tests\Centreon\Domain\Monitoring\MetaServiceConfiguration\UseCase\v2110\FindMonitoringResources
 */
class FindMonitoringResourcesResponseTest extends TestCase
{
    /**
     * We test the transformation of an empty response into an array.
     */
    public function testEmptyResponse(): void
    {
        $response = new FindMonitoringResourcesResponse();
        $monitoringResources = $response->getMonitoringResources();
        $this->assertCount(0, $monitoringResources);
    }

    /**
     * We test the transformation of an entity into an array.
     */
    public function testNotEmptyResponse(): void
    {
        $monitoringResource = MonitoringResourceTest::createServiceMonitoringResourceEntity();
        $response = new FindMonitoringResourcesResponse();
        $response->setMonitoringResources([$monitoringResource]);
        $monitoringResources = $response->getMonitoringResources();

        $this->assertCount(1, $monitoringResources);
        $this->assertEquals($monitoringResource->getId(), $monitoringResources[0]['id']);
        $this->assertEquals($monitoringResource->getUuid(), $monitoringResources[0]['uuid']);
        $this->assertEquals($monitoringResource->getShortType(), $monitoringResources[0]['short_type']);
        $this->assertEquals($monitoringResource->getName(), $monitoringResources[0]['name']);
        $this->assertEquals($monitoringResource->getType(), $monitoringResources[0]['type']);
        $this->assertEquals($monitoringResource->getAlias(), $monitoringResources[0]['alias']);
        $this->assertEquals($monitoringResource->getFqdn(), $monitoringResources[0]['fqdn']);
        $this->assertEquals($monitoringResource->getServiceId(), $monitoringResources[0]['service_id']);
        $this->assertEquals($monitoringResource->getHostId(), $monitoringResources[0]['host_id']);
        $this->assertEquals($monitoringResource->getAcknowledged(), $monitoringResources[0]['acknowledged']);
        $this->assertEquals($monitoringResource->getActiveChecks(), $monitoringResources[0]['active_checks']);
        $this->assertEquals($monitoringResource->getFlapping(), $monitoringResources[0]['flapping']);

        if ($monitoringResource->getIcon() !== null) {
            $this->assertEquals($monitoringResource->getIcon()->getName(), $monitoringResources[0]['icon']['name']);
            $this->assertEquals($monitoringResource->getIcon()->getUrl(), $monitoringResources[0]['icon']['url']);
        }

        $this->assertEquals($monitoringResource->getInDowntime(), $monitoringResources[0]['in_downtime']);
        $this->assertEquals($monitoringResource->getInformation(), $monitoringResources[0]['information']);
        $this->assertEquals($monitoringResource->getLastCheckAsString(), $monitoringResources[0]['last_check']);

        if ($monitoringResource->getLastStatusChange() !== null) {
            $this->assertEquals(
                $monitoringResource->getLastStatusChange(),
                $monitoringResources[0]['last_status_change']
            );
        }

        $this->assertEquals(
            $monitoringResource->getMonitoringServerName(),
            $monitoringResources[0]['monitoring_server_name']
        );
        $this->assertEquals(
            $monitoringResource->isNotificationEnabled(),
            $monitoringResources[0]['notification_enabled']
        );
        $this->assertEquals($monitoringResource->getPassiveChecks(), $monitoringResources[0]['passive_checks']);
        $this->assertEquals($monitoringResource->getPerformanceData(), $monitoringResources[0]['performance_data']);
        $this->assertEquals($monitoringResource->getSeverityLevel(), $monitoringResources[0]['severity_level']);

        if ($monitoringResource->getStatus() !== null) {
            $this->assertEquals($monitoringResource->getStatus()->getCode(), $monitoringResources[0]['status']['code']);
            $this->assertEquals($monitoringResource->getStatus()->getName(), $monitoringResources[0]['status']['name']);
            $this->assertEquals(
                $monitoringResource->getStatus()->getSeverityCode(),
                $monitoringResources[0]['status']['severity_code']
            );
        }

        $this->assertEquals($monitoringResource->getTries(), $monitoringResources[0]['tries']);
        $this->assertEquals($monitoringResource->getDuration(), $monitoringResources[0]['duration']);

        if ($monitoringResource->getLinks()->getExternals()->getNotes() !== null) {
            $this->assertEquals(
                $monitoringResource->getLinks()->getExternals()->getNotes()->getUrl(),
                $monitoringResources[0]['links']['externals']['notes']['url']
            );
            $this->assertEquals(
                $monitoringResource->getLinks()->getExternals()->getNotes()->getLabel(),
                $monitoringResources[0]['links']['externals']['notes']['label']
            );
            $this->assertEquals(
                $monitoringResource->getLinks()->getExternals()->getActionUrl(),
                $monitoringResources[0]['links']['externals']['action_url']
            );
        }

        /** parent monitoring resource */
        $parentMonitoringResource = $monitoringResource->getParent();

        if ($parentMonitoringResource !== null) {
            $this->assertEquals($parentMonitoringResource->getId(), $monitoringResources[0]['parent']['id']);
            $this->assertEquals($parentMonitoringResource->getUuid(), $monitoringResources[0]['parent']['uuid']);
            $this->assertEquals(
                $parentMonitoringResource->getShortType(),
                $monitoringResources[0]['parent']['short_type']
            );
            $this->assertEquals($parentMonitoringResource->getName(), $monitoringResources[0]['parent']['name']);
            $this->assertEquals($parentMonitoringResource->getType(), $monitoringResources[0]['parent']['type']);
            $this->assertEquals($parentMonitoringResource->getAlias(), $monitoringResources[0]['parent']['alias']);
            $this->assertEquals($parentMonitoringResource->getFqdn(), $monitoringResources[0]['parent']['fqdn']);
            $this->assertEquals(
                $parentMonitoringResource->getServiceId(),
                $monitoringResources[0]['parent']['service_id']
            );
            $this->assertEquals($parentMonitoringResource->getHostId(), $monitoringResources[0]['parent']['host_id']);
            $this->assertEquals(
                $parentMonitoringResource->getAcknowledged(),
                $monitoringResources[0]['parent']['acknowledged']
            );
            $this->assertEquals(
                $parentMonitoringResource->getActiveChecks(),
                $monitoringResources[0]['parent']['active_checks']
            );
            $this->assertEquals(
                $parentMonitoringResource->getFlapping(),
                $monitoringResources[0]['parent']['flapping']
            );

            if ($parentMonitoringResource->getIcon() !== null) {
                $this->assertEquals(
                    $parentMonitoringResource->getIcon()->getName(),
                    $monitoringResources[0]['parent']['icon']['name']
                );
                $this->assertEquals(
                    $parentMonitoringResource->getIcon()->getUrl(),
                    $monitoringResources[0]['parent']['icon']['url']
                );
            }

            $this->assertEquals(
                $parentMonitoringResource->getInDowntime(),
                $monitoringResources[0]['parent']['in_downtime']
            );
            $this->assertEquals(
                $parentMonitoringResource->getInformation(),
                $monitoringResources[0]['parent']['information']
            );
            $this->assertEquals(
                $parentMonitoringResource->getLastCheckAsString(),
                $monitoringResources[0]['parent']['last_check']
            );

            if ($parentMonitoringResource->getLastStatusChange() !== null) {
                $this->assertEquals(
                    $parentMonitoringResource->getLastStatusChange(),
                    $monitoringResources[0]['parent']['last_status_change']
                );
            }

            $this->assertEquals(
                $parentMonitoringResource->getMonitoringServerName(),
                $monitoringResources[0]['parent']['monitoring_server_name']
            );
            $this->assertEquals(
                $monitoringResource->isNotificationEnabled(),
                $monitoringResources[0]['parent']['notification_enabled']
            );
            $this->assertEquals(
                $parentMonitoringResource->getPassiveChecks(),
                $monitoringResources[0]['parent']['passive_checks']
            );
            $this->assertEquals(
                $parentMonitoringResource->getPerformanceData(),
                $monitoringResources[0]['parent']['performance_data']
            );
            $this->assertEquals(
                $parentMonitoringResource->getSeverityLevel(),
                $monitoringResources[0]['parent']['severity_level']
            );

            if ($parentMonitoringResource->getStatus() !== null) {
                $this->assertEquals(
                    $parentMonitoringResource->getStatus()->getCode(),
                    $monitoringResources[0]['parent']['status']['code']
                );
                $this->assertEquals(
                    $parentMonitoringResource->getStatus()->getName(),
                    $monitoringResources[0]['parent']['status']['name']
                );
                $this->assertEquals(
                    $parentMonitoringResource->getStatus()->getSeverityCode(),
                    $monitoringResources[0]['parent']['status']['severity_code']
                );
            }

            $this->assertEquals($parentMonitoringResource->getTries(), $monitoringResources[0]['parent']['tries']);
            $this->assertEquals(
                $parentMonitoringResource->getDuration(),
                $monitoringResources[0]['parent']['duration']
            );

            if ($parentMonitoringResource->getLinks()->getExternals()->getNotes() !== null) {
                $this->assertEquals(
                    $parentMonitoringResource->getLinks()->getExternals()->getNotes()->getUrl(),
                    $monitoringResources[0]['parent']['links']['externals']['notes']['url']
                );
                $this->assertEquals(
                    $parentMonitoringResource->getLinks()->getExternals()->getNotes()->getLabel(),
                    $monitoringResources[0]['parent']['links']['externals']['notes']['label']
                );
                $this->assertEquals(
                    $parentMonitoringResource->getLinks()->getExternals()->getActionUrl(),
                    $monitoringResources[0]['parent']['links']['externals']['action_url']
                );
            }
        }
    }
}
