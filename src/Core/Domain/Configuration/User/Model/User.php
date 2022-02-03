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

namespace Core\Domain\Configuration\User\Model;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Common\Assertion\AssertionException;

class User
{
    public const MIN_ALIAS_LENGTH = 1,
                 MAX_ALIAS_LENGTH = 255,
                 MIN_NAME_LENGTH = 1,
                 MAX_NAME_LENGTH = 255,
                 MIN_EMAIL_LENGTH = 1,
                 MAX_EMAIL_LENGTH = 255;

    /**
     * @param int $id
     * @param string $alias
     * @param string $name
     * @param string $email
     * @param bool $isAdmin
     * @throws AssertionException
     */
    public function __construct(
        private int $id,
        private string $alias,
        private string $name,
        private string $email,
        private bool $isAdmin,
    ) {
        Assertion::minLength($alias, self::MIN_ALIAS_LENGTH, 'User::alias');
        Assertion::maxLength($alias, self::MAX_ALIAS_LENGTH, 'User::alias');

        Assertion::minLength($name, self::MIN_ALIAS_LENGTH, 'User::name');
        Assertion::maxLength($name, self::MAX_ALIAS_LENGTH, 'User::name');

        // Email format validation cannot be done here until legacy form does not check it
        Assertion::minLength($email, self::MIN_EMAIL_LENGTH, 'User::email');
        Assertion::maxLength($email, self::MAX_EMAIL_LENGTH, 'User::email');
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return self
     */
    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @param bool $isAdmin
     * @return self
     */
    public function setAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;
        return $this;
    }
}
