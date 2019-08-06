<?php

namespace Centreon\Tests\Infrastructure\CentreonLegacyDB;

use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use Centreon\Infrastructure\CentreonLegacyDB\EntityPersister;
use Centreon\Infrastructure\CentreonLegacyDB\Mapping\ClassMetadata;
use Centreon\Test\Mock\CentreonDB;
use Centreon\Test\Mock\CentreonDBManagerService;
use Centreon\Tests\Resource\Mock;
use Centreon\Tests\Resource\CheckPoint;
use Centreon\Test\Traits\TestCaseExtensionTrait;

class CentreonDBAdapterTest extends TestCase
{

    use TestCaseExtensionTrait;

    public function setUp()
    {
        $this->db = new CentreonDB;
        $this->manager = new CentreonDBManagerService;
        $this->dbAdapter = new CentreonDBAdapter($this->db, $this->manager);
    }

    public function testGetRepository()
    {
        $this->assertInstanceOf(Mock\RepositoryMock::class,
            $this->dbAdapter->getRepository(Mock\RepositoryMock::class)
        );
    }

    /**
     * @expectedException \Centreon\Infrastructure\Service\Exception\NotFoundException
     */
    public function testGetRepositoryWithException()
    {
        $this->dbAdapter->getRepository(\stdClass::class);
    }

    public function testGetCentreonDBInstance()
    {
        $this->assertInstanceOf(CentreonDB::class,
            $this->dbAdapter->getCentreonDBInstance()
        );
    }

