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

namespace Tests\Centreon\Domain\Monitoring\MetaService\Exceptions;

use Centreon\Domain\Monitoring\MetaService\Exception\MetaServiceMetricException;
use PHPUnit\Framework\TestCase;

/**
 * @package Tests\Centreon\Domain\Monitoring\MetaService\Exceptions
 */
class MetaServiceMetricExceptionTest extends TestCase
{
    /**
     * Tests the arguments of the static method findMetaServiceMetricsException.
     */
    public function testFindMetaServiceMetricsException(): void
    {
        $previousMessage1 = 'Error message 1';
        $errorMessageMetaServiceMetricsError = 'Error when searching for the meta service (%d) metrics';

        $exception = MetaServiceMetricException::findMetaServiceMetricsException(
            new \Exception($previousMessage1),
            999
        );
        self::assertEquals(sprintf($errorMessageMetaServiceMetricsError, 999), $exception->getMessage());
        self::assertNotNull($exception->getPrevious());
        self::assertEquals($previousMessage1, $exception->getPrevious()->getMessage());
    }

    /**
     * Tests the arguments of the static method findMetaServiceException.
     */
    public function testFindMetaServiceException(): void
    {
        $errorMessageMetaServiceNotFound = 'Meta service with ID %d not found';

        $exception = MetaServiceMetricException::findMetaServiceException(
            999
        );
        self::assertEquals(sprintf($errorMessageMetaServiceNotFound, 999), $exception->getMessage());
    }
}
