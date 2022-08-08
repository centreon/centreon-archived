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

namespace CentreonNotification\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Infrastructure\CentreonLegacyDB\Interfaces\PaginationRepositoryInterface;
use PDO;
use CentreonNotification\Domain\Entity\Escalation;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Centreon\Domain\Repository\Traits\CheckListOfIdsTrait;

class EscalationRepository extends ServiceEntityRepository implements PaginationRepositoryInterface
{
    use CheckListOfIdsTrait;

    /**
     * {@inheritdoc}
     */
    public static function entityClass(): string
    {
        return Escalation::class;
    }

    /**
     * Check list of IDs
     *
     * @param int[] $ids
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
        $sql = 'SELECT SQL_CALC_FOUND_ROWS `esc_id`, `esc_name` '
            . 'FROM `' . $this->getClassMetadata()->getTableName() . '`';

        $collector = new StatementCollector();

        $isWhere = false;
        if ($filters !== null) {
            if (array_key_exists('search', $filters) && $filters['search']) {
                $sql .= ' WHERE `esc_name` LIKE :search';
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
                $sql .= ' `esc_id` IN (' . implode(',', $idsListKey) . ')';
            }
        }

        $sql .= ' ORDER BY `esc_name` ASC';

        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            $collector->addValue(':limit', $limit, PDO::PARAM_INT);

            if ($offset !== null) {
                $sql .= ' OFFSET :offset';
                $collector->addValue(':offset', $offset, PDO::PARAM_INT);
            }
        }

        $stmt = $this->db->prepare($sql);
        $collector->bind($stmt);

        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $this->getEntityPersister()->load($row);
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
}
