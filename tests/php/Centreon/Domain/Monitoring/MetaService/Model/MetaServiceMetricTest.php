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

namespace Tests\Centreon\Domain\Monitoring\MetaService\Model;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\Monitoring\MetaService\Model\MetaServiceMetric;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;

/**
 * This class is designed to test all setters of the MetaServiceMetric entity, especially those with exceptions.
 *
 * @package Tests\Centreon\Domain\Monitoring\MetaService\Model
 */
class MetaServiceMetricTest extends TestCase
{
    /**
     * Too short name test
     */
    public function testNameTooShortException(): void
    {
        $name = str_repeat('.', MetaServiceMetric::MIN_METRIC_NAME_LENGTH - 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::minLength(
                $name,
                strlen($name),
                MetaServiceMetric::MIN_METRIC_NAME_LENGTH,
                'MetaServiceMetric::name'
            )->getMessage()
        );
        new MetaServiceMetric($name);
    }

    /**
     * Too long name test
     */
    public function testNameTooLongException(): void
    {
        $name = str_repeat('.', MetaServiceMetric::MAX_METRIC_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                MetaServiceMetric::MAX_METRIC_NAME_LENGTH,
                'MetaServiceMetric::name'
            )->getMessage()
        );
        new MetaServiceMetric($name);
    }

    /**
     * Too long metric unit name test
     */
    public function testMetricUnitNameTooLongException(): void
    {
        $unitName = str_repeat('.', MetaServiceMetric::MAX_METRIC_UNIT_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $unitName,
                strlen($unitName),
                MetaServiceMetric::MAX_METRIC_UNIT_NAME_LENGTH,
                'MetaServiceMetric::unit'
            )->getMessage()
        );
        (new MetaServiceMetric('metric_name'))->setUnit($unitName);
    }

    /**
     * @return MetaServiceMetric
     * @throws \Assert\AssertionFailedException
     */
    public static function createMetaServiceMetricEntity(): MetaServiceMetric
    {
        return (new MetaServiceMetric('rta'))
            ->setId(10)
            ->setUnit('ms')
            ->setValue(0.5);
    }

    /**
     * @return Resource
     * @throws \Assert\AssertionFailedException
     */
    public static function createResourceEntity(): MonitoringResource
    {
        $parentResource = new MonitoringResource(1, 'Centreon-Central', 'host');
        return (new MonitoringResource(1, 'Ping', 'service'))->setParent($parentResource);
    }
}
