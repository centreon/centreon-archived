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
        $ref = new ReflectionClass($object);
        $hasInterfaces = (
            $ref->isSubclassOf(CentreonWebService::class) ||
            $ref->implementsInterface(CentreonWebserviceServiceInterface::class)
            );

        if ($hasInterfaces === false) {
            throw new NotFoundException;
        }

        $name = strtolower($object::getName());
        $this->objects[$name] = $object;
    }
}
