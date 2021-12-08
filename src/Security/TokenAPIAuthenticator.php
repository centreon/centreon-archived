<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Centreon\Domain\Exception\ContactDisabledException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
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
     * Returns a response that directs the user to authenticate.
     *
     * This is called when an anonymous request accesses a resource that
     * requires authentication. The job of this method is to return some
     * response that "helps" the user start into the authentication process.
     *
     * Examples:
     *
     * - For a form login, you might redirect to the login page
     *
     *     return new RedirectResponse('/login');
     *
     * - For an API token authentication system, you return a 401 response
     *
     *     return new Response('Auth header required', 401);
     *
     * @param Request $request The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     *
     * @return Response
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
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    /**
     * Get the authentication credentials from the request and return them
     * as any type (e.g. an associate array).
     *
     * Whatever value you return here will be passed to getUser() and checkCredentials()
     *
     * For example, for a form login, you might:
     *
     *      return [
     *          'username' => $request->request->get('_username'),
     *          'password' => $request->request->get('_password'),
     *      ];
     *
     * Or for an API token that's on a header, you might use:
     *
     *      return ['api_key' => $request->headers->get('X-API-TOKEN')];
     *
     * @param Request $request
     *
     * @return mixed Any non-null value
     *
     * @throws \UnexpectedValueException If null is returned
     */
    public function getCredentials(Request $request)
    {
        return [
            'token' => $request->headers->get('X-AUTH-TOKEN'),
        ];
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface|null
     * @throws \Exception
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $apiToken = $credentials['token'];
        if (null === $apiToken) {
            return null;
        }
        $tokens = $this->authenticationRepository->findAuthenticationTokensByToken($apiToken);

        if (is_null($tokens)) {
            throw new TokenNotFoundException();
        }

        $providerToken = $tokens->getProviderToken();
        $expirationDate = $providerToken->getExpirationDate();
        if ($expirationDate !== null && $expirationDate->getTimestamp() < time()) {
            throw new CredentialsExpiredException();
        }

        $contact = $this->contactRepository->findById($tokens->getUserId());
        if (isset($contact) && $contact->isActive() === false) {
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

    /**
     * Returns true if the credentials are valid.
     *
     * If any value other than true is returned, authentication will
     * fail. You may also throw an AuthenticationException if you wish
     * to cause authentication to fail.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * @param mixed $credentials
     * @param UserInterface $user
     *
     * @return bool
     *
     * @throws AuthenticationException
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
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
     * Does this method support remember me cookies?
     *
     * Remember me cookie will be set if *all* of the following are met:
     *  A) This method returns true
     *  B) The remember_me key under your firewall is configured
     *  C) The "remember me" functionality is activated. This is usually
     *      done by having a _remember_me checkbox in your form, but
     *      can be configured by the "always_remember_me" and "remember_me_parameter"
     *      parameters under the "remember_me" firewall key
     *  D) The onAuthenticationSuccess method returns a Response object
     *
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('X-AUTH-TOKEN');
        if (null === $apiToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }
        $contact = $this->contactRepository->findByAuthenticationToken($apiToken);
        $contact->setToken($apiToken);
        return new SelfValidatingPassport(new UserBadge($contact->getUserIdentifier()));
    }
}
