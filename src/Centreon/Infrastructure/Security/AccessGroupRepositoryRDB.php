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

namespace Centreon\Infrastructure\Security;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;

/**
 * Database repository for the access groups.
 *
 * @package Centreon\Infrastructure\Security
 */
final class AccessGroupRepositoryRDB implements AccessGroupRepositoryInterface
{

    /**
     * @var DatabaseConnection
     */
    private $pdo;

    /**
     * AccessGroupRepositoryRDB constructor.
     *
     * @param DatabaseConnection $pdo
     */
    public function __construct(DatabaseConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @inheritDoc
     */
    public function findByContact(ContactInterface $contact): array
    {
        $contactGroups = [];
        if (! is_null($contactId = $contact->getId())) {
            /**
             * Retrieve all access group from contact
             * and contact groups linked to contact
             */
            $prepare = $this->pdo->prepare(
                "SELECT * FROM acl_groups
                WHERE acl_group_activate = '1'
                AND (
                  acl_group_id IN (
                    SELECT acl_group_id FROM acl_group_contacts_relations
                    WHERE contact_contact_id = :contact_id
                  )
                  OR acl_group_id IN (
                    SELECT acl_group_id FROM acl_group_contactgroups_relations agcr
                    INNER JOIN contactgroup_contact_relation cgcr
                      ON cgcr.contactgroup_cg_id = agcr.cg_cg_id
                    WHERE cgcr.contact_contact_id = :contact_id
                  )
                )"
            );
            $prepare->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);
            if ($prepare->execute()) {
                while ($result = $prepare->fetch(\PDO::FETCH_ASSOC)) {
                    $contactGroups[] = (new AccessGroup())
                        ->setId((int) $result['acl_group_id'])
                        ->setName($result['acl_group_name'])
                        ->setAlias($result['acl_group_alias'])
                        ->setActivate($result['acl_group_activate'] === '1');
                }
                return $contactGroups;
            }
        }
        return $contactGroups;
    }
}
