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

use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroupRepositoryInterface;
use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\HostConfiguration\Repository\Model\HostGroupFactoryRdb;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * This class is designed to represent the MariaDb repository to manage host groups
 *
 * @package Centreon\Infrastructure\HostConfiguration
 */
class HostGroupRepositoryRDB extends AbstractRepositoryDRB implements HostGroupRepositoryInterface
{

    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

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
    public function addHostGroup(HostGroup $hostGroup): int
    {
        try {
            $this->db->beginTransaction();
            $request = $this->translateDbName(
                'INSERT INTO `:db`.hostgroup
                (hg_name, hg_alias, hg_notes, hg_notes_url, hg_action_url, hg_icon_image, hg_map_icon_image,
                hg_rrd_retention, geo_coords, hg_comment, hg_activate)
                VALUES (:name, :alias, :notes, :notesUrl, :actionUrl, :icon,  :iconMap,
                        :rrd, :geo_coords, :comment, :is_activate)'
            );
            $statement = $this->db->prepare($request);
            $statement->bindValue(':name', $hostGroup->getName(), \PDO::PARAM_STR);
            empty($hostGroup->getAlias())
                ? $statement->bindValue(':alias', null, \PDO::PARAM_NULL)
                : $statement->bindValue(':alias', $hostGroup->getAlias(), \PDO::PARAM_STR);
            $statement->bindValue(':notes', $hostGroup->getNotes(), \PDO::PARAM_STR);
            $statement->bindValue(':notesUrl', $hostGroup->getNotesUrl(), \PDO::PARAM_STR);
            $statement->bindValue(':actionUrl', $hostGroup->getActionUrl(), \PDO::PARAM_STR);
            $statement->bindValue(':icon', $hostGroup->getIcon(), \PDO::PARAM_STR);
            $statement->bindValue(':iconMap', $hostGroup->getIconMap(), \PDO::PARAM_STR);
            $statement->bindValue(':rrd', $hostGroup->getRrd(), \PDO::PARAM_INT);
            $statement->bindValue(':geo_coords', $hostGroup->getGeoCoords(), \PDO::PARAM_STR);
            $statement->bindValue(':comment', $hostGroup->getComment(), \PDO::PARAM_STR);
            $statement->bindValue(':is_activate', $hostGroup->isActivated(), \PDO::PARAM_STR);
            $statement->execute();

            $hostGroupId = (int)$this->db->lastInsertId();
            $this->db->commit();

            return $hostGroupId;
        } catch (\Exception $ex) {
            $this->db->rollBack();
            throw $ex;
        }
    }

    /**
     * @inheritDoc
     */
    public function getNumberOfHostGroups(): int
    {
        $request = $this->translateDbName('SELECT COUNT(*) AS total FROM `:db`.host WHERE host_register = \'1\'');
        $statement = $this->db->query($request);
        if ($statement !== false && ($result = $statement->fetchColumn()) !== false) {
            return (int)$result;
        }
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function hasHostGroupWithSameName(string $hostName): bool
    {
        $request = $this->translateDbName('SELECT COUNT(*) FROM `:db`.host WHERE host_name = :host_name');
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_name', $hostName, \PDO::FETCH_ASSOC);
        $statement->execute();
        if (($result = $statement->fetchColumn()) !== false) {
            return ((int)$result) > 0;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function changeActivationStatus(int $hostId, bool $shouldBeActivated): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName('UPDATE `:db`.host SET host_activate = :activation_status WHERE host_id = :host_id')
        );
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $statement->bindValue(':activation_status', $shouldBeActivated ? '1' : '0', \PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * @inheritDoc
     */
    public function findHostGroups(): array
    {
        $request = $this->translateDbName(
            'SELECT SQL_CALC_FOUND_ROWS hg.*, icon.img_id AS icon_id, icon.img_name AS icon_name,
                CONCAT(iconD.dir_name,\'/\',icon.img_path) AS icon_path,
                icon.img_comment AS icon_comment, imap.img_id AS imap_id, imap.img_name AS imap_name,
                imap.img_path AS imap_path, imap.img_comment AS imap_comment
            FROM `:db`.hostgroup hg
            LEFT JOIN `:db`.view_img icon
                ON icon.img_id = hg.hg_icon_image
            LEFT JOIN `centreon`.view_img_dir_relation iconR
                ON iconR.img_img_id = icon.img_id
            LEFT JOIN `centreon`.view_img_dir iconD
                ON iconD.dir_id = iconR.dir_dir_parent_id
            LEFT JOIN `:db`.view_img imap
                ON imap.img_id = hg.hg_map_icon_image'
        );

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest)
            ? $searchRequest . ' GROUP BY hg.hg_id'
            : ' GROUP BY hg.hg_id';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY hg.hg_id ASC';
        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();
        $statement = $this->db->query($request);
        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int)$total);
        }
        $hostGroups = [];
        if ($statement !== false) {
            while (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $hostGroups[] = HostGroupFactoryRdb::create($result);
            }
        }
        return $hostGroups;
    }

    /**
     * @inheritDoc
     */
    public function addHostGroupRelation(int $hostId, array $hostGroups): void
    {
        if (empty($hostGroups)) {
            return;
        }
        foreach (array_values($hostGroups) as $hostGroup) {
            try {
                $this->db->beginTransaction();
                if ($hostGroup->getId() !== null) {
                    // Associate the host and host group using host group id
                    $request = $this->translateDbName(
                        'INSERT INTO `:db`.hostgroup_relation
                    (`host_host_id`, `hostgroup_hg_id`)
                    VALUES (:host_id, :hg_id)'
                    );
                    $statement = $this->db->prepare($request);
                    $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
                    $statement->bindValue(':hg_id', $hostGroup->getId(), \PDO::PARAM_INT);
                    $statement->execute();
                    if ($statement->rowCount() === 0) {
                        throw new RepositoryException(
                            sprintf(_('Host Group with id %d not found'), $hostGroup->getId())
                        );
                    }
                } elseif (!empty($hostGroup->getName())) {
                    // Associate the host and host group using host group name
                    $request = $this->translateDbName(
                        'INSERT INTO `:db`.hostgroup_relation
                    (`host_host_id`, `hostgroup_hg_id`)
                    SELECT :host_id, hostgroup.hg_id
                    FROM `:db`.hostgroup
                    WHERE hostgroup.hg_name = :hg_name'
                    );
                    $statement = $this->db->prepare($request);
                    $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
                    $statement->bindValue(':hg_name', $hostGroup->getName(), \PDO::PARAM_STR);
                    $statement->execute();
                    if ($statement->rowCount() === 0) {
                        throw new RepositoryException(sprintf(_('Host Group %s not found'), $hostGroup->getName()));
                    }
                }
                $this->db->commit();
            } catch (\Exception $ex) {
                $this->db->rollBack();
                throw $ex;
            }
        }
    }
}
