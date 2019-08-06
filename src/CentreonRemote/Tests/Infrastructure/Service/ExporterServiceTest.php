<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace CentreonRemote\Tests\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use CentreonRemote\Infrastructure\Service\ExporterService;
use CentreonRemote\Domain\Exporter\PollerExporter;
use CentreonRemote\Domain\Exporter\ServiceExporter;

/**
 * @group CentreonRemote
 */
class ExporterServiceTest extends TestCase
{

    protected function setUp()
    {
        $this->exporter = new ExporterService();
        $this->exporter->add(ServiceExporter::class, function () {
            return new ServiceExporter(new Container);
        });
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Service\ExporterService::add
     * @covers \CentreonRemote\Infrastructure\Service\ExporterService::_sort
     * @expectedException \Centreon\Infrastructure\Service\Exception\NotFoundException
     */
    public function testAdd()
    {
        $this->exporter->add(ExporterService::class, function () {
            return new ExporterService(new Container);
        });

        $this->exporter->add(PollerExporter::class, function () {
            return new PollerExporter(new Container);
        });

        $this->assertTrue($this->exporter->has(PollerExporter::getName()));
        
        $all = $this->exporter->all();

        $this->assertGreaterThan(ServiceExporter::order(), PollerExporter::order());
        $this->assertEquals(PollerExporter::getName(), $all[0]['name']);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Service\ExporterService::has
     * @covers \CentreonRemote\Infrastructure\Service\ExporterService::_getKey
     */
    public function testHas()
    {
        $this->assertTrue($this->exporter->has(ServiceExporter::getName()));
        $this->assertFalse($this->exporter->has(PollerExporter::getName()));
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Service\ExporterService::get
     * @covers \CentreonRemote\Infrastructure\Service\ExporterService::_getKey
     * @expectedException \Centreon\Infrastructure\Service\Exception\NotFoundException
     */
    public function testGet()
    {
        $data = $this->exporter->get(ServiceExporter::getName());

        $this->assertEquals(ServiceExporter::getName(), $data['name']);
        $this->assertEquals(ServiceExporter::class, $data['classname']);

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('classname', $data);
        $this->assertArrayHasKey('factory', $data);
        
        // throw exception
        $this->exporter->get(PollerExporter::getName());
    }
}
