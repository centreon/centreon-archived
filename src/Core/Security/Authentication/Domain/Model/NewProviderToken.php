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

namespace Core\Security\Authentication\Domain\Model;

use DateTime;
use DateTimeImmutable;

class NewProviderToken
{
    /**
     * ProviderToken constructor.
     *
     * @param string $token
     * @param DateTimeImmutable $creationDate
     * @param DateTimeImmutable|null $expirationDate
     */
    public function __construct(
        private string $token,
        private DateTimeImmutable $creationDate,
        private ?DateTimeImmutable $expirationDate = null
    ) {
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreationDate(): DateTimeImmutable
    {
        return $this->creationDate;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getExpirationDate(): ?DateTimeImmutable
    {
        return $this->expirationDate;
    }

    /**
     * // TODO To be removed
     * @param DateTimeImmutable|null $expirationDate
     * @return self
     */
    public function setExpirationDate(?DateTimeImmutable $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * @param DateTimeImmutable|null $now
     * @return bool
     */
    public function isExpired(DateTimeImmutable $now = null): bool
    {
        if ($this->expirationDate === null) {
            return false;
        }

        if ($now === null) {
            $now = new DateTime();
        }

        return $this->expirationDate->getTimestamp() < $now->getTimestamp();
    }
}
