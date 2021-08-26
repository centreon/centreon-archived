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
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Tests\Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResourceTest;
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Model\MonitoringResourceServiceDetailFormatter;
use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110 as UseCase;

/**
 * @package  Tests\Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\API\Model
 */
class MonitoringResourceServiceDetailFormatterTest extends TestCase
{
    /**
     * @var MonitoringResource
     */
    private $monitoringResource;

    protected function setUp(): void
    {
        $this->monitoringResource = MonitoringResourceTest::createEntity();
    }

    /**
     * We check the format sent for the API request (v21.10) using the factory
     */
    public function testCreateFromResponse(): void
    {
        $response = new UseCase\DetailServiceMonitoringResource\DetailServiceMonitoringResourceResponse();
        $response->setServiceMonitoringResourceDetail($this->monitoringResource);
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
                    'logs' => 'logs_uri',
                    'reporting' => 'reporting_uri'
                ],
                'parent' => [
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
                        'logs' => 'logs_uri',
                        'reporting' => 'reporting_uri'
                    ]
                ]
        ];
        $monitoringResource = MonitoringResourceServiceDetailFormatter::createFromResponse($response, $responseLinks);

        $monitoringResourceDetail = $response->getServiceMonitoringResourceDetail();

        $this->assertCount(
            count($response->getServiceMonitoringResourceDetail()),
            $response->getServiceMonitoringResourceDetail()
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
        $this->assertEquals($monitoringResourceDetail['parent']['uuid'], $monitoringResource->parent['uuid']);
        $this->assertEquals(
            $monitoringResourceDetail['parent']['short_type'],
            $monitoringResource->parent['short_type']
        );
        $this->assertEquals($monitoringResourceDetail['parent']['id'], $monitoringResource->parent['id']);
        $this->assertEquals($monitoringResourceDetail['parent']['name'], $monitoringResource->parent['name']);
        $this->assertEquals($monitoringResourceDetail['parent']['alias'], $monitoringResource->parent['alias']);
        $this->assertEquals($monitoringResourceDetail['parent']['fqdn'], $monitoringResource->parent['fqdn']);
        $this->assertEquals(
            $monitoringResourceDetail['parent']['service_id'],
            $monitoringResource->parent['service_id']
        );
        $this->assertEquals($monitoringResourceDetail['parent']['host_id'], $monitoringResource->parent['host_id']);
        $this->assertEquals(
            $monitoringResourceDetail['parent']['acknowledged'],
            $monitoringResource->parent['acknowledged']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['active_checks'],
            $monitoringResource->parent['active_checks']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['flapping'],
            $monitoringResource->parent['flapping']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['icon']['name'],
            $monitoringResource->parent['icon']['name']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['icon']['url'],
            $monitoringResource->parent['icon']['url']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['in_downtime'],
            $monitoringResource->parent['in_downtime']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['information'],
            $monitoringResource->parent['information']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['last_check'],
            $monitoringResource->parent['last_check']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['last_status_change'],
            $monitoringResource->parent['last_status_change']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['monitoring_server_name'],
            $monitoringResource->parent['monitoring_server_name']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['notification_enabled'],
            $monitoringResource->parent['notification_enabled']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['passive_checks'],
            $monitoringResource->parent['passive_checks']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['performance_data'],
            $monitoringResource->parent['performance_data']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['severity_level'],
            $monitoringResource->parent['severity_level']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['status']['code'],
            $monitoringResource->parent['status']['code']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['status']['name'],
            $monitoringResource->parent['status']['name']
        );
        $this->assertEquals(
            $monitoringResourceDetail['parent']['status']['severity_code'],
            $monitoringResource->parent['status']['severity_code']
        );
        $this->assertEquals($monitoringResourceDetail['parent']['tries'], $monitoringResource->parent['tries']);
        $this->assertEquals($monitoringResourceDetail['parent']['duration'], $monitoringResource->parent['duration']);
        $this->assertEquals(
            $responseLinks['parent']['uris'],
            $monitoringResource->parent['links']['uris']
        );
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
            $responseLinks['endpoints']['status_graph'],
            $monitoringResource->links['endpoints']['status_graph']
        );
        $this->assertEquals(
            $responseLinks['endpoints']['performance_graph'],
            $monitoringResource->links['endpoints']['performance_graph']
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
