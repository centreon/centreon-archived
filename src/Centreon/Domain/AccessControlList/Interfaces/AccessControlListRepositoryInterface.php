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

namespace Centreon\Domain\AccessControlList\Interfaces;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Security\AccessGroup;

interface AccessControlListRepositoryInterface
{
    /**
     * @param ContactInterface $contact
     * @return $this
     */
    public function setContact(ContactInterface $contact);

    /**
     * Sets the access groups that will be used to filter services and the host.
     *
     * @param AccessGroup[] $accessGroups
     * @return $this
     */
    public function filterByAccessGroups(array $accessGroups);
}
