<?php
namespace Centreon\Application\DataRepresenter;

use JsonSerializable;

class Entity implements JsonSerializable
{

    /**
     * @var mixed
     */
    private $entity;

    /**
     * Construct
     * 
     * @param mixed $entity
     */
    public function __construct($entity)
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
        return (array) $this->entity;
    }
}
