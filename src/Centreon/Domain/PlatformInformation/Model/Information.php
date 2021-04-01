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

namespace Centreon\Domain\PlatformInformation\Model;

use Centreon\Domain\Common\Assertion\Assertion;

/**
 * Class designed to retrieve servers' specific information
 *
 */
class Information
{
    public const MAX_KEY_LENGTH = 25,
                 MAX_VALUE_LENGTH = 1024,
                 MIN_KEY_LENGTH = 1,
                 MIN_VALUE_LENGTH = 1;
    /**
     * Information key
     *
     * @var string
     */
    private $key;

    /**
     * Information value
     *
     * @var mixed|null
     */
    private $value;

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @throws \Assert\AssertionFailedException
     * @return self
     */
    public function setKey(string $key): self
    {
        Assertion::minLength($key, self::MIN_KEY_LENGTH, 'Information::key');
        Assertion::maxLength($key, self::MAX_KEY_LENGTH, 'Information::key');
        $this->key = $key;
        return $this;
    }

    /**
     * @return string|array<string,mixed>|int|bool|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @throws \Assert\AssertionFailedException
     * @return self
     */
    public function setValue($value): self
    {
        if ($value !== null && is_string($value)) {
            Assertion::minLength($value, self::MIN_VALUE_LENGTH, 'Information::value');
            Assertion::maxLength($value, self::MAX_VALUE_LENGTH, 'Information::value');
        }
        $this->value = $value;
        return $this;
    }
}
