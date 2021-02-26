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

namespace Security\Domain\Authentication;

use Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface;
use Security\Domain\Authentication\Exceptions\AuthenticationManagerException;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Guard\Token\PreAuthenticationGuardToken;

/**
 * @package Security
 */
class AuthenticationManager implements AuthenticationManagerInterface
{

    /**
     * @var bool
     */
    private $eraseCredentials;
    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepository;
    /**
     * @var AuthenticationRepositoryInterface
     */
    private $authenticationRepository;
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param AuthenticationRepositoryInterface $authenticationRepository
     * @param AuthenticationServiceInterface $authenticationService
     * @param ContactRepositoryInterface $contactRepository
     * @param bool $eraseCredentials
     */
    public function __construct(
        AuthenticationRepositoryInterface $authenticationRepository,
        AuthenticationServiceInterface $authenticationService,
        ContactRepositoryInterface $contactRepository,
        bool $eraseCredentials = true
    ) {
        $this->eraseCredentials = $eraseCredentials;
        $this->contactRepository = $contactRepository;
        $this->authenticationRepository = $authenticationRepository;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param TokenInterface $token
     * @return PreAuthenticatedToken|TokenInterface|null
     * @throws AuthenticationManagerException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function authenticate(TokenInterface $token)
    {
        if (array_key_exists('token', $token->getCredentials())) { // replace token by session
            $sessionToken = $token->getCredentials()['token'];
        } elseif (array_key_exists('session', $token->getCredentials())) {
            $sessionToken = $token->getCredentials()['session'];
        } else {
            throw AuthenticationManagerException::sessionTokenNotFoundException();
        }
        $authenticationToken = $this->authenticationService->findAuthenticationTokenBySessionToken($sessionToken);
        if ($authenticationToken === null) {
            throw AuthenticationManagerException::sessionNotFoundException();
        }

        $provider = $this->authenticationService->findProviderByConfigurationId(
            $authenticationToken->getConfigurationProviderId()
        );

        if ($provider === null) {
            throw AuthenticationManagerException::providerNotFoundException();
        }

        if ($authenticationToken->getProviderToken()->isExpired()) {
            if (!$provider->canRefreshToken() || $authenticationToken->getProviderRefreshToken()->isExpired()) {
                throw AuthenticationManagerException::sessionExpiredException();
            }
            $newAuthenticationToken = $provider->refreshToken($authenticationToken);
            if ($newAuthenticationToken === null) {
                throw AuthenticationManagerException::refreshTokenException();
            }
            $this->authenticationService->updateAuthenticationToken($newAuthenticationToken);
        }

        $user = $this->contactRepository->findById($authenticationToken->getUserId());
        if ($user === null) {
            throw AuthenticationManagerException::userNotFoundException();
        }

        return new PreAuthenticatedToken(
            $user,
            $token->getCredentials(),
            $provider->getName(),
            $user->getRoles()
        );
    }
}
