<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\HostGroup\Infrastructure\Repository;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Core\HostGroup\Application\Repository\ReadHostGroupRepositoryInterface;
use Core\HostGroup\Domain\Model\HostGroup;
use Utility\SqlStringBuilder;

class DbReadHostGroupRepository extends AbstractRepositoryDRB implements ReadHostGroupRepositoryInterface
{
    public function __construct(
        DatabaseConnection $db,
        private SqlRequestParametersTranslator $sqlRequestTranslator
    ) {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function findAllWithoutAcl(): array
    {
        return $this->findAllByContact(null);
    }

    /**
     * @inheritDoc
     */
    public function findAllWithAcl(ContactInterface $contact): array
    {
        return $this->findAllByContact($contact);
    }

    /**
     * @param array $result
     *
     * @return HostGroup
     */
    protected function createHostGroupFromArray(array $result): HostGroup
    {
        return new HostGroup(
            $result['hg_id'],
            $result['hg_name'],
            $result['hg_alias'],
            $result['hg_icon_image'],
            $result['geo_coords'],
        );
    }

    /**
     * @param ContactInterface|null $contact
     *
     * @return array
     */
    protected function findAllByContact(?ContactInterface $contact): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'hg.hg_id',
            'alias' => 'hg.hg_alias',
            'name' => 'hg.hg_name',
        ]);

        // Build query
        $query = (new SqlStringBuilder())
            ->setSelect('hg.hg_id, hg.hg_name, hg.hg_alias, hg.hg_icon_image, hg.geo_coords')
            ->setFrom('`:db`.`hostgroup` hg')
            ->setOrderBy('hg.hg_name ASC');

        if ($contact) {
            $query
                ->addBindValue(':contact_id', $contact->getId(), \PDO::PARAM_INT)
                ->addWhere('(agcr.contact_contact_id = :contact_id OR cgcr.contact_contact_id = :contact_id)')
                ->addJoins(
                    <<<'JOINS'
                        INNER JOIN `:db`.acl_resources_hg_relations arhr
                            ON hg.hg_id = arhr.hg_hg_id
                        INNER JOIN `:db`.acl_resources res
                            ON arhr.acl_res_id = res.acl_res_id
                        INNER JOIN `:db`.acl_res_group_relations argr
                            ON res.acl_res_id = argr.acl_res_id
                        INNER JOIN `:db`.acl_groups ag
                            ON argr.acl_group_id = ag.acl_group_id
                        LEFT JOIN `:db`.acl_group_contacts_relations agcr
                            ON ag.acl_group_id = agcr.acl_group_id
                        LEFT JOIN `:db`.acl_group_contactgroups_relations agcgr
                            ON ag.acl_group_id = agcgr.acl_group_id
                        LEFT JOIN `:db`.contactgroup_contact_relation cgcr
                            ON cgcr.contactgroup_cg_id = agcgr.cg_cg_id
                        JOINS
                );
        }

        // Update the SQL string builder with the RequestParameters through SqlRequestParametersTranslator
        $this->sqlRequestTranslator->translateForBuilder($query);

        // Prepare SQL + bind values
        $statement = $this->db->prepare($this->translateDbName($query->getSql()));
        $this->sqlRequestTranslator->bindSearchValues($statement);
        foreach ($query->getBindValues() as $param => [$value, $type]) {
            $statement->bindValue($param, $value, $type);
        }
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->execute();

        // Calculate the number of rows for the pagination.
        $this->sqlRequestTranslator->calculateNumberOfRows($this->db);

        // Retrieve data
        $hostGroups = [];
        foreach ($statement as $result) {
            $hostGroups[] = $this->createHostGroupFromArray($result);
        }

        return $hostGroups;
    }
}
