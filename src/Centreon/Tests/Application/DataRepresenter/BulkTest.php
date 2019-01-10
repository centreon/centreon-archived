<?php

namespace Centreon\Tests\Application\DataRepresenter;

use PHPUnit\Framework\TestCase;
use Centreon\Application\DataRepresenter\Bulk;
use Centreon\Application\DataRepresenter\Listing;

class BulkTest extends TestCase
{

    public function testJsonSerialize()
    {
        $lists = [
            'mocks' => [
                'First',
                'Second',
            ],
            'drafts' => [],
        ];

        $dataRepresenter = new Bulk($lists);
        $result = $dataRepresenter->jsonSerialize();

        $this->assertArrayHasKey('mocks', $result);
        $this->assertArrayHasKey('drafts', $result);

        $this->assertInstanceOf(Listing::class, $result['mocks']);
        $this->assertInstanceOf(Listing::class, $result['drafts']);
    }
}