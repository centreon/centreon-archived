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

use Centreon\Infrastructure\CentreonLegacyDB\Interfaces\PaginationRepositoryInterface;
use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Centreon\Domain\Entity\Image;
use Centreon\Domain\Entity\ImageDir;
use Centreon\Domain\Repository\Traits\CheckListOfIdsTrait;
use PDO;

class ImagesRepository extends ServiceEntityRepository implements PaginationRepositoryInterface
{
    use CheckListOfIdsTrait;

    /**
     * Check list of IDs
     *
     * @return bool
     */
    public function checkListOfIds(array $ids): bool
    {
        return $this->checkListOfIdsTrait($ids, Image::TABLE, 'img_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationList($filters = null, int $limit = null, int $offset = null, $ordering = []): array
    {
        $collector = new StatementCollector;
        $sql = 'SELECT * FROM `' . ImageDir::TABLE . '`,`' . ImageDir::JOIN_TABLE . '` vidr,`' . Image::TABLE . '` '
            . 'WHERE `img_id` = `vidr`.`img_img_id` AND `dir_id` = `vidr`.`dir_dir_parent_id`';

        $isWhere = true;
        if ($filters !== null) {
            if (array_key_exists('search', $filters) && $filters['search']) {
                $sql .= ' AND `img_name` LIKE :search';
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
                $sql .= $isWhere ? ' AND' : ' WHERE';
                $sql .= ' `img_id` IN (' . implode(',', $idsListKey) . ')';
            }
        }

        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            $collector->addValue(':limit', $limit, PDO::PARAM_INT);
        }

        if ($limit !== null) {
            $sql .= ' OFFSET :offset';
            $collector->addValue(':offset', $offset, PDO::PARAM_INT);
        }

        $sql .= ' ORDER BY `dir_name`, `img_name`';
        $stmt = $this->db->prepare($sql);
        $collector->bind($stmt);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Image::class);

        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function getOnebyId(int $id)
    {
        if (empty($id)) {
            throw new \Exception('Id required to get Icon by ID, none provided');
        }
        $sql = 'SELECT * FROM `' . ImageDir::TABLE . '`,`' . ImageDir::JOIN_TABLE . '` vidr,`' . Image::TABLE
            . '` WHERE `img_id` = `vidr`.`img_img_id` AND `dir_id` = `vidr`.`dir_dir_parent_id` AND `img_id` ='.$id;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Image::class);
        $result = $stmt->fetch();

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