    public function testQuery()
    {
        $checkPoint = new CheckPoint($this);
        $checkPoint->add('select');

        $id = 1;
        $sql = 'SELECT * FROM some_table WHERE id = :id';
        $this->db
            ->addResultSet(
                $sql,
                [
                    [
                        'id' => (string)$id,
                    ],
                ],
                null,
                function ($params) use ($id, $checkPoint) {
                    $checkPoint->mark('select');

                    $this->assertEquals([
                        $id,
                    ], $params);
                }
            );

        $this->dbAdapter
            ->query(
                $sql,
                [
                    ':id' => $id,
                ]
            )
            ->results();

        $this->assertEquals(1, $this->dbAdapter->count());
        $checkPoint->assert();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Error at preparing the query.
     */
    public function testQueryWithPrepareException()
    {
        $db = $this->createMock(CentreonDB::class);
        $db
            ->method('prepare')
            ->willReturn(null);

        (new CentreonDBAdapter($db, $this->manager))
            ->query('SELECT 1');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Query failed. test exception
     */
    public function testQueryWithExceptionInExecution()
    {
        $db = $this->createMock(CentreonDB::class);
        $db
            ->method('prepare')
            ->will($this->returnCallback(function () {
                $stmt = $this->createMock(\PDOStatement::class);
                $stmt
                    ->method('execute')
                    ->will($this->throwException(new \Exception('test exception')));

                return $stmt;
            }));

        (new CentreonDBAdapter($db, $this->manager))
            ->query('SELECT 1');
    }

    public function testQueryWithoutSelectQuery()
    {
        $errorInfo = 'test info for DB error';

        $db = $this->createMock(CentreonDB::class);
        $db
            ->method('prepare')
            ->will($this->returnCallback(function () use ($errorInfo) {
                $stmt = $this->createMock(\PDOStatement::class);
                $stmt
                    ->method('execute')
                    ->willReturn(null);
                $stmt
                    ->method('errorInfo')
                    ->willReturn($errorInfo);

                return $stmt;
            }));

        $dbAdapter = new CentreonDBAdapter($db, $this->manager);
        $dbAdapter->query('UPDATE query');
        
        $this->assertTrue($dbAdapter->fails());
        $this->assertEquals($errorInfo, $dbAdapter->errorInfo());
    }

    public function testInsert()
    {
        $checkPoint = new CheckPoint($this);
        $checkPoint->add('insert');

        $name = 'test name';

        $this->db
            ->addResultSet(
                'INSERT INTO some_table (`name`) VALUES (:name)',
                [],
                null,
                function ($params) use ($name, $checkPoint) {
                    $checkPoint->mark('insert');

                    $this->assertEquals([
                        ':name' => $name,
                    ], $params);
                }
            );

        $this->dbAdapter
            ->insert('some_table', [
                'name' => $name,
            ]);

        $checkPoint->assert();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The argument `fields` can't be empty
     */
    public function testInsertWithoutFields()
    {
        $this->dbAdapter
            ->insert('some_table', []);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Query failed. test exception
     */
    public function testInsertWithExceptionInExecution()
    {
        $db = $this->createMock(CentreonDB::class);
        $db
            ->method('prepare')
            ->will($this->returnCallback(function () {
                $stmt = $this->createMock(\PDOStatement::class);
                $stmt
                    ->method('execute')
                    ->will($this->throwException(new \Exception('test exception')));

                return $stmt;
            }));

        (new CentreonDBAdapter($db, $this->manager))
            ->insert('some_table', [
                'name' => 'test name',
            ]);
    }

    public function testUpdate()
    {
        $checkPoint = new CheckPoint($this);
        $checkPoint->add('update');

        $id = 1;
        $name = 'test name';

        $this->db
            ->addResultSet(
                'UPDATE some_table SET name= :name WHERE id = :id',
                [],
                null,
                function ($params) use ($id, $name, $checkPoint) {
                    $checkPoint->mark('update');

                    $this->assertEquals([
                        ':id' => $id,
                        ':0' => [
                            'name',
                            $name,
                        ],
                    ], $params);
                }
            );

        $this->dbAdapter
            ->update('some_table', [
                'name' => $name,
            ], $id);

        $checkPoint->assert();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Query failed. test exception
     */
    public function testUpdateWithExceptionInExecution()
    {
        $id = 1;

        $db = $this->createMock(CentreonDB::class);
        $db
            ->method('prepare')
            ->will($this->returnCallback(function () {
                $stmt = $this->createMock(\PDOStatement::class);
                $stmt
                    ->method('execute')
                    ->will($this->throwException(new \Exception('test exception')));

                return $stmt;
            }));

        (new CentreonDBAdapter($db, $this->manager))
            ->update('some_table', [
                'name' => 'test name',
            ], $id);
    }

    public function testFails()
    {
        $dbAdapter = new CentreonDBAdapter($this->db, $this->manager);
        $this->setProtectedProperty($dbAdapter, 'error', true);

        $this->assertTrue($dbAdapter->fails());
        $this->assertFalse($dbAdapter->passes());
    }

    public function testErrorInfo()
    {
        $msg = 'test msg';
        $dbAdapter = new CentreonDBAdapter($this->db, $this->manager);
        $this->setProtectedProperty($dbAdapter, 'errorInfo', $msg);

        $this->assertEquals($msg, $dbAdapter->errorInfo());
    }

    public function testTransaction()
    {
        $checkPoint = new CheckPoint($this);
        $checkPoint->add('beginTransaction');
        $checkPoint->add('commit');
        $checkPoint->add('rollBack');

        $db = $this->createMock(CentreonDB::class);
        $db
            ->method('beginTransaction')
            ->will($this->returnCallback(function () use ($checkPoint) {
                $checkPoint->mark('beginTransaction');

                return true;
            }));
        $db
            ->method('commit')
            ->will($this->returnCallback(function () use ($checkPoint) {
                $checkPoint->mark('commit');

                return true;
            }));
        $db
            ->method('rollBack')
            ->will($this->returnCallback(function () use ($checkPoint) {
                $checkPoint->mark('rollBack');
            }));

        $dbAdapter = new CentreonDBAdapter($db, $this->manager);

        $dbAdapter->beginTransaction();
        $dbAdapter->commit();
        $dbAdapter->rollBack();

        $checkPoint->assert();
    }
}
