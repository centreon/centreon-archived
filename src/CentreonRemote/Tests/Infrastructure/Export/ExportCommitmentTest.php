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

namespace CentreonRemote\Tests\Infrastructure\Export;

use PHPUnit\Framework\TestCase;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportParserYaml;
use CentreonRemote\Infrastructure\Export\ExportParserInterface;

/**
 * @group CentreonRemote
 */
class ExportCommitmentTest extends TestCase
{

    protected $commitment;
    protected $remote = 1;
    protected $pollers = [2, 3];
    protected $meta = [
        '',
    ];
    protected $path = '/tmp';
    protected $exporters = [];

    protected function setUp(): void
    {
        $parser = $this->getMockBuilder(ExportParserYaml::class)
            ->onlyMethods(['parse', 'dump'])
            ->getMock();

        $this->commitment = new ExportCommitment(
            $this->remote,
            $this->pollers,
            $this->meta,
            $parser,
            $this->path,
            $this->exporters
        );
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getRemote
     */
    public function testGetRemote()
    {
        $this->assertEquals($this->remote, $this->commitment->getRemote());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getPollers
     */
    public function testGetPollers()
    {
        $result = array_merge($this->pollers, [$this->remote]);
        $this->assertEquals($result, $this->commitment->getPollers());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getMeta
     */
    public function testGetMeta()
    {
        $this->assertEquals($this->meta, $this->commitment->getMeta());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getPath
     */
    public function testGetPath()
    {
        $this->assertEquals($this->path, $this->commitment->getPath());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getExporters
     */
    public function testGetExporters()
    {
        $this->assertEquals($this->exporters, $this->commitment->getExporters());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getFilePermission
     */
    public function testGetFilePermission()
    {
        $this->assertEquals(0775, $this->commitment->getFilePermission());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getParser
     */
    public function testGetParser()
    {
        $this->assertInstanceOf(ExportParserInterface::class, $this->commitment->getParser());
    }
}
