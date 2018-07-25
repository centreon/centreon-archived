<?php

namespace Centreon\Domain\Service;

class AppKeyGeneratorService
{
    /**
     * Generate microtime based md5 hash of unique values
     * @return string
     */
    public function generateKey(): string
    {
        return md5(uniqid(rand(), TRUE));
    }
}