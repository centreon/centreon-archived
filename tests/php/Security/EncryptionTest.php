<?php
/**
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Tests\Security;

use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;
use Security\Encryption;

class EncryptionTest extends TestCase
{
    /**
     * @var string
     */
    private $firstKey;
    /**
     * @var string
     */
    private $secondKey;

    public function setUp()
    {
        parent::setUp();
        $this->firstKey = random_bytes(64);
        $this->secondKey = random_bytes(64);
    }

    public function testCryptDecrypt()
    {
        $messageToEncrypt = 'my secret message';
        $encryption = (new Encryption())
            ->setFirstKey($this->firstKey)
            ->setSecondKey($this->secondKey);

        $encrypedMessage = $encryption->crypt($messageToEncrypt);

        $decryptedMessage = $encryption->decrypt($encrypedMessage);
        $this->assertEquals($messageToEncrypt, $decryptedMessage);

        $encryption->setSecondKey(random_bytes(64)); // False second secret key
        $falseDecryptedMessage = $encryption->decrypt($encrypedMessage);
        $this->assertNull($falseDecryptedMessage);

        $encryption
            ->setFirstKey(random_bytes(64)) // False first secret key
            ->setSecondKey($this->secondKey);
        $falseDecryptedMessage = $encryption->decrypt($encrypedMessage);
        $this->assertNull($falseDecryptedMessage);
    }

    public function testExceptionOnFirstKeyWhileEncryption()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('First key not defined');
        $encryption = (new Encryption())
            ->setSecondKey($this->secondKey);

        // The data to be encrypted is not important
        $encrypedMessage = $encryption->crypt(random_bytes(64));
    }

    public function testExceptionOnSecondKeyWhileEncryption()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Second key not defined');
        $encryption = (new Encryption())
            ->setFirstKey($this->secondKey);

        // The data to be encrypted is not important
        $encrypedMessage = $encryption->crypt(random_bytes(64));
    }

    public function testWarningOnBadHashAlgorihtmWhileEncryption()
    {
        $this->expectException(Warning::class);
        $this->expectExceptionMessage('openssl_cipher_iv_length(): Unknown cipher algorithm');
        $encryption = (new Encryption(''))
            ->setFirstKey($this->secondKey)
            ->setSecondKey($this->secondKey);

        // The data to be encrypted is not important
        $encryption->crypt(random_bytes(64));
    }

    public function testWarningOnBadHashMethodWhileEncryption()
    {
        $this->expectException(Warning::class);
        $this->expectExceptionMessage('hash_hmac(): Unknown hashing algorithm:');
        $encryption = (new Encryption('aes-256-cbc', ''))
            ->setFirstKey($this->secondKey)
            ->setSecondKey($this->secondKey);

        // The data to be encrypted is not important
        $encryption->crypt(random_bytes(64));
    }

    public function testExceptionOnFirstKeyWhileDecryption()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('First key not defined');
        $encryption = (new Encryption())
            ->setSecondKey($this->firstKey);

        // The data to be decrypted is not important
        $decryptedMessage = $encryption->decrypt(random_bytes(64));
    }

    public function testExceptionOnSecondKeyWhileDecryption()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Second key not defined');
        $encryption = (new Encryption())
            ->setFirstKey($this->firstKey);

        // The data to be decrypted is not important
        $decryptedMessage = $encryption->decrypt(random_bytes(64));
    }

    public function testWarningOnBadHashAlgorihtmWhileDecryption()
    {
        $this->expectException(Warning::class);
        $this->expectExceptionMessage('openssl_cipher_iv_length(): Unknown cipher algorithm');
        $encryption = (new Encryption(''))
            ->setFirstKey($this->secondKey)
            ->setSecondKey($this->secondKey);

        // The data to be decrypted is not important
        $encryption->decrypt('456');
    }
}
