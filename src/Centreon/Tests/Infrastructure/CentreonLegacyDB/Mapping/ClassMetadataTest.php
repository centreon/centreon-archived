<?php

namespace Centreon\Tests\Infrastructure\CentreonLegacyDB\Mapping;

use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\CentreonLegacyDB\Mapping\ClassMetadata;
use Centreon\Tests\Resource\Mock\EntityMock;
use PDO;

class ClassMetadataTest extends TestCase
{
    public function setUp()
    {
        $this->metadata = new ClassMetadata;
        EntityMock::loadMetadata($this->metadata);
    }

    public function testGetTableName()
    {
        $this->assertEquals('mock_table', $this->metadata->getTableName());
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('id', $this->metadata->getPrimaryKey());
    }

    public function testGetPrimaryKeyColumn()
    {
        $this->assertEquals('id_column', $this->metadata->getPrimaryKeyColumn('id'));
    }

    public function testGetType()
    {
        $this->assertEquals(PDO::PARAM_INT, $this->metadata->getType('id'));
    }

    public function testGet()
    {
        $this->assertEquals([
            ClassMetadata::COLUMN => 'name_column',
            ClassMetadata::TYPE => PDO::PARAM_STR,
            ClassMetadata::FORMATTER => null,
        ], $this->metadata->get('name'));
    }
}
