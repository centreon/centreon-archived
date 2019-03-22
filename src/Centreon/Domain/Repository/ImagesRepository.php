<?php

namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\Interfaces\PaginationRepositoryInterface;
use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Centreon\Domain\Entity\Image;
use Centreon\Domain\Entity\ImageDir;
use PDO;

class ImagesRepository extends ServiceEntityRepository implements PaginationRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPaginationList($filters = null, int $limit = null, int $offset = null): array
    {
        $collector = new StatementCollector;
        $sql = 'SELECT * FROM `' . ImageDir::TABLE . '`,`' . ImageDir::JOIN_TABLE . '` vidr,`' . Image::TABLE . '` WHERE `img_id` = `vidr`.`img_img_id` AND `dir_id` = `vidr`.`dir_dir_parent_id`';

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
        $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, Image::class);
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
