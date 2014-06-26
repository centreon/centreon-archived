<?php

namespace Test\Centreon;

use \Centreon\Internal\Di;

class DiTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Di::reset();
    }

    public function testSetShared()
    {
        $di = new Di();
        $di->setShared('test1', 'Test string');
        $this->assertEquals('Test string', $di->get('test1'));
        $obj = new \StdClass();
        $di->setShared('test2', $obj);
        $this->assertSame($obj, $di->get('test2'));
        $di->set('test3', $obj, true);
        $this->assertSame($obj, $di->get('test3'));
    }

    public function testSet()
    {
        $di = new Di();
        $di->set('testStdClass', 'StdClass');
        $tmp = $di->get('testStdClass');
        $this->assertInstanceOf('\StdClass', $tmp);
        $this->assertSame($tmp, $di->get('testStdClass'));
        $di->set('testClosure', function () {
            return 'String';
        });
        $this->assertEquals('String', $di->get('testClosure'));
    }

    public function testGetBadName()
    {
        $di = new Di();
        $this->setExpectedException('\Centreon\Internal\Exception', "The service injector is not defined.");
        $di->get('no_service');
    }

    public function testGetBadServiceType()
    {
        $di = new Di();
        $di->set('badservice', array());
        $this->setExpectedException('\Centreon\Internal\Exception', "Bad type of service");
        $di->get('badservice');
    }

    public function testInstance()
    {
        $di = new Di();
        $this->assertSame($di, Di::getDefault());
        $di2 = new Di();
        $this->assertNotSame($di2, Di::getDefault());
        Di::setDefault($di2);
        $this->assertSame($di2, Di::getDefault());
    }
}
