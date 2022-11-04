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

namespace Security\Infrastructure\Repository\Model;

use Core\Security\ProviderConfiguration\Domain\Model\Configuration;

class ProviderConfigurationFactoryRdb
{
    /**
     * @param array<string,mixed> $data
     * @return Configuration
     */
    public static function create(array $data): Configuration
    {
        $mandatoryFields = ['id', 'type', 'name', 'is_active', 'is_forced'];
        foreach ($mandatoryFields as $mandatoryField) {
            if (!array_key_exists($mandatoryField, $data)) {
                throw new \InvalidArgumentException(
                    sprintf(_("Missing mandatory parameter: '%s'"), $mandatoryField)
                );
            }
        }
        return new Configuration(
            (int) $data['id'],
            $data['type'],
            $data['name'],
            $data['custom_configuration'],
            (bool) $data['is_active'],
            (bool) $data['is_forced']
        );
    }
}
