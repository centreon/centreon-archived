<?php
namespace Centreon\Domain\Repository\Interfaces;

interface CfgCentreonBrokerInterface
{
    public function findCentralBrokerConfigId(): int;
}
