<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Entity\Task;
use PDO;

class TaskRepository extends ServiceEntityRepository
{
    /**
     * Find one by id
     * @param integer $id
     * @return Task|null
     */
    public function findOneById($id)
    {
        $sql = 'SELECT * FROM task WHERE `id` = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, Task::class);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Find one by parent id
     * @param integer $id
     * @return Task|null
     */
    public function findOneByParentId($id)
    {
        $sql = 'SELECT * FROM task WHERE `parent_id` = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, Task::class);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * find all pending export tasks
     */
    public function findExportTasks()
    {
        $sql = 'SELECT * FROM task WHERE `type` = "export" AND `status` = "pending"';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, Task::class);
        $result = $stmt->fetchAll();

        return $result ?: null;
    }

    /**
     * find all pending import tasks
     */
    public function findImportTasks()
    {
        $sql = 'SELECT * FROM task WHERE `type` = "import" AND `status` = "pending"';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, Task::class);
        $result = $stmt->fetchAll();

        return $result ?: null;
    }

    /**
     * update task status
     */
    public function updateStatus($status, $taskId)
    {
        $sql = "UPDATE task SET status = '$status' WHERE id = $taskId";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
}
