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
 *  For more information : contact@centreon.com
 */

declare(strict_types=1);

namespace Core\Security\ProviderConfiguration\Domain\OpenId\Model;

use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\ACLConditionsException;

/**
 * ACL condition block regarding OpenID.
 */
class ACLConditions
{
    /**
     * @param bool $isEnabled
     * @param bool $applyOnlyFirstRole
     * @param string $attributePath
     * @param Endpoint $endpoint
     * @param AuthorizationRule[] $relations
     * @throws ACLConditionsException
     */
    public function __construct(
        private bool $isEnabled,
        private bool $applyOnlyFirstRole,
        private string $attributePath,
        private Endpoint $endpoint,
        private array $relations = []
    ) {
        $this->guard();
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @return bool
     */
    public function onlyFirstRoleIsApplied(): bool
    {
        return $this->applyOnlyFirstRole;
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

    /**
     * @return AuthorizationRule[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @return array<string>
     */
    public function getClaimValues(): array
    {
        $values = array_map(function ($relation) {
            return $relation->getClaimValue();
        }, $this->relations);

        if ($this->applyOnlyFirstRole) {
            $values = [$values[0]];
        }

        return $values;
    }

    /**
     * @return array<string, array<array<string,int|string>|string>|bool|string>
     */
    public function toArray(): array
    {
        return [
            'is_enabled' => $this->isEnabled,
            'apply_only_first_role' => $this->applyOnlyFirstRole,
            'attribute_path' => $this->attributePath,
            'endpoint' => $this->endpoint->toArray()
        ];
    }

    /**
     * Check mandatory parameters
     * @throws ACLConditionsException
     */
    private function guard(): void
    {
        if ($this->isEnabled) {
            $missing = [];

            if (!strlen($this->attributePath)) {
                $missing[] = 'attribute_path';
            }

            if ($this->applyOnlyFirstRole && empty($this->relations)) {
                $missing[] = 'relations';
            }

            if (!empty($missing)) {
                throw ACLConditionsException::missingFields($missing);
            }
        }
    }
}
