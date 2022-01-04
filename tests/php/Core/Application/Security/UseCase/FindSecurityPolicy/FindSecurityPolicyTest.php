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

namespace Tests\Core\Application\Security\UseCase\FindSecurityPolicy;

use PHPUnit\Framework\TestCase;
use Core\Domain\Security\Model\SecurityPolicy;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\Security\UseCase\FindSecurityPolicy\FindSecurityPolicy;
use Core\Application\Security\Repository\ReadSecurityPolicyRepositoryInterface;
use Core\Application\Security\UseCase\FindSecurityPolicy\FindSecurityPolicyErrorResponse;
use Core\Infrastructure\Security\Api\FindSecurityPolicy\FindSecurityPolicyPresenter;
use Tests\Core\Application\Security\UseCase\FindSecurityPolicy\FindSecurityPolicyPresenterFake;

class FindSecurityPolicyTest extends TestCase
{
    /**
     * @var ReadSecurityPolicyRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $repository;

    /**
     * @var PresenterFormatterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenterFormatter;

    public function setUp(): void
    {
        $this->repository = $this->createMock(ReadSecurityPolicyRepositoryInterface::class);
        $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
    }

    /**
     * Test that the use case will correctly pass the security policy to the presenter.
     */
    public function testFindSecurityPolicy(): void
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

        $useCase = new FindSecurityPolicy($this->repository);

        $presenter = new FindSecurityPolicyPresenterFake();

        $this->repository
            ->expects($this->once())
            ->method('findSecurityPolicy')
            ->willReturn($securityPolicy);

        $useCase($presenter);

        $this->assertEquals($presenter->response->passwordMinimumLength, $securityPolicy->getPasswordMinimumLength());
        $this->assertEquals($presenter->response->hasUppercase, $securityPolicy->hasUppercase());
        $this->assertEquals($presenter->response->hasLowercase, $securityPolicy->hasLowercase());
        $this->assertEquals($presenter->response->hasNumber, $securityPolicy->hasNumber());
        $this->assertEquals($presenter->response->hasSpecialCharacter, $securityPolicy->hasSpecialCharacter());
        $this->assertEquals($presenter->response->canReusePassword, $securityPolicy->canReusePassword());
        $this->assertEquals($presenter->response->attempts, $securityPolicy->getAttempts());
        $this->assertEquals($presenter->response->blockingDuration, $securityPolicy->getBlockingDuration());
        $this->assertEquals($presenter->response->passwordExpiration, $securityPolicy->getPasswordExpiration());
        $this->assertEquals($presenter->response->delayBeforeNewPassword, $securityPolicy->getDelayBeforeNewPassword());
    }

    /**
     * Test that an error message is returned by the API when no security policy was found.
     */
    public function testFindSecurityPolicyError(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findSecurityPolicy')
            ->willReturn(null);
        $useCase = new FindSecurityPolicy($this->repository);
        $presenter = new FindSecurityPolicyPresenter($this->presenterFormatter);

        $useCase($presenter);

        $this->assertEquals(
            $presenter->getResponseStatus(),
            new FindSecurityPolicyErrorResponse(
                'Security policy not found. Please verify that your installation is valid'
            )
        );
    }
}
