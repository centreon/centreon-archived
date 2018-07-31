<?php
namespace Centreon\Infrastructure\Service;

use Centreon\Infrastructure\Service\Exception\NotFoundException;
use ReflectionClass;
use Psr\Container\ContainerInterface;
use Centreon\Infrastructure\Service\Traits\ServiceContainerTrait;

class CentreonClapiService implements ContainerInterface
{

    use ServiceContainerTrait;

    public function add(string $object): void
    {
        $interface = CentreonClapiServiceInterface::class;
        $hasInterface = (new ReflectionClass($object))
            ->implementsInterface($interface);

        if ($hasInterface === false) {
            throw new NotFoundException(sprintf('Object %s must implement %s', $object, $interface));
        }

        $name = strtolower($object::getName());
        $this->objects[$name] = $object;
    }
}
