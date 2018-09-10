<?php

namespace CentreonRemote\Domain\Service;

use Centreon\Domain\Entity\Command;
use Centreon\Domain\Entity\Task;
use Centreon\Domain\Repository\TaskRepository;
use Centreon\Domain\Service\KeyGeneratorInterface;
use Centreon\Infrastructure\Service\CentcoreCommandService;
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
     * @var CentcoreCommandService
     */
    private $cmdService;

    /**
     * @return CentcoreCommandService
     */
    public function getCmdService(): CentcoreCommandService
    {
        return $this->cmdService;
    }

    /**
     * @param CentcoreCommandService $cmdService
     */
    public function setCmdService(CentcoreCommandService $cmdService): void
    {
        $this->cmdService = $cmdService;
    }

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
    public function __construct(KeyGeneratorInterface $generator, CentreonDBManagerService $dbman, CentcoreCommandService $cmdService)
    {
        $this->gen = $generator;
        $this->dbman = $dbman;
        $this->cmdService = $cmdService;
    }

    /**
     * Adds a new task
     * @param string $type
     * @param array $params
     * @return mixed
     */
    public function addTask(string $type, array $params)
    {
        $newTask = new Task();
        switch ($type) {
            case Task::TYPE_EXPORT:
                $newTask->setType(Task::TYPE_EXPORT);
                $newTask->setCreatedAt(new \DateTime());
                $newTask->setStatus(Task::STATE_PENDING);
                $newTask->setParams(serialize($params));
                $result = $this->getDbman()->getAdapter('configuration_db')->insert('task',$newTask->toArray());
                $cmd = new Command();
                $cmd->setCommandLine(Command::COMMAND_START_IMPEX_WORKER);
                $cmdWritten = $this->getCmdService()->sendCommand($cmd);
                break;
            case Task::TYPE_IMPORT:
                $newTask->setType(Task::TYPE_IMPORT);
                $newTask->setCreatedAt(new \DateTime());
                $newTask->setStatus(Task::STATE_PENDING);
                $newTask->setParams(serialize($params));
                $result = $this->getDbman()->getAdapter('configuration_db')->insert('task',$newTask->toArray());
                $cmd = new Command();
                $cmd->setCommandLine(Command::COMMAND_START_IMPEX_WORKER);
                $cmdWritten = $this->getCmdService()->sendCommand($cmd);
                break;
            default:
                return false;
        }

        return ($result && $cmdWritten) ? true : false;
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

        return $result;
    }
}
