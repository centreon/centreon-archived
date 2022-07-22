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

use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Common\Assertion\Assertion;

/**
 * This class is designed to represent a  meta service metric.
 *
 * @package Centreon\Domain\Monitoring\MetaService\Model
 */
class MetaServiceMetric
{
    final public const MAX_METRIC_NAME_LENGTH = 255,
                 MIN_METRIC_NAME_LENGTH = 1,
                 MAX_METRIC_UNIT_NAME_LENGTH = 32;
    /**
     * @var int ID of the Metric
     */
    private ?int $id = null;
    /**
     * @var string Name of the Metric
     */
    private string $name;
    /**
     * @var string Name of the Metric Unit
     */
    private ?string $unit = null;

    /**
     * @var float Current value of the Metric in RealTime
     */
    private ?float $value = null;

    /**
     * @var ResourceEntity Resource on which Metric is attached
     */
    private ?ResourceEntity $resource = null;

    /**
     * Contructor of MetaServiceMetric entity
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->setName($name);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): MetaServiceMetric
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @throws \Assert\AssertionFailedException
     */
    public function setName(string $name): MetaServiceMetric
    {
        Assertion::maxLength($name, self::MAX_METRIC_NAME_LENGTH, 'MetaServiceMetric::name');
        Assertion::minLength($name, self::MIN_METRIC_NAME_LENGTH, 'MetaServiceMetric::name');
        $this->name = $name;
        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
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

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(?float $value): MetaServiceMetric
    {
        $this->value = $value;
        return $this;
    }

    public function getResource(): ?ResourceEntity
    {
        return $this->resource;
    }

    public function setResource(?ResourceEntity $resource): MetaServiceMetric
    {
        $this->resource = $resource;
        return $this;
    }
}
