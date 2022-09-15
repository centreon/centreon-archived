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

namespace Core\Security\ProviderConfiguration\Application\WebSSO\UseCase\UpdateWebSSOConfiguration;

class UpdateWebSSOConfigurationRequest
{
    /**
     * @var bool
     */
    public bool $isActive;

    /**
     * @var bool
     */
    public bool $isForced;

    /**
     * @var array<string>
     */
    public array $trustedClientAddresses;

    /**
     * @var array<string>
     */
    public array $blacklistClientAddresses;

    /**
     * @var string|null
     */
    public ?string $loginHeaderAttribute;

    /**
     * @var string|null
     */
    public ?string $patternMatchingLogin;

    /**
     * @var string|null
     */
    public ?string $patternReplaceLogin;
}
