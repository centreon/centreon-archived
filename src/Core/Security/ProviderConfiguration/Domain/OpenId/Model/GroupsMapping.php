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

namespace Core\Security\ProviderConfiguration\Domain\OpenId\Model;

/**
 * This class is designed to represent The mapping between OpenID Claims and Centreon Contact Groups.
 * Claims are gathered from the Response (attribute path) of an endpoint defined by the user.
 * e.g : "http://myprovider.com/my_authorizations" will return a response:
 *
 * {
 *   "infos": {
 *      "groups": [
 *          "groupA",
 *          "groupB"
 *      ]
 *   }
 * }
 *
 * If we want to map contact group with groupA, we should set attributePath to "infos.groups",
 * and ContactGroupRelation->claimValue to "groupA"
 * with ["groupA"]
 */
class GroupsMapping
{
    /**
     * @var ContactGroupRelation[]
     */
    private array $contactGroupRelations;

    public function __construct(
        private bool $isEnabled,
        private string $attributePath,
        private Endpoint $endpoint,
    ) {
    }

    /**
     * @param ContactGroupRelation[] $contactGroupRelations
     * @return self
     */
    public function setContactGroupRelations(array $contactGroupRelations): self
    {
        $this->contactGroupRelations = $contactGroupRelations;
        return $this;
    }

    /**
     * @return ContactGroupRelation[]
     */
    public function getContactGroupRelations(): array
    {
        return $this->contactGroupRelations;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @return string
     */
    public function getAttributePath(): string
    {
        return $this->attributePath;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint(): Endpoint
    {
        return $this->endpoint;
    }
}
