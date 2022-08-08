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

namespace Centreon\Tests\Resources\Mock;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Infrastructure\CentreonLegacyDB\Interfaces\PaginationRepositoryInterface;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Centreon\Tests\Resources\Mock\EntityMock;
use PDO;

/**
 * Mock of repository class
 */
class RepositoryPaginationMock extends ServiceEntityRepository implements PaginationRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public static function entityClass(): string
    {
        return EntityMock::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationList($filters = null, int $limit = null, int $offset = null, $ordering = []): array
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS t.* '
            . 'FROM `' . $this->getClassMetadata()->getTableName() . '` AS `t`';

        $collector = new StatementCollector;

        $isWhere = false;

        if ($filters !== null) {
            if (array_key_exists('search', $filters) && $filters['search']) {
                $sql .= ' WHERE `name_column` LIKE :search';
                $collector->addValue(':search', "%{$filters['search']}%");
                $isWhere = true;
            }
        }

        if (!empty($ordering['field'])) {
            $sql .= ' ORDER BY `' . $ordering['field'] . '` ' . $ordering['order'];
        }

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
