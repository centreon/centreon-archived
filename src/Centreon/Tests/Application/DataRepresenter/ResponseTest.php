<?php

namespace Centreon\Tests\Application\DataRepresenter;

use PHPUnit\Framework\TestCase;
use Centreon\Application\DataRepresenter\Response;

class ResponseTest extends TestCase
{

    public function testJsonSerialize()
    {
        $data = 'OK';

        $dataRepresenter = new Response($data, false);
        $result = $dataRepresenter->jsonSerialize();

        $this->assertArrayHasKey('result', $result);
        $this->assertArrayHasKey('status', $result);

        $this->assertEquals($data, $result['result']);
        $this->assertFalse($result['status']);
    }
}