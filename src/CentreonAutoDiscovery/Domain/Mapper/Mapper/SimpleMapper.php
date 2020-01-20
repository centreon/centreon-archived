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

class SimpleMapper extends AbstractMapper implements MapperInterface
{
    const MODIFIER_NAME = 'simple_mapping';
    const MODIFIER_MAPPING = [
        'from' => 'Discovered attributes',
        'to' => 'Host attributes'
    ];

    public function getName(): string
    {
        return self::MODIFIER_NAME;
    }

    public function getMapping(): array
    {
        return self::MODIFIER_MAPPING;
    }

    public function process(Host $host, array $mapperDetails, array $discoveredHost): Host
    {
        $fromAttribute = $mapperDetails['from'];
        $toAttribute = $mapperDetails['to'];
        $fromAttribute = substr($fromAttribute, strlen(self::PREFIX_DISCOVERY_RESULT));
        $newValue = $discoveredHost[$fromAttribute];
        $this->updateHost($host, $toAttribute, $newValue);
        return $host;
    }

    public function hasValidJson(array $discoveredAttributes, array $discoveredHost): bool
    {
        $this->validationErrors = [];

        /*
         * Check the 'from' attribute
         */
        $fromAttribute = $discoveredAttributes['from'] ?? null;
        if ($fromAttribute === null) {
            $this->addValidationError('from', 'The \'from\' attribute is not defined or is empty');
        } else {
            if (substr($fromAttribute, 0, strlen(self::PREFIX_DISCOVERY_RESULT)) !== self::PREFIX_DISCOVERY_RESULT) {
                $this->addValidationError('from', 'Prefix of parameter not recognized');
            }
            $fromAttribute = substr($fromAttribute, strlen(self::PREFIX_DISCOVERY_RESULT));
            if (!array_key_exists($fromAttribute, $discoveredHost)) {
                $this->addValidationError('from', 'The \''. $fromAttribute . '\' attribute is not available');
            }
        }

        /*
         * Check the 'to' attribute
         */
        $toAttribute = $discoveredAttributes['to'] ?? null;
        if ($toAttribute === null) {
            $this->addValidationError('to', 'The \'to\' attribute is not defined or is empty');
        } elseif (!in_array($toAttribute, $this->availableHostAttributes)) {
            $this->addValidationError('to', 'The \''. $toAttribute . '\' attribute is not available');
        }

        return empty($this->validationErrors);
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;
    }
}