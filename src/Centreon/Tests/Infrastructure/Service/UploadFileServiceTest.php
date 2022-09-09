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

namespace Centreon\Tests\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as ContainerWrap;
use Centreon\Infrastructure\FileManager\File;
use Centreon\Infrastructure\Service\UploadFileService;

class UploadFileServiceTest extends TestCase
{
    /**
     * @var array<string, array<string, array<int,string>|string>>
     */
    private $filesRequest;

    /**
     * @var UploadFileService
     */
    private $service;

    public function setUp(): void
    {
        $container = new ContainerWrap(new Container());
        $this->filesRequest = [
            'field1' => [
                'name' => 'A.txt',
                'type' => 'B',
                'tmp_name' => 'C',
                'error' => 'D',
                'size' => 'E',
            ],
            'field2' => [
                'name' => [
                    'A1.svg',
                    'A2.exe'
                ],
                'type' => [
                    'B1',
                    'B2',
                ],
                'tmp_name' => [
                    'C1',
                    'C2',
                ],
                'error' => [
                    'D1',
                    'D2',
                ],
                'size' => [
                    'E1',
                    'E2',
                ],
            ],
        ];

        $this->service = new UploadFileService($container, $this->filesRequest);
    }

    public function testGetFiles(): void
    {
        (function () {
            $result = $this->service->getFiles('field1');

            $this->assertCount(1, $result);
            $this->assertInstanceOf(File::class, $result[0]);
            $this->assertEquals($this->filesRequest['field1']['name'], $result[0]->getName());
        })();

        (function () {
            $result = $this->service->getFiles('field2', ['svg']);

            $this->assertCount(1, $result);
            $this->assertEquals('svg', $result[0]->getExtension());
        })();
    }

    public function testPrepare(): void
    {
        (function () {
            $result = $this->service->prepare('field1');

            $this->assertCount(1, $result);
        })();

        (function () {
            $result = $this->service->prepare('field2');

            $this->assertCount(2, $result);

            $value = [
                'name' => 'A1.svg',
                'type' => 'B1',
                'tmp_name' => 'C1',
                'error' => 'D1',
                'size' => 'E1',
            ];
            $this->assertEquals($value, $result[0]);
        })();

        (function () {
            $result = $this->service->prepare('field3');

            $this->assertEquals([], $result);
        })();
    }
}
