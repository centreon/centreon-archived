<?php

namespace Centreon\Tests\Infrastructure\FileManager;

use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\FileManager\File;

class FileTest extends TestCase
{
    public function testAll()
    {
        $data = [
            'name' => 'A.txt',
            'type' => 'B',
            'tmp_name' => 'C',
            'error' => 'D',
            'size' => 'E',
        ];

        $entity = new File($data);

        $this->assertEquals($data['name'], $entity->getName());
        $this->assertEquals('txt', $entity->getExtension());
        $this->assertEquals($data['type'], $entity->getType());
        $this->assertEquals($data['tmp_name'], $entity->getTmpName());
        $this->assertEquals($data['error'], $entity->getError());
        $this->assertEquals($data['size'], $entity->getSize());
    }
}
