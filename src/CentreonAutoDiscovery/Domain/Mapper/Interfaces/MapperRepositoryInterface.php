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

use CentreonAutoDiscovery\Domain\Mapper\DiscoveredHost;
use CentreonAutoDiscovery\Domain\Mapper\MapperRule;

interface MapperRepositoryInterface
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
}
