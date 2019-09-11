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

namespace Centreon\Infrastructure\Contact;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;

/**
 * Database repository for the contacts.
 *
 * @package Centreon\Infrastructure\Contact
 */
final class ContactRepositoryRDB implements ContactRepositoryInterface
{
    /**
     * @var DatabaseConnection
     */
    private $db;

    /**
     * ContactRepositoryRDB constructor.
     * @param DatabaseConnection $pdo
     */
    public function __construct(DatabaseConnection $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $contactId): ?Contact
    {
        $prepare = $this->db->prepare(
            "SELECT * FROM contact WHERE contact_id = :contact_id"
        );
        $prepare->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);

        $contact = null;
        if ($prepare->execute()
            && ($result = $prepare->fetch(\PDO::FETCH_ASSOC))
        ) {
            $contact = $this->createContact($result);
        }

        return $contact;
    }

    /**
     * @inheritDoc
     */
    public function findByName(string $name): ?Contact
    {
        $statement = $this->db->prepare(
            'SELECT * 
            FROM contact 
            WHERE contact_alias = :username
            LIMIT 1'
        );
        $statement->bindValue(':username', $name, \PDO::PARAM_STR);
        if ($statement->execute()
            && ($result = $statement->fetch(\PDO::FETCH_ASSOC))
        ) {
            $contact = $this->createContact($result);
            return $contact;
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function findBySession(string $sessionId): ?Contact
    {
        $statement = $this->db->prepare(
            'SELECT contact.*
            FROM contact
            INNER JOIN session
              on session.user_id = contact.contact_id
            WHERE session.session_id = :session_id
            LIMIT 1'
        );
        $statement->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        if ($statement->execute()
            && ($result = $statement->fetch(\PDO::FETCH_ASSOC))
        ) {
            $contact = $this->createContact($result);
            return $contact;
        } else {
            return null;
        }
    }

    /**
     * Create a contact based on the data.
     *
     * @param array $contact Array of values representing the contact informations
     * @return Contact Returns a new instance of contact
     */
    private function createContact(array $contact): Contact
    {
        return (new Contact())
            ->setId((int) $contact['contact_id'])
            ->setName($contact['contact_name'])
            ->setAlias($contact['contact_alias'])
            ->setEmail($contact['contact_email'])
            ->setTemplateId($contact['contact_template_id'])
            ->setIsActive($contact['contact_activate'] === '1')
            ->setAdmin($contact['contact_admin'] === '1')
            ->setToken($contact['contact_autologin_key'])
            ->setEncodedPassword($contact['contact_passwd']);
    }
}
