<?php

namespace CentreonRemote\Application\Clapi;

use Centreon\Domain\Repository\InformationsRepository;
use Centreon\Domain\Repository\TaskRepository;
use Centreon\Domain\Repository\TopologyRepository;
use Centreon\Domain\Repository\OptionsRepository;
use Pimple\Container;
use Centreon\Infrastructure\Service\CentreonClapiServiceInterface;
use ReflectionClass;

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
        echo "Checking for pending export tasks: ";

        $tasks = $this->getDi()['centreon.db-manager']->getRepository(TaskRepository::class)->findExportTasks();

        if (count($tasks) == 0)
        {
            echo "None found\n";
        } else {
            // process export
        }

        echo "\n Checking for pending import tasks: ";

        $tasks = $this->getDi()['centreon.db-manager']->getRepository(TaskRepository::class)->findImportTasks();

        if (count($tasks) == 0)
        {
            echo "None found\n";
        } else {
            // process import
        }

        echo "\n Worker cycle completed.\n";
    }

    public function getDi(): Container
    {
        return $this->di;
    }
}
