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

namespace CentreonCommand\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Infrastructure\CentreonLegacyDB\Interfaces\PaginationRepositoryInterface;
use PDO;
use CentreonCommand\Domain\Entity\Command;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;

class CommandRepository extends ServiceEntityRepository implements PaginationRepositoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function getPaginationList(
        $filters = null,
        int $limit = null,
        int $offset = null,
        $ordering = []
    ): array {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS `command_id` AS `id`, `command_name` AS `name` '
            . 'FROM `' . Command::TABLE . '`';

        $collector = new StatementCollector();

        $sql .= ' WHERE `command_activate` = :active';
        $collector->addValue(':active', true, PDO::PARAM_STR);

        if ($filters !== null) {
            if (array_key_exists('search', $filters) && $filters['search']) {
                $sql .= ' AND `command_name` LIKE :search';
                $collector->addValue(':search', "%{$filters['search']}%");
            }

            if (array_key_exists('ids', $filters) && is_array($filters['ids'])) {
                $idsListKey = [];
                foreach ($filters['ids'] as $x => $id) {
                    $key = ":id{$x}";
                    $idsListKey[] = $key;
                    $collector->addValue($key, $id, PDO::PARAM_INT);

                    unset($x, $id);
                }

                $sql .= ' AND `command_id` IN (' . implode(',', $idsListKey) . ')';
            }

            if (array_key_exists('type', $filters) && $filters['type']) {
                $sql .= ' AND `command_type` LIKE :type';
                $collector->addValue(':type', "%{$filters['type']}%");
            }
        }

        $sql .= ' ORDER BY `command_name` ASC';

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
        $stmt->setFetchMode(PDO::FETCH_CLASS, Command::class);
        $result = $stmt->fetchAll();

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
