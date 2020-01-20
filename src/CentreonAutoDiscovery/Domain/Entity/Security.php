<?php
/*
 * CENTREON
 *
 * Source Copyright 2005-2019 CENTREON
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@centreon.com
 *
*/

namespace CentreonAutoDiscovery\Domain\Entity;

/**
  * This class can be used to encrypt and decrypt data with two secure keys.
 *
 * @package CentreonAutoDiscovery\Domain\Entity
 */
class Security
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
        $firstEncrypted = substr($mix, $ivLength + 64);

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
     * @return Security
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
     * @return Security
     */
    public function setFirstKey(string $firstKey): self
    {
        $this->firstKey = $firstKey;
        return $this;
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
