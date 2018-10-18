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
            'classname' => $object,
            'factory' => $factory,
        ];

        $this->_sort();
    }

    private function _sort(): void
    {
        usort($this->objects, function($a, $b) {
            return $a['classname']::order() - $b['classname']::order();
        });
    }
}
