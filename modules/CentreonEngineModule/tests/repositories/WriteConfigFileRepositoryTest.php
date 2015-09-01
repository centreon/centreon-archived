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
use Centreon\Internal\Utils\Filesystem\Directory;
use CentreonEngine\Repository\WriteConfigFileRepository;

class WriteConfigFileRepositoryTest extends \PHPUnit_Framework_TestCase
{
    protected $tmpDir;

    public function setUp()
    {
        $this->tmpDir = Directory::temporary('ut_', true);
    }

    public function tearDown()
    {
        if ($this->tmpDir != '' && is_dir($this->tmpDir)) {
            Directory::delete($this->tmpDir, true);
        }
    }

    public function testInitFile()
    {
        $fh = WriteConfigFileRepository::initFile($this->tmpDir . '/test/init.cfg');
        $this->assertInternalType('resource', $fh);
        fclose($fh);
        $this->assertFileExists($this->tmpDir . '/test/init.cfg');
    }

    public function testInitFileCannotWrite()
    {
        /*$this->setExpectedException(
            '\Centreon\Internal\Exception',
            'Cannot open file "' . $this->tmpDir . '"'
        );
        WriteConfigFileRepository::initFile('/error');*/
    }

    public function testGetFileType()
    {
        $this->assertEquals('resource_file', WriteConfigFileRepository::getFileType('resources.cfg'));
        $this->assertEquals('cfg_file', WriteConfigFileRepository::getFileType('command.cfg'));
        $this->assertEquals('cfg_file', WriteConfigFileRepository::getFileType('periods.cfg'));
        $this->assertEquals('cfg_file', WriteConfigFileRepository::getFileType('connectors.cfg'));
        $this->assertEquals('main_file', WriteConfigFileRepository::getFileType('centengine.cfg'));
        $this->assertEquals('cfg_dir', WriteConfigFileRepository::getFileType('other.cfg'));
    }

    public function testWriteObjectFile()
    {
        $filename = $this->tmpDir . '/centengine.cfg';
        $content = array(
            array(
                'type' => 'object1',
                'content' => array(
                    'key1' => 'value1',
                    'key2' => 'value2',
                )
            ),
            array(
                'type' => 'object2',
                'content' => array(
                    'key3' => 'value3',
                    'key4' => 'value4',
                )
            )
        );
        $fileList = array();
        WriteConfigFileRepository::writeObjectFile($content, $filename, $fileList);
        $resultContent = file_get_contents(dirname(__DIR__) . '/data/configfiles/generate1.cfg');
        $contentFile = file_get_contents($filename);
        /* Remove line with the generate date */
        $lines = explode("\n", $contentFile);
        $lines = preg_grep('/^#\s+Last.*#$/', $lines, PREG_GREP_INVERT);
        $contentFile = join("\n", $lines);
        $this->assertEquals($resultContent, $contentFile);
    }

    public function testWriteParamsFile()
    {
        $filename = $this->tmpDir . '/centengine.cfg';
        $content = array(
            'object1' => array(
                'value1',
                'value2'
            ),
            'object2' => 'value3'
        );
        $fileList = array();
        WriteConfigFileRepository::writeParamsFile($content, $filename, $fileList);
        $resultContent = file_get_contents(dirname(__DIR__) . '/data/configfiles/generate2.cfg');
        $contentFile = file_get_contents($filename);
        /* Remove line with the generate date */
        $lines = explode("\n", $contentFile);
        $lines = preg_grep('/^#\s+Last.*#$/', $lines, PREG_GREP_INVERT);
        $contentFile = join("\n", $lines);
        $this->assertEquals($resultContent, $contentFile);
    }
}
