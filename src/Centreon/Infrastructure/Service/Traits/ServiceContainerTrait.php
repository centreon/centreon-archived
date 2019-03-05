<?php

namespace Centreon\Infrastructure\Service\Traits;

use Centreon\Infrastructure\Service\Exception\NotFoundException;

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
            throw new NotFoundException('Not found exporter with name: ' . $id);
        }

        $result = $this->objects[strtolower($id)];

        return $result;
    }

    public function all(): array
    {
        return $this->objects;
    }
}
