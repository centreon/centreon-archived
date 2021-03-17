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

namespace Centreon\Infrastructure\MetaServiceConfiguration\Repository;

use Assert\AssertionFailedException;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfiguration;
use Centreon\Infrastructure\RequestParameters\Interfaces\NormalizerInterface;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Domain\MetaServiceConfiguration\Interfaces\MetaServiceConfigurationReadRepositoryInterface;
use Centreon\Infrastructure\MetaServiceConfiguration\Repository\Model\MetaServiceConfigurationFactoryRdb;

/**
 * This class is designed to represent the MariaDb repository to manage meta service configuration.
 *
 * @package Centreon\Infrastructure\MetaServiceConfiguration\Repository
 */
class MetaServiceConfigurationRepositoryRDB extends AbstractRepositoryDRB implements
    MetaServiceConfigurationReadRepositoryInterface
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
     * Find all meta services configurations filtered by contact id.
     *
     * @param int|null $contactId Contact id related to host categories
     * @return MetaServiceConfiguration[]
     * @throws AssertionFailedException
     * @throws \InvalidArgumentException
     */
    private function findAllRequest(?int $contactId): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'ms.meta_id',
            'name' => 'ms.meta_name',
            'is_activated' => 'ms.meta_activate',
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
                'SELECT SQL_CALC_FOUND_ROWS * FROM `:db`.meta_service ms'
            );
        } else {
            $request = $this->translateDbName(
                'SELECT SQL_CALC_FOUND_ROWS ms.*
                FROM `:db`.meta_service ms
                INNER JOIN `:db`.acl_resources_meta_relations armr
                    ON ms.meta_id = armr.meta_id
                INNER JOIN `:db`.acl_resources res
                    ON armr.acl_res_id = res.acl_res_id
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
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        if ($contactId !== null) {
            $request .= ' AND (agcr.contact_contact_id = :contact_id OR cgcr.contact_contact_id = :contact_id)';
        }

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY ms.meta_name ASC';

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

        $metaServicesConfigurations = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $metaServicesConfigurations[] = MetaServiceConfigurationFactoryRdb::create($record);
        }
        return $metaServicesConfigurations;
    }

    /**
     * @inheritDoc
     * @throws AssertionFailedException
     */
    public function findById(int $metaId): ?MetaServiceConfiguration
    {
        return $this->findByIdRequest($metaId, null);
    }

    /**
     * @inheritDoc
     * @throws AssertionFailedException
     */
    public function findByIdAndContact(int $metaId, ContactInterface $contact): ?MetaServiceConfiguration
    {
        return $this->findByIdRequest($metaId, $contact->getId());
    }

    /**
     * Find a category by id and contact id.
     *
     * @param int $metaId Id of the meta service configuration to be found
     * @param int|null $contactId Contact id related to host categories
     * @return MetaServiceConfiguration|null
     * @throws AssertionFailedException
     */
    private function findByIdRequest(int $metaId, ?int $contactId): ?MetaServiceConfiguration
    {
        if ($contactId === null) {
            $statement = $this->db->prepare(
                $this->translateDbName('SELECT * FROM `:db`.meta_service WHERE meta_id = :id')
            );
        } else {
            $statement = $this->db->prepare(
                $this->translateDbName(
                    'SELECT ms.*
                    FROM `:db`.meta_service ms
                    INNER JOIN `:db`.acl_resources_meta_relations armr
                        ON ms.meta_id = armr.meta_id
                    INNER JOIN `:db`.acl_resources res
                        ON armr.acl_res_id = res.acl_res_id
                    INNER JOIN `:db`.acl_res_group_relations argr
                        ON res.acl_res_id = argr.acl_res_id
                    INNER JOIN `:db`.acl_groups ag
                        ON argr.acl_group_id = ag.acl_group_id
                    LEFT JOIN `:db`.acl_group_contacts_relations agcr
                        ON ag.acl_group_id = agcr.acl_group_id
                    LEFT JOIN `:db`.acl_group_contactgroups_relations agcgr
                        ON ag.acl_group_id = agcgr.acl_group_id
                    LEFT JOIN `:db`.contactgroup_contact_relation cgcr
                        ON  cgcr.contactgroup_cg_id = agcgr.cg_cg_id
                    WHERE ms.meta_id = :id
                        AND (agcr.contact_contact_id = :contact_id OR cgcr.contact_contact_id = :contact_id)'
                )
            );
            $statement->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);
        }

        $statement->bindValue(':id', $metaId, \PDO::PARAM_INT);
        $statement->execute();

        if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            return MetaServiceConfigurationFactoryRdb::create($result);
        }
        return null;
    }
}
