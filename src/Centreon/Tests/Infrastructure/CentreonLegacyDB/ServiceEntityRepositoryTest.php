<?php

namespace Centreon\Tests\Infrastructure\CentreonLegacyDB;

use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Infrastructure\CentreonLegacyDB\EntityPersister;
use Centreon\Infrastructure\CentreonLegacyDB\Mapping\ClassMetadata;
use Centreon\Test\Mock\CentreonDB;
use Centreon\Test\Mock\CentreonDBManagerService;
use Centreon\Tests\Resource\Mock;
use Centreon\Tests\Resource\CheckPoint;
use Centreon\Test\Traits\TestCaseExtensionTrait;

class ServiceEntityRepositoryTest extends TestCase
{
    use TestCaseExtensionTrait;

    public function setUp()
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
