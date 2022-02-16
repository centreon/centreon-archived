<?php

namespace Core\Application\User\Repository;

use Core\Domain\User\Model\User;

interface ReadUserRepositoryInterface
{
    /**
     * Find a user by his alias.
     *
     * @param string $alias
     * @return User|null
     */
    public function findUserByAlias(string $alias): ?User;
}
