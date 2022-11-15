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

namespace Core\Security\Vault\Infrastructure\Repository;

use Core\Security\Vault\Domain\Model\VaultConfiguration;
use Security\Interfaces\EncryptionInterface;

class DbVaultConfigurationFactory
{
    /**
     * @param EncryptionInterface $encryption
     * @param DbReadVaultRepository $vaultRepository
     */
    public function __construct(private EncryptionInterface $encryption, private DbReadVaultRepository $vaultRepository)
    {
    }

    /**
     * @param array<string,int|string> $recordData
     *
     * @return VaultConfiguration|null
     */
    public function createFromRecord(array $recordData): ?VaultConfiguration
    {
        $vault = $this->vaultRepository->findById((int) $recordData['type_id']);

        if (empty($recordData) || $vault === null) {
            return null;
        }

        /** @var string $roleId */
        $roleId = $this->encryption
            ->setSecondKey((string) $recordData['salt'])
            ->decrypt((string) $recordData['role_id']);

        /** @var string $secretId */
        $secretId = $this->encryption
            ->setSecondKey((string) $recordData['salt'])
            ->decrypt((string) $recordData['secret_id']);

        return new VaultConfiguration(
            (int) $recordData['id'],
            (string) $recordData['name'],
            $vault,
            (string) $recordData['url'],
            (int) $recordData['port'],
            (string) $recordData['storage'],
            $roleId,
            $secretId,
            (string) $recordData['salt']
        );
    }
}
