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

namespace Core\Infrastructure\RealTime\Api\FindHost;

use CentreonDuration;
use Symfony\Component\HttpFoundation\Response;
use Core\Infrastructure\Common\Api\HttpUrlTrait;
use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Infrastructure\Common\Presenter\PresenterTrait;
use Core\Application\Common\UseCase\ResponseStatusInterface;
use Core\Infrastructure\RealTime\Hypermedia\HypermediaCreator;
use Core\Application\RealTime\UseCase\FindHost\FindHostResponse;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\RealTime\UseCase\FindHost\FindHostPresenterInterface;

class FindHostPresenter extends AbstractPresenter implements FindHostPresenterInterface
{
    use PresenterTrait;
    use HttpUrlTrait;

    /**
     * @param HypermediaCreator $hypermediaCreator
     * @param PresenterFormatterInterface $presenterFormatter
     */
    public function __construct(
        private HypermediaCreator $hypermediaCreator,
        protected PresenterFormatterInterface $presenterFormatter
    ) {
        parent::__construct($presenterFormatter);
    }

    /**
     * {@inheritDoc}
     *
     * @param FindHostResponse $response
     */
    public function present(mixed $response): void
    {
        $presenterResponse = [
            'uuid' => 'h' . $response->hostId,
            'id' => $response->hostId,
            'name' => $response->name,
            'monitoring_server_name' => $response->monitoringServerName,
            'type' => 'host',
            'short_type' => 'h',
            'fqdn' => $response->address,
            'alias' => $response->alias,
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
            'parent' => null,
            'icon' => $response->icon,
            'groups' => $this->hypermediaCreator->convertGroupsForPresenter($response),
            'categories' => $this->hypermediaCreator->convertCategoriesForPresenter($response),
            'severity' => $response->severity,
        ];

        if ($presenterResponse['severity'] !== null) {
            // normalize the URL to the severity icon
            $presenterResponse['severity']['icon']['url'] = $this->getBaseUri()
                . '/img/media/' . $response->severity['icon']['url'];
        }

        $acknowledgement = null;

        if (!empty($response->acknowledgement)) {
            // Convert Acknowledgement dates into ISO 8601 format
            $acknowledgement = $response->acknowledgement;
            $acknowledgement['entry_time'] = $this->formatDateToIso8601($response->acknowledgement['entry_time']);
            $acknowledgement['deletion_time'] = $this->formatDateToIso8601($response->acknowledgement['deletion_time']);
        }

        $presenterResponse['acknowledgement'] = $acknowledgement;

        // Convert downtime dates into ISO 8601 format
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
         * Remove ':' character from the timezone string
         */
        $presenterResponse['timezone'] = !empty($response->timezone)
            ? preg_replace('/^:/', '', $response->timezone)
            : null;

        /**
         * Calculate the duration
         */
        $presenterResponse['duration'] = $response->lastStatusChange !== null
            ? CentreonDuration::toString(time() - $response->lastStatusChange->getTimestamp())
            : null;

        /**
         * Convert dates to ISO 8601 format
         */
        $presenterResponse['next_check'] = $this->formatDateToIso8601($response->nextCheck);
        $presenterResponse['last_check'] = $this->formatDateToIso8601($response->lastCheck);
        $presenterResponse['last_time_with_no_issue'] = $this->formatDateToIso8601($response->lastTimeUp);
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
        $parameters = [
            'type' => $response->type,
            'hostId' => $response->hostId
        ];

        $endpoints = $this->hypermediaCreator->createEndpoints($parameters);

        $presenterResponse['links']['endpoints'] = [
            'notification_policy' => $endpoints['notification_policy'],
            'timeline' => $endpoints['timeline'],
            'timeline_download' => $endpoints['timeline_download'],
            'details' => $endpoints['details']
        ];

        $presenterResponse['links']['uris'] = $this->hypermediaCreator->createInternalUris($parameters);

        parent::present($presenterResponse);
    }
}
