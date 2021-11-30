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

namespace Centreon\Infrastructure\Contact;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface;
use Centreon\Domain\Menu\Model\Page;
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
        $request = $this->translateDbName(
            'SELECT contact.*, cp.password AS contact_passwd, t.topology_url,
            t.topology_url_opt, t.is_react, t.topology_id, tz.timezone_name
            FROM `:db`.contact
            LEFT JOIN `:db`.contact_password cp
                ON cp.contact_id = contact.contact_id
            LEFT JOIN `:db`.timezone tz
                ON tz.timezone_id = contact.contact_location
            LEFT JOIN `:db`.topology t
                ON t.topology_page = contact.default_page
            WHERE contact.contact_id = :contact_id
            ORDER BY cp.creation_date DESC LIMIT 1'
        );

        $statement = $this->db->prepare($request);
        $statement->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);

        $contact = null;
        $statement->execute();

        if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $contact = $this->createContact($result);
            $this->addActionRules($contact);
            $this->addTopologyRules($contact);
        }
        return $contact;
    }

    /**
     * @inheritDoc
     */
    public function findByName(string $name): ?Contact
    {
        $request = $this->translateDbName(
            'SELECT contact.*, cp.password AS contact_passwd, t.topology_url,
            t.topology_url_opt, t.is_react, t.topology_id, tz.timezone_name
            FROM `:db`.contact
            LEFT JOIN `:db`.contact_password cp
                ON cp.contact_id = contact.contact_id
            LEFT JOIN `:db`.timezone tz
                ON tz.timezone_id = contact.contact_location
            LEFT JOIN `:db`.topology t
                ON t.topology_page = contact.default_page
            WHERE contact_alias = :username
            ORDER BY cp.creation_date DESC LIMIT 1'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':username', $name, \PDO::PARAM_STR);

        $statement->execute();

        $contact = null;
        if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $contact = $this->createContact($result);
            $this->addActionRules($contact);
            $this->addTopologyRules($contact);
        }

        return $contact;
    }

    /**
     * @inheritDoc
     */
    public function findBySession(string $sessionId): ?Contact
    {
        $request = $this->translateDbName(
            'SELECT contact.*, cp.password AS contact_passwd, t.topology_url,
            t.topology_url_opt, t.is_react, t.topology_id, tz.timezone_name
            FROM `:db`.contact
            LEFT JOIN `:db`.contact_password cp
                ON cp.contact_id = contact.contact_id
            LEFT JOIN `:db`.timezone tz
                ON tz.timezone_id = contact.contact_location
            LEFT JOIN `:db`.topology t
                ON t.topology_page = contact.default_page
            INNER JOIN `:db`.session
              on session.user_id = contact.contact_id
            WHERE session.session_id = :session_id
            ORDER BY cp.creation_date DESC LIMIT 1'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        $statement->execute();

        $contact = null;
        if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $contact = $this->createContact($result);
            $this->addActionRules($contact);
            $this->addTopologyRules($contact);
        }

        return $contact;
    }


    /**
     * @inheritDoc
     */
    public function findByAuthenticationToken(string $token): ?Contact
    {
        $statement = $this->db->prepare(
            $this->translateDbName(
                "SELECT contact.*, cp.password AS contact_passwd, t.topology_url,
                t.topology_url_opt, t.is_react, t.topology_id, tz.timezone_name
                FROM `:db`.contact
                LEFT JOIN `:db`.contact_password cp
                    ON cp.contact_id = contact.contact_id
                LEFT JOIN `:db`.timezone tz
                    ON tz.timezone_id = contact.contact_location
                LEFT JOIN `:db`.topology t
                    ON t.topology_page = contact.default_page
                INNER JOIN `:db`.security_authentication_tokens sat
                    ON sat.user_id = contact.contact_id
                WHERE sat.token = :token
                ORDER BY cp.creation_date DESC LIMIT 1'"
            )
        );
        $statement->bindValue(':token', $token, \PDO::PARAM_STR);
        $statement->execute();

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return $this->createContact($result);
        }

        return null;
    }

    /**
     * Find and add all topology rules defined by all menus access defined for this contact.
     * The purpose is to limit access to the API based on menus access.
     *
     * @param Contact $contact Contact for which we want to add the topology rules
     */
    private function addTopologyRules(Contact $contact): void
    {
        $toplogySubquery =
            $contact->isAdmin()
            ? 'SELECT topology.topology_id, 1 AS access_right
                FROM topology
                WHERE topology.is_react = \'0\''
            : 'SELECT topology.topology_id, acltr.access_right
                FROM `:db`.contact contact
                LEFT JOIN `:db`.contactgroup_contact_relation cgcr
                    ON cgcr.contact_contact_id = contact.contact_id
                LEFT JOIN `:db`.acl_group_contactgroups_relations gcgr
                    ON gcgr.cg_cg_id = cgcr.contactgroup_cg_id
                LEFT JOIN `:db`.acl_group_contacts_relations gcr
                    ON gcr.contact_contact_id = contact.contact_id
                LEFT JOIN `:db`.acl_group_topology_relations agtr
                    ON agtr.acl_group_id  = gcr.acl_group_id
                    OR agtr.acl_group_id  = gcgr.acl_group_id
                LEFT JOIN `:db`.acl_topology_relations acltr
                    ON acltr.acl_topo_id = agtr.acl_topology_id
                INNER JOIN `:db`.topology
                    ON topology.topology_id = acltr.topology_topology_id
                WHERE contact.contact_id = :contact_id
                    AND topology.is_react = \'0\'';

        $request =
            'SELECT topology.topology_name, topology.topology_page,
                    topology.topology_parent, access.access_right
            FROM `:db`.topology
            LEFT JOIN (' . $toplogySubquery . ') AS access
            ON access.topology_id = topology.topology_id
            WHERE topology.topology_page IS NOT NULL
            ORDER BY topology.topology_page';

        $prepare = $this->db->prepare(
            $this->translateDbName($request)
        );
        if ($contact->isAdmin() === false) {
            $prepare->bindValue(':contact_id', $contact->getId(), \PDO::PARAM_INT);
        }
        $prepare->execute();

        $topologies = [];
        $rightsCounter = 0;
        while ($row = $prepare->fetch(\PDO::FETCH_ASSOC)) {
            $topologies[$row['topology_page']] = [
                'name' => $row['topology_name'],
                'right' => (int) $row['access_right']
            ];
            if ($row['access_right'] !== null) {
                $rightsCounter++;
            }
        }

        $nameOfTopologiesRules = [];
        if ($rightsCounter > 0) {
            foreach ($topologies as $topologyPage => $details) {
                $originalTopologyPage = $topologyPage;
                if ($details['right'] === 0 || strlen((string) $topologyPage) < 5) {
                    continue;
                }
                $ruleName = null;
                $lvl2Name = null;
                $lvl3Name = null;
                $lvl4Name = null;
                if (strlen((string) $topologyPage) === 7) {
                    $lvl4Name = $topologies[$topologyPage]['name'];
                    $topologyPage = (int) substr((string) $topologyPage, 0, 5);

                    // To avoid create entry for the parent menu
                    $nameOfTopologiesRules[$topologyPage] = null;
                }
                if (strlen((string) $topologyPage) === 5) {
                    if ($lvl4Name === null && array_key_exists($topologyPage, $nameOfTopologiesRules)) {
                        continue;
                    }
                    $lvl3Name = $topologies[$topologyPage]['name'];
                    $topologyPage = (int) substr((string) $topologyPage, 0, 3);
                }
                if (strlen((string) $topologyPage) === 3) {
                    $lvl2Name = $topologies[$topologyPage]['name'];
                    $topologyPage = (int) substr((string) $topologyPage, 0, 1);
                }
                if (strlen((string) $topologyPage) === 1) {
                    $ruleName = 'ROLE_' . $topologies[$topologyPage]['name'];
                }
                if ($lvl2Name !== null) {
                    $ruleName .= '_' . $lvl2Name;
                }
                if ($lvl3Name !== null) {
                    $ruleName .= '_' . $lvl3Name;
                }
                if ($lvl4Name !== null) {
                    $ruleName .= '_' . $lvl4Name;
                }

                $ruleName .= ($details['right'] === 2) ? '_R' : '_RW';

                $nameOfTopologiesRules[$originalTopologyPage] = $ruleName;
            }
            foreach ($nameOfTopologiesRules as $page => $name) {
                if ($name !== null) {
                    $name = preg_replace(['/\s/', '/\W/'], ['_', ''], $name);
                    $name = strtoupper($name);
                    $contact->addTopologyRule($name);
                }
            }
        }
    }

    /**
     * Find and add all rules for a contact.
     *
     * @param Contact $contact Contact for which we want to find and add all rules
     */
    private function addActionRules(Contact $contact): void
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
            ORDER BY rules.acl_action_name';

        $request = $this->translateDbName($request);
        $statement = $this->db->prepare($request);
        $statement->bindValue(':contact_id', $contact->getId(), \PDO::PARAM_INT);
        $statement->execute();

        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->addActionRule($contact, $result['acl_action_name']);
        }
    }

    /**
     * Remove charset part of contact lang
     *
     * @param string $lang
     * @return string The contact locale
     */
    private function parseLocaleFromContactLang(string $lang): string
    {
        $locale = Contact::DEFAULT_LOCALE;

        if (preg_match('/^(\w{2}_\w{2})/', $lang, $matches)) {
            $locale = $matches[1];
        }

        return $locale;
    }

    /**
     * Create a contact based on the data.
     *
     * @param mixed[] $contact Array of values representing the contact information
     * @return Contact Returns a new instance of contact
     */
    private function createContact(array $contact): Contact
    {
        $contactTimezoneName = !empty($contact['timezone_name'])
            ? $contact['timezone_name']
            : date_default_timezone_get();

        $contactLocale = !empty($contact['contact_lang'])
            ? $this->parseLocaleFromContactLang($contact['contact_lang'])
            : null;

        $page = null;
        if ($contact['default_page'] !== null) {
            $page = new Page(
                (int) $contact['topology_id'],
                $contact['topology_url'],
                (int) $contact['default_page'],
                (bool) $contact['is_react']
            );
            if (!empty($contact['topology_url_opt'])) {
                $page->setUrlOptions($contact['topology_url_opt']);
            }
        }

        return (new Contact())
            ->setId((int) $contact['contact_id'])
            ->setName($contact['contact_name'])
            ->setAlias($contact['contact_alias'])
            ->setEmail($contact['contact_email'])
            ->setTemplateId((int) $contact['contact_template_id'])
            ->setIsActive($contact['contact_activate'] === '1')
            ->setAllowedToReachWeb($contact['contact_oreon'] === '1')
            ->setAdmin($contact['contact_admin'] === '1')
            ->setToken($contact['contact_autologin_key'])
            ->setEncodedPassword($contact['contact_passwd'])
            ->setAccessToApiRealTime($contact['reach_api_rt'] === '1')
            ->setAccessToApiConfiguration($contact['reach_api'] === '1')
            ->setTimezone(new \DateTimeZone($contactTimezoneName))
            ->setLocale($contactLocale)
            ->setDefaultPage($page)
            ->setUseDeprecatedPages($contact['show_deprecated_pages'] === '1')
            ->setOneClickExportEnabled($contact['enable_one_click_export'] === '1');
    }

    /**
     * Add an action rule to contact.
     *
     * @param Contact $contact Contact for which we want to add rule
     * @param string $ruleName Rule to add
     */
    private function addActionRule(Contact $contact, string $ruleName): void
    {
        switch ($ruleName) {
            case 'host_schedule_check':
            case 'host_schedule_forced_check':
                $contact->addRole(Contact::ROLE_HOST_CHECK);
                break;
            case 'service_schedule_check':
            case 'service_schedule_forced_check':
                $contact->addRole(Contact::ROLE_SERVICE_CHECK);
                break;
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
            case 'service_submit_result':
                $contact->addRole(Contact::ROLE_SERVICE_SUBMIT_RESULT);
                break;
            case 'host_submit_result':
                $contact->addRole(Contact::ROLE_HOST_SUBMIT_RESULT);
                break;
            case 'host_comment':
                $contact->addRole(Contact::ROLE_HOST_ADD_COMMENT);
                break;
            case 'service_comment':
                $contact->addRole(Contact::ROLE_SERVICE_ADD_COMMENT);
                break;
            case 'service_display_command':
                $contact->addRole(Contact::ROLE_DISPLAY_COMMAND);
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
