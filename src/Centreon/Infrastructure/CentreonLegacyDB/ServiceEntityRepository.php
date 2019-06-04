<?php
namespace Centreon\Infrastructure\CentreonLegacyDB;

use CentreonDB;

/**
 * Compatibility with Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
 */
abstract class ServiceEntityRepository
{

    /**
     * @var \CentreonDB
     */
    protected $db;

    /**
     * Construct
     *
     * @param \CentreonDB $db
     */
    public function __construct(CentreonDB $db)
    {
        $this->db = $db;
    }

    /**
     * This method will update the relation table to clean up old data and add the missing
     *
     * @param array $list
     * @param int $id
     * @param string $tableName
     * @param string $columnA
     * @param string $columnB
     */
    protected function updateRelationData(array $list, int $id, string $tableName, string $columnA, string $columnB)
    {
        $listExists = [];
        $listAdd = [];
        $listRemove = [];

        $rows = (function () use ($id, $tableName, $columnA, $columnB) {
                $sql = "SELECT `{$columnB}` FROM `{$tableName}` WHERE `{$columnA}` = :{$columnA} LIMIT 0, 5000";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(":{$columnA}", $id, \PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll();

                return $rows;
            })();

        // to remove
        foreach ($rows as $row) {
            $pollerId = $row[$columnB];
            if (!in_array($pollerId, $list)) {
                $listRemove[] = $pollerId;
            }

            $listExists[] = $pollerId;
            unset($row, $pollerId);
        }

        // to add
        foreach ($list as $pollerId) {
            if (!in_array($pollerId, $listExists)) {
                $listAdd[] = $pollerId;
            }
            unset($pollerId);
        }

        // removing
        foreach ($listRemove as $pollerId) {
            (function () use ($id, $pollerId, $tableName, $columnA, $columnB) {
                $sql = "DELETE FROM `{$tableName}` WHERE `{$columnA}` = :{$columnA} AND `{$columnB}` = :{$columnB}";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(":{$columnA}", $id, \PDO::PARAM_INT);
                $stmt->bindValue(":{$columnB}", $pollerId, \PDO::PARAM_INT);
                $stmt->execute();
            })();
            unset($pollerId);
        }

        // adding
        foreach ($listAdd as $pollerId) {
            (function () use ($id, $pollerId, $tableName, $columnA, $columnB) {
                $sql = "INSERT INTO `{$tableName}` (`{$columnA}`, `{$columnB}`)  VALUES (:{$columnA}, :$columnB)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(":{$columnA}", $id, \PDO::PARAM_INT);
                $stmt->bindValue(":{$columnB}", $pollerId, \PDO::PARAM_INT);
                $stmt->execute();
            })();
            unset($pollerId);
        }
    }
}
