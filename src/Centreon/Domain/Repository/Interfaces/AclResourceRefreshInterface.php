<?php
namespace Centreon\Domain\Repository\Interfaces;

interface AclResourceRefreshInterface
{

    public function refresh(): void;
}
