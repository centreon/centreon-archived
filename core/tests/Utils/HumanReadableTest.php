<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */
namespace Test\Centreon\Utils;

use Centreon\Internal\Utils\HumanReadable;

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
