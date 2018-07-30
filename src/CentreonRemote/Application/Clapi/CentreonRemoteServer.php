<?php

namespace CentreonRemote\Application\Clapi;

use Centreon\Domain\Repository\InformationsRepository;
use Centreon\Domain\Repository\TopologyRepository;
use Centreon\Domain\Repository\OptionsRepository;
use Pimple\Container;
use Centreon\Infrastructure\Service\CentreonClapiServiceInterface;
use ReflectionClass;

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

    public function test($args): int
    {
        print_r($args);
        echo "OK\n";

        return 200;
    }

    public function enableRemote($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            echo "Incorrect IP parameter, please pass `-v IP` of the master server\n"; return 200;
        }

        echo "Starting Centreon Remote enable process: \n";

        echo "\n Limiting Menu Access...";
        $result = $this->getDi()['centreon.db-manager']->getRepository(TopologyRepository::class)->disableMenus();
        echo ($result) ? 'Success' : 'Fail' . "\n";

        echo "\n Limiting Actions...";
        $result = $this->getDi()['centreon.db-manager']->getRepository(InformationsRepository::class)->toggleRemote('yes');
        echo 'Done'. "\n";

        echo "\n Notifying Master...";
        $result = $this->getDi()['centreon.notifymaster']->pingMaster($ip);
        echo (!empty($result['status']) && $result['status'] == 'success') ? 'Success' : 'Fail' . "\n";

        echo "\n Centreon Remote enabling finished.\n";
    }

    public function disableRemote()
    {
        echo "Starting Centreon Remote disable process: \n";

        echo "\n Restoring Menu Access...";
        $result = $this->getDi()['centreon.db-manager']->getRepository(TopologyRepository::class)->enableMenus();
        echo ($result) ? 'Success' : 'Fail' . "\n";

        echo "\n Restoring Actions...";
        $result = $this->getDi()['centreon.db-manager']->getRepository(InformationsRepository::class)->toggleRemote('no');
        echo 'Done'. "\n";

        echo "\n Centreon Remote disabling finished.\n";
    }

    public function getDi(): Container
    {
        return $this->di;
    }
}
