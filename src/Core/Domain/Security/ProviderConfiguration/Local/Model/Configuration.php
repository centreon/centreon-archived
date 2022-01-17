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

namespace Core\Domain\Security\ProviderConfiguration\Local\Model;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Common\Assertion\AssertionException;

class Configuration
{
    public const SPECIAL_CHARACTERS_LIST = '@$!%*?&',
         MIN_PASSWORD_LENGTH = 8,
         MAX_PASSWORD_LENGTH = 128,
         MIN_ATTEMPTS = 1,
         MAX_ATTEMPTS = 10,
         MIN_BLOCKING_DURATION = 1,
         MAX_BLOCKING_DURATION = 604800, // 7 days in seconds.
         MIN_PASSWORD_EXPIRATION = 604800, // 7 days in seconds.
         MAX_PASSWORD_EXPIRATION = 31536000, // 12 months in seconds.
         MIN_NEW_PASSWORD_DELAY = 3600, // 1 hour in seconds.
         MAX_NEW_PASSWORD_DELAY = 604800; // 7 days in seconds.

    /**
     * @param int $passwordMinimumLength
     * @param bool $hasUppercase
     * @param bool $hasLowercase
     * @param bool $hasNumber
     * @param bool $hasSpecialCharacter
     * @param bool $canReusePasswords
     * @param int|null $attempts
     * @param int|null $blockingDuration
     * @param int|null $passwordExpiration
     * @param int|null $delayBeforeNewPassword
     * @throws AssertionException
     */
    public function __construct(
        private int $id,
        private string $type,
        private string $name,
        private bool $isActive,
        private bool $isForced,
        private int $passwordMinimumLength,
        private bool $hasUppercase,
        private bool $hasLowercase,
        private bool $hasNumber,
        private bool $hasSpecialCharacter,
        private bool $canReusePasswords,
        private ?int $attempts,
        private ?int $blockingDuration,
        private ?int $passwordExpiration,
        private ?int $delayBeforeNewPassword
    ) {
        Assertion::min($passwordMinimumLength, self::MIN_PASSWORD_LENGTH, 'Configuration::passwordMinimumLength');
        Assertion::max($passwordMinimumLength, self::MAX_PASSWORD_LENGTH, 'Configuration::passwordMinimumLength');
        if ($attempts !== null) {
            Assertion::min($attempts, self::MIN_ATTEMPTS, 'Configuration::attempts');
            Assertion::max($attempts, self::MAX_ATTEMPTS, 'Configuration::attempts');
        }
        if ($blockingDuration !== null) {
            Assertion::min($blockingDuration, self::MIN_BLOCKING_DURATION, 'Configuration::blockingDuration');
            Assertion::max($blockingDuration, self::MAX_BLOCKING_DURATION, 'Configuration::blockingDuration');
        }
        if ($passwordExpiration !== null) {
            Assertion::min($passwordExpiration, self::MIN_PASSWORD_EXPIRATION, 'Configuration::passwordExpiration');
            Assertion::max($passwordExpiration, self::MAX_PASSWORD_EXPIRATION, 'Configuration::passwordExpiration');
        }
        if ($delayBeforeNewPassword !== null) {
            Assertion::min(
                $delayBeforeNewPassword,
                self::MIN_NEW_PASSWORD_DELAY,
                'Configuration::delayBeforeNewPassword'
            );
            Assertion::max(
                $delayBeforeNewPassword,
                self::MAX_NEW_PASSWORD_DELAY,
                'Configuration::delayBeforeNewPassword'
            );
        }
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;

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
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return self
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return bool
     */
    public function isForced(): bool
    {
        return $this->isForced;
    }

    /**
     * @param bool $isForced
     * @return self
     */
    public function setIsForced(bool $isForced): self
    {
        $this->isForced = $isForced;

        return $this;
    }

    /**
     * @return int
     */
    public function getPasswordMinimumLength(): int
    {
        return $this->passwordMinimumLength;
    }

    /**
     * @param int $passwordMinimumLength
     * @return self
     */
    public function setPasswordMinimumLength(int $passwordMinimumLength): self
    {
        $this->passwordMinimumLength = $passwordMinimumLength;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasUppercase(): bool
    {
        return $this->hasUppercase;
    }

    /**
     * @param bool $hasUppercase
     * @return self
     */
    public function setUppercase(bool $hasUppercase): self
    {
        $this->hasUppercase = $hasUppercase;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasLowercase(): bool
    {
        return $this->hasLowercase;
    }

    /**
     * @param bool $hasLowercase
     * @return self
     */
    public function setLowercase(bool $hasLowercase): self
    {
        $this->hasLowercase = $hasLowercase;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasNumber(): bool
    {
        return $this->hasNumber;
    }

    /**
     * @param bool $hasNumber
     * @return self
     */
    public function setNumber(bool $hasNumber): self
    {
        $this->hasNumber = $hasNumber;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasSpecialCharacter(): bool
    {
        return $this->hasSpecialCharacter;
    }

    /**
     * @param bool $hasSpecialCharacter
     * @return self
     */
    public function setSpecialCharacter(bool $hasSpecialCharacter): self
    {
        $this->hasSpecialCharacter = $hasSpecialCharacter;
        return $this;
    }

    /**
     * @return bool
     */
    public function canReusePasswords(): bool
    {
        return $this->canReusePasswords;
    }

    /**
     * @param bool $canReusePasswords
     * @return self
     */
    public function setReusePassword(bool $canReusePasswords): self
    {
        $this->canReusePasswords = $canReusePasswords;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAttempts(): ?int
    {
        return $this->attempts;
    }

    /**
     * @param int|null $attempts
     * @return self
     */
    public function setAttempts(?int $attempts): self
    {
        $this->attempts = $attempts;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getBlockingDuration(): ?int
    {
        return $this->blockingDuration;
    }

    /**
     * @param int|null $blockingDuration
     * @return self
     */
    public function setBlockingDuration(?int $blockingDuration): self
    {
        $this->blockingDuration = $blockingDuration;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPasswordExpiration(): ?int
    {
        return $this->passwordExpiration;
    }

    /**
     * @param int|null $passwordExpiration
     * @return self
     */
    public function setPasswordExpiration(?int $passwordExpiration): self
    {
        $this->passwordExpiration = $passwordExpiration;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDelayBeforeNewPassword(): ?int
    {
        return $this->delayBeforeNewPassword;
    }

    /**
     * @param int|null $delayBeforeNewPassword
     * @return self
     */
    public function setDelayBeforeNewPassword(?int $delayBeforeNewPassword): self
    {
        $this->delayBeforeNewPassword = $delayBeforeNewPassword;
        return $this;
    }
}
