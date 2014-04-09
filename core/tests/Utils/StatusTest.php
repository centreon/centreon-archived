<?php
namespace Test\Centreon\Utils;

use \Centreon\Internal\Utils\Status;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    public function testNumToString()
    {
        /* Test get status for host */
        $this->assertEquals(
            'Up',
            Status::numToString(0, Status::TYPE_HOST)
        );
        /* Test get status for service */
        $this->assertEquals(
            'Critical',
            Status::numToString(2, Status::TYPE_SERVICE)
        );
        /* Test get status for event */
        $this->assertEquals(
            'Unknown',
            Status::numToString(3, Status::TYPE_EVENT)
        );
        /* Test get status translated */
        $this->assertEquals(
            'Unknown',
            Status::numToString(3, Status::TYPE_EVENT, true)
        );
    }

    public function testNumToStringExceptionBadType()
    {
        $this->setExpectedException(
            '\OutOfBoundsException',
            "Status type or status number does not exists"
        );
        Status::numToString(1, 42);
    }

    public function testNumToStringExceptionBadStatus()
    {
        $this->setExpectedException(
            '\OutOfBoundsException',
            "Status type or status number does not exists"
        );
        Status::numToString(42, Status::TYPE_HOST);
    }

    public function testStringToNum()
    {
        /* Test get status for host */
        $this->assertEquals(
            0,
            Status::stringToNum('Up', Status::TYPE_HOST)
        );
        /* Test get status for service */
        $this->assertEquals(
            2,
            Status::stringToNum('Critical', Status::TYPE_SERVICE)
        );
        /* Test get status for event */
        $this->assertEquals(
            5,
            Status::stringToNum('Information', Status::TYPE_EVENT)
        );
    }

    public function testStringToNumExceptionBadType()
    {
        $this->setExpectedException(
            '\OutOfBoundsException',
            "Status type does not exists"
        );
        Status::stringToNum('Critical', 42);
    }

    public function testStringToNumExceptionBadStatus()
    {
        $this->setExpectedException(
            '\OutOfBoundsException',
            "Status text does not exists"
        );
        Status::stringToNum('BadStatus', Status::TYPE_HOST);
    }
}
