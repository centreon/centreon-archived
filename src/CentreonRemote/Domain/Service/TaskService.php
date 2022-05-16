<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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
    private $dbManager;

    /**
     * @var CentcoreCommandService
     */
    private $cmdService;

    /**
     * @var \CentreonRestHttp
     */
    private $centreonRestHttp;

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
    public function getDbManager(): CentreonDBManagerService
    {
        return $this->dbManager;
    }

    /**
     * @param \CentreonRestHttp $centreonRestHttp
     */
    public function setCentreonRestHttp(\CentreonRestHttp $centreonRestHttp): void
    {
        $this->centreonRestHttp = $centreonRestHttp;
    }

    /**
     * @return \CentreonRestHttp
     */
    public function getCentreonRestHttp(): \CentreonRestHttp
    {
        return $this->centreonRestHttp;
    }

    /**
     * TaskService constructor
     * @param KeyGeneratorInterface $generator
     * @param CentreonDBManagerService $dbManager
     */
    public function __construct(
        KeyGeneratorInterface $generator,
        CentreonDBManagerService $dbManager,
        CentcoreCommandService $cmdService
    ) {
        $this->gen = $generator;
        $this->dbManager = $dbManager;
        $this->cmdService = $cmdService;
    }

    /**
     * Adds a new task
     *
     * @param string $type
     * @param array<string, array<string,mixed>> $params
     * @param int $parentId
     * @return int|bool
     */
    public function addTask(string $type, array $params, int $parentId = null): int|bool
    {
        $newTask = new Task();
        $newTask->setStatus(Task::STATE_PENDING);
        $newTask->setParams(serialize($params));
        $newTask->setParentId($parentId);

        switch ($type) {
            case Task::TYPE_EXPORT:
            case Task::TYPE_IMPORT:
                $newTask->setType($type);
                $result = $this->getDbManager()->getAdapter('configuration_db')->insert('task', $newTask->toArray());

                $cmd = new Command();
                $cmd->setCommandLine(Command::COMMAND_START_IMPEX_WORKER);
                $cmdWritten = $this->getCmdService()->sendCommand($cmd);
                break;
            default:
                return false;
        }

        return ($result && $cmdWritten) ? $result : false;
    }

    /**
     * Get Existing Task status
     * @param string $taskId
     * @return null
     */
    public function getStatus(string $taskId)
    {
        $task = $this->getDbManager()->getAdapter('configuration_db')->getRepository(TaskRepository::class)
            ->findOneById($taskId);
        return $task ? $task->getStatus() : null;
    }

    /**
     * Get existing task status by parent id
     *
     * @param int $parentId the parent task id on remote server
     * @return null
     */
    public function getStatusByParent(int $parentId)
    {
        $task = $this->getDbManager()->getAdapter('configuration_db')
            ->getRepository(TaskRepository::class)
            ->findOneByParentId($parentId);

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
        $task = $this->getDbManager()
            ->getAdapter('configuration_db')
            ->getRepository(TaskRepository::class)
            ->findOneById($taskId);

        if (!in_array($status, $task->getStatuses())) {
            return false;
        }

        $result = $this->getDbManager()
            ->getAdapter('configuration_db')
            ->getRepository(TaskRepository::class)
            ->updateStatus($status, $taskId);
        return $result;
    }
}
