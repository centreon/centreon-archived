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

class AssociationMapper extends AbstractMapper implements MapperInterface
{
    use ConditionHelper;

    /**
     * @inheritDoc
     */
    public function process (Host $host, array $mapperDetails, array $discoveredHost): Host
    {
        // If there are no conditions, we will apply the change.
        $canApplyChange = true;
        if (!empty($mapperDetails['conditions'])) {
            $canApplyChange = false;
            foreach ($mapperDetails['conditions'] as $condition) {
                $canApplyChange |= $this->verifyCondition($condition, $discoveredHost);
            }
        }
        if ($canApplyChange) {
            $sourceValue = $this->findValue($mapperDetails['source'], $discoveredHost);
            if (is_array($sourceValue)) {
                $sourceValue = null;
            }
            $destinationName = $mapperDetails['destination'];
            $this->updateHost($host, $destinationName, $sourceValue);
        }
        return $host;
    }

    public function getName (): string
    {
        return 'association';
    }

    public function getMapping (): array
    {
        return [
            "conditions" => [
                [
                    "source" => "Discovered attributes",
                    "value" => "Value to test",
                    "operator" => "Test operator"
                ]
            ],
            "source" => "Discovered attributes",
            "destination" => "Host attributes"
        ];
    }
}
