<?php

namespace Centreon\Tests\Infrastructure\CentreonLegacyDB;

use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\CentreonLegacyDB\EntityPersister;
use Centreon\Infrastructure\CentreonLegacyDB\Mapping\ClassMetadata;
use Centreon\Tests\Resource\Mock\EntityMock;

class EntityPersisterTest extends TestCase
{

    public function testLoad()
    {
        $metadata = new ClassMetadata();
        EntityMock::loadMetadata($metadata);

        $metadata->add('text', 'text');
        $metadata->add('name', 'name_column', \PDO::PARAM_STR, function ($value) {
            return "{$value} with formatter";
        });
        
        $entity = new EntityMock;
        $entity->setId(2);
        $entity->setName('test name with formatter');

        $entityPersister = new EntityPersister(EntityMock::class, $metadata);
        
        $this->assertEquals($entity, $entityPersister->load([
            'id_column' => '2',
            'name_column' => 'test name',
            'description_column' => 'test description',
            'text_column' => 'test text',
        ]));
    }
}
