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
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Model\MonitoringResourceFormatter;
use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\FindMonitoringResources\FindMonitoringResourcesResponse;

/**
 * @package  Tests\Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\API\Model
 */
class MonitoringResourceFormatterTest extends TestCase
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
        $response = new FindMonitoringResourcesResponse();
        $response->setMonitoringResources([$this->monitoringResource]);
        $responseLinks = [
            [
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
        $monitoringResources = MonitoringResourceFormatter::createFromResponse($response, $responseLinks);

        $oneMonitoringResource = $response->getMonitoringResources()[0];

        $this->assertCount(count($response->getMonitoringResources()), $response->getMonitoringResources());

        $this->assertEquals($oneMonitoringResource['uuid'], $monitoringResources[0]->uuid);
        $this->assertEquals($oneMonitoringResource['id'], $monitoringResources[0]->id);
        $this->assertEquals($oneMonitoringResource['name'], $monitoringResources[0]->name);
        $this->assertEquals($oneMonitoringResource['type'], $monitoringResources[0]->type);
        $this->assertEquals($oneMonitoringResource['short_type'], $monitoringResources[0]->short_type);
        $this->assertEquals($oneMonitoringResource['alias'], $monitoringResources[0]->alias);
        $this->assertEquals($oneMonitoringResource['fqdn'], $monitoringResources[0]->fqdn);
        $this->assertEquals($oneMonitoringResource['acknowledged'], $monitoringResources[0]->acknowledged);
        $this->assertEquals($oneMonitoringResource['active_checks'], $monitoringResources[0]->active_checks);
        $this->assertEquals($oneMonitoringResource['duration'], $monitoringResources[0]->duration);
        $this->assertEquals($oneMonitoringResource['flapping'], $monitoringResources[0]->flapping);
        $this->assertEquals($oneMonitoringResource['icon']['name'], $monitoringResources[0]->icon['name']);
        $this->assertEquals($oneMonitoringResource['icon']['url'], $monitoringResources[0]->icon['url']);
        $this->assertEquals($oneMonitoringResource['in_downtime'], $monitoringResources[0]->in_downtime);
        $this->assertEquals($oneMonitoringResource['information'], $monitoringResources[0]->information);
        $this->assertEquals($oneMonitoringResource['last_check'], $monitoringResources[0]->last_check);
        $this->assertEquals($oneMonitoringResource['last_status_change'], $monitoringResources[0]->last_status_change);
        $this->assertEquals(
            $oneMonitoringResource['monitoring_server_name'],
            $monitoringResources[0]->monitoring_server_name
        );
        $this->assertEquals(
            $oneMonitoringResource['notification_enabled'],
            $monitoringResources[0]->notification_enabled
        );
        $this->assertEquals($oneMonitoringResource['parent']['uuid'], $monitoringResources[0]->parent['uuid']);
        $this->assertEquals(
            $oneMonitoringResource['parent']['short_type'],
            $monitoringResources[0]->parent['short_type']
        );
        $this->assertEquals($oneMonitoringResource['parent']['id'], $monitoringResources[0]->parent['id']);
        $this->assertEquals($oneMonitoringResource['parent']['name'], $monitoringResources[0]->parent['name']);
        $this->assertEquals($oneMonitoringResource['parent']['alias'], $monitoringResources[0]->parent['alias']);
        $this->assertEquals($oneMonitoringResource['parent']['fqdn'], $monitoringResources[0]->parent['fqdn']);
        $this->assertEquals(
            $oneMonitoringResource['parent']['service_id'],
            $monitoringResources[0]->parent['service_id']
        );
        $this->assertEquals($oneMonitoringResource['parent']['host_id'], $monitoringResources[0]->parent['host_id']);
        $this->assertEquals(
            $oneMonitoringResource['parent']['acknowledged'],
            $monitoringResources[0]->parent['acknowledged']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['active_checks'],
            $monitoringResources[0]->parent['active_checks']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['flapping'],
            $monitoringResources[0]->parent['flapping']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['icon']['name'],
            $monitoringResources[0]->parent['icon']['name']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['icon']['url'],
            $monitoringResources[0]->parent['icon']['url']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['in_downtime'],
            $monitoringResources[0]->parent['in_downtime']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['information'],
            $monitoringResources[0]->parent['information']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['last_check'],
            $monitoringResources[0]->parent['last_check']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['last_status_change'],
            $monitoringResources[0]->parent['last_status_change']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['monitoring_server_name'],
            $monitoringResources[0]->parent['monitoring_server_name']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['notification_enabled'],
            $monitoringResources[0]->parent['notification_enabled']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['passive_checks'],
            $monitoringResources[0]->parent['passive_checks']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['performance_data'],
            $monitoringResources[0]->parent['performance_data']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['severity_level'],
            $monitoringResources[0]->parent['severity_level']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['status']['code'],
            $monitoringResources[0]->parent['status']['code']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['status']['name'],
            $monitoringResources[0]->parent['status']['name']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['status']['severity_code'],
            $monitoringResources[0]->parent['status']['severity_code']
        );
        $this->assertEquals($oneMonitoringResource['parent']['tries'], $monitoringResources[0]->parent['tries']);
        $this->assertEquals($oneMonitoringResource['parent']['duration'], $monitoringResources[0]->parent['duration']);
        $this->assertEquals(
            $oneMonitoringResource['parent']['links']['uris'],
            $monitoringResources[0]->parent['links']['uris']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['links']['endpoints'],
            $monitoringResources[0]->parent['links']['endpoints']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['links']['externals']['notes'],
            $monitoringResources[0]->parent['links']['externals']['notes']
        );
        $this->assertEquals(
            $oneMonitoringResource['parent']['links']['externals']['action_url'],
            $monitoringResources[0]->parent['links']['externals']['action_url']
        );
        $this->assertEquals($oneMonitoringResource['passive_checks'], $monitoringResources[0]->passive_checks);
        $this->assertEquals($oneMonitoringResource['performance_data'], $monitoringResources[0]->performance_data);
        $this->assertEquals($oneMonitoringResource['severity_level'], $monitoringResources[0]->severity_level);
        $this->assertEquals($oneMonitoringResource['status']['code'], $monitoringResources[0]->status['code']);
        $this->assertEquals($oneMonitoringResource['status']['name'], $monitoringResources[0]->status['name']);
        $this->assertEquals(
            $oneMonitoringResource['status']['severity_code'],
            $monitoringResources[0]->status['severity_code']
        );
        $this->assertEquals($oneMonitoringResource['tries'], $monitoringResources[0]->tries);
        $this->assertEquals($responseLinks[0]['uris']['logs'], $monitoringResources[0]->links['uris']['logs']);
        $this->assertEquals(
            $responseLinks[0]['uris']['configuration'],
            $monitoringResources[0]->links['uris']['configuration']
        );
        $this->assertEquals(
            $responseLinks[0]['uris']['reporting'],
            $monitoringResources[0]->links['uris']['reporting']
        );
        $this->assertEquals(
            $responseLinks[0]['endpoints']['detail'],
            $monitoringResources[0]->links['endpoints']['detail']
        );
        $this->assertEquals(
            $responseLinks[0]['endpoints']['downtime'],
            $monitoringResources[0]->links['endpoints']['downtime']
        );
        $this->assertEquals(
            $responseLinks[0]['endpoints']['acknowledgement'],
            $monitoringResources[0]->links['endpoints']['acknowledgement']
        );
        $this->assertEquals(
            $responseLinks[0]['endpoints']['timeline'],
            $monitoringResources[0]->links['endpoints']['timeline']
        );
        $this->assertEquals(
            $responseLinks[0]['endpoints']['status_graph'],
            $monitoringResources[0]->links['endpoints']['status_graph']
        );
        $this->assertEquals(
            $responseLinks[0]['endpoints']['performance_graph'],
            $monitoringResources[0]->links['endpoints']['performance_graph']
        );
        $this->assertEquals(
            $oneMonitoringResource['links']['externals']['action_url'],
            $monitoringResources[0]->links['externals']['action_url']
        );
        $this->assertEquals(
            $oneMonitoringResource['links']['externals']['notes'],
            $monitoringResources[0]->links['externals']['notes']
        );
    }
}
