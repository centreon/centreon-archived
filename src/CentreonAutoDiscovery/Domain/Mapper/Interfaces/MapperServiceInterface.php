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

namespace CentreonAutoDiscovery\Domain\Mapper\Interfaces;

use Centreon\Domain\HostConfiguration\Host;
use CentreonAutoDiscovery\Domain\Mapper\DiscoveredHost;
use CentreonAutoDiscovery\Domain\Mapper\MapperEngine;
use CentreonAutoDiscovery\Domain\Mapper\Mapper;
use CentreonAutoDiscovery\Domain\Mapper\MapperService;
use CentreonAutoDiscovery\Domain\Mapper\MapperRule;

interface MapperServiceInterface
{
    /**
     * @param int $jobId Job id
     * @return MapperRule[]
     * @throws \Exception
     */
    public function findMappersToApplyByJob(int $jobId): array;

    /**
     * @param int $jobId
     * @return DiscoveredHost[]
     * @throws \Exception
     */
    public function findDiscoveredHostsByJob (int $jobId): array;

    /**
     * @param DiscoveredHost[] $discoveredHosts
     * @param MapperRule[] $mapperRulesToApply
     * @return Host[]
     */
    public function applyMapperRulesOnDiscoveredHosts (array $discoveredHosts, array $mapperRulesToApply): array;

    /**
     * Defines the mapping engines that will be used to apply the mapping rules to the host
     *
     * @param MapperInterface[] $mappers Mapping engines
     */
    public function setMappers (array $mappers): void;
}
