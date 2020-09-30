<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\ActionLog\Interfaces;

use Centreon\Domain\ActionLog\ActionLog;
use Centreon\Domain\Repository\RepositoryException;

interface ActionLogRepositoryInterface
{
    /**
     * Add action log.
     *
     * @param ActionLog $actionLog Action log to be added
     * @return int Return id of the last added action
     * @throws \Exception
     */
    public function addAction(ActionLog $actionLog): int;

    /**
     * Add details for the given action log.
     *
     * @param ActionLog $actionLog Action log for which you want to add details
     * @param array<string, string|int|bool> $details Details of action
     * @throws RepositoryException
     * @throws \Exception
     */
    public function addDetailsOfAction(ActionLog $actionLog, array $details): void;
}
