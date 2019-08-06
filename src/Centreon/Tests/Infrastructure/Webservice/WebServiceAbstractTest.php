<?php

namespace Centreon\Tests\Infrastructure\CentreonLegacyDB;

use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\Webservice\WebServiceAbstract;

class WebServiceAbstractTest extends TestCase
{

    public function setUp()
    {
        $this->webservice = $this->getMockBuilder(WebServiceAbstract::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getName',
            ])
            ->getMockForAbstractClass();
    }

    public function testQuery()
    {
        $_GET = [
            'test1' => '1',
            'test2' => '2',
        ];

        $this->assertEquals($_GET, $this->webservice->query());
    }

    public function testQueryWithoutGet()
    {
        $_GET = null;
        
        $this->assertEquals([], $this->webservice->query());
    }

    public function testPayloadRaw()
    {
        $this->assertEquals('', $this->webservice->payloadRaw());
    }

    public function testPayload()
    {
        $webservice = $this->getMockBuilder(WebServiceAbstract::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'payloadRaw',
            ])
            ->getMockForAbstractClass();

        $webservice
             ->method('payloadRaw')
             ->will($this->returnValue('{"id":"1"}'));
        
        $this->assertEquals([
            'id' => '1',
        ], $webservice->payload());
    }

    public function testPayloadWithException()
    {
        $webservice = $this->getMockBuilder(WebServiceAbstract::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'payloadRaw',
            ])
            ->getMockForAbstractClass();

        $webservice
             ->method('payloadRaw')
             ->will($this->returnValue('{id":"1"}'));
        
        $this->assertEquals([], $webservice->payload());
    }
}
