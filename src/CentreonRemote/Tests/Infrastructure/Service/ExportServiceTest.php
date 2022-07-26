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
use Pimple\Psr11\Container as ContainerWrap;
use CentreonRemote\Infrastructure\Service\ExportService;
use CentreonRemote\Infrastructure\Service\ExporterService;
use CentreonRemote\Infrastructure\Service\ExporterCacheService;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportManifest;
use CentreonRemote\Domain\Exporter\ConfigurationExporter;
use CentreonClapi\CentreonACL;
use Centreon\Test\Mock;
use Centreon\Tests\Resources\CheckPoint;
use VirtualFileSystem\FileSystem;
use Centreon\Test\Traits\TestCaseExtensionTrait;
use Centreon\ServiceProvider;

/**
 * @group CentreonRemote
 */
class ExportServiceTest extends TestCase
{
    use TestCaseExtensionTrait;

    /**
     * @var boolean
     */
    private $aclReload = false;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var FileSystem
     */
    private $fs;

    /**
     * @var ExportService
     */
    private $export;


    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->container = new Container();

        // Exporter
        $this->container['centreon_remote.exporter'] = $this->getMockBuilder(ExporterService::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'get',
            ])
            ->getMock();

        $this->container['centreon_remote.exporter']->method('get')
            ->will($this->returnCallback(function () {
                return [
                    'name' => ConfigurationExporter::getName(),
                    'classname' => ConfigurationExporter::class,
                    'factory' => function () {
                        return $this->getMockBuilder(ConfigurationExporter::class)
                            ->disableOriginalConstructor()
                            ->getMock();
                    },
                ];
            }));

        // Cache
        $this->container['centreon_remote.exporter.cache'] = $this->getMockBuilder(ExporterCacheService::class)
            ->getMock();

        // ACL
        $this->container['centreon.acl'] = $this->getMockBuilder(CentreonACL::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container['centreon.acl']->method('reload')
            ->will($this->returnCallback(function () {
                $this->aclReload = true;
            }));

        // DB service
        $this->initDbDataSet();

        // mount VFS
        $this->fs = new FileSystem();
        $this->fs->createDirectory('/export');

        // Export
        $this->export = new ExportService(new ContainerWrap($this->container));
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Service\ExportService::import
     */
    public function testImport(): void
    {
        $path = $this->fs->path('/export');

        // missing export path
        $this->assertNull($this->export->import(new ExportCommitment(null, null, null, null, "{$path}/not-found", [
            ConfigurationExporter::class,
        ])));

        $manifest = '{
    "date": "Tuesday 23rd of July 2019 11:22:19 AM",
    "pollers": [],
    "import": {},
    "remote_server": 1,
    "version": "x.y"
}';

        $this->fs->createFile('/export/manifest.json', $manifest);
        $points = new CheckPoint();
        $points->add('ConfigurationExporter::setCommitment');
        $points->add('ConfigurationExporter::import');

        $this->initDbDataSet();
        $container = clone $this->container;

        // Exporter
        $container['centreon_remote.exporter'] = $this->getMockBuilder(ExporterService::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'get',
            ])
            ->getMock();

        $container['centreon_remote.exporter']->method('get')
            ->will($this->returnCallback(function ($arg) use ($points) {
                $this->assertEquals('configuration', $arg);

                return [
                    'name' => ConfigurationExporter::getName(),
                    'classname' => ConfigurationExporter::class,
                    'factory' => function () use ($points) {
                        $exporter = $this->getMockBuilder(ConfigurationExporter::class)
                            ->disableOriginalConstructor()
                            ->disableOriginalClone()
                            ->disableArgumentCloning()
                            ->disallowMockingUnknownTypes()
                            ->getMock();

                        $exporter->method('setCommitment')
                            ->will($this->returnCallback(function ($argCommitment) use ($points) {
                                $points->mark('ConfigurationExporter::setCommitment');
                                $this->assertInstanceOf(ExportCommitment::class, $argCommitment);
                            }));

                        $exporter->method('import')
                            ->will($this->returnCallback(function ($argManifest) use ($points) {
                                $points->mark('ConfigurationExporter::import');
                                $this->assertInstanceOf(ExportManifest::class, $argManifest);
                            }));

                        return $exporter;
                    },
                ];
            }));

        (new ExportService(new ContainerWrap($container)))
            ->import(new ExportCommitment(null, null, null, null, $path, [
                ConfigurationExporter::class,
            ]));

        // assert the checklist is passed
        $points->assert($this);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Service\ExportService::refreshAcl
     */
    public function testRefreshAcl(): void
    {
        $this->invokeMethod($this->export, 'refreshAcl');

        $this->assertTrue($this->aclReload);
    }

    /**
     * Init mock of DB manager and data sets
     *
     * @return void
     */
    protected function initDbDataSet(): void
    {
        $this->container[ServiceProvider::CENTREON_DB_MANAGER] = new Mock\CentreonDBManagerService();
        $this->container[ServiceProvider::CENTREON_DB_MANAGER]
            ->addResultSet(
                "SELECT * FROM informations WHERE `key` = :key LIMIT 1",
                [[
                    'key' => 'version',
                    'value' => 'x.y',
                ]]
            )
            ->addResultSet(
                "DELETE FROM acl_resources_hc_relations "
                . "WHERE hc_id NOT IN (SELECT t2.hc_id FROM hostcategories AS t2)",
                []
            )
            ->addResultSet(
                "DELETE FROM acl_resources_hg_relations "
                . "WHERE hg_hg_id NOT IN (SELECT t2.hg_id FROM hostgroup AS t2)",
                []
            )
            ->addResultSet(
                "DELETE FROM acl_resources_hostex_relations "
                . "WHERE host_host_id NOT IN (SELECT t2.host_id FROM host AS t2)",
                []
            )
            ->addResultSet(
                "DELETE FROM acl_resources_host_relations "
                . "WHERE host_host_id NOT IN (SELECT t2.host_id FROM host AS t2)",
                []
            )
            ->addResultSet(
                "DELETE FROM acl_resources_meta_relations "
                . "WHERE meta_id NOT IN (SELECT t2.meta_id FROM meta_service AS t2)",
                []
            )
            ->addResultSet(
                "DELETE FROM acl_resources_poller_relations "
                . "WHERE poller_id NOT IN (SELECT t2.id FROM nagios_server AS t2)",
                []
            )
            ->addResultSet(
                "DELETE FROM acl_resources_sc_relations "
                . "WHERE sc_id NOT IN (SELECT t2.sc_id FROM service_categories AS t2)",
                []
            )
            ->addResultSet(
                "DELETE FROM acl_resources_service_relations "
                . "WHERE service_service_id NOT IN (SELECT t2.service_id FROM service AS t2)",
                []
            )
            ->addResultSet(
                "DELETE FROM acl_resources_sg_relations "
                . "WHERE sg_id NOT IN (SELECT t2.sg_id FROM servicegroup AS t2)",
                []
            );
    }
}
