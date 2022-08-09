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

namespace Core\Security\Authentication\Application\UseCase\LoginOpenIdSession;

use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Pimple\Container;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Menu\Model\Page;
use Security\Domain\Authentication\Model\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;
use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Infrastructure\Service\Exception\NotFoundException;
use Core\Security\Authentication\Domain\Exception\AuthenticationException;
use Core\Security\Authentication\Domain\Exception\SSOAuthenticationException;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Security\Domain\Authentication\Interfaces\OpenIdProviderInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\Configuration;
use Core\Contact\Application\Repository\WriteContactGroupRepositoryInterface;
use Core\Security\AccessGroup\Application\Repository\WriteAccessGroupRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\OpenIdConfigurationException;
use Centreon\Domain\Authentication\Exception\AuthenticationException as LegacyAuthenticationException;
use Core\Security\ProviderConfiguration\Application\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;

/**
 * @deprecated
 */
class LoginOpenIdSession
{
    use LoggerTrait;

    /**
     * @param string $redirectDefaultPage
     * @param ReadOpenIdConfigurationRepositoryInterface $repository
     * @param OpenIdProviderInterface $provider
     * @param RequestStack $requestStack
     * @param Container $dependencyInjector
     * @param AuthenticationServiceInterface $authenticationService
     * @param AuthenticationRepositoryInterface $authenticationRepository
     * @param SessionRepositoryInterface $sessionRepository
     * @param DataStorageEngineInterface $dataStorageEngine
     * @param WriteContactGroupRepositoryInterface $contactGroupRepository
     * @param WriteAccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        private string $redirectDefaultPage,
        private ReadOpenIdConfigurationRepositoryInterface $repository,
        private OpenIdProviderInterface $provider,
        private RequestStack $requestStack,
        private Container $dependencyInjector,
        private AuthenticationServiceInterface $authenticationService,
        private AuthenticationRepositoryInterface $authenticationRepository,
        private SessionRepositoryInterface $sessionRepository,
        private DataStorageEngineInterface $dataStorageEngine,
        private WriteContactGroupRepositoryInterface $contactGroupRepository,
        private WriteAccessGroupRepositoryInterface $accessGroupRepository,
    ) {
    }

    /**
     * @param LoginOpenIdSessionRequest $request
     * @param LoginOpenIdSessionPresenterInterface $presenter
     */
    public function __invoke(LoginOpenIdSessionRequest $request, LoginOpenIdSessionPresenterInterface $presenter): void
    {
        global $pearDB;
        $pearDB = $this->dependencyInjector['configuration_db'];

        try {
            $openIdProviderConfiguration = $this->repository->findConfiguration();
            if ($openIdProviderConfiguration === null) {
                throw new NotFoundException('Provider not found');
            }
            $this->provider->setConfiguration($openIdProviderConfiguration);
            $this->provider->authenticateOrFail($request->authorizationCode, $request->clientIp);
            $user = $this->findUserOrFail();
            $this->updateUserACL($user);
            $sessionUserInfos = [
                'contact_id' => $user->getId(),
                'contact_name' => $user->getName(),
                'contact_alias' => $user->getAlias(),
                'contact_email' => $user->getEmail(),
                'contact_lang' => $user->getLang(),
                'contact_passwd' => $user->getEncodedPassword(),
                'contact_autologin_key' => '',
                'contact_admin' => $user->isAdmin() ? '1' : '0',
                'default_page' => $user->getDefaultPage(),
                'contact_location' => $user->getLocale(),
                'show_deprecated_pages' => $user->isUsingDeprecatedPages(),
                'reach_api' => $user->hasAccessToApiConfiguration() ? 1 : 0,
                'reach_api_rt' => $user->hasAccessToApiRealTime() ? 1 : 0
            ];
            $this->provider->setLegacySession(new \Centreon($sessionUserInfos));
            $this->startLegacySession($this->provider->getLegacySession());

            /**
             * Search for an already existing and available authentications token.
             * Create a new one if no one are found.
             */
            $currentRequest = $this->requestStack->getCurrentRequest();
            if ($currentRequest !== null) {
                $authenticationTokens = $this->authenticationService->findAuthenticationTokensByToken(
                    $currentRequest->getSession()->getId()
                );

                if ($authenticationTokens === null) {
                    $this->createAuthenticationTokens(
                        $currentRequest->getSession()->getId(),
                        $user,
                        $this->provider->getProviderToken(),
                        $this->provider->getProviderRefreshToken(),
                        $request->clientIp,
                    );
                }
            }
        } catch (SSOAuthenticationException | NotFoundException | OpenIdConfigurationException $e) {
            $this->error('An unexpected error occurred while authenticating with OpenID', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $presenter->present($this->createResponse(null, $e->getMessage()));
            return;
        } catch (\Throwable $e) {
            $this->error('An unexpected error occurred while authenticating with OpenID', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $presenter->present($this->createResponse(
                null,
                'An unexpected error occurred while authenticating with OpenID'
            ));
            return;
        }

        $this->debug(
            "[AUTHENTICATE] Authentication success",
            [
                "provider_name" => Provider::OPENID,
                "contact_id" => $user->getId(),
                "contact_alias" => $user->getAlias()
            ]
        );

        /**
         * Define the redirection uri where user will be redirect once logged.
         */
        $redirectionUri = $this->getRedirectionUri($user);
        $presenter->present($this->createResponse($redirectionUri));
    }

    /**
     * Start the Centreon session.
     *
     * @param \Centreon $legacySession
     * @throws LegacyAuthenticationException
     */
    private function startLegacySession(\Centreon $legacySession): void
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest !== null) {
            $this->info('[AUTHENTICATE] Starting Centreon Session');
            $currentRequest->getSession()->start();
            $currentRequest->getSession()->set('centreon', $legacySession);
            $_SESSION['centreon'] = $legacySession;
        }
    }

