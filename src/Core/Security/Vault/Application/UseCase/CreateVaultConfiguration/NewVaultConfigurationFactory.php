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

namespace Core\Security\Vault\Application\UseCase\CreateVaultConfiguration;

use Assert\InvalidArgumentException;
use Core\Security\Vault\Domain\Model\NewVaultConfiguration;
use Security\Interfaces\EncryptionInterface;

class NewVaultConfigurationFactory
{
    /**
     * @param EncryptionInterface $encryption
     */
    public function __construct(private EncryptionInterface $encryption)
    {
    }

    /**
     * This method will crypt $roleId and $secretId before instanciating NewVaultConfiguraiton.
     *
     * @param CreateVaultConfigurationRequest $request
     *
     * @throws InvalidArgumentException
     *
     * @return NewVaultConfiguration
     */
    public function create(CreateVaultConfigurationRequest $request): NewVaultConfiguration
    {
        $salt = base64_encode(openssl_random_pseudo_bytes(NewVaultConfiguration::SALT_LENGTH));
        $roleId = $this->encryption
            ->setSecondKey($salt)
            ->crypt($request->roleId);
        $secretId = $this->encryption
            ->setSecondKey($salt)
            ->crypt($request->secretId);

        return new NewVaultConfiguration(
            $request->name,
            $request->type,
            $request->address,
            $request->port,
            $request->storage,
            $roleId,
            $secretId,
            $salt
        );
    }
}
