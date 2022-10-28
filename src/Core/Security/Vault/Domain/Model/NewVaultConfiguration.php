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

/**
 * This class represents vault configuration being created.
 */
class NewVaultConfiguration
{
    public const TYPE_HASHICORP = 'hashicorp';

    /**
     * @param string $name
     * @param string $type
     * @param string $address
     * @param int $port
     * @param string $storage
     * @param VaultCustomConfigurationInterface $customConfiguration
     */
    public function __construct(
        protected string $name,
        protected string $type,
        protected string $address,
        protected int $port,
        protected string $storage,
        protected VaultCustomConfigurationInterface $customConfiguration
    ) {
        $errors = [];
        if (empty($name)) {
            $errors[] = 'name';
        }
        if (
            filter_var($address, FILTER_VALIDATE_IP) === false
            && filter_var($address, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
        ) {
            $errors[] = 'address';
        }
        if (empty($storage)) {
            $errors[] = 'storage';
        }
        if (! empty($errors)) {
            throw VaultConfigurationException::invalidParameters($errors);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getStorage(): string
    {
        return $this->storage;
    }

    /**
     * @return VaultCustomConfigurationInterface
     */
    public function getCustomConfiguration(): VaultCustomConfigurationInterface
    {
        return $this->customConfiguration;
    }
}
