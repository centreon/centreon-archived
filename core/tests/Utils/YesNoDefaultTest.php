<?php
namespace Test\Centreon\Utils;

use \Centreon\Internal\Utils\YesNoDefault;

class YesNoDefaultTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $this->assertEquals('No', YesNoDefault::toString(0));
        $this->assertEquals('Yes', YesNoDefault::toString(1));
        $this->assertEquals('', YesNoDefault::toString(42));
    }
}
