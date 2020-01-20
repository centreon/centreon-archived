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
use CentreonAutoDiscovery\Domain\Mapper\Mapper\MapperException;

interface MapperInterface
{
    /**
     * @param Host $host
     * @param array $mapperDetails
     * @param array $discoveredHost
     * @return Host
     * @throws MapperException
     */
    public function process(Host $host, array $mapperDetails, array $discoveredHost): Host;

    public function addValidationError(string $attribute, string $message): void;

    public function getValidationErrors(): array;

    /**
     * @param array $discoveredAttributes
     * @param array $discoveredHost
     * @return bool
     */
    public function hasValidJson(array $discoveredAttributes, array $discoveredHost): bool;

    public function getName(): string;

    public function getMapping(): array;
}
