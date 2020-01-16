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

namespace Security\Interfaces;

interface EncryptionInterface
{
    /**
     * Generates a random string.
     *
     * Can be use as salt with password.
     *
     * @param int $length Length of the generated string
     * @return string
     */
    public static function generateRandomString (int $length = 64): string;

    /**
     * Crypt data according to first and second keys.
     *
     * @param string $data Data to be encrypted
     * @see Security::$firstKey
     * @see Security::$secondKey
     * @return string Encrypted data
     */
    public function crypt(string $data): string;

    /**
     * Set the first secure key.
     *
     * @param string $firstKey
     * @return EncryptionInterface
     * @see Security::$firstKey
     */
    public function setFirstKey(string $firstKey): EncryptionInterface;

    /**
     * Set the second secure key.
     *
     * @param string $secondKey
     * @return EncryptionInterface
     * @see Security::$secondKey
     */
    public function setSecondKey(string $secondKey): EncryptionInterface;

    /**
     * Decrypt input according to first and second keys.
     *
     * @param string $input Data to be decrypted
     * @return string|null Data decrypted if successful otherwise null
     */
    public function decrypt(string $input): ?string;
}
