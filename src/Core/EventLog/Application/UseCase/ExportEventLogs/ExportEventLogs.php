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

namespace Core\EventLog\Application\UseCase\ExportEventLogs;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\EventLog\Application\Repository\ReadEventLogRepositoryInterface;
use Core\EventLog\Domain\EventLog;
use Core\Security\Application\Repository\ReadAccessGroupRepositoryInterface;

class ExportEventLogs
{
    use LoggerTrait;

    public function __construct(
        private ContactInterface $contact,
        private ReadEventLogRepositoryInterface $eventLogRepository,
        private ReadAccessGroupRepositoryInterface $accessGroupRepository
    ) {
    }

    public function __invoke(ExportEventLogsPresenterInterface $presenter): void
    {
        try {
            $eventLogs = $this->getEventLogs();
            $presenter->present($this->createResponse($eventLogs));
        } catch (\Throwable $ex) {
            $errorResponse = new ExportEventLogsErrorResponse();
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
        if ($this->contact->isAdmin()) {
            return $this->eventLogRepository->findAll();
        }

        $contactAccessGroups = $this->accessGroupRepository->findByContact($this->contact);

        return $this->eventLogRepository->findByAccessGroups($contactAccessGroups);
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
