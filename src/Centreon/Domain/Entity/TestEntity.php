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
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\Accessor(getter="isOk")
     */
    private $isOk;

    /**
     * @var int[]
     * @Serializer\Type("array<integer>")
     */
    private $ids;

    /**
     * @var string|null
     * @Serializer\Type("string")
     */
    private $name;

    /**
     * @var Command|null
     * @Serializer\Type("Centreon\Domain\Entity\Command")
     */
    private $oneCommand;

    /**
     * @var Command[]
     * @Serializer\Type("array<Centreon\Domain\Entity\Command>")
     */
    private $commands;


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

    /**
     * @return Command|null
     */
    public function getOneCommand(): Command
    {
        return $this->oneCommand;
    }

    /**
     * @param Command|null $oneCommand
     * @return TestEntity
     */
    public function setOneCommand(Command $oneCommand): TestEntity
    {
        $this->oneCommand = $oneCommand;
        return $this;
    }

    /**
     * @return Command[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @param Command[] $commands
     * @return TestEntity
     */
    public function setCommands(array $commands): TestEntity
    {
        $this->commands = $commands;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->isOk;
    }

    /**
     * @param bool $isOk
     * @return TestEntity
     */
    public function setIsOk(bool $isOk): TestEntity
    {
        $this->isOk = $isOk;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @param int[] $ids
     * @return TestEntity
     */
    public function setIds(array $ids): TestEntity
    {
        $this->ids = $ids;
        return $this;
    }
}
