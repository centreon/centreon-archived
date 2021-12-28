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

namespace Tests\Core\Domain\Security\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Domain\Security\Model\SecurityPolicy;
use PHPUnit\Framework\TestCase;

class SecurityPolicyTest extends TestCase
{
    /**
     * Test that an exception is thrown when creating a Security Policy with invalid password length.
     */
    public function testPasswordMinimumLengthTooSmallException(): void
    {
        $passwordMinimumLength = SecurityPolicy::MIN_PASSWORD_LENGTH - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::min(
            $passwordMinimumLength,
            SecurityPolicy::MIN_PASSWORD_LENGTH,
            'SecurityPolicy::passwordMinimumLength'
        )->getMessage());

        new SecurityPolicy($passwordMinimumLength, true, true, true, true, true);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid password length.
     */
    public function testPasswordMinimumLengthTooHighException(): void
    {
        $passwordMinimumLength = SecurityPolicy::MAX_PASSWORD_LENGTH + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::max(
            $passwordMinimumLength,
            SecurityPolicy::MAX_PASSWORD_LENGTH,
            'SecurityPolicy::passwordMinimumLength'
        )->getMessage());

        new SecurityPolicy($passwordMinimumLength, true, true, true, true, true);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid attempts number.
     */
    public function testAttemptsTooSmallException(): void
    {
        $attempts = SecurityPolicy::MIN_ATTEMPTS - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::min(
            $attempts,
            SecurityPolicy::MIN_ATTEMPTS,
            'SecurityPolicy::attempts'
        )->getMessage());

        new SecurityPolicy(SecurityPolicy::MIN_PASSWORD_LENGTH, true, true, true, true, true, $attempts);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid attempts number.
     */
    public function testAttemptsTooHighException(): void
    {
        $attempts = SecurityPolicy::MAX_ATTEMPTS + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::max(
            $attempts,
            SecurityPolicy::MAX_ATTEMPTS,
            'SecurityPolicy::attempts'
        )->getMessage());

        new SecurityPolicy(SecurityPolicy::MIN_PASSWORD_LENGTH, true, true, true, true, true, $attempts);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid blocking duration.
     */
    public function testBlockingDurationTooSmallException(): void
    {
        $blockingDuration = SecurityPolicy::MIN_BLOCKING_DURATION - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::min(
            $blockingDuration,
            SecurityPolicy::MIN_BLOCKING_DURATION,
            'SecurityPolicy::blockingDuration'
        )->getMessage());

        new SecurityPolicy(
            SecurityPolicy::MIN_PASSWORD_LENGTH,
            true,
            true,
            true,
            true,
            true,
            SecurityPolicy::MIN_ATTEMPTS,
            $blockingDuration
        );
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid blocking duration.
     */
    public function testBlockingDurationTooHighException(): void
    {
        $blockingDuration = SecurityPolicy::MAX_BLOCKING_DURATION + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::max(
            $blockingDuration,
            SecurityPolicy::MAX_BLOCKING_DURATION,
            'SecurityPolicy::blockingDuration'
        )->getMessage());

        new SecurityPolicy(
            SecurityPolicy::MIN_PASSWORD_LENGTH,
            true,
            true,
            true,
            true,
            true,
            SecurityPolicy::MIN_ATTEMPTS,
            $blockingDuration
        );
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid password expiration.
     */
    public function testPasswordExpirationTooSmallException(): void
    {
        $passwordExpiration = SecurityPolicy::MIN_PASSWORD_EXPIRATION - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::min(
            $passwordExpiration,
            SecurityPolicy::MIN_PASSWORD_EXPIRATION,
            'SecurityPolicy::passwordExpiration'
        )->getMessage());

        new SecurityPolicy(
            SecurityPolicy::MIN_PASSWORD_LENGTH,
            true,
            true,
            true,
            true,
            true,
            SecurityPolicy::MIN_ATTEMPTS,
            SecurityPolicy::MIN_BLOCKING_DURATION,
            $passwordExpiration
        );
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid password expiration.
     */
    public function testPasswordExpirationTooHighException(): void
    {
        $passwordExpiration = SecurityPolicy::MAX_PASSWORD_EXPIRATION + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::max(
            $passwordExpiration,
            SecurityPolicy::MAX_PASSWORD_EXPIRATION,
            'SecurityPolicy::passwordExpiration'
        )->getMessage());

        new SecurityPolicy(
            SecurityPolicy::MIN_PASSWORD_LENGTH,
            true,
            true,
            true,
            true,
            true,
            SecurityPolicy::MIN_ATTEMPTS,
            SecurityPolicy::MIN_BLOCKING_DURATION,
            $passwordExpiration
        );
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid delay before new password.
     */
    public function testDelayBeforeNewPasswordTooSmallException(): void
    {
        $delayBeforeNewPassword = SecurityPolicy::MIN_NEW_PASSWORD_DELAY - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::min(
            $delayBeforeNewPassword,
            SecurityPolicy::MIN_NEW_PASSWORD_DELAY,
            'SecurityPolicy::delayBeforeNewPassword'
        )->getMessage());

        new SecurityPolicy(
            SecurityPolicy::MIN_PASSWORD_LENGTH,
            true,
            true,
            true,
            true,
            true,
            SecurityPolicy::MIN_ATTEMPTS,
            SecurityPolicy::MIN_BLOCKING_DURATION,
            SecurityPolicy::MIN_PASSWORD_EXPIRATION,
            $delayBeforeNewPassword
        );
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid delay before new password.
     */
    public function testDelayBeforeNewPasswordTooHighException(): void
    {
        $delayBeforeNewPassword = SecurityPolicy::MAX_NEW_PASSWORD_DELAY + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::max(
            $delayBeforeNewPassword,
            SecurityPolicy::MAX_NEW_PASSWORD_DELAY,
            'SecurityPolicy::delayBeforeNewPassword'
        )->getMessage());

        new SecurityPolicy(
            SecurityPolicy::MIN_PASSWORD_LENGTH,
            true,
            true,
            true,
            true,
            true,
            SecurityPolicy::MIN_ATTEMPTS,
            SecurityPolicy::MIN_BLOCKING_DURATION,
            SecurityPolicy::MIN_PASSWORD_EXPIRATION,
            $delayBeforeNewPassword
        );
    }

    public static function createSecurityPolicyModel(): SecurityPolicy
    {
        return new SecurityPolicy(
            SecurityPolicy::MIN_PASSWORD_LENGTH,
            true,
            true,
            true,
            true,
            true,
            SecurityPolicy::MIN_ATTEMPTS,
            SecurityPolicy::MIN_BLOCKING_DURATION,
            SecurityPolicy::MIN_PASSWORD_EXPIRATION,
            SecurityPolicy::MIN_NEW_PASSWORD_DELAY
        );
    }
}
