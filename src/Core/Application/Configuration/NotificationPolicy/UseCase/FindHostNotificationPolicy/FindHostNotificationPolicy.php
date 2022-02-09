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

namespace Core\Application\Configuration\NotificationPolicy\UseCase\FindHostNotificationPolicy;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\Engine\EngineConfiguration;
use Core\Domain\Configuration\Contact\Model\Contact;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Domain\Configuration\ContactGroup\Model\ContactGroup;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Core\Application\Configuration\Contact\Repository\ReadContactRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Core\Application\Configuration\ContactGroup\Repository\ReadContactGroupRepositoryInterface;
use Core\Application\Configuration\NotificationPolicy\Repository\LegacyNotificationPolicyRepositoryInterface;

class FindHostNotificationPolicy
{
    use LoggerTrait;

    public function __construct(
        private LegacyNotificationPolicyRepositoryInterface $legacyRepository,
        private HostConfigurationRepositoryInterface $hostRepository,
        private EngineConfigurationServiceInterface $engineService,
        private ReadContactRepositoryInterface $contactRepository,
        private ReadContactGroupRepositoryInterface $contactGroupRepository,
        private ContactInterface $contact,
        private AccessGroupRepositoryInterface $accessGroupRepository
    ) {
    }

    /**
     * @param int $hostId
     * @param FindHostNotificationPolicyPresenterInterface $presenter
     */
    public function __invoke(
        int $hostId,
        FindHostNotificationPolicyPresenterInterface $presenter
    ): void {
        $this->info('Searching for host notification policy', ['id' => $hostId]);
        if ($this->contact->isAdmin()) {
            $host = $this->hostRepository->findHost($hostId);
            if ($host === null) {
                $this->handleHostNotFound($hostId, $presenter);
                return;
            }
        } else {
            $accessGroups = $this->accessGroupRepository->findByContact($this->contact);
            $accessGroupIds = array_map(
                fn($accessGroup) => $accessGroup->getId(),
                $accessGroups
            );
            $host = $this->hostRepository->findHostByAccessGroupIds($hostId, $accessGroupIds);
            if ($host === null) {
                $this->handleHostNotFound($hostId, $presenter);
                return;
            }
        }

        // check if notifications are enabled.
        switch ($host->getNotificationsEnabled()) {
            case Host::NOTIFICATION_DISABLED:
                $this->handleNoNotificationsEnabled($hostId, $presenter);
                return;
            case Host::NOTIFICATION_DEFAULT_ENGINE_VALUE:
                // GET default value set on engine side
                $engineConfiguration = $this->engineService->findEngineConfigurationByHost($host);
                if ($engineConfiguration === null) {
                    $this->handleEngineHostConfigurationNotFound($hostId, $presenter);
                    return;
                }
                if ($engineConfiguration->getNotificationsEnabled() === EngineConfiguration::NOTIFICATIONS_DISABLED) {
                    $this->handleNoNotificationsEnabled($hostId, $presenter);
                    return;
                }
            default:
                break;
        }

        $notificationPolicy = $this->legacyRepository->findHostNotificationPolicy($hostId);
        $contacts = $this->contactRepository->findByIds($notificationPolicy['contact']);
        $contactGroups = $this->contactGroupRepository->findByIds($notificationPolicy['cg']);

        $presenter->present($this->createResponse($contacts, $contactGroups));
    }

    /**
     * @param int $hostId
     * @param FindHostNotificationPolicyPresenterInterface $presenter
     * @return void
     */
    private function handleHostNotFound(int $hostId, FindHostNotificationPolicyPresenterInterface $presenter)
    {
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
     * @param FindHostNotificationPolicyPresenterInterface $presenter
     * @return void
     */
    private function handleEngineHostConfigurationNotFound(
        int $hostId,
        FindHostNotificationPolicyPresenterInterface $presenter
    ) {
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
     * @param int $hostId
     * @param FindHostNotificationPolicyPresenterInterface $presenter
     * @return void
     */
    private function handleNoNotificationsEnabled(int $hostId, FindHostNotificationPolicyPresenterInterface $presenter)
    {
        $this->info(
            "No notifications enabled for this host",
            [
                'id' => $hostId
            ]
        );
        $presenter->setResponseStatus(new NoContentResponse());
    }

    /**
     * @param Contact[] $contacts
     * @param ContactGroup[] $contactGroups
     * @return FindHostNotificationPolicyResponse
     */
    public function createResponse(array $contacts, array $contactGroups): FindHostNotificationPolicyResponse
    {
        return new FindHostNotificationPolicyResponse($contacts, $contactGroups);
    }
}
