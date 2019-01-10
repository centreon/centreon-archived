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
     * @var int
     */
    private $total;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * Construct
     * 
     * @param \CentreonModule\Infrastructure\Entity\Module $entity
     * @param string $entityClass Entity JSON wrap class
     * @param int $total
     * @param int $offset
     * @param int $limit
     * @param string $entityClass
     */
    public function __construct($entities, int $total = null, int $offset = null, int $limit = null, string $entityClass = null)
    {
        $this->entities = $entities ?? [];
        $this->total = $total ? $total : count($this->entities);
        $this->offset = $offset;
        $this->limit = $limit !== null ? $limit : $this->total;
        $this->entityClass = $entityClass ?? Entity::class;
    }

    /**
     * JSON serialization of list
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result = [
            'pagination' => [
                'total' => $this->total,
                'offset' => $this->offset !== null ? $this->offset : 0,
                'limit' => $this->limit,
            ],
            'entities' => [],
        ];

        foreach ($this->entities as $entity) {
            $result['entities'][] = new $this->entityClass($entity);
        }

        return $result;
    }
}
