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

namespace Centreon\Tests\Infrastructure\CentreonLegacyDB;

use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Infrastructure\CentreonLegacyDB\EntityPersister;
use Centreon\Infrastructure\CentreonLegacyDB\Mapping\ClassMetadata;
use Centreon\Test\Mock\CentreonDB;
use Centreon\Test\Mock\CentreonDBManagerService;
use Centreon\Tests\Resources\Mock;
use Centreon\Tests\Resources\CheckPoint;
use Centreon\Test\Traits\TestCaseExtensionTrait;

class ServiceEntityRepositoryTest extends TestCase
{
    use TestCaseExtensionTrait;

    public function setUp(): void
    {
        $this->db = new CentreonDB;
        $this->manager = new CentreonDBManagerService;
        $this->repository = new Mock\RepositoryMock($this->db, $this->manager);
    }

    public function testEntityClass()
    {
        $this->assertEquals(
            'Centreon\\Infrastructure\\CentreonLegacyDB\\ServiceEntity',
            ServiceEntityRepository::entityClass()
        );
    }

    public function testGetEntityPersister()
    {
        $result = $this->repository->getEntityPersister();
        $classMetadata = $this->getProtectedProperty($result, 'classMetadata');

        $this->assertInstanceOf(EntityPersister::class, $result);
        $this->assertEquals(Mock\EntityMock::class, $this->getProtectedProperty($result, 'entityClassName'));
        $this->assertInstanceOf(ClassMetadata::class, $classMetadata);
        $this->assertEquals($this->repository->getClassMetadata()->getTableName(), $classMetadata->getTableName());
    }

    public function testUpdateRelationData()
    {
        $list = [1, 2];
        $id = 7;
        $tableName = $this->repository->getClassMetadata()->getTableName();
        $columnA = 'id_a';
        $columnB = 'id_b';
        $checkPoint = (new CheckPoint)
            ->add('select')
            ->add('delete')
            ->add('insert');

        $this->db
            ->addResultSet(
                "SELECT `id_b` FROM `mock_table` WHERE `id_a` = :id_a LIMIT 0, 5000",
                [
                    [
                        $columnA => '1',
                        $columnB => '10',
                    ],
                    [
                        $columnA => '4',
                        $columnB => '10',
                    ],
                ],
                null,
                function ($params) use ($id, $columnA, $checkPoint) {
                    $checkPoint->mark('select');

                    $this->assertEquals([
                        ":{$columnA}" => $id,
                    ], $params);
                }
            )
            ->addResultSet(
                "DELETE FROM `mock_table` WHERE `id_a` = :id_a AND `id_b` = :id_b",
                [],
                null,
                function ($params) use ($id, $columnA, $columnB, $checkPoint) {
                    $checkPoint->mark('delete');

                    $this->assertEquals([
                        ":{$columnA}" => $id,
                        ":{$columnB}" => '10',
                    ], $params);
                }
            )
            ->addResultSet(
                "INSERT INTO `mock_table` (`id_a`, `id_b`)  VALUES (:id_a, :id_b)",
                [],
                null,
                function ($params) use ($id, $columnA, $columnB, $checkPoint) {
                    $checkPoint->mark('insert');

                    $this->assertContains($params, [
                        [
                            ":{$columnA}" => $id,
                            ":{$columnB}" => 1,
                        ],
                        [
                            ":{$columnA}" => $id,
                            ":{$columnB}" => 2,
                        ],
                    ]);
                }
            );

        $result = $this->invokeMethod($this->repository, 'updateRelationData', [
            $list,
            $id,
            $tableName,
            $columnA,
            $columnB,
        ]);

        $checkPoint->assert($this);
    }
}
