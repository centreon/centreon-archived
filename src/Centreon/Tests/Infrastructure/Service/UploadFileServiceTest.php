<?php

namespace Centreon\Tests\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as ContainerWrap;
use Centreon\Infrastructure\FileManager\File;
use Centreon\Infrastructure\Service\UploadFileService;

class UploadFileServiceTest extends TestCase
{

    public function setUp()
    {
        $container = new ContainerWrap(new Container);
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

    public function testGetFiles()
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

    public function testPrepare()
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
