<?php
namespace Test\Centreon\Utils;

use \Centreon\Internal\Utils\Datetime;

class DatetimeTest extends \PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $this->assertEquals('2012-12-23 22:47:56', Datetime::format(1356299276));
    }

    public function testHumanReadable()
    {
        /* Diff in seconds */
        $diff = 47492387;
        $this->assertEquals(
            '1 year 6 months 4 days 16 hours 19 min 47 sec',
            Datetime::humanReadable(47492387)
        );
        $this->assertEquals(
            '1 year 6 months',
            Datetime::humanReadable(47492387, Datetime::PRECISION_FORMAT, 2)
        );
        $this->assertEquals(
            '1 year 6 months 4 days',
            Datetime::humanReadable(47492387, Datetime::PRECISION_CHAR, 25)
        );
        $this->assertEquals(
            '1 year',
            Datetime::humanReadable(47492387, Datetime::PRECISION_CHAR, 2)
        );
    }
}
