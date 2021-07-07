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

namespace Centreon\Infrastructure\HostConfiguration\Repository;

use Assert\AssertionFailedException;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroup\HostGroupReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroup\HostGroupWriteRepositoryInterface;
use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\HostConfiguration\Repository\Model\HostGroupFactoryRdb;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\Interfaces\NormalizerInterface;
use Centreon\Infrastructure\RequestParameters\RequestParametersTranslatorException;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * This class is designed to represent the MariaDb repository to manage host groups
 *
 * @package Centreon\Infrastructure\HostConfiguration\Repository
 */
class HostGroupRepositoryRDB extends AbstractRepositoryDRB implements
    HostGroupReadRepositoryInterface,
    HostGroupWriteRepositoryInterface
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
    public function addGroup(HostGroup $group): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                INSERT INTO `:db`.hostgroup
                (hg_name, hg_alias, hg_notes, hg_notes_url, hg_action_url, hg_icon_image, hg_map_icon_image,
                hg_rrd_retention, geo_coords, hg_comment, hg_activate)
                VALUES (:group_name, :group_alias, :group_notes, :group_notes_url, :group_action_url, :group_icon_id,
                :group_map_icon_id, :group_rrd, :group_geo, :group_comment, :is_activate)
            ')
        );
        $statement->bindValue(':group_name', $group->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':group_alias', $group->getAlias(), \PDO::PARAM_STR);
        $statement->bindValue(':group_notes', $group->getNotes(), \PDO::PARAM_STR);
        $statement->bindValue(':group_notes_url', $group->getNotesUrl(), \PDO::PARAM_STR);
        $statement->bindValue(':group_action_url', $group->getActionUrl(), \PDO::PARAM_STR);
        $statement->bindValue(
            ':group_icon_id',
            ($group->getIcon() !== null) ? $group->getIcon()->getId() : null,
            \PDO::PARAM_INT
        );
        $statement->bindValue(
            ':group_map_icon_id',
            ($group->getIconMap() !== null) ? $group->getIconMap()->getId() : null,
            \PDO::PARAM_INT
        );
        $statement->bindValue(':group_rrd', $group->getRrd(), \PDO::PARAM_STR);
        $statement->bindValue(':group_geo', $group->getGeoCoords(), \PDO::PARAM_STR);
        $statement->bindValue(':group_comment', $group->getComment(), \PDO::PARAM_STR);
        $statement->bindValue(':is_activate', $group->isActivated() ? '1' : '0', \PDO::PARAM_STR);
        $statement->execute();
        $group->setId((int)$this->db->lastInsertId());
    }

    /**
     * @inheritDoc
     */
    public function findAll(): array
    {
        try {
            return $this->findAllRequest(null);
        } catch (
            RequestParametersTranslatorException
            | \InvalidArgumentException
            | AssertionFailedException $ex
        ) {
            throw new RepositoryException($ex->getMessage(), 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * @inheritDoc
     */
    public function findAllByContact(ContactInterface $contact): array
    {
        try {
            return $this->findAllRequest($contact->getId());
        } catch (
            RequestParametersTranslatorException
            | \InvalidArgumentException
            | AssertionFailedException $ex
        ) {
            throw new RepositoryException($ex->getMessage(), 0, $ex);
        }
    }

    /**
     * Find all groups filtered by contact id.
     *
     * @param int|null $contactId Contact id related to host categories
     * @return HostGroup[]
     * @throws AssertionFailedException
     * @throws \InvalidArgumentException
     * @throws RequestParametersTranslatorException
     */
    private function findAllRequest(?int $contactId): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'hg.hg_id',
            'name' => 'hg.hg_name',
            'alias' => 'hg.hg_alias',
            'is_activated' => 'hg.hg_activate',
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
                'SELECT SQL_CALC_FOUND_ROWS hg.*, icon.img_id AS icon_id, icon.img_name AS icon_name,
                    CONCAT(iconD.dir_name,\'/\',icon.img_path) AS icon_path,
                    icon.img_comment AS icon_comment, imap.img_id AS imap_id, imap.img_name AS imap_name,
                    CONCAT(imapD.dir_name,\'/\',imap.img_path) AS imap_path, imap.img_comment AS imap_comment
                FROM `:db`.hostgroup hg
                LEFT JOIN `:db`.view_img icon
                    ON icon.img_id = hg.hg_icon_image
                LEFT JOIN `:db`.view_img_dir_relation iconR
                    ON iconR.img_img_id = icon.img_id
                LEFT JOIN `:db`.view_img_dir iconD
                    ON iconD.dir_id = iconR.dir_dir_parent_id
                LEFT JOIN `:db`.view_img imap
                    ON imap.img_id = hg.hg_map_icon_image
                LEFT JOIN `:db`.view_img_dir_relation imapR
                    ON imapR.img_img_id = imap.img_id
                LEFT JOIN `:db`.view_img_dir imapD
                    ON imapD.dir_id = imapR.dir_dir_parent_id'
            );
        } else {
            $request = $this->translateDbName(
                'SELECT SQL_CALC_FOUND_ROWS hg.*, icon.img_id AS icon_id, icon.img_name AS icon_name,
                    CONCAT(iconD.dir_name,\'/\',icon.img_path) AS icon_path,
                    icon.img_comment AS icon_comment, imap.img_id AS imap_id, imap.img_name AS imap_name,
                    CONCAT(imapD.dir_name,\'/\',imap.img_path) AS imap_path, imap.img_comment AS imap_comment
                FROM `:db`.hostgroup hg
                LEFT JOIN `:db`.view_img icon
                    ON icon.img_id = hg.hg_icon_image
                LEFT JOIN `:db`.view_img_dir_relation iconR
                    ON iconR.img_img_id = icon.img_id
                LEFT JOIN `:db`.view_img_dir iconD
                    ON iconD.dir_id = iconR.dir_dir_parent_id
                LEFT JOIN `:db`.view_img imap
                    ON imap.img_id = hg.hg_map_icon_image
                LEFT JOIN `:db`.view_img_dir_relation imapR
                    ON imapR.img_img_id = imap.img_id
                LEFT JOIN `:db`.view_img_dir imapD
                    ON imapD.dir_id = imapR.dir_dir_parent_id
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
                LEFT JOIn `:db`.contactgroup_contact_relation cgcr
                    ON cgcr.contactgroup_cg_id = agcgr.cg_cg_id'
            );
        }

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= $searchRequest;
        if ($contactId !== null) {
            $request .= ($searchRequest !== null) ? ' AND' : ' WHERE';
            $request .= ' (agcr.contact_contact_id = :contact_id OR cgcr.contact_contact_id = :contact_id)';
        }

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY hg.hg_name ASC';

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

        $hostGroups = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $hostGroups[] = HostGroupFactoryRdb::create($record);
        }
        return $hostGroups;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $hostGroupId): ?HostGroup
    {
        try {
            return $this->findByIdRequest($hostGroupId, null);
        } catch (AssertionFailedException $ex) {
            throw new RepositoryException($ex->getMessage(), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findByIdAndContact(int $hostGroupId, ContactInterface $contact): ?HostGroup
    {
        try {
            return $this->findByIdRequest($hostGroupId, $contact->getId());
        } catch (AssertionFailedException $ex) {
            throw new RepositoryException($ex->getMessage(), 0, $ex);
        }
    }

    /**
     * Find a group by id and contact id.
     *
     * @param int $hostGroupId Id of the host group to be found
     * @param int|null $contactId Contact id related to host groups
     * @return HostGroup|null
     * @throws AssertionFailedException
     */
    private function findByIdRequest(int $hostGroupId, ?int $contactId): ?HostGroup
    {
        if ($contactId === null) {
            $statement = $this->db->prepare(
                $this->translateDbName(
                    'SELECT hg.*, icon.img_id AS icon_id, icon.img_name AS icon_name,
                        CONCAT(iconD.dir_name,\'/\',icon.img_path) AS icon_path,
                        icon.img_comment AS icon_comment, imap.img_id AS imap_id, imap.img_name AS imap_name,
                        CONCAT(imapD.dir_name,\'/\',imap.img_path) AS imap_path, imap.img_comment AS imap_comment
                    FROM `:db`.hostgroup hg
                    LEFT JOIN `:db`.view_img icon
                        ON icon.img_id = hg.hg_icon_image
                    LEFT JOIN `:db`.view_img_dir_relation iconR
                        ON iconR.img_img_id = icon.img_id
                    LEFT JOIN `:db`.view_img_dir iconD
                        ON iconD.dir_id = iconR.dir_dir_parent_id
                    LEFT JOIN `:db`.view_img imap
                        ON imap.img_id = hg.hg_map_icon_image
                    LEFT JOIN `:db`.view_img_dir_relation imapR
                        ON imapR.img_img_id = imap.img_id
                    LEFT JOIN `:db`.view_img_dir imapD
                        ON imapD.dir_id = imapR.dir_dir_parent_id
                    WHERE hg.hg_id = :id'
                )
            );
        } else {
            $statement = $this->db->prepare(
                $this->translateDbName(
                    'SELECT hg.*, icon.img_id AS icon_id, icon.img_name AS icon_name,
                        CONCAT(iconD.dir_name,\'/\',icon.img_path) AS icon_path,
                        icon.img_comment AS icon_comment, imap.img_id AS imap_id, imap.img_name AS imap_name,
                        CONCAT(imapD.dir_name,\'/\',imap.img_path) AS imap_path, imap.img_comment AS imap_comment
                    FROM `:db`.hostgroup hg
                    LEFT JOIN `:db`.view_img icon
                        ON icon.img_id = hg.hg_icon_image
                    LEFT JOIN `:db`.view_img_dir_relation iconR
                        ON iconR.img_img_id = icon.img_id
                    LEFT JOIN `:db`.view_img_dir iconD
                        ON iconD.dir_id = iconR.dir_dir_parent_id
                    LEFT JOIN `:db`.view_img imap
                        ON imap.img_id = hg.hg_map_icon_image
                    LEFT JOIN `:db`.view_img_dir_relation imapR
                        ON imapR.img_img_id = imap.img_id
                    LEFT JOIN `:db`.view_img_dir imapD
                        ON imapD.dir_id = imapR.dir_dir_parent_id
                    INNER JOIN `:db`.acl_resources_hc_relations arhr
                        ON hg.hg_id = arhr.hc_id
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
                    WHERE hg.hg_id = :id
                        AND (agcr.contact_contact_id = :contact_id OR cgcr.contact_contact_id = :contact_id)'
                )
            );
            $statement->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);
        }

        $statement->bindValue(':id', $hostGroupId, \PDO::PARAM_INT);
        $statement->execute();

        if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            return HostGroupFactoryRdb::create($result);
        }
        return null;
    }

    /**
     * @inheritDoc
     * @throws AssertionFailedException
     */
    public function findByNames(array $groupsName): array
    {
        $hostGroups = [];
        if (empty($groupsName)) {
            return $hostGroups;
        }
        $statement = $this->db->prepare(
            $this->translateDbName(
                'SELECT hg.*, icon.img_id AS icon_id, icon.img_name AS icon_name,
                    CONCAT(iconD.dir_name,\'/\',icon.img_path) AS icon_path,
                    icon.img_comment AS icon_comment, imap.img_id AS imap_id, imap.img_name AS imap_name,
                    CONCAT(imapD.dir_name,\'/\',imap.img_path) AS imap_path, imap.img_comment AS imap_comment
                FROM `:db`.hostgroup hg
                LEFT JOIN `:db`.view_img icon
                    ON icon.img_id = hg.hg_icon_image
                LEFT JOIN `:db`.view_img_dir_relation iconR
                    ON iconR.img_img_id = icon.img_id
                LEFT JOIN `:db`.view_img_dir iconD
                    ON iconD.dir_id = iconR.dir_dir_parent_id
                LEFT JOIN `:db`.view_img imap
                    ON imap.img_id = hg.hg_map_icon_image
                LEFT JOIN `:db`.view_img_dir_relation imapR
                    ON imapR.img_img_id = imap.img_id
                LEFT JOIN `:db`.view_img_dir imapD
                    ON imapD.dir_id = imapR.dir_dir_parent_id
                WHERE hg.hg_name IN (?' . str_repeat(',?', count($groupsName) - 1) . ')'
            )
        );
        $statement->execute($groupsName);

        while (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $hostGroups[] = HostGroupFactoryRdb::create($result);
        }
        return $hostGroups;
    }

    /**
     * @inheritDoc
     * @throws AssertionFailedException
     * @throws \InvalidArgumentException
     */
    public function findHostGroups(): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'hg_id',
            'name' => 'hg_name',
            'alias' => 'hg_alias',
            'is_activated' => 'hg_activate',
        ]);

        $this->sqlRequestTranslator->addNormalizer(
            'is_activated',
            new class () implements NormalizerInterface
            {
                /**
                 * @inheritDoc
                 */
                public function normalize($valueToNormalize)
                {
                    if (is_bool($valueToNormalize)) {
                        return $valueToNormalize === true ? '1' : '0';
                    }
                    return $valueToNormalize;
                }
            }
        );

        $request = $this->translateDbName(
            'SELECT SQL_CALC_FOUND_ROWS hg.*, icon.img_id AS icon_id, icon.img_name AS icon_name,
                CONCAT(iconD.dir_name,\'/\',icon.img_path) AS icon_path,
                icon.img_comment AS icon_comment, imap.img_id AS imap_id, imap.img_name AS imap_name,
                CONCAT(imapD.dir_name,\'/\',imap.img_path) AS imap_path, imap.img_comment AS imap_comment
            FROM `:db`.hostgroup hg
            LEFT JOIN `:db`.view_img icon
                ON icon.img_id = hg.hg_icon_image
            LEFT JOIN `:db`.view_img_dir_relation iconR
                ON iconR.img_img_id = icon.img_id
            LEFT JOIN `:db`.view_img_dir iconD
                ON iconD.dir_id = iconR.dir_dir_parent_id
            LEFT JOIN `:db`.view_img imap
                ON imap.img_id = hg.hg_map_icon_image
            LEFT JOIN `:db`.view_img_dir_relation imapR
                ON imapR.img_img_id = imap.img_id
            LEFT JOIN `:db`.view_img_dir imapD
                ON imapD.dir_id = imapR.dir_dir_parent_id'
        );

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY hg.hg_id ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();
        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }
        $hostGroups = [];
        if ($statement !== false) {
            while (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $hostGroups[] = HostGroupFactoryRdb::create($result);
            }
        }
        return $hostGroups;
    }
}
