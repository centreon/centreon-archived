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

namespace Core\Infrastructure\Security\Api\FindProviderConfigurations\PresenterProviders;

use Core\Application\Security\UseCase\FindProviderConfigurations\FindLocalProviderConfigurationResponse;

class LocalProviderPresenter
{
    public function isValidFor(mixed $response): bool
    {
        return is_a($response, FindLocalProviderConfigurationResponse::class);
    }

    public function format(FindLocalProviderConfigurationResponse $response): array
    {
        return [
            'id' => $response->id,
            'type' => $response->type,
            'name' => $response->name,
            'authentication_uri' => $response->authenticationUri,
            'is_active' => $response->isActive,
            'is_forced' => $response->isForced,
        ];
    }
}
