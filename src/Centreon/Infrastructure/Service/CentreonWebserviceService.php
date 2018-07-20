<?php
namespace Centreon\Infrastructure\Service;

use ReflectionClass;
use Psr\Container\ContainerInterface;
use CentreonWebserviceServiceInterface;
use CentreonWebService;
use Centreon\Infrastructure\Service\Traits\ServiceContainerTrait;

class CentreonWebserviceService implements ContainerInterface
{
    use ServiceContainerTrait;

    public function add(string $object): void
    {
        $class = CentreonWebService::class;
        $interface = CentreonWebserviceServiceInterface::class;
        $ref = new ReflectionClass($object);
        $hasInterfaces = (
            $ref->isSubclassOf($class) ||
            $ref->implementsInterface($interface)
            );

        if ($hasInterfaces === false) {
            throw new NotFoundException(sprintf('Object %s must implement %s and extend %s class', $object, $interface, $class));
        }

        $name = strtolower($object::getName());
        $this->objects[$name] = $object;
    }
}
