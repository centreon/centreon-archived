<?php

namespace Test\Centreon\Centreon;

use \Centreon\Core\Di;

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

    public function testInstance()
    {
        $di = new Di();
        $this->assertSame($di, Di::getDefault());
    }
}
