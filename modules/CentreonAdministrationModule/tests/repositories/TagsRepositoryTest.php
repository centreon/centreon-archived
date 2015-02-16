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
 */

namespace Test\CentreonAdministration\Repository;


use CentreonAdministration\Repository\TagsRepository;
use CentreonAdministration\Internal\User;
use Test\Centreon\DbTestCase;

class TagsRepositoryTest extends DbTestCase
{
    protected $dataPath = '/modules/CentreonAdministrationModule/tests/data/json/';

    public function setUp()
    {
        parent::setUp();

        if (!isset($_SESSION)) {
            $_SESSION = array();
        }
        $_SESSION['user'] = new User(1);
    }

    public function testGetListResource()
    {
        $this->assertEquals(
            array(
                'host',
                'service',
                'hostgroup',
                'servicegroup',
                'ba'
            ),
            TagsRepository::getListResource()
        );
    }

    public function testAdd()
    {
        /* Test with a new tag */
        $this->assertEquals(3, TagsRepository::add('newTag', 'host', 1));
        /* Test with an exists tag */
        $this->assertEquals(1, TagsRepository::add('Tag1', 'host', 1));
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/tags.add.tags.xml'
        )->getTable('cfg_tags');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_tags',
            'SELECT * FROM cfg_tags'
        );
        $this->assertTablesEqual($dataset, $tableResult);
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/tags.add.tagshosts.xml'
        )->getTable('cfg_tags_hosts');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_tags_hosts',
            'SELECT * FROM cfg_tags_hosts'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testAddWithBadResourceType()
    {
        $this->setExpectedException(
            'Centreon\Internal\Exception',
            'This resource type does not support tags.'
        );
        TagsRepository::add('Tag1', 'badtype', 1);
    }

    public function testDelete()
    {
        TagsRepository::delete(2, 'host', 1);
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/tags.del.tagshosts.xml'
        )->getTable('cfg_tags_hosts');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_tags_hosts',
            'SELECT * FROM cfg_tags_hosts'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testGetList()
    {
        $this->assertEquals(
            array(2 => 'Tag2'),
            TagsRepository::getList('host', 1)
        );
    }

    public function testGetTagId()
    {
        $this->assertEquals(1, TagsRepository::getTagId('Tag1'));
    }
}
