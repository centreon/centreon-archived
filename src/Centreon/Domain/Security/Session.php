<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Security;

class Session
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var \DateTime
     */
    private $lastReload;

    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @var bool
     */
    private $isValid;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Session
     */
    public function setId(int $id): Session
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     * @return Session
     */
    public function setSessionId(string $sessionId): Session
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return Session
     */
    public function setUserId(int $userId): Session
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastReload(): \DateTime
    {
        return $this->lastReload;
    }

    /**
     * @param \DateTime $lastReload
     * @return Session
     */
    public function setLastReload(\DateTime $lastReload): Session
    {
        $this->lastReload = $lastReload;
        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     * @return Session
     */
    public function setIpAddress(string $ipAddress): Session
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @param bool $isValid
     * @return Session
     */
    public function setIsValid(bool $isValid): Session
    {
        $this->isValid = $isValid;
        return $this;
    }
}
