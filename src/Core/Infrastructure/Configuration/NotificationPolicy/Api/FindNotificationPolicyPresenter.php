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

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\Configuration\NotificationPolicy\UseCase\FindNotificationPolicyPresenterInterface;
use Core\Infrastructure\Configuration\NotificationPolicy\Api\Hypermedia\ContactGroupHypermediaCreator;
use Core\Infrastructure\Configuration\NotificationPolicy\Api\Hypermedia\ContactHypermediaCreator;

class FindNotificationPolicyPresenter extends AbstractPresenter implements FindNotificationPolicyPresenterInterface
{
    /**
     * @param ContactHypermediaCreator $contactHypermediaCreator
     * @param ContactGroupHypermediaCreator $contactGroupHypermediaCreator
     * @param PresenterFormatterInterface $presenterFormatter
     */
    public function __construct(
        private ContactHypermediaCreator $contactHypermediaCreator,
        private ContactGroupHypermediaCreator $contactGroupHypermediaCreator,
        protected PresenterFormatterInterface $presenterFormatter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function present(mixed $response): void
    {
        $presenterResponse['contacts'] = array_map(
            fn (array $notifiedContact) => [
                'id' => $notifiedContact['id'],
                'name' => $notifiedContact['name'],
                'alias' => $notifiedContact['alias'],
                'email' => $notifiedContact['email'],
                'notifications' => $notifiedContact['notifications'],
                'configuration_uri' => $this->contactHypermediaCreator->createContactConfigurationUri($notifiedContact['id'])
            ],
            $response->notifiedContacts,
        );

        $presenterResponse['contact_groups'] = array_map(
            fn (array $notifiedContactGroup) => [
                'id' => $notifiedContactGroup['id'],
                'name' => $notifiedContactGroup['name'],
                'alias' => $notifiedContactGroup['alias'],
                'configuration_uri' => $this->contactGroupHypermediaCreator->createContactGroupConfigurationUri(
                    $notifiedContactGroup['id']
                ),
            ],
            $response->notifiedContactGroups,
        );

        $presenterResponse['is_notification_enabled'] = $response->isNotificationEnabled;

        $this->presenterFormatter->present($presenterResponse);
    }
}
