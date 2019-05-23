<?php

namespace Centreon\Domain\Repository\Interfaces;

use Centreon\Domain\Entity\AccessGroup;
use Centreon\Domain\Entity\Host;

interface MonitoringServiceRepositoryInterface
{
    /**
     * Retrieve all real time services.
     *
     * @param AccessGroup[]|null $accessGroupEntity
     * @return Host[]
     */
    public function getServices(?array $accessGroupEntity): array;
}
