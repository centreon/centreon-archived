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

namespace Core\Domain\Security\User\Model;

class UserPassword
{
    /**
     * @param integer $userId
     * @param string $passwordValue
     * @param integer $creationDate
     */
    public function __construct(private int $userId, private string $passwordValue, private int $creationDate)
    {
    }

    /**
     * @return integer
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getPasswordValue(): string
    {
        return $this->passwordValue;
    }

    /**
     * @return integer
     */
    public function getCreationDate(): int
    {
        return $this->creationDate;
    }

    /**
     * @param string $passwordValue
     * @return self
     */
    public function setPasswordValue(string $passwordValue): self
    {
        $this->passwordValue = $passwordValue;
        return $this;
    }

    /**
     * @param integer $creationDate
     * @return self
     */
    public function setCreationDate(int $creationDate): self
    {
        $this->creationDate = $creationDate;
        return $this;
    }
}
