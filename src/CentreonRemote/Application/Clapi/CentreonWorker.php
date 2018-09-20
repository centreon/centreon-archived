<?php

namespace CentreonRemote\Application\Clapi;

use Centreon\Domain\Repository\InformationsRepository;
use Centreon\Domain\Repository\TaskRepository;
use Centreon\Domain\Repository\TopologyRepository;
use Centreon\Domain\Repository\OptionsRepository;
use Pimple\Container;
use Centreon\Infrastructure\Service\CentreonClapiServiceInterface;
use ReflectionClass;
use Centreon\Domain\Entity\Task;

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
        echo "Checking for pending export tasks: \n";

        $tasks = $this->getDi()['centreon.db-manager']->getRepository(TaskRepository::class)->findExportTasks();

        if (count($tasks) == 0)
        {
            echo "None found\n";
        } else {
            foreach ($tasks as $task) {
                $params = unserialize($task->getParams());
                $commitment = new CentreonRemote\Infrastructure\Export\ExportCommitment($params['server'], $params['pollers']);
                try {
                    $this->getDi()['centreon_remote.export']->export($commitment);
                } catch (\Exception $e) {
                //todo error handling
                }
                $this->getDi()['centreon.taskservice']->updateStatus($task->getId(),Task::STATE_COMPLETED);
            }
        }

        echo "\n Checking for pending import tasks: ";

        $tasks = $this->getDi()['centreon.db-manager']->getRepository(TaskRepository::class)->findImportTasks();

        if (count($tasks) == 0)
        {
            echo "None found\n";
        } else {
            foreach ($tasks as $task) {
                try {
                    $this->getDi()['centreon_remote.export']->import();
                } catch (\Exception $e) {
                    //todo error handling
                }
                $this->getDi()['centreon.taskservice']->updateStatus($task->getId(),Task::STATE_COMPLETED);
            }

        }

        echo "\n Worker cycle completed.\n";
    }

    public function getDi(): Container
    {
        return $this->di;
    }
}
