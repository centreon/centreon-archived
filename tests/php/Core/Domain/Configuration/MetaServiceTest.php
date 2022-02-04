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

namespace Tests\Core\Domain\Configuration;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Domain\Configuration\Model\MetaService;

class MetaServiceTest extends TestCase
{
    /**
     * test name empty exception
     */
    public function testNameEmptyException(): void
    {
        $name = '';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::notEmpty(
                'MetaService::name'
            )->getMessage()
        );
        new MetaService(1, $name, 'average', 1, 'gauge');
    }

    public function testNameTooLongException(): void
    {
        $name = str_repeat('.', MetaService::MAX_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                MetaService::MAX_NAME_LENGTH,
                'MetaService::name'
            )->getMessage()
        );
        new MetaService(1, $name, 'average', 1, 'gauge');
    }

    /**
     * test wrong data source type provided
     */
    public function testUnknownDataSourceType(): void
    {
        $dataSourceType = 'not-handled';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::inArray(
                $dataSourceType,
                MetaService::AVAILABLE_DATA_SOURCE_TYPES,
                'MetaService::dataSourceType'
            )->getMessage()
        );
        new MetaService(1, 'Meta 1', 'average', 1, $dataSourceType);
    }

    /**
     * test wrong calculation type provided
     */
    public function testUnknownCalculationType(): void
    {
        $calculationType = 'not-handled';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::inArray(
                $calculationType,
                MetaService::AVAILABLE_CALCULATION_TYPES,
                'MetaService::calculationType'
            )->getMessage()
        );
        new MetaService(1, 'Meta 1', $calculationType, 1, 'gauge');
    }

    /**
     * @return MetaService
     */
    public static function createMetaServiceModel(): MetaService
    {
        return (new MetaService(1, 'Meta 1', 'average', 1, 'gauge'))
            ->setCritical('80')
            ->setWarning('70')
            ->setMetric('rta')
            ->setActivated(true)
            ->setregexpSearchServices('Ping')
            ->setOutput('Meta output: %s');
    }
}
