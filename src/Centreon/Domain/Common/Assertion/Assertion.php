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

namespace Centreon\Domain\Common\Assertion;

use Assert\Assertion as Assert;

/**
 * This class is designed to allow the translation of error messages and provide them with a unique format.
 *
 * @package Centreon\Domain\Common\Assertion
 */
class Assertion
{
    /**
     * Assert that string value is not longer than $maxLength characters.
     *
     * @param string $value Value to test
     * @param int $maxLength Maximum length of the expected value in characters
     * @param string|null $propertyPath Property's path (ex: Host::name)
     * @throws \Assert\AssertionFailedException
     */
    public static function maxLength(string $value, int $maxLength, string $propertyPath = null): void
    {
        Assert::maxLength(
            $value,
            $maxLength,
            function (array $parameters) {
                return AssertionException::maxLength(
                    $parameters['value'],
                    self::calculateStringLengthOrFails(
                        $parameters['value'],
                        $parameters['encoding'],
                        $parameters['propertyPath']
                    ),
                    $parameters['maxLength'],
                    $parameters['propertyPath']
                )->getMessage();
            },
            $propertyPath,
            'UTF-8'
        );
    }

    /**
     * Assert that a string is at least $minLength characters long.
     *
     * @param string $value Value to test
     * @param int $minLength Minimum length of the expected value in characters
     * @param string|null $propertyPath Property's path (ex: Host::name)
     * @throws \Assert\AssertionFailedException
     */
    public static function minLength(string $value, int $minLength, string $propertyPath = null): void
    {
        Assert::minLength(
            $value,
            $minLength,
            function (array $parameters) {
                return AssertionException::minLength(
                    $parameters['value'],
                    self::calculateStringLengthOrFails(
                        $parameters['value'],
                        $parameters['encoding'],
                        $parameters['propertyPath']
                    ),
                    $parameters['minLength'],
                    $parameters['propertyPath']
                )->getMessage();
            },
            $propertyPath,
            'UTF-8'
        );
    }

    /**
     * Assert that a value is at least as big as a given limit.
     *
     * @param int $value Value to test
     * @param int $minValue Minimum value
     * @param string|null $propertyPath Property's path (ex: Host::maxCheckAttempts)
     * @throws \Assert\AssertionFailedException
     */
    public static function min(int $value, int $minValue, string $propertyPath = null): void
    {
        Assert::min(
            $value,
            $minValue,
            function (array $parameters) {
                return AssertionException::min(
                    $parameters['value'],
                    $parameters['minValue'],
                    $parameters['propertyPath']
                )->getMessage();
            },
            $propertyPath
        );
    }

    /**
     * Assert that a number is smaller as a given limit.
     *
     * @param int $value Value to test
     * @param int $maxValue Maximum value
     * @param string|null $propertyPath Property's path (ex: Host::maxCheckAttempts)
     * @throws \Assert\AssertionFailedException
     */
    public static function max(int $value, int $maxValue, string $propertyPath = null): void
    {
        Assert::max(
            $value,
            $maxValue,
            function (array $parameters) {
                return AssertionException::max(
                    $parameters['value'],
                    $parameters['maxValue'],
                    $parameters['propertyPath']
                )->getMessage();
            },
            $propertyPath
        );
    }

    /**
     * Assert that a date is smaller as a given limit.
     *
     * @param \DateTime $value
     * @param \DateTime $maxDate
     * @param string|null $propertyPath
     * @throws AssertionException
     */
    public static function maxDate(\DateTime $value, \DateTime $maxDate, string $propertyPath = null): void
    {
        if ($value->getTimestamp() > $maxDate->getTimestamp()) {
            throw AssertionException::maxDate($value, $maxDate, $propertyPath);
        }
    }

    /**
     * Determines if the value is greater or equal than given limit.
     *
     * @param int $value Value to test
     * @param int $limit Limit value (>=)
     * @param string|null $propertyPath Property's path (ex: Host::maxCheckAttempts)
     * @throws \Assert\AssertionFailedException
     */
    public static function greaterOrEqualThan(int $value, int $limit, string $propertyPath = null): void
    {
        Assert::greaterOrEqualThan(
            $value,
            $limit,
            function (array $parameters) {
                return AssertionException::greaterOrEqualThan(
                    $parameters['value'],
                    $parameters['limit'],
                    $parameters['propertyPath']
                )->getMessage();
            },
            $propertyPath
        );
    }

    /**
     * Assert that value is not empty.
     *
     * @param mixed $value Value to test
     * @param string|null $propertyPath Property's path (ex: Host::name)
     * @throws \Assert\AssertionFailedException
     */
    public static function notEmpty($value, string $propertyPath = null): void
    {
        Assert::notEmpty(
            $value,
            function (array $parameters) {
                return AssertionException::notEmpty($parameters['propertyPath']);
            },
            $propertyPath
        );
    }

    /**
     * Assert that value is not null.
     *
     * @param mixed $value Value to test
     * @param string|null $propertyPath Property's path (ex: Host::name)
     * @throws \Assert\AssertionFailedException
     */
    public static function notNull($value, string $propertyPath = null): void
    {
        Assert::notNull(
            $value,
            function (array $parameters) {
                return AssertionException::notNull($parameters['propertyPath']);
            },
            $propertyPath
        );
    }

    /**
     * Calculates the string length or fails.
     *
     * @param string $value Value for which we have to calculate the length
     * @param string $encoding Encoding used for calculation
     * @param string $propertyPath Property's path (ex: Host::name)
     * @return int Calculated length
     */
    private static function calculateStringLengthOrFails(string $value, string $encoding, string $propertyPath): int
    {
        $length = \mb_strlen($value, $encoding);
        if ($length === false) {
            throw new \RuntimeException(
                sprintf(
                    _('[%s] Error when calculating string length of "%s" in %s'),
                    $propertyPath,
                    $value,
                    $encoding
                )
            );
        }
        return $length;
    }
}
