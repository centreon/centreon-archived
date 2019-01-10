<?php

namespace Centreon\Tests\Application\DataRepresenter;

use PHPUnit\Framework\TestCase;
use Centreon\Application\DataRepresenter\Entity;
use Centreon\Application\DataRepresenter\Listing;

class ListingTest extends TestCase
{

    public function testJsonSerialize()
    {
        $data = [
            1, 2, 3, 4, 5, 6,
        ];

        $dataRepresenter = new Listing($data, null, 1, 2);
        $result = $dataRepresenter->jsonSerialize();

        $this->assertArrayHasKey('pagination', $result);
        $this->assertArrayHasKey('total', $result['pagination']);
        $this->assertArrayHasKey('offset', $result['pagination']);
        $this->assertArrayHasKey('limit', $result['pagination']);

        $this->assertArrayHasKey('entities', $result);
        $this->assertEquals(6, $result['pagination']['total']);
        $this->assertEquals(1, $result['pagination']['offset']);
        $this->assertEquals(2, $result['pagination']['limit']);

        $this->assertInstanceOf(Entity::class, current($result['entities']));
    }
}