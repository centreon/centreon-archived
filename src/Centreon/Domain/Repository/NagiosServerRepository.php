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

namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Infrastructure\CentreonLegacyDB\Interfaces\PaginationRepositoryInterface;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Centreon\Domain\Repository\Traits\CheckListOfIdsTrait;
use PDO;

class NagiosServerRepository extends ServiceEntityRepository implements PaginationRepositoryInterface
{
     use CheckListOfIdsTrait;

     /**
     * Check list of IDs
     *
     * @return bool
     */
    public function checkListOfIds(array $ids): bool
    {
        return $this->checkListOfIdsTrait($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationList($filters = null, int $limit = null, int $offset = null, $ordering = []): array
    {
        $collector = new StatementCollector;

        $sql = 'SELECT SQL_CALC_FOUND_ROWS * '
            . 'FROM `' . $this->getClassMetadata()->getTableName() . '`';

        if ($filters !== null) {
            $isWhere = false;

            if (!empty($filters['search'])) {
                $sql .= ' WHERE `' . $this->getClassMetadata()->getColumn('name') . '` LIKE :search';
                $collector->addValue(':search', "%{$filters['search']}%");
                $isWhere = true;
            }

            if (array_key_exists('ids', $filters) && is_array($filters['ids'])) {
                $idsListKey = [];

                foreach ($filters['ids'] as $x => $id) {
                    $key = ":id{$x}";
                    $idsListKey[] = $key;
                    $collector->addValue($key, $id, PDO::PARAM_INT);
                    unset($x, $id);
                }

                $sql .= $isWhere ? ' AND' : ' WHERE';
                $sql .= ' `' . $this->getClassMetadata()->getPrimaryKeyColumn()
                    . '` IN (' . implode(',', $idsListKey) . ')';
            }
        }

        if (!empty($ordering['field'])) {
            $sql .= ' ORDER BY `' . $this->getClassMetadata()->getColumn($ordering['field']) . '` '
                . $ordering['order'];
        } else {
            $sql .= ' ORDER BY `' . $this->getClassMetadata()->getColumn('name') . '` ASC';
        }

        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            $collector->addValue(':limit', $limit, PDO::PARAM_INT);
        }

        if ($offset !== null) {
            $sql .= ' OFFSET :offset';
            $collector->addValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt = $this->db->prepare($sql);
        $collector->bind($stmt);
        $stmt->execute();

        $result = [];

        if ($stmt->rowCount()) {
            foreach ($stmt->fetchAll() as $data) {
                $result[] = $this->getEntityPersister()->load($data);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationListTotal(): int
    {
        return $this->db->numberRows();
    }

    /**
     * Export poller's Nagios data
     *
     * @param int[] $pollerIds
     * @return array
     */
    public function export(array $pollerIds): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);

        $sql = "SELECT * FROM nagios_server WHERE id IN ({$ids})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Truncate the data
     */
    public function truncate()
    {
        $sql = <<<SQL
TRUNCATE TABLE `nagios_server`;
TRUNCATE TABLE `cfg_nagios`;
TRUNCATE TABLE `cfg_nagios_broker_module`
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    /**
     * Sets poller as updated (shows that poller needs restarting)
     *
     * @param int $id id of poller
     */
    public function setUpdated(int $id): void
    {
        $sql = "UPDATE `nagios_server` SET `updated` = '1' WHERE `id` = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Get Central Poller
     *
     * @return int|null
     */
    public function getCentral(): ?int
    {
        $query = "SELECT id FROM nagios_server WHERE localhost = '1' LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        if (!$stmt->rowCount()) {
            return null;
        }

        return (int)$stmt->fetch()['id'];
    }
}
