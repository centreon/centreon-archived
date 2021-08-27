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

namespace Tests\Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\API\Model;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110 as UseCase;
use Tests\Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResourceTest;
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Model\MonitoringResourceHostDetailFormatter;

/**
 * @package  Tests\Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\API\Model
 */
class MonitoringResourceHostDetailFormatterTest extends TestCase
{
    /**
     * @var MonitoringResource
     */
    private $monitoringResource;

    protected function setUp(): void
    {
        $this->monitoringResource = (MonitoringResourceTest::createEntity())
            ->setType('host')
            ->setParent(null)
            ->setStatus((new ResourceStatus())
                ->setCode(0)
                ->setName(ResourceStatus::STATUS_NAME_UP)
                ->setSeverityCode(ResourceStatus::SEVERITY_OK));
    }

    /**
     * We check the format sent for the API request (v21.10) using the factory
     */
    public function testCreateFromResponse(): void
    {
        $response = new UseCase\DetailHostMonitoringResource\DetailHostMonitoringResourceResponse();
        $response->setHostMonitoringResourceDetail($this->monitoringResource);
        $responseLinks = [
                'endpoints' => [
                    'detail' => 'details_endpoint',
                    'downtime' => 'downtime_endpoint',
                    'acknowledgement' => 'acknowledgement_endpoint',
                    'timeline' => 'timeline_endpoint',
                    'status_graph' => 'status_graph_endpoint',
                    'performance_graph' => 'performance_graph_endpoint'
                ],
                'uris' => [
                    'configuration' => 'configuration_uri',
                    'reporting' => 'reporting_uri',
                    'logs' => 'logs_uri'
                ]
        ];
        $monitoringResource = MonitoringResourceHostDetailFormatter::createFromResponse($response, $responseLinks);

        $monitoringResourceDetail = $response->getHostMonitoringResourceDetail();

        $this->assertCount(
            count($response->getHostMonitoringResourceDetail()),
            $response->getHostMonitoringResourceDetail()
        );

        $this->assertEquals($monitoringResourceDetail['uuid'], $monitoringResource->uuid);
        $this->assertEquals($monitoringResourceDetail['id'], $monitoringResource->id);
        $this->assertEquals($monitoringResourceDetail['name'], $monitoringResource->name);
        $this->assertEquals($monitoringResourceDetail['type'], $monitoringResource->type);
        $this->assertEquals($monitoringResourceDetail['short_type'], $monitoringResource->short_type);
        $this->assertEquals($monitoringResourceDetail['alias'], $monitoringResource->alias);
        $this->assertEquals($monitoringResourceDetail['fqdn'], $monitoringResource->fqdn);
        $this->assertEquals($monitoringResourceDetail['acknowledged'], $monitoringResource->acknowledged);
        $this->assertEquals($monitoringResourceDetail['active_checks'], $monitoringResource->active_checks);
        $this->assertEquals($monitoringResourceDetail['duration'], $monitoringResource->duration);
        $this->assertEquals($monitoringResourceDetail['flapping'], $monitoringResource->flapping);
        $this->assertEquals($monitoringResourceDetail['icon']['name'], $monitoringResource->icon['name']);
        $this->assertEquals($monitoringResourceDetail['icon']['url'], $monitoringResource->icon['url']);
        $this->assertEquals($monitoringResourceDetail['in_downtime'], $monitoringResource->in_downtime);
        $this->assertEquals($monitoringResourceDetail['information'], $monitoringResource->information);
        $this->assertEquals($monitoringResourceDetail['last_check'], $monitoringResource->last_check);
        $this->assertEquals($monitoringResourceDetail['last_status_change'], $monitoringResource->last_status_change);
        $this->assertEquals(
            $monitoringResourceDetail['monitoring_server_name'],
            $monitoringResource->monitoring_server_name
        );
        $this->assertEquals(
            $monitoringResourceDetail['notification_enabled'],
            $monitoringResource->notification_enabled
        );
        $this->assertEquals($monitoringResourceDetail['parent'], $monitoringResource->parent);
        $this->assertEquals($monitoringResourceDetail['passive_checks'], $monitoringResource->passive_checks);
        $this->assertEquals($monitoringResourceDetail['performance_data'], $monitoringResource->performance_data);
        $this->assertEquals($monitoringResourceDetail['severity_level'], $monitoringResource->severity_level);
        $this->assertEquals($monitoringResourceDetail['status']['code'], $monitoringResource->status['code']);
        $this->assertEquals($monitoringResourceDetail['status']['name'], $monitoringResource->status['name']);
        $this->assertEquals(
            $monitoringResourceDetail['status']['severity_code'],
            $monitoringResource->status['severity_code']
        );
        $this->assertEquals($monitoringResourceDetail['tries'], $monitoringResource->tries);
        $this->assertEquals($responseLinks['uris']['logs'], $monitoringResource->links['uris']['logs']);
        $this->assertEquals(
            $responseLinks['uris']['configuration'],
            $monitoringResource->links['uris']['configuration']
        );
        $this->assertEquals(
            $responseLinks['uris']['reporting'],
            $monitoringResource->links['uris']['reporting']
        );
        $this->assertEquals(
            $responseLinks['endpoints']['detail'],
            $monitoringResource->links['endpoints']['detail']
        );
        $this->assertEquals(
            $responseLinks['endpoints']['downtime'],
            $monitoringResource->links['endpoints']['downtime']
        );
        $this->assertEquals(
            $responseLinks['endpoints']['acknowledgement'],
            $monitoringResource->links['endpoints']['acknowledgement']
        );
        $this->assertEquals(
            $responseLinks['endpoints']['timeline'],
            $monitoringResource->links['endpoints']['timeline']
        );
        $this->assertEquals(
            $monitoringResourceDetail['links']['externals']['action_url'],
            $monitoringResource->links['externals']['action_url']
        );
        $this->assertEquals(
            $monitoringResourceDetail['links']['externals']['notes'],
            $monitoringResource->links['externals']['notes']
        );
    }
}
