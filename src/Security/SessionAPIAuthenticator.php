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
use Symfony\Component\Security\Core\Exception\SessionUnavailableException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
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
        return $request->headers->has('Cookie') && $request->cookies->has('PHPSESSID');
    }

    /**
     * Called when authentication executed, but failed (e.g. wrong username password).
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the login page or a 403 response.
     *
     * If you return null, the request will continue, but the user will
     * not be authenticated. This is probably not what you want to do.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication executed and was successful!
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the last page they visited.
     *
     * If you return null, the current request will continue, and the user
     * will be authenticated. This makes sense, for example, with an API.
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey The provider (i.e. firewall) key
     *
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
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
        $apiToken = $request->cookies->get('PHPSESSID');
        if (null === $apiToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new SessionUnavailableException('Session id not provided');
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $apiToken,
                function ($userIdentifier) {
                    return $this->getUserAndUpdateSession($userIdentifier);
                }
            )
        );
    }

    /**
     * Return a UserInterface object based on session id provided.
     *
     * @param string $apiToken
     *
     * @return UserInterface
     * @throws TokenNotFoundException
     * @throws CredentialsExpiredException
     * @throws ContactDisabledException
     */
    private function getUserAndUpdateSession(string $sessionId): UserInterface
    {
        $this->authenticationService->isValidToken($sessionId);
        $this->sessionRepository->deleteExpiredSession();

        $contact = $this->contactRepository->findBySession($sessionId);
        if ($contact === null) {
            throw new UserNotFoundException();
        }
        if (!$contact->isActive()) {
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
