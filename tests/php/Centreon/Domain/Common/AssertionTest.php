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

use Centreon\Domain\Common\Assertion\AssertionException;
use PHPUnit\Framework\TestCase;
use Centreon\Domain\Common\Assertion\Assertion;

/**
 * @package Tests\Centreon\Domain\Common
 */
class AssertionTest extends TestCase
{
    /**
     * @var string
     */
    private $propertyName;

    protected function setUp(): void
    {
        $this->propertyName = 'Class::property';
    }

    /**
     * Test the maxLength assertion
     */
    public function testMaxLengthException(): void
    {
        $propertyValue = 'test_value_too_long';
        $maxLength = strlen($propertyValue) - 1;
        $expectedExceptionMessage = AssertionException::maxLength(
            $propertyValue,
            mb_strlen($propertyValue, 'UTF-8'),
            $maxLength,
            $this->propertyName
        )->getMessage();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        Assertion::maxLength($propertyValue, $maxLength, $this->propertyName);
    }

    /**
     * Test no exception on maxLength assertion
     */
    public function testNoExceptionMaxLength(): void
    {
        $propertyValue = 'test_value_too_long';
        Assertion::maxLength($propertyValue, strlen($propertyValue), $this->propertyName);
        $this->expectNotToPerformAssertions();
    }

    /**
     * Test the minLength assertion
     */
    public function testMinLengthException(): void
    {
        $propertyValue = 'test_value_too_short';
        $minLength = strlen($propertyValue) + 1;
        $expectedExceptionMessage = AssertionException::minLength(
            $propertyValue,
            mb_strlen($propertyValue, 'UTF-8'),
            $minLength,
            $this->propertyName
        )->getMessage();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        Assertion::minLength($propertyValue, $minLength, $this->propertyName);
    }

    /**
     * Test no exception on minLength assertion
     */
    public function testNoExceptionMinLength(): void
    {
        $propertyValue = 'test_value_too_long';
        Assertion::minLength($propertyValue, strlen($propertyValue), $this->propertyName);
        $this->expectNotToPerformAssertions();
    }

    /**
     * Test the min assertion
     */
    public function testMinException(): void
    {
        $propertyValue = 49;
        $minLength = $propertyValue + 1;
        $expectedExceptionMessage = AssertionException::min(
            $propertyValue,
            $minLength,
            $this->propertyName
        )->getMessage();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        Assertion::min($propertyValue, $minLength, $this->propertyName);
    }

    /**
     * Test no exception on min assertion
     */
    public function testNoExceptionMin(): void
    {
        $propertyValue = 49;
        $minLength = $propertyValue;
        Assertion::min($propertyValue, $minLength, $this->propertyName);
        $this->expectNotToPerformAssertions();
    }

    /**
     * Test the max assertion
     */
    public function testMaxException(): void
    {
        $propertyValue = 49;
        $minLength = $propertyValue - 1;
        $expectedExceptionMessage = AssertionException::max(
            $propertyValue,
            $minLength,
            $this->propertyName
        )->getMessage();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        Assertion::max($propertyValue, $minLength, $this->propertyName);
    }

    /**
     * Test no exception on max assertion
     */
    public function testNoExceptionMax(): void
    {
        $propertyValue = 49;
        $minLength = $propertyValue;
        Assertion::max($propertyValue, $minLength, $this->propertyName);
        $this->expectNotToPerformAssertions();
    }

    /**
     * Test the greaterOrEqualThan exception
     */
    public function testGreaterOrEqualThan(): void
    {
        $propertyValue = 1;
        $minLength = $propertyValue + 1;
        $expectedExceptionMessage = sprintf(
            _('[%s] The value "%d" is not greater or equal than %d'),
            $this->propertyName,
            $propertyValue,
            $minLength
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        Assertion::greaterOrEqualThan($propertyValue, $minLength, $this->propertyName);
    }

    /**
     * Test no exception on greaterOrEqualThan assertion
     */
    public function testNoExceptionGreaterOrEqualThan(): void
    {
        $propertyValue = 49;
        $minLength = $propertyValue;
        Assertion::greaterOrEqualThan($propertyValue, $minLength, $this->propertyName);
        $this->expectNotToPerformAssertions();
    }

    /**
     * Test the notEmpty assertion
     */
    public function testNotEmptyException(): void
    {
        $propertyValue = '';
        $expectedExceptionMessage = AssertionException::notEmpty(
            $this->propertyName
        )->getMessage();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        Assertion::notEmpty($propertyValue, $this->propertyName);
    }

    /**
     * Test no exception on notEmpty assertion
     */
    public function testNoExceptionNotEmpty(): void
    {
        $propertyValue = 'test_value_too_long';
        Assertion::notEmpty($propertyValue, $this->propertyName);
        $this->expectNotToPerformAssertions();
    }

    /**
     * Test the notNull assertion
     */
    public function testNotNullException(): void
    {
        $propertyValue = null;
        $expectedExceptionMessage = AssertionException::notNull(
            $this->propertyName
        )->getMessage();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        Assertion::notNull($propertyValue, $this->propertyName);
    }

    /**
     * Test no exception on notNull assertion
     */
    public function testNoExceptionNotNull(): void
    {
        $propertyValue = 'test_value_too_long';
        Assertion::notNull($propertyValue, $this->propertyName);
        $this->expectNotToPerformAssertions();
    }
}
