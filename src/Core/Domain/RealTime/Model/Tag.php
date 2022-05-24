<?php

namespace Core\Domain\RealTime\Model;

class Tag
{
    public const SERVICE_GROUP_TYPE_ID = 0,
        HOST_GROUP_TYPE_ID = 1,
        SERVICE_CATEGORY_TYPE_ID = 2,
        HOST_CATEGORY_TYPE_ID = 3;

    /**
     * @param int $id
     * @param string $name
     */
    public function __construct(private int $id, private string $name, private int $type)
    {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }
}