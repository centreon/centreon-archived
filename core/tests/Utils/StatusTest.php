<?php
/*
 * Copyright 2005-2014 CENTREON
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

use Centreon\Internal\Utils\Status;

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
