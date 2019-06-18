<?php


namespace Centreon\Domain\Entity\Interfaces;


interface ContactInterface
{
    public function isAdmin(): bool;

    public function isActive(): bool;
}