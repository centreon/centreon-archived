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
use Tests\Core\Domain\Security\Model\SecurityPolicyTest;
use Core\Application\Security\Repository\ReadSecurityPolicyRepositoryInterface;
use Core\Application\Security\UseCase\FindSecurityPolicy\FindSecurityPolicy;

class FindSecurityPolicyTest extends TestCase
{
    /**
     * @var ReadSecurityPolicyRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $repository;

    public function setUp(): void
    {
        $this->repository = $this->createMock(ReadSecurityPolicyRepositoryInterface::class);
    }

    /**
     * Test that the use case will correctly pass the security policy to the presenter.
     */
    public function testFindSecurityPolicy(): void
    {
        $securityPolicy = SecurityPolicyTest::createSecurityPolicyModel();

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
}
