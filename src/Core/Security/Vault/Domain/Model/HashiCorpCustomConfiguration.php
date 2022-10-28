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

namespace Core\Security\Vault\Domain\Model;

use Core\Security\Vault\Domain\Exceptions\VaultConfigurationException;

class HashiCorpCustomConfiguration implements VaultCustomConfigurationInterface
{
    /**
     * @param string $roleId
     * @param string $secretId
     */
    public function __construct(private string $roleId, private string $secretId)
    {
        $errors = [];
        if (empty($this->roleId)) {
            $errors[] = 'role_id';
        }
        if (empty($this->secretId)) {
            $errors[] = 'secret_id';
        }
        if (! empty($errors)) {
            throw VaultConfigurationException::invalidParameters($errors);
        }
    }

    /**
     * @return string
     */
    public function getRoleId(): string
    {
        return $this->roleId;
    }

    /**
     * @return string
     */
    public function getSecretId(): string
    {
        return $this->secretId;
    }
}
