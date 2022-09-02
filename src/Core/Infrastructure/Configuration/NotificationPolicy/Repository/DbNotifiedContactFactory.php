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

use Core\Domain\Configuration\Notification\Model\NotifiedContact;

class DbNotifiedContactFactory
{
    /**
     * @param array<string,int|string|null> $contact
     * @return NotifiedContact
     */
    public static function createFromRecord(array $contact): NotifiedContact
    {
        $hostNotification = DbContactHostNotificationFactory::createFromRecord($contact);

        $serviceNotification = DbContactServiceNotificationFactory::createFromRecord($contact);

        return new NotifiedContact(
            (int) $contact['contact_id'],
            (string) $contact['contact_name'],
            (string) $contact['contact_alias'],
            (string) $contact['contact_email'],
            $hostNotification,
            $serviceNotification,
        );
    }
}
