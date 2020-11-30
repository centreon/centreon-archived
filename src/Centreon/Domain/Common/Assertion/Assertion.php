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
                $length = \mb_strlen($parameters['value'], $parameters['encoding']);
                if ($length === false) {
                    $length = strlen($parameters['value']);
                }
                return AssertionException::maxLength(
                    $parameters['value'],
                    $length,
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
                return sprintf(
                    _(
                        '[%s] The value "%s" is too short, it should have at least %d characters,'
                        . ' but only has %d characters'
                    ),
                    $parameters['propertyPath'],
                    $parameters['value'],
                    $parameters['minLength'],
                    \mb_strlen($parameters['value'], $parameters['encoding'])
                );
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
                return sprintf(
                    _('[%s] The value "%d" was expected to be at least %d'),
                    $parameters['propertyPath'],
                    $parameters['value'],
                    $parameters['minValue']
                );
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
                return sprintf(
                    _('[%s] The value "%d" was expected to be at most %d'),
                    $parameters['propertyPath'],
                    $parameters['value'],
                    $parameters['maxValue']
                );
            },
            $propertyPath
        );
    }

    /**
     * Determines if the value is greater or equal than given limit.
     *
     * @param int $value Value to test
     * @param int $limit Limit value (>=)
     * @param string|null $propertyPath
     * @throws \Assert\AssertionFailedException
     */
    public static function greaterOrEqualThan(int $value, int $limit, string $propertyPath = null): void
    {
        Assert::greaterOrEqualThan(
            $value,
            $limit,
            function (array $parameters) {
                return sprintf(
                    _('[%s] The value "%d" is not greater or equal than %d'),
                    $parameters['propertyPath'],
                    $parameters['value'],
                    $parameters['limit']
                );
            },
            $propertyPath
        );
    }
}
