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
use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use Centreon\Infrastructure\Service\Exception\NotFoundException;
use Centreon\Test\Mock\CentreonDB;
use Centreon\Test\Mock\CentreonDBStatement;
use Centreon\Test\Mock\CentreonDBManagerService;
use Centreon\Tests\Resources\Mock;
use Centreon\Tests\Resources\CheckPoint;
use Centreon\Test\Traits\TestCaseExtensionTrait;

class CentreonDBAdapterTest extends TestCase
{
    use TestCaseExtensionTrait;

    /**
     * @var CentreonDB
     */
    private $db;

    /**
     * @var CentreonDBManagerService
     */
    private $manager;

    /**
     * @var CentreonDBAdapter
     */
    private $dbAdapter;

    public function setUp(): void
    {
        $this->db = new CentreonDB();
        $this->manager = new CentreonDBManagerService();
        $this->dbAdapter = new CentreonDBAdapter($this->db, $this->manager);
    }

    public function testGetRepository()
    {
        $this->assertInstanceOf(
            Mock\RepositoryMock::class,
            $this->dbAdapter->getRepository(Mock\RepositoryMock::class)
        );
    }

    public function testGetRepositoryWithException()
    {
        $this->expectException(NotFoundException::class);

        $this->dbAdapter->getRepository(\stdClass::class);
    }

    public function testGetCentreonDBInstance()
    {
        $this->assertInstanceOf(
            CentreonDB::class,
            $this->dbAdapter->getCentreonDBInstance()
        );
    }

    public function testQuery(): void
    {
        $checkPoint = new CheckPoint();
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
        $checkPoint->assert($this);
    }

    public function testQueryWithPrepareException(): void
    {
        $db = $this->createMock(CentreonDB::class);
        $db->method('prepare')
            ->willReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error at preparing the query.');

        (new CentreonDBAdapter($db, $this->manager))
            ->query('SELECT 1');
    }

    public function testQueryWithExceptionInExecution(): void
    {
        $db = $this->createMock(CentreonDB::class);
        $db
            ->method('prepare')
            ->will($this->returnCallback(function () {
                $stmt = $this->createMock(CentreonDBStatement::class);
                $stmt
                    ->method('execute')
                    ->will($this->throwException(new \Exception('test exception')));

                return $stmt;
            }));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Query failed. test exception');

        (new CentreonDBAdapter($db, $this->manager))
            ->query('SELECT 1');
    }

    public function testQueryWithoutSelectQuery(): void
    {
        $errorInfo = ['test info for DB error'];

        $db = $this->createMock(CentreonDB::class);
        $db
            ->method('prepare')
            ->will($this->returnCallback(function () use ($errorInfo) {
                $stmt = $this->createMock(CentreonDBStatement::class);
                $stmt
                    ->method('execute')
                    ->willReturn(false);
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

    public function testInsert(): void
    {
        $checkPoint = new CheckPoint();
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

        $checkPoint->assert($this);
    }

    public function testInsertWithoutFields(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The argument `fields` can't be empty");

        $this->dbAdapter
            ->insert('some_table', []);
    }

    public function testInsertWithExceptionInExecution(): void
    {
        $db = $this->createMock(CentreonDB::class);
        $db
            ->method('prepare')
            ->will($this->returnCallback(function () {
                $stmt = $this->createMock(CentreonDBStatement::class);
                $stmt
                    ->method('execute')
                    ->will($this->throwException(new \Exception('test exception')));

                return $stmt;
            }));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Query failed. test exception');

        (new CentreonDBAdapter($db, $this->manager))
            ->insert('some_table', [
                'name' => 'test name',
            ]);
    }

    public function testUpdate(): void
    {
        $checkPoint = new CheckPoint();
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

        $checkPoint->assert($this);
    }

    public function testUpdateWithExceptionInExecution(): void
    {
        $id = 1;

        $db = $this->createMock(CentreonDB::class);
        $db
            ->method('prepare')
            ->will($this->returnCallback(function () {
                $stmt = $this->createMock(CentreonDBStatement::class);
                $stmt
                    ->method('execute')
                    ->will($this->throwException(new \Exception('test exception')));

                return $stmt;
            }));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Query failed. test exception');

        (new CentreonDBAdapter($db, $this->manager))
            ->update('some_table', [
                'name' => 'test name',
            ], $id);
    }

    public function testFails(): void
    {
        $dbAdapter = new CentreonDBAdapter($this->db, $this->manager);
        $this->setProtectedProperty($dbAdapter, 'error', true);

        $this->assertTrue($dbAdapter->fails());
        $this->assertFalse($dbAdapter->passes());
    }

    public function testErrorInfo(): void
    {
        $msg = 'test msg';
        $dbAdapter = new CentreonDBAdapter($this->db, $this->manager);
        $this->setProtectedProperty($dbAdapter, 'errorInfo', $msg);

        $this->assertEquals($msg, $dbAdapter->errorInfo());
    }

    public function testTransaction(): void
    {
        $checkPoint = new CheckPoint();
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

                return true;
            }));

        $dbAdapter = new CentreonDBAdapter($db, $this->manager);

        $dbAdapter->beginTransaction();
        $dbAdapter->commit();
        $dbAdapter->rollBack();

        $checkPoint->assert($this);
    }
}
