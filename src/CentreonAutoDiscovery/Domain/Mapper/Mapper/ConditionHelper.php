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

trait ConditionHelper
{
    private function verifyCondition(array $condition, array $discoveredHost): bool
    {
        $sourceName = substr($condition['source'], strlen(AbstractMapper::PREFIX_DISCOVERY_RESULT));
        $sourceValue = $discoveredHost[$sourceName] ?? null;
        $valueToTest = $condition['value'];
        $operator = $condition['operator'];
        switch ($operator) {
            case 'equal':
                return $sourceValue === $valueToTest;
            case 'notequal':
                return $sourceValue !== $valueToTest;
        }
        return false;
    }
    /**
     * @param array $discoveredAttributes
     * @param array $discoveredHost
     * @return bool
     */
    public function hasValidJson (array $discoveredAttributes, array $discoveredHost): bool
    {
        $this->validationErrors = [];
        /*
         * Check the 'conditions' attribute
         */
        $conditions = $discoveredAttributes['conditions'] ?? null;
        if ($conditions === null) {
            $this->addValidationError('conditions', 'The \'conditions\' attribute is not defined or is empty');
        } else {
            foreach ($discoveredAttributes['conditions'] as $condition) {
                $conditionAttributes = array_keys($condition);
                if (count($conditionAttributes) !== 3) {
                    $this->addValidationError(
                        'conditions',
                        'Bad number of attributes detected ('.implode(',', $conditionAttributes).')'
                    );
                }
                $unauthorizedAttributes = array_diff($conditionAttributes, ['source', 'value', 'operator']);
                if (!empty($unauthorizedAttributes)) {
                    $this->addValidationError(
                        'conditions',
                        'Attribute(s) not authorized \''.implode(',', $unauthorizedAttributes).'\''
                    );
                }
            }
        }

        /*
         * Check the 'source' attribute
         */
        $sourceAttribute = $discoveredAttributes['source'] ?? null;
        if ($sourceAttribute === null) {
            $this->addValidationError('source', 'The \'source\' attribute is not defined or is empty');
        } else {
            if (strpos($sourceAttribute, self::PREFIX_DISCOVERY_RESULT) !== 0) {
                $this->addValidationError('source', 'Prefix of parameter not recognized');
            } else {
                $sourceAttributeWithoutPrefix = substr($sourceAttribute, strlen(self::PREFIX_DISCOVERY_RESULT));
                // We check if the source is based
                if (!array_key_exists($sourceAttributeWithoutPrefix, $discoveredHost)) {
                    $this->addValidationError('source', 'The \''. $sourceAttributeWithoutPrefix . '\' attribute is not available');
                }
            }
        }

        /*
         * Check the 'destination' attribute
         */
        $destinationAttribute = $discoveredAttributes['destination'] ?? null;
        if ($destinationAttribute === null) {
            $this->addValidationError('destination', 'The \'destination\' attribute is not defined or is empty');
        } elseif (!in_array($destinationAttribute, $this->availableHostAttributes)) {
            $this->addValidationError('destination', 'The \''. $destinationAttribute . '\' attribute is not available');
        }

        return empty($this->validationErrors);
    }
}
