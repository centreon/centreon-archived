<?php
/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Security\Authentication\Application\UseCase\Login;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\Common\UseCase\PresenterInterface;
use Core\Security\Authentication\Application\Provider\ProviderFactoryInterface;

class Login
{
    /**
     * @param ProviderFactoryInterface $providerFactory
     */
    public function __construct(private ProviderFactoryInterface $providerFactory)
    {
    }

    /**
     * @param LoginRequest $loginRequest
     * @param PresenterInterface $presenter
     * @return void
     */
    public function __invoke(LoginRequest $loginRequest, PresenterInterface $presenter): void
    {
        $provider = $this->providerFactory->create($loginRequest->getProviderName());

        $provider->authenticateOrFail($loginRequest);

        // TODO call the repo (ContactRepositoryRDB) to get the user
        $provider->findUserOrFail($provider->getUsername());
    }

    private function findUserOrFail(string $username): ContactInterface
    {
        $contact = $this->contactRepository->findByName($username);
        if ($contact === null) {
            if (! $this->provider->canAutoImport) {
                throw \Exception();
            }
            // autoimport
        }
        return $contact;
    }
}