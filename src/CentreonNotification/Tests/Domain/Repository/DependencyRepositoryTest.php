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

namespace CentreonNotification\Tests\Domain\Repository;

use PHPUnit\Framework\TestCase;
use Centreon\Test\Mock\CentreonDB;
use CentreonNotification\Domain\Repository\DependencyRepository;

/**
 * @group Centreon
 * @group ORM-repository
 */
class DependencyRepositoryTest extends TestCase
{

    /**
     * @var \Centreon\Test\Mock\CentreonDB
     */
    protected $db;

    /**
     * @var \CentreonNotification\Domain\Repository\DependencyRepository
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->db = new CentreonDB();
        $this->repository = new DependencyRepository($this->db);
    }

    /**
     * Test the method removeById
     *
     * @covers \CentreonNotification\Domain\Repository\DependencyRepository::removeById
     */
    public function testRemoveById(): void
    {
        $this->db->addResultSet(
            "DELETE FROM `dependency` WHERE dep_id = :id",
            [],
            null,
            function ($values) {
                $this->assertEquals([':id' => 1], $values);
            }
        );

        $this->repository->removeById(1);
    }
}
