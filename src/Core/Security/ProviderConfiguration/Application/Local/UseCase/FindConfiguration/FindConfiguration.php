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

namespace Core\Security\ProviderConfiguration\Application\Local\UseCase\FindConfiguration;

use Centreon\Domain\Log\LoggerTrait;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\ProviderConfiguration\Domain\Local\Model\CustomConfiguration;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;

class FindConfiguration
{
    use LoggerTrait;

    /**
     * @param ProviderAuthenticationFactoryInterface $providerFactory
     */
    public function __construct(private ProviderAuthenticationFactoryInterface $providerFactory)
    {
    }

    /**
     * @param FindConfigurationPresenterInterface $presenter
     */
    public function __invoke(FindConfigurationPresenterInterface $presenter): void
    {
        $this->debug('Searching for local provider configuration');

        try {
            $provider = $this->providerFactory->create(Provider::LOCAL);
            $configuration = $provider->getConfiguration();
        } catch (\Throwable $e) {
            $this->critical($e->getMessage());
            $presenter->setResponseStatus(
                new FindConfigurationErrorResponse($e->getMessage())
            );
            return;
        }

        $presenter->present($this->createResponse($configuration));
    }

    /**
     * @param Configuration $configuration
     * @return FindConfigurationResponse
     */
    public function createResponse(Configuration $configuration): FindConfigurationResponse
    {
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $configuration->getCustomConfiguration();
        $response = new FindConfigurationResponse();
        $response->passwordMinimumLength = $customConfiguration->getSecurityPolicy()->getPasswordMinimumLength();
        $response->hasUppercase = $customConfiguration->getSecurityPolicy()->hasUppercase();
        $response->hasLowercase = $customConfiguration->getSecurityPolicy()->hasLowercase();
        $response->hasNumber = $customConfiguration->getSecurityPolicy()->hasNumber();
        $response->hasSpecialCharacter = $customConfiguration->getSecurityPolicy()->hasSpecialCharacter();
        $response->canReusePasswords = $customConfiguration->getSecurityPolicy()->canReusePasswords();
        $response->attempts = $customConfiguration->getSecurityPolicy()->getAttempts();
        $response->blockingDuration = $customConfiguration->getSecurityPolicy()->getBlockingDuration();
        $response->passwordExpirationDelay = $customConfiguration->getSecurityPolicy()->getPasswordExpirationDelay();
        $response->passwordExpirationExcludedUserAliases =
            $customConfiguration
                ->getSecurityPolicy()
                ->getPasswordExpirationExcludedUserAliases();
        $response->delayBeforeNewPassword = $customConfiguration->getSecurityPolicy()->getDelayBeforeNewPassword();

        return $response;
    }
}
