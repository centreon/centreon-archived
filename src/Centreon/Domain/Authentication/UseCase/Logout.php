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

namespace Centreon\Domain\Authentication\UseCase;

use Centreon\Domain\Authentication\UseCase\LogoutRequest;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;

class Logout
{
    use LoggerTrait;

    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @var AuthenticationRepositoryInterface
     */
    private $authenticationRepository;

    public function __construct(
        AuthenticationServiceInterface $authenticationService,
        AuthenticationRepositoryInterface $authenticationRepository
    ) {
        $this->authenticationService = $authenticationService;
        $this->authenticationRepository = $authenticationRepository;
    }

    /**
     * Execute the Logout Use Case.
     *
     * @param LogoutRequest $request
     * @throws AuthenticationException
     */
    public function execute(LogoutRequest $request): void
    {
        $this->info('Processing api logout...');
        $this->authenticationService->deleteExpiredSecurityTokens();
        $this->authenticationRepository->deleteSecurityToken($request->getToken());
    }
}
