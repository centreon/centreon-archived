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

namespace Core\Security\ProviderConfiguration\Application\WebSSO\UseCase\FindWebSSOConfiguration;

use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Repository\RepositoryException;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Security\ProviderConfiguration\Domain\WebSSO\Model\WebSSOConfiguration;
use Core\Security\ProviderConfiguration\Application\WebSSO\Repository\ReadWebSSOConfigurationRepositoryInterface;

class FindWebSSOConfiguration
{
    use LoggerTrait;

    /**
     * @param ReadWebSSOConfigurationRepositoryInterface $repository
     */
    public function __construct(private ReadWebSSOConfigurationRepositoryInterface $repository)
    {
    }

    /**
     * @param FindWebSSOConfigurationPresenterInterface $presenter
     */
    public function __invoke(FindWebSSOConfigurationPresenterInterface $presenter): void
    {
        try {
            $configuration = $this->repository->findConfiguration();
        } catch (RepositoryException | AssertionException $ex) {
            $this->error($ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            $presenter->setResponseStatus(new ErrorResponse($ex->getMessage()));
            return;
        }

        if ($configuration === null) {
            $presenter->setResponseStatus(new NotFoundResponse('WebSSOConfiguration'));
            return;
        }

        $presenter->present($this->createResponse($configuration));
    }

    /**
     * @param WebSSOConfiguration $configuration
     * @return FindWebSSOConfigurationResponse
     */
    private function createResponse(WebSSOConfiguration $configuration): FindWebSSOConfigurationResponse
    {
        $response = new FindWebSSOConfigurationResponse();
        $response->isActive = $configuration->isActive();
        $response->isForced = $configuration->isForced();
        $response->trustedClientAddresses = $configuration->getTrustedClientAddresses();
        $response->blacklistClientAddresses = $configuration->getBlackListClientAddresses();
        $response->loginHeaderAttribute = $configuration->getLoginHeaderAttribute();
        $response->patternMatchingLogin = $configuration->getPatternMatchingLogin();
        $response->patternReplaceLogin = $configuration->getPatternReplaceLogin();

        return $response;
    }
}
