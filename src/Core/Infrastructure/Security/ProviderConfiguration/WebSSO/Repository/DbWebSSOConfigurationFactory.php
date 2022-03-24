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

namespace Core\Infrastructure\Security\ProviderConfiguration\WebSSO\Repository;

use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Domain\Security\ProviderConfiguration\WebSSO\Model\WebSSOConfiguration;

class DbWebSSOConfigurationFactory
{
    /**
     * @param array<string,mixed> $customConfiguration
     * @param array<string,mixed> $configuration
     * @throws AssertionException
     * @return WebSSOConfiguration
     */
    public static function createFromRecord(array $customConfiguration, array $configuration): WebSSOConfiguration
    {
        return new WebSSOConfiguration(
            $configuration['is_active'] === '1',
            $configuration['is_forced'] === '1',
            $customConfiguration['trusted_client_addresses'],
            $customConfiguration['blacklist_client_addresses'],
            $customConfiguration['login_header_attribute'],
            $customConfiguration['pattern_matching_login'],
            $customConfiguration['pattern_replace_login']
        );
    }
}
