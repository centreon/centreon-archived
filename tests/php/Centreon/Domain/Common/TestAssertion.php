<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Tests\Centreon\Domain\Common;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Common\Assertion\Assertion;

class TestAssertion extends TestCase
{
    private string $propertyName;

    protected function setUp()
    {
        $this->propertyName = 'Class::property';
    }

    /**
     * Test the maxLength assertion
     */
    public function testMaxLength(): void
    {
        $propertyValue = 'test_value_too_long';
        $maxLength = 10;
        $expectedExceptionMessage = sprintf(
            _('[%s] The value "%s" is too long, it should have no more than %d characters, but has %d characters'),
            $this->propertyName,
            $propertyValue,
            $maxLength,
            mb_strlen($propertyValue, 'UTF-8')
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        Assertion::maxLength($propertyValue, $maxLength, $this->propertyName);
    }

    /**
     * Test the minLength assertion
     */
    public function testMinLength(): void
    {
        $propertyValue = 'test_value_too_short';
        $minLength = 50;
        $expectedExceptionMessage = sprintf(
            _('[%s] The value "%s" is too short, it should have at least %d characters, but only has %d characters'),
            $this->propertyName,
            $propertyValue,
            $minLength,
            mb_strlen($propertyValue, 'UTF-8')
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        Assertion::minLength($propertyValue, $minLength, $this->propertyName);
    }
}
