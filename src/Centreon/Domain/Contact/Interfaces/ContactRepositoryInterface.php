<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

interface ContactRepositoryInterface
{
    /**
     * Find a contact by name.
     *
     * @param string $name Username
     * @return Contact|null
     */
    public function findByName(string $name): ?Contact;

    /**
     * Find a contact by id
     *
     * @param int $contactId Contact id
     * @return Contact|null
     */
    public function findById(int $contactId): ?Contact;

    /**
     * Find a contact based on their session id
     *
     * @param string $sessionId Session id
     * @return Contact|null
     */
    public function findBySession(string $sessionId): ?Contact;
}
