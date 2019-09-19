<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace CentreonUser\Tests\Domain\Repository;

use PHPUnit\Framework\TestCase;
use Centreon\Test\Mock\CentreonDB;
use CentreonUser\Domain\Entity\Timeperiod;
use CentreonUser\Domain\Repository\TimeperiodRepository;
use Centreon\Tests\Resource\Traits;

/**
 * @group CentreonUser
 * @group ORM-repository
 */
class TimeperiodRepositoryTest extends TestCase
{
    use Traits\CheckListOfIdsTrait,
        Traits\PaginationListTrait;

    protected $datasets = [];

    protected function setUp()
    {
        $this->db = new CentreonDB;
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

    public function testEntityClass()
    {
        $this->assertEquals(Timeperiod::class, TimeperiodRepository::entityClass());
    }

    public function testCheckListOfIds()
    {
        $this->checkListOfIdsTrait(
            TimeperiodRepository::class,
            'checkListOfIds'
        );
    }

    public function testGetPaginationList()
    {
        $this->getPaginationListTrait($this->datasets[0]['data'][0]);
    }

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

    public function testGetPaginationListTotal()
    {
        $this->getPaginationListTotalTrait(
            $this->datasets[2]['data'][0]['number']
        );
    }
}
