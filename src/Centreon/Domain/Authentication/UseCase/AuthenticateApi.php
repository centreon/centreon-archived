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
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\ProviderInterface;

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
     * @throws ProviderServiceException
     * @throws AuthenticationServiceException
     * @throws AuthenticationException
     */
    public function execute(AuthenticateApiRequest $request, AuthenticateApiResponse $response): void
    {
        $this->info(sprintf("[AUTHENTICATE API] Beginning API authentication for contact '%s'", $request->getLogin()));
        $this->deleteExpiredToken();

        $localProvider = $this->findLocalProviderOrFail();
        $this->authenticateOrFail($localProvider, $request);

        $contact = $this->getUserFromProviderOrFail($localProvider);
        $token = Encryption::generateRandomString();
        $this->createApiAuthenticationTokenOrFail($token, $localProvider, $contact);
        $this->setResponseAuthentication($response, $contact, $token);
    }

    /**
     * Delete all expired Security tokens.
     */
    private function deleteExpiredToken(): void
    {
        /**
         * Remove all expired token before starting authentication process.
         */
        try {
            $this->authenticationService->deleteExpiredSecurityTokens();
        } catch (AuthenticationServiceException $ex) {
            $this->notice('[AUTHENTICATE API] Unable to delete expired security tokens');
        }
    }

    /**
     * Find the local provider or throw an Exception.
     *
     * @return ProviderInterface
     * @throws ProviderServiceException
     */
    private function findLocalProviderOrFail(): ProviderInterface
    {
        $localProvider = $this->providerService->findProviderByConfigurationName(LocalProvider::NAME);

        if ($localProvider === null) {
            throw ProviderServiceException::providerConfigurationNotFound(LocalProvider::NAME);
        }

        return $localProvider;
    }

    /**
     * Authenticate the user or throw an Exception.
     *
     * @param ProviderInterface $localProvider
     * @param AuthenticateApiRequest $request
     * @throws AuthenticationException
     */
    private function authenticateOrFail(ProviderInterface $localProvider, AuthenticateApiRequest $request): void
    {
        /**
         * Authenticate with the legacy mechanism encapsulated into the Local Provider.
         */
        $this->debug('[AUTHENTICATE API] Authentication using provider', ['provider_name' => LocalProvider::NAME]);
        $localProvider->authenticate(['login' => $request->getLogin(), 'password' => $request->getPassword()]);
        if (!$localProvider->isAuthenticated()) {
            $this->critical(
                "[AUTHENTICATE API] Provider can't authenticate successfully user ",
                [
                    "provider_name" => $localProvider->getName(),
                    "user" => $request->getLogin()
                ]
            );
            throw AuthenticationException::invalidCredentials();
        }
    }

    /**
     * Retrieve user from provider or throw an Exception.
     *
     * @param ProviderInterface $localProvider
     * @return ContactInterface
     * @throws AuthenticationException
     */
    private function getUserFromProviderOrFail(ProviderInterface $localProvider): ContactInterface
    {
        $this->info('[AUTHENTICATE API] Retrieving user informations from provider');
        $contact = $localProvider->getUser();

        /**
         * Contact shouldn't be null in this case as the LocalProvider::authenticate method check if the user exists.
         * But the ProviderInterface::getUser method could return a ContactInterface or null
         * so we need to do this check.
         */
        if ($contact === null) {
            $this->critical(
                '[AUTHENTICATE API] No contact could be found from provider',
                ['provider_name' => LocalProvider::NAME]
            );
            throw AuthenticationException::userNotFound();
        }

        return $contact;
    }

    /**
     * Create the authentication token or throw an Exception.
     *
     * @param string $token
     * @param ProviderInterface $localProvider
     * @param ContactInterface $contact
     * @return void
     */
    private function createApiAuthenticationTokenOrFail(
        string $token,
        ProviderInterface $localProvider,
        ContactInterface $contact
    ): void {
        /**
         * Create the token.
         */
        $this->authenticationService->createAPIAuthenticationTokens(
            $token,
            $localProvider->getConfiguration(),
            $contact,
            $localProvider->getProviderToken($token),
            null
        );
    }

    /**
     * Set the authentication to the response.
     *
     * @param AuthenticateApiResponse $response
     * @param ContactInterface $contact
     * @param string $token
     */
    private function setResponseAuthentication(
        AuthenticateApiResponse $response,
        ContactInterface $contact,
        string $token
    ): void {
        /**
         * Prepare the response with contact informations and API authentication token.
         */
        $response->setApiAuthentication($contact, $token);
        $this->debug(
            "[AUTHENTICATE API] Authentication success",
            [
                "provider_name" => LocalProvider::NAME,
                "contact_id" => $contact->getId(),
                "contact_alias" => $contact->getAlias()
            ]
        );
    }
}
