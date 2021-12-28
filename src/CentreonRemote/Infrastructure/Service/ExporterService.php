<?php
namespace CentreonRemote\Infrastructure\Service;

use Centreon\Infrastructure\Service\Exception\NotFoundException;
use ReflectionClass;
use Psr\Container\ContainerInterface;
use Centreon\Infrastructure\Service\Traits\ServiceContainerTrait;

class ExporterService implements ContainerInterface
{
    use ServiceContainerTrait;

    /**
     *
     * @param string $object
     * @param callable $factory
     * @return void
     */
    public function add(string $object, callable $factory): void
    {
        $interface = ExporterServiceInterface::class;
        $hasInterface = (new ReflectionClass($object))
            ->implementsInterface($interface);

        if ($hasInterface === false) {
            throw new NotFoundException(sprintf('Object %s must implement %s', $object, $interface));
        }

        $name = strtolower($object::getName());
        $this->objects[$name] = [
            'name' => $name,
            'classname' => $object,
            'factory' => $factory
        ];

        $this->sort();
    }

    /**
     *
     * @param string $id
     */
    public function has($id): bool
    {
        $result = $this->getKey($id);

        return $result !== null;
    }

    /**
     *
     * @param string $id
     */
    public function get($id): object
    {
        $key = $this->getKey($id);
        if ($key === null) {
            throw new NotFoundException('Not found exporter with name: ' . $id);
        }

        $result = $this->objects[$key];

        return $result;
    }

    /**
     *
     * @param string $id
     */
    private function getKey($id): ?int
    {
        foreach ($this->objects as $key => $object) {
            if ($object['name'] === $id) {
                return $key;
            }
        }

        return null;
    }

    private function sort(): void
    {
        usort($this->objects, function ($a, $b) {
            return $a['classname']::order() - $b['classname']::order();
        });
    }
}
