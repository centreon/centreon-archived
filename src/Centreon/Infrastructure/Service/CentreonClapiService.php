<?php
namespace Centreon\Infrastructure\Service;

use ReflectionClass;
use Psr\Container\ContainerInterface;
use Centreon\Infrastructure\Service\CentreonClapiServiceInterface;
use Centreon\Infrastructure\Service\Traits\ServiceContainerTrait;

class CentreonClapiService implements ContainerInterface
{

    use ServiceContainerTrait;

    public function add(string $object): void
    {
        $hasInterface = (new ReflectionClass($object))
            ->implementsInterface(CentreonClapiServiceInterface::class)
        ;

        if ($hasInterface === false) {
            throw new NotFoundException;
        }

        $name = strtolower($object::getName());
        $this->objects[$name] = $object;
    }
}
