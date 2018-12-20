<?php
namespace CentreonModule\Application\DataRepresenter;

use JsonSerializable;
use CentreonModule\Infrastructure\Entity\Module;

class ModuleEntity implements JsonSerializable
{

    /**
     * @var \CentreonModule\Infrastructure\Entity\Module
     */
    private $entity;

    /**
     * Construct
     * 
     * @param \CentreonModule\Infrastructure\Entity\Module $entity
     */
    public function __construct(Module $entity)
    {
        $this->entity = $entity;
    }

    /**
     * JSON serialization of entity
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->entity->getId(),
            'type' => $this->entity->getType(),
            'description' => $this->entity->getName(),
            'label' => $this->entity->getAuthor(),
            'version' => [
                'current' => $this->entity->getVersionCurrent(),
                'available' => $this->entity->getVersion(),
                'outdated' => !$this->entity->isUpdated(),
            ],
            'license:' => null,
        ];
    }
}
