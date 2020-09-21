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

namespace Centreon\Domain\ActionLog;

use Centreon\Domain\ActionLog\Interfaces\ActionLogRepositoryInterface;
use Centreon\Domain\ActionLog\Interfaces\ActionLogServiceInterface;

/**
 * This class is designed to manage action log.
 *
 * @package Centreon\Domain\ActionLog
 */
class ActionLogService implements ActionLogServiceInterface
{
    /**
     * @var ActionLogRepositoryInterface
     */
    private $actionLogRepository;

    /**
     * ActionLogService constructor.
     *
     * @param ActionLogRepositoryInterface $actionLogRepository
     */
    public function __construct(ActionLogRepositoryInterface $actionLogRepository)
    {
        $this->actionLogRepository = $actionLogRepository;
    }

    /**
     * @inheritDoc
     */
    public function addLog(ActionLog $actionLog): void
    {
        try {
            $this->actionLogRepository->addLog($actionLog);
        } catch (\Throwable $ex) {
            throw new ActionLogException(_('Error when adding an entry in the action log'), 0, $ex);
        }
    }
}
