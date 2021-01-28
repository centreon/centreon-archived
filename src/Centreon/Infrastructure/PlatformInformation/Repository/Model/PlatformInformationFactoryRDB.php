<?php

/*
 * Copyright 2005 - 201 Centreon (https://www.centreon.com/)
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

namespace Centreon\Infrastructure\PlatformInformation\Repository\Model;

use Security\Encryption;
use Centreon\Domain\PlatformInformation\Model\PlatformInformation;

class PlatformInformationFactoryRDB
{
        /**
     * Credentials encryption key
     */
    public const ENCRYPT_SECOND_KEY = 'api_remote_credentials';

    public static function create(array $information)
    {
        $platFormInformation = new PlatformInformation();
        foreach ($information as $key => $value) {
            switch ($key) {
                case 'isRemote':
                    $platFormInformation->setRemote($value);
                    break;
                case 'centralServerAddress':
                    $platFormInformation->setCentralServerAddress($value);
                    break;
                case 'apiUsername':
                    $platFormInformation->setApiUsername($value);
                    break;
                case 'apiCredentials':
                    $platFormInformation->setEncryptedApiCredentials($value);
                    $decryptedPassword = self::decryptApiCredentials($value);
                    $platFormInformation->setApiCredentials($decryptedPassword);
                    break;
                case 'apiScheme':
                    $platFormInformation->setApiScheme($value);
                    break;
                case 'apiPort':
                    $platFormInformation->setApiPort((int) $value);
                    break;
                case 'apiPath':
                    $platFormInformation->setApiPath($value);
                    break;
                case 'apiPeerValidation':
                    $platFormInformation->setApiPeerValidation($value);
                    break;
            }
        }
        return $platFormInformation;
    }

    /**
     * @param string|null $encryptedKey
     * @return string|null
     */
    private static function decryptApiCredentials(?string $encryptedKey): ?string
    {
        if (empty($encryptedKey)) {
            return null;
        }

        if (!isset($_ENV['APP_SECRET'])) {
            throw new \InvalidArgumentException(
                _("Unable to find the encryption key. Please check the '.env.local.php' file.")
            );
        }

        // second key
        $secondKey = base64_encode(self::ENCRYPT_SECOND_KEY);

        try {
            $centreonEncryption = new Encryption();
            $centreonEncryption->setFirstKey($_ENV['APP_SECRET'])->setSecondKey($secondKey);
            return $centreonEncryption->decrypt($encryptedKey);
        } catch (\throwable $e) {
            throw new \InvalidArgumentException(
                _("Unable to decipher central's credentials. Please check the credentials in the 'Remote Access' form")
            );
        }
    }
}