<?php
namespace Centreon\Application\DataRepresenter;

use JsonSerializable;
use Centreon\Application\DataRepresenter\Entity;

/**
 * @OA\Schema(
 *   schema="Pagination",
 *   allOf={
 *     @OA\Schema(
 *       @OA\Property(property="total", type="integer"),
 *       @OA\Property(property="offset", type="integer"),
 *       @OA\Property(property="limit", type="integer")
 *     )
 *   }
 * )
 */
class Listing implements JsonSerializable
{

    /**
     * @var array
     */
    private $entities;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * Construct
     * 
     * @param \CentreonModule\Infrastructure\Entity\Module $entity
     * @param string $entityClass Entity JSON wrap class
     */
    public function __construct($entities, int $offset = null, int $limit = null, string $entityClass = null)
    {
        $this->entities = $entities ?? [];
        $this->offset = $offset;
        $this->limit = $limit;
        $this->entityClass = $entityClass ?? Entity::class;
    }

    /**
     * JSON serialization of list
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $total = count($this->entities);
        $result = [
            'pagination' => [
                'total' => $total,
                'offset' => $this->offset !== null ? $this->offset : 0,
                'limit' => $this->limit !== null ? $this->limit : $total,
            ],
            'entities' => [],
        ];

        foreach ($this->entities as $entity) {
            $result['entities'][] = new $this->entityClass($entity);
        }

        return $result;
    }
}
