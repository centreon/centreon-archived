<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

namespace Centreon\Tests\Domain\Repository;

use Centreon\Domain\Entity\ContactGroup;
use Centreon\Domain\Repository\ContactGroupRepository;
use PHPUnit\Framework\TestCase;
use Centreon\Test\Mock\CentreonDB;
use Centreon\Tests\Resources\Traits;

/**
 * @group Centreon
 * @group ORM-repository
 */
class ContactGroupRepositoryTest extends TestCase
{
    use Traits\CheckListOfIdsTrait;

    /**
     * @var array
     */
    protected $datasets = [];

    /**
     * @var \Centreon\Domain\Repository\ContactGroupRepository
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $db = new CentreonDB();
        $this->datasets = [
            [
                'query' => "SELECT SQL_CALC_FOUND_ROWS * "
                    . "FROM `contactgroup`",
                'data' => [
                    [
                        'cg_id' => '1',
                        'cg_name' => 'test name 1',
                    ],
                ],
            ],
            [
                'query' => "SELECT SQL_CALC_FOUND_ROWS * FROM `contactgroup` "
                    . "WHERE `cg_name` LIKE :search AND `cg_id` IN (:id0) "
                    . "ORDER BY `cg_name` ASC LIMIT :limit OFFSET :offset",
                'data' => [
                    [
                        'cg_id' => '1',
                        'cg_name' => 'test name 1',
                    ],
                ],
            ],
            [
                'query' => "SELECT FOUND_ROWS() AS number",
                'data' => [
                    [
                        'number' => '10',
                    ],
                ],
            ],
        ];
        foreach ($this->datasets as $dataset) {
            $db->addResultSet($dataset['query'], $dataset['data']);
            unset($dataset);
        }
        $this->repository = new ContactGroupRepository($db);
    }

    /**
     * Test the method checkListOfIds
     */
    public function testCheckListOfIds()
    {
        $this->checkListOfIdsTrait(
            ContactGroupRepository::class,
            'checkListOfIds',
            ContactGroup::TABLE,
            ContactGroup::ENTITY_IDENTIFICATOR_COLUMN
        );
    }

    /**
     * Test the method getPaginationList
     */
    public function testGetPaginationList()
    {
        $result = $this->repository->getPaginationList();
        $data = $this->datasets[0]['data'][0];
        $entity = new ContactGroup();
        $entity->setCgId($data['cg_id']);
        $entity->setCgName($data['cg_name']);
        $this->assertEquals([$entity], $result);
    }

    /**
     * Test the method getPaginationList with a different set of arguments
     */
    public function testGetPaginationListWithArguments()
    {
        $filters = [
            'search' => 'name',
            'ids' => ['ids'],
        ];
        $limit = 1;
        $offset = 0;
        $result = $this->repository
            ->getPaginationList($filters, $limit, $offset, ['field' => 'cg_name', 'order' => 'ASC']);
        $data = $this->datasets[1]['data'][0];
        $entity = new ContactGroup();
        $entity->setCgId($data['cg_id']);
        $entity->setCgName($data['cg_name']);
        $this->assertEquals([$entity], $result);
    }

    /**
     * Test the method getPaginationTotal
     */
    public function testGetPaginationListTotal()
    {
        $total = (int)$this->datasets[2]['data'][0]['number'];
        $result = $this->repository->getPaginationListTotal();
        $this->assertEquals($total, $result);
    }
}
