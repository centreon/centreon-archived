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

    public function testJsonSerializeWithObject()
    {
        $value = [
            'prop1' => true,
            'prop2' => null,
            'prop3' => 'OK',
        ];

        $data = new class {
            public $prop1 = true;
            protected $prop2;
            private $prop3 = 'OK';
        };

        $dataRepresenter = new Entity($data);
        $result = $dataRepresenter->jsonSerialize();

        $this->assertEquals($value, $result);
    }
}
