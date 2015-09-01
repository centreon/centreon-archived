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
