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

namespace Core\Infrastructure\Configuration\NotificationPolicy\Repository;

use Pimple\Container;
use Centreon\Infrastructure\DatabaseConnection;
use Core\Application\Configuration\Notification\Repository\ReadServiceNotificationRepositoryInterface;
use Core\Domain\Configuration\Notification\Model\NotifiedContact;
use Core\Domain\Configuration\Notification\Model\NotifiedContactGroup;

class LegacyReadServiceNotificationRepository extends AbstractDbReadNotificationRepository implements
    ReadServiceNotificationRepositoryInterface
{
    /**
     * @var array<int,NotifiedContact[]>
     */
    private array $notifiedContacts = [];

    /**
     * @var array<int,NotifiedContactGroup[]>
     */
    private array $notifiedContactGroups = [];

    /**
     * @param DatabaseConnection $db
     * @param Container $dependencyInjector
     */
    public function __construct(
        DatabaseConnection $db,
        private Container $dependencyInjector,
    ) {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function findNotifiedContactsById(int $serviceId): array
    {
        if (!isset($this->notifiedContacts[$serviceId])) {
            $this->fetchNotifiedContactsAndContactGroups($serviceId);
        }

        return $this->notifiedContacts[$serviceId];
    }

    /**
     * @inheritDoc
     */
    public function findNotifiedContactGroupsById(int $serviceId): array
    {
        if (!isset($this->notifiedContactGroups[$serviceId])) {
            $this->fetchNotifiedContactsAndContactGroups($serviceId);
        }

        return $this->notifiedContactGroups[$serviceId];
    }

    /**
     * Initialize notified contacts and contactgroups for given service id
     *
     * @param int $serviceId
     */
    private function fetchNotifiedContactsAndContactGroups(int $serviceId): void
    {
        /**
         * Call to Legacy code to get the contacts and contactgroups
         * that will be notified for the Service regarding global
         * notification inheritance parameter.
         */
        $serviceInstance = \Service::getInstance($this->dependencyInjector);

        [
            'contact' => $notifiedContactIds,
            'cg' => $notifiedContactGroupIds,
        ] = $serviceInstance->getCgAndContacts($serviceId);

        $this->notifiedContacts[$serviceId] = $this->findContactsByIds($notifiedContactIds);
        $this->notifiedContactGroups[$serviceId] = $this->findContactGroupsByIds($notifiedContactGroupIds);
    }
}
