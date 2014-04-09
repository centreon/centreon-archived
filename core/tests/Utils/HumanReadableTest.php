<?php
namespace Test\Centreon\Utils;

use \Centreon\Internal\Utils\HumanReadable;

class HumanReadableTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertArray()
    {
        $newUnit = '';
        /* Test bytes */
        $values = array(12200, 23440);
        $this->assertEquals(
            array(11.9140625, 22.890625),
            HumanReadable::convertArray($values, 'B', $newUnit)
        );
        $this->assertEquals('kB', $newUnit);
        /* Test with 2 decimal */
        $this->assertEquals(
            array(11.91, 22.89),
            HumanReadable::convertArray($values, 'B', $newUnit, 2)
        );
        /* Test octets */
        $this->assertEquals(
            array(11.9140625, 22.890625),
            HumanReadable::convertArray($values, 'o', $newUnit)
        );
        $this->assertEquals('ko', $newUnit);
        /* Test bits */
        $this->assertEquals(
            array(12.2, 23.44),
            HumanReadable::convertArray($values, 'b', $newUnit)
        );
        $this->assertEquals('kb', $newUnit);
        /* Test bad unit */
        $this->assertEquals(
            $values,
            HumanReadable::convertArray($values, 'toto', $newUnit)
        );
        /* Test with Zero has value */
        $this->assertEquals(
            null,
            HumanReadable::convertArray(array(0), 'b', $newUnit)
        );
    }

    public function testConvertArrayWithFactor()
    {
        $values = array(12200, 23440);
        $this->assertEquals(
            array(0.0122, 0.02344),
            HumanReadable::convertArrayWithFactor($values, 'b', 2)
        );
        /* Test with bad unit and not convert decimal */
        $this->assertEquals(
            $values,
            HumanReadable::convertArrayWithFactor($values, 'toto', 2)
        );
        /* Test with bad unit with convert decimal */
        $this->assertEquals(
            array(12.2, 23.5),
            HumanReadable::convertArrayWithFactor(array(12.2334, 23.4531), 'toto', 1, 1)
        );
        /* Test with some null value */
        $values = array(12200, 23440, null);
        $this->assertEquals(
            array(0.0122, 0.02344, null),
            HumanReadable::convertArrayWithFactor($values, 'b', 2)
        );
        $values = array(12.2334, 23.4531, null);
        $this->assertEquals(
            array(12.2, 23.5, null),
            HumanReadable::convertArrayWithFactor($values, 'toto', 1, 1)
        );
    }

    public function testGetFactor()
    {
        $value = array(1223123344);
        $this->assertEquals(3, HumanReadable::getFactor($value));
        $value = array(10000000);
        $this->assertEquals(2, HumanReadable::getFactor($value));
        $value = array(0);
        $this->assertEquals(false, HumanReadable::getFactor($value));
    }
}
