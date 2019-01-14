<?php
namespace CentreonModule\Application\DataRepresenter;

use JsonSerializable;
use CentreonModule\Infrastructure\Entity\Module;

class ModuleDetailEntity implements JsonSerializable
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
     *   schema="ModuleDetailEntity",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="type", type="string", enum={"module","widget"}),
     *       @OA\Property(property="title", type="string"),
     *       @OA\Property(property="description", type="string"),
     *       @OA\Property(property="label", type="string"),
     *       @OA\Property(property="stability", type="string"),
     *       @OA\Property(property="version", type="object",
     *          @OA\Property(property="current", type="string"),
     *          @OA\Property(property="available", type="string"),
     *          @OA\Property(property="outdated", type="boolean"),
     *          @OA\Property(property="installed", type="boolean")
     *       ),
     *       @OA\Property(property="license", type="string"),
     *       @OA\Property(property="images", type="array", items={"string"}),
     *       @OA\Property(property="last_update", type="string"),
     *       @OA\Property(property="release_note", type="string")
     * )
     *
     * JSON serialization of entity
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $outdated = $this->entity->isInstalled() && !$this->entity->isUpdated() ?
            true :
            false
        ;

        return [
            'id' => $this->entity->getId(),
            'type' => $this->entity->getType(),
            'title' => $this->entity->getName(),
            'description' => $this->entity->getDescription(),
            'label' => $this->entity->getAuthor(),
            'stability' => $this->entity->getStability(),
            'version' => [
                'current' => $this->entity->getVersionCurrent(),
                'available' => $this->entity->getVersion(),
                'outdated' => $outdated,
                'installed' => $this->entity->isInstalled(),
            ],
            'license' => $this->entity->getLicense(),
            'images' => $this->entity->getImages(),
            'last_update' => $this->entity->getLastUpdate(),
            'release_note' => $this->entity->getReleaseNote(),
        ];
    }
}
