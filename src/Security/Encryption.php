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

class Encryption
{
    /**
     * @var string First secure key
     */
    private $firstKey;

    /**
     * @var string Second secure key
     */
    private $secondKey;

    /**
     * Crypt data according to first and second keys.
     *
     * @param string $data Data to be encrypted
     * @see Security::$firstKey
     * @see Security::$secondKey
     * @return string Encrypted data
     */
    public function crypt(string $data): string
    {
        $firstKey = base64_decode($this->firstKey);
        $secondKey = base64_decode($this->secondKey);

        $method = "aes-256-cbc";
        $ivLength = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $firstEncrypted = openssl_encrypt($data, $method, $firstKey, OPENSSL_RAW_DATA, $iv);
        $secondEncrypted = hash_hmac('sha3-512', $firstEncrypted, $secondKey, true);

        return base64_encode($iv . $secondEncrypted . $firstEncrypted);
    }

    /**
     * Decrypt input according to first and second keys.
     *
     * @param string $input Data to be decrypted
     * @return string|null Data decrypted if successful otherwise null
     */
    public function decrypt(string $input): ?string
    {
        $firstKey = base64_decode($this->firstKey);
        $secondKey = base64_decode($this->secondKey);

        $mix = base64_decode($input);

        $method = "aes-256-cbc";
        $ivLength = openssl_cipher_iv_length($method);

        $iv = substr($mix, 0, $ivLength);
        $secondEncrypted = substr($mix, $ivLength, 64);
        if ($secondEncrypted === false) {
            return null;
        }
        $firstEncrypted = substr($mix, $ivLength + 64);
        if ($firstEncrypted === false) {
            return null;
        }
        $data = openssl_decrypt($firstEncrypted, $method, $firstKey, OPENSSL_RAW_DATA, $iv);
        $secondEncryptedNew = hash_hmac('sha3-512', $firstEncrypted, $secondKey, true);

        if (hash_equals($secondEncrypted, $secondEncryptedNew)) {
            return $data;
        }
        return null;
    }

    /**
     * Set the second secure key.
     *
     * @param string $secondKey
     * @see Security::$secondKey
     * @return Encryption
     */
    public function setSecondKey(string $secondKey): self
    {
        $this->secondKey = $secondKey;
        return $this;
    }

    /**
     * Set the first secure key.
     *
     * @param string $firstKey
     * @see Security::$firstKey
     * @return Encryption
     */
    public function setFirstKey(string $firstKey): self
    {
        $this->firstKey = $firstKey;
        return $this;
    }

    /**
     * Generates a random string.
     *
     * Can be use as salt with password.
     *
     * @param int $length Length if the generated salt
     * @return string
     */
    static public function generateString(int $length = 64): string
    {
        return substr(base64_encode(openssl_random_pseudo_bytes($length)), 0, $length);
    }

    /**
     * For more security, we modify the references of the first and second keys.
     *
     * @see Security::$firstKey
     * @see Security::$secondKey
     */
    public function __destruct()
    {
        $this->firstKey = 0;
        $this->secondKey = 0;
    }
}
