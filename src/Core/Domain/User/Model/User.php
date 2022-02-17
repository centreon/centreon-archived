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

namespace Core\Domain\User\Model;

class User
{
    /**
     * Undocumented function
     *
     * @param integer $id
     * @param string $alias
     * @param UserPassword[] $oldpasswords
     * @param UserPassword $password
     */
    public function __construct(
        private int $id,
        private string $alias,
        private array $oldpasswords,
        private UserPassword $password
    ) {
    }

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return UserPassword[]
     */
    public function getOldPasswords(): array
    {
        return $this->oldpasswords;
    }

    /**
     * @return UserPassword
     */
    public function getPassword(): UserPassword
    {
        return $this->password;
    }

    /**
     * @param UserPassword $password
     * @return self
     */
    public function setPassword(UserPassword $password): self
    {
        $this->password = $password;
        return $this;
    }
}
