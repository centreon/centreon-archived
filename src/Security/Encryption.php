<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Security;

use Security\Interfaces\EncryptionInterface;

class Encryption implements EncryptionInterface
{
    /**
     * @var string|null First secure key
     */
    private $firstKey;

    /**
     * @var string|null Second secure key
     */
    private $secondKey;

    /**
     * @var string Encryption method use to encrypt/decrypt data
     */
    private $encryptionMethod;

    /**
     * @var string Hashing method use to hash/unhash data during
     */
    private $hashingAlgorithm;

    public function __construct(string $encryptionMethod = 'aes-256-cbc', string $hashingAlgorithm = 'sha3-512')
    {
        $this->encryptionMethod = $encryptionMethod;
        $this->hashingAlgorithm = $hashingAlgorithm;
    }

    /**
     * @inheritDoc
     */
    public function crypt(string $data): string
    {
        if ($this->firstKey === null) {
            throw new \Exception('First key not defined');
        }
        if ($this->secondKey === null) {
            throw new \Exception('Second key not defined');
        }

        $ivLength = openssl_cipher_iv_length($this->encryptionMethod);
        if ($ivLength === false) {
            throw new \Exception('Error when retrieving the cipher length', 10);
        }
        $iv = openssl_random_pseudo_bytes($ivLength);
        if ($iv === false) {
            throw new \Exception('Error on generated string of bytes', 11);
        }

        $encryptedFirstPart = openssl_encrypt($data, $this->encryptionMethod, $this->firstKey, OPENSSL_RAW_DATA, $iv);
        if ($encryptedFirstPart === false) {
            throw new \Exception('Error on the encrypted string', 12);
        }
        $encryptedSecondPart = hash_hmac($this->hashingAlgorithm, $encryptedFirstPart, $this->secondKey, true);
        return base64_encode($iv . $encryptedSecondPart . $encryptedFirstPart);
    }

    /**
     * @inheritDoc
     */
    public function decrypt(string $input): ?string
    {
        if ($this->firstKey === null) {
            throw new \Exception('First key not defined');
        }
        if ($this->secondKey === null) {
            throw new \Exception('Second key not defined');
        }

        $mix = base64_decode($input);

        $ivLength = openssl_cipher_iv_length($this->encryptionMethod);
        if ($ivLength === false) {
            throw new \Exception('Error when retrieving the cipher length', 20);
        }

        $iv = substr($mix, 0, $ivLength);
        if ($iv === false) {
            throw new \Exception('Error during the decryption process', 21);
        }

        $encryptedFirstPart = substr($mix, $ivLength + 64);
        if ($encryptedFirstPart === false) {
            throw new \Exception('Error during the decryption process', 22);
        }

        $encryptedSecondPart = substr($mix, $ivLength, 64);
        if ($encryptedSecondPart === false) {
            throw new \Exception('Error during the decryption process', 23);
        }

        $data = openssl_decrypt($encryptedFirstPart, $this->encryptionMethod, $this->firstKey, OPENSSL_RAW_DATA, $iv);
        if ($data !== false) {
            $secondEncryptedNew = hash_hmac($this->hashingAlgorithm, $encryptedFirstPart, $this->secondKey, true);
            if (hash_equals($encryptedSecondPart, $secondEncryptedNew)) {
                return $data;
            }
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function setSecondKey(string $secondKey): EncryptionInterface
    {
        $this->secondKey = base64_decode($secondKey);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFirstKey(string $firstKey): EncryptionInterface
    {
        $this->firstKey = base64_decode($firstKey);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function generateRandomString(int $length = 64): string
    {
        $randomBytes = openssl_random_pseudo_bytes($length);
        if ($randomBytes === false) {
            throw new \Exception('Error when generating random bytes', 30);
        }
        $encodedRandomBytes = base64_encode($randomBytes);
        return substr($encodedRandomBytes, 0, $length);
    }

    /**
     * For more security, we modify the references of the first and second keys.
     *
     * @see Encryption::$firstKey
     * @see Encryption::$secondKey
     */
    public function __destruct()
    {
        $this->firstKey = null;
        $this->secondKey = null;
    }
}
