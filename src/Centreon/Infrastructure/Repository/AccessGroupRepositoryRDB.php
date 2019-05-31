<?php

namespace Centreon\Infrastructure\Repository;

use Centreon\Domain\Entity\AccessGroup;
use Centreon\Domain\Entity\Contact;
use Centreon\Domain\Repository\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;

final class AccessGroupRepositoryRDB implements AccessGroupRepositoryInterface
{

    /**
     * @var DatabaseConnection
     */
    private $pdo;

    public function __construct(DatabaseConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Find all access groups from a contact
     *
     * @param Contact $contact
     * @return AccessGroup[]
     */
    public function findByContact(Contact $contact): array
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
