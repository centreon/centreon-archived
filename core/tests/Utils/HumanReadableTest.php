<?php
/*
 * Copyright 2005-2015 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
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
