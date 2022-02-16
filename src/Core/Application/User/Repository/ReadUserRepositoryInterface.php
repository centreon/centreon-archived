<?php

namespace Core\Application\User\Repository;

use Core\Domain\User\Model\User;

interface ReadUserRepositoryInterface
{
    /**
     * Find a user with his alias.
     *
     * @return User|null
     */
    public function findUserByAlias(): ?User;
}
