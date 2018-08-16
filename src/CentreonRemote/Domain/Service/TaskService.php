<?php

namespace CentreonRemote\Domain\Service;

use Centreon\Domain\Entity\Task;
use Centreon\Domain\Repository\TaskRepository;
use Centreon\Domain\Service\KeyGeneratorInterface;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use Centreon\Infrastructure\Service\Exception\NotFoundException;

class TaskService
{
    /**
     * @var KeyGeneratorInterface
     */
    private $gen;

    /**
     * @var CentreonDBManagerService
     */
    private $dbman;

    /**
     * @return KeyGeneratorInterface
     */
    public function getGen(): KeyGeneratorInterface
    {
        return $this->gen;
    }

    /**
     * @return CentreonDBManagerService
     */
    public function getDbman(): CentreonDBManagerService
    {
        return $this->dbman;
    }

    /**
     * TaskService constructor
     * @param KeyGeneratorInterface $generator
     * @param CentreonDBManagerService $dbman
     */
    public function __construct(KeyGeneratorInterface $generator, CentreonDBManagerService $dbman)
    {
        $this->gen = $generator;
        $this->dbman = $dbman;
    }

    /**
     * Adds a new task
     * @param string $type
     * @return mixed
     */
    public function addTask(string $type)
    {
        $newTask = new Task();
        switch ($type) {
            case Task::TYPE_EXPORT:
                $newTask->setType(Task::TYPE_EXPORT);
                $newTask->setCreatedAt(new \DateTime());
                $newTask->setStatus(Task::STATE_PENDING);
                $result = $this->getDbman()->getAdapter('configuration_db')->insert('task',$newTask->toArray());
                break;
            default:
                return false;
        }

        return $result;
    }

    /**
     * Get Existing Task status
     * @param string $taskId
     * @return null
     */
    public function getStatus(string $taskId)
    {
        $task = $this->getDbman()->getAdapter('configuration_db')->getRepository(TaskRepository::class)->findOneById($taskId);
        return $task ? $task->getStatus() : null;
    }

    /**
     * Update task status
     * @param string $taskId
     * @param string $status
     * @return mixed
     * @throws NotFoundException
     * @throws \Exception
     */
    public function updateStatus(string $taskId, string $status)
    {
        $task = $this->getDbman()->getAdapter('configuration_db')->getRepository(TaskRepository::class)->findOneById($taskId);
        if (!in_array($status,$task->getStatuses()))
        {
            return false;
        }
        $task->setStatus($status);

        $result = $this->getDbman()->getAdapter('configuration_db')->update('task', $task->toArray(), $taskId);

    }
}