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
use \CentreonConfiguration\Repository\CommandRepository;

class CommandRepositoryTest extends RepositoryTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';
    protected $objectName = 'command';
    protected $objectClass = '\CentreonConfiguration\Models\Command';
    protected $repository = '\CentreonConfiguration\Repository\CommandRepository';

    public function setUp()
    {
        parent::setUp();
    }

    public function testGetFormListWithEmptySearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => 'Test notif'),
            array('id' => 2, 'text' => 'Test check'),
            array('id' => 3, 'text' => 'Test connector')
        );
        $this->assertEquals($expectedResult, $rep::getFormList(''));
    }

    public function testGetFormListWithSearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 3, 'text' => 'Test connector')
        );
        $this->assertEquals($expectedResult, $rep::getFormList('connector'));
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
                'command_name' => "My check",
                'command_line' => '$USER1$/bin/my_check -w $ARGV1$',
                'command_example' => '$USER1$/bin/my_check -w 90',
                'command_type' => 2,
                'enable_shell' => 0,
                'command_comment' => "My check command"
            )
        );
        $this->tableEqualsXml(
            'command',
            dirname(__DIR__) . '/data/command.insert.xml'
        );
    }

    public function testUpdate()
    {
        $rep = $this->repository;
        $newData = array(
            'command_id' => 2,
            'command_comment' => 'Check ping',
            'enable_shell' => 1 
        );
        $rep::update($newData);
        $this->tableEqualsXml(
            'command',
            dirname(__DIR__) . '/data/command.update.xml'
        );
    }

    public function testDelete()
    {
        $rep = $this->repository;
        $rep::delete(array(2));
        $this->tableEqualsXml(
            'command',
            dirname(__DIR__) . '/data/command.delete.xml'
        );
    }

    public function testDuplicate()
    {
        $rep = $this->repository;
        $rep::duplicate(
            array(2 => 2)
        );
        $this->tableEqualsXml(
            'command',
            dirname(__DIR__) . '/data/command.duplicate-2.xml'
        );
    }

    public function testGetSimpleRelation()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => 'Template Host')
        );
        $this->assertEquals(
            $expectedResult,
            $rep::getSimplRelation(
                'host_id',
                '\CentreonConfiguration\Models\Hosttemplate',
                2,
                true
            )
        );
    }
}
