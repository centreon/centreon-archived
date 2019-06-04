<?php

namespace Centreon\Tests\Infrastructure\CentreonLegacyDB;

use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use PDO;
use PDOStatement;

class StatementCollectorTest extends TestCase
{

    public function testAddColumn()
    {
        $value = 987;
        $key = 'key';
        $dataType = PDO::PARAM_INT;

        $collector = new StatementCollector;

        $collector->addColumn($key, $value, $dataType);

        $this->assertAttributeEquals([
            $key => [
                'value' => $value,
                'data_type' => $dataType,
            ]
            ], 'columns', $collector);
    }

    public function testAddValue()
    {
        $value = true;
        $key = 'key';
        $dataType = PDO::PARAM_BOOL;

        $collector = new StatementCollector;

        $collector->addValue($key, $value, $dataType);

        $this->assertAttributeEquals([
            $key => [
                'value' => $value,
                'data_type' => $dataType,
            ]
            ], 'values', $collector);
    }

    public function testAddParam()
    {
        $value = null;
        $key = 'key';
        $dataType = PDO::PARAM_STR;

        $collector = new StatementCollector;

        $collector->addParam($key, $value);

        $this->assertAttributeEquals([
            $key => [
                'value' => $value,
                'data_type' => $dataType,
            ]
            ], 'params', $collector);
    }

    public function testBind()
    {
        $value = '...';
        $key = 'key';
        $dataType = PDO::PARAM_STR;

        $callback = function ($_key, $_value, $_dataType) use ($key, $value, $dataType) {
            $this->assertEquals($key, $_key);
            $this->assertEquals($value, $_value);
            $this->assertEquals($dataType, $_dataType);
        };

        $collector = new StatementCollector;
        $collector->addColumn($key, $value, $dataType);
        $collector->addValue($key, $value, $dataType);
        $collector->addParam($key, $value, $dataType);

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('bindColumn')
            ->will($this->returnCallback($callback));
        $stmt->method('bindValue')
            ->will($this->returnCallback($callback));
        $stmt->method('bindParam')
            ->will($this->returnCallback($callback));

        $collector->bind($stmt);
    }
}
