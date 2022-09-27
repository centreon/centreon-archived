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

namespace Core\Security\ProviderConfiguration\Domain\OpenId\Model;

use Core\Contact\Domain\Model\ContactGroup;
use Core\Contact\Domain\Model\ContactTemplate;

interface OpenIdCustomConfigurationInterface
{
    /**
     * @return string|null
     */
    public function getEndSessionEndpoint(): ?string;

    /**
     * @return string[]
     */
    public function getConnectionScopes(): array;

    /**
     * @return string|null
     */
    public function getLoginClaim(): ?string;

    /**
     * @return string|null
     */
    public function getAuthenticationType(): ?string;

    /**
     * @return bool
     */
    public function verifyPeer(): bool;

    /**
     * @return AuthorizationRule[]
     */
    public function getAuthorizationRules(): array;

    /**
     * @return bool
     */
    public function isAutoImportEnabled(): bool;

    /**
     * @return string|null
     */
    public function getBaseUrl(): ?string;

    /**
     * @return string|null
     */
    public function getAuthorizationEndpoint(): ?string;

    /**
     * @return string|null
     */
    public function getTokenEndpoint(): ?string;

    /**
     * @return string|null
     */
    public function getIntrospectionTokenEndpoint(): ?string;

    /**
     * @return string|null
     */
    public function getUserInformationEndpoint(): ?string;

    /**
     * @return string|null
     */
    public function getClientId(): ?string;

    /**
     * @return string|null
     */
    public function getClientSecret(): ?string;

    /**
     * @return ContactTemplate|null
     */
    public function getContactTemplate(): ?ContactTemplate;

    /**
     * @return string|null
     */
    public function getEmailBindAttribute(): ?string;

    /**
     * @return string|null
     */
    public function getUserNameBindAttribute(): ?string;

    /**
     * @return ContactGroup|null
     */
    public function getContactGroup(): ?ContactGroup;

    /**
     * @return AuthenticationConditions
     */
    public function getAuthenticationConditions(): AuthenticationConditions;
}
