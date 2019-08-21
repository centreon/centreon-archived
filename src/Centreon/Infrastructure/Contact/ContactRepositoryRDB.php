<?php
/**
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
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
