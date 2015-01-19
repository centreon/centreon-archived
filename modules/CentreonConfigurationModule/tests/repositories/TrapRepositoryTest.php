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

namespace Test\CentreonConfiguration\Repository;

require_once 'modules/CentreonConfigurationModule/tests/repositories/RepositoryTestCase.php';

use \Test\CentreonConfiguration\Repository\RepositoryTestCase;
use CentreonConfiguration\Repository\TrapRepository;

class TrapRepositoryTest extends RepositoryTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';
    protected $objectName = 'trap';
    protected $objectClass = '\CentreonConfiguration\Models\Trap';
    protected $repository = '\CentreonConfiguration\Repository\TrapRepository';

    public function setUp()
    {
        parent::setUp();
    }

    public function testGetFormListWithEmptySearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => 'linkDown'),
            array('id' => 2, 'text' => 'linkUp'),
            array('id' => 3, 'text' => 'warmStart'),
            array('id' => 4, 'text' => 'coldStart')
        );
        $this->assertEquals($expectedResult, $rep::getFormList(''));
    }

    public function testGetFormListWithSearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 3, 'text' => 'warmStart')
        );
        $this->assertEquals($expectedResult, $rep::getFormList('warm'));
    }

    public function testGetFormListWithSearchStringWithNoResult()
    {
        $rep = $this->repository;
        $expectedResult = array();
        $this->assertEquals($expectedResult, $rep::getFormList('idontexist'));
    }

    public function testCreate()
    {
        $rep = $this->repository;
        $expectedResult = array();
        $rep::create(
            array(
                "traps_name" => "test",
                "traps_oid" => ".0.0.0.0.0.0.0.0.0.1",
                "traps_args" => "test $1",
                "traps_status" => "1",
                "manufacturer_id" => "1",
                "traps_comments" => "Test for traps",
                'organization_id' => 1
            )
        );
        $this->tableEqualsXml(
            'cfg_traps',
            dirname(__DIR__) . '/data/traps.insert.xml'
        );
    }

    public function testUpdate()
    {
        $rep = $this->repository;
        $newData = array(
            "object_id" => 1,
            "traps_name" => "test update",
            "traps_oid" => ".1.0.0.0.0.0.0.0.0.1",
            "traps_args" => "test $3",
            "traps_status" => "0",
            "manufacturer_id" => "1",
            "traps_comments" => "Test for traps"
        );
        $rep::update($newData);
        $this->tableEqualsXml(
            'cfg_traps',
            dirname(__DIR__) . '/data/traps.update.xml'
        );
    }

    public function testDelete()
    {
        $rep = $this->repository;
        $rep::delete(array(3));
        $this->tableEqualsXml(
            'cfg_traps',
            dirname(__DIR__) . '/data/traps.delete.xml'
        );
    }

    public function testDuplicate()
    {
        $rep = $this->repository;
        $rep::duplicate(
            array(1 => 1, 2 => 2)
        );
        $this->tableEqualsXml(
            'cfg_traps',
            dirname(__DIR__) . '/data/traps.duplicate-2.xml'
        );
    }
}
