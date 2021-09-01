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

namespace Tests\Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailServiceMonitoringResource;

use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailServiceMonitoringResource as UseCase;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResourceTest;

/**
 * @package Tests\Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailServiceMonitoringResource
 */
class DetailServiceMonitoringResourceResponseTest extends TestCase
{
    /**
     * We test the transformation of an empty response into an array.
     */
    public function testEmptyResponse(): void
    {
        $response = new UseCase\DetailServiceMonitoringResourceResponse();
        $monitoringResources = $response->getServiceMonitoringResourceDetail();
        $this->assertCount(0, $monitoringResources);
    }

    /**
     * We test the transformation of an entity into an array.
     */
    public function testNotEmptyResponse(): void
    {
        $monitoringResource = MonitoringResourceTest::createServiceMonitoringResourceEntity();
        $response = new UseCase\DetailServiceMonitoringResourceResponse();
        $response->setServiceMonitoringResourceDetail($monitoringResource);
        $monitoringResourceDetail = $response->getServiceMonitoringResourceDetail();

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
        $this->assertEquals($monitoringResource->getIcon()->getName(), $monitoringResourceDetail['icon']['name']);
        $this->assertEquals($monitoringResource->getIcon()->getUrl(), $monitoringResourceDetail['icon']['url']);
        $this->assertEquals($monitoringResource->getInDowntime(), $monitoringResourceDetail['in_downtime']);
        $this->assertEquals($monitoringResource->getInformation(), $monitoringResourceDetail['information']);
        $this->assertEquals($monitoringResource->getLastCheck(), $monitoringResourceDetail['last_check']);
        $this->assertEquals(
            $monitoringResource->getLastStatusChange(),
            $monitoringResourceDetail['last_status_change']
        );
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
        $this->assertEquals($monitoringResource->getStatus()->getCode(), $monitoringResourceDetail['status']['code']);
        $this->assertEquals($monitoringResource->getStatus()->getName(), $monitoringResourceDetail['status']['name']);
        $this->assertEquals(
            $monitoringResource->getStatus()->getSeverityCode(),
            $monitoringResourceDetail['status']['severity_code']
        );
        $this->assertEquals($monitoringResource->getTries(), $monitoringResourceDetail['tries']);
        $this->assertEquals($monitoringResource->getDuration(), $monitoringResourceDetail['duration']);
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

        /** parent monitoring resource */
        $parentMonitoringResource = $monitoringResource->getParent();
        $parentMonitoringResourceResponse = $monitoringResourceDetail['parent'];

        $this->assertIsArray($parentMonitoringResourceResponse);
        $this->assertEquals($parentMonitoringResource->getId(), $parentMonitoringResourceResponse['id']);
        $this->assertEquals($parentMonitoringResource->getUuid(), $parentMonitoringResourceResponse['uuid']);
        $this->assertEquals($parentMonitoringResource->getShortType(), $parentMonitoringResourceResponse['short_type']);
        $this->assertEquals($parentMonitoringResource->getName(), $parentMonitoringResourceResponse['name']);
        $this->assertEquals($parentMonitoringResource->getType(), $parentMonitoringResourceResponse['type']);
        $this->assertEquals($parentMonitoringResource->getAlias(), $parentMonitoringResourceResponse['alias']);
        $this->assertEquals($parentMonitoringResource->getFqdn(), $parentMonitoringResourceResponse['fqdn']);
        $this->assertEquals($parentMonitoringResource->getServiceId(), $parentMonitoringResourceResponse['service_id']);
        $this->assertEquals($parentMonitoringResource->getHostId(), $parentMonitoringResourceResponse['host_id']);
        $this->assertEquals(
            $parentMonitoringResource->getAcknowledged(),
            $parentMonitoringResourceResponse['acknowledged']
        );
        $this->assertEquals(
            $parentMonitoringResource->getActiveChecks(),
            $parentMonitoringResourceResponse['active_checks']
        );
        $this->assertEquals($parentMonitoringResource->getFlapping(), $parentMonitoringResourceResponse['flapping']);
        $this->assertEquals(
            $parentMonitoringResource->getIcon()->getName(),
            $parentMonitoringResourceResponse['icon']['name']
        );
        $this->assertEquals(
            $parentMonitoringResource->getIcon()->getUrl(),
            $parentMonitoringResourceResponse['icon']['url']
        );
        $this->assertEquals(
            $parentMonitoringResource->getInDowntime(),
            $parentMonitoringResourceResponse['in_downtime']
        );
        $this->assertEquals(
            $parentMonitoringResource->getInformation(),
            $parentMonitoringResourceResponse['information']
        );
        $this->assertEquals(
            $parentMonitoringResource->getLastCheck(),
            $parentMonitoringResourceResponse['last_check']
        );
        $this->assertEquals(
            $parentMonitoringResource->getLastStatusChange(),
            $parentMonitoringResourceResponse['last_status_change']
        );
        $this->assertEquals(
            $parentMonitoringResource->getMonitoringServerName(),
            $parentMonitoringResourceResponse['monitoring_server_name']
        );
        $this->assertEquals(
            $parentMonitoringResource->isNotificationEnabled(),
            $parentMonitoringResourceResponse['notification_enabled']
        );
        $this->assertEquals(
            $parentMonitoringResource->getPassiveChecks(),
            $parentMonitoringResourceResponse['passive_checks']
        );
        $this->assertEquals(
            $parentMonitoringResource->getPerformanceData(),
            $parentMonitoringResourceResponse['performance_data']
        );
        $this->assertEquals(
            $parentMonitoringResource->getSeverityLevel(),
            $parentMonitoringResourceResponse['severity_level']
        );
        $this->assertEquals(
            $parentMonitoringResource->getStatus()->getCode(),
            $parentMonitoringResourceResponse['status']['code']
        );
        $this->assertEquals(
            $parentMonitoringResource->getStatus()->getName(),
            $parentMonitoringResourceResponse['status']['name']
        );
        $this->assertEquals(
            $parentMonitoringResource->getStatus()->getSeverityCode(),
            $parentMonitoringResourceResponse['status']['severity_code']
        );
        $this->assertEquals($parentMonitoringResource->getTries(), $parentMonitoringResourceResponse['tries']);
        $this->assertEquals($parentMonitoringResource->getDuration(), $parentMonitoringResourceResponse['duration']);
        $this->assertEquals(
            $parentMonitoringResource->getLinks()->getExternals()->getNotes()->getUrl(),
            $parentMonitoringResourceResponse['links']['externals']['notes']['url']
        );
        $this->assertEquals(
            $parentMonitoringResource->getLinks()->getExternals()->getNotes()->getLabel(),
            $parentMonitoringResourceResponse['links']['externals']['notes']['label']
        );
        $this->assertEquals(
            $parentMonitoringResource->getLinks()->getExternals()->getActionUrl(),
            $parentMonitoringResourceResponse['links']['externals']['action_url']
        );
    }
}
