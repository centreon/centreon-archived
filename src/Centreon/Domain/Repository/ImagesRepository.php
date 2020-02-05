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
     * {@inheritdoc}
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
        $collector = new StatementCollector();
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
            . '` WHERE `img_id` = `vidr`.`img_img_id` AND `dir_id` = `vidr`.`dir_dir_parent_id` AND `img_id` =' . $id;
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
