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

namespace Centreon\Domain\Contact\Interfaces;

use Centreon\Domain\Contact\Contact;

interface ContactServiceInterface
{
    /**
     * Find a contact based on their session number.
     *
     * @param string $session Contact session number
     * @return Contact|null
     */
    public function findBySession(string $session): ?Contact;

    /**
     * Find a contact based on its id
     *
     * @param integer $id
     * @return Contact|null
     */
    public function findContact(int $id): ?Contact;

    /**
     * Indicates whether or not the contact exists.
     *
     * @param ContactInterface $contact
     * @return bool
     */
    public function exists(ContactInterface $contact): bool;

    /**
     * Add the contact.
     *
     * @param ContactInterface $contact Contact to be added
     */
    public function addUser(ContactInterface $contact): void;

    /**
     * Update the contact.
     *
     * @param ContactInterface $contact Contact to be updated
     */
    public function updateUser(ContactInterface $contact): void;

    /**
     * Update the user's default page.
     *
     * @param ContactInterface $contact
     */
    public function updateUserDefaultPage(ContactInterface $contact): void;
}