    /**
     * Get the redirection uri where user will be redirect once logged.
     *
     * @param ContactInterface $providerUser
     * @return string
     */
    private function getRedirectionUri(
        ContactInterface $providerUser,
    ): string {
        if ($providerUser->getDefaultPage()?->getUrl() !== null) {
            return $this->buildDefaultRedirectionUri($providerUser->getDefaultPage());
        }

        return $this->redirectDefaultPage;
    }

    /**
     * build the redirection uri based on isReact page property.
     *
     * @param Page $defaultPage
     * @return string
     */
    private function buildDefaultRedirectionUri(Page $defaultPage): string
    {
        if ($defaultPage->isReact() === true) {
            return $defaultPage->getUrl();
        }
        $redirectUri = "/main.php?p=" . $defaultPage->getPageNumber();
        if ($defaultPage->getUrlOptions() !== null) {
            $redirectUri .= $defaultPage->getUrlOptions();
        }

        return $redirectUri;
    }

    /**
     * create Authentication tokens.
     *
     * @param string $sessionToken
     * @param ContactInterface $contact
     * @param ProviderToken $providerToken
     * @param ProviderToken|null $providerRefreshToken
     * @param string|null $clientIp
     */
    private function createAuthenticationTokens(
        string $sessionToken,
        ContactInterface $contact,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken,
        ?string $clientIp,
    ): void {
        $isAlreadyInTransaction = $this->dataStorageEngine->isAlreadyinTransaction();

        if (!$isAlreadyInTransaction) {
            $this->dataStorageEngine->startTransaction();
        }
        try {
            $session = new Session($sessionToken, $contact->getId(), $clientIp);
            $this->sessionRepository->addSession($session);
            $this->authenticationRepository->addAuthenticationTokens(
                $sessionToken,
                $this->provider->getConfiguration()->getId(),
                $contact->getId(),
                $providerToken,
                $providerRefreshToken
            );
            if (!$isAlreadyInTransaction) {
                $this->dataStorageEngine->commitTransaction();
            }
        } catch (\Exception $ex) {
            if (!$isAlreadyInTransaction) {
                $this->dataStorageEngine->rollbackTransaction();
            }
            throw AuthenticationException::notAuthenticated();
        }
    }

    /**
     * @param string|null $redirectUri
     * @param string|null $error
     * @return LoginOpenIdSessionResponse
     */
    private function createResponse(?string $redirectUri, ?string $error = null): LoginOpenIdSessionResponse
    {
        $response = new LoginOpenIdSessionResponse();
        $response->redirectUri = $redirectUri;
        $response->error = $error;

        return $response;
    }

    /**
     * Find User in Centreon or throw an exception
     *
     * @return ContactInterface
     * @throws NotFoundException
     */
    private function findUserOrFail(): ContactInterface
    {
        $user = $this->provider->getUser();
        if ($user === null) {
            $this->info("User not found");
            if (! $this->provider->canCreateUser()) {
                throw new NotFoundException('User could not be created');
            }
            $this->info("Start auto import");
            $this->provider->createUser();
            $user = $this->provider->getUser();
            if ($user === null) {
                throw new NotFoundException('User not found');
            }
            $this->info("User imported: " . $user->getName());
        }

        return $user;
    }

