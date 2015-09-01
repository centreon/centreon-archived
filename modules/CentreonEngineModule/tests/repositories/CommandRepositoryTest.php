<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

namespace Test\CentreonEngine\Repository;

use \Test\Centreon\DbTestCase;
use Centreon\Internal\Di;
use Centreon\Internal\Utils\Filesystem\Directory;
use CentreonEngine\Repository\CommandRepository;

class CommandRepositoryTest extends DbTestCase
{
    protected $dataPath = '/modules/CentreonEngineModule/tests/data/json/';
    protected $tmpDir;

    public function setUp()
    {
        parent::setUp();
        $this->tmpDir = Directory::temporary('ut_', true);
    }

    public function tearDown()
    {
        if ($this->tmpDir != '' && is_dir($this->tmpDir)) {
            Directory::delete($this->tmpDir, true);
        }
        parent::tearDown();
    }

    public function testGenerate()
    {
        $fileList = array();
        $pollerId = 1;
        CommandRepository::generate($fileList, $pollerId, $this->tmpDir . '/', 'command.cfg', CommandRepository::CHECK_TYPE);
        $this->assertEquals(
            array('cfg_file' => array(
                $this->tmpDir . '/1/command.cfg'
            )), $fileList
        );
        $content = file_get_contents($this->tmpDir . '/1/command.cfg');
        /* Remove line with the generate date */
        $lines = explode("\n", $content);
        $lines = preg_grep('/^#\s+Last.*#$/', $lines, PREG_GREP_INVERT);
        $content = join("\n", $lines);
        $resultContent = file_get_contents(dirname(__DIR__) . '/data/configfiles/command1.cfg');
        $this->assertEquals($resultContent, $content);
        $fileList = array();
        CommandRepository::generate($fileList, $pollerId, $this->tmpDir . '/', 'command.cfg', CommandRepository::NOTIF_TYPE);
        $this->assertEquals(
            array('cfg_file' => array(
                $this->tmpDir . '/1/command.cfg'
            )), $fileList
        );
        $content = file_get_contents($this->tmpDir . '/1/command.cfg');
        /* Remove line with the generate date */
        $lines = explode("\n", $content);
        $lines = preg_grep('/^#\s+Last.*#$/', $lines, PREG_GREP_INVERT);
        $content = join("\n", $lines);
        $resultContent = file_get_contents(dirname(__DIR__) . '/data/configfiles/command2.cfg');
        $this->assertEquals($resultContent, $content);
    }
}
