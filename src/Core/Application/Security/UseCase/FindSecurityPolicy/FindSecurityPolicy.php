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

namespace Core\Application\Security\UseCase\FindSecurityPolicy;

use Core\Application\Security\Exception\SecurityPolicyException;
use Core\Application\Security\Repository\ReadSecurityPolicyRepositoryInterface;
use Core\Application\Security\UseCase\FindSecurityPolicy\FindSecurityPolicyPresenterInterface;
use Core\Domain\Security\Model\SecurityPolicy;

class FindSecurityPolicy
{
    /**
     * @param ReadSecurityPolicyRepositoryInterface $repository
     */
    public function __construct(private ReadSecurityPolicyRepositoryInterface $repository)
    {
    }

    /**
     * @param FindSecurityPolicyPresenterInterface $presenter
     */
    public function __invoke(FindSecurityPolicyPresenterInterface $presenter): void
    {
        $securityPolicy = $this->repository->findSecurityPolicy();
        if ($securityPolicy === null) {
            throw SecurityPolicyException::securityPolicyNotFound();
        }
        $presenter->present($this->createResponse($securityPolicy));
    }

    /**
     * @param SecurityPolicy $securityPolicy
     * @return FindSecurityPolicyResponse
     */
    public function createResponse(SecurityPolicy $securityPolicy): FindSecurityPolicyResponse
    {
        $response = new FindSecurityPolicyResponse();
        $response->passwordMinimumLength = $securityPolicy->getPasswordMinimumLength();
        $response->hasUppercase = $securityPolicy->hasUppercase();
        $response->hasLowercase = $securityPolicy->hasLowercase();
        $response->hasNumber = $securityPolicy->hasNumber();
        $response->hasSpecialCharacter = $securityPolicy->hasSpecialCharacter();
        $response->canReusePassword = $securityPolicy->canReusePassword();
        $response->attempts = $securityPolicy->getAttempts();
        $response->blockingDuration = $securityPolicy->getBlockingDuration();
        $response->passwordExpiration = $securityPolicy->getPasswordExpiration();
        $response->delayBeforeNewPassword = $securityPolicy->getDelayBeforeNewPassword();

        return $response;
    }
}
