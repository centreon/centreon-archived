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
use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\LocalProviderInterface;

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
     * @var AuthenticationRepositoryInterface
     */
    private $authenticationRepository;

    /**
     * @param AuthenticationServiceInterface $authenticationService
     */
    public function __construct(
        AuthenticationServiceInterface $authenticationService,
        ProviderServiceInterface $providerService,
        AuthenticationRepositoryInterface $authenticationRepository
    ) {
        $this->authenticationService = $authenticationService;
        $this->providerService = $providerService;
        $this->authenticationRepository = $authenticationRepository;
    }

    /**
     * @param AuthenticateApiRequest $request
     * @throws ProviderException
     * @throws AuthenticationException
     */
    public function execute(AuthenticateApiRequest $request, AuthenticateApiResponse $response): void
    {
        $this->info(sprintf("[AUTHENTICATE API] Beginning API authentication for contact '%s'", $request->getLogin()));
        $this->deleteExpiredToken();

        /**
         * @var LocalProviderInterface
         */
        $localProvider = $this->findLocalProviderOrFail();
        $this->authenticateOrFail($localProvider, $request);

        $contact = $this->getUserFromProviderOrFail($localProvider);
        $token = Encryption::generateRandomString();
        $this->createAPIAuthenticationTokens(
            $token,
            $localProvider->getConfiguration(),
            $contact,
            $localProvider->getProviderToken($token),
            null
        );
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
        } catch (AuthenticationException $ex) {
            $this->notice('[AUTHENTICATE API] Unable to delete expired security tokens');
        }
    }

    /**
     * Find the local provider or throw an Exception.
     *
     * @return ProviderInterface
     * @throws ProviderException
     */
    private function findLocalProviderOrFail(): ProviderInterface
    {
        $localProvider = $this->providerService->findProviderByConfigurationName(LocalProvider::NAME);

        if ($localProvider === null) {
            throw ProviderException::providerConfigurationNotFound(LocalProvider::NAME);
        }

        return $localProvider;
    }

    /**
     * Authenticate the user or throw an Exception.
     *
     * @param LocalProviderInterface $localProvider
     * @param AuthenticateApiRequest $request
     * @throws AuthenticationException
     */
    private function authenticateOrFail(ProviderInterface $localProvider, AuthenticateApiRequest $request): void
    {
        /**
         * Authenticate with the legacy mechanism encapsulated into the Local Provider.
         */
        $this->debug('[AUTHENTICATE API] Authentication using provider', ['provider_name' => LocalProvider::NAME]);
        $localProvider->authenticateOrFail(
            [
                'login' => $request->getLogin(),
                'password' => $request->getPassword(),
            ],
        );
    }

    /**
     * Retrieve user from provider or throw an Exception.
     *
     * @param LocalProviderInterface $localProvider
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
     * @inheritDoc
     */
    public function createAPIAuthenticationTokens(
        string $token,
        ProviderConfiguration $providerConfiguration,
        ContactInterface $contact,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void {
        $this->debug(
            '[AUTHENTICATE API] Creating authentication tokens for user',
            ['user' => $contact->getAlias()]
        );
        if ($providerConfiguration->getId() === null) {
            throw new \InvalidArgumentException("Provider configuration can't be null");
        }
        try {
            $this->authenticationRepository->addAuthenticationTokens(
                $token,
                $providerConfiguration->getId(),
                $contact->getId(),
                $providerToken,
                $providerRefreshToken
            );
        } catch (\Exception $ex) {
            throw AuthenticationException::addAuthenticationToken($ex);
        }
    }
}
