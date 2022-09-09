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

namespace Tests\Centreon\Domain\HostConfiguration\Exceptions;

use Centreon\Domain\HostConfiguration\Exception\HostCategoryException;
use PHPUnit\Framework\TestCase;

/**
 * @package Tests\Centreon\Domain\HostConfiguration\Exceptions
 */
class HostCategoryExceptionTest extends TestCase
{
    /**
     * Tests the arguments of the static method findHostCategoryException.
     */
    public function testFindHostCategoryException(): void
    {
        $previousMessage1 = 'Error message 1';
        $previousMessage2 = 'Error message 2';
        $errorMessage = 'Error when searching for the host category (%s)';

        $exception = HostCategoryException::findHostCategoryException(
            new \Exception($previousMessage1),
            ['id' => 999]
        );
        self::assertEquals(sprintf($errorMessage, 999), $exception->getMessage());
        self::assertNotNull($exception->getPrevious());
        self::assertEquals($previousMessage1, $exception->getPrevious()->getMessage());

        $exception = HostCategoryException::findHostCategoryException(
            new \Exception($previousMessage2),
            ['name' => 'test name']
        );
        self::assertEquals(sprintf($errorMessage, 'test name'), $exception->getMessage());
        self::assertNotNull($exception->getPrevious());
        self::assertEquals($previousMessage2, $exception->getPrevious()->getMessage());
    }

    /**
     * Tests the arguments of the static method notFoundException.
     */
    public function testNotFoundException(): void
    {
        $previousMessage1 = 'Error message 1';
        $previousMessage2 = 'Error message 2';
        $errorMessage = 'Host category (%s) not found';

        $exception = HostCategoryException::notFoundException(
            ['id' => 999],
            new \Exception($previousMessage1),
        );
        self::assertEquals(sprintf($errorMessage, 999), $exception->getMessage());
        self::assertNotNull($exception->getPrevious());
        self::assertEquals($previousMessage1, $exception->getPrevious()->getMessage());

        $exception = HostCategoryException::notFoundException(
            ['name' => 'test name'],
            new \Exception($previousMessage2)
        );
        self::assertEquals(sprintf($errorMessage, 'test name'), $exception->getMessage());
        self::assertNotNull($exception->getPrevious());
        self::assertEquals($previousMessage2, $exception->getPrevious()->getMessage());
    }
}
