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

namespace Core\Security\ProviderConfiguration\Infrastructure\OpenId\Api\FindOpenIdConfiguration;

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Security\ProviderConfiguration\Application\OpenId\UseCase\FindOpenIdConfiguration\{
    FindOpenIdConfigurationPresenterInterface,
    FindOpenIdConfigurationResponse
};

class FindOpenIdConfigurationPresenter extends AbstractPresenter implements FindOpenIdConfigurationPresenterInterface
{
    /**
     * {@inheritDoc}
     * @param FindOpenIdConfigurationResponse $data
     */
    public function present(mixed $data): void
    {
        $presenterResponse = [
            'is_active' => $data->isActive,
            'is_forced' => $data->isForced,
            'base_url' => $data->baseUrl,
            'authorization_endpoint' => $data->authorizationEndpoint,
            'token_endpoint' => $data->tokenEndpoint,
            'introspection_token_endpoint' => $data->introspectionTokenEndpoint,
            'userinfo_endpoint' => $data->userInformationEndpoint,
            'endsession_endpoint' => $data->endSessionEndpoint,
            'connection_scopes' => $data->connectionScopes,
            'login_claim' => $data->loginClaim,
            'client_id' => $data->clientId,
            'client_secret' => $data->clientSecret,
            'authentication_type' => $data->authenticationType,
            'verify_peer' => $data->verifyPeer,
            'auto_import' => $data->isAutoImportEnabled,
            'contact_template' => $data->contactTemplate,
            'email_bind_attribute' => $data->emailBindAttribute,
            'fullname_bind_attribute' => $data->userNameBindAttribute,
            'contact_group' => $data->contactGroup,
            'roles_mapping' => $data->aclConditions,
            'authentication_conditions' => $data->authenticationConditions,
            'groups_mapping' => $data->groupsMapping
        ];

        parent::present($presenterResponse);
    }
}
