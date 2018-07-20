<?php
namespace Centreon\Infrastructure\Service\Traits;

trait ServiceContainerTrait
{

    /**
     * @var object[]
     */
    private $objects = [];

    public function has($id): bool
    {
        $result = array_key_exists(strtolower($id), $this->objects);

        return $result;
    }

    public function get($id): string
    {
        if ($this->has($id) === false) {
            throw new NotFoundException;
        }

        $result = $this->objects[strtolower($id)];

        return $result;
    }

    public function all(): array
    {
        return $this->objects;
    }
}
