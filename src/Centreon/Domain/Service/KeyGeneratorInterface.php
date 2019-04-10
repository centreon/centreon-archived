<?php

namespace Centreon\Domain\Service;

interface KeyGeneratorInterface
{
    public function generateKey() : string;
}
