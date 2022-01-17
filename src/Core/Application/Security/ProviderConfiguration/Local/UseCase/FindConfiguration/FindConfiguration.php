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

namespace Core\Application\Security\ProviderConfiguration\Local\UseCase\FindConfiguration;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Repository\RepositoryException;
use Core\Application\Security\ProviderConfiguration\Local\Repository\ReadConfigurationRepositoryInterface;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\FindConfiguration\FindConfigurationPresenterInterface;
use Core\Domain\Security\ProviderConfiguration\Local\Model\Configuration;

class FindConfiguration
{
    use LoggerTrait;

    /**
     * @param ReadConfigurationRepositoryInterface $repository
     */
    public function __construct(private ReadConfigurationRepositoryInterface $repository)
    {
    }

    /**
     * @param FindConfigurationPresenterInterface $presenter
     */
    public function __invoke(FindConfigurationPresenterInterface $presenter): void
    {
        $this->debug('Searching for security policy');

        try {
            $configuration = $this->repository->findConfiguration();
        } catch (RepositoryException $e) {
            $this->critical($e->getMessage());
            $presenter->setResponseStatus(
                new FindConfigurationErrorResponse($e->getMessage())
            );
            return;
        }

        if ($configuration === null) {
            $this->critical(
                'No security policy are present, check that your installation / upgrade went well. ' .
                'A security Policy is necessary to create / update passwords'
            );
            $presenter->setResponseStatus(
                new FindConfigurationErrorResponse(
                    'Security policy not found. Please verify that your installation is valid'
                )
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
        $response = new FindConfigurationResponse();
        $response->passwordMinimumLength = $configuration->getPasswordMinimumLength();
        $response->hasUppercase = $configuration->hasUppercase();
        $response->hasLowercase = $configuration->hasLowercase();
        $response->hasNumber = $configuration->hasNumber();
        $response->hasSpecialCharacter = $configuration->hasSpecialCharacter();
        $response->canReusePasswords = $configuration->canReusePasswords();
        $response->attempts = $configuration->getAttempts();
        $response->blockingDuration = $configuration->getBlockingDuration();
        $response->passwordExpiration = $configuration->getPasswordExpiration();
        $response->delayBeforeNewPassword = $configuration->getDelayBeforeNewPassword();

        return $response;
    }
}
