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
use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Infrastructure\Common\Presenter\PresenterTrait;
use Core\Application\Common\UseCase\ResponseStatusInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\Configuration\NotificationPolicy\UseCase\FindNotificationPolicyPresenterInterface;
use Core\Infrastructure\Configuration\NotificationPolicy\Api\Hypermedia\UserGroupHypermediaCreator;
use Core\Infrastructure\Configuration\NotificationPolicy\Api\Hypermedia\UserHypermediaCreator;

class FindNotificationPolicyPresenter extends AbstractPresenter implements FindNotificationPolicyPresenterInterface
{
    use PresenterTrait;

    /**
     * @var ResponseStatusInterface|null
     */
    protected $responseStatus;

    /**
     * @param UserHypermediaCreator $userHypermediaCreator
     * @param UserGroupHypermediaCreator $userGroupHypermediaCreator
     * @param PresenterFormatterInterface $presenterFormatter
     */
    public function __construct(
        private UserHypermediaCreator $userHypermediaCreator,
        private UserGroupHypermediaCreator $userGroupHypermediaCreator,
        protected PresenterFormatterInterface $presenterFormatter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function present(mixed $response): void
    {
        $presenterResponse['users'] = array_map(
            fn (array $user) => [
                'id' => $user['id'],
                'name' => $user['name'],
                'alias' => $user['alias'],
                'email' => $user['email'],
                'is_notified_on' => $response->usersNotificationSettings[$user['id']]['is_notified_on'],
                'time_period' => $response->usersNotificationSettings[$user['id']]['time_period'],
                'configuration_uri' => $this->userHypermediaCreator->createUserConfigurationUri($user['id'])
            ],
            $response->users
        );

        $presenterResponse['user_groups'] = [];
        foreach ($response->userGroups as $userGroup) {
            $presenterResponse['user_groups'][] = [
                'id' => $userGroup['id'],
                'name' => $userGroup['name'],
                'alias' => $userGroup['alias'],
                'configuration_uri' => $this->userGroupHypermediaCreator->createUserGroupConfigurationUri(
                    $userGroup['id']
                )
            ];
        }

        $presenterResponse['is_notification_enabled'] = $response->isNotificationEnabled;

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
