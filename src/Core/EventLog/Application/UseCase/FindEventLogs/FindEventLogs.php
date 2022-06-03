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

namespace Core\EventLog\Application\UseCase\FindEventLogs;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\Configuration\User\UseCase\FindUsers\FindEventLogsErrorResponse;
use Core\EventLog\Application\Repository\ReadEventLogRepositoryInterface;
use Core\EventLog\Domain\EventLog;
use Core\Security\Application\Repository\ReadAccessGroupRepositoryInterface;

class FindEventLogs
{
    use LoggerTrait;

    public function __construct(
        private ContactInterface $user,
        private ReadEventLogRepositoryInterface $eventLogRepository,
        private ReadAccessGroupRepositoryInterface $accessGroupRepository
    ) {
    }

    public function __invoke(FindEventLogsPresenterInterface $presenter): void
    {
        try {
            $eventLogs = $this->getEventLogs();
            $presenter->present($this->createResponse($eventLogs));
        } catch (\Throwable $ex) {
            $errorResponse = new FindEventLogsErrorResponse();
            $this->error($errorResponse->getMessage(), ['trace' => $ex->getTraceAsString()]);
            $presenter->setResponseStatus($errorResponse);
        }
    }

    /**
     * @return EventLog[]
     * @throws \Throwable
     */
    private function getEventLogs(): array
    {
        if ($this->user->isAdmin()) {
            return $this->eventLogRepository->findAll();
        }

        $accessGroups = $this->accessGroupRepository->findByContact($this->user);

        return $this->eventLogRepository->findByAccessGroups($accessGroups);
    }

    /**
     * @param EventLog[] $eventLogs
     * @return FindEventLogsResponse
     */
    private function createResponse(array $eventLogs): FindEventLogsResponse
    {
        $response = new FindEventLogsResponse();
        foreach ($eventLogs as $eventLog) {
            $response->eventLogs[] = [
                'host_id' => $eventLog->getHostId()
            ];
        }
        return $response;
    }
}
