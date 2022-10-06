<?php

namespace CentreonRemote\Application\Clapi;

use Centreon\Domain\Repository\TaskRepository;
use Centreon\Infrastructure\Service\CentcoreCommandService;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use Pimple\Container;
use Centreon\Infrastructure\Service\CentreonClapiServiceInterface;
use Centreon\Domain\Entity\Task;
use Centreon\Domain\Entity\Command;

/**
 * Manage worker queue with centcore (import/export tasks...)
 */
class CentreonWorker implements CentreonClapiServiceInterface
{

    /**
     * @var Container
     */
    private $di;

    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    /**
     * Get Class name
     * @return string
     * @throws \ReflectionException
     */
    public static function getName() : string
    {
        return (new \ReflectionClass(__CLASS__))->getShortName();
    }

    /**
     * Process task queue for import/export
     *
     * @return void
     */
    public function processQueue(): void
    {
        // check export tasks in database and execute these
        $this->processExportTasks();

        // check import tasks in database and execute these
        $this->processImportTasks();
    }

    /**
     * Execute export tasks which are store in task table
     *
     * @return void
     */
    private function processExportTasks(): void
    {
        $tasks = $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]
            ->getRepository(TaskRepository::class)
            ->findExportTasks() ?? [];

        echo date("Y-m-d H:i:s") . " - INFO - Checking for pending export tasks: "
            . count($tasks) . " task(s) found.\n";

        foreach (array_values($tasks) as $task) {
            echo date("Y-m-d H:i:s") . " - INFO - Processing task #" . $task->getId() . "...\n";

            /*
             * mark task as being worked on
             */
            $this->getDi()['centreon.taskservice']->updateStatus($task->getId(), Task::STATE_PROGRESS);
            $serializedParams = htmlspecialchars($task->getParams(), ENT_NOQUOTES);
            if (empty($serializedParams)) {
                throw new \Exception('Invalid Parameters');
            }
            $taskParams = unserialize($serializedParams);
            if (!array_key_exists('params', $taskParams)) {
                throw new \Exception('Missing parameters: params');
            }
            $params = $taskParams['params'];
            $commitment = new ExportCommitment($params['server'], $params['pollers']);

            try {
                $this->getDi()['centreon_remote.export']->export($commitment);

                $this->getDi()['centreon.taskservice']->updateStatus($task->getId(), Task::STATE_COMPLETED);

                /**
                 * move export file
                 */
                $cmd = new Command();
                $compositeKey = $params['server'] . ':' . $task->getId();
                $cmd->setCommandLine(Command::COMMAND_TRANSFER_EXPORT_FILES . $compositeKey);
                $cmdService = new CentcoreCommandService();
                $cmdWritten = $cmdService->sendCommand($cmd);

                echo date("Y-m-d H:i:s") . " - INFO - Task #" . $task->getId() . " completed.\n";
            } catch (\Exception $e) {
                echo date("Y-m-d H:i:s") . " - ERROR - Task #" . $task->getId() . " failed.\n";
                echo date("Y-m-d H:i:s") . " - ERROR - Error message: " . $e->getMessage() . "\n";
                $this->getDi()['centreon.taskservice']->updateStatus($task->getId(), Task::STATE_FAILED);
            }
        }

        echo date("Y-m-d H:i:s") . " - INFO - Worker cycle completed.\n";
    }

    /**
     * Execute import tasks which are store in task table
     *
     * @return void
     */
    private function processImportTasks(): void
    {
        $tasks = $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]
            ->getRepository(TaskRepository::class)
            ->findImportTasks() ?? [];

        echo date("Y-m-d H:i:s") . " - INFO - Checking for pending import tasks: "
            . count($tasks) . " task(s) found.\n";

        foreach ($tasks as $x => $task) {
            echo date("Y-m-d H:i:s") . " - INFO - Processing task #"
                . $task->getId() . " (parent ID #" . $task->getParentId() . ")...\n";

            /*
             * mark task as being worked on
             */
            $this->getDi()['centreon.taskservice']->updateStatus($task->getId(), Task::STATE_PROGRESS);

            try {
                $this->getDi()['centreon_remote.export']->import();

                $this->getDi()['centreon.taskservice']->updateStatus($task->getId(), Task::STATE_COMPLETED);
                echo date("Y-m-d H:i:s") . " - INFO - Task #" . $task->getId() . " completed.\n";
            } catch (\Exception $e) {
                echo date("Y-m-d H:i:s") . " - ERROR - Task #" . $task->getId() . " failed.\n";
                echo date("Y-m-d H:i:s") . " - ERROR - Error message: " . $e->getMessage() . "\n";
                $this->getDi()['centreon.taskservice']->updateStatus($task->getId(), Task::STATE_FAILED);
            }
        }

        echo date("Y-m-d H:i:s") . " - INFO - Worker cycle completed.\n";
    }

    /**
     * Worker method to create task for import on remote.
     *
     * @param int $taskId the task id to create on the remote server
     * @return void
     */
    public function createRemoteTask(int $taskId): void
    {
        // find task parameters (type, status, params...)
        $task = $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]
            ->getRepository(TaskRepository::class)
            ->findOneById($taskId);

        /**
         * create import task on remote
         */
        $serializedParams = htmlspecialchars($task->getParams());
        if (empty($serializedParams)) {
            throw new \Exception('Invalid Parameters');
        }
        $taskParams = unserialize($serializedParams);
        if (!array_key_exists('params', $taskParams)) {
            throw new \Exception('Missing parameters: params');
        }
        $params = $taskParams['params'];
        $centreonPath = trim($params['centreon_path'], '/');
        $centreonPath = $centreonPath ? $centreonPath : '/centreon';
        $url = $params['http_method'] ? $params['http_method'] . "://" : "";
        $url .= $params['remote_ip'];
        $url .= $params['http_port'] ? ":" . $params['http_port'] : "";
        $url .= "/{$centreonPath}/api/external.php?object=centreon_task_service&action=AddImportTaskWithParent";

        try {
            $curl = new \CentreonRestHttp;
            $res = $curl->call(
                $url,
                'POST',
                ['parent_id' => $task->getId()],
                [],
                false,
                $params['no_check_certificate'],
                $params['no_proxy']
            );
        } catch (\Exception $e) {
            echo date("Y-m-d H:i:s") . " - ERROR - Error while creating parent task on "
                . $url . ".\n";
            echo date("Y-m-d H:i:s") . " - ERROR - Error message: " . $e->getMessage() . "\n";
        }
    }

    public function getDi(): Container
    {
        return $this->di;
    }
}
