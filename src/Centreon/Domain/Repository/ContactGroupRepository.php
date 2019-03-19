<?php

namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\Interfaces\PaginationRepositoryInterface;
use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Centreon\Domain\Entity\ContactGroup;
use PDO;

class ContactGroupRepository extends ServiceEntityRepository implements PaginationRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPaginationList($filters = null, int $limit = null, int $offset = null): array
    {
        $collector = new StatementCollector;

        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM `' .ContactGroup::TABLE . '`';

        $isWhere = false;
        if ($filters !== null) {
            if (array_key_exists('search', $filters) && $filters['search']) {
                $sql .= ' WHERE `cg_name` LIKE :search';
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
                $sql .= ' `cg_id` IN (' . implode(',', $idsListKey) . ')';
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

        $sql .= ' ORDER BY `cg_name` ASC';
        $stmt = $this->db->prepare($sql);
        $collector->bind($stmt);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, ContactGroup::class);
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
