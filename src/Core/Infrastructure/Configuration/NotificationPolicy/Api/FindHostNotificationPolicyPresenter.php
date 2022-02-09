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

namespace Core\Infrastructure\Configuration\NotificationPolicy\Api;

use Symfony\Component\HttpFoundation\Response;
use Core\Infrastructure\Common\Presenter\PresenterTrait;
use Core\Application\Common\UseCase\ResponseStatusInterface;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaCreator;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\Configuration\NotificationPolicy\UseCase\FindHostNotificationPolicy\FindHostNotificationPolicyResponse;
use Core\Application\Configuration\NotificationPolicy\UseCase\FindHostNotificationPolicy\FindHostNotificationPolicyPresenterInterface;

class FindHostNotificationPolicyPresenter implements FindHostNotificationPolicyPresenterInterface
{
    use PresenterTrait;

    /**
     * @var ResponseStatusInterface|null
     */
    private $responseStatus;

    /**
     * @param HypermediaCreator $hypermediaCreator
     * @param PresenterFormatterInterface $presenterFormatter
     */
    public function __construct(
        private HypermediaCreator $hypermediaCreator,
        private PresenterFormatterInterface $presenterFormatter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function present(FindHostNotificationPolicyResponse $response): void
    {
        $presenterResponse['contacts'] = [];
        $presenterResponse['contact_groups'] = [];
        foreach ($response->contacts as $contact) {
            $presenterResponse['contacts'][] = [
                'id' => $contact['id'],
                'name' => $contact['name'],
                'alias' => $contact['alias'],
                'is_notified_on' => [
                    'host' => $contact['notified_on_host_events'],
                    'service' => $contact['notified_on_service_events']
                ],
                'timeperiod' => [
                    'host' => $contact['host_notification_time_period'],
                    'service' => $contact['service_notification_time_period']
                ]
            ];
        }

        foreach ($response->contactGroups as $contactGroup) {
            $presenterResponse['contact_groups'][] = [
                'id' => $contactGroup['id'],
                'name' => $contactGroup['name'],
                'alias' => $contactGroup['alias'],
            ];
        }
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
