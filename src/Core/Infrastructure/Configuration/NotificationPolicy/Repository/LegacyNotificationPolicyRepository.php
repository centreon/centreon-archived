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
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\Configuration\NotificationPolicy\Repository\LegacyNotificationPolicyRepositoryInterface;

class LegacyNotificationPolicyRepository extends AbstractRepositoryDRB implements
    LegacyNotificationPolicyRepositoryInterface
{
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
    public function findHostNotifiedUserIdsAndUserGroupIds(int $hostId): array
    {
        /**
         * Call to Legacy code to get the contacts and contactgroups
         * that will be notified for the Host regarding global
         * notification inheritance parameter.
         */
        $hostInstance = \Host::getInstance($this->dependencyInjector);
        $notifications = $hostInstance->getCgAndContacts($hostId);

        return $notifications;
    }

    /**
     * @inheritDoc
     */
    public function findServiceNotifiedUserIdsAndUserGroupIds(int $serviceId): array
    {
        /**
         * Call to Legacy code to get the contacts and contactgroups
         * that will be notified for the Host regarding global
         * notification inheritance parameter.
         */
        $serviceInstance = \Service::getInstance($this->dependencyInjector);
        $notifications = $serviceInstance->getCgAndContacts($serviceId);

        return $notifications;
    }
}
