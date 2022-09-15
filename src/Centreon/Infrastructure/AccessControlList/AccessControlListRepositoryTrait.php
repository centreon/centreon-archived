<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Infrastructure\AccessControlList;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;

trait AccessControlListRepositoryTrait
{
    /**
     * @var ContactInterface|null
     */
    protected $contact;

    /**
     * @var AccessGroup[] List of access group used to filter the requests
     */
    protected $accessGroups = [];

    /**
     * @param ContactInterface $contact
     * @return $this
     */
    public function setContact(ContactInterface $contact): self
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * Sets the access groups that will be used to filter services and the host.
     *
     * @param AccessGroup[] $accessGroups
     * @return $this
     */
    public function filterByAccessGroups(array $accessGroups): self
    {
        $this->accessGroups = $accessGroups;
        return $this;
    }

    /**
     * @return bool Return FALSE if the contact is an admin or has at least one access group.
     */
    private function hasNotEnoughRightsToContinue(): bool
    {
        return ($this->contact !== null)
            ? !($this->contact->isAdmin() || count($this->accessGroups) > 0)
            : count($this->accessGroups) == 0;
    }
}
