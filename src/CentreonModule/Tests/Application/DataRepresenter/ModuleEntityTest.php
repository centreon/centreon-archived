<?php
namespace CentreonModule\Tests\Application\DataRepresenter;

use PHPUnit\Framework\TestCase;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Application\DataRepresenter\ModuleEntity;

class ModuleEntityTestTest extends TestCase
{

    public function testJsonSerialize()
    {
        $data = [
            'id' => '1',
            'type' => 'module',
            'name' => 'Test Module',
            'author' => 'John Doe',
            'versionCurrent' => '1.0.0',
            'version' => '1.0.1',
            'license' => '11/20',
        ];

        $entity = new Module;
        $entity->setId($data['id']);
        $entity->setType($data['type']);
        $entity->setName($data['name']);
        $entity->setAuthor($data['author']);
        $entity->setVersionCurrent($data['versionCurrent']);
        $entity->setVersion($data['version']);
        $entity->setLicense($data['license']);

        $controlResult = [
            'id' => $entity->getId(),
            'type' => $entity->getType(),
            'description' => $entity->getName(),
            'label' => $entity->getAuthor(),
            'version' => [
                'current' => $entity->getVersionCurrent(),
                'available' => $entity->getVersion(),
                'outdated' => !$entity->isUpdated(),
            ],
            'license' => $entity->getLicense(),
        ];

        $dataRepresenter = new ModuleEntity($entity);
        $result = $dataRepresenter->jsonSerialize();

        $this->assertEquals($result, $controlResult);
    }
}
