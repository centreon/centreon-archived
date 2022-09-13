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
use Core\Domain\RealTime\Model\Host as RealtimeHost;
use Centreon\Domain\Engine\EngineConfiguration;
use Core\Application\Common\UseCase\NotFoundResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Domain\Configuration\Notification\Model\NotifiedContact;
use Core\Domain\Configuration\Notification\Model\NotifiedContactGroup;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Core\Application\Configuration\Notification\Repository\ReadHostNotificationRepositoryInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface as ReadRealTimeHostRepositoryInterface;

class FindHostNotificationPolicy
{
    use LoggerTrait;

    /**
     * @param ReadHostNotificationRepositoryInterface $readHostNotificationRepository
     * @param HostConfigurationRepositoryInterface $hostRepository
     * @param EngineConfigurationServiceInterface $engineService
     * @param ReadAccessGroupRepositoryInterface $accessGroupRepository
     * @param ContactInterface $contact
     * @param ReadRealTimeHostRepositoryInterface $readRealTimeHostRepository
     */
    public function __construct(
        private ReadHostNotificationRepositoryInterface $readHostNotificationRepository,
        private HostConfigurationRepositoryInterface $hostRepository,
        private EngineConfigurationServiceInterface $engineService,
        private ReadAccessGroupRepositoryInterface $accessGroupRepository,
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
        $this->info('Searching for host notification policy', ['id' => $hostId]);

        $host = $this->findHost($hostId);
        if ($host === null) {
            $this->handleHostNotFound($hostId, $presenter);
            return;
        }

        $notifiedContacts = $this->readHostNotificationRepository->findNotifiedContactsById($hostId);
        $notifiedContactGroups = $this->readHostNotificationRepository->findNotifiedContactGroupsById($hostId);

        $realtimeHost = $this->readRealTimeHostRepository->findHostById($hostId);
        if ($realtimeHost === null) {
            $this->handleHostNotFound($hostId, $presenter);
            return;
        }

        $engineConfiguration = $this->engineService->findEngineConfigurationByHost($host);
        if ($engineConfiguration === null) {
            $this->handleEngineHostConfigurationNotFound($hostId, $presenter);
            return;
        }
        $this->overrideHostNotificationByEngineConfiguration($engineConfiguration, $realtimeHost);

        $presenter->present(
            $this->createResponse(
                $notifiedContacts,
                $notifiedContactGroups,
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
        $this->info('Searching for host configuration', ['id' => $hostId]);

        $host = null;

        if ($this->contact->isAdmin()) {
            $host = $this->hostRepository->findHost($hostId);
        } else {
            $accessGroups = $this->accessGroupRepository->findByContact($this->contact);
            $accessGroupIds = array_map(
                fn($accessGroup) => $accessGroup->getId(),
                $accessGroups
            );

            if ($this->readRealTimeHostRepository->isAllowedToFindHostByAccessGroupIds($hostId, $accessGroupIds)) {
                $host = $this->hostRepository->findHost($hostId);
            }
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
     * If engine configuration related to the host has notification disabled,
     * it overrides host notification status
     *
     * @param EngineConfiguration $engineConfiguration
     * @param RealtimeHost $realtimeHost
     */
    private function overrideHostNotificationByEngineConfiguration(
        EngineConfiguration $engineConfiguration,
        RealtimeHost $realtimeHost,
    ): void {
        if (
            $engineConfiguration->getNotificationsEnabledOption() ===
                EngineConfiguration::NOTIFICATIONS_OPTION_DISABLED
        ) {
            $realtimeHost->setNotificationEnabled(false);
        }
    }

    /**
     * @param NotifiedContact[] $notifiedContacts
     * @param NotifiedContactGroup[] $notifiedContactGroups
     * @param bool $isNotificationEnabled
     * @return FindNotificationPolicyResponse
     */
    public function createResponse(
        array $notifiedContacts,
        array $notifiedContactGroups,
        bool $isNotificationEnabled,
    ): FindNotificationPolicyResponse {
        return new FindNotificationPolicyResponse(
            $notifiedContacts,
            $notifiedContactGroups,
            $isNotificationEnabled,
        );
    }
}
