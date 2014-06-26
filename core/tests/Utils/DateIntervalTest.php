<?php
namespace Test\Centreon\Utils;

use \Centreon\Internal\Utils\DateInterval;

class DateIntervalTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $diff = 47492387;
        $date = new DateInterval($diff);
        $this->assertEquals(1, $date->y);
        $this->assertEquals(6, $date->m);
        $this->assertEquals(4, $date->d);
        $this->assertEquals(16, $date->h);
        $this->assertEquals(19, $date->i);
        $this->assertEquals(47, $date->s);
    }
}
