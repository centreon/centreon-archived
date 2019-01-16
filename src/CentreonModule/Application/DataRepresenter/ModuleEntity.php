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
     * @OA\Schema(
     *   schema="ModuleEntity",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="type", type="string", enum={"module","widget"}),
     *       @OA\Property(property="description", type="string"),
     *       @OA\Property(property="label", type="string"),
     *       @OA\Property(property="version", type="object",
     *          @OA\Property(property="current", type="string"),
     *          @OA\Property(property="available", type="string"),
     *          @OA\Property(property="outdated", type="boolean")
     *       ),
     *       @OA\Property(property="license", type="string")
     * )
     *
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
            'license' => $this->entity->getLicense(),
        ];
    }
}
