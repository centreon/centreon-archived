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

namespace Core\Security\ProviderConfiguration\Domain\Local\Model;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Common\Assertion\AssertionException;

class SecurityPolicy
{
    public const SPECIAL_CHARACTERS_LIST = '@$!%*?&',
                 MIN_PASSWORD_LENGTH = 8,
                 MAX_PASSWORD_LENGTH = 128,
                 MIN_ATTEMPTS = 1,
                 MAX_ATTEMPTS = 10,
                 MIN_BLOCKING_DURATION = 1,
                 MAX_BLOCKING_DURATION = 604800, // 7 days in seconds.
                 MIN_PASSWORD_EXPIRATION_DELAY = 604800, // 7 days in seconds.
                 MAX_PASSWORD_EXPIRATION_DELAY = 31536000, // 12 months in seconds.
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
     * @param int|null $passwordExpirationDelay
     * @param string[] $passwordExpirationExcludedUserAliases
     * @param int|null $delayBeforeNewPassword
     * @throws AssertionException
     */
    public function __construct(
        private int $passwordMinimumLength,
        private bool $hasUppercase,
        private bool $hasLowercase,
        private bool $hasNumber,
        private bool $hasSpecialCharacter,
        private bool $canReusePasswords,
        private ?int $attempts,
        private ?int $blockingDuration,
        private ?int $passwordExpirationDelay,
        private array $passwordExpirationExcludedUserAliases,
        private ?int $delayBeforeNewPassword,
    ) {
        Assertion::min($passwordMinimumLength, self::MIN_PASSWORD_LENGTH, 'SecurityPolicy::passwordMinimumLength');
        Assertion::max($passwordMinimumLength, self::MAX_PASSWORD_LENGTH, 'SecurityPolicy::passwordMinimumLength');
        if ($attempts !== null) {
            Assertion::min($attempts, self::MIN_ATTEMPTS, 'SecurityPolicy::attempts');
            Assertion::max($attempts, self::MAX_ATTEMPTS, 'SecurityPolicy::attempts');
        }
        if ($blockingDuration !== null) {
            Assertion::min($blockingDuration, self::MIN_BLOCKING_DURATION, 'SecurityPolicy::blockingDuration');
            Assertion::max($blockingDuration, self::MAX_BLOCKING_DURATION, 'SecurityPolicy::blockingDuration');
        }
        if ($passwordExpirationDelay !== null) {
            Assertion::min(
                $passwordExpirationDelay,
                self::MIN_PASSWORD_EXPIRATION_DELAY,
                'SecurityPolicy::passwordExpirationDelay'
            );
            Assertion::max(
                $passwordExpirationDelay,
                self::MAX_PASSWORD_EXPIRATION_DELAY,
                'SecurityPolicy::passwordExpirationDelay'
            );
        }
        if ($delayBeforeNewPassword !== null) {
            Assertion::min(
                $delayBeforeNewPassword,
                self::MIN_NEW_PASSWORD_DELAY,
                'SecurityPolicy::delayBeforeNewPassword'
            );
            Assertion::max(
                $delayBeforeNewPassword,
                self::MAX_NEW_PASSWORD_DELAY,
                'SecurityPolicy::delayBeforeNewPassword'
            );
        }
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
    public function getPasswordExpirationDelay(): ?int
    {
        return $this->passwordExpirationDelay;
    }

    /**
     * @param int|null $passwordExpirationDelay
     * @return self
     */
    public function setPasswordExpirationDelay(?int $passwordExpirationDelay): self
    {
        $this->passwordExpirationDelay = $passwordExpirationDelay;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getPasswordExpirationExcludedUserAliases(): array
    {
        return $this->passwordExpirationExcludedUserAliases;
    }

    /**
     * @param string[] $passwordExpirationExcludedUserAliases
     * @return self
     */
    public function setPasswordExpirationExcludedUserAliases(array $passwordExpirationExcludedUserAliases): self
    {
        $this->passwordExpirationExcludedUserAliases = $passwordExpirationExcludedUserAliases;
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
