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
use Centreon\Test\Traits\TestCaseExtensionTrait;
use CentreonRemote\Infrastructure\Export\ExportParserJson;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportManifest;
use DateTime;

/**
 * @group CentreonRemote
 */
class ExportManifestTest extends TestCase
{

    use TestCaseExtensionTrait;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportCommitment
     */
    protected $commitment;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportManifest
     */
    protected $manifest;

    /**
     * @var string
     */
    protected $version = '18.10';

    /**
     * @var array<mixed>
     */
    protected $dumpData = [];

    /**
     * Set up datasets and mocks
     */
    protected function setUp(): void
    {
        $parser = $this->getMockBuilder(ExportParserJson::class)
            ->onlyMethods([
                'parse',
                'dump',
            ])
            ->getMock();
        $parser->method('parse')
            ->will($this->returnCallback(function () {
                $args = func_get_args();
                $file = $args[0];

                return [];
            }));
        $parser->method('dump')
            ->will($this->returnCallback(function () {
                $args = func_get_args();

                $this->dumpData[$args[1]] = $args[0];
            }));

        $this->commitment = new ExportCommitment(1, [2, 3], null, $parser);
        $this->manifest = $this
            ->getMockBuilder(ExportManifest::class)
            ->onlyMethods([
                'getFile',
            ])
            ->setConstructorArgs([
                $this->commitment,
                $this->version
            ])
            ->getMock();
        $this->manifest
            ->method('getFile')
            ->will($this->returnCallback(function () {
                return __FILE__;
            }));
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportManifest::get
     */
    public function testGet(): void
    {
        $this->assertNull($this->manifest->get('missing-data'));
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportManifest::dump
     */
    public function testDump(): void
    {
        $date = date('l jS \of F Y h:i:s A');
        $this->manifest->dump([
            'date' => $date,
            'remote_server' => $this->commitment->getRemote(),
            'pollers' => $this->commitment->getPollers(),
            'import' => null,
        ]);

        $this->assertEquals(
            [
                $this->manifest->getFile() => [
                    'version' => $this->version,
                    'date' => $date,
                    'remote_server' => $this->commitment->getRemote(),
                    'pollers' => $this->commitment->getPollers(),
                    'import' => null,
                ],
            ],
            $this->dumpData
        );
    }
}
