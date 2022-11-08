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

namespace Core\Security\ProviderConfiguration\Infrastructure\WebSSO\Api\FindWebSSOConfiguration;

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Security\ProviderConfiguration\Application\WebSSO\UseCase\FindWebSSOConfiguration\{
    FindWebSSOConfigurationResponse,
    FindWebSSOConfigurationPresenterInterface
};

class FindWebSSOConfigurationPresenter extends AbstractPresenter implements FindWebSSOConfigurationPresenterInterface
{
    /**
     * {@inheritDoc}
     * @param FindWebSSOConfigurationResponse $data
     */
    public function present(mixed $data): void
    {
        $presenterResponse = [
            'is_active' => $data->isActive,
            'is_forced' => $data->isForced,
            'trusted_client_addresses' => $data->trustedClientAddresses,
            'blacklist_client_addresses' => $data->blacklistClientAddresses,
            'login_header_attribute' => $data->loginHeaderAttribute,
            'pattern_matching_login' => $data->patternMatchingLogin,
            'pattern_replace_login' => $data->patternReplaceLogin
        ];

        parent::present($presenterResponse);
    }
}
