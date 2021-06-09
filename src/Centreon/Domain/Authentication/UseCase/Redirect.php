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

namespace Centreon\Domain\Authentication\UseCase;

use Centreon\Domain\Authentication\UseCase\RedirectRequest;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;

class Redirect
{
    /**
     * @var ProviderServiceInterface
     */
    private $providerService;

    /**
     * @param ProviderServiceInterface $providerService
     */
    public function __construct(ProviderServiceInterface $providerService)
    {
        $this->providerService = $providerService;
    }

    /**
     * Execute redirection scenario and return the redirection URI.
     *
     * @param RedirectRequest $request
     * @return RedirectResponse
     * @throws ProviderServiceException
     */
    public function execute(RedirectRequest $request): RedirectResponse
    {
        $providers = $this->providerService->findProvidersConfigurations();
        $redirectionUri = $request->getBaseUri();
        $response = new RedirectResponse();

        foreach ($providers as $provider) {
            $provider->setCentreonBaseUri($request->getBaseUri());
            $redirectionUri = $provider->getAuthenticationUri();
            if ($provider->isForced()) {
                break;
            }
        }

        $response->setRedirectionUri($redirectionUri);
        return $response;
    }
}
