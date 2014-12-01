<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace Test\CentreonEngine\Repository;

use \Test\Centreon\DbTestCase;
use \Centreon\Internal\Utils\Filesystem\Directory;
use \CentreonEngine\Repository\WriteConfigFileRepository;

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
