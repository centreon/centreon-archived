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

namespace CentreonUser\Tests\Domain\Repository;

use PHPUnit\Framework\TestCase;
use Centreon\Test\Mock\CentreonDB;
use CentreonUser\Domain\Entity\Timeperiod;
use CentreonUser\Domain\Repository\TimeperiodRepository;
use Centreon\Tests\Resources\Traits;

/**
 * @group CentreonUser
 * @group ORM-repository
 */
class TimeperiodRepositoryTest extends TestCase
{
    use Traits\CheckListOfIdsTrait;
    use Traits\PaginationListTrait;

    /**
     * @var array<int, array<string, array<int, array<string, int|string>>|string>>
     */
    protected $datasets = [];

    /**
     * @var CentreonDB
     */
    private $db;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->db = new CentreonDB();
        $this->repository = new TimeperiodRepository($this->db);
        $tableName = $this->repository->getClassMetadata()->getTableName();

        $this->datasets = [
            [
                'query' => "SELECT SQL_CALC_FOUND_ROWS `tp_id`, `tp_name`, `tp_alias` "
                . "FROM `" . $tableName . "`",
                'data' => [
                    [
                        'tp_id' => '1',
                        'tp_name' => 'name1',
                        'tp_alias' => 'alias1',
                    ],
                ],
            ],
            [
                'query' => "SELECT SQL_CALC_FOUND_ROWS `tp_id`, `tp_name`, `tp_alias` "
                . "FROM `" . $tableName . "` WHERE (`tp_name` LIKE :search OR `tp_alias` LIKE :search) "
                . "AND `tp_id` IN (:id0) ORDER BY `tp_name` ASC LIMIT :limit OFFSET :offset",
                'data' => [
                    [
                        'tp_id' => '1',
                        'tp_name' => 'name1',
                        'tp_alias' => 'alias1',
                    ],
                ],
            ],
            [
                'query' => "SELECT FOUND_ROWS() AS number",
                'data' => [
                    [
                        'number' => 10,
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
     * @return void
     */
    public function testEntityClass()
    {
        $this->assertEquals(Timeperiod::class, TimeperiodRepository::entityClass());
    }

    /**
     * Test the method checkListOfIds
     * @return void
     */
    public function testCheckListOfIds()
    {
        $this->checkListOfIdsTrait(
            TimeperiodRepository::class,
            'checkListOfIds'
        );
    }

    /**
     * Test the method getPaginationList
     * @return void
     */
    public function testGetPaginationList()
    {
        $this->getPaginationListTrait($this->datasets[0]['data'][0]);
    }

    /**
     * Test the method getPaginationList with different set of arguments
     * @return void
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
            0,
            [
                'field' => 'tp_name',
                'order' => 'ASC'
            ]
        );
    }

    /**
     * Test the method getPaginationListTotal
     * @return void
     */
    public function testGetPaginationListTotal()
    {
        $this->getPaginationListTotalTrait(
            $this->datasets[2]['data'][0]['number']
        );
    }
}
