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

use Security\Encryption;
use Centreon\Domain\Log\LoggerTrait;
use Security\Domain\Authentication\Model\LocalProvider;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;

class AuthenticateApi
{
    use LoggerTrait;

    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @var ProviderServiceInterface
     */
    private $providerService;

    /**
     * @param AuthenticationServiceInterface $authenticationService
     */
    public function __construct(
        AuthenticationServiceInterface $authenticationService,
        ProviderServiceInterface $providerService
    ) {
        $this->authenticationService = $authenticationService;
        $this->providerService = $providerService;
    }

    /**
     * @param AuthenticateApiRequest $request
     * @return AuthenticateApiResponse
     * @throws ProviderServiceException
     * @throws AuthenticationServiceException
     * @throws AuthenticationException
     */
    public function execute(AuthenticateApiRequest $request): AuthenticateApiResponse
    {
        try {
            $this->authenticationService->deleteExpiredAPITokens();
        } catch (AuthenticationServiceException $ex) {
            // We don't propagate this error
        }
        $localProvider = $this->providerService->findProviderByConfigurationName(LocalProvider::NAME);

        if ($localProvider === null) {
            throw ProviderServiceException::providerConfigurationNotFound(LocalProvider::NAME);
        }
        $this->info('Authentication using provider', ['provider_name' => LocalProvider::NAME]);
        $localProvider->authenticate(['login' => $request->getLogin(), 'password' => $request->getPassword()]);

        if (!$localProvider->isAuthenticated()) {
            $this->warning(
                "Provider can't authenticate successfully user ",
                [
                    "provider_name" => $localProvider->getName(),
                    "user" => $request->getLogin()
                ]
            );
            throw AuthenticationException::notAuthenticated();
        }

        $this->info('Retrieving user informations from provider');
        $contact = $localProvider->getUser();
        if ($contact === null) {
            $this->error('No contact could be found from provider', ['provider_name' => LocalProvider::NAME]);
            throw AuthenticationException::userNotFound();
        }
        $token = Encryption::generateRandomString();

        $this->info('Creating authentication tokens for user', ['user' => $contact->getAlias()]);
        $this->authenticationService->createAPIAuthenticationTokens(
            $token,
            $contact,
            $localProvider->getProviderToken($token),
            null
        );

        $response = new AuthenticateApiResponse();
        $response->setApiAuthentication($contact, $token);
        $this->info(
            "Authentication success",
            [
                "provider_name" => LocalProvider::NAME,
                "contact_id" => $contact->getId(),
                "contact_alias" => $contact->getAlias()
            ]
        );
        return $response;
    }
}
