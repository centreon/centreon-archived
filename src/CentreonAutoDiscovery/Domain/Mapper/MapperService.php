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
use CentreonAutoDiscovery\Domain\Mapper\Interfaces\MapperRepositoryInterface;
use CentreonAutoDiscovery\Domain\Mapper\Interfaces\MapperServiceInterface;

class MapperService implements MapperServiceInterface
{
    /**
     * @var MapperRepositoryInterface
     */
    private $mapperRepository;
    /**
     * @var MapperInterface[]
     */
    private $mappers;

    private $parameters;


    /**
     * MapperService constructor.
     * @param MapperRepositoryInterface $modifierRepository
     */
    public function __construct(MapperRepositoryInterface $modifierRepository)
    {
        $this->mapperRepository = $modifierRepository;
    }

    public function setMappers(array $mappers): void
    {
        $this->mappers = $mappers;
    }

    /**
     * @inheritDoc
     */
    public function findMappersToApplyByJob(int $jobId): array
    {
        return $this->mapperRepository->findMappersToApplyByJob($jobId);
    }

    /**
     * @inheritDoc
     */
    public function findDiscoveredHostsByJob(int $jobId): array
    {
        return $this->mapperRepository->findDiscoveredHostsByJob($jobId);
    }

    /**
     * @inheritDoc
     */
    public function applyMapperRulesOnDiscoveredHosts(array $discoveredHosts, array $mapperRulesToApply): array
    {
        $engine = new MapperEngine();
        array_map(function ($mapper) use ($engine) {$engine->addMapper($mapper);}, $this->mappers);
        $modifiedHosts = [];
        foreach ($discoveredHosts as $discoveredHost) {
            $modifiedHosts[] = $engine->process(
                (new Host())->setName($discoveredHost->getName()),
                $mapperRulesToApply,
                $discoveredHost->getDiscoveryResult()
            );
        }
        return $modifiedHosts;
    }
}
