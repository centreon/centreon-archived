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
use Centreon\Domain\ServiceConfiguration\Service;
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
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationRepositoryInterface;
use Core\Application\Configuration\UserGroup\Repository\ReadUserGroupRepositoryInterface;
use Core\Application\Configuration\Notification\Repository\ReadNotificationRepositoryInterface;
use Core\Application\Configuration\NotificationPolicy\Repository\LegacyNotificationPolicyRepositoryInterface;
use Core\Application\RealTime\Repository\ReadServiceRepositoryInterface as ReadRealTimeServiceRepositoryInterface;

class FindServiceNotificationPolicy
{
    use LoggerTrait;

    /**
     * @param LegacyNotificationPolicyRepositoryInterface $legacyRepository
     * @param ReadNotificationRepositoryInterface $notificationRepository
     * @param ReadUserRepositoryInterface $userRepository
     * @param ReadUserGroupRepositoryInterface $userGroupRepository
     * @param HostConfigurationRepositoryInterface $hostRepository
     * @param ServiceConfigurationRepositoryInterface $serviceRepository
     * @param EngineConfigurationServiceInterface $engineService
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param ContactInterface $contact
     * @param ReadRealTimServiceRepositoryInterface $readRealTimeServiceRepository
     */
    public function __construct(
        private LegacyNotificationPolicyRepositoryInterface $legacyRepository,
        private ReadNotificationRepositoryInterface $notificationRepository,
        private ReadUserRepositoryInterface $userRepository,
        private ReadUserGroupRepositoryInterface $userGroupRepository,
        private HostConfigurationRepositoryInterface $hostRepository,
        private ServiceConfigurationRepositoryInterface $serviceRepository,
        private EngineConfigurationServiceInterface $engineService,
        private AccessGroupRepositoryInterface $accessGroupRepository,
        private ContactInterface $contact,
        private ReadRealTimeServiceRepositoryInterface $readRealTimeServiceRepository,
    ) {
    }

    /**
     * @param int $hostId
     * @param int $serviceId
     * @param FindNotificationPolicyPresenterInterface $presenter
     */
    public function __invoke(
        int $hostId,
        int $serviceId,
        FindNotificationPolicyPresenterInterface $presenter,
    ): void {
        $host = $this->findHost($hostId);
        if ($host === null) {
            $this->handleHostNotFound($hostId, $presenter);
            return;
        }

        $service = $this->findService($hostId, $serviceId);
        if ($service === null) {
            $this->handleServiceNotFound($hostId, $serviceId, $presenter);
            return;
        }

        /**
         * Returns the contacts and contactgroups notified for this Host
         */
        [
            'contact' => $notifiedUserIds,
            'cg' => $notifiedUserGroupIds,
        ] = $this->legacyRepository->findServiceNotifiedUserIdsAndUserGroupIds($serviceId);
        $users = $this->userRepository->findUsersByIds($notifiedUserIds);
        $usersNotificationSettings = $this->notificationRepository->findServiceNotificationSettingsByUserIds(
            $notifiedUserIds
        );
        $userGroups = $this->userGroupRepository->findByIds($notifiedUserGroupIds);

        $realtimeService = $this->readRealTimeServiceRepository->findServiceById($hostId, $serviceId);
        if ($realtimeService === null) {
            $this->handleServiceNotFound($hostId, $serviceId, $presenter);
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
            $realtimeService->setNotificationEnabled(false);
        }

        $presenter->present(
            $this->createResponse(
                $users,
                $userGroups,
                $usersNotificationSettings,
                $realtimeService->isNotificationEnabled(),
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
     * Find service by id
     *
     * @param int $hostId
     * @param int $serviceId
     * @return Service|null
     */
    private function findService(int $hostId, int $serviceId): ?Service
    {
        $this->info('Searching for host notification policy', ['host_id' => $hostId, 'service_id' => $serviceId]);

        $service = null;

        if ($this->contact->isAdmin()) {
            $service = $this->serviceRepository->findService($serviceId);
        } else {
            $accessGroups = $this->accessGroupRepository->findByContact($this->contact);
            $accessGroupIds = array_map(
                fn($accessGroup) => $accessGroup->getId(),
                $accessGroups
            );

            if (
                $this->readRealTimeServiceRepository->isAllowedToFindServiceByAccessGroupIds(
                    $hostId,
                    $serviceId,
                    $accessGroupIds,
                )
            ) {
                $service = $this->serviceRepository->findService($serviceId);
            }
        }

        return $service;
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
     * @param int $serviceId
     * @param FindNotificationPolicyPresenterInterface $presenter
     */
    private function handleServiceNotFound(
        int $hostId,
        int $serviceId,
        FindNotificationPolicyPresenterInterface $presenter,
    ): void {
        $this->error(
            "Service not found",
            [
                'host_id' => $hostId,
                'service_id' => $serviceId,
                'userId' => $this->contact->getId(),
            ]
        );

        $presenter->setResponseStatus(new NotFoundResponse('Service'));
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
                'host_id' => $hostId,
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
