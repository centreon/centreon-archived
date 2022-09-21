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

namespace Core\Security\ProviderConfiguration\Application\OpenId\Repository;

use Core\Contact\Domain\Model\ContactGroup;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\AuthorizationRule;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\ContactGroupRelation;

interface ReadOpenIdConfigurationRepositoryInterface
{
    /**
     * @param int $providerConfigurationId
     * @return array<AuthorizationRule>
     */
    public function getAuthorizationRulesByConfigurationId(int $providerConfigurationId): array;

    /**
     * Get Contact Template
     *
     * @param int $contactTemplateId
     * @return ContactTemplate|null
     * @throws \Throwable
     */
    public function getContactTemplate(int $contactTemplateId): ?ContactTemplate;

    /**
     * Get Contact Group
     *
     * @param int $contactGroupId
     * @return ContactGroup|null
     * @throws \Throwable
     */
    public function getContactGroup(int $contactGroupId): ?ContactGroup;

    /**
     * Get Contact Group Relations by provider configuration id
     *
     * @param int $providerConfigurationId
     * @return ContactGroupRelation[]
     */
    public function getContactGroupRelationsByConfigurationId(int $providerConfigurationId): array;
}
