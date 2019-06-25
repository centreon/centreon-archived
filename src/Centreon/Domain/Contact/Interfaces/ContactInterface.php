<?php

namespace Centreon\Domain\Contact\Interfaces;

interface ContactInterface
{
    public function isAdmin(): bool;

    public function isActive(): bool;
}