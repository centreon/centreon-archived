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

namespace Core\Application\Security\UseCase\UpdateSecurityPolicy;

use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Core\Domain\Security\Model\SecurityPolicyFactory;
use Core\Application\Security\Repository\WriteSecurityPolicyRepositoryInterface;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicyRequest;

class UpdateSecurityPolicy
{
    use LoggerTrait;

    /**
     * @param WriteSecurityPolicyRepositoryInterface $repository
     */
    public function __construct(private WriteSecurityPolicyRepositoryInterface $repository)
    {
    }

    /**
     * @param UpdateSecurityPolicyPresenterInterface $presenter
     * @param UpdateSecurityPolicyRequest $request
     */
    public function __invoke(
        UpdateSecurityPolicyPresenterInterface $presenter,
        UpdateSecurityPolicyRequest $request
    ): void {
        $this->debug('Updating Security Policy');
        try {
            $securityPolicy = SecurityPolicyFactory::createFromRequest($request);
        } catch (AssertionException $ex) {
            $this->error('Unable to create Security Policy because of one or many parameters are invalid');
            $presenter->setResponseStatus(new ErrorResponse($ex->getMessage()));
        }

        $this->repository->updateSecurityPolicy($securityPolicy);
        $presenter->setResponseStatus(new NoContentResponse());
    }
}
