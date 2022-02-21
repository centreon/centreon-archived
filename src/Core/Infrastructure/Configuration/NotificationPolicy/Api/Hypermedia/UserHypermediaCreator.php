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

namespace Core\Infrastructure\Configuration\NotificationPolicy\Api\Hypermedia;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaProviderTrait;

class UserHypermediaCreator
{
    use HypermediaProviderTrait;

    private const URI_CONFIGURATION_USER = '/main.php?p=60301&o=c&contact_id={userId}';

    /**
     * @param ContactInterface $contact
     */
    public function __construct(
        private ContactInterface $contact
    ) {
    }

    /**
     * Create the configuration URI to the user regarding ACL
     *
     * @param integer $userId
     * @return string|null
     */
    public function createUserConfigurationUri(int $userId): ?string
    {
        return (
            $this->contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_USERS_CONTACTS__USERS_READ_WRITE)
            || $this->contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_USERS_CONTACTS__USERS_READ)
            || $this->contact->isAdmin()
        )
        ? $this->getBaseUri() . str_replace('{userId}', (string) $userId, self::URI_CONFIGURATION_USER)
        : null;
    }
}
