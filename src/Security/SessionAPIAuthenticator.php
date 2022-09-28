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

namespace Security;

use Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface;
use Centreon\Domain\Exception\ContactDisabledException;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\SessionUnavailableException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * Class used to authenticate a request by using a session id.
 *
 * @package Security
 */
class SessionAPIAuthenticator extends AbstractAuthenticator
{
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepository;

    /**
     * @var SessionRepositoryInterface
     */
    private $sessionRepository;

    /**
     * SessionAPIAuthenticator constructor.
     *
     * @param AuthenticationServiceInterface $authenticationService
     * @param ContactRepositoryInterface $contactRepository
     * @param SessionRepositoryInterface $sessionRepository
     */
    public function __construct(
        AuthenticationServiceInterface $authenticationService,
        ContactRepositoryInterface $contactRepository,
        SessionRepositoryInterface $sessionRepository
    ) {
        $this->authenticationService = $authenticationService;
        $this->contactRepository = $contactRepository;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request): bool
    {
        return $request->headers->has('Cookie') && ! empty($request->getSession()->getId());
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @return SelfValidatingPassport
     * @throws CustomUserMessageAuthenticationException
     * @throws TokenNotFoundException
     */
    public function authenticate(Request $request): SelfValidatingPassport
    {
        /**
         * @var string|null $sessionId
         */
        $sessionId = $request->getSession()->getId();
        if (null === $sessionId) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new SessionUnavailableException('Session id not provided');
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $sessionId,
                function ($userIdentifier) {
                    return $this->getUserAndUpdateSession($userIdentifier);
                }
            )
        );
    }

    /**
     * Return a UserInterface object based on session id provided.
     *
     * @param string $sessionId
     *
     * @return UserInterface
     * @throws BadCredentialsException
     * @throws SessionUnavailableException
     * @throws ContactDisabledException
     */
    private function getUserAndUpdateSession(string $sessionId): UserInterface
    {
        $isValidToken = $this->authenticationService->isValidToken($sessionId);

        $this->authenticationService->deleteExpiredSecurityTokens();
        $this->sessionRepository->deleteExpiredSession();

        if (! $isValidToken) {
            throw new BadCredentialsException();
        }

        $contact = $this->contactRepository->findBySession($sessionId);
        if ($contact === null) {
            throw new SessionUnavailableException();
        }
        if ($contact->isActive() === false) {
            throw new ContactDisabledException();
        }

        // force api access to true cause it comes from session
        $contact
            ->setAccessToApiRealTime(true)
            ->setAccessToApiConfiguration(true);

        // It's an internal API call, so this contact has all roles
        return $contact;
    }
}
