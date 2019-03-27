<?php
namespace Centreon\Infrastructure\CentreonLegacyDB;

use CentreonDB;
use Centreon\Infrastructure\Service\CentreonDBManagerService;

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
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    protected $manager;

    /**
     * Construct
     * 
     * @param \CentreonDB $db
     * @param \Centreon\Infrastructure\Service\CentreonDBManagerService $manager
     */
    public function __construct(CentreonDB $db, CentreonDBManagerService $manager = null)
    {
        $this->db = $db;
        $this->manager = $manager;
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

        $rows = (function () use ($id) {
                $sql = "SELECT `{$columnB}` FROM `{$tableName}` WHERE `{$columnA}` = :{$columnA} LIMIT 0, 2000";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':{$columnA}', $id, \PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll();

                return $rows;
            })();

        // to remove
        foreach ($rows as $row) {
            if (!in_array($row[$columnB], $list)) {
                $listRemove[] = $row[$columnB];
            }

            $listExists[] = $row[$columnB];
            unset($row);
        }

        // to add
        foreach ($list as $pollerId) {
            if (!in_array($pollerId, $listExists)) {
                $listAdd[] = $row[$columnB];
            }
            unset($pollerId);
        }

        // removing
        foreach ($listRemove as $pollerId) {
            (function () use ($id, $pollerId) {
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
            (function () use ($id, $pollerId) {
                $sql = "INSERT INTO `{$tableName}` (`baId`, `$columnB`)  VALUES (:{$columnA}, :$columnB)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(":{$columnA}", $id, \PDO::PARAM_INT);
                $stmt->bindValue(":{$columnB}", $pollerId, \PDO::PARAM_INT);
                $stmt->execute();
            })();
            unset($pollerId);
        }
    }
}
