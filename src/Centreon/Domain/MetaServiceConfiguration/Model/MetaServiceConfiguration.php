<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Centreon\Domain\MetaServiceConfiguration\Model;

use Centreon\Domain\Common\Assertion\Assertion;
use InvalidArgumentException;

/**
 * This class is designed to represent a meta service configuration.
 *
 * @package Centreon\Domain\MetaServiceConfiguration\Model
 */
class MetaServiceConfiguration
{
    public const MAX_NAME_LENGTH = 254,
                 MIN_NAME_LENGTH = 1,
                 MAX_OUTPUT_LENGTH = 254,
                 MAX_METRIC_LENGTH = 255,
                 MAX_REGEXP_STRING_LENGTH = 254,
                 MAX_WARNING_LENGTH = 254,
                 MAX_CRITICAL_LENGTH = 254;

    public const AVAILABLE_DATA_SOURCE_TYPES = ['gauge', 'counter', 'derive', 'absolute'];
    public const AVAILABLE_CALCULATION_TYPES = ['average', 'minimum', 'maximum', 'sum'];
    public const META_SELECT_MODE_LIST = 1;
    public const META_SELECT_MODE_SQL_REGEXP = 2;

    /**
     * @var int ID of the Meta Service
     */
    private $id;

    /**
     * @var string Name used to identity the Meta Service
     */
    private $name;

    /**
     * @var string|null Define the output displayed by the Meta Service
     */
    private $output;

    /**
     * @var string Define the function to be applied to calculate the Meta Service status
     */
    private $calculationType;

    /**
     * @var string Define the data source type of the Meta Service
     * 0 - GAUGE
     * 1 - COUNTER
     * 2 - DERIVE
     * 3 - ABSOLUTE
     */
    private $dataSourceType = 'gauge';

    /**
     * @var int Selection mode for services to be considered for this meta service.
     * 0 - In service list mode, mark selected services in the options on meta service list.
     * 1 - In SQL matching mode, specify a search string to be used in an SQL query.
     */
    private $metaSelectMode = 1;

    /**
     * @var string|null Search string to be used in a SQL LIKE query for service selection
     */
    private $regexpString;

    /**
     * @var string|null Select the metric to measure for meta service status.
     */
    private $metric;

    /**
     * @var string|null Absolute value for warning level (low threshold).
     */
    private $warning;

    /**
     * @var string|null Absolute value for critical level (high threshold).
     */
    private $critical;

    /**
     * @var bool Indicates whether this Meta Service is enabled or not (TRUE by default)
     */
    private $isActivated = true;

    /**
     * @param string $name
     * @param string $calculationType
     * @param int $metaSelectMode
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $name, string $calculationType, int $metaSelectMode)
    {
        $this->setName($name);
        $this->setCalculationType($calculationType);
        $this->setMetaSelectMode($metaSelectMode);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return MetaServiceConfiguration
     */
    public function setId(int $id): MetaServiceConfiguration
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MetaServiceConfiguration
     * @throws \Assert\AssertionFailedException
     */
    public function setName(string $name): MetaServiceConfiguration
    {
        Assertion::minLength($name, self::MIN_NAME_LENGTH, 'MetaServiceConfiguration::name');
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'MetaServiceConfiguration::name');
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->isActivated;
    }

    /**
     * @param bool $isActivated
     * @return MetaServiceConfiguration
     */
    public function setActivated(bool $isActivated): MetaServiceConfiguration
    {
        $this->isActivated = $isActivated;
        return $this;
    }

    /**
     * @return string
     */
    public function getCalculationType(): string
    {
        return $this->calculationType;
    }

    /**
     * @param string $calculationType
     * @return MetaServiceConfiguration
     * @throws InvalidArgumentException
     */
    public function setCalculationType(string $calculationType): MetaServiceConfiguration
    {
        if (!in_array($calculationType, self::AVAILABLE_CALCULATION_TYPES)) {
            throw new InvalidArgumentException(
                sprintf(_('Calculation method provided not supported (%s)'), $calculationType)
            );
        }
        $this->calculationType = $calculationType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOutput(): ?string
    {
        return $this->output;
    }

    /**
     * @param string|null $output
     * @return MetaServiceConfiguration
     * @throws \Assert\AssertionFailedException
     */
    public function setOutput(?string $output): MetaServiceConfiguration
    {
        if (!is_null($output)) {
            Assertion::maxLength($output, self::MAX_OUTPUT_LENGTH, 'MetaServiceConfiguration::output');
        }
        $this->output = $output;
        return $this;
    }

    /**
     * @return string
     */
    public function getDataSourceType(): string
    {
        return $this->dataSourceType;
    }

    /**
     * @param string $dataSourceType
     * @return MetaServiceConfiguration
     */
    public function setDataSourceType(string $dataSourceType): MetaServiceConfiguration
    {
        if (!in_array($dataSourceType, self::AVAILABLE_DATA_SOURCE_TYPES)) {
            throw new InvalidArgumentException(
                sprintf(_('Data source type provided not supported (%s)'), $dataSourceType)
            );
        }
        $this->dataSourceType = $dataSourceType;
        return $this;
    }

    /**
     * @return int
     */
    public function getMetaSelectMode(): int
    {
        return $this->metaSelectMode;
    }

    /**
     * @param int $metaSelectMode
     * @return MetaServiceConfiguration
     */
    public function setMetaSelectMode(int $metaSelectMode): MetaServiceConfiguration
    {
        $this->metaSelectMode = $metaSelectMode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegexpString(): ?string
    {
        return $this->regexpString;
    }

    /**
     * @param string|null $regexpString
     * @return MetaServiceConfiguration
     * @throws \Assert\AssertionFailedException
     */
    public function setRegexpString(?string $regexpString): MetaServiceConfiguration
    {
        if (!is_null($regexpString)) {
            Assertion::maxLength(
                $regexpString,
                self::MAX_REGEXP_STRING_LENGTH,
                'MetaServiceConfiguration::regexpString'
            );
        }
        $this->regexpString = $regexpString;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMetric(): ?string
    {
        return $this->metric;
    }

    /**
     * @param string|null $metric
     * @return MetaServiceConfiguration
     * @throws \Assert\AssertionFailedException
     */
    public function setMetric(?string $metric): MetaServiceConfiguration
    {
        if (!is_null($metric)) {
            Assertion::maxLength($metric, self::MAX_METRIC_LENGTH, 'MetaServiceConfiguration::metric');
        }
        $this->metric = $metric;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWarning(): ?string
    {
        return $this->warning;
    }

    /**
     * @param string|null $warning
     * @return MetaServiceConfiguration
     * @throws \Assert\AssertionFailedException
     */
    public function setWarning(?string $warning): MetaServiceConfiguration
    {
        if (!is_null($warning)) {
            Assertion::maxLength($warning, self::MAX_WARNING_LENGTH, 'MetaServiceConfiguration::warning');
        }
        $this->warning = $warning;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCritical(): ?string
    {
        return $this->critical;
    }

    /**
     * @param string|null $critical
     * @return MetaServiceConfiguration
     * @throws \Assert\AssertionFailedException
     */
    public function setCritical(?string $critical): MetaServiceConfiguration
    {
        if (!is_null($critical)) {
            Assertion::maxLength($critical, self::MAX_CRITICAL_LENGTH, 'MetaServiceConfiguration::critical');
        }
        $this->critical = $critical;
        return $this;
    }
}
