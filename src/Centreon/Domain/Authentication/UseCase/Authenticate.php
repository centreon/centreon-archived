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

use Security\Domain\Authentication\Model\ProviderToken;
use Security\Domain\Authentication\AuthenticationService;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;

class Authenticate
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var ContactServiceInterface
     */
    private $contactService;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param AuthenticationService $authenticationService
     * @param ContactServiceInterface $contactService
     * @param SessionInterface $session
     * @param OptionServiceInterface $optionService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        ContactServiceInterface $contactService,
        SessionInterface $session,
        OptionServiceInterface $optionService
    ) {
        $this->authenticationService = $authenticationService;
        $this->contactService = $contactService;
        $this->session = $session;
        $this->optionService = $optionService;
    }

    /**
     * Execute authentication scenario
     *
     * @param AuthenticateRequest $request
     * @return void
     */
    public function execute(AuthenticateRequest $request): void
    {
        $authenticationProvider = $this->authenticationService->findProviderByConfigurationName(
            $request->getProviderConfigurationName()
        );

        if ($authenticationProvider === null) {
            throw AuthenticationServiceException::providerConfigurationNotFound(
                $request->getProviderConfigurationName()
            );
        }

        $authenticationProvider->authenticate($request->getCredentials());

        if (!$authenticationProvider->isAuthenticated()) {
            throw AuthenticationException::NotAuthenticatedException();
        }

        $providerUser = $authenticationProvider->getUser();
        if ($providerUser === null) {
            throw AuthenticationException::UserNotFoundException();
        }

        if (!$this->contactService->exists($providerUser)) {
            if ($authenticationProvider->canCreateUser()) {
                $this->contactService->addUser($providerUser);
            } else {
                throw AuthenticationException::CannotCreateUserException();
            }
        } else {
            $this->contactService->updateUser($providerUser);
        }

        $this->session->start();
        $_SESSION['centreon'] = $authenticationProvider->getLegacySession();

        /*
        @TODO: Maybe check here if the tokens exist (SAT,Security_token)
         If So, create a method updateAuthenticationsTokens(
            string $token (the session token),
            string $request->getProviderConfigurationName(),
            ContactInterface $providerUser,
            ProviderToken $providerToken,
            ?ProviderToken $providerRefreshToken)
        )
        */
        $authenticationTokens = $this->authenticationService->findAuthenticationTokensByToken($this->session->getId());
        if ($authenticationTokens === null) {
            $providerToken = $this->createProviderToken($this->session->getId());
        } else {
            $providerToken = $authenticationTokens->getProviderToken($authenticationTokens);
        }

        $this->authenticationService->createAuthenticationTokens(
            $this->session->getId(),
            $request->getProviderConfigurationName(),
            $providerUser,
            $providerToken,
            $authenticationProvider->getProviderRefreshToken($this->session->getId())
        );
    }

    private function createProviderToken(string $token): ProviderToken
    {
        $expirationSessionDelay = 120;
        $sessionExpireOption = $this->optionService->findSelectedOptions(['session_expire']);
        if (!empty($sessionExpireOption)) {
            $expirationSessionDelay = (int) $sessionExpireOption[0]->getValue();
        }
        return new ProviderToken(
            null,
            $token,
            new \DateTime(),
            (new \DateTime())->add(new \DateInterval('PT' . $expirationSessionDelay . 'M')),
            null
        );
    }
}
