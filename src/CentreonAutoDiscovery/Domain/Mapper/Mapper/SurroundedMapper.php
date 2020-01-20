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

namespace CentreonAutoDiscovery\Domain\Mapper\Mapper;

use Centreon\Domain\HostConfiguration\Host;
use CentreonAutoDiscovery\Domain\Mapper\Interfaces\MapperInterface;

class SurroundedMapper implements MapperInterface
{
    const MODIFIER_NAME = 'surrounded_mapping';
    const MODIFIER_MAPPING = [
        'from_label' => 'Discovered attributes',
        'to_label' => 'Host attributes'
    ];

    public function process(Host $host, array $mapperDetails, array $discoveredHost): Host
    {
        $host->setName('*' . $host->getName() . '*');
        return $host;
    }

    public function getName(): string
    {
        return self::MODIFIER_NAME;
    }

    public function getMapping(): array
    {
        return self::MODIFIER_MAPPING;
    }


    public function addValidationError(string $attribute, string $message): void
    {
        // TODO: Implement addValidationError() method.
    }

    public function hasValidJson(array $discoveredAttributes, array $discoveredHost): bool
    {
        return true;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        // TODO: Implement setOptions() method.
    }

    public function getValidationErrors (): array
    {
        return $this->getValidationErrors();
    }
}
