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
     * Clapi command to enable remote server
     *
     * @param string $ip ip address of the central server
     */
    public function enableRemote(string $string_ip)
    {
        $ipList = explode(',', $string_ip);
        foreach ($ipList as $ip)
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                printf("Incorrect IP parameter: '%s', please pass `-v IP` of the master server\n", $ip);
                return null;
            }
        }

        echo "Starting Centreon Remote enable process: \n";

        echo "Limiting Menu Access...";
        $result = $this->getDi()['centreon.db-manager']->getRepository(TopologyRepository::class)->disableMenus();
        echo ($result) ? 'Success' : 'Fail' . "\n";

        echo "Limiting Actions...";
        $this->getDi()['centreon.db-manager']->getRepository(InformationsRepository::class)->toggleRemote('yes');
        echo "Done\n";

        echo "\n Authorizing Master...";
        $this->getDi()['centreon.db-manager']->getRepository(InformationsRepository::class)->authorizeMaster($string_ip);
        echo "Done\n";

        echo "\n Set 'remote' instance type...";
        system(
            "sed -i -r 's/(\\\$instance_mode?\s+=?\s+\")([a-z]+)(\";)/\\1remote\\3/' " . _CENTREON_ETC_ . "/conf.pm"
        );
        echo "Done\n";

        echo "\n Notifying Master...";
        $result = $this->getDi()['centreon.notifymaster']->pingMaster($ip);
        $result = "";
        foreach ($ipList as $ip) {
            $result = $this->getDi()['centreon.notifymaster']->pingMaster($ip);
            if (!empty($result['status']) && $result['status'] == 'success') {
                echo "Success\n";
                continue;
            }
        }
        if (empty($result['status']) || $result['status'] != 'success') {
            printf("Fail: %s\n", $result['details']);
        }

        echo "Centreon Remote enabling finished.\n";
    }

    /**
     * Clapi command to disable remote server
     */
    public function disableRemote(): void
    {
        echo "Starting Centreon Remote disable process: \n";

        echo "Restoring Menu Access...";
        $result = $this->getDi()['centreon.db-manager']->getRepository(TopologyRepository::class)->enableMenus();
        echo ($result) ? 'Success' : 'Fail' . "\n";

        echo "Restoring Actions...";
        $this->getDi()['centreon.db-manager']->getRepository(InformationsRepository::class)->toggleRemote('no');
        echo 'Done'. "\n";

        echo "Restore 'central' instance type...";
        system(
            "sed -i -r 's/(\\\$instance_mode?\s+=?\s+\")([a-z]+)(\";)/\\1central\\3/' " . _CENTREON_ETC_ . "/conf.pm"
        );
        echo "Done\n";

        echo "Centreon Remote disabling finished.\n";
    }

    /**
     * Import files which are stored in import directory
     */
    public function import(): void
    {
        echo "Starting Centreon Remote import process: \n";
        echo "Importing...";

        try {
            $this->getDi()['centreon_remote.export']->import();
            echo "Success\n";
        } catch (\Exception $e) {
            echo "Fail: " . $e->getMessage() . "\n";
        }

        echo "Centreon Remote import finished.\n";
    }

    public function getDi(): Container
    {
        return $this->di;
    }
}
