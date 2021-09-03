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

namespace Tests\Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailHostMonitoringResource;

use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailHostMonitoringResource as UseCase;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResourceTest;

/**
 * @package Tests\Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailHostMonitoringResource
 */
class DetailHostMonitoringResourceResponseTest extends TestCase
{
    /**
     * We test the transformation of an empty response into an array.
     */
    public function testEmptyResponse(): void
    {
        $response = new UseCase\DetailHostMonitoringResourceResponse();
        $monitoringResources = $response->getHostMonitoringResourceDetail();
        $this->assertCount(0, $monitoringResources);
    }

    /**
     * We test the transformation of an entity into an array.
     */
    public function testNotEmptyResponse(): void
    {
        $monitoringResource = MonitoringResourceTest::createHostMonitoringResourceEntity();
        $response = new UseCase\DetailHostMonitoringResourceResponse();
        $response->setHostMonitoringResourceDetail($monitoringResource);
        $monitoringResourceDetail = $response->getHostMonitoringResourceDetail();

        $this->assertIsArray($monitoringResourceDetail);
        $this->assertEquals($monitoringResource->getId(), $monitoringResourceDetail['id']);
        $this->assertEquals($monitoringResource->getUuid(), $monitoringResourceDetail['uuid']);
        $this->assertEquals($monitoringResource->getShortType(), $monitoringResourceDetail['short_type']);
        $this->assertEquals($monitoringResource->getName(), $monitoringResourceDetail['name']);
        $this->assertEquals($monitoringResource->getType(), $monitoringResourceDetail['type']);
        $this->assertEquals($monitoringResource->getAlias(), $monitoringResourceDetail['alias']);
        $this->assertEquals($monitoringResource->getFqdn(), $monitoringResourceDetail['fqdn']);
        $this->assertEquals($monitoringResource->getServiceId(), $monitoringResourceDetail['service_id']);
        $this->assertEquals($monitoringResource->getHostId(), $monitoringResourceDetail['host_id']);
        $this->assertEquals($monitoringResource->getAcknowledged(), $monitoringResourceDetail['acknowledged']);
        $this->assertEquals($monitoringResource->getActiveChecks(), $monitoringResourceDetail['active_checks']);
        $this->assertEquals($monitoringResource->getFlapping(), $monitoringResourceDetail['flapping']);

        if ($monitoringResource->getIcon() !== null) {
            $this->assertEquals($monitoringResource->getIcon()->getName(), $monitoringResourceDetail['icon']['name']);
            $this->assertEquals($monitoringResource->getIcon()->getUrl(), $monitoringResourceDetail['icon']['url']);
        }

        $this->assertEquals($monitoringResource->getInDowntime(), $monitoringResourceDetail['in_downtime']);
        $this->assertEquals($monitoringResource->getInformation(), $monitoringResourceDetail['information']);

        if ($monitoringResource->getLastCheck() !== null) {
            $this->assertEquals($monitoringResource->getLastCheck(), $monitoringResourceDetail['last_check']);
        }

        if ($monitoringResource->getLastStatusChange() !== null) {
            $this->assertEquals(
                $monitoringResource->getLastStatusChange(),
                $monitoringResourceDetail['last_status_change']
            );
        }

        $this->assertEquals(
            $monitoringResource->getMonitoringServerName(),
            $monitoringResourceDetail['monitoring_server_name']
        );
        $this->assertEquals(
            $monitoringResource->isNotificationEnabled(),
            $monitoringResourceDetail['notification_enabled']
        );
        $this->assertEquals($monitoringResource->getPassiveChecks(), $monitoringResourceDetail['passive_checks']);
        $this->assertEquals($monitoringResource->getPerformanceData(), $monitoringResourceDetail['performance_data']);
        $this->assertEquals($monitoringResource->getSeverityLevel(), $monitoringResourceDetail['severity_level']);

        if ($monitoringResource->getStatus() !== null) {
            $this->assertEquals(
                $monitoringResource->getStatus()->getCode(),
                $monitoringResourceDetail['status']['code']
            );
            $this->assertEquals(
                $monitoringResource->getStatus()->getName(),
                $monitoringResourceDetail['status']['name']
            );
            $this->assertEquals(
                $monitoringResource->getStatus()->getSeverityCode(),
                $monitoringResourceDetail['status']['severity_code']
            );
        }

        $this->assertEquals($monitoringResource->getTries(), $monitoringResourceDetail['tries']);
        $this->assertEquals($monitoringResource->getDuration(), $monitoringResourceDetail['duration']);

        if ($monitoringResource->getLinks()->getExternals()->getNotes() !== null) {
            $this->assertEquals(
                $monitoringResource->getLinks()->getExternals()->getNotes()->getUrl(),
                $monitoringResourceDetail['links']['externals']['notes']['url']
            );
            $this->assertEquals(
                $monitoringResource->getLinks()->getExternals()->getNotes()->getLabel(),
                $monitoringResourceDetail['links']['externals']['notes']['label']
            );
            $this->assertEquals(
                $monitoringResource->getLinks()->getExternals()->getActionUrl(),
                $monitoringResourceDetail['links']['externals']['action_url']
            );
        }

        /** parent monitoring resource */
        $parentMonitoringResource = $monitoringResource->getParent();
        $this->assertNull($parentMonitoringResource);
    }
}
