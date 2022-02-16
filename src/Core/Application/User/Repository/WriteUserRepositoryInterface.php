<?php

namespace Core\Application\User\Repository;

use Core\Domain\User\Model\User;

interface WriteUserRepositoryInterface
{
    /**
     * Renew password of user.
     *
     * @param User $user
     */
    public function renewPassword(User $user): void;
}
