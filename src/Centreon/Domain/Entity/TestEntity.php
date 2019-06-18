<?php


namespace Centreon\Domain\Entity;


use JMS\Serializer\Annotation as Serializer;

class TestEntity
{
    /**
     * @var int|null
     * @Serializer\Type("integer")
     */
    private $id;

    /**
     * @var string|null
     * @Serializer\Type("string")
     */
    private $name;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return TestEntity
     */
    public function setId(?int $id): TestEntity
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return TestEntity
     */
    public function setName(?string $name): TestEntity
    {
        $this->name = $name;
        return $this;
    }

}