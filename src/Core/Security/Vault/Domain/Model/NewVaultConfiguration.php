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

use Centreon\Domain\Common\Assertion\{Assertion, AssertionException};

/**
 * This class represents vault configuration being created.
 */
class NewVaultConfiguration
{
    public const MAX_LENGTH = 255;
    public const MAX_ADDRESS_LENGTH = 1024;
    public const MIN_PORT_VALUE = 1;
    public const MAX_PORT_VALUE = 65535;
    public const TYPE_HASHICORP = 'hashicorp';
    public const ALLOWED_TYPES = [self::TYPE_HASHICORP];
    public const SECOND_ENCRYPTION_KEY = 'vault_configuration_credentials';

    /**
     * @param string $name
     * @param string $type
     * @param string $address
     * @param int $port
     * @param string $storage
     * @param string $roleId
     * @param string $secretId
     */
    public function __construct(
        protected string $name,
        protected string $type,
        protected string $address,
        protected int $port,
        protected string $storage,
        protected string $roleId,
        protected string $secretId,
    ) {
        Assertion::notEmpty($name, 'NewVaultConfiguration::name');
        Assertion::maxLength($name, self::MAX_LENGTH, 'NewVaultConfiguration::name');
        Assertion::notEmpty($type, 'NewVaultConfiguration::type');
        Assertion::inArray($type, self::ALLOWED_TYPES, 'NewVaultConfiguration::type');
        Assertion::notEmpty($address, 'NewVaultConfiguration::address');
        Assertion::maxLength($address, self::MAX_ADDRESS_LENGTH, 'NewVaultConfiguration::address');
        if (
            filter_var($address, FILTER_VALIDATE_IP) === false
            && filter_var($address, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
        ) {
            throw AssertionException::ipOrDomain($address, 'NewVaultConfiguration::address');
        }
        Assertion::max($port, self::MAX_PORT_VALUE, 'NewVaultConfiguration::port');
        Assertion::min($port, self::MIN_PORT_VALUE, 'NewVaultConfiguration::port');
        Assertion::notEmpty($port, 'NewVaultConfiguration::port');
        Assertion::notEmpty($storage, 'NewVaultConfiguration::storage');
        Assertion::maxLength($storage, self::MAX_LENGTH, 'NewVaultConfiguration::storage');
        Assertion::notEmpty($roleId, 'NewVaultConfiguration::roleId');
        Assertion::maxLength($roleId, self::MAX_LENGTH, 'NewVaultConfiguration::roleId');
        Assertion::notEmpty($secretId, 'NewVaultConfiguration::secretId');
        Assertion::maxLength($secretId, self::MAX_LENGTH, 'NewVaultConfiguration::secretId');
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
