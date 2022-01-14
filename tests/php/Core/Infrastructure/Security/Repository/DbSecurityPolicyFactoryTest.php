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

namespace Tests\Core\Infrastructure\Security\Repository;

use PHPUnit\Framework\TestCase;
use Core\Domain\Security\Model\SecurityPolicy;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Infrastructure\Security\Repository\DbSecurityPolicyFactory;

class DbSecurityPolicyFactoryTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private $securityPolicyData;

    public function setUp(): void
    {
        $this->securityPolicyData = [
            'password_length' => SecurityPolicy::MIN_PASSWORD_LENGTH,
            'has_uppercase_characters' => true,
            'has_lowercase_characters' => true,
            'has_numbers' => true,
            'has_special_characters' => true,
            'can_reuse_passwords' => true,
            'attempts' => SecurityPolicy::MIN_ATTEMPTS,
            'blocking_duration' => SecurityPolicy::MIN_BLOCKING_DURATION,
            'password_expiration' => SecurityPolicy::MIN_PASSWORD_EXPIRATION,
            'delay_before_new_password' => SecurityPolicy::MIN_NEW_PASSWORD_DELAY,
        ];
    }
    /**
     * Test that an exception is thrown when creating a Security Policy with invalid password length.
     */
    public function testPasswordMinimumLengthTooSmallException(): void
    {
        $this->securityPolicyData['password_length'] =
        SecurityPolicy::MIN_PASSWORD_LENGTH - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::min(
            $this->securityPolicyData['password_length'],
            SecurityPolicy::MIN_PASSWORD_LENGTH,
            'SecurityPolicy::passwordMinimumLength'
        )->getMessage());

        DbSecurityPolicyFactory::createFromRecord($this->securityPolicyData);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid password length.
     */
    public function testPasswordMinimumLengthTooHighException(): void
    {
        $this->securityPolicyData['password_length'] =
            SecurityPolicy::MAX_PASSWORD_LENGTH + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::max(
            $this->securityPolicyData['password_length'],
            SecurityPolicy::MAX_PASSWORD_LENGTH,
            'SecurityPolicy::passwordMinimumLength'
        )->getMessage());

        DbSecurityPolicyFactory::createFromRecord($this->securityPolicyData);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid attempts number.
     */
    public function testAttemptsTooSmallException(): void
    {
        $this->securityPolicyData['attempts'] =
            SecurityPolicy::MIN_ATTEMPTS - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::min(
            $this->securityPolicyData['attempts'],
            SecurityPolicy::MIN_ATTEMPTS,
            'SecurityPolicy::attempts'
        )->getMessage());

        DbSecurityPolicyFactory::createFromRecord($this->securityPolicyData);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid attempts number.
     */
    public function testAttemptsTooHighException(): void
    {
        $this->securityPolicyData['attempts'] =
            SecurityPolicy::MAX_ATTEMPTS + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::max(
            $this->securityPolicyData['attempts'],
            SecurityPolicy::MAX_ATTEMPTS,
            'SecurityPolicy::attempts'
        )->getMessage());

        DbSecurityPolicyFactory::createFromRecord($this->securityPolicyData);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid blocking duration.
     */
    public function testBlockingDurationTooSmallException(): void
    {
        $this->securityPolicyData['blocking_duration'] =
            SecurityPolicy::MIN_BLOCKING_DURATION - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::min(
            $this->securityPolicyData['blocking_duration'],
            SecurityPolicy::MIN_BLOCKING_DURATION,
            'SecurityPolicy::blockingDuration'
        )->getMessage());

        DbSecurityPolicyFactory::createFromRecord($this->securityPolicyData);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid blocking duration.
     */
    public function testBlockingDurationTooHighException(): void
    {
        $this->securityPolicyData['blocking_duration'] =
            SecurityPolicy::MAX_BLOCKING_DURATION + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::max(
            $this->securityPolicyData['blocking_duration'],
            SecurityPolicy::MAX_BLOCKING_DURATION,
            'SecurityPolicy::blockingDuration'
        )->getMessage());

        DbSecurityPolicyFactory::createFromRecord($this->securityPolicyData);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid password expiration.
     */
    public function testPasswordExpirationTooSmallException(): void
    {
        $this->securityPolicyData['password_expiration'] =
            SecurityPolicy::MIN_PASSWORD_EXPIRATION - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::min(
            $this->securityPolicyData['password_expiration'],
            SecurityPolicy::MIN_PASSWORD_EXPIRATION,
            'SecurityPolicy::passwordExpiration'
        )->getMessage());

        DbSecurityPolicyFactory::createFromRecord($this->securityPolicyData);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid password expiration.
     */
    public function testPasswordExpirationTooHighException(): void
    {
        $this->securityPolicyData['password_expiration'] =
            SecurityPolicy::MAX_PASSWORD_EXPIRATION + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::max(
            $this->securityPolicyData['password_expiration'],
            SecurityPolicy::MAX_PASSWORD_EXPIRATION,
            'SecurityPolicy::passwordExpiration'
        )->getMessage());

        DbSecurityPolicyFactory::createFromRecord($this->securityPolicyData);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid delay before new password.
     */
    public function testDelayBeforeNewPasswordTooSmallException(): void
    {
        $this->securityPolicyData['delay_before_new_password'] =
            SecurityPolicy::MIN_NEW_PASSWORD_DELAY - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::min(
            $this->securityPolicyData['delay_before_new_password'],
            SecurityPolicy::MIN_NEW_PASSWORD_DELAY,
            'SecurityPolicy::delayBeforeNewPassword'
        )->getMessage());

        DbSecurityPolicyFactory::createFromRecord($this->securityPolicyData);
    }

    /**
     * Test that an exception is thrown when creating a Security Policy with invalid delay before new password.
     */
    public function testDelayBeforeNewPasswordTooHighException(): void
    {
        $this->securityPolicyData['delay_before_new_password'] =
            SecurityPolicy::MAX_NEW_PASSWORD_DELAY + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(AssertionException::max(
            $this->securityPolicyData['delay_before_new_password'],
            SecurityPolicy::MAX_NEW_PASSWORD_DELAY,
            'SecurityPolicy::delayBeforeNewPassword'
        )->getMessage());

        DbSecurityPolicyFactory::createFromRecord($this->securityPolicyData);
    }

    /**
     * Test that the SecurityPolicy is correctly created when valid data are sent.
     */
    public function testSecurityPolicyCorrectlyCreated(): void
    {
        $createdSecurityPolicy = DbSecurityPolicyFactory::createFromRecord($this->securityPolicyData);

        $this->assertEquals(
            $this->securityPolicyData['password_length'],
            $createdSecurityPolicy->getPasswordMinimumLength()
        );
        $this->assertEquals(
            $this->securityPolicyData['has_uppercase_characters'],
            $createdSecurityPolicy->hasUppercase()
        );
        $this->assertEquals(
            $this->securityPolicyData['has_lowercase_characters'],
            $createdSecurityPolicy->hasLowercase()
        );
        $this->assertEquals(
            $this->securityPolicyData['has_numbers'],
            $createdSecurityPolicy->hasNumber()
        );
        $this->assertEquals(
            $this->securityPolicyData['has_special_characters'],
            $createdSecurityPolicy->hasSpecialCharacter()
        );
        $this->assertEquals(
            $this->securityPolicyData['can_reuse_passwords'],
            $createdSecurityPolicy->canReusePasswords()
        );
        $this->assertEquals(
            $this->securityPolicyData['attempts'],
            $createdSecurityPolicy->getAttempts()
        );
        $this->assertEquals(
            $this->securityPolicyData['blocking_duration'],
            $createdSecurityPolicy->getBlockingDuration()
        );
        $this->assertEquals(
            $this->securityPolicyData['password_expiration'],
            $createdSecurityPolicy->getPasswordExpiration()
        );
        $this->assertEquals(
            $this->securityPolicyData['delay_before_new_password'],
            $createdSecurityPolicy->getDelayBeforeNewPassword()
        );
    }
}
