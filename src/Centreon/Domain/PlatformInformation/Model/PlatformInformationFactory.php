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

namespace Centreon\Domain\PlatformInformation\Model;

use Centreon\Domain\PlatformInformation\Model\PlatformInformation;
use Security\Encryption;

class PlatformInformationFactory
{
    /**
     * Credentials encryption key
     */
    public const ENCRYPT_SECOND_KEY = 'api_remote_credentials';

    /**
     * @param array<string, mixed> $information
     * @return PlatformInformation
     */
    public static function create(array $information): PlatformInformation
    {
        $platFormInformation = new PlatformInformation();

        foreach ($information as $informationDto) {
            if ($informationDto->key === 'isRemote') {
                if ($informationDto->value === true) {
                    $platFormInformation->setRemote(true);
                } else {
                    $platFormInformation->setRemote(false);
                    $platFormInformation->setCentralServerAddress(null);
                    $platFormInformation->setApiUsername(null);
                    $platFormInformation->setApiCredentials(null);
                    $platFormInformation->setEncryptedApiCredentials(null);
                    $platFormInformation->setApiScheme(null);
                    $platFormInformation->setApiPort(null);
                    $platFormInformation->setApiPath(null);
                    $platFormInformation->setApiPeerValidation(null);
                    break;
                }
            }
            switch ($informationDto->key) {
                case 'centralServerAddress':
                    $platFormInformation->setCentralServerAddress($informationDto->value);
                    break;
                case 'apiUsername':
                    $platFormInformation->setApiUsername($informationDto->value);
                    break;
                case 'apiCredentials':
                    $platFormInformation->setApiCredentials($informationDto->value);
                    $passwordEncrypted =  self::encryptApiCredentials($informationDto->value);
                    $platFormInformation->setEncryptedApiCredentials($passwordEncrypted);
                    break;
                case 'apiScheme':
                    $platFormInformation->setApiScheme($informationDto->value);
                    break;
                case 'apiPort':
                    $platFormInformation->setApiPort($informationDto->value);
                    break;
                case 'apiPath':
                    $platFormInformation->setApiPath($informationDto->value);
                    break;
                case 'peerValidation':
                    $platFormInformation->setApiPeerValidation($informationDto->value);
                    break;
            }
        }

        return $platFormInformation;
    }

    /**
     * encrypt the Central API Password
     *
     * @param string $password
     * @return string
     */
    private static function encryptApiCredentials(string $password): string
    {
        if (!isset($_ENV['APP_SECRET'])) {
            throw new \InvalidArgumentException(
                _("Unable to find the encryption key. Please check the '.env.local.php' file.")
            );
        }

        $secondKey = base64_encode(self::ENCRYPT_SECOND_KEY);
        $centreonEncryption = new Encryption();
        $centreonEncryption->setFirstKey($_ENV['APP_SECRET'])->setSecondKey($secondKey);
        return $centreonEncryption->crypt($password);
    }
}
