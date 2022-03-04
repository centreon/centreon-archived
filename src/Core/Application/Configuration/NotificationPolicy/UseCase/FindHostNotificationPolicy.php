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

namespace Core\Application\Configuration\NotificationPolicy\UseCase;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\HostConfiguration\Host;
use Core\Domain\Configuration\User\Model\User;
use Centreon\Domain\Engine\EngineConfiguration;
use Core\Application\Common\UseCase\NotFoundResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Domain\Configuration\UserGroup\Model\UserGroup;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Core\Domain\Configuration\Notification\Model\NotificationInterface;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Core\Application\Configuration\User\Repository\ReadUserRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Core\Application\Configuration\UserGroup\Repository\ReadUserGroupRepositoryInterface;
use Core\Application\Configuration\Notification\Repository\ReadNotificationRepositoryInterface;
use Core\Application\Configuration\NotificationPolicy\Repository\LegacyNotificationPolicyRepositoryInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface as ReadRealTimeHostRepositoryInterface;

class FindHostNotificationPolicy
{
    use LoggerTrait;

    /**
     * @param LegacyNotificationPolicyRepositoryInterface $legacyRepository
     * @param ReadNotificationRepositoryInterface $notificationRepository
     * @param ReadUserRepositoryInterface $userRepository
     * @param ReadUserGroupRepositoryInterface $userGroupRepository
     * @param HostConfigurationRepositoryInterface $hostRepository
     * @param EngineConfigurationServiceInterface $engineService
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param ContactInterface $contact
     * @param ReadRealTimeHostRepositoryInterface $readRealTimeHostRepository
     */
    public function __construct(
        private LegacyNotificationPolicyRepositoryInterface $legacyRepository,
        private ReadNotificationRepositoryInterface $notificationRepository,
        private ReadUserRepositoryInterface $userRepository,
        private ReadUserGroupRepositoryInterface $userGroupRepository,
        private HostConfigurationRepositoryInterface $hostRepository,
        private EngineConfigurationServiceInterface $engineService,
        private AccessGroupRepositoryInterface $accessGroupRepository,
        private ContactInterface $contact,
        private ReadRealTimeHostRepositoryInterface $readRealTimeHostRepository,
    ) {
    }

    /**
     * @param int $hostId
     * @param FindNotificationPolicyPresenterInterface $presenter
     */
    public function __invoke(
        int $hostId,
        FindNotificationPolicyPresenterInterface $presenter,
    ): void {
        $host = $this->findHost($hostId);
        if ($host === null) {
            $this->handleHostNotFound($hostId, $presenter);
            return;
        }

        /**
         * Returns the contacts and contactgroups notified for this Host
         */
        [
            'contact' => $notifiedUserIds,
            'cg' => $notifiedUserGroupIds,
        ] = $this->legacyRepository->findHostNotifiedUserIdsAndUserGroupIds($hostId);
        $users = $this->userRepository->findUsersByIds($notifiedUserIds);
        $usersNotificationSettings = $this->notificationRepository->findHostNotificationSettingsByUserIds(
            $notifiedUserIds
        );
        $userGroups = $this->userGroupRepository->findByIds($notifiedUserGroupIds);

        $realtimeHost = $this->readRealTimeHostRepository->findHostById($hostId);
        if ($realtimeHost === null) {
            $this->handleHostNotFound($hostId, $presenter);
            return;
        }

        // If engine configuration related to the host has notification disabled,
        // it overrides host configuration
        $engineConfiguration = $this->engineService->findEngineConfigurationByHost($host);
        if ($engineConfiguration === null) {
            $this->handleEngineHostConfigurationNotFound($hostId, $presenter);
            return;
        }
        if (
            $engineConfiguration->getNotificationsEnabledOption() ===
                EngineConfiguration::NOTIFICATIONS_OPTION_DISABLED
        ) {
            $realtimeHost->setNotificationEnabled(false);
        }

        $presenter->present(
            $this->createResponse(
                $users,
                $userGroups,
                $usersNotificationSettings,
                $realtimeHost->isNotificationEnabled(),
            )
        );
    }

    /**
     * Find host by id
     *
     * @param int $hostId
     * @return Host|null
     */
    private function findHost(int $hostId): ?Host
    {
        $this->info('Searching for host notification policy', ['id' => $hostId]);
        if ($this->contact->isAdmin()) {
            $host = $this->hostRepository->findHost($hostId);
        } else {
            $accessGroups = $this->accessGroupRepository->findByContact($this->contact);
            $accessGroupIds = array_map(
                fn($accessGroup) => $accessGroup->getId(),
                $accessGroups
            );
            $host = $this->hostRepository->findHostByAccessGroupIds($hostId, $accessGroupIds);
        }

        return $host;
    }

    /**
     * @param int $hostId
     * @param FindNotificationPolicyPresenterInterface $presenter
     */
    private function handleHostNotFound(
        int $hostId,
        FindNotificationPolicyPresenterInterface $presenter,
    ): void {
        $this->error(
            "Host not found",
            [
                'id' => $hostId,
                'userId' => $this->contact->getId(),
            ]
        );
        $presenter->setResponseStatus(new NotFoundResponse('Host'));
    }

    /**
     * @param int $hostId
     * @param FindNotificationPolicyPresenterInterface $presenter
     */
    private function handleEngineHostConfigurationNotFound(
        int $hostId,
        FindNotificationPolicyPresenterInterface $presenter,
    ): void {
        $this->error(
            "Engine configuration not found for Host",
            [
                'id' => $hostId,
                'userId' => $this->contact->getId(),
            ]
        );
        $presenter->setResponseStatus(new NotFoundResponse('Engine configuration'));
    }

    /**
     * @param User[] $users
     * @param UserGroup[] $userGroups
     * @param NotificationInterface[] $usersNotificationSettings
     * @param bool $isNotificationEnabled
     * @return FindNotificationPolicyResponse
     */
    public function createResponse(
        array $users,
        array $userGroups,
        array $usersNotificationSettings,
        bool $isNotificationEnabled,
    ): FindNotificationPolicyResponse {
        return new FindNotificationPolicyResponse(
            $users,
            $userGroups,
            $usersNotificationSettings,
            $isNotificationEnabled,
        );
    }
}
