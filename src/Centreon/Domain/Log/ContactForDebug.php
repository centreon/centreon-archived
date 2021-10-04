<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Log;

use Centreon\Domain\Contact\Interfaces\ContactInterface;

/**
 * This class is designed to specify the unique contact for which messages will be logged.
 *
 * @package Centreon\Domain\Log
 */
class ContactForDebug
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @param int|string|null $identifier
     */
    public function __construct($identifier)
    {
        if ($identifier !== null) {
            if (is_numeric($identifier)) {
                $this->id = (int) $identifier;
            } elseif (is_string($identifier)) {
                $this->email = $identifier;
            }
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Indicates whether the logger can log messages for the given contact.
     * The comparison is made by comparing the id or email of the contact.
     * If no id or email has been defined the method will always return TRUE.
     *
     * @param ContactInterface $contact
     * @return bool
     */
    public function isValidForContact(ContactInterface $contact): bool
    {
        if ($this->id === null && $this->email === null) {
            return true;
        } elseif ($this->id !== null && $contact->getId() === $this->id) {
            return true;
        } elseif ($this->email !== null && $contact->getEmail() === $this->email) {
            return true;
        }
        return false;
    }
}
