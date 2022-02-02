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

namespace Centreon\Infrastructure\HostConfiguration\Repository;

use Assert\AssertionFailedException;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\HostConfiguration\Exception\HostCategoryException;
use Centreon\Domain\HostConfiguration\Interfaces\HostCategory\HostCategoryReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostCategory\HostCategoryWriteRepositoryInterface;
use Centreon\Domain\HostConfiguration\Model\HostCategory;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\HostConfiguration\Repository\Model\HostCategoryFactoryRdb;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\Interfaces\NormalizerInterface;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * This class is designed to represent the MariaDb repository to manage host category.
 *
 * @package Centreon\Infrastructure\HostConfiguration\Repository
 */
class HostCategoryRepositoryRDB extends AbstractRepositoryDRB implements
    HostCategoryReadRepositoryInterface,
    HostCategoryWriteRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @param DatabaseConnection $db
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function __construct(DatabaseConnection $db, SqlRequestParametersTranslator $sqlRequestTranslator)
    {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT);
    }

    /**
     * @inheritDoc
     */
    public function addCategory(HostCategory $category): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                INSERT INTO `:db`.hostcategories (hc_name, hc_alias, level, icon_id, hc_comment, hc_activate)
                VALUES (:name, :alias, :level, :icon_id, :comment, :is_activated)
            ')
        );
        $statement->bindValue(':name', $category->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':alias', $category->getAlias(), \PDO::PARAM_STR);
        $statement->bindValue(':comment', $category->getComments(), \PDO::PARAM_STR);
        $statement->bindValue(':level', null, \PDO::PARAM_INT);
        $statement->bindValue(':icon_id', null, \PDO::PARAM_INT);
        $statement->bindValue(':is_activated', $category->isActivated() ? '1' : '0', \PDO::PARAM_STR);
        $statement->execute();
        $category->setId((int)$this->db->lastInsertId());
    }

    /**
     * @inheritDoc
     */
    public function findAll(): array
    {
        return $this->findAllRequest(null);
    }

    /**
     * @inheritDoc
     */
    public function findAllByContact(ContactInterface $contact): array
    {
        return $this->findAllRequest($contact->getId());
    }

    /**
     * Find all categories filtered by contact id.
     *
     * @param int|null $contactId Contact id related to host categories
     * @return HostCategory[]
     * @throws AssertionFailedException
     * @throws \InvalidArgumentException
     */
    private function findAllRequest(?int $contactId): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'hc.hc_id',
            'name' => 'hc.hc_name',
            'alias' => 'hc.hc_alias',
            'is_activated' => 'hc.hc_activate',
        ]);
        $this->sqlRequestTranslator->addNormalizer(
            'is_activated',
            new class implements NormalizerInterface
            {
                /**
                 * @inheritDoc
                 */
                public function normalize($valueToNormalize)
                {
                    if (is_bool($valueToNormalize)) {
                        return ($valueToNormalize === true) ? '1' : '0';
                    }
                    return $valueToNormalize;
                }
            }
        );
        if ($contactId === null) {
            $request = $this->translateDbName(
                'SELECT SQL_CALC_FOUND_ROWS * FROM `:db`.hostcategories hc'
            );
        } else {
            $request = $this->translateDbName(
                'SELECT SQL_CALC_FOUND_ROWS hc.*
                FROM `:db`.hostcategories hc
                INNER JOIN `:db`.acl_resources_hc_relations arhr
                    ON hc.hc_id = arhr.hc_id
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
                LEFT JOIn `:db`.contactgroup_contact_relation cgcr
                    ON cgcr.contactgroup_cg_id = agcgr.cg_cg_id'
            );
        }

            // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest)
            ? $searchRequest . ' AND hc.level IS NULL'
            : '  WHERE hc.level IS NULL';

        if ($contactId !== null) {
            $request .= ' AND (agcr.contact_contact_id = :contact_id OR cgcr.contact_contact_id = :contact_id)';
        }

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY hc.hc_name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();
        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if ($contactId !== null) {
            $statement->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);
        }
        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $hostCategories = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $hostCategories[] = HostCategoryFactoryRdb::create($record);
        }
        return $hostCategories;
    }

    /**
     * @inheritDoc
     * @throws AssertionFailedException
     */
    public function findById(int $categoryId): ?HostCategory
    {
        return $this->findByIdRequest($categoryId, null);
    }

    /**
     * @inheritDoc
     * @throws AssertionFailedException
     */
    public function findByIdAndContact(int $categoryId, ContactInterface $contact): ?HostCategory
    {
        return $this->findByIdRequest($categoryId, $contact->getId());
    }

    /**
     * Find a category by id and contact id.
     *
     * @param int $categoryId Id of the host category to be found
     * @param int|null $contactId Contact id related to host categories
     * @return HostCategory|null
     * @throws AssertionFailedException
     */
    private function findByIdRequest(int $categoryId, ?int $contactId): ?HostCategory
    {
        if ($contactId === null) {
            $statement = $this->db->prepare(
                $this->translateDbName('SELECT * FROM `:db`.hostcategories WHERE level IS NULL AND hc_id = :id')
            );
        } else {
            $statement = $this->db->prepare(
                $this->translateDbName(
                    'SELECT hc.*
                    FROM `:db`.hostcategories hc
                    INNER JOIN `:db`.acl_resources_hc_relations arhr
                        ON hc.hc_id = arhr.hc_id
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
                    LEFT JOIn `:db`.contactgroup_contact_relation cgcr
                        ON  cgcr.contactgroup_cg_id = agcgr.cg_cg_id
                    WHERE hc.level IS NULL
                        AND hc.hc_id = :id
                        AND (agcr.contact_contact_id = :contact_id OR cgcr.contact_contact_id = :contact_id)'
                )
            );
            $statement->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);
        }

        $statement->bindValue(':id', $categoryId, \PDO::PARAM_INT);
        $statement->execute();

        if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            return HostCategoryFactoryRdb::create($result);
        }
        return null;
    }

    /**
     * @inheritDoc
     * @throws AssertionFailedException
     */
    public function findByName(string $name): ?HostCategory
    {
        return $this->findByNameRequest($name, null);
    }

    /**
     * @inheritDoc
     * @throws AssertionFailedException
     */
    public function findByNames(array $categoriesName): array
    {
        $hostCategories = [];
        if ((empty($categoriesName))) {
            return $hostCategories;
        }
        $statement = $this->db->prepare(
            $this->translateDbName('
                SELECT * FROM `:db`.hostcategories
                WHERE `hc_name` IN (?' . str_repeat(',?', count($categoriesName) - 1) . ')
            ')
        );
        $statement->execute($categoriesName);

        while (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $hostCategories[] = HostCategoryFactoryRdb::create($result);
        }
        return $hostCategories;
    }

    /**
     * @inheritDoc
     * @throws AssertionFailedException
     */
    public function findByNameAndContact(string $name, ContactInterface $contact): ?HostCategory
    {
        return $this->findByNameRequest($name, $contact->getId());
    }

    /**
     * Find a category by name and contact id.
     *
     * @param string $hostCategoryName Name of the host category to be found
     * @param int|null $contactId Contact id related to host categories
     * @return HostCategory|null
     * @throws AssertionFailedException
     */
    private function findByNameRequest(string $hostCategoryName, ?int $contactId): ?HostCategory
    {
        if (empty($hostCategoryName)) {
            return null;
        }

        if ($contactId === null) {
            $statement = $this->db->prepare(
                $this->translateDbName(
                    'SELECT * FROM `:db`.hostcategories WHERE level IS NULL AND hc_name = :name'
                )
            );
        } else {
            $statement = $this->db->prepare(
                $this->translateDbName(
                    'SELECT hc.*
                    FROM `:db`.hostcategories hc
                    INNER JOIN `:db`.acl_resources_hc_relations arhr
                        ON hc.hc_id = arhr.hc_id
                    INNER JOIN `:db`.acl_resources res
                        ON arhr.acl_res_id = res.acl_res_id
                    INNER JOIN `:db`.acl_res_group_relations argr
                        ON res.acl_res_id = argr.acl_res_id
                    INNER JOIN `:db`.acl_groups ag
                        ON argr.acl_group_id = ag.acl_group_id
                    LEFT JOIN `:db`.acl_group_contacts_relations agcr
                        ON ag.acl_group_id = agcr.acl_group_id
                        AND agcr.contact_contact_id = :contact_id
                    LEFT JOIN `:db`.acl_group_contactgroups_relations agcgr
                        ON ag.acl_group_id = agcgr.acl_group_id
                    LEFT JOIn `:db`.contactgroup_contact_relation cgcr
                        ON  cgcr.contactgroup_cg_id = agcgr.cg_cg_id
                        AND cgcr.contact_contact_id = :contact_id
                    WHERE hc.level IS NULL
                        AND hc.hc_name = :name
                        AND (agcr.contact_contact_id = :contact_id OR cgcr.contact_contact_id = :contact_id)'
                )
            );
            $statement->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);
        }


        $statement->bindValue(':name', $hostCategoryName, \PDO::PARAM_STR);
        $statement->execute();

        if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            return HostCategoryFactoryRdb::create($result);
        }
        return null;
    }

    /**
     * Find HostCategories by host id
     *
     * @param Host $host
     * @return HostCategory[]
     */
    public function findByHost(Host $host): array
    {
        $request = $this->translateDbName(
            'SELECT SQL_CALC_FOUND_ROWS * FROM `:db`.hostcategories hc
            JOIN `:db`.hostcategories_relation hc_rel ON hc.hc_id = hc_rel.hostcategories_hc_id
            WHERE hc_rel.host_host_id = :host_id'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
        $statement->execute();

        $hostCategories = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $hostCategories[] = HostCategoryFactoryRdb::create($record);
        }
        return $hostCategories;
    }
}
