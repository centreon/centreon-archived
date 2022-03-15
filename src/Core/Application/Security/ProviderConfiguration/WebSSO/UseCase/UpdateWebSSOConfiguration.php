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

namespace Core\Application\Security\ProviderConfiguration\WebSSO\UseCase;

use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Domain\Security\ProviderConfiguration\WebSSO\Model\WebSSOConfigurationFactory;
use Core\Application\Security\ProviderConfiguration\WebSSO\Repository\WriteWebSSOConfigurationRepositoryInterface;

class UpdateWebSSOConfiguration
{
    /**
     * @param WriteWebSSOConfigurationRepositoryInterface $repository
     */
    public function __construct(private WriteWebSSOConfigurationRepositoryInterface $repository)
    {
    }

    /**
     * @param UpdateWebSSOConfigurationPresenterInterface $presenter
     * @param UpdateWebSSOConfigurationRequest $request
     * @return void
     */
    public function __invoke(
        UpdateWebSSOConfigurationPresenterInterface $presenter,
        UpdateWebSSOConfigurationRequest $request
    ): void {
        try {
            $configuration = WebSSOConfigurationFactory::createFromRequest($request);
        } catch (AssertionException $ex) {
            $presenter->setResponseStatus(new ErrorResponse($ex->getMessage()));
            return;
        }
        $this->repository->updateConfiguration($configuration);

        $presenter->setResponseStatus(new NoContentResponse());
    }
}
