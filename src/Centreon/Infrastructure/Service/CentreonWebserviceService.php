<?php

namespace Centreon\Infrastructure\Service;

use Centreon\Infrastructure\Service\Exception\NotFoundException;
use CentreonRemote\Application\Webservice\CentreonWebServiceAbstract;
use ReflectionClass;
use Psr\Container\ContainerInterface;
use CentreonWebService;
use Centreon\Infrastructure\Service\Traits\ServiceContainerTrait;

class CentreonWebserviceService implements ContainerInterface
{
    use ServiceContainerTrait;

    public function add(string $object): void
    {
        $centreonClass = CentreonWebService::class;
        $abstractClass = CentreonWebServiceAbstract::class;
        $ref = new ReflectionClass($object);
        $hasInterfaces = (
            $ref->isSubclassOf($centreonClass) ||
            $ref->isSubclassOf($abstractClass)
        );

        if ($hasInterfaces === false) {
            throw new NotFoundException(sprintf('Object %s must extend %s class or %s class', $object, $centreonClass, $abstractClass));
        }

        $name = strtolower($object::getName());
        $this->objects[$name] = $object;
    }
}
