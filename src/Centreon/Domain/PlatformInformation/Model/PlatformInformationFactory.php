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
     * @var string|null
     */
    private $appSecret;

    public function __construct(?string $appSecret)
    {
        $this->appSecret = $appSecret;
    }

    /**
     * @param array<string, mixed> $information
     * @return PlatformInformation
     */
    public function create(array $information): PlatformInformation
    {
        $platformInformation = new PlatformInformation();

        foreach ($information as $key => $value) {
            if ($key === 'isRemote') {
                if ($value === true) {
                    $platformInformation->setRemote(true);
                } else {
                    $platformInformation->setRemote(false);
                    $platformInformation->setCentralServerAddress(null);
                    $platformInformation->setApiUsername(null);
                    $platformInformation->setApiCredentials(null);
                    $platformInformation->setEncryptedApiCredentials(null);
                    $platformInformation->setApiScheme(null);
                    $platformInformation->setApiPort(null);
                    $platformInformation->setApiPath(null);
                    $platformInformation->setApiPeerValidation(null);
                    break;
                }
            }
            switch ($key) {
                case 'centralServerAddress':
                    $platformInformation->setCentralServerAddress($value);
                    break;
                case 'apiUsername':
                    $platformInformation->setApiUsername($value);
                    break;
                case 'apiCredentials':
                    $platformInformation->setApiCredentials($value);
                    $passwordEncrypted =  $this->encryptApiCredentials($value);
                    $platformInformation->setEncryptedApiCredentials($passwordEncrypted);
                    break;
                case 'apiScheme':
                    $platformInformation->setApiScheme($value);
                    break;
                case 'apiPort':
                    $platformInformation->setApiPort($value);
                    break;
                case 'apiPath':
                    $platformInformation->setApiPath($value);
                    break;
                case 'peerValidation':
                    $platformInformation->setApiPeerValidation($value);
                    break;
            }
        }

        return $platformInformation;
    }

    /**
     * encrypt the Central API Password
     *
     * @param string $password
     * @return string
     */
    private function encryptApiCredentials(string $password): string
    {
        if ($this->appSecret === null) {
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
