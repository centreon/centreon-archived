<?php

namespace CentreonRemote\Application\Clapi;

use Centreon\Domain\Repository\InformationsRepository;
use Centreon\Domain\Repository\TopologyRepository;
use Centreon\Domain\Repository\OptionsRepository;
use Pimple\Container;
use Centreon\Infrastructure\Service\CentreonClapiServiceInterface;
use ReflectionClass;

/**
 * Class to manage remote server with clapi (enable, disable, import)
 */
class CentreonRemoteServer implements CentreonClapiServiceInterface
{

    /**
     * @var Container
     * todo: extract only services we need to avoid using whole container
     */
    private $di;

    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    public static function getName() : string
    {
        return (new \ReflectionClass(__CLASS__))->getShortName();
    }



    /**
     * Import files which are stored in import directory
     */
    public function import(): void
    {
        echo date("Y-m-d H:i:s") . " - INFO - Starting Centreon Remote import process...\n";

        try {
            $this->getDi()['centreon_remote.export']->import();
            echo date("Y-m-d H:i:s") . " - INFO - Import succeed\n";
        } catch (\Exception $e) {
            echo date("Y-m-d H:i:s") . " - ERROR - Import failed\n";
            echo date("Y-m-d H:i:s") . " - ERROR - Error message: " . $e->getMessage() . "\n";
        }

        echo date("Y-m-d H:i:s") . " - INFO - Centreon Remote import process finished.\n";
    }

    public function getDi(): Container
    {
        return $this->di;
    }
}
