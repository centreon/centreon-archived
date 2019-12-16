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
        $request = "SELECT * FROM `:db`.contact WHERE contact_id = :contact_id";
        $request = $this->translateDbName($request);
        
        $statement = $this->db->prepare($request);
        $statement->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);

        $contact = null;
        $statement->execute();

        if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $contact = $this->createContact($result);
            $this->findAndAddRules($contact);
        }
        return $contact;
    }

    /**
     * @inheritDoc
     */
    public function findByName(string $name): ?Contact
    {
        $request = 'SELECT * 
            FROM `:db`.contact 
            WHERE contact_alias = :username
            LIMIT 1';

        $request = $this->translateDbName($request);
        $statement = $this->db->prepare($request);
        $statement->bindValue(':username', $name, \PDO::PARAM_STR);

        $statement->execute();

        $contact = null;
        if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $contact = $this->createContact($result);
            $this->findAndAddRules($contact);
        }

        return $contact;
    }

    /**
     * @inheritDoc
     */
    public function findBySession(string $sessionId): ?Contact
    {
        $request = 'SELECT contact.*
            FROM `:db`.contact
            INNER JOIN `:db`.session
              on session.user_id = contact.contact_id
            WHERE session.session_id = :session_id
            LIMIT 1';

        $request = $this->translateDbName($request);
        $statement = $this->db->prepare($request);
        $statement->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        $statement->execute();

        $contact = null;
        if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $contact = $this->createContact($result);
            $this->findAndAddRules($contact);
        }

        return $contact;
    }

    /**
     * Find and add all rules for a contact.
     *
     * @param Contact $contact Contact for which we want to find and add all rules
     */
    private function findAndAddRules(Contact $contact): void
    {
        $request =
            'SELECT DISTINCT rules.acl_action_name
            FROM `:db`.contact contact
            LEFT JOIN `:db`.contactgroup_contact_relation cgcr
                ON cgcr.contact_contact_id = contact.contact_id
            LEFT JOIN `:db`.acl_group_contactgroups_relations gcgr
                ON gcgr.cg_cg_id = cgcr.contactgroup_cg_id
            LEFT JOIN `:db`.acl_group_contacts_relations gcr
                ON gcr.contact_contact_id = contact.contact_id
            LEFT JOIN `:db`.acl_group_actions_relations agar
                ON agar.acl_group_id = gcr.acl_group_id
                OR agar.acl_group_id = gcgr.acl_group_id
            LEFT JOIN `:db`.acl_actions actions
                ON actions.acl_action_id = agar.acl_action_id
            LEFT JOIN `:db`.acl_actions_rules rules
                ON rules.acl_action_rule_id = actions.acl_action_id
            WHERE contact.contact_id = :contact_id 
                AND rules.acl_action_name IS NOT NULL
            ORDER BY contact.contact_id, rules.acl_action_name';

        $request = $this->translateDbName($request);
        $statement = $this->db->prepare($request);
        $statement->bindValue(':contact_id', $contact->getId(), \PDO::PARAM_INT);
        $statement->execute();

        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->addSpecificRule($contact, $result['acl_action_name']);
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
            ->setEncodedPassword($contact['contact_passwd'])
            ->setAccessToApiRealTime($contact['reach_api_rt'] === '1')
            ->setAccessToApiConfiguration($contact['reach_api'] === '1');
    }

    /**
     * Add a specific rule to contact.
     *
     * @param Contact $contact Contact for which we want to add rule
     * @param string $ruleName Rule to add
     */
    private function addSpecificRule(Contact $contact, string $ruleName): void
    {
        switch ($ruleName) {
            case 'host_acknowledgement':
                $contact->addRole(Contact::ROLE_HOST_ACKNOWLEDGEMENT);
                break;
            case 'host_disacknowledgement':
                $contact->addRole(Contact::ROLE_HOST_DISACKNOWLEDGEMENT);
                break;
            case 'service_acknowledgement':
                $contact->addRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT);
                break;
            case 'service_disacknowledgement':
                $contact->addRole(Contact::ROLE_SERVICE_DISACKNOWLEDGEMENT);
                break;
            case 'service_schedule_downtime':
                $contact->addRole(Contact::ROLE_ADD_SERVICE_DOWNTIME);
                $contact->addRole(Contact::ROLE_CANCEL_SERVICE_DOWNTIME);
                break;
            case 'host_schedule_downtime':
                $contact->addRole(Contact::ROLE_ADD_HOST_DOWNTIME);
                $contact->addRole(Contact::ROLE_CANCEL_HOST_DOWNTIME);
                break;
        }
    }

    /**
     * Replace all instances of :dbstg and :db by the real db names.
     * The table names of the database are defined in the services.yaml
     * configuration file.
     *
     * @param string $request Request to translate
     * @return string Request translated
     */
    protected function translateDbName(string $request): string
    {
        return str_replace(
            array(':dbstg', ':db'),
            array($this->db->getStorageDbName(), $this->db->getCentreonDbName()),
            $request
        );
    }
}
