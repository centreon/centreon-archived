<?php

namespace Centreon\Domain\Service;

class AppKeyGeneratorService implements KeyGeneratorInterface
{
    /**
     * Generate microtime based md5 hash of unique values
     * @return string
     */
    public function generateKey(): string
    {
        return md5(uniqid(rand(), true));
    }
}
