<?php
namespace Centreon\Application\DataRepresenter;

use JsonSerializable;
use Centreon\Application\DataRepresenter\Listing;
use Centreon\Application\DataRepresenter\Entity;

/**
 * 
 */
class Bulk implements JsonSerializable
{

    /**
     * @var array
     */
    private $lists;

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
    private $listingClass;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * Construct
     * 
     * @param array $lists
     * @param string $listingClass
     * @param string $entityClass
     */
    public function __construct(array $lists, int $offset = null, int $limit = null, string $listingClass = null, string $entityClass = null)
    {
        $this->lists = $lists;
        $this->offset = $offset;
        $this->limit = $limit;
        $this->listingClass = $listingClass ?? Listing::class;
        $this->entityClass = $entityClass ?? Entity::class;
    }

    /**
     * JSON serialization of several lists
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result = [];

        foreach ($this->lists as $name => $entities) {
            $result[$name] = new $this->listingClass($entities, null, $this->offset, $this->limit, $this->entityClass);
        }

        return $result;
    }
}
