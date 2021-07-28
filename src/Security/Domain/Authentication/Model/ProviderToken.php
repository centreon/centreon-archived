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

namespace Security\Domain\Authentication\Model;

/**
 * @package Security\Domain\Authentication\Model
 */
class ProviderToken
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $token;

    /**
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @var \DateTime|null
     */
    private $expirationDate;

    /**
     * ProviderToken constructor.
     *
     * @param int|null $id
     * @param string $token
     * @param \DateTime $creationDate
     * @param \DateTime|null $expirationDate
     */
    public function __construct(
        ?int $id,
        string $token,
        \DateTime $creationDate,
        \DateTime $expirationDate = null
    ) {
        $this->id = $id;
        $this->token = $token;
        $this->creationDate = $creationDate;
        $this->expirationDate = $expirationDate;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate(): \DateTime
    {
        return $this->creationDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpirationDate(): ?\DateTime
    {
        return $this->expirationDate;
    }

    /**
     * @param \DateTime|null $expirationDate
     * @return self
     */
    public function setExpirationDate(?\DateTime $expirationDate): self
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    /**
     * Indicates whether or not the token is expired.
     *
     * @param \DateTime|null $now
     * @return bool
     */
    public function isExpired(\DateTime $now = null): bool
    {
        if ($this->expirationDate === null) {
            return false;
        }

        if ($now === null) {
            $now = new \DateTime();
        }
        return $this->expirationDate->getTimestamp() < $now->getTimestamp();
    }
}