    /**
     * Update User ACL On authentication
     *
     * @param ContactInterface $user
     */
    private function updateUserACL(ContactInterface $user): void
    {
        $userClaims = $this->getUserClaimsFromIdTokenOrUserInformation();
        $userAccessGroups = $this->getUserAccessGroupsFromClaims($userClaims);
        $this->updateAccessGroupsForUser($user, $userAccessGroups);
        $this->updateContactGroupsForUser($user);
    }

    /**
     * Parse Id Token and User Information to get claims
     *
     * @return string[]
     */
    private function getUserClaimsFromIdTokenOrUserInformation(): array
    {
        $userClaims = [];
        $configuration = $this->provider->getConfiguration();
        $idTokenPayload = $this->provider->getIdTokenPayload();
        $userInformation = $this->provider->getUserInformation();
        if (array_key_exists($configuration->getClaimName(), $idTokenPayload)) {
            $userClaims = $idTokenPayload[$configuration->getClaimName()];
        } elseif (array_key_exists($configuration->getClaimName(), $userInformation)) {
            $userClaims = $userInformation[$configuration->getClaimName()];
        } else {
            $this->info(
                "configured claim name not found in user information or id_token, " .
                "default contact group ACL will be apply",
                ["claim_name" => $configuration->getClaimName()]
            );
        }

        /**
         * Claims can sometime be listed as a string e.g: "claim1,claim2,claim3" so we explode
         * them to handle only one format
         */
        if (is_string($userClaims)) {
            $userClaims = explode(",", $userClaims);
        }

        $this->info("Claims found", [
            "claims_value" => implode(", ", $userClaims),
            "claim_name" => $configuration->getClaimName()
        ]);

        return $userClaims;
    }

    /**
     * Get Access Group linked to user claims
     *
     * @param string[] $claims
     * @return AccessGroup[]
     */
    private function getUserAccessGroupsFromClaims(array $claims): array
    {
        $userAccessGroups = [];
        $configuration = $this->provider->getConfiguration();
        foreach ($configuration->getAuthorizationRules() as $authorizationRule) {
            if (! in_array($authorizationRule->getClaimValue(), $claims)) {
                $this->info(
                    "Configured Claim Value not found in user claims",
                    ["claim_value" => $authorizationRule->getClaimValue()]
                );

                continue;
            }
            // We ensure here to not duplicate access group while using their id as index
            $userAccessGroups[$authorizationRule->getAccessGroup()->getId()] = $authorizationRule->getAccessGroup();
        }
        return $userAccessGroups;
    }

    /**
     * Delete and Insert Access Groups for authenticated user
     *
     * @param ContactInterface $user
     * @param AccessGroup[] $userAccessGroups
     */
    private function updateAccessGroupsForUser(ContactInterface $user, array $userAccessGroups): void
    {
        try {
            $this->info("Updating User Access Groups", [
                "user_id" => $user->getId(),
                "access_groups" => $userAccessGroups
            ]);
            $this->dataStorageEngine->startTransaction();
            $this->accessGroupRepository->deleteAccessGroupsForUser($user);
            $this->accessGroupRepository->insertAccessGroupsForUser($user, $userAccessGroups);
            $this->dataStorageEngine->commitTransaction();
        } catch (\Exception $ex) {
            $this->dataStorageEngine->rollbackTransaction();
            $this->error('Error during ACL update', [
                "user_id" => $user->getId(),
                "access_groups" => $userAccessGroups,
                "trace" => $ex->getTraceAsString()
            ]);
        }
    }

    /**
     * Delete and Insert Contact Group for authenticated user
     *
     * @param ContactInterface $user
     */
    private function updateContactGroupsForUser(ContactInterface $user): void
    {
        $contactGroup = $this->provider->getConfiguration()->getContactGroup();
        try {
            $this->info('Updating User Contact Group', [
                "user_id" => $user->getId(),
                "contact_group_id" => $contactGroup->getId(),
            ]);
            $this->dataStorageEngine->startTransaction();
            $this->contactGroupRepository->deleteContactGroupsForUser($user);
            $this->contactGroupRepository->insertContactGroupForUser($user, $contactGroup);
            $this->dataStorageEngine->commitTransaction();
        } catch (\Exception $ex) {
            $this->dataStorageEngine->rollbackTransaction();
            $this->error('Error during contact group update', [
                "user_id" => $user->getId(),
                "contact_group_id" => $contactGroup->getId(),
                "trace" => $ex->getTraceAsString()
            ]);
        }
    }
}
