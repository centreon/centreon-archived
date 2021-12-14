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
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * Class used to authenticate a request by using a security token.
 *
 * @package Security
 */
class TokenAPIAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public const EXPIRATION_DELAY = 120;

    /**
     * @var AuthenticationRepositoryInterface
     */
    private $authenticationRepository;

    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepository;

    /**
     * @var OptionServiceInterface
     */
    private $optionService;

    /**
     * TokenAPIAuthenticator constructor.
     *
     * @param AuthenticationRepositoryInterface $authenticationRepository
     * @param ContactRepositoryInterface $contactRepository
     * @param OptionServiceInterface $optionService
     */
    public function __construct(
        AuthenticationRepositoryInterface $authenticationRepository,
        ContactRepositoryInterface $contactRepository,
        OptionServiceInterface $optionService
    ) {
        $this->authenticationRepository = $authenticationRepository;
        $this->contactRepository = $contactRepository;
        $this->optionService = $optionService;
    }

    /**
     * @inheritDoc
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            // you might translate this message
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request): bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => 'ahah' . strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * @inheritDoc
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
        $apiToken = $request->headers->get('X-AUTH-TOKEN');
        if (null === $apiToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new TokenNotFoundException('API token not provided');
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $apiToken,
                function ($userIdentifier) {
                    return $this->getUserAndUpdateToken($userIdentifier);
                }
            )
        );
    }

    /**
     * Return a UserInterface object based on the token provided.
     *
     * @param string $apiToken
     *
     * @return UserInterface
     * @throws TokenNotFoundException
     * @throws CredentialsExpiredException
     * @throws ContactDisabledException
     */
    private function getUserAndUpdateToken(string $apiToken): UserInterface
    {
        $tokens = $this->authenticationRepository->findAuthenticationTokensByToken($apiToken);
        if ($tokens === null) {
            throw new TokenNotFoundException();
        }

        $providerToken = $tokens->getProviderToken();
        $expirationDate = $providerToken->getExpirationDate();
        if ($expirationDate !== null && $expirationDate->getTimestamp() < time()) {
            throw new CredentialsExpiredException();
        }

        $contact = $this->contactRepository->findById($tokens->getUserId());
        if ($contact === null) {
            throw new UserNotFoundException();
        }
        if (!$contact->isActive()) {
            throw new ContactDisabledException();
        }

        $expirationSessionDelay = self::EXPIRATION_DELAY;
        $sessionExpireOption = $this->optionService->findSelectedOptions(['session_expire']);
        if (!empty($sessionExpireOption)) {
            $expirationSessionDelay = (int) $sessionExpireOption[0]->getValue();
        }
        $providerToken->setExpirationDate(
            (new \DateTime())->add(new \DateInterval('PT' . $expirationSessionDelay . 'M'))
        );

        $this->authenticationRepository->updateProviderToken($providerToken);

        return $contact;
    }
}
