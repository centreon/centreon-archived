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

namespace CentreonCommand\Tests\Domain\Repository;

use PHPUnit\Framework\TestCase;
use Centreon\Test\Mock\CentreonDB;
use CentreonCommand\Domain\Entity\Command;
use CentreonCommand\Domain\Repository\CommandRepository;

/**
 * @group CentreonCommand
 * @group ORM-repository
 */
class CommandRepositoryTest extends TestCase
{

    /**
     * @var array<int, array<string, array<int, array<string, int|string>>|string>>
     */
    protected $datasets = [];

    /**
     * @var \CentreonCommand\Domain\Repository\CommandRepository
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $db = new CentreonDB();
        $this->datasets = [
            [
                'query' => "SELECT SQL_CALC_FOUND_ROWS `command_id` AS `id`, `command_name` AS `name` "
                . "FROM `" . Command::TABLE . "` WHERE `command_activate` = :active "
                . "ORDER BY `command_name` ASC",
                'data' => [
                    [
                        'id' => '1',
                        'name' => 'name1',
                    ],
                ],
            ],
            [
                'query' => "SELECT SQL_CALC_FOUND_ROWS `command_id` AS `id`, `command_name` AS `name` "
                . "FROM `" . Command::TABLE . "` WHERE `command_activate` = :active "
                . "AND `command_name` LIKE :search AND `command_id` IN (:id0) AND `command_type` LIKE :type "
                . "ORDER BY `command_name` ASC LIMIT :limit OFFSET :offset",
                'data' => [
                    [
                        'id' => '1',
                        'name' => 'name1',
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
            $db->addResultSet($dataset['query'], $dataset['data']);
            unset($dataset);
        }

        $this->repository = new CommandRepository($db);
    }

    /**
     * Test the method getPaginationList
     *
     * @covers \CentreonCommand\Domain\Repository\CommandRepository::getPaginationList
     */
    public function testGetPaginationList(): void
    {
        $result = $this->repository->getPaginationList();
        $command = new Command();
        if (array_key_exists('id', $result[0]) && array_key_exists('name', $result[0])) {
            $command->setId($result[0]['id']);
            $command->setName($result[0]['name']);
        }

        $data = $this->datasets[0]['data'][0];
        $expectedCommand = new Command();
        $expectedCommand->setId($data['id']);
        $expectedCommand->setName($data['name']);

        $this->assertEquals($expectedCommand, $command);
    }

    /**
     * Test the method getPaginationList with a different set of arguments
     *
     * @covers \CentreonCommand\Domain\Repository\CommandRepository::getPaginationList
     */
    public function testGetPaginationListWithArguments(): void
    {
        $filters = [
            'search' => 'name',
            'ids' => ['ids'],
            'type' => 3,
        ];
        $limit = 1;
        $offset = 0;

        $result = $this->repository->getPaginationList($filters, $limit, $offset);
        $command = new Command();
        if (array_key_exists('id', $result[0]) && array_key_exists('name', $result[0])) {
            $command->setId($result[0]['id']);
            $command->setName($result[0]['name']);
        }

        $data = $this->datasets[1]['data'][0];
        $expectedCommand = new Command();
        $expectedCommand->setId($data['id']);
        $expectedCommand->setName($data['name']);

        $this->assertEquals($expectedCommand, $command);
    }

    /**
     * Test the method getPaginationTotal
     *
     * @covers \CentreonCommand\Domain\Repository\CommandRepository::getPaginationListTotal
     */
    public function testGetPaginationListTotal(): void
    {
        $total = $this->datasets[2]['data'][0]['number'];
        $result = $this->repository->getPaginationListTotal();

        $this->assertEquals($total, $result);
    }
}
