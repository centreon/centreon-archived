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

namespace Tests\Core\Application\Security\UseCase\UpdateSecurityPolicy;

use PHPUnit\Framework\TestCase;
use Core\Domain\Security\Model\SecurityPolicy;
use Core\Application\Security\Repository\WriteSecurityPolicyRepositoryInterface;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicy;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicyPresenterInterface;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicyRequest;

class UpdateSecurityPolicyTest extends TestCase
{
    /**
     * @var WriteSecurityPolicyRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $repository;

    /**
     * @var UpdateSecurityPolicyPresenterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenter;

    public function setUp(): void
    {
        $this->repository = $this->createMock(WriteSecurityPolicyRepositoryInterface::class);
        $this->presenter = $this->createMock(UpdateSecurityPolicyPresenterInterface::class);
    }

    /**
     * Test that the use case will correctly be executed.
     */
    public function testUpdateSecurityPolicy(): void
    {
        $securityPolicy = new SecurityPolicy(
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

        $request = new UpdateSecurityPolicyRequest();
        $request->passwordMinimumLength = SecurityPolicy::MIN_PASSWORD_LENGTH;
        $request->hasUppercase = true;
        $request->hasLowercase = true;
        $request->hasNumber = true;
        $request->hasSpecialCharacter = true;
        $request->canReusePassword = true;
        $request->attempts = SecurityPolicy::MIN_ATTEMPTS;
        $request->blockingDuration = SecurityPolicy::MIN_BLOCKING_DURATION;
        $request->passwordExpiration = SecurityPolicy::MIN_PASSWORD_EXPIRATION;
        $request->delayBeforeNewPassword = SecurityPolicy::MIN_NEW_PASSWORD_DELAY;

        $this->repository
            ->expects($this->once())
            ->method('updateSecurityPolicy')
            ->with($securityPolicy);

        $this->presenter
            ->expects($this->once())
            ->method('setResponseStatus');

        $useCase = new UpdateSecurityPolicy($this->repository);
        $useCase($this->presenter, $request);
    }
}
