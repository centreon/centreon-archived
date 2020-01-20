<?php
/*
 * CENTREON
 *
 * Source Copyright 2005-2019 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace CentreonAutoDiscovery\Domain\Mapper;

use Centreon\Domain\HostConfiguration\Host;
use CentreonAutoDiscovery\Domain\Mapper\Interfaces\MapperInterface;
use CentreonAutoDiscovery\Domain\Mapper\Mapper\MapperException;

class MapperEngine
{
    /**
     * @var array
     */
    private $attributes;
    /**
     * @var MapperInterface[]
     */
    private $mappers = [];
    private $index = 0;

    /**
     * @param MapperInterface $middleware
     */
    public function addMapper(MapperInterface $middleware): void
    {
        if (!array_key_exists($middleware->getName(), $this->mappers)) {
            $this->mappers[$middleware->getName()] = $middleware;
        }
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @param Host $host
     * @param MapperRule[] $mapperRulesToApply
     * @param array $discoveredHost
     * @return Host
     * @throws Mapper\MapperException
     */
    public function process(Host $host, array $mapperRulesToApply, array $discoveredHost): Host
    {
        usort($mapperRulesToApply, function($mapperRuleToApply1, $mapperRuleToApply2) {
            return $mapperRuleToApply1->getOrder() >  $mapperRuleToApply2->getOrder();
        });

        foreach ($mapperRulesToApply as $mapperRuleToApply) {
            if (null !== ($mapper = $this->findMapperByName($mapperRuleToApply->getName()))) {
                $mapperDetails = json_decode($mapperRuleToApply->getDetails(), true);
                if ($mapperDetails === null) {
                    throw new MapperException(
                        'The mapper detail of \'' . $mapperRuleToApply->getName() . '\' is null'
                    );
                }
                if ($mapper->hasValidJson($mapperDetails, $discoveredHost)) {
                    $mapper->process($host, $mapperDetails, $discoveredHost);
                } else {
                    $errorMessages = [];
                    // First, we sort the errors by type
                    foreach ($mapper->getValidationErrors() as $attribute => $error) {
                        $errorMessages[$attribute][] = $error;
                    }
                    throw new MapperException(json_encode($errorMessages));
                } 
            }
        }

        return $host;
    }

    /**
     * @param string $name
     * @return MapperInterface|null
     */
    private function findMapperByName(string $name): ?MapperInterface
    {
        return $this->mappers[$name] ?? null;
    }

    private function getModifierAttributesByOrder(int $index): array
    {

    }
}
