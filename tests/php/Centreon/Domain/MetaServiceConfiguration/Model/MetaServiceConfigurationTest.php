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

namespace Tests\Centreon\Domain\MetaServiceConfiguration\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\MetaServiceConfiguration\Exception\MetaServiceConfigurationException;
use Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * This class is designed to test all setters of the MetaServiceConfiguration entity, especially those with exceptions.
 *
 * @package Tests\Centreon\Domain\MetaServiceConfiguration\Model
 */
class MetaServiceConfigurationTest extends TestCase
{
    /**
     * Too long name test
     */
    public function testNameTooShortException(): void
    {
        $name = str_repeat('.', MetaServiceConfiguration::MIN_NAME_LENGTH - 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::minLength(
                $name,
                strlen($name),
                MetaServiceConfiguration::MIN_NAME_LENGTH,
                'MetaServiceConfiguration::name'
            )->getMessage()
        );
        new MetaServiceConfiguration($name, 'average', 1);
    }

    /**
     * Too long name test
     */
    public function testNameTooLongException(): void
    {
        $name = str_repeat('.', MetaServiceConfiguration::MAX_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                MetaServiceConfiguration::MAX_NAME_LENGTH,
                'MetaServiceConfiguration::name'
            )->getMessage()
        );
        new MetaServiceConfiguration($name, 'average', 1);
    }

    /**
     * Too long output test
     */
    public function testOutputTooLongException(): void
    {
        $output = str_repeat('.', MetaServiceConfiguration::MAX_OUTPUT_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $output,
                strlen($output),
                MetaServiceConfiguration::MAX_OUTPUT_LENGTH,
                'MetaServiceConfiguration::output'
            )->getMessage()
        );
        (new MetaServiceConfiguration('name', 'average', 1))->setOutput($output);
    }

    /**
     * Too long regexp test
     */
    public function testRegexpStringTooLong(): void
    {
        $regexpString = str_repeat('.', MetaServiceConfiguration::MAX_REGEXP_STRING_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $regexpString,
                strlen($regexpString),
                MetaServiceConfiguration::MAX_REGEXP_STRING_LENGTH,
                'MetaServiceConfiguration::regexpString'
            )->getMessage()
        );
        (new MetaServiceConfiguration('name', 'average', 1))->setRegexpString($regexpString);
    }

    /**
     * Too long warning test
     */
    public function testWarningTooLong(): void
    {
        $warning = str_repeat('.', MetaServiceConfiguration::MAX_WARNING_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $warning,
                strlen($warning),
                MetaServiceConfiguration::MAX_WARNING_LENGTH,
                'MetaServiceConfiguration::warning'
            )->getMessage()
        );
        (new MetaServiceConfiguration('name', 'average', 1))->setWarning($warning);
    }

    /**
     * Too long warning test
     */
    public function testCriticalTooLong(): void
    {
        $critical = str_repeat('.', MetaServiceConfiguration::MAX_CRITICAL_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $critical,
                strlen($critical),
                MetaServiceConfiguration::MAX_CRITICAL_LENGTH,
                'MetaServiceConfiguration::critical'
            )->getMessage()
        );
        (new MetaServiceConfiguration('name', 'average', 1))->setCritical($critical);
    }

    /**
     * Too long metric test
     */
    public function testMetricTooLong(): void
    {
        $metric = str_repeat('.', MetaServiceConfiguration::MAX_METRIC_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $metric,
                strlen($metric),
                MetaServiceConfiguration::MAX_METRIC_LENGTH,
                'MetaServiceConfiguration::metric'
            )->getMessage()
        );
        (new MetaServiceConfiguration('name', 'average', 1))->setMetric($metric);
    }

    /**
     * Not supported calculation type
     */
    public function testNotSupportedCalculationType(): void
    {
        $calculationType = 'calculationType';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(_('Calculation method provided not supported (%s)'), $calculationType)
        );
        new MetaServiceConfiguration('name', $calculationType, 1);
    }

    /**
     * Not supported calculation type
     */
    public function testNotSupportedDataSourceType(): void
    {
        $dataSourceType = 'dataSourceType';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(_('Data source type provided not supported (%s)'), $dataSourceType)
        );
        (new MetaServiceConfiguration('name', 'average', 1))->setDataSourceType($dataSourceType);
    }


    /**
     * @return MetaServiceConfiguration
     * @throws \Assert\AssertionFailedException
     */
    public static function createEntity(): MetaServiceConfiguration
    {
        return (new MetaServiceConfiguration('name', 'average', 1))
            ->setId(1)
            ->setOutput('output')
            ->setDataSourceType('gauge')
            ->setMetaSelectMode(1)
            ->setRegexpString(null)
            ->setWarning('10')
            ->setCritical('20')
            ->setMetric(null)
            ->setActivated(true);
    }
}
