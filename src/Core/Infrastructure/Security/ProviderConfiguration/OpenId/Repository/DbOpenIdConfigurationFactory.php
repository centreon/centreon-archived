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

namespace Core\Infrastructure\Security\ProviderConfiguration\OpenId\Repository;

use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;

class DbOpenIdConfigurationFactory
{
    public static function createFromRecord(array $record, array $customConfiguration): OpenIdConfiguration
    {
        return new OpenIdConfiguration(
            $record['is_active'] === '1',
            $record['is_forced'] === '1',
            $customConfiguration['trusted_client_addresses'],
            $customConfiguration['blacklist_client_addresses'],
            $customConfiguration['base_url'],
            $customConfiguration['authorization_endpoint'],
            $customConfiguration['token_endpoint'],
            $customConfiguration['introspection_token_endpoint'],
            $customConfiguration['userinfo_endpoint'],
            $customConfiguration['endsession_endpoint'],
            $customConfiguration['connection_scopes'],
            $customConfiguration['login_claim'],
            $customConfiguration['client_id'],
            $customConfiguration['client_secret'],
            $customConfiguration['authentication_type'],
            $customConfiguration['verify_peer']
        );
    }
}
