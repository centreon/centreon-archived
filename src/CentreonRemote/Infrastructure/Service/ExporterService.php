<?php
namespace CentreonRemote\Infrastructure\Service;

use Centreon\Infrastructure\Service\Exception\NotFoundException;
use ReflectionClass;
use Psr\Container\ContainerInterface;
use Centreon\Infrastructure\Service\Traits\ServiceContainerTrait;

class ExporterService implements ContainerInterface
{

    use ServiceContainerTrait;

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
            'factory' => $factory,
        ];

        $this->_sort();
    }

    public function has($id): bool
    {
        $result = $this->_getKey($id);

        return $result !== null;
    }

    public function get($id): array
    {
        $key = $this->_getKey($id);
        if ($key === null) {
            throw new NotFoundException('Not found exporter with name: ' . $id);
        }

        $result = $this->objects[$key];

        return $result;
    }

    private function _getKey($id): ?int
    {
        foreach ($this->objects as $key => $object) {
            if ($object['name'] === $id) {
                return $key;
            }
        }

        return null;
    }

    private function _sort(): void
    {
        usort($this->objects, function($a, $b) {
            return $a['classname']::order() - $b['classname']::order();
        });
    }
}
