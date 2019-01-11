<?php

namespace Centreon\Tests\Application\DataRepresenter;

use PHPUnit\Framework\TestCase;
use Centreon\Application\DataRepresenter\Entity;

class EntityTest extends TestCase
{

    public function testJsonSerialize()
    {
        $data = 'OK';

        $dataRepresenter = new Entity($data);
        $result = $dataRepresenter->jsonSerialize();

        $this->assertEquals([$data], $result);
    }
}