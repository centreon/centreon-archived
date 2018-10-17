<?php

namespace CentreonRemote\Application\Clapi;

use Centreon\Domain\Repository\TaskRepository;
use Centreon\Infrastructure\Service\CentcoreCommandService;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use Curl\Curl;
use Pimple\Container;
use Centreon\Infrastructure\Service\CentreonClapiServiceInterface;
use Centreon\Domain\Entity\Task;
use Centreon\Domain\Entity\Command;

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
     * Worker method to process task queue for import/export
     * @return int
     */
    public function processQueue()
    {
        $datetime = (new \DateTime())->format("Y:m:d h:i:s");
        echo "\n[{$datetime}] Checking for pending export tasks: ";

        $tasks = $this->getDi()['centreon.db-manager']->getRepository(TaskRepository::class)->findExportTasks();
        if (count($tasks) == 0) {
            echo "None found";
        } else {
            foreach ($tasks as $x => $task) {
                /*
                 * mark task as being worked on
                 */
                $this->getDi()['centreon.taskservice']->updateStatus($task->getId(),Task::STATE_PROGRESS);

                $params = unserialize($task->getParams())['params'];
                $commitment = new ExportCommitment($params['server'], $params['pollers']);

                try {
                   $this->getDi()['centreon_remote.export']->export($commitment);
                } catch (\Exception $e) {
                    echo $e->__toString()."\n";
                }

                $this->getDi()['centreon.taskservice']->updateStatus($task->getId(),Task::STATE_COMPLETED);

                /**
                 * move export file
                 */
                $cmd = new Command();
                $compositeKey = $params['server'].':'.$task->getId();
                $cmd->setCommandLine(Command::COMMAND_TRANSFER_EXPORT_FILES.$compositeKey);
                $cmdService = new CentcoreCommandService();
                $cmdWritten = $cmdService->sendCommand($cmd);
                
                echo ($x ? ', ' : '') . "#" . $task->getId();
            }
        }

        echo "\n[{$datetime}] Checking for pending import tasks: ";

        $tasks = $this->getDi()['centreon.db-manager']->getRepository(TaskRepository::class)->findImportTasks();

        if (count($tasks) == 0)
        {
            echo "None found";
        } else {
            foreach ($tasks as $x => $task) {
                /*
                * mark task as being worked on
                */
                $this->getDi()['centreon.taskservice']->updateStatus($task->getId(),Task::STATE_PROGRESS);

                try {
                    $this->getDi()['centreon_remote.export']->import();
                } catch (\Exception $e) {
                    echo $e->__toString()."\n";
                }
                $this->getDi()['centreon.taskservice']->updateStatus($task->getId(),Task::STATE_COMPLETED);
                
                echo ($x ? ', ' : '') . "#" . $task->getId() . " (parent ID #" . $task->getParentId() . ")";
            }

        }

        echo "\n[{$datetime}] Worker cycle completed.";
    }

    /**
     * Worker method to create task for import on remote.
     */
    public function createRemoteTask(int $taskId)
    {
        $task = $this->getDi()['centreon.db-manager']->getRepository(TaskRepository::class)->findOneById($taskId);

        /**
         * create import task on remote
         */
        $params = unserialize($task->getParams())['params'];
        $centreonPath = trim($params['centreon_path'], '/');
        $centreonPath = $centreonPath ? $centreonPath : '/centreon';
        $url = "{$params['remote_ip']}/{$centreonPath}/api/external.php?object=centreon_task_service&action=AddImportTaskWithParent";

        try {
            $curl = new Curl;
            $res = $curl->post($url, ['parent_id' => $task->getId()]);

            if ($curl->error) {
                printf("Curl error while creating parent task: %s\n", $curl->error_message);
                printf("url called: %s\n", $url);
            }
        } catch (\ErrorException $e) {
            echo "Curl error while creating parent task\n";
            echo $e->__toString()."\n";
        }
    }

    public function getDi(): Container
    {
        return $this->di;
    }
}
