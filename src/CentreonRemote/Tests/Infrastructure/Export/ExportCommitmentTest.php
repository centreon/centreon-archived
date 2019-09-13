<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
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

    protected function setUp()
    {
        $parser = $this->getMockBuilder(ExportParserYaml::class)
            ->setMethods(['parse', 'dump'])
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
        $this->assertEquals(0777, $this->commitment->getFilePermission());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getParser
     */
    public function testGetParser()
    {
        $this->assertInstanceOf(ExportParserInterface::class, $this->commitment->getParser());
    }
}
