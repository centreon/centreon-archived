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

namespace Centreon\Domain\Repository\Traits;

use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;

trait CheckListOfIdsTrait
{
    /**
     * Check a list of IDs
     *
     * @param array $ids
     * @param string $tableName not needed if entity had metadata
     * @param string $columnNameOfIdentificator not needed if entity had metadata
     * @return bool
     */
    protected function checkListOfIdsTrait(
        array $ids,
        string $tableName = null,
        string $columnNameOfIdentificator = null
    ): bool {
        if ($tableName === null) {
            $tableName = $this->getClassMetadata()->getTableName();
        }

        if ($columnNameOfIdentificator === null) {
            $columnNameOfIdentificator = $this->getClassMetadata()->getPrimaryKeyColumn();
        }

        $count = count($ids);

        $collector = new StatementCollector;
        $sql = "SELECT COUNT(*) AS `total` FROM `{$tableName}` ";

        $isWhere = false;
        foreach ($ids as $x => $value) {
            $key = ":id{$x}";

            $sql .= (!$isWhere ? 'WHERE ' : 'OR ') . "`{$columnNameOfIdentificator}` = {$key} ";
            $collector->addValue($key, $value);

            $isWhere = true;
            unset($x, $value);
        }

        $sql .= 'LIMIT 0, 1';

        $stmt = $this->db->prepare($sql);
        $collector->bind($stmt);
        $stmt->execute();

        $result = $stmt->fetch();

        return (int) $result['total'] === $count;
    }
}
