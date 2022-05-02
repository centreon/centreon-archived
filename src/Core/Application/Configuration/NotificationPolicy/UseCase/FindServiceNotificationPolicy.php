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
use Core\Domain\RealTime\Model\Service as RealtimeService;
use Centreon\Domain\Engine\EngineConfiguration;
use Core\Application\Common\UseCase\NotFoundResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Domain\Configuration\Notification\Model\NotifiedContact;
use Core\Domain\Configuration\Notification\Model\NotifiedContactGroup;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationRepositoryInterface;
use Core\Application\Configuration\Notification\Repository\ReadServiceNotificationRepositoryInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface as ReadRealTimeHostRepositoryInterface;
use Core\Application\RealTime\Repository\ReadServiceRepositoryInterface as ReadRealTimeServiceRepositoryInterface;

class FindServiceNotificationPolicy
{
    use LoggerTrait;

    /**
     * @param ReadServiceNotificationRepositoryInterface $readServiceNotificationRepository
     * @param HostConfigurationRepositoryInterface $hostRepository
     * @param ServiceConfigurationRepositoryInterface $serviceRepository
     * @param EngineConfigurationServiceInterface $engineService
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param ContactInterface $contact
     * @param ReadRealTimeHostRepositoryInterface $readRealTimeHostRepository
     * @param ReadRealTimeServiceRepositoryInterface $readRealTimeServiceRepository
     */
    public function __construct(
        private ReadServiceNotificationRepositoryInterface $readServiceNotificationRepository,
        private HostConfigurationRepositoryInterface $hostRepository,
        private ServiceConfigurationRepositoryInterface $serviceRepository,
        private EngineConfigurationServiceInterface $engineService,
        private AccessGroupRepositoryInterface $accessGroupRepository,
        private ContactInterface $contact,
        private ReadRealTimeHostRepositoryInterface $readRealTimeHostRepository,
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
        $this->info('Searching for service notification policy', ['host_id' => $hostId, 'service_id' => $serviceId]);

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

        $notifiedContacts = $this->readServiceNotificationRepository->findNotifiedContactsById($serviceId);
        $notifiedContactGroups = $this->readServiceNotificationRepository->findNotifiedContactGroupsById($serviceId);

        $realtimeService = $this->readRealTimeServiceRepository->findServiceById($hostId, $serviceId);
        if ($realtimeService === null) {
            $this->handleServiceNotFound($hostId, $serviceId, $presenter);
            return;
        }

        $engineConfiguration = $this->engineService->findEngineConfigurationByHost($host);
        if ($engineConfiguration === null) {
            $this->handleEngineHostConfigurationNotFound($hostId, $presenter);
            return;
        }
        $this->overrideServiceNotificationByEngineConfiguration($engineConfiguration, $realtimeService);

        $presenter->present(
            $this->createResponse(
                $notifiedContacts,
                $notifiedContactGroups,
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
     * Find service by id
     *
     * @param int $hostId
     * @param int $serviceId
     * @return Service|null
     */
    private function findService(int $hostId, int $serviceId): ?Service
    {
        $this->info('Searching for service configuration', ['host_id' => $hostId, 'service_id' => $serviceId]);

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
     * If engine configuration related to the host has notification disabled,
     * it overrides host notification status
     *
     * @param EngineConfiguration $engineConfiguration
     * @param RealtimeService $realtimeService
     */
    private function overrideServiceNotificationByEngineConfiguration(
        EngineConfiguration $engineConfiguration,
        RealtimeService $realtimeService,
    ): void {
        if (
            $engineConfiguration->getNotificationsEnabledOption() ===
                EngineConfiguration::NOTIFICATIONS_OPTION_DISABLED
        ) {
            $realtimeService->setNotificationEnabled(false);
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
