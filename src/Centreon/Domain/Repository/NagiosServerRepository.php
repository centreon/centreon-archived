<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

            if (array_key_exists('search', $filters) && $filters['search']) {
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
