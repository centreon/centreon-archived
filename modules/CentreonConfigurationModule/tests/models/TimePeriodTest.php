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
 */

namespace Test\CentreonConfiguration\Models;

use \Test\Centreon\DbTestCase;
use \CentreonConfiguration\Models\Timeperiod;

class TimeperiodTest extends DbTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $tpInsert = array(
            'tp_name' => 'test_name',
            'tp_alias' => 'test_alias',
            'tp_monday' => '09:00-18:00',
            'tp_tuesday' => '09:00-18:00',
            'tp_wednesday' => '09:00-18:00',
            'tp_thursday' => '09:00-18:00',
            'tp_friday' => '09:00-17:00',
        );
        Timeperiod::insert($tpInsert);
        $this->tableEqualsXml(
            'timeperiod',
            dirname(__DIR__) . '/data/timeperiod.insert.xml'
        );
    }

    public function testInsertDuplicateKey()
    {
        $tpInsert = array(
            'tp_name' => 'test_name',
            'tp_alias' => 'test_alias',
            'tp_monday' => '09:00-18:00',
            'tp_tuesday' => '09:00-18:00',
            'tp_wednesday' => '09:00-18:00',
            'tp_thursday' => '09:00-18:00',
            'tp_friday' => '09:00-17:00',
        );
        Timeperiod::insert($tpInsert);
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Timeperiod::insert($tpInsert);
    }

    public function testDelete()
    {
        Timeperiod::delete(1);
        $this->tableEqualsXml(
            'timeperiod',
            dirname(__DIR__) . '/data/timeperiod.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            'Object not in database.'
        );
        Timeperiod::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'tp_alias' => 'new_alias'
        );
        Timeperiod::update(1, $newInfo);
        $this->tableEqualsXml(
            'timeperiod',
            dirname(__DIR__) . '/data/timeperiod.update.xml'
        );
    }

    public function testUpdateDuplicateKey()
    {
        $newInfo = array(
            'tp_name' => '24x7'
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Timeperiod::update(2, $newInfo);
    }

    public function testUpdateUnknownId()
    {
      $newInfo = array(
            'tp_name' => '24x7'
        );
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            'Object not in database.'
        );
        Timeperiod::update(9999, $newInfo);
    }
/*
    public function testDuplicateItemOnce()
    {
        Timeperiod::duplicate(1);
        $this->tableEqualsXml(
            'timeperiod',
            ''
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        Timeperiod::duplicate(1, 2);
    }

    public function testDuplicateUnknownId()
    {

    }
 */
}
