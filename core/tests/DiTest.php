<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */


namespace Test\Centreon;

use Centreon\Internal\Di;

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
        $di->set(
            'testClosure',
            function () {
                return 'String';
            }
        );
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
