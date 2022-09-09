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

use Centreon\Domain\Repository\AclGroupRepository;
use Centreon\Domain\Entity\AclGroup;
use PHPUnit\Framework\TestCase;
use Centreon\Tests\Resources\Traits;
use Centreon\Test\Mock\CentreonDB;

/**
 * @group Centreon
 * @group ORM-repository
 */
class AclGroupRepositoryTest extends TestCase
{
    use Traits\CheckListOfIdsTrait;
    use Traits\PaginationListTrait;

    /**
     * @var array
     */
    protected $datasets = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->db = new CentreonDB();
        $this->repository = new AclGroupRepository($this->db);
        $tableName = $this->repository->getClassMetadata()->getTableName();

        $this->datasets = [
            [
                'query' => "SELECT SQL_CALC_FOUND_ROWS t.* FROM `" . $tableName . "` AS `t` "
                . "ORDER BY t.acl_group_name ASC",
                'data' => [
                    [
                        'id' => '1',
                        'name' => 'test name 1',
                        'alias' => 'test alias 1',
                        'changed' => '1',
                        'activate' => '0',
                    ],
                ],
            ],
            [
                'query' => "SELECT SQL_CALC_FOUND_ROWS t.* FROM `" . $tableName . "` AS `t` "
                . "WHERE t.acl_group_name LIKE :search AND t.acl_group_id IN (:id0) "
                . "ORDER BY t.acl_group_name ASC LIMIT :limit OFFSET :offset",
                'data' => [
                    [
                        'id' => '1',
                        'name' => 'test name 1',
                        'alias' => 'test alias 1',
                        'changed' => '1',
                        'activate' => '0',
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
            $this->db->addResultSet($dataset['query'], $dataset['data']);
            unset($dataset);
        }
    }

    /**
     * Test the method entityClass
     */
    public function testEntityClass()
    {
        $this->assertEquals(AclGroup::class, AclGroupRepository::entityClass());
    }

    /**
     * Test the method checkListOfIds
     */
    public function testCheckListOfIds()
    {
        $this->checkListOfIdsTrait(
            AclGroupRepository::class,
            'checkListOfIds'
        );
    }

    /**
     * Test the method getPaginationList
     */
    public function testGetPaginationList()
    {
        $this->getPaginationListTrait($this->datasets[0]['data'][0]);
    }

    /**
     * Test the method getPaginationList with a different arguments
     */
    public function testGetPaginationListWithArguments()
    {
        $this->getPaginationListTrait(
            $this->datasets[1]['data'][0],
            [
                'search' => 'name',
                'ids' => ['ids'],
            ],
            1,
            0
        );
    }

    /**
     * Test the method getPaginationTotal
     */
    public function testGetPaginationListTotal()
    {
        $this->getPaginationListTotalTrait(
            $this->datasets[2]['data'][0]['number']
        );
    }
}
