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

namespace Core\Infrastructure\Security\ProviderConfiguration\WebSSO\Api\UpdateWebSSOConfiguration;

use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Security\ProviderConfiguration\WebSSO\UseCase\UpdateWebSSOConfiguration;
use Core\Application\Security\ProviderConfiguration\WebSSO\UseCase\UpdateWebSSOConfigurationRequest;
use Core\Application\Security\ProviderConfiguration\WebSSO\UseCase\UpdateWebSSOConfigurationPresenterInterface;

class UpdateWebSSOConfigurationController extends AbstractController
{
    use LoggerTrait;

    /**
     * @param UpdateWebSSOConfiguration $useCase
     * @param Request $request
     * @param UpdateWebSSOConfigurationPresenterInterface $presenter
     * @return object
     */
    public function __invoke(
        UpdateWebSSOConfiguration $useCase,
        Request $request,
        UpdateWebSSOConfigurationPresenterInterface $presenter
    ): object {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $this->info('Validating request body...');
        $this->validateDataSent($request, __DIR__ . '/UpdateWebSSOConfigurationSchema.json');
        $updateWebSSOConfigurationRequest = $this->createUpdateWebSSOConfigurationRequest($request);
        $useCase($presenter, $updateWebSSOConfigurationRequest);

        return $presenter->show();
    }

    /**
     * @param Request $request
     * @return UpdateWebSSOConfigurationRequest
     */
    private function createUpdateWebSSOConfigurationRequest(Request $request): UpdateWebSSOConfigurationRequest
    {
        $requestData = json_decode((string) $request->getContent(), true);
        $updateWebSSOConfigurationRequest = new UpdateWebSSOConfigurationRequest();
        $updateWebSSOConfigurationRequest->isActive = $requestData['is_active'];
        $updateWebSSOConfigurationRequest->isForced = $requestData['is_forced'];
        $updateWebSSOConfigurationRequest->trustedClientAddresses  = $requestData['trusted_client_addresses'];
        $updateWebSSOConfigurationRequest->blacklistClientAddresses = $requestData['blacklist_client_addresses'];
        $updateWebSSOConfigurationRequest->loginHeaderAttribute = $requestData['login_header_attribute'];
        $updateWebSSOConfigurationRequest->patternMatchingLogin = $requestData['pattern_matching_login'];
        $updateWebSSOConfigurationRequest->patternReplaceLogin = $requestData['pattern_replace_login'];

        return $updateWebSSOConfigurationRequest;
    }
}
