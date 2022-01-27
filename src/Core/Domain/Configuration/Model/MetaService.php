<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Domain\Configuration\Model;

use Centreon\Domain\Common\Assertion\Assertion;

class MetaService
{
    public const AVAILABLE_DATA_SOURCE_TYPES = ['gauge', 'counter', 'derive', 'absolute'];
    public const AVAILABLE_CALCULATION_TYPES = ['average', 'minimum', 'maximum', 'sum'];
    public const META_SELECT_MODE_LIST = 1;
    public const META_SELECT_MODE_SQL_REGEXP = 2;

    public const MAX_NAME_LENGTH = 254;

    /**
     * @var string|null
     */
    private $output;

    /**
     * @var string|null Search string to be used in a SQL LIKE query for service selection
     */
    private $regexpSearchServices;

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
     * @param int $id
     * @param string $name
     * @param string $calculationType
     * @param int $metaSelectionMode Selection mode for services to be considered for this meta service.
     *     0 - In service list mode, mark selected services in the options on meta service list.
     *     1 - In SQL matching mode, specify a search string to be used in an SQL query.
     * @param string $dataSourceType Define the data source type of the Meta Service
     *     0 - GAUGE / 1 - COUNTER / 2 - DERIVE / 3 - ABSOLUTE
     */
    public function __construct(
        private int $id,
        private string $name,
        private string $calculationType,
        private int $metaSelectionMode,
        private string $dataSourceType
    ) {
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'MetaService::name');
        Assertion::notEmpty($name, 'MetaService::name');
        Assertion::inArray($dataSourceType, self::AVAILABLE_DATA_SOURCE_TYPES, 'MetaService::dataSourceType');
        Assertion::inArray($calculationType, self::AVAILABLE_CALCULATION_TYPES, 'MetaService::calculationType');
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCalculationType(): string
    {
        return $this->calculationType;
    }

    /**
     * @return int
     */
    public function getMetaSelectionMode(): int
    {
        return $this->metaSelectionMode;
    }

    /**
     * @return string
     */
    public function getDataSourceType(): string
    {
        return $this->dataSourceType;
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
     * @return self
     */
    public function setOutput(?string $output): self
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegexpSearchServices(): ?string
    {
        return $this->regexpSearchServices;
    }

    /**
     * @param string|null $regexpSearchServices
     * @return self
     */
    public function setregexpSearchServices(?string $regexpSearchServices): self
    {
        $this->regexpSearchServices = $regexpSearchServices;
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
     * @return self
     */
    public function setMetric(?string $metric): self
    {
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
     * @return self
     */
    public function setWarning(?string $warning): self
    {
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
     * @return self
     */
    public function setCritical(?string $critical): self
    {
        $this->critical = $critical;
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
     * @return self
     */
    public function setIsActivated(bool $isActivated): self
    {
        $this->isActivated = $isActivated;
        return $this;
    }
}
