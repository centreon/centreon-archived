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

namespace Core\Infrastructure\RealTime\Api\FindMetaService;

use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaService;
use Symfony\Component\HttpFoundation\Response;
use Core\Application\Common\UseCase\ResponseStatusInterface;
use Core\Application\RealTime\UseCase\FindMetaService\FindMetaServiceResponse;
use Core\Application\RealTime\UseCase\FindMetaService\FindMetaServicePresenterInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Infrastructure\Common\Presenter\PresenterTrait;

class FindMetaServicePresenter implements FindMetaServicePresenterInterface
{
    use PresenterTrait;

    /**
     * @var ResponseStatusInterface|null
     */
    private $responseStatus;

    /**
     * @param HypermediaService $hypermediaService
     * @param PresenterFormatterInterface $presenterFormatter
     */
    public function __construct(
        private HypermediaService $hypermediaService,
        private PresenterFormatterInterface $presenterFormatter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function present(FindMetaServiceResponse $response): void
    {
        $presenterResponse = [
            'uuid' => 'm' . $response->id,
            'id' => $response->id,
            'name' => $response->name,
            'type' => 'metaservice',
            'short_type' => 'm',
            'status' => $response->status,
            'in_downtime' => $response->isInDowntime,
            'acknowledged' => $response->isAcknowledged,
            'flapping' => $response->isFlapping,
            'performance_data' => $response->performanceData,
            'information' => $response->output,
            'command_line' => $response->commandLine,
            'notification_number' => $response->notificationNumber,
            'latency' => $response->latency,
            'percent_state_change' => $response->statusChangePercentage,
            'passive_checks' => $response->hasPassiveChecks,
            'execution_time' => $response->executionTime,
            'active_checks' => $response->hasActiveChecks,
            'groups' => [],
            'parent' => null,
            'monitoring_server_name' => null
        ];

        $acknowledgement = null;

        if (!empty($response->acknowledgement)) {
            /**
             * Convert Acknowledgement dates into ISO 8601 format
             */
            $acknowledgement = $response->acknowledgement;
            $acknowledgement['entry_time'] = $this->formatDateToIso8601($response->acknowledgement['entry_time']);
            $acknowledgement['deletion_time'] = $this->formatDateToIso8601($response->acknowledgement['deletion_time']);
        }

        $presenterResponse['acknowledgement'] = $acknowledgement;

        /**
         * Convert downtime dates into ISO 8601 format
         */
        $formattedDatesDowntimes = [];

        foreach ($response->downtimes as $key => $downtime) {
            $formattedDatesDowntimes[$key] = $downtime;
            $formattedDatesDowntimes[$key]['start_time'] = $this->formatDateToIso8601($downtime['start_time']);
            $formattedDatesDowntimes[$key]['end_time'] = $this->formatDateToIso8601($downtime['end_time']);
            $formattedDatesDowntimes[$key]['actual_start_time'] =
                $this->formatDateToIso8601($downtime['actual_start_time']);
            $formattedDatesDowntimes[$key]['actual_end_time'] =
                $this->formatDateToIso8601($downtime['actual_end_time']);
            $formattedDatesDowntimes[$key]['entry_time'] = $this->formatDateToIso8601($downtime['entry_time']);
            $formattedDatesDowntimes[$key]['deletion_time'] = $this->formatDateToIso8601($downtime['deletion_time']);
        }

        $presenterResponse['downtimes'] = $formattedDatesDowntimes;

        /**
         * Calculate the duration
         */
        $presenterResponse['duration'] = $response->lastStatusChange !== null
            ? \CentreonDuration::toString(time() - $response->lastStatusChange->getTimestamp())
            : null;

        /**
         * Convert dates to ISO 8601 format
         */
        $presenterResponse['next_check'] = $this->formatDateToIso8601($response->nextCheck);
        $presenterResponse['last_check'] = $this->formatDateToIso8601($response->lastCheck);
        $presenterResponse['last_time_with_no_issue'] = $this->formatDateToIso8601($response->lastTimeOk);
        $presenterResponse['last_status_change'] = $this->formatDateToIso8601($response->lastStatusChange);
        $presenterResponse['last_notification'] = $this->formatDateToIso8601($response->lastNotification);

        /**
         * Creating the 'tries' entry
         */
        $tries = $response->checkAttempts . '/' . $response->maxCheckAttempts;
        $statusType = $response->status['type'] === 0 ? 'S' : 'H';
        $presenterResponse['tries'] = $tries . '(' . $statusType . ')';

        /**
         * Creating Hypermedias
         */
        $presenterResponse['links'] = [
            'uris' => $this->hypermediaService->createInternalUris($response),
            'endpoints' => $this->hypermediaService->createEndpoints($response),
        ];
        $this->presenterFormatter->present($presenterResponse);
    }

    /**
     * @inheritDoc
     */
    public function show(): Response
    {
        if ($this->getResponseStatus() !== null) {
            $this->presenterFormatter->present($this->getResponseStatus());
        }
        return $this->presenterFormatter->show();
    }

    /**
     * @inheritDoc
     */
    public function setResponseStatus(?ResponseStatusInterface $responseStatus): void
    {
        $this->responseStatus = $responseStatus;
    }

    /**
     * @inheritDoc
     */
    public function getResponseStatus(): ?ResponseStatusInterface
    {
        return $this->responseStatus;
    }
}
