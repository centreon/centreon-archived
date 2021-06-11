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

namespace Centreon\Domain\Monitoring\MetaService\Model;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;

/**
 * This class is designed to represent a  meta service metric.
 *
 * @package Centreon\Domain\Monitoring\MetaService\Model
 */
class MetaServiceMetric
{
    public const MAX_METRIC_NAME_LENGTH = 255,
                 MIN_METRIC_NAME_LENGTH = 1,
                 MAX_METRIC_UNIT_NAME_LENGTH = 32;
    /**
     * @var int ID of the Metric
     */
    private $id;
    /**
     * @var string Name of the Metric
     */
    private $name;
    /**
     * @var string Name of the Metric Unit
     */
    private $unit;

    /**
     * @var float Current value of the Metric in RealTime
     */
    private $value;

    /**
     * @var MonitoringResource Resource on which Metric is attached
     */
    private $monitoringResource;

    /**
     * Contructor of MetaServiceMetric entity
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->setName($name);
    }

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param integer $id
     * @return MetaServiceMetric
     */
    public function setId(int $id): MetaServiceMetric
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
     * @return MetaServiceMetric
     * @throws \Assert\AssertionFailedException
     */
    public function setName(string $name): MetaServiceMetric
    {
        Assertion::maxLength($name, self::MAX_METRIC_NAME_LENGTH, 'MetaServiceMetric::name');
        Assertion::minLength($name, self::MIN_METRIC_NAME_LENGTH, 'MetaServiceMetric::name');
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * @param string|null $unit
     * @return MetaServiceMetric
     * @throws \Assert\AssertionFailedException
     */
    public function setUnit(?string $unit): MetaServiceMetric
    {
        if (!is_null($unit)) {
            Assertion::maxLength($unit, self::MAX_METRIC_UNIT_NAME_LENGTH, 'MetaServiceMetric::unit');
        }
        $this->unit = $unit;
        return $this;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param float|null $value
     * @return MetaServiceMetric
     */
    public function setValue(?float $value): MetaServiceMetric
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return MonitoringResource|null
     */
    public function getMonitoringResource(): ?MonitoringResource
    {
        return $this->monitoringResource;
    }

    /**
     * @param MonitoringResource|null $monitoringResource
     * @return MetaServiceMetric
     */
    public function setMonitoringResource(?MonitoringResource $monitoringResource): MetaServiceMetric
    {
        $this->monitoringResource = $monitoringResource;
        return $this;
    }
}
