<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

declare(strict_types=1);

namespace Core\Platform\Infrastructure\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Core\Platform\Application\Repository\UpdateLockerRepositoryInterface;
use Core\Platform\Application\Repository\UpdateLockerException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class SymfonyUpdateLockerRepository implements UpdateLockerRepositoryInterface
{
    use LoggerTrait;

    private const LOCK_NAME = 'update-centreon';

    /**
     * @var LockInterface
     */
    private LockInterface $lock;

    /**
     * @param LockFactory $lockFactory
     */
    public function __construct(
        LockFactory $lockFactory,
    ) {
        $this->lock = $lockFactory->createLock(self::LOCK_NAME);
    }

    /**
     * @inheritDoc
     */
    public function lock(): bool
    {
        $this->info('Locking centreon update process on filesystem...');

        try {
            return $this->lock->acquire();
        } catch (\Throwable $ex) {
            $this->error($ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            throw UpdateLockerException::errorWhileLockingUpdate($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function unlock(): void
    {
        $this->info('Unlocking centreon update process from filesystem...');

        try {
            $this->lock->release();
        } catch (\Throwable $ex) {
            $this->error($ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            throw UpdateLockerException::errorWhileUnlockingUpdate($ex);
        }
    }
}
