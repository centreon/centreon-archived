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
     * Assert that string value is not longer than $maxLength bits.
     *
     * @param string $value Value to test
     * @param int $maxLength Max length in bits
     * @param string|null $propertyPath Property's path (ex: Host::name)
     * @throws \Assert\AssertionFailedException
     */
    public static function maxLength(string $value, int $maxLength, string $propertyPath = null): void
    {
        Assert::maxLength(
            $value,
            $maxLength,
            function (array $parameters) {
                return sprintf(
                    _(
                        '[%s] The value "%s" is too long, it should have no more than %d characters,'
                        . ' but has %d characters'
                    ),
                    $parameters['propertyPath'],
                    $parameters['value'],
                    $parameters['maxLength'],
                    \mb_strlen($parameters['value'], $parameters['encoding'])
                );
            },
            $propertyPath,
            'UTF-8'
        );
    }

    /**
     * Assert that a string is at least $minLength bits long.
     *
     * @param string $value Value to test
     * @param int $minLength Min length in bits
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
}
