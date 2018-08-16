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
}
